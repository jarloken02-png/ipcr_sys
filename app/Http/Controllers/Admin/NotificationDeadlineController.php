<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\UpcomingDeadline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationDeadlineController extends Controller
{
    /**
     * Show the management page for notifications and deadlines.
     */
    public function index(): View
    {
        $notifications = AdminNotification::whereNull('user_id')->orderByDesc('created_at')->get();
        $deadlines = UpcomingDeadline::orderBy('deadline_date')->get();

        return view('admin.notifications.index', compact('notifications', 'deadlines'));
    }

    // ─── Notifications ────────────────────────────────────────────────

    public function storeNotification(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:info,warning,success,danger',
            'audience' => 'required|in:all,faculty,dean,director',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        // Convert empty strings to null so the active() scope works correctly
        $validated['published_at'] = ! empty($validated['published_at']) ? $validated['published_at'] : null;
        $validated['expires_at'] = ! empty($validated['expires_at']) ? $validated['expires_at'] : null;
        $validated['is_active'] = true;
        $validated['created_by'] = auth()->id();

        AdminNotification::create($validated);

        return $this->redirectAfterAction($request)
            ->with('success', 'Notification created successfully.');
    }

    public function updateNotification(Request $request, AdminNotification $notification): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
            'type' => 'required|in:info,warning,success,danger',
            'audience' => 'required|in:all,faculty,dean,director',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
        ]);

        // Convert empty strings to null so the active() scope works correctly
        $validated['published_at'] = ! empty($validated['published_at']) ? $validated['published_at'] : null;
        $validated['expires_at'] = ! empty($validated['expires_at']) ? $validated['expires_at'] : null;

        // Guard: admin panel cannot edit user-targeted notifications
        if ($notification->user_id !== null) {
            abort(403, 'This notification is user-targeted and cannot be managed here.');
        }

        $notification->update($validated);

        return $this->redirectAfterAction($request)
            ->with('success', 'Notification updated successfully.');
    }

    public function toggleNotification(Request $request, AdminNotification $notification): RedirectResponse
    {
        // User-targeted notifications are managed exclusively for users, not via the admin panel
        if ($notification->user_id !== null) {
            abort(403, 'This notification is user-targeted and cannot be managed here.');
        }

        $notification->update(['is_active' => ! $notification->is_active]);

        $status = $notification->is_active ? 'activated' : 'deactivated';

        return $this->redirectAfterAction($request)
            ->with('success', "Notification {$status} successfully.");
    }

    public function destroyNotification(Request $request, AdminNotification $notification): RedirectResponse
    {
        // User-targeted notifications are managed exclusively for users, not via the admin panel
        if ($notification->user_id !== null) {
            abort(403, 'This notification is user-targeted and cannot be managed here.');
        }

        $notification->delete();

        return $this->redirectAfterAction($request)
            ->with('success', 'Notification deleted successfully.');
    }

    // ─── Deadlines ────────────────────────────────────────────────────

    public function storeDeadline(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'deadline_date' => 'required|date',
            'audience' => 'required|in:all,faculty,dean,director',
        ]);

        $validated['is_active'] = true;
        $validated['created_by'] = auth()->id();

        UpcomingDeadline::create($validated);

        return $this->redirectAfterAction($request)
            ->with('success', 'Deadline created successfully.');
    }

    public function updateDeadline(Request $request, UpcomingDeadline $deadline): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'deadline_date' => 'required|date',
            'audience' => 'required|in:all,faculty,dean,director',
        ]);

        $deadline->update($validated);

        return $this->redirectAfterAction($request)
            ->with('success', 'Deadline updated successfully.');
    }

    public function toggleDeadline(Request $request, UpcomingDeadline $deadline): RedirectResponse
    {
        $deadline->update(['is_active' => ! $deadline->is_active]);

        $status = $deadline->is_active ? 'activated' : 'deactivated';

        return $this->redirectAfterAction($request)
            ->with('success', "Deadline {$status} successfully.");
    }

    public function destroyDeadline(Request $request, UpcomingDeadline $deadline): RedirectResponse
    {
        $deadline->delete();

        return $this->redirectAfterAction($request)
            ->with('success', 'Deadline deleted successfully.');
    }

    /**
     * Redirect to admin notifications by default, or back to Summary Reports module.
     */
    private function redirectAfterAction(Request $request): RedirectResponse
    {
        $redirectTo = (string) $request->input('redirect_to', '');

        if ($redirectTo !== '' && str_starts_with($redirectTo, url('/faculty/summary-reports'))) {
            return redirect()->to($redirectTo);
        }

        return redirect()->route('admin.notifications.index');
    }

    // ─── API endpoints for dashboard widgets ──────────────────────────

    /**
     * Return active notifications as JSON (for AJAX dashboard widgets).
     */
    public function apiNotifications(Request $request): JsonResponse
    {
        $role = $request->get('role', 'all');

        $notifications = AdminNotification::active()
            ->forAudience($role)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get(['id', 'title', 'message', 'type', 'published_at', 'created_at']);

        return response()->json($notifications);
    }

    /**
     * Return active upcoming deadlines as JSON (for AJAX dashboard widgets).
     */
    public function apiDeadlines(Request $request): JsonResponse
    {
        $role = $request->get('role', 'all');

        $deadlines = UpcomingDeadline::active()
            ->upcoming()
            ->forAudience($role)
            ->orderBy('deadline_date')
            ->get(['id', 'title', 'description', 'deadline_date']);

        return response()->json($deadlines);
    }
}
