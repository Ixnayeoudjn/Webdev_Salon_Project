@extends('layouts.app')
@section('content')
<div class="container">
    <h2>All Appointments</h2>
    <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary mb-3">Create Appointment</a>
    <table class="table">
        <thead>
            <tr>
            <th>Customer</th>
            <th>Service</th>
            <th>Staff</th>
            <th>Date & Time</th>
            <th>Notes</th>
            <th>Status</th>
            <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $appointment)
                <tr>
                    <td>{{ $appointment->customer ? $appointment->customer->name : 'Unknown' }}</td>
                    <td>{{ $appointment->service ? $appointment->service->name : 'Unknown' }}</td>
                    <td>{{ $appointment->staff ? $appointment->staff->name : 'Unassigned' }}</td>
                    <td>{{ $appointment->start_time ? $appointment->start_time->format('Y-m-d H:i') : '' }}</td>
                    <td>{{ $appointment->notes }}</td>
                    <td>{{ $appointment->status }}</td>
                    <td>
                        @if($appointment->status !== 'Cancelled')
                            <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('admin.appointments.destroy', $appointment->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        @else
                            <form action="{{ route('admin.appointments.destroy', $appointment->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection