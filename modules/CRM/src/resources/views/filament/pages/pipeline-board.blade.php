<x-filament-panels::page>
    <div 
        x-data="{
            draggingLeadId: null,
            dragStart(e, id) {
                this.draggingLeadId = id;
                e.dataTransfer.setData('text/plain', id);
                e.dataTransfer.effectAllowed = 'move';
            },
            dragOver(e) {
                e.preventDefault();
            },
            drop(e, stageId) {
                e.preventDefault();
                const leadId = this.draggingLeadId || e.dataTransfer.getData('text/plain');
                if (leadId) {
                    $wire.moveLead(leadId, stageId);
                }
                this.draggingLeadId = null;
            }
        }"
        class="flex gap-4 overflow-x-auto pb-4"
    >
        @foreach($this->getStages() as $stage)
            @php
                $leads = $this->getLeadsByStage($stage->id);
                $stageValue = $leads->sum('estimated_value');
            @endphp
            <div 
                class="flex-shrink-0 w-80 bg-gray-50 dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 p-4 flex flex-col max-h-[800px]"
                @dragover.prevent="dragOver($event)"
                @drop="drop($event, {{ $stage->id }})"
            >
                <!-- Stage Header -->
                <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-200 dark:border-gray-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="w-3.5 h-3.5 rounded-full" style="background-color: {{ $stage->color ?? '#6b7280' }}"></span>
                            <h3 class="font-bold text-gray-800 dark:text-gray-200 text-sm uppercase tracking-wider">{{ $stage->name }}</h3>
                        </div>
                        <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                            {{ $leads->count() }} {{ Str::plural('lead', $leads->count()) }}
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                            ${{ number_format($stageValue, 2) }}
                        </span>
                    </div>
                </div>

                <!-- Stage Cards Container -->
                <div class="flex-grow overflow-y-auto space-y-3 min-h-[200px] pb-2">
                    @forelse($leads as $lead)
                        <div 
                            draggable="true"
                            @dragstart="dragStart($event, {{ $lead->id }})"
                            class="bg-white dark:bg-gray-800 rounded-lg p-3 shadow-sm hover:shadow-md border border-gray-200 dark:border-gray-700 cursor-grab active:cursor-grabbing transition duration-200 space-y-2 group relative"
                        >
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100 text-sm leading-snug group-hover:text-amber-600 dark:group-hover:text-amber-500 transition duration-150 pr-4">
                                    {{ $lead->title }}
                                </h4>
                            </div>

                            @if($lead->contact_name)
                                <div class="flex items-center gap-1.5 text-xs text-gray-600 dark:text-gray-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>{{ $lead->contact_name }}</span>
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-1 border-t border-gray-100 dark:border-gray-700/50 mt-1">
                                <span class="font-bold text-xs text-gray-900 dark:text-gray-200">
                                    ${{ number_format($lead->estimated_value, 2) }}
                                </span>
                                
                                @if($lead->expected_close_date)
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400 flex items-center gap-1 bg-gray-100 dark:bg-gray-700/50 px-1.5 py-0.5 rounded">
                                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        {{ \Carbon\Carbon::parse($lead->expected_close_date)->format('M d') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Click-to-move quick actions for mobile/accessibility -->
                            <div class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition duration-150">
                                <x-filament::dropdown placement="bottom-end">
                                    <x-slot name="trigger">
                                        <button class="p-0.5 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/></svg>
                                        </button>
                                    </x-slot>

                                    <x-filament::dropdown.list>
                                        @foreach($this->getStages() as $targetStage)
                                            @if($targetStage->id !== $stage->id)
                                                <x-filament::dropdown.list.item 
                                                    wire:click="moveLead({{ $lead->id }}, {{ $targetStage->id }})"
                                                    icon="heroicon-m-arrow-right"
                                                >
                                                    Move to {{ $targetStage->name }}
                                                </x-filament::dropdown.list.item>
                                            @endif
                                        @endforeach
                                    </x-filament::dropdown.list>
                                </x-filament::dropdown>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-8 px-4 border border-dashed border-gray-200 dark:border-gray-700 rounded-lg text-gray-400 dark:text-gray-500 text-xs">
                            <span>No leads in stage</span>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
