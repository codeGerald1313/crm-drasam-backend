<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebSocketAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        if (Auth::guard('api')->check()) {
            return response()->json(['message' => 'Authenticated']);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
}
