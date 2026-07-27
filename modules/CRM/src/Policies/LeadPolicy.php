<?php

namespace Modules\CRM\Policies;

use App\Models\User;
use Modules\CRM\Models\Lead;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_leads');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('view_leads');
    }

    public function create(User $user): bool
    {
        return $user->can('create_leads');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can('edit_leads');
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->can('delete_leads');
    }
}
