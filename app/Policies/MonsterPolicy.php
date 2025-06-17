<?php

namespace App\Policies;

use App\Models\Monster;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonsterPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // List filtered by resource query (shows own and global)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Monster $monster): bool
    {
        return $monster->user_id === null || $user->id === $monster->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true; // user_id will be set by form default or can be nulled
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Monster $monster): bool
    {
        // Global monsters (user_id is null) are not updatable by GMs here.
        // Admins would need separate logic if they can update global monsters.
        if ($monster->user_id === null) {
            return false; // GMs cannot update global monsters
        }
        return $user->id === $monster->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Monster $monster): bool
    {
        // Global monsters (user_id is null) are not deletable by GMs here.
        if ($monster->user_id === null) {
            return false; // GMs cannot delete global monsters
        }
        return $user->id === $monster->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Monster $monster): bool
    {
        // Assuming same logic as update/delete for soft-deleted owned monsters
        if ($monster->user_id === null) {
            return false;
        }
        return $user->id === $monster->user_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Monster $monster): bool
    {
        // Assuming same logic as update/delete for force-deleting owned monsters
        if ($monster->user_id === null) {
            return false;
        }
        return $user->id === $monster->user_id;
    }
}
