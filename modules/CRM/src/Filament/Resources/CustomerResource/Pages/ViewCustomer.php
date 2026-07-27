<?php

namespace Modules\CRM\Filament\Resources\CustomerResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Modules\CRM\Filament\Resources\CustomerResource;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('add_note')
                ->label('Add Note')
                ->icon('heroicon-m-chat-bubble-bottom-center-text')
                ->color('info')
                ->form([
                    Textarea::make('note')
                        ->label('Note Content')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (array $data) {
                    $this->record->histories()->create([
                        'company_id' => $this->record->company_id,
                        'event_type' => 'note',
                        'description' => $data['note'],
                        'created_by' => auth()->id(),
                    ]);
                    
                    Notification::make()
                        ->title('Note added successfully!')
                        ->success()
                        ->send();
                }),
        ];
    }
}
