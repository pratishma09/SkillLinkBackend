<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Google_Client;
use Google_Service_Calendar;

class SetupGoogleCalendar extends Command
{
    protected $signature = 'google:setup-calendar';
    protected $description = 'Setup Google Calendar OAuth authentication';

    public function handle()
    {
        $this->info('Setting up Google Calendar authentication...');

        try {
            $client = new Google_Client();
            $client->setAuthConfig(storage_path('app/google-calendar/oauth-credentials.json'));
            $client->setScopes([Google_Service_Calendar::CALENDAR]);
            $client->setRedirectUri('urn:ietf:wg:oauth:2.0:oob');
            $client->setAccessType('offline');
            $client->setPrompt('consent');

            // Check if token already exists
            $tokenPath = storage_path('app/google-calendar/oauth-token.json');
            if (file_exists($tokenPath)) {
                $accessToken = json_decode(file_get_contents($tokenPath), true);
                $client->setAccessToken($accessToken);

                if ($client->isAccessTokenExpired()) {
                    $this->warn('Access token is expired. Refreshing...');
                    
                    if ($client->getRefreshToken()) {
                        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
                        $this->info('Token refreshed successfully!');
                        return 0;
                    } else {
                        $this->warn('No refresh token available. Need to re-authenticate.');
                    }
                } else {
                    $this->info('Google Calendar is already authenticated and token is valid!');
                    return 0;
                }
            }

            // Get new token
            $authUrl = $client->createAuthUrl();
            $this->info('Open this URL in your browser:');
            $this->line($authUrl);
            $this->line('');

            $authCode = $this->ask('Paste the authorization code here');

            if (empty($authCode)) {
                $this->error('Authorization code is required!');
                return 1;
            }

            // Exchange code for token
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            if (isset($accessToken['error'])) {
                $this->error('Error getting access token: ' . $accessToken['error']);
                return 1;
            }

            // Save token
            file_put_contents($tokenPath, json_encode($accessToken));
            $this->info('Access token saved successfully!');

            // Test the connection
            $service = new Google_Service_Calendar($client);
            $calendarList = $service->calendarList->listCalendarList();
            
            $this->info('Google Calendar authentication successful!');
            $this->info('Available calendars:');
            
            foreach ($calendarList->getItems() as $calendar) {
                $this->line('- ' . $calendar->getSummary() . ' (' . $calendar->getId() . ')');
            }

            $this->line('');
            $this->info('Make sure to set GOOGLE_CALENDAR_ID in your .env file to one of the calendar IDs above.');

            return 0;

        } catch (\Exception $e) {
            $this->error('Error setting up Google Calendar: ' . $e->getMessage());
            return 1;
        }
    }
}
