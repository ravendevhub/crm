<?php

namespace Modules\CRM\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PipelineStage extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_id',
        'name',
        'order',
        'color',
    ];

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }
}
