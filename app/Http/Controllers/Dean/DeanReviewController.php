<?php

namespace App\Http\Controllers\Dean;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\DeanCalibration;
use App\Models\IpcrSubmission;
use App\Models\User;
use App\Services\ActivityLogService;
use DOMDocument;
use DOMXPath;
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
            ->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->with('user:id,name,employee_id')
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function ($submission) use ($user) {
                $myCalibration = $this->getDeanCalibration($user->id, $submission->id);
                $latestCalibrated = $this->getLatestCalibratedCalibration($submission->id);

                $displayStatus = $latestCalibrated
                    ? 'calibrated'
                    : ($myCalibration?->status ?? null);

                $displayScore = $latestCalibrated?->overall_score
                    ?? $myCalibration?->overall_score;

                return [
                    'id' => $submission->id,
                    'title' => $submission->title,
                    'school_year' => $submission->school_year,
                    'semester' => $submission->semester,
                    'status' => $submission->status,
                    'submitted_at' => $submission->submitted_at?->format('M d, Y'),
                    'user_name' => $submission->user?->name ?? 'Unknown',
                    'employee_id' => $submission->user?->employee_id ?? 'N/A',
                    'calibration_status' => $displayStatus,
                    'calibration_score' => $displayScore,
                    'calibrated_by' => $latestCalibrated?->dean?->name,
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
            ->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->whereHas('user', function ($query) use ($departmentId, $user) {
                $query->where('department_id', $departmentId)
                      ->where('id', '!=', $user->id);
            })
            ->with('user:id,name,employee_id')
            ->firstOrFail();

        ActivityLogService::log('dean_reviewed_faculty_submission', 'Reviewed faculty IPCR submission: ' . $submission->title . ' by ' . ($submission->user->name ?? 'Unknown'), $submission);

        // Load this dean's calibration first; otherwise load latest finalized calibration for recalibration.
        $myCalibration = $this->getDeanCalibration($user->id, $submission->id);
        $latestCalibrated = $this->getLatestCalibratedCalibration($submission->id);
        $calibrationToLoad = $myCalibration ?: $latestCalibrated;

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
                'calibration' => $calibrationToLoad ? [
                    'id' => $calibrationToLoad->id,
                    'calibration_data' => $calibrationToLoad->calibration_data,
                    'overall_score' => $calibrationToLoad->overall_score,
                    'status' => $calibrationToLoad->status,
                    'dean_feedback' => $this->mergeDeanFeedback($calibrationToLoad),
                    'dean_comment' => $calibrationToLoad->dean_comment,
                    'dean_suggestion' => $calibrationToLoad->dean_suggestion,
                    'dean_name' => $calibrationToLoad->dean?->name,
                    'is_current_dean' => (int) $calibrationToLoad->dean_id === (int) $user->id,
                ] : null,
                'latest_calibration' => $latestCalibrated ? [
                    'overall_score' => $latestCalibrated->overall_score,
                    'status' => $latestCalibrated->status,
                    'dean_feedback' => $this->mergeDeanFeedback($latestCalibrated),
                    'dean_comment' => $latestCalibrated->dean_comment,
                    'dean_suggestion' => $latestCalibrated->dean_suggestion,
                    'dean_name' => $latestCalibrated->dean?->name,
                    'updated_at' => $latestCalibrated->updated_at?->format('M d, Y h:i A'),
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
            ->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->with(['user:id,name,employee_id,department_id', 'user.department:id,name,code'])
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->map(function ($submission) use ($user) {
                $myCalibration = $this->getDeanCalibration($user->id, $submission->id);
                $latestCalibrated = $this->getLatestCalibratedCalibration($submission->id);

                $displayStatus = $latestCalibrated
                    ? 'calibrated'
                    : ($myCalibration?->status ?? null);

                $displayScore = $latestCalibrated?->overall_score
                    ?? $myCalibration?->overall_score;

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
                    'calibration_status' => $displayStatus,
                    'calibration_score' => $displayScore,
                    'calibrated_by' => $latestCalibrated?->dean?->name,
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
            ->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->whereIn('user_id', $deanUserIds)
            ->with(['user:id,name,employee_id,department_id', 'user.department:id,name,code'])
            ->firstOrFail();

        ActivityLogService::log('dean_reviewed_dean_submission', 'Reviewed dean IPCR submission: ' . $submission->title . ' by ' . ($submission->user->name ?? 'Unknown'), $submission);

        // Load this dean's calibration first; otherwise load latest finalized calibration for recalibration.
        $myCalibration = $this->getDeanCalibration($user->id, $submission->id);
        $latestCalibrated = $this->getLatestCalibratedCalibration($submission->id);
        $calibrationToLoad = $myCalibration ?: $latestCalibrated;

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
                'calibration' => $calibrationToLoad ? [
                    'id' => $calibrationToLoad->id,
                    'calibration_data' => $calibrationToLoad->calibration_data,
                    'overall_score' => $calibrationToLoad->overall_score,
                    'status' => $calibrationToLoad->status,
                    'dean_feedback' => $this->mergeDeanFeedback($calibrationToLoad),
                    'dean_comment' => $calibrationToLoad->dean_comment,
                    'dean_suggestion' => $calibrationToLoad->dean_suggestion,
                    'dean_name' => $calibrationToLoad->dean?->name,
                    'is_current_dean' => (int) $calibrationToLoad->dean_id === (int) $user->id,
                ] : null,
                'latest_calibration' => $latestCalibrated ? [
                    'overall_score' => $latestCalibrated->overall_score,
                    'status' => $latestCalibrated->status,
                    'dean_feedback' => $this->mergeDeanFeedback($latestCalibrated),
                    'dean_comment' => $latestCalibrated->dean_comment,
                    'dean_suggestion' => $latestCalibrated->dean_suggestion,
                    'dean_name' => $latestCalibrated->dean?->name,
                    'updated_at' => $latestCalibrated->updated_at?->format('M d, Y h:i A'),
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
            'dean_feedback' => 'nullable|string|max:5000',
            'dean_comment' => 'nullable|string|max:5000',
            'dean_suggestion' => 'nullable|string|max:5000',
        ]);

        $dean = $request->user();
        $submissionId = $request->ipcr_submission_id;
        $deanFeedback = trim((string) $request->input('dean_feedback', ''));

        if ($deanFeedback === '') {
            $legacyComment = trim((string) $request->input('dean_comment', ''));
            $legacySuggestion = trim((string) $request->input('dean_suggestion', ''));
            $deanFeedback = $this->joinFeedbackParts($legacyComment, $legacySuggestion);
        }

        // Verify the dean has access to this submission
        $this->verifyAccessToSubmission($dean, $submissionId);

        $submission = IpcrSubmission::findOrFail($submissionId);
        if ($submission->status !== 'submitted' || is_null($submission->submitted_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Only submitted IPCRs can be calibrated.',
            ], 422);
        }

        $calibrationData = (array) $request->input('calibration_data', []);
        $computedOverallScore = $this->computeWeightedOverallScore((string) $submission->table_body_html, $calibrationData);
        $overallScore = $computedOverallScore ?? (is_numeric($request->overall_score) ? (float) $request->overall_score : null);

        $calibration = DeanCalibration::updateOrCreate(
            [
                'dean_id' => $dean->id,
                'ipcr_submission_id' => $submissionId,
            ],
            [
                'calibration_data' => $calibrationData,
                'overall_score' => $overallScore,
                'status' => $request->status,
                'dean_comment' => $deanFeedback !== '' ? $deanFeedback : null,
                'dean_suggestion' => null,
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
            if ($submission) {
                AdminNotification::create([
                    'title' => 'IPCR Calibrated',
                    'message' => $dean->name . ' has calibrated your IPCR with an overall score of ' . number_format((float) ($overallScore ?? 0), 2) . '.',
                    'type' => 'success',
                    'audience' => 'all',
                    'user_id' => $submission->user_id,
                    'is_active' => true,
                    'created_by' => $dean->id,
                ]);
            }
        }

        $latestCalibrated = $this->getLatestCalibratedCalibration($submissionId);
        $displayStatus = $latestCalibrated
            ? 'calibrated'
            : $calibration->status;
        $displayScore = $latestCalibrated?->overall_score
            ?? $calibration->overall_score;
        $displayCalibratedBy = $latestCalibrated?->dean?->name;

        return response()->json([
            'success' => true,
            'message' => $request->status === 'calibrated'
                ? 'Calibration finalized successfully.'
                : 'Calibration draft saved.',
            'calibration' => [
                'id' => $calibration->id,
                'status' => $calibration->status,
                'overall_score' => $calibration->overall_score,
                'dean_feedback' => $this->mergeDeanFeedback($calibration),
                'dean_comment' => $calibration->dean_comment,
                'dean_suggestion' => $calibration->dean_suggestion,
                'dean_name' => $dean->name,
                'display_status' => $displayStatus,
                'display_score' => $displayScore,
                'display_calibrated_by' => $displayCalibratedBy,
            ],
        ]);
    }

    /**
     * Return a submitted faculty IPCR back for revision with a rejection reason.
     */
    public function returnSubmission(Request $request)
    {
        $request->validate([
            'ipcr_submission_id' => 'required|integer|exists:ipcr_submissions,id',
            'rejection_reason' => 'required|string|max:5000',
        ]);

        $dean = $request->user();
        $submissionId = (int) $request->ipcr_submission_id;
        $rejectionReason = trim((string) $request->input('rejection_reason', ''));

        $submission = IpcrSubmission::where('id', $submissionId)
            ->where('status', 'submitted')
            ->whereNotNull('submitted_at')
            ->whereHas('user', function ($query) use ($dean) {
                $query->where('department_id', $dean->department_id)
                    ->where('id', '!=', $dean->id)
                    ->whereHas('userRoles', function ($roleQuery) {
                        $roleQuery->where('role', 'faculty');
                    });
            })
            ->with('user:id,name')
            ->first();

        if (! $submission) {
            return response()->json([
                'success' => false,
                'message' => 'Only submitted faculty IPCRs in your department can be returned.',
            ], 422);
        }

        $existingCalibration = $this->getDeanCalibration($dean->id, $submissionId);

        $calibration = DeanCalibration::updateOrCreate(
            [
                'dean_id' => $dean->id,
                'ipcr_submission_id' => $submissionId,
            ],
            [
                'calibration_data' => $existingCalibration?->calibration_data ?? [],
                'overall_score' => null,
                'status' => 'returned',
                'dean_comment' => $rejectionReason,
                'dean_suggestion' => null,
            ]
        );

        $submission->update([
            'status' => 'draft',
            'submitted_at' => null,
            'is_active' => false,
        ]);

        ActivityLogService::log(
            'dean_calibration_returned',
            'Returned IPCR submission: ' . $submission->title . ' with reason: ' . $rejectionReason,
            $submission
        );

        AdminNotification::create([
            'title' => 'IPCR Returned for Revision',
            'message' => $dean->name . ' returned your IPCR "' . $submission->title . '". Reason: ' . $rejectionReason,
            'type' => 'warning',
            'audience' => 'all',
            'user_id' => $submission->user_id,
            'is_active' => true,
            'created_by' => $dean->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'IPCR returned successfully. Submission is now unsubmitted.',
            'calibration' => [
                'id' => $calibration->id,
                'status' => $calibration->status,
                'overall_score' => $calibration->overall_score,
                'dean_feedback' => $this->mergeDeanFeedback($calibration),
                'dean_name' => $dean->name,
                'display_status' => 'returned',
                'display_score' => null,
                'display_calibrated_by' => null,
            ],
            'submission' => [
                'id' => $submission->id,
                'status' => $submission->status,
                'submitted_at' => $submission->submitted_at,
            ],
        ]);
    }

    /**
     * Get calibration saved by the current dean for a submission.
     */
    private function getDeanCalibration(int $deanId, int $submissionId): ?DeanCalibration
    {
        return DeanCalibration::with('dean:id,name')
            ->where('dean_id', $deanId)
            ->where('ipcr_submission_id', $submissionId)
            ->first();
    }

    /**
     * Get the latest finalized calibration for a submission across all deans.
     */
    private function getLatestCalibratedCalibration(int $submissionId): ?DeanCalibration
    {
        return DeanCalibration::with('dean:id,name')
            ->where('ipcr_submission_id', $submissionId)
            ->where('status', 'calibrated')
            ->orderByDesc('updated_at')
            ->first();
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

    /**
     * Compute weighted overall score using section averages:
     * Strategic Objectives (35%), Core Functions (55%), Support Function (10%).
     */
    private function computeWeightedOverallScore(string $tableBodyHtml, array $calibrationData): ?float
    {
        if (trim($tableBodyHtml) === '' || empty($calibrationData)) {
            return null;
        }

        $wrappedHtml = '<table><tbody>' . $tableBodyHtml . '</tbody></table>';
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        if (! $loaded) {
            return null;
        }

        $xpath = new DOMXPath($dom);
        $rows = $xpath->query('//tr');

        $sectionScores = [
            'strategic-objectives' => [],
            'core-functions' => [],
            'support-function' => [],
        ];

        $currentSection = null;
        $dataRowIndex = 0;

        foreach ($rows as $row) {
            $class = (string) $row->getAttribute('class');

            if (str_contains($class, 'bg-green-100')) {
                $currentSection = 'strategic-objectives';
                continue;
            }

            if (str_contains($class, 'bg-purple-100')) {
                $currentSection = 'core-functions';
                continue;
            }

            if (str_contains($class, 'bg-orange-100')) {
                $currentSection = 'support-function';
                continue;
            }

            $isSoHeader = str_contains($class, 'bg-blue-100');
            $isGrayHeader = str_contains($class, 'bg-gray-100');
            $hasColspan = $xpath->query('.//td[@colspan]', $row)->length > 0;

            if ($isGrayHeader && $hasColspan) {
                $currentSection = null;
                continue;
            }

            if ($isSoHeader || $isGrayHeader || $hasColspan) {
                continue;
            }

            $rowData = $calibrationData[$dataRowIndex] ?? null;
            $dataRowIndex++;

            if (! $currentSection || ! is_array($rowData)) {
                continue;
            }

            $aValue = $this->extractCalibrationAverage($rowData);
            if ($aValue !== null) {
                $sectionScores[$currentSection][] = $aValue;
            }
        }

        $strategicAvg = $this->average($sectionScores['strategic-objectives']);
        $coreAvg = $this->average($sectionScores['core-functions']);
        $supportAvg = $this->average($sectionScores['support-function']);

        $hasAnyScore = $strategicAvg !== null || $coreAvg !== null || $supportAvg !== null;
        if (! $hasAnyScore) {
            return null;
        }

        $weightedTotal =
            (($strategicAvg ?? 0.0) * 0.35) +
            (($coreAvg ?? 0.0) * 0.55) +
            (($supportAvg ?? 0.0) * 0.10);

        return round($weightedTotal, 2);
    }

    /**
     * Use row A if present, otherwise derive A from Q/E/T.
     */
    private function extractCalibrationAverage(array $rowData): ?float
    {
        $a = isset($rowData['a']) && is_numeric($rowData['a']) ? (float) $rowData['a'] : null;
        if ($a !== null && $a > 0) {
            return $a;
        }

        $q = isset($rowData['q']) && is_numeric($rowData['q']) ? (float) $rowData['q'] : null;
        $e = isset($rowData['e']) && is_numeric($rowData['e']) ? (float) $rowData['e'] : null;
        $t = isset($rowData['t']) && is_numeric($rowData['t']) ? (float) $rowData['t'] : null;

        if ($q !== null && $e !== null && $t !== null && $q > 0 && $e > 0 && $t > 0) {
            return round(($q + $e + $t) / 3, 2);
        }

        return null;
    }

    private function average(array $values): ?float
    {
        if (count($values) === 0) {
            return null;
        }

        return array_sum($values) / count($values);
    }

    /**
     * Combine legacy comment/suggestion values into one feedback string.
     */
    private function joinFeedbackParts(?string $comment, ?string $suggestion): string
    {
        $parts = array_filter([
            trim((string) $comment),
            trim((string) $suggestion),
        ], fn ($value) => $value !== '');

        return implode("\n\n", $parts);
    }

    /**
     * Resolve dean feedback text for API responses.
     */
    private function mergeDeanFeedback(?DeanCalibration $calibration): string
    {
        if (! $calibration) {
            return '';
        }

        return $this->joinFeedbackParts($calibration->dean_comment, $calibration->dean_suggestion);
    }
}
