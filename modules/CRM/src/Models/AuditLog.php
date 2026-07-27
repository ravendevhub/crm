<?php

namespace Modules\CRM\Models;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class AuditLog extends Model
{
    public $timestamps = false; // Only has created_at

    protected $fillable = [
        'company_id',
        'user_id',
        'subject_type',
        'subject_id',
        'action',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // ─── Scopes ──────────────────────────────────────────────

    /** Scope to the current tenant company. */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    /** Scope to a specific subject model (e.g. a Customer). */
    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query
            ->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->getKey());
    }

    // ─── Helpers ─────────────────────────────────────────────

    /** Icon and color mapping for each action type. */
    public function getActionMeta(): array
    {
        return match ($this->action) {
            'customer_created',
            'lead_created',
            'quotation_created' => ['icon' => 'check-circle',      'color' => 'emerald'],
            'customer_updated'  => ['icon' => 'pencil-square',     'color' => 'amber'],
            'lead_status_changed',
            'quotation_status_changed' => ['icon' => 'arrows-right-left', 'color' => 'blue'],
            'follow_up_task_completed' => ['icon' => 'check-badge', 'color' => 'green'],
            'created'        => ['icon' => 'check-circle',      'color' => 'emerald'],
            'updated'        => ['icon' => 'pencil-square',      'color' => 'amber'],
            'status_changed' => ['icon' => 'arrows-right-left',  'color' => 'blue'],
            'completed'      => ['icon' => 'check-badge',        'color' => 'green'],
            'deleted'        => ['icon' => 'trash',              'color' => 'red'],
            default          => ['icon' => 'information-circle', 'color' => 'gray'],
        };
    }
}
