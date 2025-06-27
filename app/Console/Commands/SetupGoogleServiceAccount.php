<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google_Client;
use Google_Service_Calendar;

class SetupGoogleServiceAccount extends Command
{
    protected $signature = 'google:setup-service-account';
    protected $description = 'Setup Google Calendar using Service Account (recommended for production)';

    public function handle()
    {
        $this->info('Setting up Google Calendar with Service Account...');

        $serviceAccountPath = storage_path('app/google-calendar/service-account-credentials.json');

        if (!file_exists($serviceAccountPath)) {
            $this->error('Service account credentials file not found!');
            $this->line('');
            $this->info('To set up service account authentication:');
            $this->line('1. Go to Google Cloud Console: https://console.cloud.google.com/');
            $this->line('2. Select your project: laravel-social-auth-457204');
            $this->line('3. Go to APIs & Services → Credentials');
            $this->line('4. Click "Create Credentials" → "Service Account"');
            $this->line('5. Fill in the details and create');
            $this->line('6. Click on the created service account');
            $this->line('7. Go to "Keys" tab → "Add Key" → "Create new key" → JSON');
            $this->line('8. Download the JSON file');
            $this->line('9. Rename it to "service-account-credentials.json"');
            $this->line('10. Place it in: storage/app/google-calendar/');
            $this->line('');
            $this->warn('Important: You need to share your calendar with the service account email!');
            return 1;
        }

        try {
            $client = new Google_Client();
            $client->setAuthConfig($serviceAccountPath);
            $client->setScopes([Google_Service_Calendar::CALENDAR]);

            $service = new Google_Service_Calendar($client);
            
            // Test the connection
            $calendarList = $service->calendarList->listCalendarList();
            
            $this->info('✅ Service Account authentication successful!');
            $this->info('Available calendars:');
            
            foreach ($calendarList->getItems() as $calendar) {
                $this->line('- ' . $calendar->getSummary() . ' (' . $calendar->getId() . ')');
            }

            // Get service account email
            $credentials = json_decode(file_get_contents($serviceAccountPath), true);
            $serviceAccountEmail = $credentials['client_email'];

            $this->line('');
            $this->info("Service Account Email: {$serviceAccountEmail}");
            $this->line('');
            $this->warn('IMPORTANT: Make sure to share your calendar with this service account email!');
            $this->info('To share your calendar:');
            $this->line('1. Open Google Calendar');
            $this->line('2. Click on your calendar settings');
            $this->line('3. Click "Share with specific people"');
            $this->line("4. Add: {$serviceAccountEmail}");
            $this->line('5. Give "Make changes to events" permission');

            return 0;

        } catch (\Exception $e) {
            $this->error('Error setting up service account: ' . $e->getMessage());
            return 1;
        }
    }
}
