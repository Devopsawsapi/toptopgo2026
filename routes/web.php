<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DriverController;

/*
|--------------------------------------------------------------------------
| Routes publiques
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return response()->json([
        'name' => 'TopTopGo API',
        'version' => '1.0.0',
        'status' => 'running',
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
    ]);
});


/*
|--------------------------------------------------------------------------
| Routes Admin (Login)
|--------------------------------------------------------------------------
*/

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

            if (auth()->user()->role !== 'admin') {
                auth()->logout();
                return back()->withErrors([
                    'email' => 'Accès non autorisé'
                ]);
            }

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors([
            'email' => 'Identifiants incorrects'
        ]);

    })->name('login.submit');
});


/*
|--------------------------------------------------------------------------
| Routes Admin protégées
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');


        /*
        |--------------------------------------------------------------------------
        | Chauffeurs
        |--------------------------------------------------------------------------
        */

        Route::get('/drivers', [DriverController::class, 'index'])
            ->name('drivers.index');

        Route::get('/drivers/create', [DriverController::class, 'create'])
            ->name('drivers.create');

        Route::post('/drivers', [DriverController::class, 'store'])
            ->name('drivers.store');

        Route::get('/drivers/{driver}', [DriverController::class, 'show'])
            ->name('drivers.show');

        Route::patch('/drivers/{driver}/toggle-status',
            [DriverController::class, 'toggleStatus']
        )->name('drivers.toggle-status');


        /*
        |--------------------------------------------------------------------------
        | Autres menus
        |--------------------------------------------------------------------------
        */

        Route::get('/users', fn() => 'Page Utilisateurs')->name('users');
        Route::get('/rides', fn() => 'Page Courses')->name('rides');
        Route::get('/transactions', fn() => 'Page Transactions')->name('transactions');
        Route::get('/kyc', fn() => 'Page KYC')->name('kyc');
        Route::get('/withdrawals', fn() => 'Page Retraits')->name('withdrawals');
        Route::get('/settings', fn() => 'Page Paramètres')->name('settings');

        Route::get('/kyc/{id}', function ($id) {
            return "KYC Review ID: " . $id;
        })->name('kyc.review');


        /*
        |--------------------------------------------------------------------------
        | Logout
        |--------------------------------------------------------------------------
        */

        Route::post('/logout', function () {
            auth()->logout();
            return redirect()->route('admin.login');
        })->name('logout');

    });

