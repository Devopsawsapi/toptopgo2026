<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Trip;
use App\Models\User\User;
use App\Http\Resources\MessageResource;
use App\Events\MessageSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * DriverMessageController — Messagerie chauffeur ↔ client
 * 🔄 MODIFIÉ : ajout de la modération côté serveur dans store()
 *
 * Conserve exactement le même style que votre version originale :
 *   - index()  → liste des conversations (inchangé)
 *   - show()   → messages d'un trajet + MessageResource (inchangé)
 *   - store()  → ✅ modération ajoutée avant la sauvegarde
 */
class DriverMessageController extends Controller
{
    // ── Règles de modération (miroir Flutter ModerationService) ────────────
    private const PHONE_REGEX    = '/(\+?\d[\d\s\-\.\/\(\)]{6,}\d)/';
    private const EMAIL_REGEX    = '/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}/';
    private const URL_REGEX      = '/(https?:\/\/|www\.|bit\.ly|t\.me|wa\.me|\.(com|fr|net|org|io|co|me))/i';

    private const THREATS = [
        'je vais te tuer', 'je te tue', 'mort à', 'tu vas mourir',
        'je te retrouve', 'je te fracasse', 'gare à toi', 'tu vas regretter',
        'je vais te buter', 'crève', 'je te massacre', 'prépare-toi',
    ];

    private const INSULTS = [
        'fils de pute', 'fdp', 'connard', 'connasse', 'salope', 'pute',
        'enculé', 'batard', 'bâtard', 'nique ta mère', 'ntm',
        'va te faire foutre', 'va te faire enculer',
    ];

    private const ROMANTIC = [
        'je t\'aime', 'je taime', 'je vous aime', 'donne-moi ton numéro',
        'donne moi ton numero', 'ton whatsapp', 'viens chez moi',
    ];

    private const OFF_PLATFORM = [
        'payer en cash', 'payer en liquide', 'paiement direct',
        'mobile money direct', 'orange money direct', 'sans l\'appli',
    ];

    // ── Modération ─────────────────────────────────────────────────────────
    private function moderate(string $text): ?string
    {
        $t        = mb_strtolower($text);
        $collapsed = preg_replace('/[\s\-\.\(\)]/', '', $text);

        if (preg_match(self::PHONE_REGEX, $collapsed) && preg_match('/\d{7,}/', $collapsed)) {
            return 'numéro de téléphone';
        }
        if (preg_match(self::EMAIL_REGEX, $text))  return 'adresse e-mail';
        if (preg_match(self::URL_REGEX, $t))        return 'lien externe';

        foreach (self::THREATS      as $w) { if (str_contains($t, $w)) return 'menace'; }
        foreach (self::INSULTS      as $w) { if (str_contains($t, $w)) return 'insulte'; }
        foreach (self::ROMANTIC     as $w) { if (str_contains($t, $w)) return 'contenu inapproprié'; }
        foreach (self::OFF_PLATFORM as $w) { if (str_contains($t, $w)) return 'paiement hors plateforme'; }

        return null;
    }

    // ── index() — inchangé par rapport à votre original ────────────────────
    public function index(Request $request)
    {
        $driver = $request->user();

        $trips = Trip::where('driver_id', $driver->id)
            ->with(['messages' => function ($q) {
                $q->where('refused', false)->latest()->limit(1);
            }, 'user'])
            ->latest()
            ->get();

        $data = $trips->map(function ($trip) {
            $user        = $trip->user;
            $lastMessage = $trip->messages->first();
            $clientPhoto = null;

            if ($user && $user->profile_photo) {
                $clientPhoto = str_starts_with($user->profile_photo, 'http')
                    ? $user->profile_photo
                    : asset('storage/' . $user->profile_photo);
            }

            $unread = Message::where('trip_id', $trip->id)
                ->where('sender_type', User::class)
                ->where('is_read', false)
                ->where('refused', false)
                ->count();

            return [
                'trip_id'      => $trip->id,
                'client_id'    => $user?->id,
                'client_name'  => $user
                    ? trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                    : 'Client',
                'client_photo' => $clientPhoto,
                'client_phone' => $user?->phone ?? '',
                'trip_status'  => $trip->status ?? 'pending',
                'last_message' => $lastMessage?->content ?? '',
                'updated_at'   => $lastMessage?->created_at ?? $trip->updated_at,
                'unread_count' => $unread,
            ];
        });

        return response()->json(['success' => true, 'data' => $data]);
    }

    // ── show() — inchangé par rapport à votre original ─────────────────────
    public function show(Request $request, $tripId)
    {
        $driver = $request->user();

        $trip = Trip::where('id', $tripId)
            ->where('driver_id', $driver->id)
            ->firstOrFail();

        $messages = Message::where('trip_id', $tripId)->oldest()->get();

        // Marquer les messages client comme lus
        Message::where('trip_id', $tripId)
            ->where('receiver_id', $driver->id)
            ->where('receiver_type', get_class($driver))
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return MessageResource::collection($messages);
    }

    // ── store() — ✅ MODIFIÉ : modération ajoutée ──────────────────────────
    public function store(Request $request, $tripId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $driver  = $request->user();
        $content = trim($request->content);

        $trip = Trip::where('id', $tripId)
            ->where('driver_id', $driver->id)
            ->firstOrFail();

        // ── Modération côté serveur ──────────────────────────────────────
        $reason = $this->moderate($content);
        if ($reason) {
            Log::warning('🚫 Message chauffeur bloqué', [
                'driver_id' => $driver->id,
                'trip_id'   => $tripId,
                'reason'    => $reason,
                'content'   => substr($content, 0, 80),
            ]);

            // Enregistrer pour traçabilité admin (visible dans l'interface admin)
            Message::create([
                'trip_id'       => $tripId,
                'sender_type'   => get_class($driver),
                'sender_id'     => $driver->id,
                'receiver_type' => User::class,
                'receiver_id'   => $trip->user_id,
                'content'       => $content,
                'refused'       => true,
                'refused_reason'=> $reason,
            ]);

            return response()->json([
                'success' => false,
                'blocked' => true,
                'reason'  => $reason,
                'message' => 'Message refusé par la modération.',
            ], 422);
        }

        // ── Sauvegarde normale ───────────────────────────────────────────
        $message = Message::create([
            'trip_id'       => $tripId,
            'sender_type'   => get_class($driver),
            'sender_id'     => $driver->id,
            'receiver_type' => User::class,
            'receiver_id'   => $trip->user_id,
            'content'       => $content,
        ]);

        // ── Diffusion Pusher (même pattern que votre original) ───────────
        try {
            MessageSent::dispatch($message);
        } catch (\Exception $e) {
            Log::warning('Pusher broadcast error: ' . $e->getMessage());
        }

        return new MessageResource($message);
    }
}