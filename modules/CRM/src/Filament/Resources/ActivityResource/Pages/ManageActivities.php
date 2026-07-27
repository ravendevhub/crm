<?php

namespace Modules\CRM\Filament\Resources\ActivityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\CRM\Filament\Resources\ActivityResource;

class ManageActivities extends ManageRecords
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
