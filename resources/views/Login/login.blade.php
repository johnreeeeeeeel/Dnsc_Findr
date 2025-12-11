@extends('Layouts.app')

@section('title', 'Login')

@section('content')
<div id="login-app" data-component="LoginPage">
    
</div>

<!-- CSRF token for Axios -->
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection
