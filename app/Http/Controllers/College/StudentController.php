<?php

namespace App\Http\Controllers\College;

use App\Http\Controllers\Controller;
use App\Models\Jobseeker;
use App\Models\Applicant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * Get college dashboard data including student count and student list with application stats
     */
    public function getDashboardData(): JsonResponse
    {
        try {
            $collegeId = Auth::id();

            // Verify the authenticated user is a college
            if (Auth::user()->role !== 'college') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only colleges can access this dashboard'
                ], 403);
            }

            // Get total number of students enrolled in this college
            $totalStudents = Jobseeker::where('college_id', $collegeId)->count();

            // Get students with their application statistics
            $students = Jobseeker::with(['user:id,name,email'])
                ->where('college_id', $collegeId)
                ->get()
                ->map(function ($student) {
                    // Get application statistics for this student
                    $applicationStats = Applicant::where('jobseeker_id', $student->id)
                        ->select('jobseeker_status', DB::raw('count(*) as count'))
                        ->groupBy('jobseeker_status')
                        ->pluck('count', 'jobseeker_status')
                        ->toArray();

                    return [
                        'id' => $student->id,
                        'name' => $student->user->name ?? 'N/A',
                        'email' => $student->user->email ?? 'N/A',
                        'mobile' => $student->mobile ?? 'N/A',
                        'applied_count' => $applicationStats['applied'] ?? 0,
                        'shortlisted_count' => $applicationStats['shortlisted'] ?? 0,
                        'rejected_count' => $applicationStats['rejected'] ?? 0,
                        'total_applications' => array_sum($applicationStats),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'total_students' => $totalStudents,
                    'students' => $students
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of students enrolled in the college
     */
    public function index(): JsonResponse
    {
        $collegeId = Auth::id();

        $students = Jobseeker::with(['user:id,name,email'])
            ->where('college_id', $collegeId)
            ->paginate(10);

        return response()->json($students);
    }
}
