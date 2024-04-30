<?php

namespace DTW\FilamentMultilanguage\Policies;

use DTW\FilamentMultilanguage\Models\Translation;

class TranslationPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before($user, string $ability): bool|null
    {
        if (in_array($user->email, config('filament-multilanguage.authorized_emails'))) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny($user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view($user, Translation $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create($user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update($user, Translation $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete($user, Translation $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore($user, Translation $model): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete($user, Translation $model): bool
    {
        return false;
    }
}
