<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\IpcrTemplate;
use App\Models\IpcrSubmission;
use App\Models\IpcrSavedCopy;
use App\Models\OpcrSavedCopy;
use App\Models\UserPhoto;
use App\Models\SupportingDocument;
use App\Models\AdminNotification;
use App\Models\UpcomingDeadline;
use App\Services\PhotoService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Services\ActivityLogService;
use App\Models\DeanCalibration;

class FacultyDashboardController extends Controller
{
    public function index(): View
    {
        // Get the active submission for the user (fallback to latest submission)
        $activeSubmission = IpcrSubmission::where('user_id', auth()->id())
            ->where('is_active', true)
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at', 'desc')
            ->first();

        if (!$activeSubmission) {
            $activeSubmission = IpcrSubmission::where('user_id', auth()->id())
                ->whereNotNull('submitted_at')
                ->orderBy('submitted_at', 'desc')
                ->first();
        }

        // Initialize counters
        $strategicObjectivesCount = 0;
        $coreFunctionsCount = 0;
        $supportFunctionsCount = 0;
        $strategicObjectivesAccomplished = 0;
        $coreFunctionsAccomplished = 0;
        $supportFunctionsAccomplished = 0;

        // Use JSON data from active submission for SO counts
        if ($activeSubmission && $activeSubmission->so_count_json) {
            \Log::info('Active submission found with SO counts', [
                'submission_id' => $activeSubmission->id,
                'so_count_json' => $activeSubmission->so_count_json,
            ]);

            $counts = $activeSubmission->so_count_json;
            $strategicObjectivesCount = $counts['strategic_objectives'] ?? 0;
            $coreFunctionsCount = $counts['core_functions'] ?? 0;
            $supportFunctionsCount = $counts['support_functions'] ?? 0;
        }

        // Extract per-SO performance data (names, average ratings, accomplishment tracking)
        $soPerformanceData = [];
        if ($activeSubmission && $activeSubmission->table_body_html) {
            try {
                $html = $activeSubmission->table_body_html;
                $dom = new \DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHTML('<meta charset="utf-8"><table><tbody>' . $html . '</tbody></table>');
                libxml_clear_errors();

                $rows = $dom->getElementsByTagName('tr');
                $currentSection = null;
                $currentSoLabel = null;
                $currentSoName = null;
                $currentRatings = [];
                $currentRowCount = 0;
                $currentFilledCount = 0;
                $currentRows = [];
                $pendingSubHeader = null; // Label from a gray sub-header row, stamped onto next data row

                // Also count SOs from HTML if so_count_json was empty
                $countFromHtml = !($activeSubmission->so_count_json);

                $sectionMap = [
                    'green' => 'strategic_objectives',
                    'purple' => 'core_functions',
                    'orange' => 'support_functions',
                ];

                $saveSo = function () use (&$soPerformanceData, &$currentSoLabel, &$currentSoName, &$currentRatings, &$currentSection, &$currentRowCount, &$currentFilledCount, &$currentRows, &$strategicObjectivesAccomplished, &$coreFunctionsAccomplished, &$supportFunctionsAccomplished) {
                    if (!$currentSoLabel) return;

                    $avg = count($currentRatings) > 0
                        ? round(array_sum($currentRatings) / count($currentRatings), 2)
                        : 0;

                    // An SO is accomplished only when ALL its data rows have actual accomplishment filled
                    $isAccomplished = ($currentRowCount > 0 && $currentFilledCount === $currentRowCount);

                    $soPerformanceData[] = [
                        'label'     => $currentSoLabel,
                        'name'      => $currentSoName,
                        'average'   => $avg,
                        'section'   => $currentSection,
                        'rows'      => $currentRows,
                        'documents' => [],
                    ];

                    if ($isAccomplished) {
                        if ($currentSection === 'strategic_objectives') $strategicObjectivesAccomplished++;
                        elseif ($currentSection === 'core_functions') $coreFunctionsAccomplished++;
                        elseif ($currentSection === 'support_functions') $supportFunctionsAccomplished++;
                    }

                    $currentSoLabel = null;
                    $currentSoName = null;
                    $currentRatings = [];
                    $currentRowCount = 0;
                    $currentFilledCount = 0;
                    $currentRows = [];
                };

                foreach ($rows as $row) {
                    $classAttr = $row->attributes->getNamedItem('class');
                    $className = $classAttr ? $classAttr->nodeValue : '';

                    // Detect section header
                    $isSectionHeader = false;
                    foreach ($sectionMap as $color => $sectionKey) {
                        if (str_contains($className, "bg-{$color}-100")) {
                            $saveSo();
                            $currentSection = $sectionKey;
                            $isSectionHeader = true;
                            break;
                        }
                    }
                    if ($isSectionHeader) continue;

                    // Gray header (sub-section like "Preparation and Submission of:")
                    // Store its label to be stamped onto the next data row as a visual divider
                    if (str_contains($className, 'bg-gray-100')) {
                        $grayInputs = $row->getElementsByTagName('input');
                        $grayLabel = '';
                        foreach ($grayInputs as $gi) {
                            if ($gi->getAttribute('type') === 'text') {
                                $grayLabel = trim($gi->getAttribute('value'));
                                break;
                            }
                        }
                        if (!$grayLabel) {
                            $grayLabel = trim($row->textContent);
                        }
                        $pendingSubHeader = $grayLabel ?: null;
                        continue;
                    }

                    $isSoHeader = str_contains($className, 'bg-blue-100');

                    if ($isSoHeader) {
                        $saveSo();

                        // Count SOs from HTML fallback
                        if ($countFromHtml && $currentSection) {
                            if ($currentSection === 'strategic_objectives') $strategicObjectivesCount++;
                            elseif ($currentSection === 'core_functions') $coreFunctionsCount++;
                            elseif ($currentSection === 'support_functions') $supportFunctionsCount++;
                        }

                        // Extract SO label and description
                        $spans = $row->getElementsByTagName('span');
                        $inputs = $row->getElementsByTagName('input');
                        $soLabel = '';
                        $soDesc = '';

                        foreach ($spans as $span) {
                            if (str_contains($span->getAttribute('class') ?: '', 'font-semibold')) {
                                $soLabel = rtrim(trim($span->textContent), ':');
                            }
                        }

                        foreach ($inputs as $input) {
                            if ($input->getAttribute('type') === 'text') {
                                $soDesc = trim($input->getAttribute('value'));
                            }
                        }

                        $currentSoLabel = $soLabel ?: 'SO';
                        $currentSoName = $soDesc
                            ? strtoupper("$currentSoLabel. $soDesc")
                            : strtoupper($currentSoLabel);
                        continue;
                    }

                    // Data row — extract all columns
                    if ($currentSoLabel) {
                        $cells = $row->getElementsByTagName('td');
                        if ($cells->length >= 3) {
                            $currentRowCount++;

                            $getCellText = function (\DOMElement $cell): string {
                                $tas = $cell->getElementsByTagName('textarea');
                                if ($tas->length > 0) return trim($tas->item(0)->textContent);
                                $ins = $cell->getElementsByTagName('input');
                                if ($ins->length > 0) return trim($ins->item(0)->getAttribute('value'));
                                return trim($cell->textContent);
                            };

                            $mfo            = $cells->length > 0 ? $getCellText($cells->item(0)) : '';
                            $successInd     = $cells->length > 1 ? $getCellText($cells->item(1)) : '';
                            $accomplishment = $cells->length > 2 ? $getCellText($cells->item(2)) : '';
                            $q = $e = $t = $a = '';

                            if ($cells->length >= 7) {
                                $q = $getCellText($cells->item(3));
                                $e = $getCellText($cells->item(4));
                                $t = $getCellText($cells->item(5));
                                $a = $getCellText($cells->item(6));
                                if (is_numeric($a) && (float) $a > 0) {
                                    $currentRatings[] = (float) $a;
                                }
                            }

                            if ($accomplishment !== '') $currentFilledCount++;

                            $currentRows[] = [
                                'mfo'               => $mfo,
                                'success_indicator' => $successInd,
                                'accomplishment'    => $accomplishment,
                                'q' => $q,
                                'e' => $e,
                                't' => $t,
                                'a' => $a,
                                'sub_header'        => $pendingSubHeader, // null for most rows
                            ];
                            $pendingSubHeader = null; // Only stamp on the first row after the header
                        }
                    }
                }

                // Save the last SO
                $saveSo();

                // Recount from extracted data (overrides so_count_json which may not include gray sub-sections)
                $strategicObjectivesCount = 0;
                $coreFunctionsCount = 0;
                $supportFunctionsCount = 0;
                $strategicObjectivesAccomplished = 0;
                $coreFunctionsAccomplished = 0;
                $supportFunctionsAccomplished = 0;

                foreach ($soPerformanceData as $soEntry) {
                    $sec = $soEntry['section'] ?? null;
                    if ($sec === 'strategic_objectives') {
                        $strategicObjectivesCount++;
                        if (!empty($soEntry['rows'])) {
                            $allFilled = true;
                            foreach ($soEntry['rows'] as $r) {
                                if (trim($r['accomplishment'] ?? '') === '') { $allFilled = false; break; }
                            }
                            if ($allFilled) $strategicObjectivesAccomplished++;
                        }
                    } elseif ($sec === 'core_functions') {
                        $coreFunctionsCount++;
                        if (!empty($soEntry['rows'])) {
                            $allFilled = true;
                            foreach ($soEntry['rows'] as $r) {
                                if (trim($r['accomplishment'] ?? '') === '') { $allFilled = false; break; }
                            }
                            if ($allFilled) $coreFunctionsAccomplished++;
                        }
                    } elseif ($sec === 'support_functions') {
                        $supportFunctionsCount++;
                        if (!empty($soEntry['rows'])) {
                            $allFilled = true;
                            foreach ($soEntry['rows'] as $r) {
                                if (trim($r['accomplishment'] ?? '') === '') { $allFilled = false; break; }
                            }
                            if ($allFilled) $supportFunctionsAccomplished++;
                        }
                    }
                }

                // Attach supporting documents per SO label
                // Query across both template and submission types (same logic as SupportingDocumentController::index)
                // so documents uploaded on a draft or template are visible on the active submission dashboard
                if (!empty($soPerformanceData)) {
                    $docsByLabel = SupportingDocument::where('user_id', auth()->id())
                        ->whereIn('documentable_type', ['ipcr_template', 'ipcr_submission'])
                        ->orderBy('created_at', 'desc')
                        ->get(['id', 'so_label', 'original_name', 'path', 'mime_type', 'file_size'])
                        ->unique('path') // Deduplicate same Cloudinary file copied between template/submission
                        ->groupBy('so_label');

                    foreach ($soPerformanceData as &$soEntry) {
                        $lbl = $soEntry['label'];
                        $soEntry['documents'] = $docsByLabel->has($lbl)
                            ? $docsByLabel[$lbl]->map(fn ($d) => [
                                'id'             => $d->id,
                                'original_name'  => $d->original_name,
                                'path'           => $d->path,
                                'mime_type'      => $d->mime_type,
                                'file_size_human'=> $d->file_size_human,
                                'download_url'   => route('faculty.supporting-documents.download', $d->id),
                            ])->values()->toArray()
                            : [];
                    }
                    unset($soEntry);
                }

                // Load calibration actual ratings from dean calibrations
                if ($activeSubmission && !empty($soPerformanceData)) {
                    $calibration = DeanCalibration::where('ipcr_submission_id', $activeSubmission->id)
                        ->where('status', 'calibrated')
                        ->latest()
                        ->first();

                    if ($calibration && $calibration->calibration_data) {
                        $calData = $calibration->calibration_data;
                        $calIndex = 0;
                        foreach ($soPerformanceData as &$soEntry) {
                            $soCalRatings = [];
                            foreach ($soEntry['rows'] as $rowIdx => $row) {
                                if (isset($calData[$calIndex])) {
                                    $cd = $calData[$calIndex];
                                    $a = isset($cd['a']) ? (float) $cd['a'] : 0;
                                    if ($a > 0) $soCalRatings[] = $a;
                                }
                                $calIndex++;
                            }
                            $soEntry['actual_rating'] = count($soCalRatings) > 0
                                ? round(array_sum($soCalRatings) / count($soCalRatings), 2)
                                : 0;
                        }
                        unset($soEntry);
                    } else {
                        foreach ($soPerformanceData as &$soEntry) {
                            $soEntry['actual_rating'] = 0;
                        }
                        unset($soEntry);
                    }
                }

            } catch (\Throwable $e) {
                \Log::error('Failed to extract SO performance data', [
                    'submission_id' => $activeSubmission->id ?? null,
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            \Log::info('No active submission or no table_body_html', [
                'has_submission' => $activeSubmission ? true : false,
                'submission_id' => $activeSubmission->id ?? null,
                'so_count_json' => $activeSubmission->so_count_json ?? null,
            ]);
        }

        // Format the text display (e.g., "0/3" where 0 is accomplished and 3 is total SOs)
        // Show "N/A" when total is 0, otherwise show "accomplished/total"
        $strategicObjectivesText = $strategicObjectivesCount > 0 
            ? "$strategicObjectivesAccomplished/$strategicObjectivesCount" 
            : "N/A";
        $coreFunctionsText = $coreFunctionsCount > 0 
            ? "$coreFunctionsAccomplished/$coreFunctionsCount" 
            : "N/A";
        $supportFunctionsText = $supportFunctionsCount > 0 
            ? "$supportFunctionsAccomplished/$supportFunctionsCount" 
            : "N/A";

        // Calculate percentages based on accomplished/total
        $strategicObjectivesPercent = $strategicObjectivesCount > 0 
            ? round(($strategicObjectivesAccomplished / $strategicObjectivesCount) * 100) . "%" 
            : "0%";
        $coreFunctionsPercent = $coreFunctionsCount > 0 
            ? round(($coreFunctionsAccomplished / $coreFunctionsCount) * 100) . "%" 
            : "0%";
        $supportFunctionsPercent = $supportFunctionsCount > 0 
            ? round(($supportFunctionsAccomplished / $supportFunctionsCount) * 100) . "%" 
            : "0%";

        // Dummy IPCR Progress values
        $ipcrAccomplishedText = "0/0";
        $ipcrPercentageValue = 0;
        $ipcrPercentageText = "0%";

        // Fetch notifications and deadlines from database
        $userRole = auth()->user()->getPrimaryRole() ?? 'faculty';
        $notifications = AdminNotification::active()
            ->forAudience($userRole)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $readNotifIds = DB::table('notification_reads')
            ->where('user_id', auth()->id())
            ->whereIn('notification_id', $notifications->pluck('id'))
            ->pluck('notification_id')
            ->toArray();
        $unreadCount = $notifications->whereNotIn('id', $readNotifIds)->count();

        $deadlines = UpcomingDeadline::active()
            ->upcoming()
            ->forAudience($userRole)
            ->orderBy('deadline_date')
            ->limit(5)
            ->get();

        $departmentName = auth()->user()->department?->name ?? 'Your Department';
        $departmentCode = auth()->user()->department?->code ?? '';

        // Load calibration for the active submission (if a dean has calibrated it)
        $returnedCalibration = null;
        if ($activeSubmission) {
            $returnedCalibration = DeanCalibration::where('ipcr_submission_id', $activeSubmission->id)
                ->where('status', 'calibrated')
                ->with('dean:id,name')
                ->latest()
                ->first();
        }

        return view('dashboard.faculty.index', compact(
            'strategicObjectivesText',
            'strategicObjectivesPercent',
            'coreFunctionsText',
            'coreFunctionsPercent',
            'supportFunctionsText',
            'supportFunctionsPercent',
            'ipcrAccomplishedText',
            'ipcrPercentageValue',
            'ipcrPercentageText',
            'soPerformanceData',
            'notifications',
            'readNotifIds',
            'unreadCount',
            'deadlines',
            'departmentName',
            'departmentCode',
            'returnedCalibration',
            'activeSubmission'
        ));
    }

    public function myIpcrs(): View
    {
        $templates = IpcrTemplate::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        $submissions = IpcrSubmission::where('user_id', auth()->id())
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at', 'desc')
            ->get();

        $opcrSubmissions = \App\Models\OpcrSubmission::where('user_id', auth()->id())
            ->whereNotNull('submitted_at')
            ->orderBy('submitted_at', 'desc')
            ->get();

        $savedIpcrs = IpcrSavedCopy::where('user_id', auth()->id())
            ->orderBy('saved_at', 'desc')
            ->get();

        $savedOpcrs = OpcrSavedCopy::where('user_id', auth()->id())
            ->orderBy('saved_at', 'desc')
            ->get();

        $departmentName = auth()->user()->department?->name ?? 'Your Department';
        $departmentCode = auth()->user()->department?->code ?? '';

        $userRole = auth()->user()->getPrimaryRole() ?? 'faculty';
        $notifications = AdminNotification::active()
            ->forAudience($userRole)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $readNotifIds = DB::table('notification_reads')
            ->where('user_id', auth()->id())
            ->whereIn('notification_id', $notifications->pluck('id'))
            ->pluck('notification_id')
            ->toArray();
        $unreadCount = $notifications->whereNotIn('id', $readNotifIds)->count();
            
        return view('dashboard.faculty.my-ipcrs', compact(
            'templates', 'submissions', 'opcrSubmissions',
            'savedIpcrs', 'savedOpcrs',
            'departmentName', 'departmentCode', 'notifications', 'readNotifIds', 'unreadCount'
        ));
    }

    public function profile(): View
    {
        $departments = \App\Models\Department::all();
        $designations = \App\Models\Designation::all();
        $user = auth()->user();
        $profileCompleteness = $user->getProfileCompleteness();
        $completenessColor = $user->getCompletenessColor();
        $photoCount = $user->photos()->count();
        
        $userRole = auth()->user()->getPrimaryRole() ?? 'faculty';
        $notifications = AdminNotification::active()
            ->forAudience($userRole)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $readNotifIds = DB::table('notification_reads')
            ->where('user_id', auth()->id())
            ->whereIn('notification_id', $notifications->pluck('id'))
            ->pluck('notification_id')
            ->toArray();
        $unreadCount = $notifications->whereNotIn('id', $readNotifIds)->count();

        return view('dashboard.faculty.profile', compact(
            'departments', 'designations', 'profileCompleteness', 'completenessColor', 'photoCount',
            'notifications', 'readNotifIds', 'unreadCount'
        ));
    }

    public function markAllRead(): \Illuminate\Http\JsonResponse
    {
        $userRole = auth()->user()->getPrimaryRole() ?? 'faculty';
        $notifIds = AdminNotification::active()
            ->forAudience($userRole)
            ->pluck('id');

        $userId = auth()->id();
        foreach ($notifIds as $notifId) {
            DB::table('notification_reads')->insertOrIgnore([
                'user_id' => $userId,
                'notification_id' => $notifId,
                'read_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = auth()->user();

        // Check if current password is correct
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => [
                    'current_password' => ['The current password is incorrect.']
                ]
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        ActivityLogService::log('password_changed', 'Changed own password', $user);

        return response()->json([
            'message' => 'Password updated successfully!'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'designation_id' => ['nullable', 'exists:designations,id'],
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        if ($emailChanged) {
            $validated['email_verified_at'] = null;
        }

        $user->fill($validated);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        ActivityLogService::log('profile_updated', 'Updated own profile', $user);

        if ($emailChanged) {
            DB::table('email_verifications')
                ->where('user_id', $user->id)
                ->delete();
        }

        return response()->json([
            'message' => $emailChanged
                ? 'Profile updated. Please verify your new email address.'
                : 'Profile updated successfully!',
            'email_changed' => $emailChanged,
            'user' => $user
        ]);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max
        ]);

        $user = auth()->user();
        $photoService = app(PhotoService::class);

        try {
            $photoService->uploadPhoto($request->file('photo'), $user);

            ActivityLogService::log('photo_uploaded', 'Uploaded a profile photo', $user);

            return response()->json([
                'success' => true,
                'message' => 'Photo uploaded successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload photo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getPhotos()
    {
        $user = auth()->user();
        $photos = $user->photos()->orderBy('created_at', 'desc')->get()->map(function ($photo) {
            return [
                'id' => $photo->id,
                'url' => $photo->photo_url,
                'is_profile' => $photo->is_profile_photo
            ];
        });

        return response()->json([
            'photos' => $photos
        ]);
    }

    public function setProfilePhoto(Request $request)
    {
        $request->validate([
            'photo_id' => 'required|integer'
        ]);

        $user = auth()->user();

        // Verify the photo belongs to the authenticated user (prevent IDOR)
        $photo = UserPhoto::where('id', $request->photo_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$photo) {
            return response()->json([
                'success' => false,
                'message' => 'Photo not found or does not belong to you.'
            ], 403);
        }

        $photoService = app(PhotoService::class);

        try {
            $photoService->setAsProfilePhoto($photo);

            return response()->json([
                'success' => true,
                'message' => 'Profile photo updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set profile photo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deletePhoto($id)
    {
        $user = auth()->user();
        $photo = UserPhoto::where('user_id', $user->id)->where('id', $id)->firstOrFail();

        $photoService = app(PhotoService::class);

        try {
            $photoService->deletePhoto($photo);

            ActivityLogService::log('photo_deleted', 'Deleted a profile photo', $user);

            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete photo: ' . $e->getMessage()
            ], 500);
        }
    }
}