<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

/**
 * Represents a user in the application.
 *
 * This model is used for authentication and managing user-specific data,
 * including their characters and campaigns they manage as a Game Master.
 * It implements MustVerifyEmail for email verification and FilamentUser
 * for integration with the Filament admin panel.
 */
class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable; // Includes Notifiable trait for email notifications

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Ensures email_verified_at is a Carbon instance
            'password' => 'hashed',          // Automatically hashes passwords when set
        ];
    }

    /**
     * Generates the user's initials from their name.
     *
     * Example: "John Doe" becomes "JD".
     *
     * @return string The user's initials.
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ') // Split name into words
            ->map(fn (string $name) => Str::of($name)->substr(0, 1)) // Take the first letter of each word
            ->implode(''); // Join the letters
    }

	/**
	 * Determines if the user can access the Filament admin panel.
	 *
	 * Currently, access is granted if the user has verified their email address.
	 *
	 * @param \Filament\Panel $panel The Filament panel instance.
	 * @return bool True if the user can access the panel, false otherwise.
	 */
	public function canAccessPanel(Panel $panel): bool
	{
		return $this->hasVerifiedEmail(); // User must have a verified email
	}

	/**
	 * Defines the relationship for characters owned by this user.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function characters(): HasMany
	{
		return $this->hasMany(Character::class);
	}

	/**
	 * Defines the relationship for campaigns where this user is the Game Master (GM).
	 *
	 * The foreign key 'gm_user_id' on the 'campaigns' table links back to this user.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function campaignsGm(): HasMany
	{
		return $this->hasMany(Campaign::class, 'gm_user_id');
	}
}
