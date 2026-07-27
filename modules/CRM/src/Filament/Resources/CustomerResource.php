<?php

namespace Modules\CRM\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Models\Customer;
use Modules\CRM\Filament\Resources\CustomerResource\Pages;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    protected static ?string $navigationGroup = 'CRM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Profile')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('address')
                            ->maxLength(500)
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('MetaData & Assignment')
                    ->schema([
                        Forms\Components\Select::make('customer_type')
                            ->options([
                                'individual' => 'Individual',
                                'corporate' => 'Corporate',
                            ])
                            ->required()
                            ->default('individual'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'lead' => 'Lead',
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->required()
                            ->default('lead'),
                        Forms\Components\TextInput::make('source')
                            ->placeholder('e.g. Website, Referral, Cold Call')
                            ->maxLength(255),
                        Forms\Components\Select::make('assigned_user_id')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('company_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\TextColumn::make('customer_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'corporate' => 'info',
                        'individual' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        'lead' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->sortable()
                    ->label('Assigned To'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'lead' => 'Lead',
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                Tables\Filters\SelectFilter::make('customer_type')
                    ->options([
                        'individual' => 'Individual',
                        'corporate' => 'Corporate',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Grid::make(3)
                    ->schema([
                        \Filament\Infolists\Components\Group::make([
                            \Filament\Infolists\Components\Section::make('Profile Information')
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('name')
                                        ->weight(\Filament\Support\Enums\FontWeight::Bold),
                                    \Filament\Infolists\Components\TextEntry::make('company_name')
                                        ->placeholder('None'),
                                    \Filament\Infolists\Components\TextEntry::make('email')
                                        ->icon('heroicon-m-envelope')
                                        ->copyable(),
                                    \Filament\Infolists\Components\TextEntry::make('phone')
                                        ->icon('heroicon-m-phone'),
                                    \Filament\Infolists\Components\TextEntry::make('website')
                                        ->icon('heroicon-m-globe-alt')
                                        ->url(fn ($state) => $state, shouldOpenInNewTab: true),
                                    \Filament\Infolists\Components\TextEntry::make('address')
                                        ->placeholder('No address provided')
                                        ->columnSpanFull(),
                                ])->columns(2),

                            \Filament\Infolists\Components\Section::make('Metadata & Assignment')
                                ->schema([
                                    \Filament\Infolists\Components\TextEntry::make('customer_type')
                                        ->badge()
                                        ->color(fn ($state) => $state === 'corporate' ? 'info' : 'gray')
                                        ->formatStateUsing(fn ($state) => ucfirst($state)),
                                    \Filament\Infolists\Components\TextEntry::make('status')
                                        ->badge()
                                        ->color(fn ($state) => match ($state) {
                                            'active' => 'success',
                                            'inactive' => 'danger',
                                            'lead' => 'warning',
                                            default => 'gray',
                                        })
                                        ->formatStateUsing(fn ($state) => ucfirst($state)),
                                    \Filament\Infolists\Components\TextEntry::make('source')
                                        ->placeholder('Unknown'),
                                    \Filament\Infolists\Components\TextEntry::make('assignedUser.name')
                                        ->label('Assigned To')
                                        ->placeholder('Unassigned'),
                                    \Filament\Infolists\Components\TextEntry::make('creator.name')
                                        ->label('Created By'),
                                    \Filament\Infolists\Components\TextEntry::make('created_at')
                                        ->dateTime(),
                                ])->columns(2)
                        ])->columnSpan(2),

                        \Filament\Infolists\Components\Group::make([
                            \Filament\Infolists\Components\Section::make('Activity Timeline')
                                ->schema([
                                    \Filament\Infolists\Components\ViewEntry::make('audit_timeline')
                                        ->view('crm::filament.infolists.audit-timeline')
                                        ->label('')
                                ])
                        ])->columnSpan(1)
                    ])
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'view' => Pages\ViewCustomer::route('/{record}'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
