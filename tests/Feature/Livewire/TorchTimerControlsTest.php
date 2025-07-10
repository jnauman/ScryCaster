<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use App\Models\Encounter;
use App\Models\User; // Assuming encounters are tied to a campaign GM'd by a user
use App\Models\Campaign;
use App\Livewire\TorchTimerControls;
use App\Events\TorchTimerUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;

class TorchTimerControlsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Campaign $campaign;
    private Encounter $encounter;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake(); // Fake events for testing

        $this->user = User::factory()->create();
        $this->campaign = Campaign::factory()->create(['gm_user_id' => $this->user->id]);
        $this->encounter = Encounter::factory()->create([
            'campaign_id' => $this->campaign->id,
            'torch_timer_duration' => 60,
            'torch_timer_remaining' => 60,
            'torch_timer_is_running' => false,
        ]);

        // Acting as the user for any Filament/auth context if needed by Livewire components
        $this->actingAs($this->user);
    }

    /** @test */
    public function component_mounts_with_correct_initial_values()
    {
        Livewire::test(TorchTimerControls::class, ['encounter' => $this->encounter])
            ->assertSet('duration', 60)
            ->assertSet('remaining', 60)
            ->assertSet('isRunning', false);
    }

    /** @test */
    public function can_update_torch_duration()
    {
        Livewire::test(TorchTimerControls::class, ['encounter' => $this->encounter])
            ->set('duration', 90)
            ->assertSet('duration', 90)
            ->assertSet('remaining', 90) // Should reset remaining when duration changes if remaining > new duration or remaining was null
            ->assertSet('isRunning', false); // Should stop timer

        $this->encounter->refresh();
        $this->assertEquals(90, $this->encounter->torch_timer_duration);
        $this->assertEquals(90, $this->encounter->torch_timer_remaining);
        $this->assertFalse($this->encounter->torch_timer_is_running);
        Event::assertDispatched(TorchTimerUpdated::class);
    }

    /** @test */
    public function can_start_and_pause_timer()
    {
        $test = Livewire::test(TorchTimerControls::class, ['encounter' => $this->encounter]);

        // Start timer
        $test->call('startPauseTimer')
            ->assertSet('isRunning', true);
        $this->encounter->refresh();
        $this->assertTrue($this->encounter->torch_timer_is_running);
        Event::assertDispatched(TorchTimerUpdated::class, function ($event) {
            return $event->isRunning === true && $event->remaining === 60;
        });

        // Pause timer
        $test->call('startPauseTimer')
            ->assertSet('isRunning', false);
        $this->encounter->refresh();
        $this->assertFalse($this->encounter->torch_timer_is_running);
        Event::assertDispatched(TorchTimerUpdated::class, function ($event) {
            return $event->isRunning === false && $event->remaining === 60;
        });
    }

    /** @test */
    public function starting_timer_when_remaining_is_zero_resets_it_to_duration()
    {
        $this->encounter->update(['torch_timer_remaining' => 0, 'torch_timer_is_running' => false]);
        Livewire::test(TorchTimerControls::class, ['encounter' => $this->encounter])
            ->call('startPauseTimer')
            ->assertSet('isRunning', true)
            ->assertSet('remaining', 60); // Resets to duration

        $this->encounter->refresh();
        $this->assertTrue($this->encounter->torch_timer_is_running);
        $this->assertEquals(60, $this->encounter->torch_timer_remaining);
        Event::assertDispatched(TorchTimerUpdated::class);
    }

    /** @test */
    public function can_reset_timer()
    {
        $this->encounter->update(['torch_timer_remaining' => 30, 'torch_timer_is_running' => true]);
        Livewire::test(TorchTimerControls::class, ['encounter' => $this->encounter])
            ->call('resetTimer')
            ->assertSet('remaining', 60)
            ->assertSet('isRunning', false);

        $this->encounter->refresh();
        $this->assertEquals(60, $this->encounter->torch_timer_remaining);
        $this->assertFalse($this->encounter->torch_timer_is_running);
        Event::assertDispatched(TorchTimerUpdated::class);
    }

    /** @test */
    public function can_add_time()
    {
        $this->encounter->update(['torch_timer_remaining' => 30]);
        Livewire::test(TorchTimerControls::class, ['encounter' => $this->encounter])
            ->call('addTime', 15)
            ->assertSet('remaining', 45);

        $this->encounter->refresh();
        $this->assertEquals(45, $this->encounter->torch_timer_remaining);
        Event::assertDispatched(TorchTimerUpdated::class);
    }

    /** @test */
    public function can_subtract_time()
    {
        $this->encounter->update(['torch_timer_remaining' => 30]);
        Livewire::test(TorchTimerControls::class, ['encounter' => $this->encounter])
            ->call('subtractTime', 15)
            ->assertSet('remaining', 15);

        $this->encounter->refresh();
        $this->assertEquals(15, $this->encounter->torch_timer_remaining);
        Event::assertDispatched(TorchTimerUpdated::class);
    }

    /** @test */
    public function cannot_subtract_time_below_zero()
    {
        $this->encounter->update(['torch_timer_remaining' => 10]);
        Livewire::test(TorchTimerControls::class, ['encounter' => $this->encounter])
            ->call('subtractTime', 15)
            ->assertSet('remaining', 0);

        $this->encounter->refresh();
        $this->assertEquals(0, $this->encounter->torch_timer_remaining);
        Event::assertDispatched(TorchTimerUpdated::class);
    }

    /** @test */
    public function setting_duration_to_zero_resets_and_stops_timer()
    {
        Livewire::test(TorchTimerControls::class, ['encounter' => $this->encounter])
            ->set('duration', 0)
            ->assertSet('duration', 0)
            ->assertSet('remaining', 0)
            ->assertSet('isRunning', false);

        $this->encounter->refresh();
        $this->assertEquals(0, $this->encounter->torch_timer_duration);
        $this->assertEquals(0, $this->encounter->torch_timer_remaining);
        $this->assertFalse($this->encounter->torch_timer_is_running);
        Event::assertDispatched(TorchTimerUpdated::class);
    }
}
