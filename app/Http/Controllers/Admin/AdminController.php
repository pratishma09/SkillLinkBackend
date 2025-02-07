<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Notifications\AccountApprovedNotification;

class AdminController extends Controller
{
    public function pendingUsers()
    {
        $pendingUsers = User::where('status', 'pending')
            ->whereIn('role', [UserRole::COLLEGE->value, UserRole::COMPANY->value])
            ->latest()
            ->paginate(10);

        return response()->json($pendingUsers);
    }

    public function approve(User $user): JsonResponse
    {
        try {
            // \Log::info('Approving user:', ['user_id' => $user->id, 'current_status' => $user->status]);
            
         

            // For college and company, send verification email after approval
            if (in_array($user->role, ['college', 'company'])) {
                $user->update([
                    'status' => 'approved'
                ]);
            

            // For other roles or already verified users
            }

            $user->notify(new AccountApprovedNotification());

            return response()->json([
                'message' => 'User approved successfully',
                'user' => $user
            ]);
            
        } catch (\Exception $e) {
            // \Log::error('Error approving user:', [
            //     'user_id' => $user->id,
            //     'error' => $e->getMessage()
            // ]);
            
            return response()->json([
                'message' => 'Error approving user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function reject(Request $request, User $user)
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:1000']
        ]);

        $user->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason']
        ]);

        return response()->json([
            'message' => 'User rejected successfully',
            'user' => $user
        ]);
    }
} 