<?php

namespace Tests\Feature\Livewire;

use Tests\TestCase;
use App\Models\Encounter;
use App\Models\User;
use App\Models\Campaign;
use App\Livewire\TorchTimerDisplay;
use App\Events\TorchTimerUpdated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

class TorchTimerDisplayTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Campaign $campaign;
    private Encounter $encounter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->campaign = Campaign::factory()->create(['gm_user_id' => $this->user->id]);
        $this->encounter = Encounter::factory()->create([
            'campaign_id' => $this->campaign->id,
            'torch_timer_duration' => 60,
            'torch_timer_remaining' => 50,
            'torch_timer_is_running' => true,
        ]);
    }

    /** @test */
    public function component_mounts_with_initial_encounter_values()
    {
        Livewire::test(TorchTimerDisplay::class, ['encounter' => $this->encounter])
            ->assertSet('encounterId', $this->encounter->id)
            ->assertSet('remaining', 50)
            ->assertSet('duration', 60)
            ->assertSet('isRunning', true);
    }

    /** @test */
    public function component_updates_properties_on_torch_timer_updated_event()
    {
        $component = Livewire::test(TorchTimerDisplay::class, ['encounter' => $this->encounter]);

        $eventPayload = [
            'encounterId' => $this->encounter->id,
            'remaining' => 30,
            'duration' => 55,
            'isRunning' => false,
        ];

        // Simulate broadcasting the event.
        // Note: Livewire's event testing primarily focuses on events emitted *from* the component.
        // For testing Echo events received *by* the component, we typically assert the state change
        // after manually calling the event handler method or by triggering the event through Laravel's event system.

        // Directly call the handler for simplicity in this test environment
        $component->call('handleTorchTimerUpdate', $eventPayload);

        $component->assertSet('remaining', 30)
                  ->assertSet('duration', 55)
                  ->assertSet('isRunning', false);
    }

    /** @test */
    public function component_does_not_update_for_different_encounter_id_event()
    {
        $component = Livewire::test(TorchTimerDisplay::class, ['encounter' => $this->encounter]);

        $initialRemaining = $this->encounter->torch_timer_remaining;
        $initialDuration = $this->encounter->torch_timer_duration;
        $initialIsRunning = $this->encounter->torch_timer_is_running;

        $eventPayload = [
            'encounterId' => $this->encounter->id + 1, // Different encounter ID
            'remaining' => 10,
            'duration' => 20,
            'isRunning' => false,
        ];

        $component->call('handleTorchTimerUpdate', $eventPayload);

        // Assert that properties did not change
        $component->assertSet('remaining', $initialRemaining)
                  ->assertSet('duration', $initialDuration)
                  ->assertSet('isRunning', $initialIsRunning);
    }

    /** @test */
    public function view_renders_correctly_with_time()
    {
        Livewire::test(TorchTimerDisplay::class, ['encounter' => $this->encounter])
            ->assertSee(gmdate("H:i:s", $this->encounter->torch_timer_remaining * 60))
            ->assertSee('Burning'); // Since isRunning is true
    }

    /** @test */
    public function view_renders_correctly_when_timer_is_paused()
    {
        $this->encounter->update(['torch_timer_is_running' => false, 'torch_timer_remaining' => 40]);
        Livewire::test(TorchTimerDisplay::class, ['encounter' => $this->encounter])
            ->assertSee(gmdate("H:i:s", 40 * 60))
            ->assertSee('(Paused)');
    }

    /** @test */
    public function view_renders_correctly_when_timer_is_burnt_out()
    {
        $this->encounter->update(['torch_timer_is_running' => false, 'torch_timer_remaining' => 0]);
        Livewire::test(TorchTimerDisplay::class, ['encounter' => $this->encounter])
            ->assertSee(gmdate("H:i:s", 0))
            ->assertSee('(Burnt Out)');
    }
}
