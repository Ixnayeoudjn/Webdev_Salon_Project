@extends('layouts.app')
@section('content')
<div class="container">
    <h2>Manage Services</h2>
        <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.appointments.calendar') }}" class="btn btn-primary mb-3" style="text-decoration-line: underline">Calendar</a>
        </div>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Price</th>
                <th>Duration</th>
                <th>Available</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($services as $service)
                <tr>
                    <td>{{ $service->name }}</td>
                    <td>₱{{ $service->price }}</td>
                    <td>{{ $service->duration }} min</td>
                    <td>
                        @if($service->is_available)
                            <span class="badge bg-success">Available</span>
                        @else
                            <span class="badge bg-danger">Unavailable</span>
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('admin.services.toggle', $service->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $service->is_available ? 'btn-danger' : 'btn-success' }}">
                                {{ $service->is_available ? 'Set to Unavailable' : 'Set to Available' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection