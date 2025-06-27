@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Appointment Calendar</h2>
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary mb-3">Create Appointment</a>
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary mb-3">View All Appointments</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <!-- Calendar Container -->
            <div id="calendar" style="width: 100%; height: 800px; max-height: 90vh;"></div>
        </div>
    </div>
</div>

<!-- Schedule-X CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@schedule-x/theme-default@2.2.0/dist/index.css">

<!-- Schedule-X JavaScript Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/preact@10.23.2/dist/preact.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/preact@10.23.2/hooks/dist/hooks.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@preact/signals-core@1.8.0/dist/signals-core.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@preact/signals@1.3.0/dist/signals.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/preact@10.23.2/jsx-runtime/dist/jsxRuntime.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/preact@10.23.2/compat/dist/compat.umd.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@schedule-x/calendar@2.2.0/dist/core.umd.js"></script>

<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const { createCalendar, createViewMonthGrid, createViewWeek, createViewDay } = window.SXCalendar;
        
        // Convert Laravel appointments to Schedule-X events format
        const appointments = @json($appointments);
        const events = appointments.map(appointment => ({
            id: appointment.id,
            title: `${appointment.customer?.name || 'Unknown'} - ${appointment.service?.name || 'Unknown'}`,
            start: appointment.start_time ? appointment.start_time.replace(' ', 'T') : '',
            end: appointment.end_time ? appointment.end_time.replace(' ', 'T') : appointment.start_time ? new Date(new Date(appointment.start_time).getTime() + 60*60*1000).toISOString().slice(0,16) : '',
            description: `Staff: ${appointment.staff?.name || 'Unassigned'}\nStatus: ${appointment.status}\nNotes: ${appointment.notes || 'No notes'}`,
            calendarId: appointment.status === 'Cancelled' ? 'cancelled' : 'active'
        }));

        // Create the calendar
        const calendar = createCalendar({
            views: [
                createViewMonthGrid(),
                createViewWeek(),
                createViewDay()
            ],
            defaultView: 'month-grid',
            events: events,
            calendars: {
                active: {
                    colorName: 'blue',
                    lightColors: {
                        main: '#1976d2',
                        container: '#e3f2fd',
                        onContainer: '#0d47a1'
                    }
                },
                cancelled: {
                    colorName: 'red',
                    lightColors: {
                        main: '#d32f2f',
                        container: '#ffebee',
                        onContainer: '#b71c1c'
                    }
                }
            },
            callbacks: {
                // Handle date clicks in month grid - redirect to table view
                onClickDate(date) {
                    redirectToDateView(date);
                },
                
                // Handle datetime clicks in week/day views - redirect to table view
                onClickDateTime(dateTime) {
                    const date = dateTime.split(' ')[0];
                    redirectToDateView(date);
                },
                
                // Handle event clicks - show appointment details
                onEventClick(calendarEvent) {
                    const appointmentId = calendarEvent.id;
                    const appointment = appointments.find(app => app.id == appointmentId);
                    if (appointment) {
                        showAppointmentDetails(appointment);
                    }
                }
            }
        });

        // Render the calendar
        calendar.render(document.getElementById('calendar'));

        // Function to redirect to table view for a specific date
        function redirectToDateView(date) {
            const formattedDate = new Date(date).toISOString().split('T')[0];
            window.location.href = `{{ route('admin.appointments.index') }}?date=${formattedDate}`;
        }

        // Function to show appointment details in a modal or alert
        function showAppointmentDetails(appointment) {
            const details = `
                Customer: ${appointment.customer?.name || 'Unknown'}
                Service: ${appointment.service?.name || 'Unknown'}
                Staff: ${appointment.staff?.name || 'Unassigned'}
                Date & Time: ${appointment.start_time ? new Date(appointment.start_time).toLocaleString() : 'Not set'}
                Status: ${appointment.status}
                Notes: ${appointment.notes || 'No notes'}
            `;
            alert(details);
        }
    });
</script>

<!-- Optional: Add some custom styles -->
<style>
    #calendar {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }
    
    .sx__month-grid-day {
        cursor: pointer;
    }
    
    .sx__month-grid-day:hover {
        background-color: #f8f9fa !important;
    }
</style>
@endsection
