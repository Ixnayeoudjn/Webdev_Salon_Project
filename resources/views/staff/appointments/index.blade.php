@extends('layouts.app')
@section('content')
<div class="container">
    <h2>My Schedule</h2>
    <ul>
        @foreach($appointments as $appointment)
            <li>
                {{ $appointment->service->name }} with {{ $appointment->customer->name }}
                - {{ $appointment->start_time->format('Y-m-d H:i') }}
                - {{ $appointment->status }}
            </li>
        @endforeach
    </ul>
</div>
@endsection