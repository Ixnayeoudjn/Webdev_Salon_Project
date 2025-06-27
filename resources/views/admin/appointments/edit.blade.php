@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Edit Appointment</h2>
    <form action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Customer</label>
            <select name="customer_id" class="form-select" required>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ $appointment->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Staff</label>
            <select name="staff_id" class="form-select" required>
                @foreach($staffs as $staff)
                    <option value="{{ $staff->id }}" {{ $appointment->staff_id == $staff->id ? 'selected' : '' }}>{{ $staff->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Service</label>
            <select name="service_id" class="form-select" required>
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ $appointment->service_id == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Date & Time</label>
            <input type="text" id="appointment_time" name="start_time" class="form-control" value="{{ $appointment->start_time ? $appointment->start_time->format('Y-m-d H:i') : '' }}" required>
        </div>
        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control">{{ $appointment->notes }}</textarea>
        </div>
        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-select">
                @foreach(['Pending', 'Started', 'In Progress', 'Ended'] as $status)
                    <option value="{{ $status }}" {{ $appointment->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Appointment</button>
        <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
<script>
$(function() {
    $('#appointment_time').daterangepicker({
        singleDatePicker: true,
        timePicker: true,
        timePicker24Hour: true,
        locale: { format: 'YYYY-MM-DD HH:mm' },
        isInvalidDate: function(date) {
            const hour = date.hour();
            return hour < 8 || hour >= 18 || hour === 12;
        }
    });
});
</script>
@endsection
