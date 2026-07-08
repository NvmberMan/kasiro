@extends('errors.layout')
@section('badge-bg', '#fee2e2')
@section('badge-fg', '#dc2626')
@section('code', '500')
@section('title', __('Terjadi Kesalahan'))
@section('message', __('Ada masalah di sisi server kami. Tim kami sedang menanganinya — coba lagi nanti.'))
@section('icon')
    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><path d="M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
@endsection
