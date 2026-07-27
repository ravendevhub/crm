<?php

namespace Modules\CRM\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use BelongsToTenant;

    protected $table = 'quotation_items';

    protected $fillable = [
        'company_id',
        'quotation_id',
        'description',
        'quantity',
        'unit_price',
        'discount',
        'tax_rate',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::saved(function (QuotationItem $item) {
            if ($item->quotation) {
                $item->quotation->recalculateTotal();
            }
        });

        static::deleted(function (QuotationItem $item) {
            if ($item->quotation) {
                $item->quotation->recalculateTotal();
            }
        });
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }
}
