<?php

namespace Modules\CRM\Policies;

use App\Models\User;
use Modules\CRM\Models\FollowUpTask;
use Illuminate\Auth\Access\HandlesAuthorization;

class FollowUpTaskPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_leads');
    }

    public function view(User $user, FollowUpTask $task): bool
    {
        return $user->can('view_leads');
    }

    public function create(User $user): bool
    {
        return $user->can('create_leads');
    }

    public function update(User $user, FollowUpTask $task): bool
    {
        return $user->can('edit_leads');
    }

    public function delete(User $user, FollowUpTask $task): bool
    {
        return $user->can('delete_leads');
    }
}
