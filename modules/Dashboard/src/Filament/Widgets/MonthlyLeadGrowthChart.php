<?php

namespace Modules\Dashboard\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\CRM\Models\Lead;

class MonthlyLeadGrowthChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Lead Growth';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->can('view_dashboard');
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Past 6 months
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $labels[] = $month->format('M Y');
            $data[] = Lead::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Leads',
                    'data' => $data,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => '#93c5fd',
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
