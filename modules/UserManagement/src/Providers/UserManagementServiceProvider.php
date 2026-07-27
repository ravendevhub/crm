<?php

namespace Modules\UserManagement\Providers;

use Illuminate\Support\ServiceProvider;

class UserManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        }

        // Register Policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\User::class, \Modules\UserManagement\Policies\UserPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\Spatie\Permission\Models\Role::class, \Modules\UserManagement\Policies\RolePolicy::class);
    }
}
