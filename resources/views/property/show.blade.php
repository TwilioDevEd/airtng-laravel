@extends('layouts.master')

@section('title')
    Home
@endsection

@section('body_class')
  "property-page"
@endsection

@section('content')
<div class="property-detail">
    <div class="overview">
        <img src="{{ $property->image_url }}" />
    </div>
    <div class="container">
        @include('_messages')
        <h1 >{{ $property->description }}</h1><span><a href="{{ route('property-edit', ['id' => $property->id]) }}">Edit</a></span>
        <hr>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Make a Reservation</h3>
            </div>
            <div class="panel-body">
                {!! Form::open(['url' => route('reservation-create', ['id' => $property->id])]) !!}
                    <div class="form-group">
                        {!! Form::label('message') !!}
                        {!! Form::text('message', '', ['class' => 'form-control', 'placeholder' => 'Hello! I am hoping to stay in your intergalactic suite...']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::text('property_id', $property->id, ['class' => 'hidden']) !!}
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">Reserve Now</button>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
