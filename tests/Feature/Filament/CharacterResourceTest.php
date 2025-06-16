<?php

use App\Filament\Resources\CharacterResource;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;

// Prepare a user for authentication
beforeEach(function () {
    $this->user = User::factory()->create();
    // Ensure the user can access the Filament panel if policies are set up.
    // This might involve setting a flag on the user model or ensuring
    // the user implements Filament\Models\Contracts\FilamentUser
    // and its canAccessPanel() method returns true for the panel.
    // For now, we assume the default User model can access the default panel.
    actingAs($this->user);
    Filament::setCurrentPanel(Filament::getPanel('filament'));
});

// Test 1: File upload component is present on the create character page
test('file upload component is present on create character page', function () {
    Livewire::test(CharacterResource\Pages\CreateCharacter::class)
        ->assertFormFieldExists('data'); // The FileUpload component is named 'data' in CharacterResource.php
});

// Test 2: Uploading a valid JSON file populates the character fields
test('uploading a valid json file populates character fields', function () {
    $validJsonContent = json_encode([
        'name' => 'Test Character',
        'armorClass' => 15,
        'maxHitPoints' => 50,
        'stats' => [
            'STR' => 10,
            'DEX' => 12,
            'CON' => 14,
            'INT' => 8,
            'WIS' => 13,
            'CHA' => 16,
        ],
        'some_other_data' => 'value'
    ]);

    $fakeFile = UploadedFile::fake()->createWithContent('character.json', $validJsonContent);

    $livewireTest = Livewire::test(CharacterResource\Pages\CreateCharacter::class)
        ->set('data', $fakeFile) // Correctly simulate file upload
        ->call('validate') // Trigger validation which should also trigger afterStateUpdated
        ->call('mountAction', 'loadCharacterData'); // A dummy action to potentially force re-render if needed, or just rely on validate.
                                                 // More directly, one might try $livewireTest->instance()->fillForm(); but that's less clean.

    $formData = $livewireTest->get('data');

    expect($formData['name'])->toBe('Test Character');
    expect($formData['ac'])->toBe(15);
    expect($formData['max_health'])->toBe(50);
    expect($formData['current_health'])->toBe(50);
    expect($formData['strength'])->toBe(10);
    expect($formData['dexterity'])->toBe(12);
    expect($formData['constitution'])->toBe(14);
    expect($formData['intelligence'])->toBe(8);
    expect($formData['wisdom'])->toBe(13);
    expect($formData['charisma'])->toBe(16);
    expect($formData['data'])->toBe(json_decode($validJsonContent, true));

    $livewireTest->assertHasNoFormErrors();

});


// Test 3: Test that submitting the form with uploaded JSON creates the character
test('submitting form with uploaded json creates character in database', function () {
    $validJsonContent = json_encode([
        'name' => 'Database Character',
        'armorClass' => 18,
        'maxHitPoints' => 75,
        'stats' => [
            'STR' => 16,
            'DEX' => 10,
            'CON' => 15,
            'INT' => 9,
            'WIS' => 11,
            'CHA' => 10,
        ],
        'type' => 'player', // Required field for Character model
        'unique_field' => 'unique_value_for_db_test' // To ensure we find this specific entry
    ]);

    $fakeFile = UploadedFile::fake()->createWithContent('character.json', $validJsonContent);

    Livewire::test(CharacterResource\Pages\CreateCharacter::class)
        ->fillForm([ // Fill other required fields not set by JSON
            'type' => 'player',
        ])
        ->set('data', $fakeFile) // Correctly simulate file upload
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas('characters', [
        'name' => 'Database Character',
        'ac' => 18,
        'max_health' => 75,
        'strength' => 16,
        'dexterity' => 10,
        'constitution' => 15,
        'intelligence' => 9,
        'wisdom' => 11,
        'charisma' => 10,
        'type' => 'player',
        // The 'data' column in the DB should store the full JSON
        'data' => $validJsonContent,
    ]);
});


