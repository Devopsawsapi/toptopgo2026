<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = DriverProfile::with('user')
            ->latest()
            ->paginate(15);

        return view('admin.drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('admin.drivers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'vehicle_brand' => 'required|string',
            'vehicle_plate_number' => 'required|string',
            'license_number' => 'required|string',
            'country' => 'required|string',
        ]);

        DB::beginTransaction();

        try {

            // 📍 Géolocalisation automatique selon pays
            $coords = $this->getCountryCoordinates($request->country);

            // Upload images
            $avatar = $request->file('avatar')?->store('drivers', 'public');
            $registration = $request->file('vehicle_registration_image')?->store('vehicles', 'public');
            $insurance = $request->file('vehicle_insurance_image')?->store('vehicles', 'public');
            $licenseRecto = $request->file('license_image_recto')?->store('licenses', 'public');
            $licenseVerso = $request->file('license_image_verso')?->store('licenses', 'public');

            // 👤 Création user (désactivé par défaut)
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'phone' => $request->phone,
                'email' => $request->email ?? $request->phone.'@toptopgo.com',
                'password' => Hash::make('password123'),
                'role' => 'driver',
                'is_active' => false, // 🔴 Pas actif tant que KYC pas validé
                'avatar' => $avatar,
                'date_of_birth' => $request->date_of_birth,
                'country_code' => $request->country,
            ]);

            // 🚗 Création profil chauffeur
            DriverProfile::create([
                'user_id' => $user->id,

                // Permis
                'license_number' => $request->license_number,
                'license_expiry' => $request->license_expiry,
                'license_image' => $licenseRecto,
                'id_card_image' => $licenseVerso,

                // Véhicule
                'vehicle_brand' => $request->vehicle_brand,
                'vehicle_model' => $request->vehicle_model,
                'vehicle_plate_number' => $request->vehicle_plate_number,
                'vehicle_registration_image' => $registration,
                'vehicle_insurance_image' => $insurance,
                'seats_available' => $request->seats_available ?? 4,

                // 📍 Position auto
                'latitude' => $coords['lat'],
                'longitude' => $coords['lng'],

                // 🔎 KYC
                'kyc_status' => 'pending'
            ]);

            DB::commit();

            return redirect()
                ->route('admin.drivers.index')
                ->with('success', 'Chauffeur enregistré (KYC en attente) ✅');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->with('error', 'Erreur : '.$e->getMessage());
        }
    }

    // 📍 Mapping Pays → Coordonnées
    private function getCountryCoordinates($country)
    {
        $countries = [

            'France' => ['lat' => 48.8566, 'lng' => 2.3522],
            'Belgique' => ['lat' => 50.8503, 'lng' => 4.3517],
            'Cameroun' => ['lat' => 3.8480, 'lng' => 11.5021],
            'Congo' => ['lat' => -4.2634, 'lng' => 15.2429],
            'Canada' => ['lat' => 45.4215, 'lng' => -75.6972],
            'Sénégal' => ['lat' => 14.7167, 'lng' => -17.4677],
            'Côte d\'Ivoire' => ['lat' => 5.3599, 'lng' => -4.0083],

        ];

        return $countries[$country] ?? ['lat' => 0, 'lng' => 0];
    }

    public function show(DriverProfile $driver)
    {
        $driver->load('user');
        return view('admin.drivers.show', compact('driver'));
    }

    public function toggleStatus(DriverProfile $driver)
    {
        // 🚨 Activation seulement si KYC validé
        if ($driver->kyc_status !== 'approved') {
            return back()->with('error', 'Impossible d’activer : KYC non validé');
        }

        $user = $driver->user;
        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Statut mis à jour');
    }
}
