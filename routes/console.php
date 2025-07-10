<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

use Illuminate\Support\Facades\Schedule; // Add this line


// Schedule the torch timer decrement command
Schedule::command('app:decrement-torch-timers')->everyMinute();
