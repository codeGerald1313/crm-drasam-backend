<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CustomBroadcastingAuthController extends Controller
{
    public function authenticate(Request $request)
    {
        if (Auth::check()) {
            return response()->json(['message' => 'Autorizado'], 200);
        }
        return response()->json(['message' => 'No autorizado'], 401);
    }
}