<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Update loan statuses and apply daily penalties
// Runs every day at 01:00 AM
Schedule::command('loans:update-statuses')
    ->dailyAt('01:00')
    ->timezone('Africa/Nairobi')
    ->description('Update loan statuses, apply 1% daily penalties for overdue loans, and mark defaulted loans');
