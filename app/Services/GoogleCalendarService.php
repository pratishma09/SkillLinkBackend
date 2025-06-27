<?php

namespace App\Services;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Carbon\Carbon;

class GoogleCalendarService
{
    private $client;
    private $service;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->setupClient();
    }

    private function setupClient()
    {
        // Try service account first (recommended for production)
        $serviceAccountPath = storage_path('app/google-calendar/service-account-credentials.json');
        $oauthCredentialsPath = storage_path('app/google-calendar/oauth-credentials.json');
        $tokenPath = storage_path('app/google-calendar/oauth-token.json');

        if (file_exists($serviceAccountPath)) {
            // Use service account authentication
            $this->client->setAuthConfig($serviceAccountPath);
            $this->client->setScopes([Google_Service_Calendar::CALENDAR]);
            $this->service = new Google_Service_Calendar($this->client);
            return;
        }

        // Fallback to OAuth authentication
        if (!file_exists($oauthCredentialsPath)) {
            throw new \Exception('Google Calendar credentials file not found. Please add oauth-credentials.json or service-account-credentials.json to storage/app/google-calendar/');
        }

        $this->client->setAuthConfig($oauthCredentialsPath);
        $this->client->setScopes([Google_Service_Calendar::CALENDAR]);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');

        // Load existing token
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);

            // Refresh token if expired
            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    file_put_contents($tokenPath, json_encode($this->client->getAccessToken()));
                } else {
                    throw new \Exception('Google Calendar authentication expired. Please add your email as a test user in Google Cloud Console or use service account authentication.');
                }
            }
        } else {
            throw new \Exception('Google Calendar not authenticated. Please add your email as a test user in Google Cloud Console and run: php artisan google:setup-calendar');
        }

        $this->service = new Google_Service_Calendar($this->client);
    }

    public function createMeetingEvent($title, $description, $startDateTime, $endDateTime, $attendeeEmail)
    {
        try {
            // First try with Google Meet conference
            $event = new Google_Service_Calendar_Event([
                'summary' => $title,
                'description' => $description . "\n\nAttendee: " . $attendeeEmail,
                'start' => new Google_Service_Calendar_EventDateTime([
                    'dateTime' => $startDateTime,
                    'timeZone' => 'Asia/Kathmandu',
                ]),
                'end' => new Google_Service_Calendar_EventDateTime([
                    'dateTime' => $endDateTime,
                    'timeZone' => 'Asia/Kathmandu',
                ]),
                'conferenceData' => [
                    'createRequest' => [
                        'requestId' => uniqid(),
                        'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                    ],
                ],
            ]);

            $calendarId = env('GOOGLE_CALENDAR_ID', 'primary');
            $createdEvent = $this->service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);

            $meetingLink = $createdEvent->getHangoutLink();

            // If no Google Meet link was generated, try to create one manually or use alternative
            if (!$meetingLink) {
                // Try to update the event with conference data
                $meetingLink = $this->tryCreateGoogleMeetLink($createdEvent) ?: $this->generateMeetLink();
            }

            return [
                'success' => true,
                'event_id' => $createdEvent->getId(),
                'meeting_link' => $meetingLink,
                'event_link' => $createdEvent->getHtmlLink(),
                'attendee_email' => $attendeeEmail,
            ];

        } catch (\Exception $e) {
            // If conference creation fails, try without conference data
            try {
                $simpleEvent = new Google_Service_Calendar_Event([
                    'summary' => $title,
                    'description' => $description . "\n\nAttendee: " . $attendeeEmail . "\n\nNote: Please use the meeting link provided in the email.",
                    'start' => new Google_Service_Calendar_EventDateTime([
                        'dateTime' => $startDateTime,
                        'timeZone' => 'Asia/Kathmandu',
                    ]),
                    'end' => new Google_Service_Calendar_EventDateTime([
                        'dateTime' => $endDateTime,
                        'timeZone' => 'Asia/Kathmandu',
                    ]),
                ]);

                $calendarId = env('GOOGLE_CALENDAR_ID', 'primary');
                $createdEvent = $this->service->events->insert($calendarId, $simpleEvent);

                // Generate a manual Google Meet link
                $meetingLink = $this->generateMeetLink();

                return [
                    'success' => true,
                    'event_id' => $createdEvent->getId(),
                    'meeting_link' => $meetingLink,
                    'event_link' => $createdEvent->getHtmlLink(),
                    'attendee_email' => $attendeeEmail,
                    'note' => 'Calendar event created without automatic Google Meet integration. Meeting link generated manually.',
                ];

            } catch (\Exception $fallbackError) {
                return [
                    'success' => false,
                    'error' => 'Primary error: ' . $e->getMessage() . ' | Fallback error: ' . $fallbackError->getMessage(),
                ];
            }
        }
    }

    /**
     * Try to create a Google Meet link by updating the event
     */
    private function tryCreateGoogleMeetLink($event)
    {
        try {
            // Try to patch the event with conference data
            $conferenceData = [
                'createRequest' => [
                    'requestId' => uniqid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
                ],
            ];

            $event->setConferenceData($conferenceData);
            $calendarId = env('GOOGLE_CALENDAR_ID', 'primary');

            $updatedEvent = $this->service->events->patch($calendarId, $event->getId(), $event, ['conferenceDataVersion' => 1]);

            return $updatedEvent->getHangoutLink();
        } catch (\Exception $e) {
            // If this fails, we'll use the alternative method
            return null;
        }
    }

    /**
     * Generate a meeting link using alternative methods
     */
    private function generateMeetLink()
    {
        // Instead of fake Google Meet links, use Jitsi Meet which is free and reliable
        $meetingId = $this->generateMeetingId();
        return "https://meet.jit.si/SkillLink-Interview-{$meetingId}";
    }

    /**
     * Generate a meeting ID for video conferencing
     */
    private function generateMeetingId()
    {
        // Generate a unique meeting ID using timestamp and random string
        $timestamp = time();
        $random = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6);

        return "{$timestamp}-{$random}";
    }

    public function isAuthenticated()
    {
        try {
            $serviceAccountPath = storage_path('app/google-calendar/service-account-credentials.json');
            $tokenPath = storage_path('app/google-calendar/oauth-token.json');

            // Service account is always authenticated if file exists
            if (file_exists($serviceAccountPath)) {
                return true;
            }

            // Check OAuth token
            if (!file_exists($tokenPath)) {
                return false;
            }

            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);

            return !$this->client->isAccessTokenExpired() || $this->client->getRefreshToken();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getCalendars()
    {
        try {
            $calendarList = $this->service->calendarList->listCalendarList();
            return $calendarList->getItems();
        } catch (\Exception $e) {
            throw new \Exception('Failed to get calendars: ' . $e->getMessage());
        }
    }
}
