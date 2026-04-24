<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;

// Controllers
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\FoundItemController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\InstituteProgramController;
use App\Http\Controllers\OsdsAppointmentController;

// PUBLIC ROUTES
Route::view('/about', 'Login.about')->name('about');
Route::view('/how-it-works', 'Login.how-it-works')->name('how-it-works');

// GUEST ROUTES
Route::middleware(RedirectIfAuthenticated::class)->group(function () {
    Route::get('/', [UserController::class, 'login'])->name('login');
    Route::get('/login', [UserController::class, 'login'])->name('login');
    Route::post('/logincheck', [UserController::class, 'logincheck'])->name('logincheck');
});

// AUTHENTICATED ROUTES
Route::middleware(Authenticate::class)->group(function () {
    Route::get('/dashboard', [UserController::class, 'redirectToIndex'])->name('dashboard');

    // Fetch user's info
    Route::get('/api/user-info', [UserController::class, 'getUser']);

    // CHANGE PASSWORD
    Route::post('/change-password', [ChangePasswordController::class, 'changePassword'])->name('change-password');

    // SKIP CHANGE PASSWORD
    Route::post('/skip-temp-password', [ChangePasswordController::class, 'skipTempPassChange'])->name('skip-temp-password');

    // Notification
    Route::get('/api/notifications', [NotificationController::class, 'getUserNotifications']);
    Route::post('/notifications/{id}/mark-read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // Delete Notification
    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAllNotifications']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'deleteNotification']);

    // Messages
    Route::prefix('api/messages')->group(function () {
        Route::get('/conversations', [MessageController::class, 'conversations']);
        Route::get('/chat/{partnerId}', [MessageController::class, 'chatWith']);
        Route::post('/send', [MessageController::class, 'send']);
        Route::get('/users', [MessageController::class, 'users']);
        Route::get('/unread', [MessageController::class, 'unreadCount']);
    });

    Route::prefix('api/admin')->group(function () {
            Route::get('/appointments', [OsdsAppointmentController::class, 'adminIndex']);
            Route::post('/appointments/{ref}/approve', [OsdsAppointmentController::class, 'approve']);
            Route::post('/appointments/{ref}/reject', [OsdsAppointmentController::class, 'reject']);
            Route::post('/appointments/{ref}/reschedule', [OsdsAppointmentController::class, 'reschedule']);
            Route::delete('/appointments/{ref}', [OsdsAppointmentController::class, 'destroy']);
        });

    // Appointments
    Route::get('/api/appointments', [OsdsAppointmentController::class, 'index']);
    Route::post('/api/appointments', [OsdsAppointmentController::class, 'store']);

    // Display All Approved Posts
    Route::get('/found-items/all-post-approve', [FoundItemController::class, 'displayApprovedPosts']);

    // Found Items
    Route::prefix('/found-items')
        ->controller(FoundItemController::class)
        ->group(function () {
            // Create
            Route::post('/', 'createPost');

            // Edit
            Route::post('/{item_id}/edit', 'editPost');

            // Display all posts (Admin Manage Posts)
            Route::get('/all-post-status', 'displayAllPostStatus');

            // Display only the current user's posts (My Posts)
            Route::get('/user-post', 'displayUserPosts');

            // Approve / Reject
            Route::post('/{item_id}/approve', 'approvePost');
            Route::post('/{item_id}/reject', 'rejectPost');

            // Archive / Unarchive
            Route::delete('/{item_id}/archive', 'archivePost');
            Route::post('/{item_id}/unarchive', 'unarchivePost');

            // Permanent Delete
            Route::delete('/{item_id}/delete', 'deletePost');
        });

    // Claims
    Route::prefix('/claims')
        ->controller(ClaimController::class)
        ->group(function () {
            // Get claims for an item
            Route::get('/{item_id}', 'getClaimsForItem');

            // Accept and eject claim
            Route::post('/{claimId}/accept', 'acceptClaim');
            Route::post('/{claimId}/reject', 'rejectClaim');

            // Submit a claim
            Route::post('/claim-item/{item_id}', 'submitClaim');
        });

    // User claims
    Route::middleware('auth')->get('/api/user-claims', [ClaimController::class, 'getCurrentUserClaimedItemIds']);

    // ADMIN ONLY
    Route::middleware('admin')->group(function () {
        Route::view('/posts', 'Admin.posts')->name('Admin.posts');
        Route::view('/manage-posts', 'Admin.manage-posts')->name('Admin.manage-posts');
        Route::view('/feedbacks', 'Admin.feedbacks')->name('Admin.feedbacks');

        Route::view('/admin/profile.php', 'Admin.profile')->name('Admin.profile');
        Route::view('/admin/settings.php', 'Admin.settings')->name('Admin.settings');

        // Dashboard Statistics

        Route::prefix('api/admin')
            ->controller(AdminDashboardController::class)
            ->group(function () {
                Route::get('/stats', 'stats');
                Route::get('/recent-users', 'recentUsers');
            });

        // Manage User & Add User
        Route::view('/manage-users', 'Admin.manage-users')->name('Admin.manage-users');

        Route::get('/add-user', [UserController::class, 'adduser'])->name('add-user');
        Route::post('/addusercheck', [UserController::class, 'addusercheck'])->name('addusercheck');

        Route::get('/users', [UserController::class, 'userDetails']);
        Route::put('/users/{user_id}', [UserController::class, 'editUser']);
        Route::delete('/users/{user_id}', [UserController::class, 'deleteUser']);
        Route::post('/users/{user_id}/reset-password', [UserController::class, 'resetPassword']);

        Route::get('/admin/manage-institute-program.php', [InstituteProgramController::class, 'index'])->name('admin.institute-program');

        Route::post('/admin/institute-program/extract-ai', [InstituteProgramController::class, 'extractAI']);
        Route::post('/admin/institute-program/import-ai', [InstituteProgramController::class, 'importAI']);

        Route::post('/admin/institute', [App\Http\Controllers\InstituteProgramController::class, 'storeInstitute']);
        Route::post('/admin/program', [App\Http\Controllers\InstituteProgramController::class, 'storeProgram']);

        Route::put('/admin/institute/{id}', [InstituteProgramController::class, 'updateInstitute']);
        Route::delete('/admin/institute/{id}', [InstituteProgramController::class, 'deleteInstitute']);

        Route::put('/admin/program/{id}', [InstituteProgramController::class, 'updateProgram']);
        Route::delete('/admin/program/{id}', [InstituteProgramController::class, 'deleteProgram']);

        Route::get('/api/institutes', [App\Http\Controllers\InstituteProgramController::class, 'getInstitutes']);
        Route::get('/api/programs/{instituteCode}', [App\Http\Controllers\InstituteProgramController::class, 'getPrograms']);

        // Feedbacks (Admin)
        Route::prefix('/api/admin')
            ->controller(FeedbackController::class)
            ->group(function () {
                // Fetch
                Route::get('/feedbacks', 'index');

                // Delete
                Route::delete('/feedbacks/{id}', 'destroy');
            });
    });

    // USER ONLY
    Route::middleware('user')->group(function () {
        Route::view('/home', 'User.home')->name('User.home');
        Route::view('/my-posts', 'User.my-posts')->name('User.my-posts');
        Route::view('/give-feedback', 'User.give-feedback')->name('User.give-feedback');

        Route::view('/user/profile.php', 'User.profile')->name('User.profile');
        Route::view('/user/settings.php', 'User.settings')->name('User.settings');

        // Feedbacks (user)
        Route::prefix('/api/user')
            ->controller(FeedbackController::class)
            ->group(function () {
                Route::post('/feedback', 'store');
            });
    });
});

// LOGOUT
Route::post('/logout', [UserController::class, 'logout'])->name('logout');
