<?php

use App\Http\Controllers\Admin\DatabaseManagementController;
use App\Http\Controllers\Admin\NotificationDeadlineController;
use App\Http\Controllers\Admin\PhotoController;
use App\Http\Controllers\Admin\RoleDesignationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\DeanDashboardController;
use App\Http\Controllers\Dashboard\FacultyDashboardController;
use App\Http\Controllers\Dean\DeanReviewController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\Faculty\IpcrExportController;
use App\Http\Controllers\Faculty\IpcrImportController;
use App\Http\Controllers\Faculty\IpcrSavedCopyController;
use App\Http\Controllers\Faculty\IpcrSubmissionController;
use App\Http\Controllers\Faculty\IpcrTemplateController;
use App\Http\Controllers\Faculty\OpcrExportController;
use App\Http\Controllers\Faculty\OpcrSavedCopyController;
use App\Http\Controllers\Faculty\OpcrSubmissionController;
use App\Http\Controllers\Faculty\OpcrTemplateController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [LoginController::class, 'showLoginForm'])
    ->name('login')
    ->middleware('guest');

Route::get('/login', [LoginController::class, 'showLoginForm'])
    ->name('login.form')
    ->middleware('guest');

Route::post('/login', [LoginController::class, 'login'])
    ->name('login.post')
    ->middleware('guest');

Route::post('/logout', [LoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// Password Reset Routes
Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])
    ->name('password.request')
    ->middleware('guest');

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetCode'])
    ->name('password.email')
    ->middleware(['guest', 'throttle:3,15']);

Route::get('/verify-code', [PasswordResetController::class, 'showVerifyCodeForm'])
    ->name('password.verify.form')
    ->middleware('guest');

Route::post('/verify-code', [PasswordResetController::class, 'verifyCode'])
    ->name('password.verify')
    ->middleware(['guest', 'throttle:5,15']);

Route::get('/reset-password', [PasswordResetController::class, 'showResetPasswordForm'])
    ->name('password.reset.form')
    ->middleware('guest');

Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
    ->name('password.update')
    ->middleware('guest');

// Email Verification Routes
Route::post('/email/verification/send', [EmailVerificationController::class, 'sendVerificationCode'])
    ->name('verification.send')
    ->middleware('auth');

Route::post('/email/verification/verify', [EmailVerificationController::class, 'verifyCode'])
    ->name('verification.verify')
    ->middleware('auth');

