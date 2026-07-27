<?php

namespace Modules\CRM\Filament\Pages;

use Filament\Pages\Page;
use Modules\CRM\Models\PipelineStage;
use Modules\CRM\Models\Lead;
use Filament\Notifications\Notification;

class PipelineBoard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $navigationGroup = 'CRM';

    protected static ?string $title = 'Pipeline Board';

    protected static string $view = 'crm::filament.pages.pipeline-board';

    public function getStages()
    {
        return PipelineStage::orderBy('order')->get();
    }

    public function getLeadsByStage($stageId)
    {
        return Lead::where('pipeline_stage_id', $stageId)
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function moveLead($leadId, $stageId)
    {
        $lead = Lead::find($leadId);
        
        if ($lead) {
            $lead->update([
                'pipeline_stage_id' => $stageId,
            ]);
            
            $newStage = PipelineStage::find($stageId)?->name ?? 'None';
            
            Notification::make()
                ->title("Lead moved to stage '{$newStage}'")
                ->success()
                ->send();
        }
    }
}
