@extends('errors.layout')
@section('badge-bg', '#fee2e2')
@section('badge-fg', '#dc2626')
@section('code', '403')
@section('title', __('Akses Ditolak'))
@section('message', __('Kamu tidak memiliki izin untuk membuka halaman ini. Hubungi pemilik toko jika ini keliru.'))
@section('icon')
    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
@endsection
