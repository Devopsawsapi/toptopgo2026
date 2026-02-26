<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\DriverAuthController;
use App\Http\Controllers\Auth\UserAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TripController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\SosAlertController;
use App\Http\Controllers\Admin\StatisticsController;

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/

// Admin Auth
Route::prefix('admin/auth')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'me']);
    });
});

// Driver Auth
Route::prefix('driver/auth')->group(function () {
    Route::post('register', [DriverAuthController::class, 'register']);
    Route::post('login', [DriverAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [DriverAuthController::class, 'logout']);
        Route::get('me', [DriverAuthController::class, 'me']);
    });
});

// User Auth
Route::prefix('user/auth')->group(function () {
    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('login', [UserAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [UserAuthController::class, 'logout']);
        Route::get('me', [UserAuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------
| ADMIN API ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('api.admin.')->middleware(['auth:sanctum'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Super Admin
    Route::middleware('role.permission:Super Admin')->group(function () {
        Route::apiResource('admins', AdminUserController::class)->names('admins');
    });

    // Admin (gestion utilisateurs/chauffeurs)
    Route::middleware('role.permission:Admin')->group(function () {
        Route::apiResource('drivers', DriverController::class)->only(['index','store','show'])->names('drivers');
        Route::apiResource('users', UserController::class)->only(['index','show'])->names('api.users');
        Route::get('trips', [TripController::class, 'index'])->name('trips');
    });

    // Finance
    Route::middleware('role.permission:Finance Manager')->group(function () {
        Route::apiResource('payments', PaymentController::class)->only(['index','show'])->names('payments');
        Route::get('withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals');
    });

    // Compliance
    Route::middleware('role.permission:Compliance Manager')->group(function () {
        Route::get('documents/pending', [DocumentController::class, 'pending'])->name('documents.pending');
        Route::get('documents/expiring', [DocumentController::class, 'expiring'])->name('documents.expiring');
        Route::get('sos', [SosAlertController::class, 'index'])->name('sos');
    });

    // Commercial
    Route::middleware('role.permission:Commercial Manager')->group(function () {
        Route::get('stats/overview', [StatisticsController::class, 'overview'])->name('stats.overview');
        Route::get('stats/daily', [StatisticsController::class, 'daily'])->name('stats.daily');
        Route::get('stats/top-drivers', [StatisticsController::class, 'topDrivers'])->name('stats.top-drivers');
    });
});