<?php

namespace Modules\CRM\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Models\Lead;
use Modules\CRM\Filament\Resources\LeadResource\Pages;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationGroup = 'CRM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Lead Profile')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('contact_name')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->tel()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Financials & Status')
                    ->schema([
                        Forms\Components\TextInput::make('estimated_value')
                            ->numeric()
                            ->prefix('$')
                            ->default(0.00)
                            ->required(),
                        Forms\Components\DatePicker::make('expected_close_date'),
                        Forms\Components\TextInput::make('source')
                            ->placeholder('e.g. Website, Referral, Cold Call')
                            ->maxLength(255),
                        Forms\Components\Select::make('status')
                            ->options([
                                'new' => 'New',
                                'contacted' => 'Contacted',
                                'qualified' => 'Qualified',
                                'proposal_sent' => 'Proposal Sent',
                                'won' => 'Won',
                                'lost' => 'Lost',
                            ])
                            ->required()
                            ->default('new'),
                    ])->columns(2),

                Forms\Components\Section::make('Assignment & Pipeline')
                    ->schema([
                        Forms\Components\Select::make('customer_id')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Will link to a customer if converted.'),
                        Forms\Components\Select::make('pipeline_stage_id')
                            ->relationship('pipelineStage', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('assigned_user_id')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload(),
                    ])->columns(3),

                Forms\Components\Section::make('Internal Notes')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contact_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'new' => 'gray',
                        'contacted' => 'info',
                        'qualified' => 'primary',
                        'proposal_sent' => 'warning',
                        'won' => 'success',
                        'lost' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'proposal_sent' => 'Proposal Sent',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('estimated_value')
                    ->money('USD')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expected_close_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->sortable()
                    ->label('Assigned To'),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Converted Customer')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'contacted' => 'Contacted',
                        'qualified' => 'Qualified',
                        'proposal_sent' => 'Proposal Sent',
                        'won' => 'Won',
                        'lost' => 'Lost',
                    ]),
                Tables\Filters\SelectFilter::make('assigned_user_id')
                    ->relationship('assignedUser', 'name')
                    ->label('Assigned User'),
                Tables\Filters\SelectFilter::make('source')
                    ->options(fn () => Lead::whereNotNull('source')->where('source', '!=', '')->distinct()->pluck('source', 'source')->toArray()),
                Tables\Filters\Filter::make('expected_close_date')
                    ->form([
                        Forms\Components\DatePicker::make('close_from')->label('Close date from'),
                        Forms\Components\DatePicker::make('close_to')->label('Close date to'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['close_from'], fn ($q) => $q->whereDate('expected_close_date', '>=', $data['close_from']))
                            ->when($data['close_to'], fn ($q) => $q->whereDate('expected_close_date', '<=', $data['close_to']));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('convert_to_customer')
                    ->label('Convert')
                    ->icon('heroicon-m-user-plus')
                    ->color('success')
                    ->visible(fn (Lead $record) => $record->customer_id === null)
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Customer Name')
                            ->default(fn (Lead $record) => $record->contact_name ?: $record->title)
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('company_name')
                            ->label('Company Name')
                            ->default(fn (Lead $record) => $record->contact_name ? $record->title : '')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->default(fn (Lead $record) => $record->email)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->default(fn (Lead $record) => $record->phone)
                            ->maxLength(255),
                        Forms\Components\Select::make('customer_type')
                            ->label('Customer Type')
                            ->options([
                                'individual' => 'Individual',
                                'corporate' => 'Corporate',
                            ])
                            ->required()
                            ->default(fn (Lead $record) => $record->contact_name ? 'individual' : 'corporate'),
                    ])
                    ->action(function (Lead $record, array $data) {
                        // Create new Customer
                        $customer = \Modules\CRM\Models\Customer::create([
                            'company_id' => $record->company_id,
                            'name' => $data['name'],
                            'company_name' => $data['company_name'],
                            'email' => $data['email'],
                            'phone' => $data['phone'],
                            'customer_type' => $data['customer_type'],
                            'status' => 'active',
                            'source' => $record->source,
                            'assigned_user_id' => $record->assigned_user_id,
                            'created_by' => auth()->id(),
                        ]);

                        // Link lead to the customer and update status to Won
                        $record->update([
                            'customer_id' => $customer->id,
                            'status' => 'won',
                        ]);

                        // Trigger log
                        $customer->histories()->create([
                            'company_id' => $record->company_id,
                            'event_type' => 'creation',
                            'description' => "Customer created via conversion from Lead: '{$record->title}'.",
                            'created_by' => auth()->id(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Lead successfully converted to Customer!')
                            ->success()
                            ->send();
                    }),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'view'   => Pages\ViewLead::route('/{record}'),
            'edit'   => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
