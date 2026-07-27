<?php

namespace Modules\UserManagement\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Auth\Access\HandlesAuthorization;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_roles');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('view_roles');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_roles');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('manage_roles');
    }

    public function delete(User $user, Role $role): bool
    {
        // Prevent deleting the Super Admin role
        if ($role->name === 'Super Admin') {
            return false;
        }
        return $user->can('manage_roles');
    }
}
