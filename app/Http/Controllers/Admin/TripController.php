<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TripController extends Controller
{
    /**
     * GET /admin/trips
     * Liste paginée avec filtres + stats
     */
    public function index(Request $request)
    {
        $query = Trip::with([
            'driver',
            'driver.vehicle',
            'vehicle',
            'client',
            'bookings',
            'bookings.client',
            'payment',
        ])->latest();

        // ── Filtre recherche ──────────────────────────────────────────────
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('departure',   'like', "%{$search}%")
                  ->orWhere('destination', 'like', "%{$search}%")
                  ->orWhereHas('driver', fn($d) =>
                        $d->where('name',       'like', "%{$search}%")
                          ->orWhere('first_name', 'like', "%{$search}%")
                          ->orWhere('phone',      'like', "%{$search}%")
                  );
            });
        }

        // ── Filtre statut ─────────────────────────────────────────────────
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // ── Filtre dates ──────────────────────────────────────────────────
        if ($from = $request->input('from')) {
            $query->whereDate('departure_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('departure_date', '<=', $to);
        }

        // ── Export CSV ────────────────────────────────────────────────────
        if ($request->input('export') === 'csv') {
            return $this->exportCsv($query->get());
        }

        $trips = $query->paginate(20);

        // ── Stats badges en-tête ──────────────────────────────────────────
        $stats = [
            'total'       => Trip::count(),
            'pending'     => Trip::where('status', 'pending')->count(),
            'in_progress' => Trip::where('status', 'in_progress')->count(),
            'completed'   => Trip::where('status', 'completed')->count(),
            'cancelled'   => Trip::where('status', 'cancelled')->count(),
        ];

        return view('admin.trips', compact('trips', 'stats'));
    }

    /**
     * GET /admin/trips/{id}
     * Détails complets d'un trajet (JSON)
     */
    public function show($id)
    {
        $trip = Trip::with([
            'driver',
            'driver.vehicle',
            'vehicle',
            'client',
            'bookings',
            'bookings.client',
            'payment',
            'messages',
        ])->findOrFail($id);

        // Merge vehicle depuis driver si pas de relation directe
        if (! $trip->vehicle && $trip->driver?->vehicle) {
            $trip->setRelation('vehicle', $trip->driver->vehicle);
        }

        return response()->json($trip);
    }

    /**
     * GET /admin/trips/{id}/detail
     * Alias AJAX pour le modal Blade → appelle show()
     */
    public function detail($id)
    {
        return $this->show($id);
    }

    /**
     * Export CSV
     */
    private function exportCsv($trips)
    {
        $filename = 'trajets_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($trips) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8 pour Excel
            fputs($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'ID', 'Chauffeur', 'Téléphone Chauffeur',
                'Départ', 'Destination',
                'Date départ', 'Heure départ',
                'Marque Véhicule', 'Modèle', 'Immatriculation', 'Type véhicule',
                'Places dispo', 'Prix/place (FCFA)',
                'Bagages inclus', 'Frais excédent (FCFA)',
                'Statut',
                'Réservations confirmées', 'Réservations en attente', 'Réservations rejetées',
                'Revenu total (FCFA)',
                'Créé le',
            ], ';');

            foreach ($trips as $trip) {
                $driver  = $trip->driver;
                $vehicle = $trip->vehicle ?? $driver?->vehicle;

                $driverName  = $driver?->name
                    ?? trim(($driver?->first_name ?? '') . ' ' . ($driver?->last_name ?? ''))
                    ?: '—';
                $driverPhone = $driver?->phone ?? $driver?->telephone ?? '—';

                $brand = $vehicle?->brand ?? $vehicle?->make ?? '—';
                $model = $vehicle?->model ?? '—';
                $plate = $vehicle?->plate ?? $vehicle?->license_plate ?? '—';

                $bookings  = $trip->bookings ?? collect();
                $confirmed = $bookings->whereIn('status', ['confirmed', 'accepted'])->count();
                $pending   = $bookings->where('status', 'pending')->count();
                $rejected  = $bookings->whereIn('status', ['rejected', 'cancelled'])->count();
                $revenue   = $bookings
                    ->whereIn('status', ['confirmed', 'accepted', 'completed'])
                    ->sum(fn($b) => floatval($b->amount ?? $b->total_price ?? 0));

                fputcsv($handle, [
                    $trip->id,
                    $driverName,
                    $driverPhone,
                    $trip->departure,
                    $trip->destination,
                    $trip->departure_date ? Carbon::parse($trip->departure_date)->format('d/m/Y') : '—',
                    $trip->departure_time ? Carbon::parse($trip->departure_time)->format('H:i')   : '—',
                    $brand,
                    $model,
                    $plate,
                    $trip->vehicle_type ?? '—',
                    $trip->available_seats   ?? '—',
                    $trip->price_per_seat    ?? '—',
                    $trip->luggage_included  ?? 0,
                    $trip->extra_luggage_fee ?? 0,
                    $trip->status,
                    $confirmed,
                    $pending,
                    $rejected,
                    number_format($revenue, 0, '.', ''),
                    $trip->created_at?->format('d/m/Y H:i'),
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
