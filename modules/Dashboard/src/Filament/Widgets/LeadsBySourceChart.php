<?php

namespace Modules\Dashboard\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\CRM\Models\Lead;

class LeadsBySourceChart extends ChartWidget
{
    protected static ?string $heading = 'Leads by Source';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->can('view_dashboard');
    }

    protected function getData(): array
    {
        // Query distinct sources in the tenant scope
        $sourcesData = Lead::query()
            ->select('source', \DB::raw('count(*) as count'))
            ->groupBy('source')
            ->get();

        $data = [];
        $labels = [];
        $colors = [
            '#a855f7', // Purple
            '#ec4899', // Pink
            '#06b6d4', // Cyan
            '#3b82f6', // Blue
            '#10b981', // Emerald
            '#f59e0b', // Amber
        ];

        foreach ($sourcesData as $index => $row) {
            $sourceName = $row->source ? ucfirst($row->source) : 'Unknown';
            $labels[] = $sourceName;
            $data[] = $row->count;
        }

        // If empty, add a placeholder to make chart visual nice
        if (empty($data)) {
            $labels[] = 'No Leads';
            $data[] = 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Leads',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
