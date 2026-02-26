<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Models\Driver\Driver;
use App\Models\Trip;
use App\Models\Payment;
use App\Models\SosAlert;
use App\Models\DriverDocument;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_users'       => User::count(),
            'new_users_today'   => User::whereDate('created_at', today())->count(),
            'active_drivers'    => Driver::where('status', 'approved')->count(),
            'online_drivers'    => Driver::where('driver_status', 'online')->count(),
            'today_rides'       => Trip::whereDate('created_at', today())->count(),
            'active_rides'      => Trip::where('status', 'in_progress')->count(),
            'today_revenue'     => Payment::where('status', 'success')->whereDate('created_at', today())->sum('amount'),
            'today_commission'  => Payment::where('status', 'success')->whereDate('created_at', today())->sum('commission'),
        ];

        // Filtres chauffeurs
        $driversQuery = Driver::query();

        if ($request->filled('chauffeur')) {
            $driversQuery->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->chauffeur . '%')
                  ->orWhere('last_name', 'like', '%' . $request->chauffeur . '%');
            });
        }

        if ($request->filled('matricule')) {
            $driversQuery->where('vehicle_plate', 'like', '%' . $request->matricule . '%');
        }

        if ($request->filled('couleur')) {
            $driversQuery->where('vehicle_color', 'like', '%' . $request->couleur . '%');
        }

        $drivers = $driversQuery->get();

        // -------------------------------------------------------
        // Si requête API → retourner JSON
        // -------------------------------------------------------
        if ($request->expectsJson()) {
            return response()->json([
                'stats'   => $stats,
                'drivers' => $drivers,
            ]);
        }

        // -------------------------------------------------------
        // Si Blade → retourner la vue avec les données
        // -------------------------------------------------------
        return view('admin.dashboard', compact('stats', 'drivers'));
    }
}