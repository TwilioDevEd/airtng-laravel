@extends('layouts.master')

@section('title')
    Reservations
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
    @if(count($hostReservations) <= 0)
      <h2> You are not hosting any reservations. </h2>
    @else
      <h2> Your current reservations as a Host. </h2>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Guest Name</th>
            <th>Property Name</th>
            <th>Phone Number / Status</th>
            <th>Message</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($hostReservations as $hostReservation)
            <tr>
              <th scope="row">{{ $hostReservation->id }}</th>
              <td>{{ $hostReservation->user->name }}</td>
              <td>{{ $hostReservation->property->description }}</td>
              @if(empty($hostReservation->twilio_number))
                <td>{{ $hostReservation->status }}</td>
              @else
                <td>{{ $hostReservation->twilio_number }}</td>
              @endif
              <td>{{ $hostReservation->message }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>
<hr>
<div class="container">
  <div class="row">
    @if(count($guestReservations) <= 0)
      <h2> You have not reserved a property yet. </h2>
    @else
      <h2> Your current reservations as a Guest. </h2>
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Host Name</th>
            <th>Property Name</th>
            <th>Phone Number / Status</th>
            <th>Message</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($guestReservations as $guestReservation)
            <tr>
              <th scope="row">{{ $guestReservation->id }}</th>
              <td>{{ $guestReservation->property->user->name }}</td>
              <td>{{ $guestReservation->property->description }}</td>
              @if(empty($guestReservation->twilio_number))
                <td>{{ $guestReservation->status }}</td>
              @else
                <td>{{ $guestReservation->twilio_number }}</td>
              @endif
              <td>{{ $guestReservation->message }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>
@endsection
