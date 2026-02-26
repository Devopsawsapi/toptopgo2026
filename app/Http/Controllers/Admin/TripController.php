<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with('user', 'driver');

        if ($request->status) $query->where('status', $request->status);
        if ($request->from)   $query->whereDate('created_at', '>=', $request->from);
        if ($request->to)     $query->whereDate('created_at', '<=', $request->to);

        return response()->json($query->latest()->paginate(20));
    }

    public function show($id)
    {
        return response()->json(Trip::with('user', 'driver', 'payment', 'messages')->findOrFail($id));
    }
}
