<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\ActivityLogService;

class LoginController extends Controller
{
    /**
     * Show the unified login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login — auto-detect the user's primary role.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $credentials['username'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            ActivityLogService::log(
                'login_failed',
                'Failed login attempt: invalid credentials',
                $user,
                [
                    'username' => $credentials['username'],
                    'reason' => 'invalid_credentials',
                ]
            );

            return back()->withErrors([
                'username' => 'Invalid username or password',
            ]);
        }

        if (!$user->is_active) {
            ActivityLogService::log(
                'login_failed',
                'Failed login attempt: account is inactive',
                $user,
                [
                    'username' => $credentials['username'],
                    'reason' => 'inactive_account',
                ]
            );

            return back()->withErrors([
                'username' => 'Your account is inactive',
            ]);
        }

        $primaryRole = $user->getPrimaryRole();

        if (!$primaryRole) {
            ActivityLogService::log(
                'login_failed',
                'Failed login attempt: no role assigned to account',
                $user,
                [
                    'username' => $credentials['username'],
                    'reason' => 'missing_role',
                ]
            );

            return back()->withErrors([
                'username' => 'No role assigned to your account. Please contact an administrator.',
            ]);
        }

        // Check dashboard permission (admin always bypasses)
        if (!$user->hasRole('admin')) {
            $dashboardPermissionMap = [
                'faculty'  => 'faculty.dashboard',
                'dean'     => 'faculty.dashboard',
                'director' => 'director.dashboard',
            ];

            $permissionKey = $dashboardPermissionMap[$primaryRole] ?? null;
            if ($permissionKey && !$user->hasPermission($permissionKey)) {
                ActivityLogService::log(
                    'login_failed',
                    'Failed login attempt: dashboard permission denied',
                    $user,
                    [
                        'username' => $credentials['username'],
                        'reason' => 'permission_denied',
                        'required_permission' => $permissionKey,
                    ]
                );

                return back()->withErrors([
                    'username' => 'Your account does not have dashboard access. Please contact an administrator.',
                ]);
            }
        }

        $user->update(['last_login_at' => now()]);

        Auth::login($user);
        $request->session()->regenerate();

        ActivityLogService::log('login', 'Logged in as ' . $primaryRole, $user);

        return $this->redirectToDashboard($primaryRole);
    }

    /**
     * Redirect to appropriate dashboard based on role.
     */
    private function redirectToDashboard(string $role)
    {
        $route = match ($role) {
            'admin'    => 'admin.dashboard',
            'director' => 'faculty.dashboard',
            default    => 'faculty.dashboard',
        };

        return redirect()->route($route)->header('Turbo-Visit-Control', 'reload');
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();
        ActivityLogService::log('logout', 'Logged out', $user);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->with('success', 'Logged out successfully');
    }
}