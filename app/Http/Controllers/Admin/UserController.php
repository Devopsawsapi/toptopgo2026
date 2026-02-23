<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'verified') {
                $query->whereNotNull('phone_verified_at');
            } elseif ($request->status === 'unverified') {
                $query->whereNull('phone_verified_at');
            }
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['rides', 'driverProfile', 'wallets']);

        $stats = [
            'total_rides' => $user->rides()->count(),
            'completed_rides' => $user->rides()->where('status', 'completed')->count(),
            'total_spent' => $user->rides()->where('status', 'completed')->sum('price'),
        ];

        if ($user->isDriver()) {
            $stats['total_earnings'] = $user->driverProfile?->total_earnings ?? 0;
            $stats['rating'] = $user->driverProfile?->rating ?? 0;
        }

        return view('admin.users.show', compact('user', 'stats'));
    }

    public function toggleStatus(User $user)
    {
        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with('success', 'Statut utilisateur mis à jour');
    }

    public function destroy(User $user)
    {
        // Soft delete
        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'Utilisateur supprimé');
    }
}
