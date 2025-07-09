<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Encounter;
use App\Events\TorchTimerUpdated;
use Illuminate\Support\Facades\Log;

class DecrementTorchTimers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:decrement-torch-timers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Decrements active torch timers by one minute and broadcasts updates.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting to decrement torch timers...');

        $activeEncounters = Encounter::where('torch_timer_is_running', true)
                                     ->where('torch_timer_remaining', '>', 0)
                                     ->get();

        if ($activeEncounters->isEmpty()) {
            $this->info('No active torch timers to decrement.');
            return;
        }

        foreach ($activeEncounters as $encounter) {
            $encounter->torch_timer_remaining -= 1;
            $logMessage = "Encounter ID {$encounter->id}: Timer decremented. Remaining: {$encounter->torch_timer_remaining} min.";

            if ($encounter->torch_timer_remaining <= 0) {
                $encounter->torch_timer_remaining = 0; // Ensure it doesn't go negative
                $encounter->torch_timer_is_running = false;
                $logMessage .= " Timer stopped as it reached zero.";
            }

            $encounter->save();
            Log::info($logMessage);

            // Broadcast the update
            event(new TorchTimerUpdated(
                $encounter->id,
                $encounter->torch_timer_remaining,
                $encounter->torch_timer_duration,
                $encounter->torch_timer_is_running
            ));
            $this->line("Processed Encounter ID {$encounter->id}. Remaining: {$encounter->torch_timer_remaining}, Running: " . ($encounter->torch_timer_is_running ? 'Yes' : 'No'));
        }

        $this->info('Finished decrementing torch timers.');
    }
}
