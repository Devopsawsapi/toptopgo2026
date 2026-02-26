<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    /**
     * Liste des chauffeurs
     */
    public function index(Request $request)
    {
        $query = Driver::orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->search . '%')
                  ->orWhere('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $drivers = $query->paginate(15);

        return view('admin.drivers.index', compact('drivers'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        return view('admin.drivers.create');
    }

    /**
     * Enregistrer un nouveau chauffeur
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name'  => 'required|string|max:100',
            'last_name'   => 'required|string|max:100',
            'birth_date'  => 'required|date',
            'birth_place' => 'required|string|max:100',
            'country_birth' => 'required|string|max:100',
            'phone'       => 'required|string|unique:drivers,phone',
            'password'    => 'required|string|min:8|confirmed',
            'vehicle_plate' => 'nullable|string|unique:drivers,vehicle_plate',
            'profile_photo'       => 'nullable|image|max:2048',
            'id_card_front'       => 'nullable|file|max:5120',
            'id_card_back'        => 'nullable|file|max:5120',
            'license_front'       => 'nullable|file|max:5120',
            'license_back'        => 'nullable|file|max:5120',
            'vehicle_registration'=> 'nullable|file|max:5120',
            'insurance'           => 'nullable|file|max:5120',
        ]);

        $data = $request->except(['password', 'password_confirmation',
            'profile_photo', 'id_card_front', 'id_card_back',
            'license_front', 'license_back', 'vehicle_registration', 'insurance']);

        $data['password'] = Hash::make($request->password);

        // Upload des fichiers
        $fileFields = ['profile_photo', 'id_card_front', 'id_card_back',
                       'license_front', 'license_back', 'vehicle_registration', 'insurance'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('drivers/' . $field, 'public');
            }
        }

        Driver::create($data);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Chauffeur créé avec succès.');
    }

    /**
     * Détail d'un chauffeur
     */
    public function show($id)
    {
        $driver = Driver::findOrFail($id);
        return view('admin.drivers.show', compact('driver'));
    }

    /**
     * Formulaire de modification
     */
    public function edit($id)
    {
        $driver = Driver::findOrFail($id);
        return view('admin.drivers.edit', compact('driver'));
    }

    /**
     * Mettre à jour un chauffeur
     */
    public function update(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'phone'        => 'required|string|unique:drivers,phone,' . $id,
            'vehicle_plate'=> 'nullable|string|unique:drivers,vehicle_plate,' . $id,
            'password'     => 'nullable|string|min:8|confirmed',
        ]);

        $data = $request->except(['password', 'password_confirmation',
            'profile_photo', 'id_card_front', 'id_card_back',
            'license_front', 'license_back', 'vehicle_registration', 'insurance']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // Upload des fichiers
        $fileFields = ['profile_photo', 'id_card_front', 'id_card_back',
                       'license_front', 'license_back', 'vehicle_registration', 'insurance'];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('drivers/' . $field, 'public');
            }
        }

        $driver->update($data);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Chauffeur modifié avec succès.');
    }

    /**
     * Approuver un chauffeur (KYC)
     */
    public function approve($id)
    {
        $driver = Driver::findOrFail($id);
        $driver->update(['status' => 'approved']);

        return back()->with('success', $driver->first_name . ' a été approuvé.');
    }

    /**
     * Rejeter un chauffeur (KYC)
     */
    public function reject($id)
    {
        $driver = Driver::findOrFail($id);
        $driver->update(['status' => 'rejected']);

        return back()->with('success', $driver->first_name . ' a été rejeté.');
    }

    /**
     * Suspendre un chauffeur
     */
    public function suspend($id)
    {
        $driver = Driver::findOrFail($id);
        $driver->update(['status' => 'suspended']);

        return back()->with('success', $driver->first_name . ' a été suspendu.');
    }

    /**
     * Réactiver un chauffeur suspendu
     */
    public function activate($id)
    {
        $driver = Driver::findOrFail($id);
        $driver->update(['status' => 'approved']);

        return back()->with('success', $driver->first_name . ' a été réactivé.');
    }

    /**
     * Supprimer un chauffeur
     */
    public function destroy($id)
    {
        $driver = Driver::findOrFail($id);
        $driver->tokens()->delete();
        $driver->delete();

        return back()->with('success', 'Chauffeur supprimé.');
    }
}