<?php

namespace Modules\CRM\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Modules\CRM\Models\FollowUpTask;
use Modules\CRM\Filament\Resources\FollowUpTaskResource\Pages;

class FollowUpTaskResource extends Resource
{
    protected static ?string $model = FollowUpTask::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $modelLabel = 'Follow Up Task';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                            ])
                            ->required()
                            ->default('medium'),

                        Forms\Components\Select::make('related_type')
                            ->label('Related Type')
                            ->options([
                                \Modules\CRM\Models\Customer::class => 'Customer',
                                \Modules\CRM\Models\Lead::class => 'Lead',
                            ])
                            ->reactive()
                            ->afterStateUpdated(fn (callable $set) => $set('related_id', null))
                            ->required(),

                        Forms\Components\Select::make('related_id')
                            ->label('Related Record')
                            ->options(function (callable $get) {
                                $type = $get('related_type');
                                if (! $type) {
                                    return [];
                                }
                                return $type::query()->pluck(
                                    $type === \Modules\CRM\Models\Customer::class ? 'name' : 'title',
                                    'id'
                                );
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DateTimePicker::make('due_date')
                            ->required(),

                        Forms\Components\DateTimePicker::make('reminder_at')
                            ->label('Reminder Date'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('pending'),

                        Forms\Components\Select::make('assigned_user_id')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\Textarea::make('notes')
                            ->columnSpanFull()
                            ->maxLength(65535),
                    ])->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('related')
                    ->label('Related To')
                    ->state(function (FollowUpTask $record) {
                        if (!$record->related) {
                            return 'None';
                        }
                        return ($record->related instanceof \Modules\CRM\Models\Customer)
                            ? $record->related->name
                            : $record->related->title;
                    })
                    ->description(function (FollowUpTask $record) {
                        if (!$record->related_type) {
                            return null;
                        }
                        return class_basename($record->related_type);
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'cancelled' => 'danger',
                        'in_progress' => 'info',
                        'pending' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime()
                    ->sortable()
                    ->color(fn (FollowUpTask $record): string => $record->isOverdue() ? 'danger' : 'gray')
                    ->weight(fn (FollowUpTask $record): string => $record->isOverdue() ? 'bold' : 'normal')
                    ->icon(fn (FollowUpTask $record): ?string => $record->isOverdue() ? 'heroicon-m-exclamation-triangle' : null)
                    ->iconColor('danger'),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->sortable()
                    ->label('Assigned To'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                    ]),

                Tables\Filters\SelectFilter::make('assigned_user_id')
                    ->label('Assigned To')
                    ->relationship('assignedUser', 'name'),

                Tables\Filters\Filter::make('is_overdue')
                    ->label('Overdue Only')
                    ->query(fn (\Illuminate\Database\Eloquent\Builder $query) => $query
                        ->where('due_date', '<', now())
                        ->whereIn('status', ['pending', 'in_progress'])
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
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
            'index' => Pages\ManageFollowUpTasks::route('/'),
        ];
    }
}
