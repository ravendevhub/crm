<?php

namespace Modules\CRM\Filament\Resources\LeadResource\Pages;

use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Modules\CRM\Filament\Resources\LeadResource;

class ViewLead extends ViewRecord
{
    protected static string $resource = LeadResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)
                    ->schema([
                        // ─── Left 2 cols: Lead Details ───────────────────
                        Group::make([
                            Section::make('Lead Information')
                                ->schema([
                                    TextEntry::make('title')
                                        ->weight(FontWeight::Bold)
                                        ->columnSpanFull(),
                                    TextEntry::make('contact_name')
                                        ->label('Contact Name')
                                        ->placeholder('N/A'),
                                    TextEntry::make('email')
                                        ->icon('heroicon-m-envelope')
                                        ->copyable()
                                        ->placeholder('N/A'),
                                    TextEntry::make('phone')
                                        ->icon('heroicon-m-phone')
                                        ->placeholder('N/A'),
                                    TextEntry::make('source')
                                        ->badge()
                                        ->color('gray')
                                        ->placeholder('Unknown'),
                                    TextEntry::make('estimated_value')
                                        ->label('Estimated Value')
                                        ->money('USD')
                                        ->placeholder('N/A'),
                                    TextEntry::make('expected_close_date')
                                        ->label('Expected Close')
                                        ->date()
                                        ->placeholder('N/A'),
                                ])->columns(2),

                            Section::make('Status & Pipeline')
                                ->schema([
                                    TextEntry::make('status')
                                        ->badge()
                                        ->color(fn ($state) => match ($state) {
                                            'won'           => 'success',
                                            'lost'          => 'danger',
                                            'proposal_sent' => 'info',
                                            'qualified'     => 'warning',
                                            default         => 'gray',
                                        })
                                        ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),
                                    TextEntry::make('pipelineStage.name')
                                        ->label('Pipeline Stage')
                                        ->badge()
                                        ->color('primary')
                                        ->placeholder('No Stage'),
                                    TextEntry::make('assignedUser.name')
                                        ->label('Assigned To')
                                        ->placeholder('Unassigned'),
                                    TextEntry::make('creator.name')
                                        ->label('Created By')
                                        ->placeholder('N/A'),
                                    TextEntry::make('created_at')
                                        ->label('Created')
                                        ->dateTime(),
                                    TextEntry::make('notes')
                                        ->label('Notes')
                                        ->placeholder('No notes')
                                        ->columnSpanFull(),
                                ])->columns(2),
                        ])->columnSpan(2),

                        // ─── Right 1 col: Audit Timeline ─────────────────
                        Group::make([
                            Section::make('Activity Timeline')
                                ->schema([
                                    ViewEntry::make('audit_timeline')
                                        ->view('crm::filament.infolists.audit-timeline')
                                        ->label(''),
                                ]),
                        ])->columnSpan(1),
                    ]),
            ]);
    }
}
