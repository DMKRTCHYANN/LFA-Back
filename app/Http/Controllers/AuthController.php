<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = User::query()
            ->where('email', $request->get('email'))
            ->first();

        if (is_null($user) || !Hash::check($request->get('password'), $user->password)) {
            return response()->json([
                'error' => 'Unauthorized'
            ], 401);
        }

        $accessToken = $user->createToken('authToken')->accessToken;

        return response()->json([
            'access_token' => $accessToken
        ]);
    }
}
