<?php

namespace Modules\Dashboard\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\CRM\Models\Customer;
use Modules\CRM\Models\Lead;
use Modules\CRM\Models\FollowUpTask;

class CRMStatsOverview extends BaseWidget
{
    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->can('view_dashboard');
    }

    protected function getStats(): array
    {
        // Because of the BelongsToTenant global scope, these queries are automatically scoped by tenant!
        $totalCustomers = Customer::count();
        $totalLeads = Lead::count();
        $newLeadsThisMonth = Lead::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $wonLeadsCount = Lead::where('status', 'won')->count();
        $lostLeadsCount = Lead::where('status', 'lost')->count();

        $pendingTasksCount = FollowUpTask::whereIn('status', ['pending', 'in_progress'])->count();
        $overdueTasksCount = FollowUpTask::whereIn('status', ['pending', 'in_progress'])
            ->where('due_date', '<', now())
            ->count();

        $totalLeadsValue = Lead::whereNotIn('status', ['won', 'lost'])->sum('estimated_value');

        return [
            Stat::make('Total Customers', $totalCustomers)
                ->description('Active customer organizations')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),

            Stat::make('Total Leads', $totalLeads)
                ->description('Total leads generated')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),

            Stat::make('New Leads This Month', $newLeadsThisMonth)
                ->description('Created in ' . now()->format('F'))
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning'),

            Stat::make('Pipeline Value', '$' . number_format($totalLeadsValue, 2))
                ->description('Active opportunities')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('Leads Won', $wonLeadsCount)
                ->description('Closed won status')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Leads Lost', $lostLeadsCount)
                ->description('Closed lost status')
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger'),

            Stat::make('Pending Tasks', $pendingTasksCount)
                ->description('Assigned follow-up tasks')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Overdue Tasks', $overdueTasksCount)
                ->description('Tasks past their due date')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($overdueTasksCount > 0 ? 'danger' : 'gray'),
        ];
    }
}
