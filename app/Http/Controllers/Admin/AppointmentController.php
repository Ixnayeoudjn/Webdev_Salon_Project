<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

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
        $customers = User::role('customer')->get();
        $staffs = User::role('staff')->get();
        $services = Service::all();
        $selectedDate = $request->date;
        return view('admin.appointments.index', compact('appointments', 'customers', 'staffs', 'services', 'selectedDate'));
    }
public function calendar()
{
    // Eager load relationships and fetch all appointment data
    $appointments = Appointment::with('customer', 'staff', 'service')->get();

    // Pass raw data to the view; frontend JS will handle formatting
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
        $input = $request->all();

        // Parse the input as 12-hour format with AM/PM
        try {
            $start = Carbon::createFromFormat('Y-m-d h:i A', $input['start_time']);
        } catch (\Exception $e) {
            return back()->withErrors('Invalid date/time format.')->withInput();
        }

        // Convert to 24-hour format string for validation
        $input['start_time'] = $start->format('Y-m-d H:i');

        // Validate input using 24-hour format
        $validator = Validator::make($input, [
            'customer_id' => 'required|exists:users,id',
            'staff_id'    => 'required|exists:users,id',
            'service_id'  => 'required|exists:services,id',
            'start_time'  => 'required|date_format:Y-m-d H:i'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $service = Service::findOrFail($input['service_id']);
        $end = $start->copy()->addMinutes($service->duration);

        // Business rule checks
        if ($start->hour < 8 || $start->hour >= 18 || $start->hour === 12) {
            return back()->withErrors('Invalid time slot.')->withInput();
        }

        // --- CUSTOMER 1-HOUR BUFFER VALIDATION ---
        $customerId = $input['customer_id'];
        $bufferMinutes = 60;

        $customerConflict = Appointment::where('customer_id', $customerId)
            ->where('status', '!=', 'Cancelled')
            ->where(function($query) use ($start, $end, $bufferMinutes) {
                $query->where(function($q) use ($start, $end, $bufferMinutes) {
                    $q->whereBetween('start_time', [
                        $start->copy()->subMinutes($bufferMinutes),
                        $end->copy()->addMinutes($bufferMinutes - 1)
                    ]);
                })
                ->orWhere(function($q) use ($start, $end, $bufferMinutes) {
                    $q->whereBetween('end_time', [
                        $start->copy()->subMinutes($bufferMinutes + 1),
                        $end->copy()->addMinutes($bufferMinutes)
                    ]);
                })
                ->orWhere(function($q) use ($start, $end) {
                    $q->where('start_time', '<=', $start)
                      ->where('end_time', '>=', $end);
                });
            })
            ->first();

        if ($customerConflict) {
            return back()->withErrors('This customer already has an appointment at or near this time. Please book at least 1 hour before or after their existing bookings.')->withInput();
        }
        // --- END CUSTOMER BUFFER VALIDATION ---

        // Staff conflict check (existing logic)
        $conflict = Appointment::where('staff_id', $input['staff_id'])
            ->whereBetween('start_time', [$start, $end])
            ->exists();

        if ($conflict) {
            return back()->withErrors('Staff already booked for this slot.')->withInput();
        }

        Appointment::create([
            'customer_id' => $input['customer_id'],
            'staff_id'    => $input['staff_id'],
            'service_id'  => $input['service_id'],
            'start_time'  => $start,
            'end_time'    => $end,
            'notes'       => $input['notes'] ?? null,
            'status'      => 'Pending'
        ]);

        return redirect()->route('admin.appointments.calendar')->with('success', 'Appointment created.');
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
            return redirect()->route('admin.appointments.index')->withErrors('Cannot update a cancelled appointment.');
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

        // --- CUSTOMER 1-HOUR BUFFER VALIDATION (for update) ---
        $customerId = $request->customer_id;
        $bufferMinutes = 60;

        $customerConflict = Appointment::where('customer_id', $customerId)
            ->where('id', '!=', $id)
            ->where('status', '!=', 'Cancelled')
            ->where(function($query) use ($start, $end, $bufferMinutes) {
                $query->where(function($q) use ($start, $end, $bufferMinutes) {
                    $q->whereBetween('start_time', [
                        $start->copy()->subMinutes($bufferMinutes),
                        $end->copy()->addMinutes($bufferMinutes - 1)
                    ]);
                })
                ->orWhere(function($q) use ($start, $end, $bufferMinutes) {
                    $q->whereBetween('end_time', [
                        $start->copy()->subMinutes($bufferMinutes + 1),
                        $end->copy()->addMinutes($bufferMinutes)
                    ]);
                })
                ->orWhere(function($q) use ($start, $end) {
                    $q->where('start_time', '<=', $start)
                      ->where('end_time', '>=', $end);
                });
            })
            ->first();

        if ($customerConflict) {
            return back()->withErrors('There is already an appointment at or near this time. Please book at least 1 hour before or after their existing bookings.');
        }
        // --- END CUSTOMER BUFFER VALIDATION ---

        // Staff conflict check (existing logic)
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

        return redirect()->route('admin.appointments.index')->with('success','Appointment updated.');
    }

    public function destroy($id)
    {
        Appointment::destroy($id);
        return redirect()->route('admin.appointments.index')->with('success','Appointment deleted.');
    }
}