/*
|--------------------------------------------------------------------------
| Faculty Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::get('/faculty/dashboard', [FacultyDashboardController::class, 'index'])
    ->name('faculty.dashboard')
    ->middleware(['auth', 'role:faculty,director', 'permission:faculty.dashboard,director.dashboard']);

Route::post('/faculty/notifications/mark-read', [FacultyDashboardController::class, 'markAllRead'])
    ->name('faculty.notifications.mark-read')
    ->middleware(['auth', 'role:faculty,director,hr']);

Route::get('/faculty/my-ipcrs', [FacultyDashboardController::class, 'myIpcrs'])
    ->name('faculty.my-ipcrs')
    ->middleware(['auth', 'role:faculty,director', 'permission:faculty.dashboard,director.dashboard']);

Route::get('/faculty/profile', [FacultyDashboardController::class, 'profile'])
    ->name('faculty.profile')
    ->middleware(['auth', 'role:faculty,director', 'permission:faculty.profile.manage,director.dashboard']);

Route::get('/faculty/summary-reports', [\App\Http\Controllers\Faculty\SummaryReportController::class, 'index'])
    ->name('faculty.summary-reports')
    ->middleware(['auth', 'role:hr']);

Route::get('/faculty/summary-reports/faculty/export', [\App\Http\Controllers\Faculty\SummaryReportController::class, 'exportFacultyXlsx'])
    ->name('faculty.summary-reports.faculty.export')
    ->middleware(['auth', 'role:hr']);

Route::get('/faculty/summary-reports/staff/export', [\App\Http\Controllers\Faculty\SummaryReportController::class, 'exportStaffXlsx'])
    ->name('faculty.summary-reports.staff.export')
    ->middleware(['auth', 'role:hr']);

Route::get('/faculty/summary-reports/export-all', [\App\Http\Controllers\Faculty\SummaryReportController::class, 'exportAllXlsx'])
    ->name('faculty.summary-reports.export-all')
    ->middleware(['auth', 'role:hr']);

Route::put('/faculty/summary-reports/dean-director/{user}/scores', [\App\Http\Controllers\Faculty\SummaryReportController::class, 'updateDeanDirectorScores'])
    ->name('faculty.summary-reports.dean-director.update')
    ->middleware(['auth', 'role:hr']);

Route::get('/faculty/summary-reports/dean-director/export', [\App\Http\Controllers\Faculty\SummaryReportController::class, 'exportDeanDirectorXlsx'])
    ->name('faculty.summary-reports.dean-director.export')
    ->middleware(['auth', 'role:hr']);

Route::get('/faculty/summary-reports/dean-ipcrs/{submission}', [\App\Http\Controllers\Faculty\SummaryReportController::class, 'showDeanIpcrSubmission'])
    ->name('faculty.summary-reports.dean-ipcrs.show')
    ->middleware(['auth', 'role:hr']);

Route::get('/faculty/summary-reports/dean-ipcrs/{submission}/export', [\App\Http\Controllers\Faculty\SummaryReportController::class, 'exportDeanIpcrSubmission'])
    ->name('faculty.summary-reports.dean-ipcrs.export')
    ->middleware(['auth', 'role:hr']);

Route::patch('/faculty/password/change', [FacultyDashboardController::class, 'changePassword'])
    ->name('faculty.password.change')
    ->middleware(['auth', 'role:faculty,director', 'permission:faculty.profile.manage,director.dashboard']);

Route::patch('/faculty/profile/update', [FacultyDashboardController::class, 'updateProfile'])
    ->name('faculty.profile.update')
    ->middleware(['auth', 'role:faculty,director', 'permission:faculty.profile.manage,director.dashboard']);

Route::post('/faculty/profile/photo/upload', [FacultyDashboardController::class, 'uploadPhoto'])
    ->name('faculty.profile.photo.upload')
    ->middleware(['auth', 'role:faculty,director', 'permission:faculty.profile.manage,director.dashboard']);

Route::get('/faculty/profile/photos', [FacultyDashboardController::class, 'getPhotos'])
    ->name('faculty.profile.photos')
    ->middleware(['auth', 'role:faculty,director', 'permission:faculty.profile.manage,director.dashboard']);

Route::post('/faculty/profile/photo/set-profile', [FacultyDashboardController::class, 'setProfilePhoto'])
    ->name('faculty.profile.photo.set-profile')
    ->middleware(['auth', 'role:faculty,director', 'permission:faculty.profile.manage,director.dashboard']);

Route::delete('/faculty/profile/photo/{id}', [FacultyDashboardController::class, 'deletePhoto'])
    ->name('faculty.profile.photo.delete')
    ->middleware(['auth', 'role:faculty,director', 'permission:faculty.profile.manage,director.dashboard']);

// IPCR Template Routes
Route::get('/faculty/ipcr/templates', [IpcrTemplateController::class, 'index'])
    ->name('faculty.ipcr.templates.index')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.templates']);

Route::post('/faculty/ipcr/store', [IpcrTemplateController::class, 'store'])
    ->name('faculty.ipcr.store')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.templates']);

Route::post('/faculty/ipcr/templates/from-saved-copy', [IpcrTemplateController::class, 'storeFromSavedCopy'])
    ->name('faculty.ipcr.templates.from-saved-copy')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.templates']);

Route::post('/faculty/ipcr/templates/{id}/save-copy', [IpcrTemplateController::class, 'saveCopy'])
    ->name('faculty.ipcr.templates.save-copy')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.templates']);

Route::get('/faculty/ipcr/templates/{id}', [IpcrTemplateController::class, 'show'])
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.templates']);

Route::delete('/faculty/ipcr/templates/{id}', [IpcrTemplateController::class, 'destroy'])
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.templates']);

Route::put('/faculty/ipcr/templates/{id}', [IpcrTemplateController::class, 'update'])
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.templates']);

Route::post('/faculty/ipcr/templates/{id}/set-active', [IpcrTemplateController::class, 'setActive'])
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.templates']);

// IPCR Submission Routes
Route::post('/faculty/ipcr/submissions', [IpcrSubmissionController::class, 'store'])
    ->name('faculty.ipcr.submissions.store')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.submissions']);

Route::get('/faculty/ipcr/submissions/{id}', [IpcrSubmissionController::class, 'show'])
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.submissions']);

Route::put('/faculty/ipcr/submissions/{id}', [IpcrSubmissionController::class, 'update'])
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.submissions']);

Route::post('/faculty/ipcr/submissions/{id}/set-active', [IpcrSubmissionController::class, 'setActive'])
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.submissions']);

Route::post('/faculty/ipcr/submissions/{id}/deactivate', [IpcrSubmissionController::class, 'deactivate'])
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.submissions']);

Route::post('/faculty/ipcr/submissions/{id}/unsubmit', [IpcrSubmissionController::class, 'unsubmit'])
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.submissions']);

Route::delete('/faculty/ipcr/submissions/{id}', [IpcrSubmissionController::class, 'destroy'])
    ->middleware(['auth', 'role:faculty,admin', 'permission:faculty.ipcr.submissions']);

// IPCR Export Routes
Route::get('/faculty/ipcr/submissions/{id}/export', [IpcrExportController::class, 'export'])
    ->name('faculty.ipcr.submissions.export')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.submissions']);

Route::get('/faculty/ipcr/saved-copies/{id}/export', [IpcrExportController::class, 'exportSavedCopy'])
    ->name('faculty.ipcr.saved-copies.export')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.saved-copies']);

Route::get('/faculty/ipcr/templates/{id}/export', [IpcrExportController::class, 'exportTemplate'])
    ->name('faculty.ipcr.templates.export')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.templates']);

// IPCR/OPCR Import Route
Route::post('/faculty/ipcr/import', [IpcrImportController::class, 'import'])
    ->name('faculty.ipcr.import')
    ->middleware(['auth', 'role:faculty,director']);

// IPCR Saved Copy Routes
Route::get('/faculty/ipcr/saved-copies', [IpcrSavedCopyController::class, 'index'])
    ->name('faculty.ipcr.saved-copies.index')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.saved-copies']);

Route::post('/faculty/ipcr/saved-copies', [IpcrSavedCopyController::class, 'store'])
    ->name('faculty.ipcr.saved-copies.store')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.saved-copies']);

Route::get('/faculty/ipcr/saved-copies/{id}', [IpcrSavedCopyController::class, 'show'])
    ->name('faculty.ipcr.saved-copies.show')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.saved-copies']);

Route::put('/faculty/ipcr/saved-copies/{id}', [IpcrSavedCopyController::class, 'update'])
    ->name('faculty.ipcr.saved-copies.update')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.saved-copies']);

Route::delete('/faculty/ipcr/saved-copies/{id}', [IpcrSavedCopyController::class, 'destroy'])
    ->name('faculty.ipcr.saved-copies.destroy')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.ipcr.saved-copies']);

// OPCR Template Routes (Dean and Director)
Route::get('/faculty/opcr/templates', [OpcrTemplateController::class, 'index'])
    ->name('faculty.opcr.templates.index')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.templates,director.dashboard']);

Route::post('/faculty/opcr/store', [OpcrTemplateController::class, 'store'])
    ->name('faculty.opcr.store')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.templates,director.dashboard']);

Route::post('/faculty/opcr/templates/from-saved-copy', [OpcrTemplateController::class, 'storeFromSavedCopy'])
    ->name('faculty.opcr.templates.from-saved-copy')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.templates,director.dashboard']);

Route::post('/faculty/opcr/templates/{id}/save-copy', [OpcrTemplateController::class, 'saveCopy'])
    ->name('faculty.opcr.templates.save-copy')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.templates,director.dashboard']);

Route::get('/faculty/opcr/templates/{id}', [OpcrTemplateController::class, 'show'])
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.templates,director.dashboard']);

Route::delete('/faculty/opcr/templates/{id}', [OpcrTemplateController::class, 'destroy'])
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.templates,director.dashboard']);

Route::put('/faculty/opcr/templates/{id}', [OpcrTemplateController::class, 'update'])
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.templates,director.dashboard']);

Route::post('/faculty/opcr/templates/{id}/set-active', [OpcrTemplateController::class, 'setActive'])
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.templates,director.dashboard']);

// OPCR Submission Routes (Dean and Director)
Route::post('/faculty/opcr/submissions', [OpcrSubmissionController::class, 'store'])
    ->name('faculty.opcr.submissions.store')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.submissions,director.dashboard']);

Route::get('/faculty/opcr/submissions/{id}', [OpcrSubmissionController::class, 'show'])
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.submissions,director.dashboard']);

Route::put('/faculty/opcr/submissions/{id}', [OpcrSubmissionController::class, 'update'])
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.submissions,director.dashboard']);

Route::post('/faculty/opcr/submissions/{id}/set-active', [OpcrSubmissionController::class, 'setActive'])
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.submissions,director.dashboard']);

Route::post('/faculty/opcr/submissions/{id}/deactivate', [OpcrSubmissionController::class, 'deactivate'])
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.submissions,director.dashboard']);

Route::post('/faculty/opcr/submissions/{id}/unsubmit', [OpcrSubmissionController::class, 'unsubmit'])
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.submissions,director.dashboard']);

Route::delete('/faculty/opcr/submissions/{id}', [OpcrSubmissionController::class, 'destroy'])
    ->middleware(['auth', 'role:faculty,director,admin', 'permission:dean.opcr.submissions,director.dashboard']);

Route::get('/faculty/opcr/submissions/{id}/export', [OpcrExportController::class, 'export'])
    ->name('faculty.opcr.submissions.export')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.submissions,director.dashboard']);

Route::get('/faculty/opcr/saved-copies/{id}/export', [OpcrExportController::class, 'exportSavedCopy'])
    ->name('faculty.opcr.saved-copies.export')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.saved-copies,director.dashboard']);

Route::get('/faculty/opcr/templates/{id}/export', [OpcrExportController::class, 'exportTemplate'])
    ->name('faculty.opcr.templates.export')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.templates,director.dashboard']);

// OPCR Saved Copy Routes (Dean and Director)
Route::get('/faculty/opcr/saved-copies', [OpcrSavedCopyController::class, 'index'])
    ->name('faculty.opcr.saved-copies.index')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.saved-copies,director.dashboard']);

Route::post('/faculty/opcr/saved-copies', [OpcrSavedCopyController::class, 'store'])
    ->name('faculty.opcr.saved-copies.store')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.saved-copies,director.dashboard']);

Route::get('/faculty/opcr/saved-copies/{id}', [OpcrSavedCopyController::class, 'show'])
    ->name('faculty.opcr.saved-copies.show')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.saved-copies,director.dashboard']);

Route::put('/faculty/opcr/saved-copies/{id}', [OpcrSavedCopyController::class, 'update'])
    ->name('faculty.opcr.saved-copies.update')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.saved-copies,director.dashboard']);

Route::delete('/faculty/opcr/saved-copies/{id}', [OpcrSavedCopyController::class, 'destroy'])
    ->name('faculty.opcr.saved-copies.destroy')
    ->middleware(['auth', 'role:faculty,director', 'permission:dean.opcr.saved-copies,director.dashboard']);

// Supporting Document Routes
Route::get('/faculty/supporting-documents', [\App\Http\Controllers\Faculty\SupportingDocumentController::class, 'index'])
    ->name('faculty.supporting-documents.index')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.supporting-documents']);

Route::post('/faculty/supporting-documents', [\App\Http\Controllers\Faculty\SupportingDocumentController::class, 'store'])
    ->name('faculty.supporting-documents.store')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.supporting-documents']);

Route::delete('/faculty/supporting-documents/{id}', [\App\Http\Controllers\Faculty\SupportingDocumentController::class, 'destroy'])
    ->name('faculty.supporting-documents.destroy')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.supporting-documents']);

Route::put('/faculty/supporting-documents/{id}/rename', [\App\Http\Controllers\Faculty\SupportingDocumentController::class, 'rename'])
    ->name('faculty.supporting-documents.rename')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.supporting-documents']);

Route::get('/faculty/supporting-documents/{id}/download', [\App\Http\Controllers\Faculty\SupportingDocumentController::class, 'download'])
    ->name('faculty.supporting-documents.download')
    ->middleware(['auth', 'role:faculty', 'permission:faculty.supporting-documents']);

/*
|--------------------------------------------------------------------------
| Dean Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::get('/dean/dashboard', [DeanDashboardController::class, 'index'])
    ->name('dean.dashboard')
    ->middleware(['auth', 'role:dean', 'permission:dean.dashboard']);

// Dean IPCR Review Routes
Route::get('/dean/review/faculty-submissions', [DeanReviewController::class, 'facultySubmissions'])
    ->name('dean.review.faculty-submissions')
    ->middleware(['auth', 'role:dean', 'permission:dean.review.faculty']);

Route::get('/dean/review/faculty-submissions/{id}', [DeanReviewController::class, 'showFacultySubmission'])
    ->name('dean.review.faculty-submission.show')
    ->middleware(['auth', 'role:dean', 'permission:dean.review.faculty']);

Route::get('/dean/review/dean-submissions', [DeanReviewController::class, 'deanSubmissions'])
    ->name('dean.review.dean-submissions')
    ->middleware(['auth', 'role:dean', 'permission:dean.review.deans']);

Route::get('/dean/review/dean-submissions/{id}', [DeanReviewController::class, 'showDeanSubmission'])
    ->name('dean.review.dean-submission.show')
    ->middleware(['auth', 'role:dean', 'permission:dean.review.deans']);

Route::post('/dean/review/calibrations', [DeanReviewController::class, 'saveCalibration'])
    ->name('dean.review.calibrations.save')
    ->middleware(['auth', 'role:dean']);

/*
|--------------------------------------------------------------------------
| Director Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::get('/director/dashboard', function () {
    return redirect()->route('faculty.dashboard');
})
    ->name('director.dashboard')
    ->middleware(['auth', 'role:director', 'permission:director.dashboard']);

/*
|--------------------------------------------------------------------------
| Admin Dashboard Routes
|--------------------------------------------------------------------------
*/

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->name('admin.dashboard')
    ->middleware(['auth', 'role:admin,hr', 'permission:admin.dashboard']);

