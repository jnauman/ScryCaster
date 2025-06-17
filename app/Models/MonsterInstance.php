<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonsterInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'encounter_id',
        'monster_id',
        'current_health',
        'initiative_roll',
        'order',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function monster(): BelongsTo
    {
        return $this->belongsTo(Monster::class);
    }
}
