@extends('layouts.master')

@section('title')
    New Vacation Property
@endsection

@section('nav_bg')
  <!-- Nav Bar -->
  <nav class="navbar navbar-space">
  </nav>
@endsection

@section('content')
<div class="container">
  <h1>New Vacation Property</h1>
  @include('property._propertyForm')

  <a href="{{ route('property-index') }}">Back to properties</a></li>
</div>
@endsection
