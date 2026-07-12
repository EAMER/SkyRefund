<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login.
     */
    public function login(LoginRequest $request)
    {
        if (! Auth::attempt($request->only('email', 'password'))) {

            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);

        }

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (! $user->active) {

            return response()->json([
                'success' => false,
                'message' => 'Your account has been disabled.',
            ], 403);

        }

        $token = $user->createToken('SkyRefund')->plainTextToken;

        return response()->json([

            'success' => true,

            'message' => 'Login successful.',

            'token' => $token,

            'user' => $user,

        ]);
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        $request
            ->user()
            ->currentAccessToken()
            ->delete();

        return response()->json([

            'success' => true,

            'message' => 'Logged out successfully.',

        ]);
    }

    /**
     * Current user.
     */
    public function me(Request $request)
    {
        return response()->json([

            'success' => true,

            'user' => $request->user(),

        ]);
    }
}