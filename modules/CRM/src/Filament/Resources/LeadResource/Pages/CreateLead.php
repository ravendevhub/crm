<?php

namespace Modules\CRM\Filament\Resources\LeadResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\CRM\Filament\Resources\LeadResource;

class CreateLead extends CreateRecord
{
    protected static string $resource = LeadResource::class;
}
