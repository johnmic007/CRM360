@if (is_string($state))
    <div class="text-red-500 text-sm mt-1">
        {{ $state }}
    </div>
@elseif ($state instanceof \Closure)
    <div class="text-red-500 text-sm mt-1">
        {{ $state() }} {{-- Call the closure to render its output --}}
    </div>
@else
    <div class="text-red-500 text-sm mt-1">
        Invalid error data provided.
    </div>
@endif
