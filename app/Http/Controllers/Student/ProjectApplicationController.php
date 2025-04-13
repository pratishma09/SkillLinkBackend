<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Applicant;
use App\Models\Jobseeker;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectApplicationController extends Controller
{
    public function apply(Project $project): JsonResponse
    {
        // Verify the authenticated user is a student
        if (Auth::user()->role !== 'student') {
            return response()->json([
                'message' => 'Only students can apply to projects'
            ], 403);
        }

        // Get the jobseeker profile for the authenticated user
        $jobseeker = Jobseeker::where('user_id', Auth::id())->first();
        
        if (!$jobseeker) {
            return response()->json([
                'message' => 'Please complete your jobseeker profile before applying'
            ], 400);
        }

        // Check if already applied
        if (Applicant::where('project_id', $project->id)
            ->where('jobseeker_id', $jobseeker->id)
            ->where('is_saved', false)
            ->exists()) {
            return response()->json([
                'message' => 'You have already applied to this project'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $application = Applicant::create([
                'project_id' => $project->id,
                'jobseeker_id' => $jobseeker->id,
                'jobseeker_status' => 'applied',
                'applied_date' => now(),
                'is_saved' => false
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Application submitted successfully',
                'application' => $application->load(['project', 'project.company', 'project.projectcategory'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to submit application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function withdraw(Project $project): JsonResponse
    {
        // Verify the authenticated user is a student
        if (Auth::user()->role !== 'student') {
            return response()->json([
                'message' => 'Only students can withdraw applications'
            ], 403);
        }

        $jobseeker = Jobseeker::where('user_id', Auth::id())->first();
        
        if (!$jobseeker) {
            return response()->json([
                'message' => 'Jobseeker profile not found'
            ], 404);
        }

        $application = Applicant::where('project_id', $project->id)
            ->where('jobseeker_id', $jobseeker->id)
            ->first();

        if (!$application) {
            return response()->json([
                'message' => 'No application found for this project'
            ], 404);
        }

        $application->delete();

        return response()->json([
            'message' => 'Application withdrawn successfully'
        ]);
    }

    public function save(Project $project): JsonResponse
    {
        // Verify the authenticated user is a student
        if (Auth::user()->role !== 'student') {
            return response()->json([
                'message' => 'Only students can save projects'
            ], 403);
        }

        $jobseeker = Jobseeker::where('user_id', Auth::id())->first();
        
        if (!$jobseeker) {
            return response()->json([
                'message' => 'Jobseeker profile not found'
            ], 404);
        }

        $application = Applicant::where('project_id', $project->id)
            ->where('jobseeker_id', $jobseeker->id)
            ->first();

        if ($application) {
            $application->update(['is_saved' => true]);
        } else {
            Applicant::create([
                'project_id' => $project->id,
                'jobseeker_id' => $jobseeker->id,
                'jobseeker_status' => 'saved',
                'applied_date' => null,
                'is_saved' => true
            ]);
        }

        return response()->json([
            'message' => 'Project saved successfully'
        ]);
    }

    public function unsave(Project $project): JsonResponse
    {
        // Verify the authenticated user is a student
        if (Auth::user()->role !== 'student') {
            return response()->json([
                'message' => 'Only students can unsave projects'
            ], 403);
        }

        $jobseeker = Jobseeker::where('user_id', Auth::id())->first();
        
        if (!$jobseeker) {
            return response()->json([
                'message' => 'Jobseeker profile not found'
            ], 404);
        }

        $application = Applicant::where('project_id', $project->id)
            ->where('jobseeker_id', $jobseeker->id)
            ->first();

        if ($application) {
            if ($application->jobseeker_status === 'saved') {
                $application->delete();
            } else {
                $application->update(['is_saved' => false]);
            }
        }

        return response()->json([
            'message' => 'Project unsaved successfully'
        ]);
    }

    public function myApplications(): JsonResponse
    {
        $jobseeker = Jobseeker::where('user_id', Auth::id())->first();
        
        if (!$jobseeker) {
            return response()->json([
                'message' => 'Jobseeker profile not found'
            ], 404);
        }

        $applications = Applicant::with(['project', 'project.company', 'project.projectcategory'])
            ->where('jobseeker_id', $jobseeker->id)
            ->where('jobseeker_status', 'applied')
            ->latest()
            ->paginate(10);

        return response()->json($applications);
    }

    public function savedProjects(): JsonResponse
    {
        $jobseeker = Jobseeker::where('user_id', Auth::id())->first();
        
        if (!$jobseeker) {
            return response()->json([
                'message' => 'Jobseeker profile not found'
            ], 404);
        }

        $savedProjects = Applicant::with(['project', 'project.company', 'project.projectcategory'])
            ->where('jobseeker_id', $jobseeker->id)
            ->where('is_saved', true)
            ->latest()
            ->paginate(10);

        return response()->json($savedProjects);
    }
} 