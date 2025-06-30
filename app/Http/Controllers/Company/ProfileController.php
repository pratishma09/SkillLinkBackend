<?php

namespace App\Http\Controllers\Company;

use App\Models\Profile;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        $profiles = Profile::with('user')->get();
        return response()->json($profiles);
    }

    public function show(Profile $profile)
    {
        $profile->load('user');
        return response()->json($profile);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string',
            'logo' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'website' => 'nullable|url',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        if (!in_array($user->role, ['college', 'company'])) {
            return response()->json(['message' => 'Only colleges and companies can create profiles'], 403);
        }

        $data = $request->all();
        
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('public/profiles');
            $data['logo'] = Storage::url($path);
        }

        $profile = Profile::create([
            'user_id' => $user->id,
            'description' => $data['description'],
            'logo' => $data['logo'] ?? null,
            'website' => $data['website'],
            'phone' => $data['phone'],
            'address' => $data['address'],
        ]);

        $profile->load('user');

        return response()->json([
            'message' => 'Profile created successfully',
            'profile' => $profile
        ], 201);
    }

    public function update(Request $request, Profile $profile)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'website' => 'nullable|url',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        if ($profile->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->all();
        
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($profile->logo) {
                Storage::delete(str_replace('/storage', 'public', $profile->logo));
            }
            
            $path = $request->file('logo')->store('public/profiles');
            $data['logo'] = Storage::url($path);
        }

        $profile->update($data);
        $profile->load('user');

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $profile
        ]);
    }

    public function destroy(Profile $profile)
    {
        $user = Auth::user();
        if ($profile->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete logo if exists
        if ($profile->logo) {
            Storage::delete(str_replace('/storage', 'public', $profile->logo));
        }

        $profile->delete();

        return response()->json(['message' => 'Profile deleted successfully']);
    }

    public function getProfileByUser(User $user)
    {
        $profile = Profile::with('user')->where('user_id', $user->id)->first();
        
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        return response()->json($profile);
    }
} 