<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\Encounter;
use App\Models\User;
use App\Models\Campaign;
use App\Events\TorchTimerUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Artisan;

class DecrementTorchTimersTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Campaign $campaign;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();

        $this->user = User::factory()->create();
        $this->campaign = Campaign::factory()->create(['gm_user_id' => $this->user->id]);
    }

    /** @test */
    public function command_decrements_running_timers()
    {
        $encounter1 = Encounter::factory()->create([
            'campaign_id' => $this->campaign->id,
            'torch_timer_duration' => 60,
            'torch_timer_remaining' => 10,
            'torch_timer_is_running' => true,
        ]);
        $encounter2 = Encounter::factory()->create([
            'campaign_id' => $this->campaign->id,
            'torch_timer_duration' => 60,
            'torch_timer_remaining' => 5,
            'torch_timer_is_running' => true,
        ]);

        Artisan::call('app:decrement-torch-timers');

        $encounter1->refresh();
        $encounter2->refresh();

        $this->assertEquals(9, $encounter1->torch_timer_remaining);
        $this->assertTrue($encounter1->torch_timer_is_running);
        Event::assertDispatched(TorchTimerUpdated::class, function ($event) use ($encounter1) {
            return $event->encounterId === $encounter1->id && $event->remaining === 9 && $event->isRunning === true;
        });

        $this->assertEquals(4, $encounter2->torch_timer_remaining);
        $this->assertTrue($encounter2->torch_timer_is_running);
        Event::assertDispatched(TorchTimerUpdated::class, function ($event) use ($encounter2) {
            return $event->encounterId === $encounter2->id && $event->remaining === 4 && $event->isRunning === true;
        });
    }

    /** @test */
    public function command_stops_timer_when_remaining_reaches_zero()
    {
        $encounter = Encounter::factory()->create([
            'campaign_id' => $this->campaign->id,
            'torch_timer_duration' => 60,
            'torch_timer_remaining' => 1,
            'torch_timer_is_running' => true,
        ]);

        Artisan::call('app:decrement-torch-timers');

        $encounter->refresh();
        $this->assertEquals(0, $encounter->torch_timer_remaining);
        $this->assertFalse($encounter->torch_timer_is_running);
        Event::assertDispatched(TorchTimerUpdated::class, function ($event) use ($encounter) {
            return $event->encounterId === $encounter->id && $event->remaining === 0 && $event->isRunning === false;
        });
    }

    /** @test */
    public function command_does_not_affect_paused_timers()
    {
        $encounter = Encounter::factory()->create([
            'campaign_id' => $this->campaign->id,
            'torch_timer_duration' => 60,
            'torch_timer_remaining' => 10,
            'torch_timer_is_running' => false, // Paused
        ]);

        Artisan::call('app:decrement-torch-timers');

        $encounter->refresh();
        $this->assertEquals(10, $encounter->torch_timer_remaining); // Unchanged
        $this->assertFalse($encounter->torch_timer_is_running);
        Event::assertNotDispatched(TorchTimerUpdated::class, function ($event) use ($encounter) {
            return $event->encounterId === $encounter->id;
        });
    }

    /** @test */
    public function command_does_not_affect_timers_already_at_zero()
    {
        $encounter = Encounter::factory()->create([
            'campaign_id' => $this->campaign->id,
            'torch_timer_duration' => 60,
            'torch_timer_remaining' => 0,
            'torch_timer_is_running' => true, // Should ideally be false if remaining is 0, but command should handle this state
        ]);

        Artisan::call('app:decrement-torch-timers');

        $encounter->refresh();
        $this->assertEquals(0, $encounter->torch_timer_remaining);
        // The command should set is_running to false if remaining is 0
        $this->assertFalse($encounter->torch_timer_is_running);
        Event::assertDispatched(TorchTimerUpdated::class, function ($event) use ($encounter) {
            // Event is dispatched because is_running state changes
            return $event->encounterId === $encounter->id && $event->remaining === 0 && $event->isRunning === false;
        });
    }

    /** @test */
    public function command_handles_no_active_timers_gracefully()
    {
        // No encounters created, or only paused/zeroed encounters

        Encounter::factory()->create([ // Paused timer
            'campaign_id' => $this->campaign->id,
            'torch_timer_duration' => 60,
            'torch_timer_remaining' => 10,
            'torch_timer_is_running' => false,
        ]);
         Encounter::factory()->create([ // Zeroed timer
            'campaign_id' => $this->campaign->id,
            'torch_timer_duration' => 60,
            'torch_timer_remaining' => 0,
            'torch_timer_is_running' => false,
        ]);


        Artisan::call('app:decrement-torch-timers');
        $this->artisan('app:decrement-torch-timers')
             ->expectsOutput('Starting to decrement torch timers...')
             ->expectsOutput('No active torch timers to decrement.')
             ->expectsOutput('Finished decrementing torch timers.')
             ->assertExitCode(0);

        Event::assertNotDispatched(TorchTimerUpdated::class);
    }
}
