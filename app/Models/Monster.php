<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Monster extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'ac',
        'max_health',
        'strength',
        'dexterity',
        'constitution',
        'intelligence',
        'wisdom',
        'charisma',
        'data',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function instances(): HasMany
    {
        // Note: MonsterInstance model will be created in a future step.
        // If this causes an error during linting or automated checks,
        // it's expected for now.
        return $this->hasMany(MonsterInstance::class);
    }
}
