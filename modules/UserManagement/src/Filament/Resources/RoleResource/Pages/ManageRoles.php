<?php

namespace Modules\UserManagement\Filament\Resources\RoleResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\UserManagement\Filament\Resources\RoleResource;

class ManageRoles extends ManageRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
