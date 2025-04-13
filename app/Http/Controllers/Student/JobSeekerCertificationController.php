<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\JobSeekerCertification;
use App\Models\Jobseeker;
use Illuminate\Http\Request;

class JobSeekerCertificationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'issuing_organization' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'no_expiry' => 'required|boolean',
            'credential_id' => 'nullable|string|max:255',
            'credential_url' => 'nullable|url|max:255',
        ]);

        $jobseeker = Jobseeker::where('user_id', $request->user()->id)->firstOrFail();

        $certification = $jobseeker->certifications()->create($request->all());

        return response()->json([
            'message' => 'Certification added successfully',
            'certification' => $certification
        ]);
    }

    public function update(Request $request, JobSeekerCertification $certification)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'issuing_organization' => 'required|string|max:255',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'no_expiry' => 'required|boolean',
            'credential_id' => 'nullable|string|max:255',
            'credential_url' => 'nullable|url|max:255',
        ]);

        $certification->update($request->all());

        return response()->json([
            'message' => 'Certification updated successfully',
            'certification' => $certification
        ]);
    }

    public function destroy(JobSeekerCertification $certification)
    {
        $certification->delete();

        return response()->json([
            'message' => 'Certification deleted successfully'
        ]);
    }
} 