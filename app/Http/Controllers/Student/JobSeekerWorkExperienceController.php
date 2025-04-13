<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\JobSeekerWorkExperience;
use App\Models\Jobseeker;
use Illuminate\Http\Request;

class JobSeekerWorkExperienceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'joined_date' => 'required|date',
            'end_date' => 'nullable|date|after:joined_date',
            'currently_working' => 'required|boolean',
        ]);

        $jobseeker = Jobseeker::where('user_id', $request->user()->id)->firstOrFail();

        $experience = $jobseeker->workExperiences()->create($request->all());

        return response()->json([
            'message' => 'Work experience added successfully',
            'experience' => $experience
        ]);
    }

    public function update(Request $request, JobSeekerWorkExperience $experience)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'company_name' => 'required|string|max:255',
            'joined_date' => 'required|date',
            'end_date' => 'nullable|date|after:joined_date',
            'currently_working' => 'required|boolean',
        ]);

        $experience->update($request->all());

        return response()->json([
            'message' => 'Work experience updated successfully',
            'experience' => $experience
        ]);
    }

    public function destroy(JobSeekerWorkExperience $experience)
    {
        $experience->delete();

        return response()->json([
            'message' => 'Work experience deleted successfully'
        ]);
    }
} 