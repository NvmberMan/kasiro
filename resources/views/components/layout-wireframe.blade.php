@props(['type' => 'topbar'])

{{-- Skeleton wireframe preview of a structural layout (topbar | sidebar) --}}
@if ($type === 'topbar')
    <div class="aspect-[4/3] w-full rounded-md border border-gray-200 bg-gray-50 overflow-hidden flex flex-col">
        {{-- Top nav bar --}}
        <div class="h-3 bg-indigo-300 flex items-center gap-1 px-1.5">
            <span class="h-1.5 w-1.5 rounded-full bg-white/80"></span>
            <span class="ml-auto h-1 w-4 rounded-sm bg-white/50"></span>
            <span class="h-1 w-3 rounded-sm bg-white/50"></span>
        </div>
        {{-- Content cards grid --}}
        <div class="flex-1 grid grid-cols-3 gap-1 p-1.5">
            @for ($i = 0; $i < 6; $i++)
                <div class="rounded-sm bg-gray-200"></div>
            @endfor
        </div>
    </div>
@else
    <div class="aspect-[4/3] w-full rounded-md border border-gray-200 bg-gray-50 overflow-hidden flex">
        {{-- Left sidebar nav --}}
        <div class="w-1/4 bg-indigo-300 flex flex-col gap-1 p-1">
            <span class="h-1.5 w-1.5 rounded-full bg-white/80 mb-0.5"></span>
            @for ($i = 0; $i < 4; $i++)
                <span class="h-1 w-full rounded-sm bg-white/50"></span>
            @endfor
        </div>
        {{-- Content cards grid --}}
        <div class="flex-1 grid grid-cols-3 gap-1 p-1.5">
            @for ($i = 0; $i < 6; $i++)
                <div class="rounded-sm bg-gray-200"></div>
            @endfor
        </div>
    </div>
@endif
