@extends('layouts.app')
@section('content')
<div class="container">
  <h2>Confirm Your Appointment</h2>
  <div class="card mb-3">
    <div class="card-body">
      <p><strong>Service:</strong> {{ $summary['service_name'] }}</p>
      <p><strong>Price:</strong> ₱{{ $summary['service_price'] }}</p>
      <p><strong>Duration:</strong> {{ $summary['service_duration'] }} minutes</p>
      <p><strong>Start Time:</strong> {{ $summary['start_time'] }}</p>
      <p><strong>End Time:</strong> {{ $summary['end_time'] }}</p>
      <p><strong>Notes:</strong> {{ $summary['notes'] ?? '-' }}</p>
    </div>
  </div>

  <form method="POST" action="{{ route('customer.appointments.confirm') }}">
    @csrf

    {{-- 1) service_id --}}
    <input type="hidden" name="service_id" value="{{ $summary['service_id'] }}">

    {{-- 2) start_time --}}
    <input type="hidden" name="start_time" value="{{ $summary['start_time'] }}">

    {{-- 3) end_time  ← this is required by confirm() --}}
    <input type="hidden" name="end_time" value="{{ $summary['end_time'] }}">

    {{-- optional notes --}}
    <input type="hidden" name="notes" value="{{ $summary['notes'] ?? '' }}">

    <button type="submit" class="btn btn-success">
      Confirm Booking
    </button>
    <a href="{{ route('customer.appointments.create') }}" class="btn btn-secondary">
      Cancel
    </a>
  </form>
</div>
@endsection
