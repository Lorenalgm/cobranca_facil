<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $this->configureSchedule($schedule);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    protected function configureSchedule(Schedule $schedule)
    {
        $schedule->call(function () {
            app('App\Http\Controllers\InvoiceController')->generateDailyInvoices();
        })->daily();
    }
}
