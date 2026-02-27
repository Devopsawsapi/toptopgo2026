<?php

namespace App\Providers;

use App\Services\Payment\PaymentService;
use App\Services\Payment\PeexService;
use App\Services\Payment\MtnMomoService;
use App\Services\Payment\AirtelMoneyService;
use App\Services\Payment\StripeService;

// ↓ Ajouter ces 2 imports
use App\Models\Course;
use App\Observers\CourseObserver;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register payment services as singletons
        $this->app->singleton(PeexService::class);
        $this->app->singleton(MtnMomoService::class);
        $this->app->singleton(AirtelMoneyService::class);
        $this->app->singleton(StripeService::class);
        $this->app->singleton(PaymentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MySQL to avoid key too long error
        Schema::defaultStringLength(191);

        // ↓ Ajouter cette ligne — calcul automatique des commissions
        Course::observe(CourseObserver::class);
    }
}