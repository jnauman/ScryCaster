<?php

namespace App\Policies;

use App\Models\MonsterInstance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MonsterInstancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Access controlled by access to the parent Encounter/Campaign
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MonsterInstance $monsterInstance): bool
    {
        // Access controlled by access to the parent Encounter/Campaign
        // For more granular control, one might check:
        // return $user->id === $monsterInstance->encounter->campaign->gm_user_id;
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Access controlled by access to the parent Encounter/Campaign
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MonsterInstance $monsterInstance): bool
    {
        // Access controlled by access to the parent Encounter/Campaign
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MonsterInstance $monsterInstance): bool
    {
        // Access controlled by access to the parent Encounter/Campaign
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MonsterInstance $monsterInstance): bool
    {
        // Access controlled by access to the parent Encounter/Campaign
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MonsterInstance $monsterInstance): bool
    {
        // Access controlled by access to the parent Encounter/Campaign
        return true;
    }
}