// User Management (Admin + HR)
Route::middleware(['auth', 'role:admin,hr', 'permission:admin.users.manage'])
    ->prefix('admin/panel')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', UserManagementController::class);

        Route::patch('users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])
            ->name('users.toggleActive');

        Route::get('users/{user}/json', [UserManagementController::class, 'showJson'])
            ->name('users.showJson');
    });

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin/panel')->name('admin.')->group(function () {
    // Photo Management
    Route::middleware(['permission:admin.photos.manage'])->group(function () {
        Route::post('users/{user}/photo/upload', [PhotoController::class, 'upload'])
            ->name('users.photo.upload');

        Route::delete('users/{user}/photos/{photo}', [PhotoController::class, 'delete'])
            ->name('users.photo.delete');

        Route::patch('users/{user}/photos/{photo}/set-profile', [PhotoController::class, 'setAsProfile'])
            ->name('users.photo.setProfile');

        Route::get('users/{user}/photos', [PhotoController::class, 'getUserPhotos'])
            ->name('users.photos.get');
    });

    // Database Management
    Route::middleware(['permission:admin.database.manage'])->group(function () {
        Route::get('database', [DatabaseManagementController::class, 'index'])->name('database.index');
        Route::post('database/backup', [DatabaseManagementController::class, 'backup'])->name('database.backup');
        Route::get('database/download/{filename}', [DatabaseManagementController::class, 'download'])->name('database.download');
        Route::post('database/restore/{filename}', [DatabaseManagementController::class, 'restore'])->name('database.restore');
        Route::delete('database/{filename}', [DatabaseManagementController::class, 'delete'])->name('database.delete');
        Route::post('database/upload', [DatabaseManagementController::class, 'upload'])->name('database.upload');
        Route::post('database/settings', [DatabaseManagementController::class, 'updateSettings'])->name('database.settings');
    });

    // Activity Logs
    Route::middleware(['permission:admin.activity-logs.view'])->group(function () {
        Route::get('activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::get('activity-logs/export', [\App\Http\Controllers\Admin\ActivityLogController::class, 'export'])->name('activity-logs.export');
    });

    // Role & Department Management
    Route::middleware(['permission:admin.role-management.manage'])->group(function () {
        Route::get('role-management', [RoleDesignationController::class, 'index'])->name('role-management.index');
        // Roles
        Route::post('role-management/roles', [RoleDesignationController::class, 'storeRole'])->name('role-management.roles.store');
        Route::put('role-management/roles/{role}', [RoleDesignationController::class, 'updateRole'])->name('role-management.roles.update');
        Route::delete('role-management/roles/{role}', [RoleDesignationController::class, 'destroyRole'])->name('role-management.roles.destroy');
        // Departments
        Route::post('role-management/departments', [RoleDesignationController::class, 'storeDepartment'])->name('role-management.departments.store');
        Route::put('role-management/departments/{department}', [RoleDesignationController::class, 'updateDepartment'])->name('role-management.departments.update');
        Route::delete('role-management/departments/{department}', [RoleDesignationController::class, 'destroyDepartment'])->name('role-management.departments.destroy');
        // Designations
        Route::post('role-management/designations', [RoleDesignationController::class, 'storeDesignation'])->name('role-management.designations.store');
        Route::put('role-management/designations/{designation}', [RoleDesignationController::class, 'updateDesignation'])->name('role-management.designations.update');
        Route::delete('role-management/designations/{designation}', [RoleDesignationController::class, 'destroyDesignation'])->name('role-management.designations.destroy');
    });

    // Notifications & Deadlines Management
    Route::get('notifications', [NotificationDeadlineController::class, 'index'])->name('notifications.index');
    // Notification CRUD
    Route::post('notifications', [NotificationDeadlineController::class, 'storeNotification'])->name('notifications.store');
    Route::put('notifications/{notification}', [NotificationDeadlineController::class, 'updateNotification'])->name('notifications.update');
    Route::patch('notifications/{notification}/toggle', [NotificationDeadlineController::class, 'toggleNotification'])->name('notifications.toggle');
    Route::delete('notifications/{notification}', [NotificationDeadlineController::class, 'destroyNotification'])->name('notifications.destroy');
    // Deadline CRUD
    Route::post('deadlines', [NotificationDeadlineController::class, 'storeDeadline'])->name('deadlines.store');
    Route::put('deadlines/{deadline}', [NotificationDeadlineController::class, 'updateDeadline'])->name('deadlines.update');
    Route::patch('deadlines/{deadline}/toggle', [NotificationDeadlineController::class, 'toggleDeadline'])->name('deadlines.toggle');
    Route::delete('deadlines/{deadline}', [NotificationDeadlineController::class, 'destroyDeadline'])->name('deadlines.destroy');
    // API endpoints for dashboard widgets
    Route::get('api/notifications', [NotificationDeadlineController::class, 'apiNotifications'])->name('api.notifications');
    Route::get('api/deadlines', [NotificationDeadlineController::class, 'apiDeadlines'])->name('api.deadlines');
});
