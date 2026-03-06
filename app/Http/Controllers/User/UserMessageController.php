<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Trip;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMessageController extends Controller
{
    // ── Patterns de modération (miroir côté serveur) ────────────────
    // Même logique que dans le Flutter, mais autoritaire côté backend.

    private static array $THREAT_KEYWORDS = [
        'je vais te tuer', 'je te tue', 'mort à', 'je vais te retrouver',
        'tu vas mourir', 'je te retrouve', 'je te fracasse', 'on se retrouve',
        'gare à toi', 'tu vas regretter',
    ];

    /**
     * Retourne null si le message est autorisé,
     * sinon retourne la raison du blocage.
     */
    private function moderateContent(string $text): ?string
    {
        $lower = mb_strtolower($text);

        // 1. Numéro de téléphone
        if (preg_match('/(\+?\d[\d\s\-\.\(\)]{7,}\d)/', preg_replace('/\s+/', '', $text))) {
            return 'Les numéros de téléphone sont interdits dans le chat.';
        }

        // 2. URL / liens
        if (preg_match('/(https?:\/\/|www\.|\.com|\.fr|\.net|\.org|bit\.ly|t\.me|wa\.me)/i', $text)) {
            return 'Les liens externes sont interdits.';
        }

        // 3. Email
        if (preg_match('/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/', $text)) {
            return 'Les adresses e-mail sont interdites dans le chat.';
        }

        // 4. Menaces
        foreach (self::$THREAT_KEYWORDS as $kw) {
            if (str_contains($lower, $kw)) {
                return 'Ce message contient des propos menaçants.';
            }
        }

        return null;
    }

    /**
     * GET /api/user/messages
     * Liste des conversations du client (une par chauffeur/trajet)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $messages = Message::where('user_id', $user->id)
            ->with(['trip:id,pickup_address,dropoff_address,departure_date',
                    'trip.driver:id,first_name,last_name,photo'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('trip_id')
            ->map(fn ($msgs) => $msgs->first())
            ->values();

        return response()->json(['success' => true, 'data' => $messages]);
    }

    /**
     * GET /api/user/messages/{userId}
     * Historique de la conversation pour un trajet donné
     * (userId ici = trip_id selon la convention du Flutter)
     */
    public function show($tripId)
    {
        $user = Auth::user();

        $messages = Message::where('trip_id', $tripId)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereHas('trip', fn ($q2) => $q2->where('user_id', $user->id));
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn ($m) => [
                'id'          => $m->id,
                'content'     => $m->content,
                'sender'      => $m->sender_type === 'driver' ? 'driver' : 'client',
                'sender_type' => $m->sender_type,
                'created_at'  => $m->created_at,
                'blocked'     => $m->blocked ?? false,
            ]);

        return response()->json(['success' => true, 'messages' => $messages]);
    }

    /**
     * POST /api/user/messages/{userId}
     * Envoi d'un message — avec modération serveur
     */
    public function store(Request $request, $tripId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $user    = Auth::user();
        $content = trim($request->content);

        // ── Modération ───────────────────────────────────────────
        $reason = $this->moderateContent($content);
        if ($reason) {
            return response()->json([
                'success' => false,
                'blocked' => true,
                'reason'  => $reason,
                'message' => 'Message refusé par la modération.',
            ], 422);
        }

        // ── Vérification que le trajet existe ────────────────────
        $trip = Trip::find($tripId);
        if (!$trip) {
            return response()->json([
                'success' => false,
                'message' => 'Trajet introuvable.',
            ], 404);
        }

        // ── Sauvegarde ───────────────────────────────────────────
        $message = Message::create([
            'trip_id'     => $tripId,
            'user_id'     => $user->id,
            'sender_type' => 'App\\Models\\User',
            'content'     => $content,
            'blocked'     => false,
        ]);

        // ── Diffusion Pusher → chauffeur ─────────────────────────
        try {
            broadcast(new MessageSent($message))->toOthers();
        } catch (\Exception $e) {
            \Log::warning('Pusher broadcast error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ], 201);
    }
}
