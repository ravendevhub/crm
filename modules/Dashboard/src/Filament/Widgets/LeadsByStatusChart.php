<?php

namespace Modules\Dashboard\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\CRM\Models\Lead;

class LeadsByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Leads by Status';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->can('view_dashboard');
    }

    protected function getData(): array
    {
        $statuses = [
            'new' => 'New',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'proposal_sent' => 'Proposal Sent',
            'won' => 'Won',
            'lost' => 'Lost',
        ];

        $data = [];
        $labels = [];
        $colors = [
            '#9ca3af', // Gray (New)
            '#3b82f6', // Blue (Contacted)
            '#f59e0b', // Amber (Qualified)
            '#06b6d4', // Cyan (Proposal Sent)
            '#10b981', // Green (Won)
            '#ef4444', // Red (Lost)
        ];

        foreach ($statuses as $key => $label) {
            $data[] = Lead::where('status', $key)->count();
            $labels[] = $label;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
