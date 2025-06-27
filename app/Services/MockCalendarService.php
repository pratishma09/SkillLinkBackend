<?php

namespace App\Services;

use Carbon\Carbon;

class MockCalendarService
{
    public function createMeetingEvent($title, $description, $startDateTime, $endDateTime, $attendeeEmail)
    {
        // Generate a reliable meeting link using Jitsi Meet
        $meetingId = $this->generateMeetingId();
        $meetingLink = "https://meet.jit.si/SkillLink-Interview-{$meetingId}";

        // Log the meeting details for debugging
        \Log::info('Mock Calendar Event Created', [
            'title' => $title,
            'description' => $description,
            'start' => $startDateTime,
            'end' => $endDateTime,
            'attendee' => $attendeeEmail,
            'meeting_link' => $meetingLink
        ]);

        return [
            'success' => true,
            'event_id' => 'mock-event-' . uniqid(),
            'meeting_link' => $meetingLink,
            'event_link' => 'https://calendar.google.com/calendar/mock-event',
            'note' => 'Using Jitsi Meet for video conferencing (Google Calendar not configured)',
        ];
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
        return true; // Mock service is always "authenticated"
    }

    public function getCalendars()
    {
        return [
            (object) [
                'id' => 'primary',
                'summary' => 'Primary Calendar (Mock)'
            ],
            (object) [
                'id' => 'mock@example.com',
                'summary' => 'Mock Calendar'
            ]
        ];
    }
}
