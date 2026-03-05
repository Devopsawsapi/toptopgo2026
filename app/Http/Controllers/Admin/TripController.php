<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;

class TripController extends Controller
{

    public function index(Request $request)
    {
        $query = Trip::with(['driver','vehicle','bookings']);

        // Recherche
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('departure','like','%'.$request->search.'%')
                  ->orWhere('destination','like','%'.$request->search.'%');
            });
        }

        // Filtre statut
        if ($request->status) {
            $query->where('status',$request->status);
        }

        // Filtre date début
        if ($request->from) {
            $query->whereDate('departure_date','>=',$request->from);
        }

        // Filtre date fin
        if ($request->to) {
            $query->whereDate('departure_date','<=',$request->to);
        }

        $trips = $query->latest()->paginate(10);

        // Statistiques
        $stats = [
            'total' => Trip::count(),
            'pending' => Trip::where('status','pending')->count(),
            'in_progress' => Trip::where('status','in_progress')->count(),
            'completed' => Trip::where('status','completed')->count(),
            'cancelled' => Trip::where('status','cancelled')->count(),
        ];

        return view('admin.trips.index', compact('trips','stats'));
    }


    public function show($id)
    {
        $trip = Trip::with(['driver','vehicle','bookings'])->findOrFail($id);

        return view('admin.trips.show',compact('trip'));
    }


    public function detail($id)
    {
        $trip = Trip::with(['driver','vehicle','bookings'])->findOrFail($id);

        return response()->json($trip);
    }

}