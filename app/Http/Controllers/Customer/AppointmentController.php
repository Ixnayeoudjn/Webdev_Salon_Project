<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::where('customer_id', Auth::id())
            ->with('service', 'staff')
            ->latest()
            ->get();

        $now = Carbon::now();

        // UPCOMING
        $upcoming = $appointments->filter(function ($a) use ($now) {
            $start = $a->start_time instanceof Carbon ? $a->start_time : Carbon::parse($a->start_time);
            return $start >= $now;
        });

        foreach ($upcoming as $a) {
            $start = $a->start_time instanceof Carbon ? $a->start_time : Carbon::parse($a->start_time);
            $minutesUntil = Carbon::now()->diffInMinutes($start, false);
            $a->can_cancel = $a->status !== 'Cancelled' && $minutesUntil >= 60;
        }

        // PAST
        $past = $appointments->filter(function ($a) use ($now) {
            $start = $a->start_time instanceof Carbon ? $a->start_time : Carbon::parse($a->start_time);
            return $start < $now;
        });

        return view('customers.appointments.index', compact('upcoming', 'past'));
    }

    public function create()
    {
        $services = Service::all();
        return view('customers.appointments.create', compact('services'));
    }

    /**
     * Step 1: Validate and show confirmation page
     */
public function store(Request $request)
{
    $validated = $request->validate([
        'service_id' => 'required|exists:services,id',
        'start_time' => 'required|date_format:Y-m-d h:i A'
    ]);

    $start = Carbon::createFromFormat('Y-m-d h:i A', $validated['start_time']);
    $service = Service::findOrFail($validated['service_id']);
    $end = $start->copy()->addMinutes($service->duration);

    if ($start->lessThan(now())) {
        return back()->withErrors('You cannot book an appointment in the past.')->withInput();
    }

    if ($start->hour < 8 || $start->hour >= 18 || $start->hour === 12) {
        return back()->withErrors('Booking not allowed during lunch or off-hours.')->withInput();
    }

    $customerId = Auth::id();
    $conflict = Appointment::where('customer_id', $customerId)
        ->where('status', '!=', 'Cancelled')
        ->whereDate('start_time', $start->toDateString())
        ->where(function ($q) use ($start, $end) {
            $q->where('start_time', '<', $end)
              ->where('end_time', '>', $start);
        })->first();

    if ($conflict) {
        return back()->withErrors('This appointment overlaps with an existing booking.')->withInput();
    }

    // Return confirmation view
    $summary = [
        'service_id' => $service->id,
        'service_name' => $service->name,
        'service_price' => $service->price,
        'service_duration' => $service->duration,
        'start_time' => $start->format('Y-m-d h:i A'),
        'end_time' => $end->format('Y-m-d h:i A'),
        'notes' => $request->notes,
    ];

    return view('customers.appointments.confirm', compact('summary'));
}


    /**
     * Step 2: Actually save after confirmation
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'start_time' => 'required|date_format:Y-m-d h:i A',
            'end_time'   => 'required|date_format:Y-m-d h:i A',
        ]);

        $start = Carbon::createFromFormat('Y-m-d h:i A', $request->start_time);
        $end = Carbon::createFromFormat('Y-m-d h:i A', $request->end_time);
        $service = Service::findOrFail($request->service_id);

        // Double-check for conflicts before saving
        $customerId = Auth::id();
        $conflictingAppointment = Appointment::where('customer_id', $customerId)
        ->where('status', '!=', 'Cancelled')
        ->whereDate('start_time', $start->toDateString())
        ->where(function($query) use ($start, $end) {
        $query->where('start_time', '<', $end)
        ->where('end_time', '>', $start);
        })
        ->first();
        
        if ($conflictingAppointment) {
        return redirect()->route('customer.appointments.create')
        ->withErrors('This appointment overlaps with an existing booking on the same date.');
        }

        $appointment = new Appointment();
        $appointment->customer_id = $customerId;
        $appointment->service_id = $service->id;
        $appointment->start_time = $start;
        $appointment->end_time = $end;
        $appointment->notes = $request->notes;
        $appointment->status = 'Pending';
        $appointment->staff_id = null;
        $appointment->save();

        return redirect()->route('customer.appointments.index')->with('success', 'Appointment booked!');
    }

    public function edit($id)
    {
        $appointment = Appointment::where('customer_id', Auth::id())->findOrFail($id);

        if (Carbon::parse($appointment->start_time)->isPast()) {
            return redirect()->route('customer.appointments.index')->withErrors('You cannot edit past appointments.');
        }

        $services = Service::all();
        return view('customer.appointments.edit', compact('appointment', 'services'));
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::where('customer_id', Auth::id())->findOrFail($id);

        if (Carbon::parse($appointment->start_time)->isPast()) {
            return back()->withErrors('You cannot update past appointments.');
        }

        $request->validate([
            'service_id' => 'required|exists:services,id',
            'start_time' => 'required|date_format:Y-m-d h:i A'
        ]);
        
        $start = Carbon::createFromFormat('Y-m-d h:i A', $request->start_time);
        $service = Service::findOrFail($request->service_id);
        $end = $start->copy()->addMinutes($service->duration);

        if ($start->hour < 8 || $start->hour >= 18 || $start->hour === 12) {
            return back()->withErrors('Booking not allowed during lunch or off-hours.');
        }

        $appointment->service_id = $service->id;
        $appointment->start_time = $start;
        $appointment->end_time = $end;
        $appointment->notes = $request->notes;
        $appointment->save();

        return redirect()->route('customer.appointments.index')->with('success', 'Appointment updated.');
    }

public function destroy($id)
{
    $appointment = Appointment::where('customer_id', Auth::id())->findOrFail($id);

    $startTime = $appointment->start_time instanceof Carbon
        ? $appointment->start_time
        : Carbon::parse($appointment->start_time);

    $now = Carbon::now('Asia/Manila');
    $startTime = $startTime->timezone('Asia/Manila');

    $minutesUntil = $now->diffInMinutes($startTime, false);

    if ($minutesUntil < 60) {
        return back()->withErrors(
            'Cancellations must be made at least 1 hour before the appointment time.'
        );
    }

    if ($appointment->status === 'Cancelled') {
        return back()->withErrors('This appointment is already cancelled.');
    }

    $appointment->status = 'Cancelled';
    $appointment->save();

    return redirect()
        ->route('customer.appointments.index')
        ->with('success', 'Appointment cancelled successfully.');
}

}