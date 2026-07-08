@extends('errors.layout')
@section('badge-bg', '#fef3c7')
@section('badge-fg', '#d97706')
@section('code', '401')
@section('title', __('Belum Masuk'))
@section('message', __('Kamu harus masuk terlebih dahulu untuk mengakses halaman ini.'))
@section('icon')
    <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/></svg>
@endsection
