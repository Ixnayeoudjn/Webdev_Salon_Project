@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Book Appointment</h2>
    <form action="{{ route('customer.appointments.store') }}" method="POST">
        @csrf
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
            <label>Date & Time</label>
            <input type="text" id="appointment_time" name="start_time" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Notes</label>
            <textarea name="notes" class="form-control"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Book</button>
    </form>
</div>

<script>
$(function() {
    $('#appointment_time').daterangepicker({
    singleDatePicker: true,
    timePicker: true,
    timePicker24Hour: false,
    locale: { format: 'YYYY-MM-DD hh:mm A' },
    isInvalidDate: function(date) {
    const hour = date.hour();
    return hour < 8 || hour >= 18 || hour === 12;
    }
    });
});
</script>
@endsection