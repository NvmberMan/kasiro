@extends('errors.layout')
@section('badge-bg', '#fef3c7')
@section('badge-fg', '#d97706')
@section('code', '429')
@section('title', __('Terlalu Banyak Permintaan'))
@section('message', __('Kamu melakukan terlalu banyak permintaan. Tunggu sebentar lalu coba lagi.'))
@section('icon')
    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
@endsection
