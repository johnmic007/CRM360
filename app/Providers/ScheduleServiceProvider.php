<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);

            $schedule->command('tasks:send-notifications')
                     ->dailyAt('7:00')
                     ->withoutOverlapping();
        });
    }

    public function register()
    {
        //
    }
}
