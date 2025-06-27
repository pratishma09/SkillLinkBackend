<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicantShortlisted extends Mailable
{
    use Queueable, SerializesModels;
    public $meeting_link;
    public $start_time;
    public $end_time;

    public $project_title;

    /**
     * Create a new message instance.
     */
    public function __construct($project_title, $meeting_link = null, $start_time = null, $end_time = null, )
    {
        $this->meeting_link = $meeting_link;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
        $this->project_title = $project_title;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Applicant Shortlisted',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.applicant-shortlisted',
            with: [
                'meeting_link' => $this->meeting_link,
                'start_time' => $this->start_time,
                'end_time' => $this->end_time,
                'project_title' => $this->project_title,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
