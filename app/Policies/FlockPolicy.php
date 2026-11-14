<?php

namespace App\Policies;

use App\Models\Flock;
use App\Models\User;

class FlockPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Flock $flock): bool
    {
        return $user->isOwner() || $user->id === $flock->worker_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Flock $flock): bool
    {
        return $user->isOwner() || $user->id === $flock->worker_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Flock $flock): bool
    {
        return $user->isOwner();
    }

    /**
     * Determine whether the user can manage daily records for the flock.
     */
    public function manageDailyRecords(User $user, Flock $flock): bool
    {
        return $user->isOwner() || $user->id === $flock->worker_id;
    }
}
