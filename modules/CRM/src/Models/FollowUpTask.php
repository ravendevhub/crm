<?php

namespace Modules\CRM\Models;

use App\Models\User;
use App\Traits\BelongsToTenant;
use App\Traits\TrackCreator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FollowUpTask extends Model
{
    use BelongsToTenant, TrackCreator, SoftDeletes;

    protected $table = 'follow_up_tasks';

    protected $fillable = [
        'company_id',
        'related_type',
        'related_id',
        'title',
        'notes',
        'due_date',
        'reminder_at',
        'priority',
        'status',
        'assigned_user_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date'   => 'datetime',
        'reminder_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────

    public function related(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Polymorphic audit trail via audit_logs table. */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'subject')->latest('created_at');
    }

    // ─── Helpers ─────────────────────────────────────────────

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && in_array($this->status, ['pending', 'in_progress']);
    }
}
