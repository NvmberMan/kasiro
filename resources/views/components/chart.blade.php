@props([
    'type'       => 'line',      // line | bar
    'labels'     => [],
    'values'     => [],
    'height'     => 160,
    'horizontal' => false,       // horizontal bar
    'fill'       => true,        // area fill under line
    'format'     => 'currency',  // currency | number
    'empty'      => 'Belum ada data.',
])

@php
    $labels = collect($labels)->values()->all();
    $values = collect($values)->map(fn ($v) => (float) $v)->values()->all();
    $hasData = count($values) > 0;
@endphp

@if (! $hasData)
    <div class="flex items-center justify-center text-sm brand-muted" style="height: {{ $height }}px">{{ $empty }}</div>
@else
    <div style="height: {{ $height }}px">
        <canvas
            data-chart="{{ $type }}"
            data-horizontal="{{ $horizontal ? '1' : '0' }}"
            data-fill="{{ $fill ? '1' : '0' }}"
            data-format="{{ $format }}"
            data-labels='@json($labels)'
            data-values='@json($values)'></canvas>
    </div>
@endif
