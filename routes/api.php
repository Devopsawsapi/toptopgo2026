<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RideController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Auth
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Webhooks (no auth required)
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('/peex/collect', [WebhookController::class, 'handlePeexCollect'])->name('peex.collect');
    Route::post('/peex/payout', [WebhookController::class, 'handlePeexPayout'])->name('peex.payout');
    Route::post('/peex/bank-payout', [WebhookController::class, 'handlePeexBankPayout'])->name('peex.bank-payout');
    Route::post('/mtn-momo', [WebhookController::class, 'handleMtnMomo'])->name('mtn-momo');
    Route::post('/airtel-money', [WebhookController::class, 'handleAirtelMoney'])->name('airtel-money');
    Route::post('/stripe', [WebhookController::class, 'handleStripe'])->name('stripe');
});

/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | User Profile
    |--------------------------------------------------------------------------
    */
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserController::class, 'profile']);
        Route::put('/profile', [UserController::class, 'updateProfile']);
        Route::post('/avatar', [UserController::class, 'updateAvatar']);
        Route::put('/password', [UserController::class, 'updatePassword']);
        Route::post('/fcm-token', [UserController::class, 'updateFcmToken']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });

    /*
    |--------------------------------------------------------------------------
    | Payment Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('payments')->group(function () {

        Route::get('/methods', [PaymentController::class, 'getPaymentMethods']);
        Route::post('/detect-operator', [PaymentController::class, 'detectOperator']);
        Route::post('/verify-phone', [PaymentController::class, 'verifyPhone']);

        Route::post('/rides/{rideId}/pay', [PaymentController::class, 'initiateRidePayment']);
        Route::get('/status/{reference}', [PaymentController::class, 'getPaymentStatus']);
        Route::post('/estimate', [PaymentController::class, 'estimateBreakdown']);

        Route::post('/rides/{rideId}/refund', [PaymentController::class, 'refundPayment']);

        Route::get('/transactions', [PaymentController::class, 'getTransactionHistory']);

        Route::get('/wallet', [PaymentController::class, 'getWalletBalance']);
        Route::post('/withdraw', [PaymentController::class, 'requestWithdrawal']);
    });

    /*
    |--------------------------------------------------------------------------
    | Ride Routes (Passengers)
    |--------------------------------------------------------------------------
    */
    Route::prefix('rides')->group(function () {

        Route::post('/estimate', [RideController::class, 'estimatePrice']);
        Route::post('/', [RideController::class, 'create']);
        Route::get('/', [RideController::class, 'index']);
        Route::get('/active', [RideController::class, 'getActiveRide']);
        Route::get('/{id}', [RideController::class, 'show']);
        Route::post('/{id}/cancel', [RideController::class, 'cancel']);
        Route::post('/{id}/rate', [RideController::class, 'rate']);

        Route::post('/search-drivers', [RideController::class, 'searchDrivers']);
    });

    /*
    |--------------------------------------------------------------------------
    | Driver Routes
    |--------------------------------------------------------------------------
    */
    Route::prefix('driver')->middleware('role:driver')->group(function () {

        Route::get('/profile', [DriverController::class, 'profile']);
        Route::post('/kyc', [DriverController::class, 'submitKyc']);
        Route::get('/kyc/status', [DriverController::class, 'kycStatus']);

        Route::post('/go-online', [DriverController::class, 'goOnline']);
        Route::post('/go-offline', [DriverController::class, 'goOffline']);
        Route::post('/update-location', [DriverController::class, 'updateLocation']);

        Route::get('/available-rides', [DriverController::class, 'availableRides']);
        Route::post('/rides/{id}/accept', [DriverController::class, 'acceptRide']);
        Route::post('/rides/{id}/arrive', [DriverController::class, 'arriveAtPickup']);
        Route::post('/rides/{id}/start', [DriverController::class, 'startRide']);
        Route::post('/rides/{id}/complete', [DriverController::class, 'completeRide']);
        Route::post('/rides/{id}/cancel', [DriverController::class, 'cancelRide']);

        Route::get('/rides/history', [DriverController::class, 'rideHistory']);
        Route::get('/stats', [DriverController::class, 'stats']);
        Route::get('/earnings', [DriverController::class, 'earnings']);
    });

});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {

    Route::get('/users', [UserController::class, 'adminIndex']);
    Route::get('/users/{id}', [UserController::class, 'adminShow']);
    Route::put('/users/{id}/status', [UserController::class, 'updateStatus']);

    Route::get('/drivers', [DriverController::class, 'adminIndex']);
    Route::get('/drivers/pending-kyc', [DriverController::class, 'pendingKyc']);
    Route::post('/drivers/{id}/approve-kyc', [DriverController::class, 'approveKyc']);
    Route::post('/drivers/{id}/reject-kyc', [DriverController::class, 'rejectKyc']);

    Route::get('/rides', [RideController::class, 'adminIndex']);
    Route::get('/rides/{id}', [RideController::class, 'adminShow']);

    Route::get('/transactions', [PaymentController::class, 'adminTransactions']);
    Route::get('/transactions/stats', [PaymentController::class, 'transactionStats']);
});