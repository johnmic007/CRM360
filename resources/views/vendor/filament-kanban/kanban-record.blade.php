@if($record->allocated_to === Auth::id())
<div
    id="{{ $record->getKey() }}"
    wire:click="recordClicked('{{ $record->getKey() }}', {{ @json_encode($record) }})"
    class="record bg-white dark:bg-gray-700 rounded-lg px-4 py-2 cursor-grab font-medium text-gray-600 dark:text-gray-200"
    @if($record->timestamps && now()->diffInSeconds($record->{$record::UPDATED_AT}) < 3)
        x-data
        x-init=" 
            $el.classList.add('animate-pulse-twice', 'bg-primary-100', 'dark:bg-primary-800')
            $el.classList.remove('bg-white', 'dark:bg-gray-700')
            setTimeout(() => {
                $el.classList.remove('bg-primary-100', 'dark:bg-primary-800')
                $el.classList.add('bg-white', 'dark:bg-gray-700')
            }, 3000)
        "
    @endif
>
    <div class="flex justify-center items-center p-4 rounded-lg shadow-lg bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-100">
        <!-- School Name Label -->
        <span class="text-sm font-medium opacity-80 mr-2">
            School Name:
        </span>
        <!-- School Name Display -->
        <span class="text-base font-semibold tracking-wide">
            {{ $record->school->name ?? 'No School Assigned' }}
        </span>
    </div>
</div>
@endif
