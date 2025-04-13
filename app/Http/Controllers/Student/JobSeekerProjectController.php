<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\JobSeekerProject;
use App\Models\Jobseeker;
use Illuminate\Http\Request;

class JobSeekerProjectController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'currently_working' => 'required|boolean',
            'project_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
        ]);

        $jobseeker = Jobseeker::where('user_id', $request->user()->id)->firstOrFail();

        $project = $jobseeker->projects()->create($request->all());

        return response()->json([
            'message' => 'Project added successfully',
            'project' => $project
        ]);
    }

    public function update(Request $request, JobSeekerProject $project)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'currently_working' => 'required|boolean',
            'project_url' => 'nullable|url|max:255',
            'github_url' => 'nullable|url|max:255',
        ]);

        $project->update($request->all());

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project
        ]);
    }

    public function destroy(JobSeekerProject $project)
    {
        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully'
        ]);
    }
} 