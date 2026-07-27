<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Company extends Model
{
    protected $fillable = ['name', 'slug'];

    protected static function booted()
    {
        static::created(function (Company $company) {
            $defaultStages = [
                ['name' => 'New', 'order' => 1, 'color' => '#6b7280'],
                ['name' => 'Contacted', 'order' => 2, 'color' => '#3b82f6'],
                ['name' => 'Qualified', 'order' => 3, 'color' => '#f59e0b'],
                ['name' => 'Proposal Sent', 'order' => 4, 'color' => '#06b6d4'],
                ['name' => 'Won', 'order' => 5, 'color' => '#10b981'],
                ['name' => 'Lost', 'order' => 6, 'color' => '#ef4444'],
            ];

            foreach ($defaultStages as $stage) {
                $company->pipelineStages()->create($stage);
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function customers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\CRM\Models\Customer::class);
    }

    public function leads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\CRM\Models\Lead::class);
    }

    public function pipelineStages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\CRM\Models\PipelineStage::class);
    }

    public function quotations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\CRM\Models\Quotation::class);
    }

    public function followUpTasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\CRM\Models\FollowUpTask::class);
    }

    public function activities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\Modules\CRM\Models\Activity::class);
    }
}
