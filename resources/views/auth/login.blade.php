@extends('layouts.site')

@section('title', 'Fanous Dormitory Sign In')
@section('bare_page', 'true')
@section('hide_global_language', 'true')

@section('content')
    @include('auth.partials.login-card')
@endsection
