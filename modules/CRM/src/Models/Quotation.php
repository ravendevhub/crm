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

class Quotation extends Model
{
    use BelongsToTenant, TrackCreator, SoftDeletes;

    protected $fillable = [
        'company_id',
        'lead_id',
        'customer_id',
        'quotation_number',
        'total_amount',
        'status',
        'assigned_user_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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

    // ─── Business Logic ───────────────────────────────────────

    public function recalculateTotal(): void
    {
        $total = $this->items()->sum('total');
        $this->updateQuietly(['total_amount' => $total]);
    }
}
