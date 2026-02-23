<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Ride;
use App\Models\Transaction;
use App\Models\DriverProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        /*
        |--------------------------------------------------------------------------
        | STATS
        |--------------------------------------------------------------------------
        */

        $stats = [
            'total_users'       => User::count(),
            'new_users_today'   => User::whereDate('created_at', $today)->count(),
            'active_drivers'    => DriverProfile::where('kyc_status', 'approved')->count(),
            'online_drivers'    => DriverProfile::where('is_online', true)->count(),
            'today_rides'       => Ride::whereDate('created_at', $today)->count(),
            'active_rides'      => Ride::whereIn('status', ['pending', 'accepted', 'in_progress'])->count(),

            'today_revenue' => Ride::whereDate('created_at', $today)
                ->where('status', 'completed')
                ->sum('price'),

            'today_commission' => Transaction::whereDate('created_at', $today)
                ->where('type', 'commission')
                ->sum('amount'),
        ];

        /*
        |--------------------------------------------------------------------------
        | FILTRAGE VEHICULES (CARTE)
        |--------------------------------------------------------------------------
        */

        $driversQuery = DriverProfile::with('user')
            ->where('kyc_status', 'approved')
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude');

        // 🔎 Filtre par immatriculation
        if ($request->filled('matricule')) {
            $matricule = trim($request->matricule);
            $driversQuery->where('vehicle_plate_number', 'like', "%{$matricule}%");
        }

        // 🔎 Filtre par chauffeur
        if ($request->filled('chauffeur')) {
            $chauffeur = trim($request->chauffeur);
            $driversQuery->whereHas('user', function ($q) use ($chauffeur) {
                $q->where('name', 'like', "%{$chauffeur}%");
            });
        }

        // 🔎 Filtre par couleur
        if ($request->filled('couleur')) {
            $couleur = trim($request->couleur);
            $driversQuery->where('vehicle_color', 'like', "%{$couleur}%");
        }

        $drivers = $driversQuery->get();

        /*
        |--------------------------------------------------------------------------
        | CHART DATA (7 derniers jours)
        |--------------------------------------------------------------------------
        */

        $chartData = [
            'labels'  => [],
            'revenue' => [],
            'rides'   => [],
        ];

        for ($i = 6; $i >= 0; $i--) {

            $date = Carbon::today()->subDays($i);

            $chartData['labels'][] = $date->format('d/m');

            $chartData['revenue'][] = Ride::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('price');

            $chartData['rides'][] = Ride::whereDate('created_at', $date)
                ->count();
        }

        /*
        |--------------------------------------------------------------------------
        | RECENT RIDES
        |--------------------------------------------------------------------------
        */

        $recentRides = Ride::with(['passenger', 'driver.user'])
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | PENDING KYC
        |--------------------------------------------------------------------------
        */

        $pendingKyc = DriverProfile::with('user')
            ->where('kyc_status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'stats',
            'chartData',
            'recentRides',
            'pendingKyc',
            'drivers'
        ));
    }
}