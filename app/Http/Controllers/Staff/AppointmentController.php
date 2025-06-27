<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Appointment;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::where('staff_id', Auth::id())
            ->with('customer', 'service')
            ->orderBy('start_time', 'asc')
            ->get();

        return view('staff.appointments.index', compact('appointments'));
    }
}