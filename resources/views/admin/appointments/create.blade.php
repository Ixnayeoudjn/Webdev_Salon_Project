@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Create Appointment</h2>
    <form action="{{ route('admin.appointments.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Customer</label>
            <select name="customer_id" class="form-select">
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Service</label>
                <select name="service_id" class="form-select">
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" @if(!$service->is_available) disabled @endif>
                            {{ $service->name }} - ₱{{ $service->price }}@if(!$service->is_available) (unavailable)@endif
                        </option>
                    @endforeach
                </select>
        </div>
        <div class="mb-3">
            <label>Assign Staff</label>
            <select name="staff_id" class="form-select">
                @foreach($staffs as $staff)
                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label>Date & Time</label>
            <input type="text" id="appointment_time" name="start_time" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Create</button>
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