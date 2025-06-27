<?php

namespace Tests\Feature\Filament;

use Tests\TestCase;
use App\Models\User;
use App\Models\Monster;
use App\Filament\Resources\MonsterResource;
use App\Livewire\BulkImportMonsters; // Import the Livewire component
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

class MonsterImportActionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('livewire-tmp'); // Fake the disk Livewire uses for temporary uploads
        // Create a user and act as that user.
        $user = User::factory()->create();
        $this->actingAs($user);
        // Set the current panel for the test
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('app'));
    }

    public function test_monster_list_page_contains_bulk_import_action_and_modal()
    {
        // Pass the panel to getUrl to be explicit
        $this->get(MonsterResource::getUrl('index', panel: 'app'))
            ->assertSuccessful()
            ->assertSee('Bulk Import Monsters'); // Check if the button text is present
    }


    public function test_bulk_import_action_can_initiate_import_process()
    {
        // This test is more focused on the Livewire component itself,
        // but we trigger it as if it's in the modal context.
        $monstersData = [
            [
                "name" => "Test Dragon",
                "slug" => "test-dragon",
                "armor_class" => 20,
                "hit_points" => 200
            ]
        ];
        $file = UploadedFile::fake()->createWithContent('monsters.json', json_encode($monstersData));

        // Simulate calling the Livewire component that would be in the modal
        Livewire::test(BulkImportMonsters::class)
            ->set('file', $file)
            ->call('save')
            // Workaround for session assertion:
            // Check if the success message is rendered in the component's view
            ->assertSee('Successfully imported 1 monsters.')
            ->assertDispatched('refreshMonstersTable') // Corrected event name
            ->assertDispatched('bulk-import-finished'); // Check if event for modal closing is dispatched

        $this->assertDatabaseHas('monsters', ['slug' => 'test-dragon']);
    }

}
