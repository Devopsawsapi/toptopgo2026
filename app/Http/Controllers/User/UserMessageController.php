<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMessageController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'data'    => [],
        ]);
    }

    public function show($userId)
    {
        return response()->json([
            'success' => true,
            'data'    => [],
        ]);
    }

    public function store(Request $request, $userId)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Message envoyé.',
        ]);
    }
}