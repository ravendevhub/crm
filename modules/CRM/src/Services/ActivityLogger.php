<?php

namespace Modules\CRM\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\CRM\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    /**
     * Log an auditable event for a subject model.
     *
     * @param  Model       $subject     The model being acted upon (Customer, Lead, etc.)
     * @param  string      $action      e.g. 'customer_created', 'lead_status_changed'
     * @param  string      $description Human-readable description of what happened
     * @param  array       $metadata    Optional structured context (field diffs, old/new values)
     * @param  int|null    $userId      Override user ID (defaults to current auth user)
     */
    public static function log(
        Model   $subject,
        string  $action,
        string  $description,
        array   $metadata = [],
        ?int    $userId = null
    ): void {
        // Resolve company_id from the subject model
        $companyId = $subject->company_id ?? null;

        if (! $companyId) {
            return; // Cannot log without a company scope
        }

        // Resolve user – fallback to null for system-triggered events
        $resolvedUserId = $userId ?? Auth::id();

        AuditLog::create([
            'company_id'   => $companyId,
            'user_id'      => $resolvedUserId,
            'subject_type' => get_class($subject),
            'subject_id'   => $subject->getKey(),
            'action'       => $action,
            'description'  => $description,
            'metadata'     => ! empty($metadata) ? $metadata : null,
        ]);
    }

    /**
     * Build a human-readable field diff array from model dirty state.
     * Returns ['field' => ['old' => x, 'new' => y], ...]
     */
    public static function buildDiff(Model $model, array $fields): array
    {
        $diff = [];
        foreach ($fields as $field) {
            if ($model->isDirty($field)) {
                $diff[$field] = [
                    'old' => $model->getOriginal($field),
                    'new' => $model->getAttribute($field),
                ];
            }
        }
        return $diff;
    }
}
