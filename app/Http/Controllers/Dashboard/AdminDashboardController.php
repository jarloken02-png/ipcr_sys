<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\IpcrSubmission;
use App\Models\AdminNotification;
use App\Models\UpcomingDeadline;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $departmentId = $request->get('department_id');

        $departments = Department::orderBy('name')->get();
        $submissions = IpcrSubmission::with(['user.department'])
            ->when($departmentId, function ($query) use ($departmentId) {
                $query->whereHas('user', function ($userQuery) use ($departmentId) {
                    $userQuery->where('department_id', $departmentId);
                });
            })
            ->orderByDesc('submitted_at')
            ->get();

        $notifications = AdminNotification::active()
            ->whereNull('user_id')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $deadlines = UpcomingDeadline::active()
            ->upcoming()
            ->orderBy('deadline_date')
            ->limit(5)
            ->get();

        return view('dashboard.admin.index', [
            'departments' => $departments,
            'submissions' => $submissions,
            'selectedDepartmentId' => $departmentId,
            'notifications' => $notifications,
            'deadlines' => $deadlines,
        ]);
    }
}