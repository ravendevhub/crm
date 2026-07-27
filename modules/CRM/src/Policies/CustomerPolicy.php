<?php

namespace Modules\CRM\Policies;

use App\Models\User;
use Modules\CRM\Models\Customer;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_customers');
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->can('view_customers');
    }

    public function create(User $user): bool
    {
        return $user->can('create_customers');
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->can('edit_customers');
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->can('delete_customers');
    }
}
