@extends('layouts.app')
@section('content')
<div class="container">
    <h2>My Appointments</h2>
    <a href="{{ route('customer.appointments.create') }}" class="btn btn-primary mb-3" style="text-decoration-line: underline">Create Appointment</a>
    <h4>Upcoming</h4>
    <ul>
        @foreach($upcoming as $appointment)
            <li>
            {{ $appointment->service->name }} - {{ $appointment->start_time->format('Y-m-d H:i') }} - {{ $appointment->status }}
            @if($appointment->status !== 'Cancelled' && $appointment->start_time >= now())
            <form action="{{ route('customer.appointments.destroy', $appointment->id) }}" method="POST" style="display:inline-block; margin-left: 10px;">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this appointment?')">Cancel</button>
            </form>
            @endif
            </li>
        @endforeach
    </ul>
    <h4>Past</h4>
    <ul>
        @foreach($past as $appointment)
            <li>{{ $appointment->service->name }} - {{ $appointment->start_time->format('Y-m-d H:i') }} - {{ $appointment->status }}</li>
        @endforeach
    </ul>
</div>
@endsection