<?php

namespace Modules\Dashboard\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Modules\CRM\Models\PipelineStage;
use Modules\CRM\Models\Lead;

class SalesPipelineValueChart extends ChartWidget
{
    protected static ?string $heading = 'Sales Pipeline Value by Stage';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->can('view_dashboard');
    }

    protected function getData(): array
    {
        $stages = PipelineStage::orderBy('order')->get();
        
        $data = [];
        $labels = [];
        $colors = [];

        $defaultColors = [
            '#fbbf24', // Amber
            '#3b82f6', // Blue
            '#06b6d4', // Cyan
            '#10b981', // Emerald
            '#ef4444', // Red
            '#a855f7', // Purple
        ];

        foreach ($stages as $index => $stage) {
            $data[] = Lead::where('pipeline_stage_id', $stage->id)->sum('estimated_value');
            $labels[] = $stage->name;
            $colors[] = $stage->color ?? $defaultColors[$index % count($defaultColors)];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pipeline Value ($)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
