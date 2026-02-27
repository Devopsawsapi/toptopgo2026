<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommissionRate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class CommissionRateController extends Controller
{
    public function index(): JsonResponse
    {
        $rates = CommissionRate::with(['country:id,name', 'city:id,name'])
            ->orderBy('country_id')
            ->orderBy('city_id')
            ->get();

        return response()->json($rates);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'country_id' => ['nullable', 'exists:countries,id'],
            'city_id'    => ['nullable', 'exists:cities,id',
                             Rule::unique('commission_rates')->where(fn($q) => $q->where('country_id', $request->country_id))],
            'rate'       => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active'  => ['boolean'],
            'note'       => ['nullable', 'string', 'max:255'],
        ]);

        $rate = CommissionRate::create($data);

        return response()->json($rate->load(['country', 'city']), 201);
    }

    public function update(Request $request, CommissionRate $commissionRate): JsonResponse
    {
        $data = $request->validate([
            'rate'      => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
            'note'      => ['nullable', 'string', 'max:255'],
        ]);

        $commissionRate->update($data);

        return response()->json($commissionRate->load(['country', 'city']));
    }

    public function destroy(CommissionRate $commissionRate): JsonResponse
    {
        $commissionRate->delete();
        return response()->json(['message' => 'Taux supprimé.']);
    }
}