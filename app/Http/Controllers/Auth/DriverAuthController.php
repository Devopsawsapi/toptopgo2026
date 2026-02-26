<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Driver\Driver;
use App\Models\Wallet;

class DriverAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'birth_date'   => 'required|date',
            'birth_place'  => 'required|string',
            'country_birth'=> 'required|string',
            'phone'        => 'required|string|unique:drivers,phone',
            'password'     => 'required|string|min:6',
        ]);

        $driver = Driver::create([
            'first_name'    => $request->first_name,
            'last_name'     => $request->last_name,
            'birth_date'    => $request->birth_date,
            'birth_place'   => $request->birth_place,
            'country_birth' => $request->country_birth,
            'phone'         => $request->phone,
            'password'      => Hash::make($request->password),
            'status'        => 'pending',
        ]);

        // Créer le wallet automatiquement
        Wallet::create(['driver_id' => $driver->id, 'balance' => 0, 'currency' => 'XAF']);

        $token = $driver->createToken('driver-token')->plainTextToken;

        return response()->json(['token' => $token, 'driver' => $driver], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        $driver = Driver::where('phone', $request->phone)->first();

        if (!$driver || !Hash::check($request->password, $driver->password)) {
            throw ValidationException::withMessages([
                'phone' => ['Identifiants incorrects.'],
            ]);
        }

        $token = $driver->createToken('driver-token')->plainTextToken;

        return response()->json(['token' => $token, 'driver' => $driver]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnecté avec succès.']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->load('wallet'));
    }
}
