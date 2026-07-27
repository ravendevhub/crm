<?php

namespace Modules\CRM\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\FollowUpTask;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\Quotation;
use Modules\CRM\Observers\CrmObserver;

class CRMServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Load migrations from the module database folder
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        }

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'crm');

        // ─── Model Observers (Activity Logging) ───
        Customer::observe(CrmObserver::class);
        Lead::observe(CrmObserver::class);
        FollowUpTask::observe(CrmObserver::class);
        Quotation::observe(CrmObserver::class);

        // ─── Policies ───
        Gate::policy(\Modules\CRM\Models\Customer::class,     \Modules\CRM\Policies\CustomerPolicy::class);
        Gate::policy(\Modules\CRM\Models\Lead::class,         \Modules\CRM\Policies\LeadPolicy::class);
        Gate::policy(\Modules\CRM\Models\PipelineStage::class, \Modules\CRM\Policies\PipelineStagePolicy::class);
        Gate::policy(\Modules\CRM\Models\Quotation::class,    \Modules\CRM\Policies\QuotationPolicy::class);
        Gate::policy(\Modules\CRM\Models\FollowUpTask::class, \Modules\CRM\Policies\FollowUpTaskPolicy::class);
        Gate::policy(\Modules\CRM\Models\Activity::class,     \Modules\CRM\Policies\ActivityPolicy::class);
    }
}
