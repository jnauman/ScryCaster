<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonsterInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'monster_id',
        'encounter_id',
        'current_health',
        'max_health',
        'initiative_roll',
        'order',
    ];

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    /**
     * Generates CSS classes for displaying this monster instance in an encounter list.
     *
     * @param int $currentEncounterTurn The order number of the combatant whose turn it currently is.
     * @return string A string of CSS classes.
     */
    public function getListItemCssClasses(int $currentEncounterTurn): string
    {
        $baseType = 'monster'; // Or 'monster-instance' for more specific styling
        $isCurrentTurn = (isset($this->order) && $this->order == $currentEncounterTurn);

        if ($isCurrentTurn) {
            return "{$baseType}-current-turn";
        } else {
            return "{$baseType}-not-turn";
        }
    }
}
