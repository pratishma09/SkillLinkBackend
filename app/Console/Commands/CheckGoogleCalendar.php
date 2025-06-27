<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GoogleCalendarService;

class CheckGoogleCalendar extends Command
{
    protected $signature = 'google:check-calendar';
    protected $description = 'Check Google Calendar authentication status';

    public function handle()
    {
        try {
            $googleCalendar = new GoogleCalendarService();
            
            if ($googleCalendar->isAuthenticated()) {
                $this->info('✅ Google Calendar is properly authenticated!');
                
                // List available calendars
                $calendars = $googleCalendar->getCalendars();
                $this->info('Available calendars:');
                
                foreach ($calendars as $calendar) {
                    $this->line('- ' . $calendar->getSummary() . ' (' . $calendar->getId() . ')');
                }
                
                $currentCalendarId = env('GOOGLE_CALENDAR_ID', 'primary');
                $this->info("Current calendar ID in .env: {$currentCalendarId}");
                
            } else {
                $this->error('❌ Google Calendar is not authenticated.');
                $this->info('Run: php artisan google:setup-calendar');
            }
            
        } catch (\Exception $e) {
            $this->error('Error checking Google Calendar: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
