@extends('errors.layout')
@section('badge-bg', '#eef2ff')
@section('badge-fg', '#6366f1')
@section('code', '503')
@section('title', __('Sedang Pemeliharaan'))
@section('message', __('Kasiro sedang dalam pemeliharaan singkat. Silakan kembali beberapa saat lagi.'))
@section('icon')
    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a4 4 0 01-5 5L4 17l3 3 5.7-5.7a4 4 0 005-5l-2.3 2.3-2.8-.7-.7-2.8 2.5-2.1z"/></svg>
@endsection
