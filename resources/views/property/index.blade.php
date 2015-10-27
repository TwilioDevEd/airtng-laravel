@extends('layouts.master')

@section('title')
    Properties
@endsection

@section('hero')
  <div class="hero-text">
    <h1>Lodging fit for a captain</h1>
    <p>The Next Generation of vacation rentals.</p>
  </div>
@endsection

@section('content')
<div class="container">
  <div class="row">
    @foreach ($properties as $property)
      <div class="col-md-4">
        <a href="/property/{{ $property->id }}" class="property">

          <img src="{{ $property->image_url }}" />
          <h2>{{ $property->description }}</h2>
        </a>
      </div>
    @endforeach
  </div>
</div>
@endsection
