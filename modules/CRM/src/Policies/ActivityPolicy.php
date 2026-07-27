<?php

namespace Modules\CRM\Policies;

use App\Models\User;
use Modules\CRM\Models\Activity;
use Illuminate\Auth\Access\HandlesAuthorization;

class ActivityPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_leads');
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->can('view_leads');
    }

    public function create(User $user): bool
    {
        return $user->can('create_leads');
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->can('edit_leads');
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $user->can('delete_leads');
    }
}
