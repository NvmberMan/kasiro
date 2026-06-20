@props(['config' => []])

@php
    $vars = \App\Support\ThemeConfig::cssVariables($config);
@endphp

<style>
    :root {
        @foreach ($vars as $property => $value)
            {{ $property }}: {{ $value }};
        @endforeach
    }
</style>
