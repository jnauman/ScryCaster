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
        Log::info('DecrementTorchTimers command started.');
        $this->info('Starting to decrement torch timers...');

        $activeEncounters = Encounter::where('torch_timer_is_running', true)
                                     ->where('torch_timer_remaining', '>', 0)
                                     ->get();

        if ($activeEncounters->isEmpty()) {
            Log::info('No active torch timers to decrement.');
            $this->info('No active torch timers to decrement.');
            return;
        }

        Log::info("Found {$activeEncounters->count()} active encounters to process.");

        foreach ($activeEncounters as $encounter) {
            $originalRemaining = $encounter->torch_timer_remaining;
            $encounter->torch_timer_remaining -= 1;

            $logMessage = "Encounter ID {$encounter->id}: Original remaining: {$originalRemaining}, New remaining: {$encounter->torch_timer_remaining}. Duration: {$encounter->torch_timer_duration}.";

            if ($encounter->torch_timer_remaining <= 0) {
                $encounter->torch_timer_remaining = 0; // Ensure it doesn't go negative
                $encounter->torch_timer_is_running = false;
                $logMessage .= " Timer stopped as it reached zero.";
            }

            $encounter->save();
            Log::info("Saved Encounter ID {$encounter->id}. Current state: Remaining={$encounter->torch_timer_remaining}, IsRunning={$encounter->torch_timer_is_running}. Broadcasting update.");

            // Broadcast the update
            try {
                event(new TorchTimerUpdated(
                    $encounter->id,
                    (int)$encounter->torch_timer_remaining,
                    (int)$encounter->torch_timer_duration,
                    (bool)$encounter->torch_timer_is_running
                ));
                Log::info("Event TorchTimerUpdated broadcasted for Encounter ID {$encounter->id}.");
                $this->line("Processed Encounter ID {$encounter->id}. Remaining: {$encounter->torch_timer_remaining}, Running: " . ($encounter->torch_timer_is_running ? 'Yes' : 'No'));
            } catch (\Exception $e) {
                Log::error("Error broadcasting TorchTimerUpdated for Encounter ID {$encounter->id}: " . $e->getMessage());
                $this->error("Failed to broadcast for Encounter ID {$encounter->id}: " . $e->getMessage());
            }
        }

        Log::info('DecrementTorchTimers command finished.');
        $this->info('Finished decrementing torch timers.');
    }
}
