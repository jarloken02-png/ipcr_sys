<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ActivityLogController extends Controller
{
    /**
     * Display paginated activity logs with filters.
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest('created_at');

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereRaw('CAST(properties AS CHAR) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by action
        if ($action = $request->input('action')) {
            $query->byAction($action);
        }

        // Filter by user
        if ($userId = $request->input('user_id')) {
            $query->byUser($userId);
        }

        // Date range
        if ($from = $request->input('date_from')) {
            $query->where('created_at', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $logs = $query->paginate(25)->withQueryString();

        // Stats
        $totalLogs    = ActivityLog::count();
        $todayLogs    = ActivityLog::whereDate('created_at', today())->count();
        $uniqueToday  = ActivityLog::whereDate('created_at', today())->distinct('user_id')->count('user_id');

        // Dropdown data
        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $users   = User::orderBy('name')->get(['id', 'name']);

        return view('admin.activity-logs.index', compact(
            'logs', 'totalLogs', 'todayLogs', 'uniqueToday',
            'actions', 'users'
        ));
    }
    /**
     * Export activity logs to a text file.
     */
    public function export(Request $request)
    {
        $query = ActivityLog::with('user')->latest('created_at');

        // Apply filters (Reuse logic or extract to scope/trait if used often)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereRaw('CAST(properties AS CHAR) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('username', 'like', "%{$search}%");
                  });
            });
        }

        if ($action = $request->input('action')) {
            $query->byAction($action);
        }

        if ($userId = $request->input('user_id')) {
            $query->byUser($userId);
        }

        if ($from = $request->input('date_from')) {
            $query->where('created_at', '>=', $from);
        }
        if ($to = $request->input('date_to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $logs = $query->get();
        $fileName = 'activity_logs_' . date('Y-m-d_H-i-s') . '.txt';

        // Build header
        $separator = str_repeat('=', 140);
        $content = "ACTIVITY LOGS EXPORT\n";
        $content .= "Generated: " . now()->format('F d, Y h:i:s A') . "\n";
        $content .= "Total Records: " . $logs->count() . "\n";
        $content .= $separator . "\n\n";

        // Rows (detailed block format)
        foreach ($logs as $log) {
            $userName = $log->user ? $log->user->name : 'Unknown';
            $role     = $log->user ? $log->user->getPrimaryRole() : 'N/A';
            $dateTime = $log->created_at->format('Y-m-d h:i:s A');
            $action   = $this->formatActionLabel((string) $log->action);
            $desc     = $log->description ?? '';
            $ip       = $log->ip_address ?? 'N/A';

            $content .= "Timestamp: {$dateTime}\n";
            $content .= "User: {$userName}\n";
            $content .= "Role: {$role}\n";
            $content .= "Action: {$action}\n";
            $content .= "Description: {$desc}\n";
            $content .= "IP Address: {$ip}\n";

            $content .= str_repeat('-', 140) . "\n";
        }

        $content .= "\n" . $separator . "\n";
        $content .= "END OF REPORT\n";

        // Ensure temp directory exists
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        // Write to temporary file
        $tempFile = $tempDir . '/' . $fileName;
        file_put_contents($tempFile, $content);

        // Return file download response
        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'text/plain',
        ])->deleteFileAfterSend(true);
    }

    private function formatActionLabel(string $action): string
    {
        return match ($action) {
            'request_get', 'viewed' => 'Viewed Page',
            'request_post', 'submitted' => 'Submitted Form',
            'request_put', 'request_patch', 'updated' => 'Updated Record',
            'request_delete', 'deleted' => 'Deleted Record',
            'activity' => 'General Activity',
            'login' => 'Signed In',
            'logout' => 'Signed Out',
            'login_failed' => 'Failed Sign In',
            'created' => 'Created Record',
            'toggled_active' => 'Changed Account Status',
            'backup_created' => 'Created Backup',
            'backup_restored' => 'Restored Backup',
            'backup_deleted' => 'Deleted Backup',
            'backup_uploaded' => 'Uploaded Backup',
            'settings_updated' => 'Updated Settings',
            'profile_updated' => 'Updated Profile',
            'password_changed' => 'Changed Password',
            'password_reset' => 'Reset Password',
            'photo_uploaded' => 'Uploaded Photo',
            'photo_deleted' => 'Deleted Photo',
            default => ucfirst(str_replace('_', ' ', $action)),
        };
    }
}
