<?php

namespace Modules\CRM\Filament\Resources\QuotationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\CRM\Filament\Resources\QuotationResource;

class EditQuotation extends EditRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
