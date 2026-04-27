<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\OpcrSubmission;
use App\Models\SupportingDocument;
use App\Services\ActivityLogService;
use App\Services\SoLabelNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OpcrSubmissionController extends Controller
{
    public function store(Request $request, SoLabelNormalizer $soLabelNormalizer)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'school_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'string', 'max:50'],
            'table_body_html' => ['required', 'string'],
            'so_count_json' => ['nullable'],
            'template_id' => ['nullable', 'integer'],
            'saved_copy_id' => ['nullable', 'integer'],
            'noted_by' => ['nullable', 'string', 'max:255'],
            'approved_by' => ['nullable', 'string', 'max:255'],
        ]);

        $normalizedPayload = $soLabelNormalizer->normalizeAndExtractCounts((string) ($validated['table_body_html'] ?? ''));
        $validated['table_body_html'] = $normalizedPayload['table_body_html'];
        $soCountJson = $normalizedPayload['so_count_json'];

        $userId = $request->user()->id;
        $submissionLock = Cache::lock("opcr:submit:user:{$userId}", 10);

        if (!$submissionLock->get()) {
            return response()->json([
                'message' => 'A submission is already being processed. Please wait and try again.',
            ], 429);
        }

        try {
            $existingSubmitted = OpcrSubmission::where('user_id', $userId)
                ->where('status', 'submitted')
                ->whereNotNull('submitted_at')
                ->latest('id')
                ->first();

            if ($existingSubmitted) {
                return response()->json([
                    'message' => 'You already have a submitted OPCR. Please unsubmit it first before submitting a new one.',
                    'id' => $existingSubmitted->id,
                    'submission_cancelled' => true,
                ], 409);
            }

            $recentWindow = now()->subSeconds(15);
            $normalizedIncomingHtml = trim((string) ($validated['table_body_html'] ?? ''));

            $duplicateSubmission = OpcrSubmission::where('user_id', $userId)
                ->where('status', 'submitted')
                ->where('title', $validated['title'])
                ->where('school_year', $validated['school_year'])
                ->where('semester', $validated['semester'])
                ->where('created_at', '>=', $recentWindow)
                ->latest('id')
                ->get()
                ->first(function (OpcrSubmission $candidate) use ($normalizedIncomingHtml) {
                    return trim((string) ($candidate->table_body_html ?? '')) === $normalizedIncomingHtml;
                });

            if ($duplicateSubmission) {
                return response()->json([
                    'message' => 'OPCR submission already received',
                    'id' => $duplicateSubmission->id,
                    'duplicate_prevented' => true,
                ]);
            }

            OpcrSubmission::where('user_id', $userId)
                ->update(['is_active' => false]);

            $submission = OpcrSubmission::create([
                'user_id' => $userId,
                'title' => $validated['title'],
                'school_year' => $validated['school_year'],
                'semester' => $validated['semester'],
                'table_body_html' => $validated['table_body_html'],
                'noted_by' => $validated['noted_by'] ?? null,
                'approved_by' => $validated['approved_by'] ?? null,
                'so_count_json' => $soCountJson,
                'status' => 'submitted',
                'is_active' => true,
                'submitted_at' => now(),
            ]);

            // Copy supporting documents from template/draft to submission
            if (!empty($validated['template_id'])) {
                $this->copySupportingDocuments(
                    'opcr_template',
                    $validated['template_id'],
                    'opcr_submission',
                    $submission->id,
                    $userId
                );
            } elseif (!empty($validated['saved_copy_id'])) {
                $this->copySupportingDocuments(
                    'opcr_saved_copy',
                    $validated['saved_copy_id'],
                    'opcr_submission',
                    $submission->id,
                    $userId
                );
            }

            ActivityLogService::log('opcr_submitted', 'Submitted OPCR: ' . $submission->title, $submission);

            return response()->json([
                'message' => 'OPCR submitted successfully',
                'id' => $submission->id,
            ]);
        } finally {
            optional($submissionLock)->release();
        }
    }

    /**
     * Copy supporting documents from template to submission
     */
    private function copySupportingDocuments($fromType, $fromId, $toType, $toId, $userId)
    {
        try {
            $documents = SupportingDocument::where('user_id', $userId)
                ->where('documentable_type', $fromType)
                ->where('documentable_id', $fromId)
                ->get();

            foreach ($documents as $doc) {
                SupportingDocument::create([
                    'user_id' => $userId,
                    'documentable_type' => $toType,
                    'documentable_id' => $toId,
                    'so_label' => $doc->so_label,
                    'filename' => $doc->filename,
                    'path' => $doc->path,
                    'original_name' => $doc->original_name,
                    'mime_type' => $doc->mime_type,
                    'file_size' => $doc->file_size,
                ]);
            }

            Log::info('Copied supporting documents', [
                'from' => "{$fromType}:{$fromId}",
                'to' => "{$toType}:{$toId}",
                'count' => $documents->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to copy supporting documents: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $submission = OpcrSubmission::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'submission' => $submission,
        ]);
    }

    public function update(Request $request, $id, SoLabelNormalizer $soLabelNormalizer)
    {
        try {
            $validated = $request->validate([
                'title' => ['nullable', 'string', 'max:255'],
                'school_year' => ['nullable', 'string', 'max:20'],
                'semester' => ['nullable', 'string', 'max:50'],
                'table_body_html' => ['nullable', 'string'],
                'so_count_json' => ['nullable'],
                'noted_by' => ['nullable', 'string', 'max:255'],
                'approved_by' => ['nullable', 'string', 'max:255'],
            ]);

            $submission = OpcrSubmission::where('id', $id)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $updateData = [];

            if (array_key_exists('title', $validated) && $validated['title'] !== null) {
                $updateData['title'] = $validated['title'];
            }
            if (array_key_exists('school_year', $validated) && $validated['school_year'] !== null) {
                $updateData['school_year'] = $validated['school_year'];
            }
            if (array_key_exists('semester', $validated) && $validated['semester'] !== null) {
                $updateData['semester'] = $validated['semester'];
            }
            if (array_key_exists('table_body_html', $validated)) {
                $normalizedPayload = $soLabelNormalizer->normalizeAndExtractCounts((string) ($validated['table_body_html'] ?? ''));
                $updateData['table_body_html'] = $normalizedPayload['table_body_html'];
                $updateData['so_count_json'] = $normalizedPayload['so_count_json'];
            }
            if (!array_key_exists('table_body_html', $validated) && array_key_exists('so_count_json', $validated) && $validated['so_count_json'] !== null) {
                $soCount = $validated['so_count_json'];
                if (is_string($soCount)) {
                    $soCount = json_decode($soCount, true);
                }
                $updateData['so_count_json'] = $soCount;
            }
            if (array_key_exists('noted_by', $validated)) {
                $updateData['noted_by'] = $validated['noted_by'];
            }
            if (array_key_exists('approved_by', $validated)) {
                $updateData['approved_by'] = $validated['approved_by'];
            }

            $submission->update($updateData);
            $submission->refresh();

            ActivityLogService::log('opcr_submission_updated', 'Updated OPCR submission: ' . $submission->title, $submission);

            return response()->json([
                'success' => true,
                'message' => 'OPCR submission updated successfully',
                'submission' => $submission,
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating OPCR submission', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 400);
        }
    }

    public function setActive($id)
    {
        $userId = auth()->id();

        OpcrSubmission::where('user_id', $userId)
            ->update(['is_active' => false]);

        $submission = OpcrSubmission::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $submission->update(['is_active' => true]);

        ActivityLogService::log('opcr_submission_activated', 'Activated OPCR submission: ' . $submission->title, $submission);

        return response()->json([
            'success' => true,
            'message' => 'OPCR submission set as active successfully',
        ]);
    }

    public function deactivate($id)
    {
        $userId = auth()->id();

        $submission = OpcrSubmission::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $submission->update(['is_active' => false]);

        ActivityLogService::log('opcr_submission_deactivated', 'Deactivated OPCR submission: ' . $submission->title, $submission);

        return response()->json([
            'success' => true,
            'message' => 'OPCR submission deactivated successfully',
        ]);
    }

    public function unsubmit($id)
    {
        $userId = auth()->id();

        $submission = OpcrSubmission::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();

        $submission->update([
            'status' => 'draft',
            'submitted_at' => null,
            'is_active' => false,
        ]);

        ActivityLogService::log('opcr_unsubmitted', 'Unsubmitted OPCR: ' . $submission->title, $submission);

        return response()->json([
            'success' => true,
            'message' => 'OPCR submission has been reverted to draft status',
        ]);
    }

    public function destroy($id)
    {
        $userId = auth()->id();
        $user = auth()->user();

        $submission = OpcrSubmission::where('id', $id)->firstOrFail();

        if (!$user->hasRole('admin') && $submission->user_id != $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: You can only delete your own submissions',
            ], 403);
        }

        $title = $submission->title;
        $submission->delete();

        ActivityLogService::log('opcr_submission_deleted', 'Deleted OPCR submission: ' . $title);

        return response()->json([
            'success' => true,
            'message' => 'OPCR submission deleted successfully',
        ]);
    }
}
