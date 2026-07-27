<?php

namespace Modules\Dashboard\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Modules\CRM\Models\FollowUpTask;

class TodaysFollowUpTasks extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = "Today's & Overdue Follow-up Tasks";

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->can('view_dashboard');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                FollowUpTask::query()
                    ->whereIn('status', ['pending', 'in_progress'])
                    ->whereDate('due_date', '<=', now()->toDateString())
                    ->orderBy('due_date', 'asc')
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->weight('semibold'),

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
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_progress' => 'info',
                        'pending' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('due_date')
                    ->dateTime()
                    ->color(fn (FollowUpTask $record): string => $record->isOverdue() ? 'danger' : 'gray')
                    ->weight(fn (FollowUpTask $record): string => $record->isOverdue() ? 'bold' : 'normal')
                    ->icon(fn (FollowUpTask $record): ?string => $record->isOverdue() ? 'heroicon-m-exclamation-triangle' : null)
                    ->iconColor('danger'),
            ])
            ->actions([
                Tables\Actions\Action::make('complete')
                    ->button()
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (FollowUpTask $record) => $record->update(['status' => 'completed'])),
            ]);
    }
}
