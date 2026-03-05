<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserPaymentController extends Controller
{
    // ── Paiement Mobile Money ────────────────────────────────────────
    public function mobileMoney(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'phone'      => 'required|string',
            'provider'   => 'required|in:mtn,airtel,orange',
        ]);

        $booking = Booking::with('trip')
            ->where('user_id', Auth::id())
            ->findOrFail($request->booking_id);

        // Vérifier que la réservation est acceptée
        if ($booking->status !== 'accepted') {
            return response()->json([
                'success' => false,
                'message' => 'La réservation doit être acceptée avant le paiement (statut actuel: ' . $booking->status . ').',
            ], 422);
        }

        // Vérifier si déjà payé
        $alreadyPaid = Payment::where('booking_id', $booking->id)
            ->where('status', 'success')
            ->exists();

        if ($alreadyPaid) {
            return response()->json([
                'success' => false,
                'message' => 'Cette réservation a déjà été payée.',
            ], 422);
        }

        // Créer le paiement
        $payment = Payment::create([
            'user_id'         => Auth::id(),
            'trip_id'         => $booking->trip_id,
            'driver_id'       => $booking->trip->driver_id,
            'booking_id'      => $booking->id,
            'amount'          => $booking->amount,
            'commission'      => $booking->amount * 0.10, // 10% commission
            'driver_net'      => $booking->amount * 0.90,
            'method'          => 'mobile_money',
            'status'          => 'pending',
            'transaction_ref' => 'TXN-' . strtoupper(Str::random(10)),
            'country'         => 'CG',
            'city'            => $booking->trip->departure_city ?? 'N/A',
            'paid_at'         => null,
        ]);

        // ── Ici tu intègres ton API Mobile Money (MTN, Airtel, Orange) ──
        // $response = MobileMoneyService::pay($request->phone, $booking->amount);
        // Pour l'instant on simule un succès :
        $payment->update([
            'status'  => 'success',
            'paid_at' => now(),
        ]);

        // Mettre à jour le statut de la réservation
        $booking->update(['status' => 'paid']);

        return response()->json([
            'success' => true,
            'message' => 'Paiement effectué avec succès.',
            'data'    => [
                'payment'         => $payment,
                'transaction_ref' => $payment->transaction_ref,
                'amount'          => $payment->amount,
                'status'          => $payment->status,
            ],
        ]);
    }

    // ── Paiement Stripe ──────────────────────────────────────────────
    public function stripe(Request $request)
    {
        $request->validate([
            'booking_id'       => 'required|exists:bookings,id',
            'payment_method_id'=> 'required|string', // token Stripe
        ]);

        $booking = Booking::with('trip')
            ->where('user_id', Auth::id())
            ->findOrFail($request->booking_id);

        if ($booking->status !== 'accepted') {
            return response()->json([
                'success' => false,
                'message' => 'La réservation doit être acceptée avant le paiement.',
            ], 422);
        }

        $payment = Payment::create([
            'user_id'         => Auth::id(),
            'trip_id'         => $booking->trip_id,
            'driver_id'       => $booking->trip->driver_id,
            'booking_id'      => $booking->id,
            'amount'          => $booking->amount,
            'commission'      => $booking->amount * 0.10,
            'driver_net'      => $booking->amount * 0.90,
            'method'          => 'stripe',
            'status'          => 'pending',
            'transaction_ref' => 'STR-' . strtoupper(Str::random(10)),
            'country'         => 'CG',
            'city'            => $booking->trip->departure_city ?? 'N/A',
            'paid_at'         => null,
        ]);

        // ── Ici tu intègres Stripe ───────────────────────────────────
        // $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
        // $intent = $stripe->paymentIntents->create([...]);
        // Pour l'instant on simule :
        $payment->update([
            'status'  => 'success',
            'paid_at' => now(),
        ]);

        $booking->update(['status' => 'paid']);

        return response()->json([
            'success' => true,
            'message' => 'Paiement Stripe effectué avec succès.',
            'data'    => [
                'payment'         => $payment,
                'transaction_ref' => $payment->transaction_ref,
                'amount'          => $payment->amount,
                'status'          => $payment->status,
            ],
        ]);
    }

    // ── Statut d'un paiement ─────────────────────────────────────────
    public function status(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $payment = Payment::where('booking_id', $request->booking_id)
            ->where('user_id', Auth::id())
            ->latest()
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => [
                'transaction_ref' => $payment->transaction_ref,
                'amount'          => $payment->amount,
                'method'          => $payment->method,
                'status'          => $payment->status,
                'paid_at'         => $payment->paid_at,
            ],
        ]);
    }
}