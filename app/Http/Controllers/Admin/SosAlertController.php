<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SosAlert;
use App\Models\User\User;
use App\Models\Driver\Driver;
use Carbon\Carbon;

class SosAlertController extends Controller
{
    /**
     * Liste des alertes SOS
     */
    public function index(Request $request)
    {
        $query = SosAlert::with(['sender', 'trip', 'treatedBy'])
            ->latest();

        // Filtre statut
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filtre type expéditeur
        if ($request->filled('sender_type')) {
            $type = $request->sender_type === 'driver'
                ? \App\Models\Driver\Driver::class
                : \App\Models\User\User::class;
            $query->where('sender_type', $type);
        }

        // Filtre date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $alerts = $query->paginate(20);

        // Stats
        $totalActive   = SosAlert::where('status', 'active')->count();
        $totalTreated  = SosAlert::where('status', 'treated')->count();
        $totalToday    = SosAlert::whereDate('created_at', today())->count();
        $totalAll      = SosAlert::count();

        return view('admin.sos.index', compact(
            'alerts', 'totalActive', 'totalTreated', 'totalToday', 'totalAll'
        ));
    }

    /**
     * Détail d'une alerte SOS
     */
    public function show($id)
    {
        $alert = SosAlert::with(['sender', 'trip.driver', 'trip.user', 'treatedBy'])
            ->findOrFail($id);

        return view('admin.sos.show', compact('alert'));
    }

    /**
     * Marquer une alerte comme traitée
     */
    public function treat(Request $request, $id)
    {
        $alert = SosAlert::findOrFail($id);

        if ($alert->status === 'treated') {
            return back()->with('error', 'Cette alerte a déjà été traitée.');
        }

        $alert->update([
            'status'     => 'treated',
            'treated_by' => session('admin_id'),
            'treated_at' => now(),
        ]);

        return back()->with('success', 'Alerte SOS marquée comme traitée.');
    }

    /**
     * Traitement en masse
     */
    public function treatAll()
    {
        $count = SosAlert::where('status', 'active')->count();

        SosAlert::where('status', 'active')->update([
            'status'     => 'treated',
            'treated_by' => session('admin_id'),
            'treated_at' => now(),
        ]);

        return back()->with('success', "{$count} alerte(s) marquée(s) comme traitée(s).");
    }

    /**
     * Supprimer une alerte
     */
    public function destroy($id)
    {
        SosAlert::findOrFail($id)->delete();
        return back()->with('success', 'Alerte supprimée.');
    }

    /**
     * API JSON : alertes actives en temps réel
     */
    public function live()
    {
        $alerts = SosAlert::where('status', 'active')
            ->with('sender')
            ->latest()
            ->get()
            ->map(fn($a) => [
                'id'          => $a->id,
                'sender_name' => ($a->sender->first_name ?? '') . ' ' . ($a->sender->last_name ?? ''),
                'sender_type' => str_contains($a->sender_type, 'Driver') ? 'driver' : 'user',
                'message'     => $a->message,
                'lat'         => (float) $a->lat,
                'lng'         => (float) $a->lng,
                'created_at'  => $a->created_at->diffForHumans(),
                'trip_id'     => $a->trip_id,
            ]);

        return response()->json(['alerts' => $alerts]);
    }
}