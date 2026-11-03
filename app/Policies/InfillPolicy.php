<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Infill;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class InfillPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Infill');
    }

    public function view(AuthUser $authUser, Infill $infill): bool
    {
        return $authUser->can('View:Infill');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Infill');
    }

    public function update(AuthUser $authUser, Infill $infill): bool
    {
        return $authUser->can('Update:Infill');
    }

    public function delete(AuthUser $authUser, Infill $infill): bool
    {
        return $authUser->can('Delete:Infill');
    }

    public function restore(AuthUser $authUser, Infill $infill): bool
    {
        return $authUser->can('Restore:Infill');
    }

    public function forceDelete(AuthUser $authUser, Infill $infill): bool
    {
        return $authUser->can('ForceDelete:Infill');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Infill');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Infill');
    }

    public function replicate(AuthUser $authUser, Infill $infill): bool
    {
        return $authUser->can('Replicate:Infill');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Infill');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Infill');
    }

    public function import(AuthUser $authUser): bool
    {
        return $authUser->can('Import:Infill');
    }
}
