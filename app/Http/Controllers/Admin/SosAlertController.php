<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SosAlert;

class SosAlertController extends Controller
{
    public function index(Request $request)
    {
        $query = SosAlert::with('sender', 'trip');
        if ($request->status) $query->where('status', $request->status);
        return response()->json($query->latest()->paginate(20));
    }

    public function treat(Request $request, $id)
    {
        $sos = SosAlert::findOrFail($id);
        $sos->update([
            'status'     => 'treated',
            'treated_by' => $request->user()->id,
            'treated_at' => now(),
        ]);
        return response()->json(['message' => 'Alerte SOS traitée.', 'sos' => $sos]);
    }
}
