<?php

namespace Modules\Dashboard\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Modules\CRM\Models\Activity;

class RecentActivities extends BaseWidget
{
    protected static ?string $heading = 'Recent Activities';

    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->can('view_dashboard');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Activity::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'call' => 'info',
                        'email' => 'primary',
                        'meeting' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('description')
                    ->limit(60),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('lead.title')
                    ->label('Lead')
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
