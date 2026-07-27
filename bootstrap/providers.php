<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\Filament\AdminPanelProvider::class,
    Modules\CRM\Providers\CRMServiceProvider::class,
    Modules\UserManagement\Providers\UserManagementServiceProvider::class,
    Modules\Dashboard\Providers\DashboardServiceProvider::class,
    App\Providers\SuperAdminGateProvider::class,
];
