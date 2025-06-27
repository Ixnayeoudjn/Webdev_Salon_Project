<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::where('customer_id', Auth::id())->with('service', 'staff')->latest()->get();
        $now = now();
        $upcoming = $appointments->filter(function($a) use ($now) {
            return $a->start_time >= $now;
        });
        // Add can_cancel property for each upcoming appointment
        foreach ($upcoming as $a) {
            $a->can_cancel = ($a->status !== 'Cancelled') && (Carbon::parse($a->start_time)->greaterThan($now));
        }
        $past = $appointments->filter(function($a) use ($now) {
            return $a->start_time < $now;
        });
        return view('customers.appointments.index', compact('upcoming', 'past'));
    }

    public function create()
    {
        $services = Service::all();
        return view('customers.appointments.create', compact('services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'start_time' => 'required|date_format:Y-m-d H:i'
        ]);

        $start = Carbon::parse($request->start_time);
        $service = Service::findOrFail($request->service_id);
        $end = $start->copy()->addMinutes($service->duration);

        // Validate operating hours
        if ($start->hour < 8 || $start->hour >= 18 || $start->hour === 12) {
            return back()->withErrors('Booking not allowed during lunch or off-hours.');
        }

        $appointment = new Appointment();
        $appointment->customer_id = Auth::id();
        $appointment->service_id = $service->id;
        $appointment->start_time = $start;
        $appointment->end_time = $end;
        $appointment->notes = $request->notes;
        $appointment->status = 'Pending';
        $appointment->staff_id = null; // Staff will be assigned later
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
            'start_time' => 'required|date_format:Y-m-d H:i'
        ]);

        $start = Carbon::parse($request->start_time);
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

        if (Carbon::now()->diffInMinutes(Carbon::parse($appointment->start_time), false) < 60) {
            return back()->withErrors('Cancellations must be made at least 1 hour before.');
        }

        if ($appointment->status === 'Cancelled') {
            return back()->withErrors('This appointment is already cancelled.');
        }

        $appointment->status = 'Cancelled';
        $appointment->save();

        return redirect()->route('customer.appointments.index')->with('success', 'Appointment cancelled.');
    }
}
