<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Applicant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProjectApplicantController extends Controller
{
    

    

    public function getProjectApplications(Project $project): JsonResponse
    {
        // Verify the authenticated user owns the project
        if ($project->posted_by !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized access to project applicants'
            ], 403);
        }

        $applicants = Applicant::with([
            'jobseeker.user',
            'jobseeker.workExperiences','jobseeker.education','jobseeker.certifications','jobseeker.projects',
            'jobseeker.college'
        ])
        ->where('project_id', $project->id)
        ->where('jobseeker_status', 'applied')
        ->latest()
        ->get();

        return response()->json([
            'data' => $applicants
        ]);
    }

    public function updateApplicantStatus(Project $project, Applicant $applicant, Request $request): JsonResponse
    {
        // Verify the authenticated user owns the project
        if ($project->posted_by !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized access to project applicants'
            ], 403);
        }

        // Verify the applicant belongs to the project
        if ($applicant->project_id !== $project->id) {
            return response()->json([
                'message' => 'Applicant does not belong to this project'
            ], 400);
        }

        $validated = $request->validate([
            'status' => 'required|in:applied,shortlisted,rejected'
        ]);

        $applicant->update([
            'jobseeker_status' => $validated['status']
        ]);

        return response()->json([
            'message' => 'Applicant status updated successfully',
            'applicant' => $applicant->load(['jobseeker.user', 'jobseeker.workExperiences','jobseeker.education','jobseeker.certifications','jobseeker.projects'])
        ]);
    }
} 