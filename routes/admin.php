<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DriverController;
use App\Http\Controllers\Admin\KycController;
use App\Http\Controllers\Admin\RideController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\WithdrawalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/


Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Drivers
    Route::get('/drivers', [DriverController::class, 'index'])->name('drivers');
    Route::get('/drivers/{driver}', [DriverController::class, 'show'])->name('drivers.show');
    Route::post('/drivers/{driver}/toggle-verification', [DriverController::class, 'toggleVerification'])->name('drivers.toggle-verification');

    // KYC Verification
    Route::get('/kyc', [KycController::class, 'index'])->name('kyc');
    Route::get('/kyc/{driver}/review', [KycController::class, 'review'])->name('kyc.review');
    Route::post('/kyc/{driver}/approve', [KycController::class, 'approve'])->name('kyc.approve');
    Route::post('/kyc/{driver}/reject', [KycController::class, 'reject'])->name('kyc.reject');

    // Rides
    Route::get('/rides', [RideController::class, 'index'])->name('rides');
    Route::get('/rides/{ride}', [RideController::class, 'show'])->name('rides.show');

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');

    // Withdrawals
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])->name('withdrawals');
    Route::post('/withdrawals/{transaction}/process', [WithdrawalController::class, 'process'])->name('withdrawals.process');
    Route::post('/withdrawals/{transaction}/reject', [WithdrawalController::class, 'reject'])->name('withdrawals.reject');

    // Settings
    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('settings');

    // Logout
    Route::post('/logout', function () {
        auth()->logout();
        return redirect('/admin/login');
    })->name('logout');
});

// Admin Login (no auth required)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', function () {
        return view('admin.login');
    })->name('login');

    Route::post('/login', function () {
        $credentials = request()->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->attempt($credentials)) {
            $user = auth()->user();
            if ($user->role !== 'admin') {
                auth()->logout();
                return back()->withErrors(['email' => 'Accès non autorisé']);
            }
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['email' => 'Identifiants incorrects']);
    })->name('login.submit');
});
