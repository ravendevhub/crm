<?php

namespace Modules\UserManagement\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $currentUser): bool
    {
        return $currentUser->can('view_users');
    }

    public function view(User $currentUser, User $targetUser): bool
    {
        return $currentUser->can('view_users');
    }

    public function create(User $currentUser): bool
    {
        return $currentUser->can('create_users');
    }

    public function update(User $currentUser, User $targetUser): bool
    {
        return $currentUser->can('edit_users');
    }

    public function delete(User $currentUser, User $targetUser): bool
    {
        // Don't allow users to delete themselves
        if ($currentUser->id === $targetUser->id) {
            return false;
        }
        return $currentUser->can('delete_users');
    }
}
