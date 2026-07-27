<?php

namespace Modules\CRM\Policies;

use App\Models\User;
use Modules\CRM\Models\Quotation;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotationPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_quotations');
    }

    public function view(User $user, Quotation $quotation): bool
    {
        return $user->can('view_quotations');
    }

    public function create(User $user): bool
    {
        return $user->can('create_quotations');
    }

    public function update(User $user, Quotation $quotation): bool
    {
        return $user->can('edit_quotations');
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        return $user->can('edit_quotations');
    }
}
