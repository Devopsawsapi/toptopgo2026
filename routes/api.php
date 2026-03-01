<?php

use Illuminate\Support\Facades\Route;

// ── Auth Controllers
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\DriverAuthController;
use App\Http\Controllers\Auth\UserAuthController;

// ── Admin Controllers
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
use App\Http\Controllers\Admin\AdminDriverSupportController;
use App\Http\Controllers\Admin\AdminUserSupportController;

// ── Driver Controllers
use App\Http\Controllers\Driver\DriverTripController;
use App\Http\Controllers\Driver\DriverStatusController;
use App\Http\Controllers\Driver\DriverWalletController;
use App\Http\Controllers\Driver\DriverWithdrawalController;
use App\Http\Controllers\Driver\DriverSosController;
use App\Http\Controllers\Driver\DriverMessageController;
use App\Http\Controllers\Driver\DriverSupportController;
use App\Http\Controllers\Driver\DriverDocumentController;
use App\Http\Controllers\Driver\DriverPasswordController;
use App\Http\Controllers\Driver\DriverProfileController;

/*
|--------------------------------------------------------------------------|
| TEST ROUTE / API STATUS
|--------------------------------------------------------------------------|
*/
Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is running'
    ]);
});

/*
|--------------------------------------------------------------------------|
| AUTH ROUTES
|--------------------------------------------------------------------------|
*/
Route::prefix('admin/auth')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'me']);
    });
});

Route::prefix('driver/auth')->group(function () {
    Route::post('register', [DriverAuthController::class, 'register']);
    Route::post('login', [DriverAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [DriverAuthController::class, 'logout']);
        Route::get('me', [DriverAuthController::class, 'me']);
    });
});

Route::prefix('user/auth')->group(function () {
    Route::post('register', [UserAuthController::class, 'register']);
    Route::post('login', [UserAuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [UserAuthController::class, 'logout']);
        Route::get('me', [UserAuthController::class, 'me']);
    });
});

/*
|--------------------------------------------------------------------------|
| ADMIN API ROUTES
|--------------------------------------------------------------------------|
*/
Route::prefix('admin')->name('api.admin.')->middleware(['auth:sanctum'])->group(function () {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Super Admin
    Route::middleware('role.permission:Super Admin')->group(function () {
        Route::apiResource('admins', AdminUserController::class)->names('admins');
    });

    // Admin
    Route::middleware('role.permission:Admin')->group(function () {
        Route::apiResource('drivers', DriverController::class)->only(['index','store','show'])->names('drivers');
        Route::apiResource('users', UserController::class)->only(['index','show'])->names('api.users');
        Route::get('trips', [TripController::class, 'index'])->name('trips');

        // Support admin ↔ chauffeurs
        Route::get('support/drivers', [AdminDriverSupportController::class, 'index'])->name('support.drivers.index');
        Route::get('support/drivers/{driver}', [AdminDriverSupportController::class, 'show'])->name('support.drivers.show');
        Route::post('support/drivers/{driver}/send', [AdminDriverSupportController::class, 'send'])->name('support.drivers.send');

        // Support admin ↔ utilisateurs
        Route::get('support/users', [AdminUserSupportController::class, 'index'])->name('support.users.index');
        Route::get('support/users/{user}', [AdminUserSupportController::class, 'show'])->name('support.users.show');
        Route::post('support/users/{user}/send', [AdminUserSupportController::class, 'send'])->name('support.users.send');
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

/*
|--------------------------------------------------------------------------|
| DRIVER API ROUTES
|--------------------------------------------------------------------------|
*/
Route::prefix('driver')->name('api.driver.')->middleware(['auth:sanctum'])->group(function () {
    // Profil, mot de passe, statut, trips, wallet, retraits, sos, documents (inchangés)

    Route::get('profile', [DriverProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [DriverProfileController::class, 'update'])->name('profile.update');
    Route::put('password', [DriverPasswordController::class, 'update'])->name('password.update');
    Route::put('status', [DriverStatusController::class, 'update'])->name('status.update');

    Route::apiResource('trips', DriverTripController::class)->names('trips');
    Route::post('trips/{id}/start', [DriverTripController::class, 'start'])->name('trips.start');
    Route::post('trips/{id}/end',   [DriverTripController::class, 'end'])->name('trips.end');

    Route::get('wallet', [DriverWalletController::class, 'show'])->name('wallet.show');

    Route::get('withdrawals', [DriverWithdrawalController::class, 'index'])->name('withdrawals.index');
    Route::post('withdrawals', [DriverWithdrawalController::class, 'store'])->name('withdrawals.store');
    Route::get('withdrawals/{id}', [DriverWithdrawalController::class, 'show'])->name('withdrawals.show');

    Route::get('sos',  [DriverSosController::class, 'index'])->name('sos.index');
    Route::post('sos', [DriverSosController::class, 'store'])->name('sos.store');

    // Messages liés aux trips
    Route::get('messages', [DriverMessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{trip_id}', [DriverMessageController::class, 'show'])->name('messages.show');
    Route::post('messages/{trip_id}', [DriverMessageController::class, 'store'])->name('messages.store');

    // Support driver ↔ admin
    Route::get('support',  [DriverSupportController::class, 'index'])->name('support.index');
    Route::post('support', [DriverSupportController::class, 'store'])->name('support.store');

    // Documents
    Route::get('documents', [DriverDocumentController::class, 'index'])->name('documents.index');
    Route::post('documents', [DriverDocumentController::class, 'store'])->name('documents.store');
    Route::get('documents/{id}', [DriverDocumentController::class, 'show'])->name('documents.show');
    Route::delete('documents/{id}', [DriverDocumentController::class, 'destroy'])->name('documents.destroy');
});