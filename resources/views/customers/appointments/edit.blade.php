@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Edit Appointment</h2>
    <form action="{{ route('customer.appointments.update', $appointment->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label>Service</label>
            <select name="service_id" class="form-select">
                @foreach($services as $service)
                    <option value="{{ $service->id }}" {{ $appointment->service_id == $service->id ? 'selected' : '' }}>{{ $service->name }} - ₱{{ $service->price }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Date & Time</label>
            <input type="text" id="appointment_time" name="start_time" value="{{ $appointment->start_time->format('Y-m-d H:i') }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control">{{ $appointment->notes }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Update</button>
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