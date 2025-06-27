<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointment;
use App\Http\Controllers\Staff\AppointmentController as StaffAppointment;
use App\Http\Controllers\Customer\AppointmentController as CustomerAppointment;
use App\Http\Controllers\ServiceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Common (authenticated) routes
Route::middleware(['auth'])->group(function () {

    // Super Admin
    Route::middleware(['role:super-admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('appointments', AdminAppointment::class);
        Route::get('/calendar', [AdminAppointment::class, 'calendar'])->name('calendar');
    });

    // Staff
    Route::middleware(['role:staff'])->prefix('staff')->name('staff.')->group(function () {
        Route::get('appointments', [StaffAppointment::class, 'index'])->name('appointments.index');
    });

    // Customer
    Route::middleware(['role:customer'])->prefix('customer')->name('customer.')->group(function () {
        Route::resource('appointments', CustomerAppointment::class);
    });
    
    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:super-admin'])->group(function () {
    Route::get('services', [App\Http\Controllers\Admin\ServiceController::class, 'index'])->name('services.index');
    Route::post('services/{service}/toggle', [App\Http\Controllers\Admin\ServiceController::class, 'toggleAvailability'])->name('services.toggle');
});
