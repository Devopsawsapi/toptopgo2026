<?php

namespace App\Http\Controllers\User;

use App\Events\CallInitiated;
use App\Events\CallEnded;
use App\Http\Controllers\Controller;
use App\Models\Call;
use App\Models\Trip;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * UserCallController — Appels voix in-app côté Client
 *
 * Routes (dans api.php) :
 *   POST  /api/user/calls/{trip_id}/initiate  → appeler le chauffeur
 *   POST  /api/user/calls/{call_id}/answer    → décrocher
 *   POST  /api/user/calls/{call_id}/end       → raccrocher
 *   POST  /api/user/calls/{call_id}/missed    → marquer manqué
 *   GET   /api/user/calls/{trip_id}           → historique
 */
class UserCallController extends Controller
{
    /**
     * Initier un appel vers le chauffeur.
     * → Déclenche IncomingCallBanner sur l'app chauffeur via Pusher.
     */
    public function initiate(Request $request, $tripId): JsonResponse
    {
        $user = Auth::user();

        // Vérifier que le client a une réservation active sur ce trajet
        $trip = Trip::where('id', $tripId)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('bookings', fn($b) => $b->where('user_id', $user->id)
                      ->whereIn('status', ['confirmed', 'accepted', 'paid', 'pending']));
            })
            ->first();

        if (!$trip) {
            return response()->json([
                'success' => false,
                'message' => 'Trajet introuvable ou réservation non active.',
            ], 404);
        }

        if (!$trip->driver_id) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun chauffeur assigné à ce trajet.',
            ], 422);
        }

        // Vérifier qu'il n'y a pas déjà un appel actif
        $active = Call::forTrip($tripId)->active()->first();
        if ($active) {
            return response()->json([
                'success' => false,
                'message' => 'Un appel est déjà en cours.',
                'call_id' => $active->id,
            ], 409);
        }

        $call = Call::create([
            'trip_id'       => $tripId,
            'caller_type'   => get_class($user),                      // App\Models\User\User
            'caller_id'     => $user->id,
            'receiver_type' => \App\Models\Driver\Driver::class,       // App\Models\Driver\Driver
            'receiver_id'   => $trip->driver_id,
            'type'          => $request->input('type', 'audio'),
            'status'        => 'initiated',
            'started_at'    => now(),
        ]);

        // 📡 Pusher → bannière Flutter sur l'app chauffeur
        try {
            broadcast(new CallInitiated($call))->toOthers();
        } catch (\Exception $e) {
            Log::warning('Pusher CallInitiated error: ' . $e->getMessage());
        }

        Log::info('📞 Appel initié par client', [
            'call_id'   => $call->id,
            'user_id'   => $user->id,
            'trip_id'   => $tripId,
            'driver_id' => $trip->driver_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appel initié. En attente de réponse du chauffeur.',
            'call'    => [
                'id'      => $call->id,
                'trip_id' => $call->trip_id,
                'type'    => $call->type,
                'status'  => $call->status,
            ],
        ]);
    }

    /**
     * Décrocher un appel entrant (le chauffeur a appelé le client).
     */
    public function answer(Request $request, $callId): JsonResponse
    {
        $call = Call::find($callId);

        if (!$call || $call->status !== 'initiated') {
            return response()->json(['success' => false, 'message' => 'Appel introuvable.'], 404);
        }

        $call->update(['status' => 'answered', 'started_at' => now()]);

        return response()->json(['success' => true, 'message' => 'Appel décroché.']);
    }

    /**
     * Raccrocher (terminer l'appel).
     */
    public function end(Request $request, $callId): JsonResponse
    {
        $call = Call::with('trip')->find($callId);

        if (!$call) {
            return response()->json(['success' => false, 'message' => 'Appel introuvable.'], 404);
        }

        $duration = $call->started_at
            ? (int) now()->diffInSeconds($call->started_at)
            : 0;

        $call->update([
            'status'           => 'ended',
            'duration_seconds' => $duration,
            'ended_at'         => now(),
        ]);

        try {
            broadcast(new CallEnded($call))->toOthers();
        } catch (\Exception $e) {
            Log::warning('Pusher CallEnded error: ' . $e->getMessage());
        }

        return response()->json([
            'success'  => true,
            'message'  => 'Appel terminé.',
            'duration' => $duration,
        ]);
    }

    /**
     * Appel manqué (pas de réponse après 30s).
     */
    public function missed(Request $request, $callId): JsonResponse
    {
        $call = Call::with('trip')->find($callId);

        if (!$call) {
            return response()->json(['success' => false, 'message' => 'Appel introuvable.'], 404);
        }

        $call->update(['status' => 'missed', 'ended_at' => now()]);

        try {
            broadcast(new CallEnded($call))->toOthers();
        } catch (\Exception $e) {
            Log::warning('Pusher CallEnded error: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Appel marqué manqué.']);
    }

    /**
     * Historique des appels d'un trajet.
     */
    public function history(Request $request, $tripId): JsonResponse
    {
        $user = Auth::user();

        // Vérifier l'accès
        $hasAccess = Trip::where('id', $tripId)->where('user_id', $user->id)->exists()
            || Booking::where('trip_id', $tripId)->where('user_id', $user->id)->exists();

        if (!$hasAccess) {
            return response()->json(['success' => false, 'message' => 'Accès non autorisé.'], 403);
        }

        $calls = Call::forTrip($tripId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($c) => [
                'id'                 => $c->id,
                'type'               => $c->type,
                'status'             => $c->status,
                'duration_seconds'   => $c->duration_seconds,
                'duration_formatted' => $c->duration_formatted,
                'started_at'         => $c->started_at?->toIso8601String(),
                'ended_at'           => $c->ended_at?->toIso8601String(),
                'created_at'         => $c->created_at?->toIso8601String(),
            ]);

        return response()->json(['success' => true, 'calls' => $calls]);
    }
}