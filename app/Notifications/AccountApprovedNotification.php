<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $loginUrl = config('app.frontend_url') . '/login';

        return (new MailMessage)
            ->subject('Account Approved - Welcome!')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your account has been approved by our admin team.')
            ->line('You can now log in to your account and start using our platform.')
            ->action('Log In Now', $loginUrl)
            ->line('Thank you for joining us!');
    }
} 