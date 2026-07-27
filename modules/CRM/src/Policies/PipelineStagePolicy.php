<?php

namespace Modules\CRM\Policies;

use App\Models\User;
use Modules\CRM\Models\PipelineStage;
use Illuminate\Auth\Access\HandlesAuthorization;

class PipelineStagePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('manage_pipeline');
    }

    public function view(User $user, PipelineStage $stage): bool
    {
        return $user->can('manage_pipeline');
    }

    public function create(User $user): bool
    {
        return $user->can('manage_pipeline');
    }

    public function update(User $user, PipelineStage $stage): bool
    {
        return $user->can('manage_pipeline');
    }

    public function delete(User $user, PipelineStage $stage): bool
    {
        return $user->can('manage_pipeline');
    }
}
