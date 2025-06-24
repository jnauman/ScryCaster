<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Livewire\BulkImportMonsters;
use App\Models\Monster;
use App\Models\User; // If user association is tested
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

class MonsterImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // It's good practice to ensure storage fakes don't interfere across tests
        Storage::fake('avatars'); // or any other disk you use for livewire tmp uploads
        Storage::fake('livewire-tmp');
    }

    private function createValidJsonFile(array $data, string $filename = 'monsters.json'): UploadedFile
    {
        $content = json_encode($data);
        return UploadedFile::fake()->createWithContent($filename, $content);
    }

    public function test_can_import_monsters_from_valid_json()
    {
        $monstersData = [
            [
                "name" => "Ancient Dragon",
                "slug" => "ancient-dragon",
                "description" => "A very old and powerful dragon.",
                "armor_class" => 22,
                "hit_points" => 350,
                "level" => 20,
                "traits" => [
                  ["name" => "Frightful Presence", "description" => "DC 20 WIS or frightened."]
                ]
            ],
            [
                "name" => "Goblin",
                "slug" => "goblin",
                "description" => "A small, mischievous creature.",
                "armor_class" => 13,
                "hit_points" => 7,
                "level" => 1
            ]
        ];

        $file = $this->createValidJsonFile($monstersData);

        $component = Livewire::test(BulkImportMonsters::class)
            ->set('file', $file)
            ->call('save');

        // No need for session()->ageFlashData() if using assertSee for component's rendered output.
        $component->assertHasNoErrors()
            // Workaround for session assertion:
            // Check if the success message is rendered in the component's view
            ->assertSee('Successfully imported 2 monsters.');
            // Event dispatches might still be relevant if the success message appears AND events are fired.
            // ->assertDispatched('refreshMonstersTable')
            // ->assertDispatched('bulk-import-finished');


        $this->assertDatabaseCount('monsters', 2);
        $this->assertDatabaseHas('monsters', ['slug' => 'ancient-dragon', 'name' => 'Ancient Dragon']);
        $this->assertDatabaseHas('monsters', ['slug' => 'goblin', 'name' => 'Goblin']);

        $dragon = Monster::where('slug', 'ancient-dragon')->first();
        $this->assertIsArray($dragon->traits);
        $this->assertEquals("Frightful Presence", $dragon->traits[0]['name']);
    }

    public function test_handles_invalid_json_file_format()
    {
        $file = UploadedFile::fake()->createWithContent('invalid.json', 'this is not json');

        Livewire::test(BulkImportMonsters::class)
            ->set('file', $file)
            ->call('save')
            // Workaround for session assertion:
            // Check if the error message is rendered in the component's view
            ->assertSee('Invalid JSON file. Please check the file format.')
            ->assertDispatched('bulk-import-finished');


        $this->assertDatabaseCount('monsters', 0);
    }

    public function test_handles_json_with_invalid_monster_data_structure()
    {
        // Missing required 'name' and 'slug'
        $monstersData = [
            ["description" => "A monster missing name and slug"]
        ];
        $file = $this->createValidJsonFile($monstersData);

        Livewire::test(BulkImportMonsters::class)
            ->set('file', $file)
            ->call('save')
            // Workaround for session assertion:
            ->assertSee('Import failed.') // Check for part of the error message
            ->assertDispatched('bulk-import-finished');


        $this->assertDatabaseCount('monsters', 0);
    }

    public function test_handles_duplicate_slug_in_json()
    {
        $monstersData = [
            ["name" => "Duplicate Slugger", "slug" => "duplicate-slug"],
            ["name" => "Another Slugger", "slug" => "duplicate-slug"]
        ];
        $file = $this->createValidJsonFile($monstersData);

        Livewire::test(BulkImportMonsters::class)
            ->set('file', $file)
            ->call('save')
            // Workaround for session assertion:
            ->assertSee('Import failed.') // Check for part of the error message
            ->assertDispatched('bulk-import-finished');

        $this->assertDatabaseCount('monsters', 0);
    }

    public function test_validation_for_individual_monster_fields()
    {
        // Invalid level (negative number)
        $monstersData = [
            ["name" => "Valid Name", "slug" => "valid-slug", "level" => -5]
        ];
        $file = $this->createValidJsonFile($monstersData);

        Livewire::test(BulkImportMonsters::class)
            ->set('file', $file)
            ->call('save')
            // Workaround for session assertion:
            ->assertSee('Import failed.') // Check for part of the error message
            ->assertDispatched('bulk-import-finished');

        $this->assertDatabaseCount('monsters', 0);
    }
}
