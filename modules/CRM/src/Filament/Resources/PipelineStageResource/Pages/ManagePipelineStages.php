<?php

namespace Modules\CRM\Filament\Resources\PipelineStageResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\CRM\Filament\Resources\PipelineStageResource;

class ManagePipelineStages extends ManageRecords
{
    protected static string $resource = PipelineStageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
