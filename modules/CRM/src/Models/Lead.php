<?php

namespace Modules\CRM\Models;

use App\Models\User;
use App\Traits\BelongsToTenant;
use App\Traits\TrackCreator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Lead extends Model
{
    use BelongsToTenant, TrackCreator, SoftDeletes;

    protected $fillable = [
        'company_id',
        'customer_id',
        'pipeline_stage_id',
        'title',
        'contact_name',
        'phone',
        'email',
        'source',
        'estimated_value',
        'expected_close_date',
        'notes',
        'status',
        'assigned_user_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'expected_close_date' => 'date',
        'estimated_value'     => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function pipelineStage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class);
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

    public function followUpTasks(): HasMany
    {
        return $this->hasMany(FollowUpTask::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /** Polymorphic audit trail via audit_logs table. */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'subject')->latest('created_at');
    }
}
