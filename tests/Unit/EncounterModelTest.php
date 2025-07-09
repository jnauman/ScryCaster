<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Encounter;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EncounterModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_initializes_with_null_torch_timer_fields_if_not_provided()
    {
        $encounter = Encounter::factory()->create();

        $this->assertNull($encounter->torch_timer_duration);
        $this->assertNull($encounter->torch_timer_remaining);
        $this->assertFalse($encounter->torch_timer_is_running); // Default is false in migration
    }

    /** @test */
    public function it_can_have_torch_timer_fields_set()
    {
        $encounter = Encounter::factory()->create([
            'torch_timer_duration' => 60,
            'torch_timer_remaining' => 45,
            'torch_timer_is_running' => true,
        ]);

        $this->assertEquals(60, $encounter->torch_timer_duration);
        $this->assertEquals(45, $encounter->torch_timer_remaining);
        $this->assertTrue($encounter->torch_timer_is_running);
    }
}
