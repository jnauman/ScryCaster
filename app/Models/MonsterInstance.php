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
}
