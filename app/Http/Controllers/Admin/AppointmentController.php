<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function show($id)
    {
        $appointment = Appointment::with('customer', 'staff', 'service')->findOrFail($id);
        return view('admin.appointments.show', compact('appointment'));
    }

      public function index(Request $request)
    {
        $query = Appointment::with('customer','staff','service')->orderBy('start_time','asc');
        if ($request->has('date')) {
            $query->whereDate('start_time', $request->date);
        }
        $appointments = $query->get();
        $staff = User::role('staff')->get();
        $selectedDate = $request->date;
        return view('admin.appointments.index', compact('appointments', 'staff', 'selectedDate'));
    }

    public function calendar()
    {
        $appointments = Appointment::with('customer','staff','service')->get();
        return view('admin.appointments.calendar', compact('appointments'));
    }

    public function create()
    {
        $customers = User::role('customer')->get();
        $staffs = User::role('staff')->get();
        $services = Service::all();

        return view('admin.appointments.create', compact('customers','staffs','services'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'staff_id'    => 'required|exists:users,id',
            'service_id'  => 'required|exists:services,id',
            'start_time'  => 'required|date_format:Y-m-d H:i'
        ]);

        $start = Carbon::parse($request->start_time);
        $service = Service::findOrFail($request->service_id);
        $end = $start->copy()->addMinutes($service->duration);

        // Business rule checks
        if ($start->hour < 8 || $start->hour >= 18 || $start->hour === 12) {
            return back()->withErrors('Invalid time slot.');
        }

        $conflict = Appointment::where('staff_id', $request->staff_id)
            ->whereBetween('start_time', [$start, $end])
            ->exists();
        if ($conflict) {
            return back()->withErrors('Staff already booked for this slot.');
        }

        Appointment::create([
            'customer_id' => $request->customer_id,
            'staff_id'    => $request->staff_id,
            'service_id'  => $request->service_id,
            'start_time'  => $start,
            'end_time'    => $end,
            'notes'       => $request->notes,
            'status'      => 'Pending'
        ]);

        return redirect()->route('admin.appointments.calendar')->with('success','Appointment created.');
    }

    public function edit($id)
    {
        $appointment = Appointment::findOrFail($id);
        if ($appointment->status === 'Cancelled') {
            return redirect()->route('admin.appointments.calendar')->withErrors('Cannot edit a cancelled appointment.');
        }
        $customers = User::role('customer')->get();
        $staffs    = User::role('staff')->get();
        $services  = Service::all();

        return view('admin.appointments.edit', compact('appointment','customers','staffs','services'));
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        if ($appointment->status === 'Cancelled') {
            return redirect()->route('admin.appointments.calendar')->withErrors('Cannot update a cancelled appointment.');
        }

        $request->validate([
            'customer_id' => 'required|exists:users,id',
            'staff_id'    => 'required|exists:users,id',
            'service_id'  => 'required|exists:services,id',
            'start_time'  => 'required|date_format:Y-m-d H:i'
        ]);

        $start = Carbon::parse($request->start_time);
        $service = Service::findOrFail($request->service_id);
        $end = $start->copy()->addMinutes($service->duration);

        if ($start->hour < 8 || $start->hour >= 18 || $start->hour === 12) {
            return back()->withErrors('Invalid time slot.');
        }

        $conflict = Appointment::where('staff_id', $request->staff_id)
            ->where('id','!=',$id)
            ->whereBetween('start_time', [$start, $end])
            ->exists();
        if ($conflict) {
            return back()->withErrors('Staff already booked for this slot.');
        }

        $appointment->update([
        'customer_id' => $request->customer_id,
        'staff_id'    => $request->staff_id,
        'service_id'  => $request->service_id,
        'start_time'  => $start,
        'end_time'    => $end,
        'notes'       => $request->notes,
        'status'      => $request->status,
        ]);

        return redirect()->route('admin.appointments.calendar')->with('success','Appointment updated.');
    }

    public function destroy($id)
    {
        Appointment::destroy($id);
        return redirect()->route('admin.appointments.calendar')->with('success','Appointment deleted.');
    }
}