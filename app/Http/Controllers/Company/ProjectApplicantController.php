<?php
namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Mail\ApplicantRejected;
use App\Mail\ApplicantShortlisted;
use App\Models\Applicant;
use App\Models\Project;
use Carbon\Carbon;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
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
        $validated = $request->validate([
            'status' => 'required|in:applied,shortlisted,rejected',
        ]);

        if (! $applicant) {
            return response()->json(['error' => true, 'message' => 'Job Applicant not found'], 404);
        }

        if ($applicant->jobseeker_status == 'rejected') {
            return response()->json(['error' => true, 'message' => 'Job Applicant already rejected'], 400);
        }

        if ($request->status == 'shortlisted') {
            $client = new Google_Client();
            $client->setHttpClient(new \GuzzleHttp\Client([
                'verify' => false, // Disable SSL certificate verification
            ]));
            $client->setAuthConfig(storage_path('app/google-calendar/oauth-credentials.json'));
            $client->setScopes([Google_Service_Calendar::CALENDAR]);
            $client->setAccessType('offline');

            $tokenPath = storage_path('app/google-calendar/oauth-token.json');
            if (! file_exists($tokenPath)) {
                return response()->json([
                    'error'   => true,
                    'message' => 'OAuth token file not found. Please authenticate via the callback URL.',
                ], 500);
            }

            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);

            if ($client->isAccessTokenExpired()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                file_put_contents($tokenPath, json_encode($client->getAccessToken()));
            }

            $service       = new \Google_Service_Calendar($client);
            $startDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $request->start_time)->toIso8601String();
            $endDateTime   = Carbon::createFromFormat('Y-m-d H:i:s', $request->end_time)->toIso8601String();
            $event         = new Google_Service_Calendar_Event([
                'summary'        => 'Interview with ' . $applicant->jobseeker->user->name,
                'description'    => 'Interview for the job position. Please join via Google Meet.',
                'start'          => new Google_Service_Calendar_EventDateTime([
                    'dateTime' => $startDateTime,
                    'timeZone' => 'Asia/Kathmandu',
                ]),
                'end'            => new Google_Service_Calendar_EventDateTime([
                    'dateTime' => $endDateTime,
                    'timeZone' => 'Asia/Kathmandu',
                ]),
                'conferenceData' => [
                    'createRequest' => [
                        'requestId'             => uniqid(),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    ],
                ],
                'attendees'      => [
                    ['email' => $applicant->jobseeker->user->email],
                ],
            ]);

            $calendarId = env('GOOGLE_CALENDAR_ID');
            $event      = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);

            $meetingLink = $event->getHangoutLink();

            $applicant->update([
                'jobseeker_status' => $request->status,
                'meeting_link'     => $meetingLink,
                'meeting_time'     => $startDateTime,
            ]);

            Mail::to($applicant->jobseeker->user->email)->send(new ApplicantShortlisted($applicant->project->title, $meetingLink, $request->start_time, $request->end_time));
            return response()->json([
                'success'      => true,
                'message'      => 'Job Applicant has been shortlisted and meeting link generated.',
                'meeting_link' => $meetingLink,
            ], 200);
        }
        if ($request->status != 'shortlisted') {
            Mail::to($applicant->jobseeker->user->email)->send(new ApplicantRejected($applicant->project->title));
        } else {
            Mail::to($applicant->jobseeker->user->email)->send(new ApplicantShortlisted($applicant->project->title));
        }
        $applicant->update(['jobseeker_status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => "Job Applicant has been {$request->status}.",
        ], 200);
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
