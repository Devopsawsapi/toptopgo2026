<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

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
                  ->orWhere('last_name',  'like', '%' . $request->search . '%')
                  ->orWhere('phone',      'like', '%' . $request->search . '%');
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
     * + géocodage automatique ville/pays → lat/lng
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name'           => 'required|string|max:100',
            'last_name'            => 'required|string|max:100',
            'birth_date'           => 'required|date',
            'birth_place'          => 'required|string|max:100',
            'country_birth'        => 'required|string|max:100',
            'phone'                => 'required|string|unique:drivers,phone',
            'password'             => 'required|string|min:8|confirmed',
            'vehicle_plate'        => 'nullable|string|unique:drivers,vehicle_plate',
            'profile_photo'        => 'nullable|image|max:2048',
            'id_card_front'        => 'nullable|file|max:5120',
            'id_card_back'         => 'nullable|file|max:5120',
            'license_front'        => 'nullable|file|max:5120',
            'license_back'         => 'nullable|file|max:5120',
            'vehicle_registration' => 'nullable|file|max:5120',
            'insurance'            => 'nullable|file|max:5120',
        ]);

        $data = $request->except([
            'password', 'password_confirmation',
            'profile_photo', 'id_card_front', 'id_card_back',
            'license_front', 'license_back', 'vehicle_registration', 'insurance',
        ]);

        $data['password'] = Hash::make($request->password);

        // ── Upload fichiers ───────────────────────────────────────
        foreach ($this->fileFields() as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('drivers/' . $field, 'public');
            }
        }

        // ── Géocodage automatique ville + pays → lat/lng ──────────
        $coords = $this->geocode($request->vehicle_city, $request->vehicle_country);
        if ($coords) {
            $data['vehicle_lat'] = $coords['lat'];
            $data['vehicle_lng'] = $coords['lng'];
        }

        Driver::create($data);

        return redirect()->route('admin.drivers.index')
            ->with('success', 'Chauffeur créé avec succès.' .
                ($coords ? ' Position GPS détectée automatiquement.' : ' ⚠️ Position GPS non détectée (ville/pays manquant).'));
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
     * + regéocodage si ville/pays changé
     */
    public function update(Request $request, $id)
    {
        $driver = Driver::findOrFail($id);

        $request->validate([
            'first_name'    => 'required|string|max:100',
            'last_name'     => 'required|string|max:100',
            'phone'         => 'required|string|unique:drivers,phone,' . $id,
            'vehicle_plate' => 'nullable|string|unique:drivers,vehicle_plate,' . $id,
            'password'      => 'nullable|string|min:8|confirmed',
        ]);

        $data = $request->except([
            'password', 'password_confirmation',
            'profile_photo', 'id_card_front', 'id_card_back',
            'license_front', 'license_back', 'vehicle_registration', 'insurance',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        // ── Upload fichiers ───────────────────────────────────────
        foreach ($this->fileFields() as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('drivers/' . $field, 'public');
            }
        }

        // ── Regéocodage si ville ou pays a changé ─────────────────
        $cityChanged    = $request->vehicle_city    !== $driver->vehicle_city;
        $countryChanged = $request->vehicle_country !== $driver->vehicle_country;

        if ($cityChanged || $countryChanged || (!$driver->vehicle_lat && !$driver->vehicle_lng)) {
            $coords = $this->geocode($request->vehicle_city, $request->vehicle_country);
            if ($coords) {
                $data['vehicle_lat'] = $coords['lat'];
                $data['vehicle_lng'] = $coords['lng'];
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

    // ================================================================
    // HELPERS PRIVÉS
    // ================================================================

    /**
     * Géocode une ville + pays en lat/lng
     * Via table locale (Afrique Centrale priorité) + fallback Nominatim
     */
    private function geocode(?string $city, ?string $country): ?array
    {
        if (empty($city) && empty($country)) return null;

        // ── Table locale Afrique Centrale & Ouest ────────────────
        $localDb = [
            // Congo Brazzaville
            'brazzaville'         => ['lat' => -4.2661,  'lng' => 15.2832],
            'pointe-noire'        => ['lat' => -4.7769,  'lng' => 11.8635],
            'pointe noire'        => ['lat' => -4.7769,  'lng' => 11.8635],
            'dolisie'             => ['lat' => -4.1987,  'lng' => 12.6670],
            'nkayi'               => ['lat' => -4.1757,  'lng' => 13.2836],
            'impfondo'            => ['lat' =>  1.6177,  'lng' => 18.0669],
            'ouesso'              => ['lat' =>  1.6136,  'lng' => 16.0503],
            'sibiti'              => ['lat' => -3.6833,  'lng' => 13.3500],
            'owando'              => ['lat' => -0.4833,  'lng' => 15.9000],
            // Cameroun
            'douala'              => ['lat' =>  4.0511,  'lng' =>  9.7679],
            'yaoundé'             => ['lat' =>  3.8480,  'lng' => 11.5021],
            'yaounde'             => ['lat' =>  3.8480,  'lng' => 11.5021],
            'bafoussam'           => ['lat' =>  5.4764,  'lng' => 10.4214],
            'garoua'              => ['lat' =>  9.3010,  'lng' => 13.3980],
            'bamenda'             => ['lat' =>  5.9597,  'lng' => 10.1460],
            'maroua'              => ['lat' => 10.5910,  'lng' => 14.3158],
            'ngaoundéré'          => ['lat' =>  7.3220,  'lng' => 13.5840],
            'ngaoundere'          => ['lat' =>  7.3220,  'lng' => 13.5840],
            'bertoua'             => ['lat' =>  4.5774,  'lng' => 13.6844],
            'ebolowa'             => ['lat' =>  2.9000,  'lng' => 11.1500],
            'kribi'               => ['lat' =>  2.9395,  'lng' =>  9.9072],
            'limbe'               => ['lat' =>  4.0167,  'lng' =>  9.2000],
            'buea'                => ['lat' =>  4.1543,  'lng' =>  9.2412],
            // Gabon
            'libreville'          => ['lat' =>  0.3901,  'lng' =>  9.4544],
            'port-gentil'         => ['lat' => -0.7193,  'lng' =>  8.7815],
            'port gentil'         => ['lat' => -0.7193,  'lng' =>  8.7815],
            'franceville'         => ['lat' => -1.6330,  'lng' => 13.5830],
            'oyem'                => ['lat' =>  1.5997,  'lng' => 11.5790],
            'moanda'              => ['lat' => -1.5667,  'lng' => 13.2000],
            'lambaréné'           => ['lat' => -0.6942,  'lng' => 10.2348],
            'lambarene'           => ['lat' => -0.6942,  'lng' => 10.2348],
            // RDC
            'kinshasa'            => ['lat' => -4.3217,  'lng' => 15.3222],
            'lubumbashi'          => ['lat' => -11.6609, 'lng' => 27.4794],
            'goma'                => ['lat' => -1.6793,  'lng' => 29.2228],
            'bukavu'              => ['lat' => -2.4977,  'lng' => 28.8597],
            'kisangani'           => ['lat' =>  0.5153,  'lng' => 25.1960],
            'mbuji-mayi'          => ['lat' => -6.1360,  'lng' => 23.5900],
            'kananga'             => ['lat' => -5.8960,  'lng' => 22.4170],
            'matadi'              => ['lat' => -5.8200,  'lng' => 13.4600],
            // Centrafrique
            'bangui'              => ['lat' =>  4.3612,  'lng' => 18.5550],
            'bimbo'               => ['lat' =>  4.2560,  'lng' => 18.4130],
            'berbérati'           => ['lat' =>  4.2614,  'lng' => 15.7878],
            'berberati'           => ['lat' =>  4.2614,  'lng' => 15.7878],
            // Tchad
            "n'djamena"           => ['lat' => 12.1048,  'lng' => 15.0440],
            'ndjamena'            => ['lat' => 12.1048,  'lng' => 15.0440],
            'moundou'             => ['lat' =>  8.5667,  'lng' => 16.0833],
            'sarh'                => ['lat' =>  9.1450,  'lng' => 18.3900],
            // Côte d'Ivoire
            'abidjan'             => ['lat' =>  5.3600,  'lng' => -4.0083],
            'yamoussoukro'        => ['lat' =>  6.8276,  'lng' => -5.2893],
            'bouaké'              => ['lat' =>  7.6906,  'lng' => -5.0289],
            'bouake'              => ['lat' =>  7.6906,  'lng' => -5.0289],
            // Sénégal
            'dakar'               => ['lat' => 14.7167,  'lng' => -17.4677],
            'thiès'               => ['lat' => 14.7833,  'lng' => -16.9167],
            'thies'               => ['lat' => 14.7833,  'lng' => -16.9167],
            'saint-louis'         => ['lat' => 16.0179,  'lng' => -16.4896],
            // Mali
            'bamako'              => ['lat' => 12.6392,  'lng' => -8.0029],
            // Burkina Faso
            'ouagadougou'         => ['lat' => 12.3647,  'lng' => -1.5332],
            // Niger
            'niamey'              => ['lat' => 13.5137,  'lng' =>  2.1098],
            // Bénin
            'cotonou'             => ['lat' =>  6.3654,  'lng' =>  2.4183],
            'porto-novo'          => ['lat' =>  6.3676,  'lng' =>  2.4252],
            // Togo
            'lomé'                => ['lat' =>  6.1375,  'lng' =>  1.2123],
            'lome'                => ['lat' =>  6.1375,  'lng' =>  1.2123],
            // Ghana
            'accra'               => ['lat' =>  5.5600,  'lng' => -0.2057],
            // Nigeria
            'lagos'               => ['lat' =>  6.5244,  'lng' =>  3.3792],
            'abuja'               => ['lat' =>  9.0765,  'lng' =>  7.3986],
            'kano'                => ['lat' => 12.0022,  'lng' =>  8.5920],
            // Guinée Équatoriale
            'malabo'              => ['lat' =>  3.7523,  'lng' =>  8.7741],
            'bata'                => ['lat' =>  1.8639,  'lng' =>  9.7656],
            // São Tomé
            'são tomé'            => ['lat' =>  0.3365,  'lng' =>  6.7273],
            'sao tome'            => ['lat' =>  0.3365,  'lng' =>  6.7273],
            // Angola
            'luanda'              => ['lat' => -8.8368,  'lng' => 13.2343],
            // Rwanda
            'kigali'              => ['lat' => -1.9441,  'lng' => 30.0619],
            // Burundi
            'bujumbura'           => ['lat' => -3.3822,  'lng' => 29.3644],
            // Ouganda
            'kampala'             => ['lat' =>  0.3476,  'lng' => 32.5825],
            // Kenya
            'nairobi'             => ['lat' => -1.2921,  'lng' => 36.8219],
            // Tanzanie
            'dar es salaam'       => ['lat' => -6.7924,  'lng' => 39.2083],
            'dodoma'              => ['lat' => -6.1722,  'lng' => 35.7395],
            // Éthiopie
            'addis abeba'         => ['lat' =>  9.0250,  'lng' => 38.7469],
            'addis-abeba'         => ['lat' =>  9.0250,  'lng' => 38.7469],
        ];

        // Chercher par ville d'abord
        $cityKey = mb_strtolower(trim($city ?? ''));
        if (isset($localDb[$cityKey])) {
            return $localDb[$cityKey];
        }

        // Chercher par pays (capitale par défaut)
        $capitalByCountry = [
            'congo'                    => 'brazzaville',
            'congo brazzaville'        => 'brazzaville',
            'republic of the congo'    => 'brazzaville',
            'cameroun'                 => 'yaoundé',
            'cameroon'                 => 'yaoundé',
            'gabon'                    => 'libreville',
            'rdc'                      => 'kinshasa',
            'congo kinshasa'           => 'kinshasa',
            'democratic republic of the congo' => 'kinshasa',
            'centrafrique'             => 'bangui',
            'central african republic' => 'bangui',
            'tchad'                    => "n'djamena",
            'chad'                     => "n'djamena",
            "côte d'ivoire"            => 'abidjan',
            'cote d\'ivoire'           => 'abidjan',
            'ivory coast'              => 'abidjan',
            'sénégal'                  => 'dakar',
            'senegal'                  => 'dakar',
            'mali'                     => 'bamako',
            'burkina faso'             => 'ouagadougou',
            'niger'                    => 'niamey',
            'bénin'                    => 'cotonou',
            'benin'                    => 'cotonou',
            'togo'                     => 'lomé',
            'ghana'                    => 'accra',
            'nigeria'                  => 'abuja',
            'guinée équatoriale'       => 'malabo',
            'equatorial guinea'        => 'malabo',
            'angola'                   => 'luanda',
            'rwanda'                   => 'kigali',
            'burundi'                  => 'bujumbura',
            'ouganda'                  => 'kampala',
            'uganda'                   => 'kampala',
            'kenya'                    => 'nairobi',
            'tanzanie'                 => 'dar es salaam',
            'tanzania'                 => 'dar es salaam',
            'éthiopie'                 => 'addis abeba',
            'ethiopia'                 => 'addis abeba',
        ];

        $countryKey = mb_strtolower(trim($country ?? ''));
        if (isset($capitalByCountry[$countryKey])) {
            $capital = $capitalByCountry[$countryKey];
            if (isset($localDb[$capital])) {
                return $localDb[$capital];
            }
        }

        // Fallback Nominatim si réseau disponible
        $query = trim(implode(', ', array_filter([$city, $country])));
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(4)
                ->withHeaders(['User-Agent' => 'TopTopGo-Admin/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q'      => $query,
                    'format' => 'json',
                    'limit'  => 1,
                ]);

            if ($response->ok() && count($response->json()) > 0) {
                $result = $response->json()[0];
                return [
                    'lat' => (float) $result['lat'],
                    'lng' => (float) $result['lon'],
                ];
            }
        } catch (\Exception $e) {
            // Silencieux
        }

        return null;
    }

    /**
     * Liste des champs fichiers
     */
    private function fileFields(): array
    {
        return [
            'profile_photo', 'id_card_front', 'id_card_back',
            'license_front', 'license_back', 'vehicle_registration', 'insurance',
        ];
    }
}