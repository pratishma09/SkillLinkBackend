<?php
namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Mail\ApplicantRejected;
use App\Mail\ApplicantShortlisted;
use App\Models\Applicant;
use App\Models\Project;
use App\Services\GoogleCalendarService;
use App\Services\MockCalendarService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ProjectApplicantController extends Controller
{
    public function getProjectApplications(Project $project): JsonResponse
    {
        // Verify the authenticated user owns the project
        if ($project->posted_by !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized access to project applicants',
            ], 403);
        }

        $applicants = Applicant::with([
            'jobseeker.user',
            'jobseeker.workExperiences', 'jobseeker.education', 'jobseeker.certifications', 'jobseeker.projects',
            'jobseeker.college',
        ])
            ->where('project_id', $project->id)
            ->whereIn('jobseeker_status', ['applied', 'shortlisted', 'rejected'])
            ->latest()
            ->get();

        return response()->json([
            'data' => $applicants,
        ]);
    }

    public function updateApplicantStatus(Project $project, Applicant $applicant, Request $request): JsonResponse
    {
        if ($project->posted_by !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized access to project applicants',
            ], 403);
        }

        if ($applicant->project_id !== $project->id) {
            return response()->json([
                'message' => 'Applicant does not belong to this project',
            ], 400);
        }

        $validated = $request->validate([
            'status'       => 'required|in:shortlisted,rejected',
            'meeting_link' => 'nullable',
            'start_time'   => 'nullable',
            'end_time'     => 'nullable',
        ]);

        if ($request->status == 'shortlisted') {
            try {
                // Try Google Calendar first, fallback to mock service
                $calendarService = null;
                $usingMockService = false;

                try {
                    $googleCalendar = new GoogleCalendarService();
                    if ($googleCalendar->isAuthenticated()) {
                        $calendarService = $googleCalendar;
                    }
                } catch (\Exception $e) {
                    // Google Calendar not available, use mock service
                }

                if (!$calendarService) {
                    $calendarService = new MockCalendarService();
                    $usingMockService = true;
                }

                // Parse and format datetime for different uses
                try {
                    $startCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_time);
                    $endCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $request->end_time);
                } catch (\Exception $e) {
                    // Try alternative parsing if the format doesn't match
                    $startCarbon = Carbon::parse($request->start_time);
                    $endCarbon = Carbon::parse($request->end_time);
                }

                // Format for Google Calendar API (ISO 8601)
                $startDateTimeISO = $startCarbon->toIso8601String();
                $endDateTimeISO = $endCarbon->toIso8601String();

                // Format for database (MySQL datetime)
                $startDateTimeDB = $startCarbon->format('Y-m-d H:i:s');
                $endDateTimeDB = $endCarbon->format('Y-m-d H:i:s');

                // Create calendar event
                $result = $calendarService->createMeetingEvent(
                    'Interview with ' . $applicant->jobseeker->user->name,
                    'Interview for the position: ' . $applicant->project->title . '. Please join via Google Meet.',
                    $startDateTimeISO,
                    $endDateTimeISO,
                    $applicant->jobseeker->user->email
                );

                if (!$result['success']) {
                    throw new \Exception($result['error']);
                }

                $meetingLink = $result['meeting_link'];

                // Only update applicant status AFTER successful calendar link generation
                $applicant->update([
                    'jobseeker_status' => $request->status,
                    'meeting_link' => $meetingLink,
                    'meeting_time' => $startDateTimeDB,
                ]);

                Mail::to($applicant->jobseeker->user->email)->send(new ApplicantShortlisted($applicant->project->title, $meetingLink, $request->start_time, $request->end_time));

                $message = $usingMockService
                    ? 'Job Applicant has been shortlisted and mock meeting link generated (Google Calendar not configured).'
                    : 'Job Applicant has been shortlisted and meeting link generated.';

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'meeting_link' => $meetingLink,
                    'using_mock' => $usingMockService,
                    'applicant' => $applicant->load(['jobseeker.user', 'jobseeker.workExperiences', 'jobseeker.education', 'jobseeker.certifications', 'jobseeker.projects']),
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to generate calendar link: ' . $e->getMessage(),
                ], 500);
            }
        } elseif ($request->status == 'rejected') {
            // For rejected status, update immediately and send rejection email
            $applicant->update([
                'jobseeker_status' => $validated['status'],
            ]);

            Mail::to($applicant->jobseeker->user->email)->send(new ApplicantRejected($applicant->project->title));

            return response()->json([
                'message' => 'Applicant status updated successfully',
                'applicant' => $applicant->load(['jobseeker.user', 'jobseeker.workExperiences', 'jobseeker.education', 'jobseeker.certifications', 'jobseeker.projects']),
            ]);
        }

        // This should not be reached, but just in case
        return response()->json([
            'message' => 'Invalid status provided',
        ], 400);
    }

    public function getApplicantDetails(Project $project, Applicant $applicant): JsonResponse
    {
        // Verify the authenticated user owns the project
        if ($project->posted_by !== Auth::id()) {
            return response()->json([
                'message' => 'Unauthorized access to applicant details',
            ], 403);
        }

        // Verify the applicant belongs to the project
        if ($applicant->project_id !== $project->id) {
            return response()->json([
                'message' => 'Applicant does not belong to this project',
            ], 400);
        }

        $applicant->load([
            'jobseeker.user',
            'jobseeker.workExperiences',
            'jobseeker.education',
            'jobseeker.certifications',
            'jobseeker.projects',
            'jobseeker.college',
        ]);

        return response()->json([
            'data' => $applicant,
        ]);
    }

}
