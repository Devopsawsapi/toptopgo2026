<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Driver\Driver;
use App\Models\Trip;

class MapController extends Controller
{
    public function onlineDrivers()
    {
        $drivers = Driver::with('latestLocation')
            ->where('driver_status', 'online')
            ->where('status', 'approved')
            ->get()
            ->map(function ($driver) {
                return [
                    'id'             => $driver->id,
                    'name'           => $driver->first_name . ' ' . $driver->last_name,
                    'vehicle_plate'  => $driver->vehicle_plate,
                    'vehicle_type'   => $driver->vehicle_type,
                    'vehicle_color'  => $driver->vehicle_color,
                    'driver_status'  => $driver->driver_status,
                    'lat'            => $driver->latestLocation?->lat,
                    'lng'            => $driver->latestLocation?->lng,
                    'recorded_at'    => $driver->latestLocation?->recorded_at,
                ];
            });

        return response()->json($drivers);
    }

    public function activeTrips()
    {
        return response()->json(
            Trip::with('user', 'driver')
                ->whereIn('status', ['accepted', 'in_progress'])
                ->get()
        );
    }
}
