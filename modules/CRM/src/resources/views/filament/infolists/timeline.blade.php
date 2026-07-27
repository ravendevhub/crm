@php
    $record = $getRecord();
    $histories = $record ? $record->histories()->with('creator')->latest()->get() : collect();
@endphp

<div class="space-y-6">
    @if($histories->isEmpty())
        <div class="text-center py-6 text-gray-500 dark:text-gray-400 text-sm">
            No history or notes found for this customer.
        </div>
    @else
        <div class="relative border-s border-gray-200 dark:border-gray-700 ms-3 space-y-6">
            @foreach($histories as $history)
                @php
                    $isNote = $history->event_type === 'note';
                    $isCreation = $history->event_type === 'creation';
                    $iconBg = 'bg-gray-100 dark:bg-gray-800';
                    $iconColor = 'text-gray-600 dark:text-gray-400';
                    $iconSvg = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>'; // default plus-ish/creation

                    if ($isNote) {
                        $iconBg = 'bg-blue-50 dark:bg-blue-950';
                        $iconColor = 'text-blue-600 dark:text-blue-400';
                        $iconSvg = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>';
                    } elseif ($isCreation) {
                        $iconBg = 'bg-emerald-50 dark:bg-emerald-950';
                        $iconColor = 'text-emerald-600 dark:text-emerald-400';
                        $iconSvg = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    } else { // update or status change
                        $iconBg = 'bg-amber-50 dark:bg-amber-950';
                        $iconColor = 'text-amber-600 dark:text-amber-400';
                        $iconSvg = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89H18"/></svg>';
                    }
                @endphp

                <div class="mb-6 ms-6 relative">
                    <!-- Icon / Bullet point -->
                    <span class="absolute flex items-center justify-center w-8 h-8 rounded-full -translate-x-10 ring-8 ring-white dark:ring-gray-900 {{ $iconBg }} {{ $iconColor }}">
                        {!! $iconSvg !!}
                    </span>

                    <!-- Content Card -->
                    <div class="p-4 bg-white border border-gray-100 rounded-lg shadow-sm dark:bg-gray-850 dark:border-gray-700 hover:shadow-md transition-shadow duration-200">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                {{ ucfirst($history->event_type) }}
                            </span>
                            <time class="text-xs font-normal text-gray-400 dark:text-gray-500">
                                {{ $history->created_at->diffForHumans() }}
                            </time>
                        </div>
                        
                        <p class="text-sm font-normal text-gray-700 dark:text-gray-300 break-words whitespace-pre-line">
                            {{ $history->description }}
                        </p>

                        @if($history->creator)
                            <div class="mt-3 flex items-center gap-2 pt-2 border-t border-gray-50 dark:border-gray-700">
                                <div class="w-5 h-5 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-[10px] font-bold text-gray-600 dark:text-gray-300">
                                    {{ strtoupper(substr($history->creator->name, 0, 1)) }}
                                </div>
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">
                                    {{ $history->creator->name }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
