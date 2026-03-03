<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Events\DriverStatusUpdated;  // ✅ Ajouté
use Illuminate\Http\Request;

class DriverStatusController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'status' => 'required|in:online,pause,offline',
        ]);

        $driver = $request->user();
        $driver->status = $request->status;
        $driver->save();

        broadcast(new DriverStatusUpdated($driver));  // ✅ Ajouté

        return response()->json([
            'success' => true,
            'message' => 'Statut mis à jour avec succès.',
            'status'  => $driver->status,
        ]);
    }

    public function show(Request $request)
    {
        $driver = $request->user();

        return response()->json([
            'success' => true,
            'status'  => $driver->status,
        ]);
    }
}