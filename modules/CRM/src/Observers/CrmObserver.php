<?php

namespace Modules\CRM\Observers;

use Illuminate\Database\Eloquent\Model;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\FollowUpTask;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Quotation;
use Modules\CRM\Services\ActivityLogger;

class CrmObserver
{
    public function created(Model $model): void
    {
        match (true) {
            $model instanceof Customer => $this->customerCreated($model),
            $model instanceof Lead => $this->leadCreated($model),
            $model instanceof Quotation => $this->quotationCreated($model),
            default => null,
        };
    }

    public function updated(Model $model): void
    {
        match (true) {
            $model instanceof Customer => $this->customerUpdated($model),
            $model instanceof Lead => $this->leadUpdated($model),
            $model instanceof FollowUpTask => $this->followUpTaskUpdated($model),
            $model instanceof Quotation => $this->quotationUpdated($model),
            default => null,
        };
    }

    private function customerCreated(Customer $customer): void
    {
        ActivityLogger::log(
            $customer,
            'customer_created',
            "Customer \"{$customer->name}\" was created.",
            ['name' => $customer->name, 'type' => $customer->customer_type]
        );
    }

    private function customerUpdated(Customer $customer): void
    {
        $trackedFields = ['name', 'email', 'phone', 'status', 'assigned_user_id', 'customer_type', 'source'];
        $diff = ActivityLogger::buildDiff($customer, $trackedFields);

        if (empty($diff)) {
            return;
        }

        $changedFields = implode(', ', array_map(fn ($f) => str_replace('_', ' ', $f), array_keys($diff)));
        ActivityLogger::log(
            $customer,
            'customer_updated',
            "Customer \"{$customer->name}\" was updated. Changed: {$changedFields}.",
            $diff
        );
    }

    private function leadCreated(Lead $lead): void
    {
        ActivityLogger::log(
            $lead,
            'lead_created',
            "Lead \"{$lead->title}\" was created.",
            [
                'title'           => $lead->title,
                'status'          => $lead->status,
                'estimated_value' => $lead->estimated_value,
                'source'          => $lead->source,
            ]
        );
    }

    private function leadUpdated(Lead $lead): void
    {
        if ($lead->isDirty('status')) {
            $old = $lead->getOriginal('status');
            $new = $lead->status;
            ActivityLogger::log(
                $lead,
                'lead_status_changed',
                "Lead status changed from \"{$old}\" to \"{$new}\".",
                ['old_status' => $old, 'new_status' => $new]
            );
        }

        $trackedFields = ['title', 'assigned_user_id', 'pipeline_stage_id', 'estimated_value', 'expected_close_date'];
        $diff = ActivityLogger::buildDiff($lead, $trackedFields);

        if (! empty($diff)) {
            $changedFields = implode(', ', array_map(fn ($f) => str_replace('_', ' ', $f), array_keys($diff)));
            ActivityLogger::log(
                $lead,
                'updated',
                "Lead \"{$lead->title}\" was updated. Changed: {$changedFields}.",
                $diff
            );
        }
    }

    private function followUpTaskUpdated(FollowUpTask $task): void
    {
        if ($task->isDirty('status') && $task->status === 'completed') {
            $related = $task->related;

            ActivityLogger::log(
                $task,
                'follow_up_task_completed',
                "Follow-up task \"{$task->title}\" was marked as completed.",
                ['task_id' => $task->id, 'title' => $task->title]
            );

            if ($related && isset($related->company_id)) {
                ActivityLogger::log(
                    $related,
                    'follow_up_task_completed',
                    "Follow-up task \"{$task->title}\" was marked as completed.",
                    ['task_id' => $task->id, 'title' => $task->title]
                );
            }
        }
    }

    private function quotationCreated(Quotation $quotation): void
    {
        ActivityLogger::log(
            $quotation,
            'quotation_created',
            "Quotation \"{$quotation->quotation_number}\" was created with a total of \${$quotation->total_amount}.",
            [
                'quotation_number' => $quotation->quotation_number,
                'total_amount'     => $quotation->total_amount,
                'status'           => $quotation->status,
            ]
        );
    }

    private function quotationUpdated(Quotation $quotation): void
    {
        if ($quotation->isDirty('status')) {
            $old = $quotation->getOriginal('status');
            $new = $quotation->status;
            ActivityLogger::log(
                $quotation,
                'quotation_status_changed',
                "Quotation \"{$quotation->quotation_number}\" status changed from \"{$old}\" to \"{$new}\".",
                ['old_status' => $old, 'new_status' => $new]
            );
        }
    }
}
