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
        'slug',
        'description',
        'ac', // armor_class from JSON
        'armor_type',
        'max_health', // hit_points from JSON
        'attacks',
        'movement',
        'strength',
        'dexterity',
        'constitution',
        'intelligence',
        'wisdom',
        'charisma',
        'alignment',
        'level',
        'traits', // Will be stored as JSON
        'data', // Keeping existing data field for other custom data if any
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'traits' => 'array', // Automatically cast traits to/from JSON
        'data' => 'array',   // Existing cast
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(MonsterInstance::class);
    }
}
