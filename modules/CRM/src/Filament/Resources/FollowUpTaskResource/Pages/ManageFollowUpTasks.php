<?php

namespace Modules\CRM\Filament\Resources\FollowUpTaskResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\CRM\Filament\Resources\FollowUpTaskResource;

class ManageFollowUpTasks extends ManageRecords
{
    protected static string $resource = FollowUpTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
