<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\JobSeekerEducation;
use App\Models\Jobseeker;
use Illuminate\Http\Request;

class JobSeekerEducationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'institution' => 'required|string|max:255',
            'board' => 'required|string|max:255',
            'graduation_year' => 'required|integer|min:1900|max:2100',
            'gpa' => 'required|string|max:10',
        ]);

        $jobseeker = Jobseeker::where('user_id', $request->user()->id)->firstOrFail();

        $education = $jobseeker->education()->create($request->all());

        return response()->json([
            'message' => 'Education added successfully',
            'education' => $education
        ]);
    }

    public function update(Request $request, JobSeekerEducation $education)
    {
        $request->validate([
            'institution' => 'required|string|max:255',
            'board' => 'required|string|max:255',
            'graduation_year' => 'required|integer|min:1900|max:2100',
            'gpa' => 'required|string|max:10',
        ]);

        $education->update($request->all());

        return response()->json([
            'message' => 'Education updated successfully',
            'education' => $education
        ]);
    }

    public function destroy(JobSeekerEducation $education)
    {
        $education->delete();

        return response()->json([
            'message' => 'Education deleted successfully'
        ]);
    }
} 