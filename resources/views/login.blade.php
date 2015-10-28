@extends('layouts.master')

@section('title')
    Log in
@endsection

@section('nav_bg')
  <!-- Nav Bar -->
  <nav class="navbar navbar-space">
  </nav>
@endsection

@section('content')
<div class="container">
  <div class="login-form form">
    <h1>Log in</h1>
    {!! Form::open(['url' => route('login-action')]) !!}
      <div class="form-group">
        {!! Form::label('email') !!}
        {!! Form::text('email', '', ['class' => 'form-control', 'placeholder' => 'me@mydomain.com']) !!}
      </div>
      <div class="form-group">
        {!! Form::label('password') !!}
        {!! Form::password('password', ['class' => 'form-control']) !!}
      </div>
      <button type="submit" class="btn btn-primary">Log In</button>
    {!! Form::close() !!}
  </div>
</div>
@endsection
