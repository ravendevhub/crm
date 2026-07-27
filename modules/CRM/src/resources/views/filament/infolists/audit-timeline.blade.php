@php
    /**
     * audit-timeline.blade.php
     * Shared Filament Infolist ViewEntry component.
     * Expects the parent record to have an auditLogs() morphMany relationship.
     */
    $record = $getRecord();
    $logs = $record
        ? $record->auditLogs()->with('user')->latest('created_at')->limit(50)->get()
        : collect();

    $actionMeta = [
        'customer_created' => ['label' => 'Customer Created', 'dot' => 'bg-emerald-500', 'ring' => 'ring-emerald-100 dark:ring-emerald-900', 'text' => 'text-emerald-700 dark:text-emerald-300', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        'customer_updated' => ['label' => 'Customer Updated', 'dot' => 'bg-amber-400',   'ring' => 'ring-amber-100 dark:ring-amber-900',   'text' => 'text-amber-700 dark:text-amber-300',   'icon' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487zm0 0L19.5 7.125'],
        'lead_created' => ['label' => 'Lead Created', 'dot' => 'bg-emerald-500', 'ring' => 'ring-emerald-100 dark:ring-emerald-900', 'text' => 'text-emerald-700 dark:text-emerald-300', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        'lead_status_changed' => ['label' => 'Lead Status Changed', 'dot' => 'bg-blue-500',    'ring' => 'ring-blue-100 dark:ring-blue-900',    'text' => 'text-blue-700 dark:text-blue-300',    'icon' => 'M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5'],
        'follow_up_task_completed' => ['label' => 'Task Completed', 'dot' => 'bg-green-500',   'ring' => 'ring-green-100 dark:ring-green-900',   'text' => 'text-green-700 dark:text-green-300',   'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
        'quotation_created' => ['label' => 'Quotation Created', 'dot' => 'bg-emerald-500', 'ring' => 'ring-emerald-100 dark:ring-emerald-900', 'text' => 'text-emerald-700 dark:text-emerald-300', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        'quotation_status_changed' => ['label' => 'Quotation Status Changed', 'dot' => 'bg-blue-500',    'ring' => 'ring-blue-100 dark:ring-blue-900',    'text' => 'text-blue-700 dark:text-blue-300',    'icon' => 'M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5'],
        'created'        => ['label' => 'Created',        'dot' => 'bg-emerald-500', 'ring' => 'ring-emerald-100 dark:ring-emerald-900', 'text' => 'text-emerald-700 dark:text-emerald-300', 'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
        'updated'        => ['label' => 'Updated',        'dot' => 'bg-amber-400',   'ring' => 'ring-amber-100 dark:ring-amber-900',   'text' => 'text-amber-700 dark:text-amber-300',   'icon' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487zm0 0L19.5 7.125'],
        'status_changed' => ['label' => 'Status Changed', 'dot' => 'bg-blue-500',    'ring' => 'ring-blue-100 dark:ring-blue-900',    'text' => 'text-blue-700 dark:text-blue-300',    'icon' => 'M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5'],
        'completed'      => ['label' => 'Completed',      'dot' => 'bg-green-500',   'ring' => 'ring-green-100 dark:ring-green-900',   'text' => 'text-green-700 dark:text-green-300',   'icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
        'deleted'        => ['label' => 'Deleted',        'dot' => 'bg-red-500',     'ring' => 'ring-red-100 dark:ring-red-900',     'text' => 'text-red-700 dark:text-red-300',     'icon' => 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0'],
        'default'        => ['label' => 'Activity',       'dot' => 'bg-gray-400',    'ring' => 'ring-gray-100 dark:ring-gray-800',    'text' => 'text-gray-600 dark:text-gray-400',    'icon' => 'm11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z'],
    ];
@endphp

<div class="space-y-1">
    @if($logs->isEmpty())
        <div class="flex flex-col items-center justify-center py-10 text-gray-400 dark:text-gray-500">
            <svg class="w-10 h-10 mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <p class="text-sm font-medium">No activity recorded yet.</p>
        </div>
    @else
        <ol class="relative border-s border-gray-200 dark:border-gray-700 ms-3">
            @foreach($logs as $log)
                @php
                    $meta = $actionMeta[$log->action] ?? $actionMeta['default'];
                @endphp

                <li class="mb-5 ms-5 group">
                    {{-- Timeline dot --}}
                    <span class="absolute flex items-center justify-center w-7 h-7 rounded-full -start-3.5 ring-4 {{ $meta['ring'] }} ring-white dark:ring-gray-900 {{ $meta['dot'] }}">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['icon'] }}"/>
                        </svg>
                    </span>

                    {{-- Content card --}}
                    <div class="ms-1 p-3 bg-white border border-gray-100 rounded-lg shadow-xs dark:bg-gray-800/50 dark:border-gray-700 transition-shadow group-hover:shadow-sm">
                        {{-- Header row: action badge + time --}}
                        <div class="flex items-center justify-between mb-1.5 gap-2 flex-wrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider {{ $meta['text'] }} bg-current/10">
                                {{ $meta['label'] }}
                            </span>
                            <time class="text-[11px] text-gray-400 dark:text-gray-500 whitespace-nowrap" title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
                                {{ $log->created_at->diffForHumans() }}
                            </time>
                        </div>

                        {{-- Description --}}
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-snug">
                            {{ $log->description }}
                        </p>

                        {{-- Metadata diff (if present) --}}
                        @if($log->metadata && count($log->metadata) > 0)
                            <div class="mt-2 space-y-1">
                                @foreach($log->metadata as $field => $value)
                                    @if(is_array($value) && isset($value['old'], $value['new']))
                                        <div class="flex items-center gap-1.5 text-[11px] text-gray-500 dark:text-gray-400">
                                            <span class="font-medium capitalize">{{ str_replace('_', ' ', $field) }}:</span>
                                            <span class="line-through opacity-60">{{ $value['old'] ?? 'null' }}</span>
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $value['new'] ?? 'null' }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        {{-- Actor --}}
                        @if($log->user)
                            <div class="mt-2 flex items-center gap-1.5 pt-2 border-t border-gray-50 dark:border-gray-700/50">
                                <div class="w-4 h-4 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-[9px] font-bold text-primary-700 dark:text-primary-300 flex-shrink-0">
                                    {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                </div>
                                <span class="text-[11px] text-gray-500 dark:text-gray-400">{{ $log->user->name }}</span>
                            </div>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    @endif
</div>
