<?php

namespace App\Http\Controllers;

use App\Models\Jobseeker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class JobseekerController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string',
            'current_address' => 'required|string',
            'permanent_address' => 'required|string',
            'college_id' => 'required|exists:users,id,role,college',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'linkedin_url' => 'nullable|url|max:255',
            'professional_summary' => 'nullable|string',
            'skills' => 'nullable|array',
            'skills.*' => 'string|max:255',
        ]);

        $jobseeker = Jobseeker::updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'mobile' => $request->mobile,
                'current_address' => $request->current_address,
                'permanent_address' => $request->permanent_address,
                'college_id' => $request->college_id,
                'dob' => $request->dob,
                'gender' => $request->gender,
                'linkedin_url' => $request->linkedin_url,
                'professional_summary' => $request->professional_summary,
                'skills' => $request->skills,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Profile updated successfully',
            'data' => $jobseeker
        ]);
    }

    public function show()
    {
        $jobseeker = Jobseeker::with('college:id,name','user:id,name,email',)
            ->where('user_id', Auth::id())
            ->with('education','workExperiences','certifications','projects')
            ->first();

        if (!$jobseeker) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please complete your profile first'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $jobseeker
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'mobile' => 'required|string',
            'college_id' => 'required|exists:users,id,role,college',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'current_address' => 'required|string',
            'permanent_address' => 'required|string',
            'linkedin_url' => 'nullable|url',
            'professional_summary' => 'nullable|string',
            'skills' => 'nullable|string',
            'image' => 'nullable|image|max:2048'
        ]);

        try {
            // Get existing jobseeker profile
            $jobseeker = Jobseeker::where('user_id', Auth::id())->first();

            // Handle image upload if present
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($jobseeker && $jobseeker->image) {
                    Storage::disk('public')->delete($jobseeker->image);
                }
                $imagePath = $request->file('image')->store('profile-images', 'public');
            }

            // Decode skills JSON string to array
            $skills = $request->has('skills') ? json_decode($request->skills, true) : [];

            // Ensure skills is an array
            if (!is_array($skills)) {
                $skills = [];
            }

            // Update or create jobseeker profile
            $jobseeker = Jobseeker::updateOrCreate(
                ['user_id' => Auth::id()],
                [
                    'mobile' => $request->mobile,
                    'college_id' => $request->college_id,
                    'dob' => $request->dob,
                    'gender' => $request->gender,
                    'current_address' => $request->current_address,
                    'permanent_address' => $request->permanent_address,
                    'linkedin_url' => $request->linkedin_url,
                    'professional_summary' => $request->professional_summary,
                    'skills' => $skills,
                    'image' => $request->hasFile('image') ? $imagePath : ($jobseeker->image ?? null),
                ]
            );

            // Load the relationships
            $jobseeker->load(['user:id,name,email', 'college:id,name']);

            return response()->json([
                'status' => 'success',
                'message' => 'Profile updated successfully',
                'data' => $jobseeker
            ]);

        } catch (\Exception $e) {
            \Log::error('Profile update error: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update profile: ' . $e->getMessage()
            ], 500);
        }
    }
} 