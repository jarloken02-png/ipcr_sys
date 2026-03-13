<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\DeanCalibration;
use App\Models\IpcrSubmission;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class DeanReviewController extends Controller
{
    /**
     * Get all IPCR submissions from faculty members in the dean's department.
     */
    public function facultySubmissions(Request $request)
    {
        $user = $request->user();
        $departmentId = $user->department_id;

        if (!$departmentId) {
            return response()->json([
                'success' => true,
                'submissions' => [],
            ]);
        }

        // Get all faculty users in the same department (exclude the dean themselves)
        $facultyUserIds = User::where('department_id', $departmentId)
            ->where('id', '!=', $user->id)
            ->whereHas('userRoles', function ($query) {
                $query->where('role', 'faculty');
            })
            ->pluck('id');

        $submissions = IpcrSubmission::whereIn('user_id', $facultyUserIds)
            ->with('user:id,name,employee_id')
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function ($submission) use ($user) {
                $calibration = DeanCalibration::where('dean_id', $user->id)
                    ->where('ipcr_submission_id', $submission->id)
                    ->first();
                return [
                    'id' => $submission->id,
                    'title' => $submission->title,
                    'school_year' => $submission->school_year,
                    'semester' => $submission->semester,
                    'status' => $submission->status,
                    'submitted_at' => $submission->submitted_at?->format('M d, Y'),
                    'user_name' => $submission->user?->name ?? 'Unknown',
                    'employee_id' => $submission->user?->employee_id ?? 'N/A',
                    'calibration_status' => $calibration?->status,
                    'calibration_score' => $calibration?->overall_score,
                ];
            });

        return response()->json([
            'success' => true,
            'submissions' => $submissions,
        ]);
    }

    /**
     * View a specific faculty IPCR submission (read-only for dean).
     */
    public function showFacultySubmission(Request $request, $id)
    {
        $user = $request->user();
        $departmentId = $user->department_id;

        if (!$departmentId) {
            abort(403, 'No department assigned.');
        }

        // The submission must belong to a user in the dean's department
        $submission = IpcrSubmission::where('id', $id)
            ->whereHas('user', function ($query) use ($departmentId, $user) {
                $query->where('department_id', $departmentId)
                      ->where('id', '!=', $user->id);
            })
            ->with('user:id,name,employee_id')
            ->firstOrFail();

        ActivityLogService::log('dean_reviewed_faculty_submission', 'Reviewed faculty IPCR submission: ' . $submission->title . ' by ' . ($submission->user->name ?? 'Unknown'), $submission);

        // Load existing calibration by this dean
        $calibration = DeanCalibration::where('dean_id', $user->id)
            ->where('ipcr_submission_id', $submission->id)
            ->first();

        return response()->json([
            'success' => true,
            'submission' => [
                'id' => $submission->id,
                'user_id' => $submission->user_id,
                'title' => $submission->title,
                'school_year' => $submission->school_year,
                'semester' => $submission->semester,
                'table_body_html' => $submission->table_body_html,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at?->format('M d, Y'),
                'user_name' => $submission->user?->name ?? 'Unknown',
                'employee_id' => $submission->user?->employee_id ?? 'N/A',
                'approved_by' => $submission->approved_by ?? '',
                'noted_by' => $submission->noted_by ?? '',
                'calibration' => $calibration ? [
                    'id' => $calibration->id,
                    'calibration_data' => $calibration->calibration_data,
                    'overall_score' => $calibration->overall_score,
                    'status' => $calibration->status,
                ] : null,
            ],
        ]);
    }

    /**
     * Get all IPCR submissions from other deans (for calibration).
     */
    public function deanSubmissions(Request $request)
    {
        $user = $request->user();

        // Get all users with the dean role (excluding the current user)
        $deanUserIds = User::where('id', '!=', $user->id)
            ->whereHas('userRoles', function ($query) {
                $query->where('role', 'dean');
            })
            ->pluck('id');

        $submissions = IpcrSubmission::whereIn('user_id', $deanUserIds)
            ->with(['user:id,name,employee_id,department_id', 'user.department:id,name,code'])
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function ($submission) use ($user) {
                $calibration = DeanCalibration::where('dean_id', $user->id)
                    ->where('ipcr_submission_id', $submission->id)
                    ->first();
                return [
                    'id' => $submission->id,
                    'title' => $submission->title,
                    'school_year' => $submission->school_year,
                    'semester' => $submission->semester,
                    'status' => $submission->status,
                    'submitted_at' => $submission->submitted_at?->format('M d, Y'),
                    'user_name' => $submission->user?->name ?? 'Unknown',
                    'employee_id' => $submission->user?->employee_id ?? 'N/A',
                    'department' => $submission->user?->department?->code ?? $submission->user?->department?->name ?? 'N/A',
                    'calibration_status' => $calibration?->status,
                    'calibration_score' => $calibration?->overall_score,
                ];
            });

        return response()->json([
            'success' => true,
            'submissions' => $submissions,
        ]);
    }

    /**
     * View a specific dean's IPCR submission (read-only for calibration).
     */
    public function showDeanSubmission(Request $request, $id)
    {
        $user = $request->user();

        // The submission must belong to another dean
        $deanUserIds = User::where('id', '!=', $user->id)
            ->whereHas('userRoles', function ($query) {
                $query->where('role', 'dean');
            })
            ->pluck('id');

        $submission = IpcrSubmission::where('id', $id)
            ->whereIn('user_id', $deanUserIds)
            ->with(['user:id,name,employee_id,department_id', 'user.department:id,name,code'])
            ->firstOrFail();

        ActivityLogService::log('dean_reviewed_dean_submission', 'Reviewed dean IPCR submission: ' . $submission->title . ' by ' . ($submission->user->name ?? 'Unknown'), $submission);

        // Load existing calibration by this dean
        $calibration = DeanCalibration::where('dean_id', $user->id)
            ->where('ipcr_submission_id', $submission->id)
            ->first();

        return response()->json([
            'success' => true,
            'submission' => [
                'id' => $submission->id,
                'user_id' => $submission->user_id,
                'title' => $submission->title,
                'school_year' => $submission->school_year,
                'semester' => $submission->semester,
                'table_body_html' => $submission->table_body_html,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at?->format('M d, Y'),
                'user_name' => $submission->user?->name ?? 'Unknown',
                'employee_id' => $submission->user?->employee_id ?? 'N/A',
                'department' => $submission->user?->department?->code ?? $submission->user?->department?->name ?? 'N/A',
                'approved_by' => $submission->approved_by ?? '',
                'noted_by' => $submission->noted_by ?? '',
                'calibration' => $calibration ? [
                    'id' => $calibration->id,
                    'calibration_data' => $calibration->calibration_data,
                    'overall_score' => $calibration->overall_score,
                    'status' => $calibration->status,
                ] : null,
            ],
        ]);
    }

    /**
     * Save or update a calibration (draft or finalized).
     */
    public function saveCalibration(Request $request)
    {
        $request->validate([
            'ipcr_submission_id' => 'required|integer|exists:ipcr_submissions,id',
            'calibration_data' => 'required|array',
            'calibration_data.*.q' => 'nullable|numeric|min:0|max:5',
            'calibration_data.*.e' => 'nullable|numeric|min:0|max:5',
            'calibration_data.*.t' => 'nullable|numeric|min:0|max:5',
            'calibration_data.*.a' => 'nullable|numeric|min:0|max:5',
            'calibration_data.*.remarks' => 'nullable|string|max:500',
            'overall_score' => 'nullable|numeric|min:0|max:5',
            'status' => 'required|string|in:draft,calibrated',
        ]);

        $dean = $request->user();
        $submissionId = $request->ipcr_submission_id;

        // Verify the dean has access to this submission
        $this->verifyAccessToSubmission($dean, $submissionId);

        $calibration = DeanCalibration::updateOrCreate(
            [
                'dean_id' => $dean->id,
                'ipcr_submission_id' => $submissionId,
            ],
            [
                'calibration_data' => $request->calibration_data,
                'overall_score' => $request->overall_score,
                'status' => $request->status,
            ]
        );

        $action = $request->status === 'calibrated' ? 'finalized' : 'saved draft of';
        ActivityLogService::log(
            'dean_calibration_' . $request->status,
            ucfirst($action) . ' calibration for IPCR submission #' . $submissionId,
            $calibration
        );

        // Create notification for the submission owner when calibration is finalized
        if ($request->status === 'calibrated') {
            $submission = IpcrSubmission::find($submissionId);
            if ($submission) {
                AdminNotification::create([
                    'title' => 'IPCR Calibrated',
                    'message' => $dean->name . ' has calibrated your IPCR with an overall score of ' . number_format($request->overall_score, 2) . '.',
                    'type' => 'success',
                    'audience' => 'all',
                    'user_id' => $submission->user_id,
                    'is_active' => true,
                    'created_by' => $dean->id,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => $request->status === 'calibrated'
                ? 'Calibration finalized successfully.'
                : 'Calibration draft saved.',
            'calibration' => [
                'id' => $calibration->id,
                'status' => $calibration->status,
                'overall_score' => $calibration->overall_score,
            ],
        ]);
    }

    /**
     * Verify the dean has access to a given submission (faculty in dept or another dean).
     */
    private function verifyAccessToSubmission(User $dean, int $submissionId): void
    {
        $departmentId = $dean->department_id;

        // Check if it's a faculty submission in the dean's department
        $isFacultySubmission = IpcrSubmission::where('id', $submissionId)
            ->whereHas('user', function ($query) use ($departmentId, $dean) {
                $query->where('department_id', $departmentId)
                      ->where('id', '!=', $dean->id);
            })
            ->exists();

        if ($isFacultySubmission) return;

        // Check if it's another dean's submission
        $deanUserIds = User::where('id', '!=', $dean->id)
            ->whereHas('userRoles', function ($query) {
                $query->where('role', 'dean');
            })
            ->pluck('id');

        $isDeanSubmission = IpcrSubmission::where('id', $submissionId)
            ->whereIn('user_id', $deanUserIds)
            ->exists();

        if (!$isDeanSubmission) {
            abort(403, 'You do not have access to this submission.');
        }
    }
}