// Test 4: Uploading an invalid JSON file (syntax error)
// Note: Filament's FileUpload handles mime type validation, but not content validation directly unless custom rules are added.
// The `afterStateUpdated` hook is where our json_decode happens. If it fails, fields won't be populated.
// We'll test that fields are NOT populated if JSON is malformed.
test('uploading a syntactically invalid json file does not populate fields', function () {
    $invalidJsonContent = '{"name": "Test Character", "armorClass": 15, "maxHitPoints": 50, stats: {STR: 10}}'; // Malformed JSON

    $fakeFile = UploadedFile::fake()->createWithContent('invalid_character.json', $invalidJsonContent);

    $livewireTest = Livewire::test(CharacterResource\Pages\CreateCharacter::class)
        ->set('data', $fakeFile) // Correctly simulate file upload
        ->call('validate'); // Trigger processing
        // ->call('mountAction', 'loadCharacterData'); // Potentially force re-render

    $formData = $livewireTest->get('data');

    expect($formData['name'])->toBeNull();
    expect($formData['ac'])->toBeNull();
    expect($formData['max_health'])->toBe(10); // Default from CharacterResource schema
    expect($formData['current_health'])->toBe(10); // Default
    expect($formData['strength'])->toBe(10); // Default
    expect($formData['dexterity'])->toBe(10); // Default
    expect($formData['constitution'])->toBe(10); // Default
    expect($formData['intelligence'])->toBe(10); // Default
    expect($formData['wisdom'])->toBe(10); // Default
    expect($formData['charisma'])->toBe(10); // Default
    // For invalid JSON, the 'data' field (raw JSON) should be null or an empty array.
    // It should not be the decoded (malformed) JSON.
    // And it should ideally not be the UploadedFile instance.
    expect(is_array($formData['data']) && !empty($formData['data']))->toBeFalse();


    // We might not get a form error directly on the 'data' field from json_decode failure in afterStateUpdated
    // unless we explicitly add one using $fail or a notification (which we haven't added).
    // The primary check here is that the fields are not incorrectly populated.
});

// Test 5: Uploading a file that is not JSON (e.g., a text file)
// Filament's FileUpload `acceptedFileTypes` should handle this.
test('uploading a non-json file shows a validation error', function () {
    $notJsonContent = 'This is just a text file.';
    $fakeFile = UploadedFile::fake()->createWithContent('character.txt', $notJsonContent);

    Livewire::test(CharacterResource\Pages\CreateCharacter::class)
        ->set('data', $fakeFile) // Correctly simulate file upload
        ->call('validate') // Trigger validation
        ->assertHasFormErrors(['data']); // Expect error on the 'data' (FileUpload) field itself due to wrong file type
});

// Test 6: Test with a JSON that's valid but missing some expected fields
test('uploading valid json missing some fields populates available fields', function () {
    $partialJsonContent = json_encode([
        'name' => 'Partial Character',
        'armorClass' => 12,
        // maxHitPoints is missing
        'stats' => [
            'STR' => 8,
            // DEX is missing
        ],
    ]);

    $fakeFile = UploadedFile::fake()->createWithContent('partial_character.json', $partialJsonContent);

    $livewireTest = Livewire::test(CharacterResource\Pages\CreateCharacter::class)
        ->set('data', $fakeFile) // Correctly simulate file upload
        ->call('validate');
        // ->call('mountAction', 'loadCharacterData'); // Potentially force re-render

    $formData = $livewireTest->get('data');

    expect($formData['name'])->toBe('Partial Character');
    expect($formData['ac'])->toBe(12);
    expect($formData['max_health'])->toBe(10); // Default from CharacterResource schema
    expect($formData['current_health'])->toBe(10); // Default
    expect($formData['strength'])->toBe(8);
    expect($formData['dexterity'])->toBe(10); // Default
    expect($formData['constitution'])->toBe(10); // Default
    expect($formData['intelligence'])->toBe(10); // Default
    expect($formData['wisdom'])->toBe(10); // Default
    expect($formData['charisma'])->toBe(10); // Default
    expect($formData['data'])->toBe(json_decode($partialJsonContent, true));

    $livewireTest->assertHasNoFormErrors(); // Assuming missing fields are not required by the JSON schema itself at this stage
});

test('can create character with valid json upload', function () {
    $jsonContent = json_encode([
        'name' => 'Uploaded Test Character',
        'armorClass' => 16,
        'maxHitPoints' => 60,
        'stats' => [
            'STR' => 14,
            'DEX' => 13,
            'CON' => 15,
            'INT' => 10,
            'WIS' => 12,
            'CHA' => 8,
        ],
        'source' => 'JSON Upload Sourcebook'
    ]);

    $fakeFile = UploadedFile::fake()->createWithContent('character.json', $jsonContent);

    Livewire::test(CharacterResource\Pages\CreateCharacter::class)
        ->fillForm([ // Fill any other required fields not covered by JSON parsing.
            'type' => 'player', // 'type' is required in the schema
        ])
        ->set('data.character_json_upload', $fakeFile) // Set the file for the upload component
        // The afterStateUpdated hook should now populate other fields based on the JSON.
        // Then we call the create action.
        ->call('create')
        ->assertHasNoFormErrors();

    // Assert the character was created in the database
    $this->assertDatabaseHas('characters', [
        'name' => 'Uploaded Test Character',
        'ac' => 16,
        'max_health' => 60,
        'current_health' => 60, // Assuming current_health is set to max_health by the hook
        'strength' => 14,
        'dexterity' => 13,
        'constitution' => 15,
        'intelligence' => 10,
        'wisdom' => 12,
        'charisma' => 8,
        'type' => 'player', // Ensure this was also saved
    ]);

    // Assert the 'data' JSON column
    $character = Character::latest()->first();
    expect($character)->not->toBeNull();
    expect($character->data)->toBe(json_decode($jsonContent, true)); // Compare as arrays/objects
});

?>
