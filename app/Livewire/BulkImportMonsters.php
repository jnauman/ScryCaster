<?php

namespace App\Livewire;

use Exception;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Monster;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator; // Import Validator
use Illuminate\Support\Str;

class BulkImportMonsters extends Component
{
    use WithFileUploads;

    public $file;

    public function save()
    {
        $this->validate([
            'file' => 'required|file|mimes:json|max:10240', // Max 10MB
        ]);

        if (!$this->file) {
            session()->flash('error', 'File not found.');
            $this->dispatch('bulk-import-finished'); // Emit event to close modal or indicate completion
            return;
        }

        $jsonContent = $this->file->get();
        $monstersData = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            session()->flash('error', 'Invalid JSON file. Please check the file format. Error: ' . json_last_error_msg());
            $this->file = null; // Reset file input
            $this->dispatch('bulk-import-finished');
            return;
        }

        if (!is_array($monstersData)) {
            session()->flash('error', 'Invalid JSON structure. Expected an array of monsters.');
            $this->file = null; // Reset file input
            $this->dispatch('bulk-import-finished');
            return;
        }

        $createdCount = 0;
        $errorCount = 0;
        $errors = [];

        Log::info('Starting save method.'); // Debug
        DB::beginTransaction();
        try {
            Log::info('Inside try block, before loop.'); // Debug
            foreach ($monstersData as $index => $monsterData) {
                Log::info("Processing monster at index {$index}.", ['data' => $monsterData]); // Debug
                // Define validation rules for each monster entry
                $validator = Validator::make($monsterData, [
                    'name' => 'required|string|max:255',
                    'slug' => 'required|string|max:255|unique:monsters,slug', // Ensure slug is unique
                    'description' => 'nullable|string',
                    'armor_class' => 'nullable|integer|min:0',
                    'ac' => 'nullable|integer|min:0', // Allow both ac and armor_class
                    'armor_type' => 'nullable|string|max:255',
                    'hit_points' => 'nullable|integer|min:0',
                    'max_health' => 'nullable|integer|min:0', // Allow both hit_points and max_health
                    'attacks' => 'nullable|string',
                    'movement' => 'nullable|string|max:255',
                    'strength' => 'nullable|integer',
                    'dexterity' => 'nullable|integer',
                    'constitution' => 'nullable|integer',
                    'intelligence' => 'nullable|integer',
                    'wisdom' => 'nullable|integer',
                    'charisma' => 'nullable|integer',
                    'alignment' => 'nullable|string|max:50',
                    'level' => 'nullable|integer|min:0',
                    'traits' => 'nullable|array',
                    'traits.*.name' => 'required_with:traits|string|max:255', // If traits exist, name is required
                    'traits.*.description' => 'required_with:traits|string', // If traits exist, description is required
                ]);

                if ($validator->fails()) {
                    $monsterNameForError = isset($monsterData['name']) && $monsterData['name'] !== null ? $monsterData['name'] : 'N/A';
                    $errors[] = "Monster at index {$index} (Name: {$monsterNameForError}): " . implode(', ', $validator->errors()->all());
                    $errorCount++;
                    continue;
                }

                $validatedData = $validator->validated();

                Monster::create([
                    'name' => $validatedData['name'],
                    'slug' => $validatedData['slug'],
                    'description' => isset($validatedData['description']) && $validatedData['description'] !== null ? $validatedData['description'] : null,
                    'ac' => isset($validatedData['armor_class']) && $validatedData['armor_class'] !== null ? $validatedData['armor_class'] : (isset($validatedData['ac']) && $validatedData['ac'] !== null ? $validatedData['ac'] : 10),
                    'armor_type' => isset($validatedData['armor_type']) && $validatedData['armor_type'] !== null ? $validatedData['armor_type'] : null,
                    'max_health' => isset($validatedData['hit_points']) && $validatedData['hit_points'] !== null ? $validatedData['hit_points'] : (isset($validatedData['max_health']) && $validatedData['max_health'] !== null ? $validatedData['max_health'] : 10),
                    'attacks' => isset($validatedData['attacks']) && $validatedData['attacks'] !== null ? $validatedData['attacks'] : null,
                    'movement' => isset($validatedData['movement']) && $validatedData['movement'] !== null ? $validatedData['movement'] : null,
                    'strength' => isset($validatedData['strength']) && $validatedData['strength'] !== null ? $validatedData['strength'] : 10,
                    'dexterity' => isset($validatedData['dexterity']) && $validatedData['dexterity'] !== null ? $validatedData['dexterity'] : 10,
                    'constitution' => isset($validatedData['constitution']) && $validatedData['constitution'] !== null ? $validatedData['constitution'] : 10,
                    'intelligence' => isset($validatedData['intelligence']) && $validatedData['intelligence'] !== null ? $validatedData['intelligence'] : 10,
                    'wisdom' => isset($validatedData['wisdom']) && $validatedData['wisdom'] !== null ? $validatedData['wisdom'] : 10,
                    'charisma' => isset($validatedData['charisma']) && $validatedData['charisma'] !== null ? $validatedData['charisma'] : 10,
                    'alignment' => isset($validatedData['alignment']) && $validatedData['alignment'] !== null ? $validatedData['alignment'] : null,
                    'level' => isset($validatedData['level']) && $validatedData['level'] !== null ? $validatedData['level'] : null,
                    'traits' => isset($validatedData['traits']) && $validatedData['traits'] !== null ? $validatedData['traits'] : null,
                ]);
                $createdCount++;
            }
            Log::info("Loop finished. Error count: {$errorCount}, Created count: {$createdCount}"); // Debug

            if ($errorCount > 0) {
                DB::rollBack();
                Log::info('Rolled back due to errors.', ['errors' => $errors]); // Debug
                $errorMessage = "Import failed. {$errorCount} monster(s) had errors: <br>" . implode("<br>", $errors);
                session()->flash('error', $errorMessage);
            } else {
                DB::commit();
                Log::info('Committed successfully.'); // Debug
                session()->flash('message', "Successfully imported {$createdCount} monsters.");
                // Dispatch an event to refresh the monsters table in Filament
                $this->dispatch('refreshMonstersTable');
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Exception during bulk monster import: ' . $e->getMessage() . ' Stack trace: ' . $e->getTraceAsString()); // Debug
            session()->flash('error', 'An unexpected error occurred during import. Please check the logs. Message: ' . $e->getMessage());
        } finally {
            Log::info('In finally block.'); // Debug
            // Reset file input after processing, regardless of outcome
            $this->file = null;
            // Emit an event that the parent component (Filament modal) can listen to, e.g., to close the modal
            // Or to refresh data if needed.
            $this->dispatch('bulk-import-finished');
        }
    }

    public function render()
    {
        return view('livewire.bulk-import-monsters');
    }
}
