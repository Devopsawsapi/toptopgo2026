<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SosAlert;

class SosAlertController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'lat'      => 'nullable|numeric',
            'lng'      => 'nullable|numeric',
            'trip_id'  => 'nullable|exists:trips,id',
            'message'  => 'nullable|string',
        ]);

        $sender = $request->user();

        $sos = SosAlert::create([
            'sender_type' => get_class($sender),
            'sender_id'   => $sender->id,
            'trip_id'     => $request->trip_id,
            'lat'         => $request->lat,
            'lng'         => $request->lng,
            'message'     => $request->message,
            'status'      => 'active',
        ]);

        return response()->json(['message' => 'Alerte SOS envoyée.', 'sos' => $sos], 201);
    }
}
