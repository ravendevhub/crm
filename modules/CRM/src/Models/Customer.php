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

class Customer extends Model
{
    use BelongsToTenant, TrackCreator, SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'company_name',
        'email',
        'phone',
        'website',
        'address',
        'customer_type',
        'status',
        'source',
        'assigned_user_id',
        'created_by',
        'updated_by',
    ];

    // ─── Relationships ────────────────────────────────────────

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

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function followUpTasks(): HasMany
    {
        return $this->hasMany(FollowUpTask::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(CustomerHistory::class);
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
