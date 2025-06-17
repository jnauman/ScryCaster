<?php

namespace Tests\Unit;

use App\Filament\Resources\CharacterResource; // Assuming the static method will be here
use PHPUnit\Framework\TestCase; // Pest uses PHPUnit TestCase as a base for unit tests

// For Pest, you can also use a simpler closure-based test structure if preferred,
// but this will work and is clear for unit testing a specific method.

it('parses valid character json and extracts correct data', function () {
    $sampleJsonString = json_encode([
        'name' => 'Test Unit Character',
        'armorClass' => 18,
        'maxHitPoints' => 150,
        'stats' => [
            'STR' => 12,
            'DEX' => 15,
            'CON' => 13,
            'INT' => 11,
            'WIS' => 14,
            'CHA' => 9,
        ],
        'sourcebook' => 'Test Manual',
        'level' => 5
    ]);

    // This assumes CharacterResource::parseCharacterJson is made static and accessible.
    // The user will need to implement this refactoring.
    $parsedData = CharacterResource::parseCharacterJson($sampleJsonString);

    // Assert that the parsing was successful (not null)
    expect($parsedData)->not->toBeNull();

    // Assert specific fields
    expect($parsedData['name'])->toBe('Test Unit Character');
    expect($parsedData['ac'])->toBe(18);
    expect($parsedData['max_health'])->toBe(150);
    // expect($parsedData['current_health'])->toBe(150); // Assuming current_health defaults to max_health

    // Assert stats
    expect($parsedData['strength'])->toBe(12);
    expect($parsedData['dexterity'])->toBe(15);
    expect($parsedData['constitution'])->toBe(13);
    expect($parsedData['intelligence'])->toBe(11);
    expect($parsedData['wisdom'])->toBe(14);
    expect($parsedData['charisma'])->toBe(9);

    // Assert that the 'data' field contains the full parsed JSON as an array
    expect($parsedData['data'])->toBeArray();
    expect($parsedData['data']['name'])->toBe('Test Unit Character'); // Check a few keys
    expect($parsedData['data']['sourcebook'])->toBe('Test Manual');
    expect($parsedData['data']['stats']['CON'])->toBe(13);
    // Compare the whole array
    expect($parsedData['data'])->toEqual(json_decode($sampleJsonString, true));
});

it('returns null for invalid json string', function () {
    $invalidJsonString = '{"name": "Broken", "armorClass": 15, stats: {STR: 10}}'; // Malformed JSON

    $parsedData = CharacterResource::parseCharacterJson($invalidJsonString);

    expect($parsedData)->toBeNull();
});

it('handles missing optional fields gracefully', function () {
    $partialJsonString = json_encode([
        'name' => 'Partial Char',
        'stats' => [
            'STR' => 10,
        ]
    ]);
    $parsedData = CharacterResource::parseCharacterJson($partialJsonString);

    expect($parsedData)->not->toBeNull();
    expect($parsedData['name'])->toBe('Partial Char');
    expect($parsedData['ac'])->toBeNull(); // Was not in JSON, should be null based on assumed static method logic
    expect($parsedData['max_health'])->toBeNull(); // Was not in JSON
    expect($parsedData['strength'])->toBe(10);
    expect($parsedData['dexterity'])->toBeNull(); // Was not in JSON

    // Check that the full partial JSON is still in 'data'
    expect($parsedData['data'])->toEqual(json_decode($partialJsonString, true));
});

?>
