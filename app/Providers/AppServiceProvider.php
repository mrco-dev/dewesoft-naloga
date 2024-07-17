<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot(Schedule $schedule)
    {
        $schedule->command('googlecalendar:fetch')->dailyAt('00:00');
    }
}
