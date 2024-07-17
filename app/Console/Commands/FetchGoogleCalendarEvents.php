<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\GoogleController;
use Illuminate\Support\Facades\Cache;

class FetchGoogleCalendarEvents extends Command
{
    protected $signature = 'googlecalendar:fetch';
    protected $description = 'Fetch events from Google Calendar and save to the database';
    private $googleController;

    public function __construct(GoogleController $googleController)
    {
        parent::__construct();
        $this->googleController = $googleController;
    }

    public function handle()
    {
        Cache::flush();

        $this->googleController->fetchEvents();

        $this->info('Google Calendar events fetched and saved successfully.');
    }
}
