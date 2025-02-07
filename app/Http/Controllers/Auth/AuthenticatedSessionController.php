<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;
use Laravel\Fortify\Http\Requests\LoginRequest;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends FortifyAuthenticatedSessionController
{
    public function store(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        // Check if user exists and is a company or college
        if ($user && in_array($user->role, [UserRole::COMPANY, UserRole::COLLEGE])) {
            // Check if the user is approved
            if ($user->status !== 'approved') {
                return response()->json([
                    'message' => 'Your account is pending approval or has been rejected. Please contact the administrator.',
                    'status' => $user->status
                ], 403);
            }
        }

        try {
            // Attempt to authenticate
            if (!Auth::attempt($request->only('email', 'password'))) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user = Auth::user();
            /** @var \App\Models\User $user **/
            $token = $user->createToken('auth_token')->plainTextToken;

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }
            
            if (!$user->email_verified_at) {
                return response()->json(['message' => 'Email not verified'], 403);
            }
            
            if (in_array($user->role, ['company', 'college']) && $user->status !== 'approved') {
                return response()->json(['message' => 'Account not approved by admin.'], 403);
            }

            return response()->json([
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'status' => $user->status,
                    'email_verified_at'=>$user->email_verified_at
                ],
                'message' => 'Logged in successfully'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
                'errors' => $e->errors(),
            ], 422);
        }
    }
} 