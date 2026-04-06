<?php

namespace App\Http\Controllers\Faculty;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\DeanCalibration;
use App\Models\DeanDirectorSummaryOverride;
use App\Models\Department;
use App\Models\Designation;
use App\Models\IpcrSubmission;
use App\Models\OpcrSubmission;
use App\Models\Role;
use App\Models\SupportingDocument;
use App\Models\UpcomingDeadline;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\DeanDirectorSummaryExportService;
use App\Services\FacultySummaryExportService;
use App\Services\IpcrExportService;
use App\Services\StaffSummaryExportService;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SummaryReportController extends Controller
{
    /**
     * Display the summary reports page with faculty data.
     */
    public function index(Request $request)
    {
        $activeDepartment = $request->query('department', 'all');
        $requestedCategory = $request->query('category', 'faculty');
        $activeCategory = in_array($requestedCategory, ['faculty', 'staff', 'dean-director', 'dean-ipcrs', 'user-management', 'notifications-deadlines'], true) ? $requestedCategory : 'faculty';
        $regularStaffStatusOptions = ['Permanent', 'Casual', 'Contractual'];
        $emergencyStaffStatus = 'Emergency Laborer';
        $partTimeStatus = 'Part Time';

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

        // Staff and dean-focused views are always campus-wide.
        if (in_array($activeCategory, ['staff', 'dean-director', 'dean-ipcrs', 'user-management', 'notifications-deadlines'], true)) {
            $activeDepartment = 'all';
        }

        // Get all departments for the filter tabs
        $departments = Department::orderBy('code')->get();

        $users = collect();
        $emergencyUsers = collect();
        $partTimeUsers = collect();
        $deanDirectorRows = collect();
        $deanIpcrRows = collect();
        $deanIpcrFilters = [];
        $deanIpcrDeans = collect();
        $deanIpcrDepartments = collect();
        $deanIpcrSchoolYears = collect();
        $deanIpcrSemesters = collect();
        $userManagementUsers = null;
        $userManagementSearch = '';
        $userManagementDepartment = '';
        $userManagementTotalUsers = 0;
        $userManagementActiveUsers = 0;
        $userManagementInactiveUsers = 0;
        $userManagementRoles = [];
        $userManagementDesignations = collect();
        $summaryAdminNotifications = collect();
        $summaryUpcomingDeadlines = collect();

        if ($activeCategory === 'dean-ipcrs') {
            [
                $deanIpcrRows,
                $deanIpcrFilters,
                $deanIpcrDeans,
                $deanIpcrDepartments,
                $deanIpcrSchoolYears,
                $deanIpcrSemesters,
            ] = $this->buildDeanIpcrDataset($request);

            return view('dashboard.faculty.summary-reports', compact(
                'users',
                'emergencyUsers',
                'partTimeUsers',
                'deanDirectorRows',
                'deanIpcrRows',
                'deanIpcrFilters',
                'deanIpcrDeans',
                'deanIpcrDepartments',
                'deanIpcrSchoolYears',
                'deanIpcrSemesters',
                'departments',
                'activeDepartment',
                'activeCategory',
                'notifications',
                'readNotifIds',
                'unreadCount'
            ));
        }

        if ($activeCategory === 'dean-director') {
            $deanDirectorRows = $this->buildDeanDirectorRows();

            return view('dashboard.faculty.summary-reports', compact(
                'users',
                'emergencyUsers',
                'partTimeUsers',
                'deanDirectorRows',
                'deanIpcrRows',
                'deanIpcrFilters',
                'deanIpcrDeans',
                'deanIpcrDepartments',
                'deanIpcrSchoolYears',
                'deanIpcrSemesters',
                'departments',
                'activeDepartment',
                'activeCategory',
                'notifications',
                'readNotifIds',
                'unreadCount'
            ));
        }

        if ($activeCategory === 'user-management') {
            $userManagementSearch = trim((string) $request->query('search', ''));
            $userManagementDepartment = (string) $request->query('user_department', '');

            $userQuery = User::with('department', 'designation', 'userRoles');

            if ($userManagementSearch !== '') {
                $search = $userManagementSearch;
                $userQuery->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('employee_id', 'like', "%{$search}%");
                });
            }

            if ($userManagementDepartment !== '') {
                $userQuery->where('department_id', (int) $userManagementDepartment);
            }

            $userManagementUsers = $userQuery
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString();

            $userManagementTotalUsers = User::count();
            $userManagementActiveUsers = User::where('is_active', true)->count();
            $userManagementInactiveUsers = User::where('is_active', false)->count();
            $userManagementRoles = Role::getNames();
            $userManagementDesignations = Designation::orderBy('title')->get();

            return view('dashboard.faculty.summary-reports', compact(
                'users',
                'emergencyUsers',
                'partTimeUsers',
                'deanDirectorRows',
                'deanIpcrRows',
                'deanIpcrFilters',
                'deanIpcrDeans',
                'deanIpcrDepartments',
                'deanIpcrSchoolYears',
                'deanIpcrSemesters',
                'departments',
                'activeDepartment',
                'activeCategory',
                'notifications',
                'readNotifIds',
                'unreadCount',
                'userManagementUsers',
                'userManagementSearch',
                'userManagementDepartment',
                'userManagementTotalUsers',
                'userManagementActiveUsers',
                'userManagementInactiveUsers',
                'userManagementRoles',
                'userManagementDesignations'
            ));
        }

        if ($activeCategory === 'notifications-deadlines') {
            $summaryAdminNotifications = AdminNotification::whereNull('user_id')
                ->orderByDesc('created_at')
                ->get();

            $summaryUpcomingDeadlines = UpcomingDeadline::orderBy('deadline_date')
                ->get();

            return view('dashboard.faculty.summary-reports', compact(
                'users',
                'emergencyUsers',
                'partTimeUsers',
                'deanDirectorRows',
                'deanIpcrRows',
                'deanIpcrFilters',
                'deanIpcrDeans',
                'deanIpcrDepartments',
                'deanIpcrSchoolYears',
                'deanIpcrSemesters',
                'departments',
                'activeDepartment',
                'activeCategory',
                'notifications',
                'readNotifIds',
                'unreadCount',
                'summaryAdminNotifications',
                'summaryUpcomingDeadlines'
            ));
        }

        // Query users: exclude dean, admin, hr, director roles
        $excludedRoles = ['dean', 'admin', 'hr', 'director'];

        $baseQuery = User::with(['department', 'designation', 'userRoles'])
            ->where('is_active', true)
            ->whereHas('userRoles', function ($q) {
                $q->where('role', 'faculty');
            })
            ->whereDoesntHave('userRoles', function ($q) use ($excludedRoles) {
                $q->whereIn('role', $excludedRoles);
            });

        // Filter by department if not "all"
        if ($activeDepartment !== 'all') {
            $baseQuery->whereHas('department', function ($q) use ($activeDepartment) {
                $q->where('code', $activeDepartment);
            });
        }

        if ($activeCategory === 'staff') {
            $users = (clone $baseQuery)
                ->whereIn('employment_status', $regularStaffStatusOptions)
                ->orderBy('name')
                ->get();

            $emergencyUsers = (clone $baseQuery)
                ->where('employment_status', $emergencyStaffStatus)
                ->orderBy('name')
                ->get();

            $partTimeUsers = collect();
        } else {
            $users = (clone $baseQuery)
                ->whereNull('employment_status')
                ->orderBy('name')
                ->get();

            $partTimeUsers = (clone $baseQuery)
                ->where('employment_status', $partTimeStatus)
                ->orderBy('name')
                ->get();

            $emergencyUsers = collect();
        }

        $this->appendRatings($users);
        $this->appendRatings($emergencyUsers);
        $this->appendRatings($partTimeUsers);

        return view('dashboard.faculty.summary-reports', compact(
            'users',
            'emergencyUsers',
            'partTimeUsers',
            'deanDirectorRows',
            'deanIpcrRows',
            'deanIpcrFilters',
            'deanIpcrDeans',
            'deanIpcrDepartments',
            'deanIpcrSchoolYears',
            'deanIpcrSemesters',
            'departments',
            'activeDepartment',
            'activeCategory',
            'notifications',
            'readNotifIds',
            'unreadCount'
        ));
    }

    /**
     * Build dean IPCR calibrated-submission dataset with filter options.
     */
    private function buildDeanIpcrDataset(Request $request): array
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'dean_id' => (string) $request->query('dean_id', 'all'),
            'department_id' => (string) $request->query('dean_department_id', 'all'),
            'school_year' => (string) $request->query('school_year', 'all'),
            'semester' => (string) $request->query('semester', 'all'),
            'submitted_from' => (string) $request->query('submitted_from', ''),
            'submitted_to' => (string) $request->query('submitted_to', ''),
        ];

        $deanUsers = User::with('department')
            ->where('is_active', true)
            ->whereHas('userRoles', function ($query) {
                $query->where('role', 'dean');
            })
            ->orderBy('name')
            ->get();

        $deanDepartments = Department::whereIn('id', $deanUsers->pluck('department_id')->filter()->unique())
            ->orderBy('code')
            ->get();

        $baseQuery = IpcrSubmission::with([
            'user:id,name,employee_id,department_id',
            'user.department:id,name,code',
            'deanCalibrations' => function ($query) {
                $query->where('status', 'calibrated')
                    ->with('dean:id,name')
                    ->orderByDesc('updated_at');
            },
        ])
            ->whereHas('user.userRoles', function ($query) {
                $query->where('role', 'dean');
            })
            ->whereHas('deanCalibrations', function ($query) {
                $query->where('status', 'calibrated');
            });

        if ($filters['search'] !== '') {
            $search = $filters['search'];
            $baseQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('employee_id', 'like', "%{$search}%");
                    });
            });
        }

        if ($filters['dean_id'] !== 'all') {
            $baseQuery->where('user_id', (int) $filters['dean_id']);
        }

        if ($filters['department_id'] !== 'all') {
            $baseQuery->whereHas('user', function ($query) use ($filters) {
                $query->where('department_id', (int) $filters['department_id']);
            });
        }

        if ($filters['school_year'] !== 'all') {
            $baseQuery->where('school_year', $filters['school_year']);
        }

        if ($filters['semester'] !== 'all') {
            $baseQuery->where('semester', $filters['semester']);
        }

        if ($filters['submitted_from'] !== '') {
            $baseQuery->whereDate('submitted_at', '>=', $filters['submitted_from']);
        }

        if ($filters['submitted_to'] !== '') {
            $baseQuery->whereDate('submitted_at', '<=', $filters['submitted_to']);
        }

        $rows = $baseQuery
            ->orderByDesc('submitted_at')
            ->get()
            ->map(function (IpcrSubmission $submission) {
                $latestCalibrated = $submission->deanCalibrations->first();

                return [
                    'id' => $submission->id,
                    'title' => $submission->title,
                    'school_year' => $submission->school_year,
                    'semester' => $submission->semester,
                    'submitted_at' => $submission->submitted_at,
                    'dean_name' => $submission->user?->name ?? 'Unknown',
                    'employee_id' => $submission->user?->employee_id ?? 'N/A',
                    'department_code' => $submission->user?->department?->code ?? 'N/A',
                    'calibrated_score' => $latestCalibrated?->overall_score,
                    'calibrated_by' => $latestCalibrated?->dean?->name,
                    'calibrated_at' => $latestCalibrated?->updated_at,
                ];
            })
            ->values();

        $schoolYears = IpcrSubmission::whereHas('user.userRoles', function ($query) {
            $query->where('role', 'dean');
        })
            ->whereHas('deanCalibrations', function ($query) {
                $query->where('status', 'calibrated');
            })
            ->whereNotNull('school_year')
            ->distinct()
            ->orderByDesc('school_year')
            ->pluck('school_year');

        $semesters = IpcrSubmission::whereHas('user.userRoles', function ($query) {
            $query->where('role', 'dean');
        })
            ->whereHas('deanCalibrations', function ($query) {
                $query->where('status', 'calibrated');
            })
            ->whereNotNull('semester')
            ->distinct()
            ->orderBy('semester')
            ->pluck('semester');

        return [
            $rows,
            $filters,
            $deanUsers,
            $deanDepartments,
            $schoolYears,
            $semesters,
        ];
    }

    /**
     * Build the campus-wide summary rows for director and all deans.
     */
    private function buildDeanDirectorRows(): Collection
    {
        $leaders = User::with(['department', 'designation', 'userRoles'])
            ->where('is_active', true)
            ->whereHas('userRoles', function ($q) {
                $q->whereIn('role', ['dean', 'director']);
            })
            ->get();

        $overrides = DeanDirectorSummaryOverride::whereIn('user_id', $leaders->pluck('id'))
            ->get()
            ->keyBy('user_id');

        return $leaders
            ->map(function (User $user) use ($overrides) {
                $submission = $this->resolveLatestPerformanceSubmission($user);
                $calculatedScores = $this->computeWeightedSectionScores($submission?->table_body_html ?? '');
                $scores = $this->applyDeanDirectorOverride($calculatedScores, $overrides->get($user->id));

                $isDirector = $user->hasRole('director');
                $departmentName = $user->department?->name ?? 'Unassigned Department';
                $departmentCode = $user->department?->code;
                $roleLabel = $isDirector
                    ? 'Campus Director'
                    : 'Dean, '.$departmentName;
                $roleLabelShort = $isDirector
                    ? 'Campus Director'
                    : 'Dean, '.($departmentCode ?: $departmentName);

                return [
                    'user_id' => $user->id,
                    'employee_label' => $roleLabel,
                    'employee_label_short' => $roleLabelShort,
                    'employee_name' => $user->name,
                    'employee_id' => $user->employee_id,
                    'department_code' => $departmentCode,
                    'is_director' => $isDirector,
                    'strategic_score' => $scores['strategic_score'],
                    'core_score' => $scores['core_score'],
                    'support_score' => $scores['support_score'],
                    'total_score' => $scores['total_score'],
                    'adjectival_rating' => $scores['adjectival_rating'],
                    'is_manual' => $scores['is_manual'],
                ];
            })
            ->sortBy([
                fn (array $row) => $row['is_director'] ? 0 : 1,
                fn (array $row) => $row['department_code'] ?? 'ZZZ',
                fn (array $row) => $row['employee_name'],
            ])
            ->values();
    }

    /**
     * Save manual 35/55/10 weighted percentage-point values for a dean/director row.
     */
    public function updateDeanDirectorScores(Request $request, User $user)
    {
        if (! $user->hasAnyRole(['dean', 'director'])) {
            abort(404);
        }

        if ($request->boolean('clear_scores')) {
            DeanDirectorSummaryOverride::where('user_id', $user->id)->delete();

            return redirect()
                ->route('faculty.summary-reports', ['category' => 'dean-director', 'department' => 'all'])
                ->with('success', 'Dean and director scores cleared successfully.');
        }

        $validated = $request->validate([
            'strategic_score' => ['nullable', 'numeric', 'min:0', 'max:35'],
            'core_score' => ['nullable', 'numeric', 'min:0', 'max:55'],
            'support_score' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ]);

        $normalized = collect(['strategic_score', 'core_score', 'support_score'])
            ->mapWithKeys(function (string $field) use ($validated) {
                $value = $validated[$field] ?? null;

                if ($value === null || $value === '') {
                    return [$field => null];
                }

                return [$field => round((float) $value, 2)];
            })
            ->toArray();

        $hasAnyValue = collect($normalized)->contains(fn ($value) => $value !== null);

        if (! $hasAnyValue) {
            DeanDirectorSummaryOverride::where('user_id', $user->id)->delete();
        } else {
            DeanDirectorSummaryOverride::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'strategic_score' => $normalized['strategic_score'],
                    'core_score' => $normalized['core_score'],
                    'support_score' => $normalized['support_score'],
                    'updated_by' => $request->user()->id,
                ]
            );
        }

        return redirect()
            ->route('faculty.summary-reports', ['category' => 'dean-director', 'department' => 'all'])
            ->with('success', 'Dean and director scores updated successfully.');
    }

    /**
     * Export the dean/director summary as XLSX.
     */
    public function exportDeanDirectorXlsx(Request $request, DeanDirectorSummaryExportService $exportService)
    {
        try {
            $rows = $this->buildDeanDirectorRows();

            $preparedByUser = $request->user()->loadMissing('designation');
            $director = User::with(['designation', 'userRoles'])
                ->where('is_active', true)
                ->whereHas('userRoles', function ($q) {
                    $q->where('role', 'director');
                })
                ->orderBy('name')
                ->first();

            $preparedBy = [
                'name' => $preparedByUser?->name,
                'position' => 'Campus HRMO',
            ];

            $notedBy = [
                'name' => $director?->name ?? '',
                'position' => 'Campus Director',
            ];

            $filePath = $exportService->export($rows, $preparedBy, $notedBy);

            ActivityLogService::log(
                'dean_director_summary_exported',
                'Exported Dean and Director summary report to Excel.',
                null,
                ['record_count' => $rows->count()]
            );

            $downloadName = 'Dean_Director_Summary_'.now()->format('Ymd_His').'.xlsx';

            return response()->download($filePath, $downloadName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            \Log::error('Dean/Director summary export error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('faculty.summary-reports', ['category' => 'dean-director', 'department' => 'all'])
                ->withErrors(['export' => 'Failed to export Dean and Director summary report.']);
        }
    }

    /**
     * Export faculty, staff, and dean/director summaries in a single multi-sheet XLSX.
     */
    public function exportAllXlsx(
        Request $request,
        FacultySummaryExportService $facultyExportService,
        StaffSummaryExportService $staffExportService,
        DeanDirectorSummaryExportService $deanDirectorExportService
    ) {
        $sourceFiles = [];

        try {
            $excludedRoles = ['dean', 'admin', 'hr', 'director'];
            $regularStaffStatusOptions = ['Permanent', 'Casual', 'Contractual'];
            $emergencyStaffStatus = 'Emergency Laborer';

            $baseQuery = User::with(['department', 'designation', 'userRoles'])
                ->where('is_active', true)
                ->whereHas('userRoles', function ($q) {
                    $q->where('role', 'faculty');
                })
                ->whereDoesntHave('userRoles', function ($q) use ($excludedRoles) {
                    $q->whereIn('role', $excludedRoles);
                });

            // Faculty dataset (all departments)
            $departments = Department::orderBy('code')->get();
            $facultyUsers = (clone $baseQuery)
                ->whereNull('employment_status')
                ->orderBy('name')
                ->get();

            $facultyPartTimeUsers = (clone $baseQuery)
                ->where('employment_status', 'Part Time')
                ->orderBy('name')
                ->get();

            $this->appendRatings($facultyUsers);
            $this->appendRatings($facultyPartTimeUsers);

            $departmentRows = $departments
                ->map(function ($department) use ($facultyUsers, $facultyPartTimeUsers) {
                    return [
                        'department' => $department,
                        'permanent' => $facultyUsers->where('department_id', $department->id)->values(),
                        'part_time' => $facultyPartTimeUsers->where('department_id', $department->id)->values(),
                    ];
                })
                ->filter(function (array $row) {
                    return $row['permanent']->isNotEmpty() || $row['part_time']->isNotEmpty();
                })
                ->values();

            $preparedByUser = $request->user();
            $directorForFacultyStaff = User::query()
                ->where('is_active', true)
                ->whereHas('userRoles', function ($query) {
                    $query->where('role', 'director');
                })
                ->orderByDesc('updated_at')
                ->orderBy('name')
                ->first();

            $facultyPreparedBy = [
                'name' => $preparedByUser?->name ?? '',
                'position' => 'HRMO',
            ];

            $facultyApprovedBy = [
                'name' => $directorForFacultyStaff?->name ?? '',
                'position' => 'Campus Director',
            ];

            $facultyMeta = [
                'campus' => 'BINANGONAN',
                'generated_at' => now(),
                'department_code' => 'all',
            ];

            $facultyFilePath = $facultyExportService->export($departmentRows, $facultyPreparedBy, $facultyApprovedBy, $facultyMeta);
            $sourceFiles[] = $facultyFilePath;

            // Staff dataset (all)
            $regularStaffRows = (clone $baseQuery)
                ->whereIn('employment_status', $regularStaffStatusOptions)
                ->orderBy('name')
                ->get();

            $emergencyLaborerRows = (clone $baseQuery)
                ->where('employment_status', $emergencyStaffStatus)
                ->orderBy('name')
                ->get();

            $this->appendRatings($regularStaffRows);
            $this->appendRatings($emergencyLaborerRows);

            $staffPreparedBy = [
                'name' => $preparedByUser?->name ?? '',
                'position' => 'HRMO',
            ];

            $staffNotedBy = [
                'name' => $directorForFacultyStaff?->name ?? '',
                'position' => 'Campus Director',
            ];

            $staffMeta = [
                'campus' => 'BINANGONAN',
            ];

            $staffFilePath = $staffExportService->export($regularStaffRows, $emergencyLaborerRows, $staffPreparedBy, $staffNotedBy, $staffMeta);
            $sourceFiles[] = $staffFilePath;

            // Dean/Director dataset (all)
            $deanDirectorRows = $this->buildDeanDirectorRows();
            $deanPreparedByUser = $request->user()->loadMissing('designation');
            $directorForDean = User::with(['designation', 'userRoles'])
                ->where('is_active', true)
                ->whereHas('userRoles', function ($q) {
                    $q->where('role', 'director');
                })
                ->orderBy('name')
                ->first();

            $deanPreparedBy = [
                'name' => $deanPreparedByUser?->name,
                'position' => 'Campus HRMO',
            ];

            $deanNotedBy = [
                'name' => $directorForDean?->name ?? '',
                'position' => 'Campus Director',
            ];

            $deanDirectorFilePath = $deanDirectorExportService->export($deanDirectorRows, $deanPreparedBy, $deanNotedBy);
            $sourceFiles[] = $deanDirectorFilePath;

            $combinedFilePath = $this->mergeSummaryExportFiles($sourceFiles, [
                'Faculty Summary',
                'Staff Summary',
                'Dean Director Summary',
            ]);

            $this->cleanupExportFiles($sourceFiles);

            ActivityLogService::log(
                'summary_reports_export_all',
                'Exported combined summary workbook (Faculty, Staff, Dean and Director).',
                null,
                [
                    'faculty_count' => $facultyUsers->count() + $facultyPartTimeUsers->count(),
                    'staff_count' => $regularStaffRows->count() + $emergencyLaborerRows->count(),
                    'dean_director_count' => $deanDirectorRows->count(),
                ]
            );

            $downloadName = 'IPCR-Summary-of-Performance-Rating-URSB_'.now()->year.'.xlsx';

            return response()->download($combinedFilePath, $downloadName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            $this->cleanupExportFiles($sourceFiles);

            \Log::error('Summary reports export-all error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $fallbackCategory = (string) $request->query('category', 'faculty');
            if (! in_array($fallbackCategory, ['faculty', 'staff', 'dean-director'], true)) {
                $fallbackCategory = 'faculty';
            }

            $fallbackDepartment = $fallbackCategory === 'faculty'
                ? (string) $request->query('department', 'all')
                : 'all';

            return redirect()
                ->route('faculty.summary-reports', ['category' => $fallbackCategory, 'department' => $fallbackDepartment])
                ->withErrors(['export' => 'Failed to export all summary reports in one workbook.']);
        }
    }

    /**
     * Export the faculty summary report as XLSX with the official matrix layout.
     */
    public function exportFacultyXlsx(Request $request, FacultySummaryExportService $exportService)
    {
        $activeDepartment = (string) $request->query('department', 'all');

        try {
            $departments = Department::orderBy('code')->get();
            $scopeDepartments = $activeDepartment === 'all'
                ? $departments
                : $departments->filter(fn ($department) => $department->code === $activeDepartment)->values();

            $excludedRoles = ['dean', 'admin', 'hr', 'director'];
            $baseQuery = User::with(['department', 'designation', 'userRoles'])
                ->where('is_active', true)
                ->whereHas('userRoles', function ($q) {
                    $q->where('role', 'faculty');
                })
                ->whereDoesntHave('userRoles', function ($q) use ($excludedRoles) {
                    $q->whereIn('role', $excludedRoles);
                });

            if ($activeDepartment !== 'all') {
                $baseQuery->whereHas('department', function ($q) use ($activeDepartment) {
                    $q->where('code', $activeDepartment);
                });
            }

            $users = (clone $baseQuery)
                ->whereNull('employment_status')
                ->orderBy('name')
                ->get();

            $partTimeUsers = (clone $baseQuery)
                ->where('employment_status', 'Part Time')
                ->orderBy('name')
                ->get();

            $this->appendRatings($users);
            $this->appendRatings($partTimeUsers);

            $departmentRows = $scopeDepartments->map(function ($department) use ($users, $partTimeUsers) {
                return [
                    'department' => $department,
                    'permanent' => $users->where('department_id', $department->id)->values(),
                    'part_time' => $partTimeUsers->where('department_id', $department->id)->values(),
                ];
            });

            if ($activeDepartment === 'all') {
                $departmentRows = $departmentRows
                    ->filter(function (array $row) {
                        return $row['permanent']->isNotEmpty() || $row['part_time']->isNotEmpty();
                    });
            }

            $departmentRows = $departmentRows->values();

            $preparedByUser = $request->user();
            $director = User::query()
                ->where('is_active', true)
                ->whereHas('userRoles', function ($query) {
                    $query->where('role', 'director');
                })
                ->orderByDesc('updated_at')
                ->orderBy('name')
                ->first();

            $preparedBy = [
                'name' => $preparedByUser?->name ?? '',
                'position' => 'HRMO',
            ];

            $approvedBy = [
                'name' => $director?->name ?? '',
                'position' => 'Campus Director',
            ];

            $meta = [
                'campus' => 'BINANGONAN',
                'generated_at' => now(),
                'department_code' => $activeDepartment,
            ];

            $filePath = $exportService->export($departmentRows, $preparedBy, $approvedBy, $meta);

            ActivityLogService::log(
                'faculty_summary_exported',
                'Exported faculty summary report to Excel.',
                null,
                [
                    'department' => $activeDepartment,
                    'record_count' => $users->count() + $partTimeUsers->count(),
                ]
            );

            $departmentSuffix = $activeDepartment === 'all' ? 'All_Departments' : strtoupper($activeDepartment);
            $downloadName = 'Faculty_Summary_'.$departmentSuffix.'_'.now()->format('Ymd_His').'.xlsx';

            return response()->download($filePath, $downloadName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            \Log::error('Faculty summary export error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'department' => $activeDepartment,
            ]);

            return redirect()
                ->route('faculty.summary-reports', ['category' => 'faculty', 'department' => $activeDepartment])
                ->withErrors(['export' => 'Failed to export faculty summary report.']);
        }
    }

    /**
     * Export the staff summary report as XLSX in the official staff matrix layout.
     */
    public function exportStaffXlsx(Request $request, StaffSummaryExportService $exportService)
    {
        try {
            $excludedRoles = ['dean', 'admin', 'hr', 'director'];
            $regularStaffStatusOptions = ['Permanent', 'Casual', 'Contractual'];
            $emergencyStaffStatus = 'Emergency Laborer';

            $baseQuery = User::with(['department', 'designation', 'userRoles'])
                ->where('is_active', true)
                ->whereHas('userRoles', function ($q) {
                    $q->where('role', 'faculty');
                })
                ->whereDoesntHave('userRoles', function ($q) use ($excludedRoles) {
                    $q->whereIn('role', $excludedRoles);
                });

            $regularStaffRows = (clone $baseQuery)
                ->whereIn('employment_status', $regularStaffStatusOptions)
                ->orderBy('name')
                ->get();

            $emergencyLaborerRows = (clone $baseQuery)
                ->where('employment_status', $emergencyStaffStatus)
                ->orderBy('name')
                ->get();

            $this->appendRatings($regularStaffRows);
            $this->appendRatings($emergencyLaborerRows);

            $preparedByUser = $request->user();
            $director = User::query()
                ->where('is_active', true)
                ->whereHas('userRoles', function ($query) {
                    $query->where('role', 'director');
                })
                ->orderByDesc('updated_at')
                ->orderBy('name')
                ->first();

            $preparedBy = [
                'name' => $preparedByUser?->name ?? '',
                'position' => 'HRMO',
            ];

            $notedBy = [
                'name' => $director?->name ?? '',
                'position' => 'Campus Director',
            ];

            $meta = [
                'campus' => 'BINANGONAN',
            ];

            $filePath = $exportService->export($regularStaffRows, $emergencyLaborerRows, $preparedBy, $notedBy, $meta);

            ActivityLogService::log(
                'staff_summary_exported',
                'Exported staff summary report to Excel.',
                null,
                [
                    'regular_count' => $regularStaffRows->count(),
                    'emergency_count' => $emergencyLaborerRows->count(),
                ]
            );

            $downloadName = 'Staff_Summary_'.now()->format('Ymd_His').'.xlsx';

            return response()->download($filePath, $downloadName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            \Log::error('Staff summary export error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('faculty.summary-reports', ['category' => 'staff', 'department' => 'all'])
                ->withErrors(['export' => 'Failed to export staff summary report.']);
        }
    }

    /**
     * View a dean IPCR submission in read-only mode for HR summary module.
     */
    public function showDeanIpcrSubmission(Request $request, IpcrSubmission $submission)
    {
        $submission->load([
            'user:id,name,employee_id,department_id,designation_id',
            'user.department:id,name,code',
            'user.designation:id,title',
            'deanCalibrations' => function ($query) {
                $query->where('status', 'calibrated')
                    ->with('dean:id,name')
                    ->orderByDesc('updated_at');
            },
        ]);

        $this->ensureDeanCalibratedSubmission($submission);

        $latestCalibration = $submission->deanCalibrations->first();
        $calibrationHistory = $submission->deanCalibrations->map(function (DeanCalibration $calibration) {
            return [
                'dean_name' => $calibration->dean?->name ?? 'Unknown',
                'overall_score' => $calibration->overall_score,
                'updated_at' => $calibration->updated_at,
            ];
        });

        $supportingDocuments = SupportingDocument::query()
            ->where('user_id', $submission->user_id)
            ->where('documentable_type', 'ipcr_submission')
            ->where('documentable_id', $submission->id)
            ->orderBy('so_label')
            ->orderByDesc('created_at')
            ->get()
            ->filter(function (SupportingDocument $document) {
                return trim((string) ($document->path ?: $document->filename)) !== '';
            })
            ->unique(function (SupportingDocument $document) {
                return $document->storage_key ?: $document->path;
            })
            ->map(function (SupportingDocument $document) {
                return [
                    'id' => $document->id,
                    'so_label' => $document->so_label ?: 'Uncategorized',
                    'original_name' => $document->original_name,
                    'mime_type' => $document->mime_type,
                    'file_size_human' => $document->file_size_human,
                    'created_at' => $document->created_at,
                    'created_at_display' => $document->created_at?->format('M d, Y h:i A'),
                    'path' => $document->file_url,
                ];
            })
            ->groupBy('so_label')
            ->sortKeys();

        ActivityLogService::log(
            'hr_viewed_dean_ipcr_submission',
            'Viewed calibrated dean IPCR submission: '.$submission->title,
            $submission
        );

        return view('dashboard.faculty.dean-ipcr-submission', [
            'submission' => $submission,
            'latestCalibration' => $latestCalibration,
            'calibrationHistory' => $calibrationHistory,
            'supportingDocuments' => $supportingDocuments,
        ]);
    }

    /**
     * Export a calibrated dean IPCR submission from HR summary module.
     */
    public function exportDeanIpcrSubmission(Request $request, IpcrSubmission $submission, IpcrExportService $exportService)
    {
        $submission->load([
            'user:id,name,employee_id,department_id',
            'user.userRoles:id,user_id,role',
            'deanCalibrations' => function ($query) {
                $query->where('status', 'calibrated');
            },
        ]);

        $this->ensureDeanCalibratedSubmission($submission);

        try {
            $filePath = $exportService->export($submission);

            ActivityLogService::log(
                'hr_exported_dean_ipcr_submission',
                'Exported calibrated dean IPCR submission: '.$submission->title,
                $submission
            );

            $safeUser = preg_replace('/[^a-zA-Z0-9_-]/', '_', (string) ($submission->user?->name ?? 'Dean'));
            $downloadName = 'Dean_IPCR_'.$safeUser.'_'.$submission->school_year.'.xlsx';

            return response()->download($filePath, $downloadName)->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            \Log::error('Dean IPCR export error', [
                'submission_id' => $submission->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('faculty.summary-reports', ['category' => 'dean-ipcrs', 'department' => 'all'])
                ->withErrors(['export' => 'Failed to export the selected dean IPCR submission.']);
        }
    }

    /**
     * Ensure the submission belongs to a dean and has at least one calibrated result.
     */
    private function ensureDeanCalibratedSubmission(IpcrSubmission $submission): void
    {
        $isDeanSubmission = $submission->user && $submission->user->hasRole('dean');
        $hasCalibratedResult = $submission->deanCalibrations
            ? $submission->deanCalibrations->isNotEmpty()
            : DeanCalibration::where('ipcr_submission_id', $submission->id)
                ->where('status', 'calibrated')
                ->exists();

        if (! $isDeanSubmission || ! $hasCalibratedResult) {
            abort(404);
        }
    }

    /**
     * Merge single-sheet export files into one workbook with separate tabs.
     */
    private function mergeSummaryExportFiles(array $sourceFiles, array $sheetTitles = []): string
    {
        $mergedSpreadsheet = null;

        foreach ($sourceFiles as $index => $sourceFile) {
            if (! is_string($sourceFile) || $sourceFile === '' || ! is_file($sourceFile)) {
                continue;
            }

            $sourceSpreadsheet = IOFactory::load($sourceFile);
            $sourceSheet = $sourceSpreadsheet->getSheet(0);
            $targetTitle = $sheetTitles[$index] ?? $sourceSheet->getTitle();

            // Ensure tab title follows Excel constraints.
            $targetTitle = substr((string) $targetTitle, 0, 31);

            if ($mergedSpreadsheet === null) {
                $sourceSheet->setTitle($targetTitle);
                $mergedSpreadsheet = $sourceSpreadsheet;

                continue;
            }

            $sourceSheet->setTitle($targetTitle);
            $mergedSpreadsheet->addExternalSheet($sourceSheet);

            $sourceSpreadsheet->disconnectWorksheets();
            unset($sourceSpreadsheet);
        }

        if ($mergedSpreadsheet === null) {
            throw new \RuntimeException('No export files were available for merge.');
        }

        $outputDir = storage_path('app/exports');
        if (! is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $outputFilePath = $outputDir.DIRECTORY_SEPARATOR.'Summary_Reports_All_'.now()->format('Ymd_His').'.xlsx';

        $writer = new Xlsx($mergedSpreadsheet);
        $writer->save($outputFilePath);

        $mergedSpreadsheet->disconnectWorksheets();

        return $outputFilePath;
    }

    /**
     * Delete temporary export files when they are no longer needed.
     */
    private function cleanupExportFiles(array $files): void
    {
        foreach ($files as $file) {
            if (is_string($file) && $file !== '' && is_file($file)) {
                @unlink($file);
            }
        }
    }

    /**
     * Resolve the latest submitted performance document for a user.
     * Prefers active submissions, then latest submitted record.
     */
    private function resolveLatestPerformanceSubmission(User $user): IpcrSubmission|OpcrSubmission|null
    {
        $latestOpcr = OpcrSubmission::where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->orderByDesc('is_active')
            ->orderByDesc('submitted_at')
            ->first();

        $latestIpcr = IpcrSubmission::where('user_id', $user->id)
            ->whereNotNull('submitted_at')
            ->orderByDesc('is_active')
            ->orderByDesc('submitted_at')
            ->first();

        if (! $latestOpcr) {
            return $latestIpcr;
        }

        if (! $latestIpcr) {
            return $latestOpcr;
        }

        return $latestOpcr->submitted_at >= $latestIpcr->submitted_at ? $latestOpcr : $latestIpcr;
    }

    /**
     * Apply manual override values to calculated row scores.
     */
    private function applyDeanDirectorOverride(array $calculatedScores, ?DeanDirectorSummaryOverride $override): array
    {
        if (! $override) {
            $calculatedScores['is_manual'] = false;

            return $calculatedScores;
        }

        $hasOverrideValue = $override->strategic_score !== null
            || $override->core_score !== null
            || $override->support_score !== null;

        if (! $hasOverrideValue) {
            $calculatedScores['is_manual'] = false;

            return $calculatedScores;
        }

        $strategic = $override->strategic_score !== null
            ? (float) $override->strategic_score
            : $calculatedScores['strategic_score'];
        $core = $override->core_score !== null
            ? (float) $override->core_score
            : $calculatedScores['core_score'];
        $support = $override->support_score !== null
            ? (float) $override->support_score
            : $calculatedScores['support_score'];

        $hasAnyScore = $strategic !== null || $core !== null || $support !== null;
        $totalPercentage = $hasAnyScore
            ? round(($strategic ?? 0) + ($core ?? 0) + ($support ?? 0), 2)
            : null;
        $total = $totalPercentage !== null
            ? round($totalPercentage / 20, 2)
            : null;

        return [
            'strategic_score' => $strategic,
            'core_score' => $core,
            'support_score' => $support,
            'total_score' => $total,
            'adjectival_rating' => $total !== null ? $this->getAdjectivalRating($total) : null,
            'is_manual' => true,
        ];
    }

    /**
     * Compute 35/55/10 weighted percentage points, then convert total to 1-5 scale.
     */
    private function computeWeightedSectionScores(string $tableBodyHtml): array
    {
        if (empty(trim($tableBodyHtml))) {
            return [
                'strategic_score' => null,
                'core_score' => null,
                'support_score' => null,
                'total_score' => null,
                'adjectival_rating' => null,
            ];
        }

        $ratings = [
            'strategic-objectives' => [],
            'core-functions' => [],
            'support-function' => [],
        ];

        $wrappedHtml = '<table><tbody>'.$tableBodyHtml.'</tbody></table>';

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$wrappedHtml, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $rows = $xpath->query('//tr');
        $currentSection = null;

        foreach ($rows as $row) {
            $className = $row->getAttribute('class') ?? '';

            if (str_contains($className, 'bg-green-100')) {
                $currentSection = 'strategic-objectives';

                continue;
            }

            if (str_contains($className, 'bg-purple-100')) {
                $currentSection = 'core-functions';

                continue;
            }

            if (str_contains($className, 'bg-orange-100')) {
                $currentSection = 'support-function';

                continue;
            }

            if (! $currentSection || str_contains($className, 'bg-blue-100') || str_contains($className, 'bg-gray-100')) {
                continue;
            }

            $cells = $xpath->query('.//td', $row);
            if ($cells->length < 7) {
                continue;
            }

            $scoreValue = $this->extractCellValue($cells->item(6), $xpath);
            if (is_numeric($scoreValue)) {
                $numeric = (float) $scoreValue;
                if ($numeric > 0) {
                    $ratings[$currentSection][] = $numeric;
                }
            }
        }

        $strategicAverage = $this->average($ratings['strategic-objectives']);
        $coreAverage = $this->average($ratings['core-functions']);
        $supportAverage = $this->average($ratings['support-function']);

        // Convert section averages from 1-5 into weighted percentage points.
        $strategicWeighted = $strategicAverage !== null ? round(($strategicAverage / 5) * 35, 2) : null;
        $coreWeighted = $coreAverage !== null ? round(($coreAverage / 5) * 55, 2) : null;
        $supportWeighted = $supportAverage !== null ? round(($supportAverage / 5) * 10, 2) : null;

        $hasAnyScore = $strategicWeighted !== null || $coreWeighted !== null || $supportWeighted !== null;
        $totalPercentage = $hasAnyScore
            ? round(($strategicWeighted ?? 0) + ($coreWeighted ?? 0) + ($supportWeighted ?? 0), 2)
            : null;
        $total = $totalPercentage !== null
            ? round($totalPercentage / 20, 2)
            : null;

        return [
            'strategic_score' => $strategicWeighted,
            'core_score' => $coreWeighted,
            'support_score' => $supportWeighted,
            'total_score' => $total,
            'adjectival_rating' => $total !== null ? $this->getAdjectivalRating($total) : null,
        ];
    }

    /**
     * Extract value from td input/textarea/plain text.
     */
    private function extractCellValue($td, DOMXPath $xpath): string
    {
        $textareas = $xpath->query('.//textarea', $td);
        if ($textareas->length > 0) {
            return trim($textareas->item(0)->textContent);
        }

        $inputs = $xpath->query('.//input', $td);
        if ($inputs->length > 0) {
            return trim($inputs->item(0)->getAttribute('value'));
        }

        return trim($td->textContent);
    }

    /**
     * Get average value or null when array is empty.
     */
    private function average(array $values): ?float
    {
        if (count($values) === 0) {
            return null;
        }

        return array_sum($values) / count($values);
    }

    /**
     * Append calibrated and adjectival ratings to each user collection item.
     */
    private function appendRatings(Collection $users): void
    {
        $users->each(function ($user) {
            // Get the latest finalized calibration for this user
            $calibration = DeanCalibration::where('status', 'calibrated')
                ->whereHas('ipcrSubmission', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orderByDesc('updated_at')
                ->first();

            $user->calibrated_rating = $calibration?->overall_score;
            $user->adjectival_rating = $this->getAdjectivalRating($calibration?->overall_score);
        });
    }

    /**
     * Convert a numeric rating to its adjectival equivalent.
     */
    private function getAdjectivalRating(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }

        if ($score >= 4.50) {
            return 'Outstanding';
        }
        if ($score >= 3.50) {
            return 'Very Satisfactory';
        }
        if ($score >= 2.50) {
            return 'Satisfactory';
        }
        if ($score >= 1.50) {
            return 'Unsatisfactory';
        }

        return 'Poor';
    }
}
