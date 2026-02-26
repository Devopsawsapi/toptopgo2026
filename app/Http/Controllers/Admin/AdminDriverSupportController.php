<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SupportMessage;
use App\Models\Driver\Driver;

class AdminDriverSupportController extends Controller
{
    /**
     * Liste TOUS les chauffeurs (avec ou sans messages)
     */
    public function index(Request $request)
    {
        $query = Driver::withCount(['supportMessages as unread_count' => function ($q) {
                $q->where('is_read', false);
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

        // Ceux avec messages en premier
        $query->orderByRaw('(SELECT COUNT(*) FROM support_messages WHERE recipient_type = ? AND recipient_id = drivers.id) DESC', [
            \App\Models\Driver\Driver::class
        ])->orderBy('first_name');

        $drivers = $query->paginate(20);

        $totalConversations = Driver::whereHas('supportMessages')->count();
        $totalMessages      = SupportMessage::where('recipient_type', \App\Models\Driver\Driver::class)->count();
        $unreadMessages     = SupportMessage::where('recipient_type', \App\Models\Driver\Driver::class)
                                ->where('is_read', false)->count();

        return view('admin.messages.admin-driver', compact(
            'drivers', 'totalConversations', 'totalMessages', 'unreadMessages'
        ));
    }

    /**
     * Affiche la conversation avec un chauffeur spécifique
     */
    public function show(Request $request, $driverId)
    {
        $driver = Driver::findOrFail($driverId);

        $messages = SupportMessage::where('recipient_type', \App\Models\Driver\Driver::class)
            ->where('recipient_id', $driverId)
            ->with('admin')
            ->oldest()
            ->get();

        // Marquer comme lus
        SupportMessage::where('recipient_type', \App\Models\Driver\Driver::class)
            ->where('recipient_id', $driverId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        // Sidebar : tous les chauffeurs
        $query = Driver::withCount(['supportMessages as unread_count' => function ($q) {
                $q->where('is_read', false);
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

        $query->orderByRaw('(SELECT COUNT(*) FROM support_messages WHERE recipient_type = ? AND recipient_id = drivers.id) DESC', [
            \App\Models\Driver\Driver::class
        ])->orderBy('first_name');

        $drivers = $query->paginate(20);

        $totalConversations = Driver::whereHas('supportMessages')->count();
        $totalMessages      = SupportMessage::where('recipient_type', \App\Models\Driver\Driver::class)->count();
        $unreadMessages     = SupportMessage::where('recipient_type', \App\Models\Driver\Driver::class)
                                ->where('is_read', false)->count();

        return view('admin.messages.admin-driver', compact(
            'driver', 'drivers', 'messages',
            'totalConversations', 'totalMessages', 'unreadMessages'
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

        SupportMessage::create([
            'admin_id'       => session('admin_id'),
            'recipient_type' => \App\Models\Driver\Driver::class,
            'recipient_id'   => $driverId,
            'content'        => $request->content,
            'is_read'        => false,
        ]);

        return redirect()->route('admin.support.drivers.show', array_filter([
            'driver' => $driverId,
            'search' => $request->search,
        ]))->with('success', 'Message envoyé à ' . $driver->first_name . ' !');
    }
}