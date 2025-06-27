<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::all();
        return view('admin.services.index', compact('services'));
    }

    public function toggleAvailability(Service $service)
    {
        $service->is_available = !$service->is_available;
        $service->save();

        return redirect()->route('admin.services.index')->with('success', 'Service availability updated.');
    }
}