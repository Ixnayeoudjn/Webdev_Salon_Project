@extends('layouts.app')
@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>
            @if($selectedDate)
                Appointments for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}
            @else
                All Appointments
            @endif
        </h2>
        <div>
            <a href="{{ route('admin.appointments.calendar') }}" class="btn btn-info" style="text-decoration-line: underline">Calendar View</a>
            <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary" style="text-decoration-line: underline">Create Appointment</a>
            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary mb-3" style="text-decoration-line: underline">Manage Services</a>
        </div>
    </div>

    @if($selectedDate)
        <div class="alert alert-info">
            <div class="d-flex justify-content-between align-items-center">
                <span>Showing appointments for {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}</span>
                <a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-outline-secondary">View All Appointments</a>
            </div>
        </div>
    @endif

    @if($appointments->count() > 0)
        <table class="table table-striped">
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
                        <td>
                            <span class="badge badge-{{ $appointment->status === 'Completed' ? 'success' : ($appointment->status === 'Cancelled' ? 'danger' : ($appointment->status === 'Confirmed' ? 'primary' : 'warning')) }}">
                                {{ $appointment->status }}
                            </span>
                        </td>
                        <td>
                            @if($appointment->status !== 'Cancelled')
                                <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                <form action="{{ route('admin.appointments.destroy', $appointment->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this appointment?')">Delete</button>
                                </form>
                            @else
                                <form action="{{ route('admin.appointments.destroy', $appointment->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this appointment?')">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="alert alert-info">
            @if($selectedDate)
                No appointments scheduled for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}.
            @else
                No appointments found.
            @endif
        </div>
    @endif
</div>
@endsection
