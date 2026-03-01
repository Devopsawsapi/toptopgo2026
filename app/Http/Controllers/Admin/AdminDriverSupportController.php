<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportMessage;
use App\Models\Driver\Driver;
use App\Models\Admin\AdminUser;
use Illuminate\Support\Facades\Schema;

class AdminDriverSupportController extends Controller
{
    /**
     * Liste TOUS les chauffeurs (avec ou sans messages)
     */
    public function index(Request $request)
    {
        $query = Driver::withCount(['supportMessages as unread_count' => function ($q) {
                $q->where('recipient_type', Driver::class)
                  ->where('is_read', false);
            }])
            ->with(['supportMessages' => function ($q) {
                $q->latest()->limit(1);
            }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                  ->orWhere('last_name',  'like', "%$search%")
                  ->orWhere('phone',      'like', "%$search%");
            });
        }

        $query->orderByRaw(
            '(SELECT COUNT(*) FROM support_messages WHERE recipient_type = ? AND recipient_id = drivers.id) DESC',
            [Driver::class]
        )->orderBy('first_name');

        $drivers = $query->paginate(20);

        $totalConversations = Driver::whereHas('supportMessages')->count();

        $totalMessages = SupportMessage::where(function ($q) {
            $q->where('sender_type', Driver::class)
              ->where('recipient_type', AdminUser::class);
        })->orWhere(function ($q) {
            $q->where('sender_type', AdminUser::class)
              ->where('recipient_type', Driver::class);
        })->count();

        $unreadMessages = SupportMessage::where('recipient_type', AdminUser::class)
            ->where('is_read', false)
            ->count();

        return view('admin.messages.admin-driver', compact(
            'drivers',
            'totalConversations',
            'totalMessages',
            'unreadMessages'
        ));
    }

    /**
     * Affiche la conversation avec un chauffeur spécifique
     */
    public function show(Request $request, $driverId)
    {
        $driver = Driver::findOrFail($driverId);

        $messages = SupportMessage::where(function ($q) use ($driverId) {
                $q->where('recipient_type', Driver::class)
                  ->where('recipient_id', $driverId)
                  ->where('sender_type', AdminUser::class);
            })->orWhere(function ($q) use ($driverId) {
                $q->where('sender_type', Driver::class)
                  ->where('sender_id', $driverId)
                  ->where('recipient_type', AdminUser::class);
            })
            ->with('sender', 'recipient')
            ->oldest()
            ->get();

        // Marquer comme lus les messages reçus par l'admin depuis ce chauffeur
        $adminId = session('admin_id');
        SupportMessage::where('recipient_type', AdminUser::class)
            ->where('recipient_id', $adminId)
            ->where('sender_type', Driver::class)
            ->where('sender_id', $driverId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        $drivers = Driver::withCount(['supportMessages as unread_count' => function ($q) {
                $q->where('recipient_type', Driver::class)
                  ->where('is_read', false);
            }])
            ->with(['supportMessages' => function ($q) {
                $q->latest()->limit(1);
            }])
            ->orderBy('first_name')
            ->paginate(20);

        $totalConversations = Driver::whereHas('supportMessages')->count();

        $totalMessages = SupportMessage::where(function ($q) {
            $q->where('sender_type', Driver::class)
              ->where('recipient_type', AdminUser::class);
        })->orWhere(function ($q) {
            $q->where('sender_type', AdminUser::class)
              ->where('recipient_type', Driver::class);
        })->count();

        $unreadMessages = SupportMessage::where('recipient_type', AdminUser::class)
            ->where('is_read', false)
            ->count();

        return view('admin.messages.admin-driver', compact(
            'driver',
            'drivers',
            'messages',
            'totalConversations',
            'totalMessages',
            'unreadMessages'
        ));
    }

    /**
     * Envoyer un message à un chauffeur
     */
    public function send(Request $request, $driverId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $driver = Driver::findOrFail($driverId);

        $adminId = session('admin_id');

        if (!$adminId) {
            $admin = AdminUser::first();
            $adminId = $admin?->id;
        }

        if (!$adminId) {
            return back()->withErrors(['error' => 'Admin introuvable, reconnectez-vous.']);
        }

        $data = [
            'sender_type'    => AdminUser::class,
            'sender_id'      => $adminId,
            'recipient_type' => Driver::class,
            'recipient_id'   => $driverId,
            'content'        => $request->content,
            'is_read'        => false,
        ];

        // ✅ Si la table a une colonne admin_id dédiée, on la remplit aussi
        if (Schema::hasColumn('support_messages', 'admin_id')) {
            $data['admin_id'] = $adminId;
        }

        SupportMessage::create($data);

        return redirect()->route('admin.support.drivers.show', $driverId)
                         ->with('success', 'Message envoyé à ' . $driver->first_name . ' !');
    }
}