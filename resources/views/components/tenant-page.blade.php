@props([])
@php
    $tenant = app(\App\Support\TenantContext::class)->get();
    $layout = $tenant?->layout() ?? 'modern';
@endphp

@include("tenant.layouts.{$layout}", [
    'tenant' => $tenant,
    'slot'   => new \Illuminate\Support\HtmlString($slot->toHtml()),
])
