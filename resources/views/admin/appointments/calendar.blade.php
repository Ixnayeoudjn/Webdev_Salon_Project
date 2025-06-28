@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Appointment Calendar</h2>
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary mb-3" style="text-decoration-line: underline">Create Appointment</a>
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary mb-3" style="text-decoration-line: underline">View All Appointments</a>
            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary mb-3" style="text-decoration-line: underline">Manage Services</a>
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

        // Pass raw DB values to JS
        const appointments = @json($appointments);

        // Use raw start_time and end_time for event positions
        const events = appointments.map(appointment => ({
            id: appointment.id,
            title: `${appointment.customer?.name || 'Unknown'} - ${appointment.service?.name || 'Unknown'}`,
            start: appointment.start_time ? appointment.start_time.replace(' ', 'T') : '',
            end: appointment.end_time ? appointment.end_time.replace(' ', 'T') : '',
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
                onClickDate(date) {
                    redirectToDateView(date);
                },
                onClickDateTime(dateTime) {
                    const date = dateTime.split(' ')[0];
                    redirectToDateView(date);
                },
                onEventClick(calendarEvent) {
                    const appointmentId = calendarEvent.id;
                    const appointment = appointments.find(app => app.id == appointmentId);
                    if (appointment) {
                        showAppointmentDetails(appointment);
                    }
                }
            }
        });

        calendar.render(document.getElementById('calendar'));

        function redirectToDateView(date) {
            const formattedDate = new Date(date).toISOString().split('T')[0];
            window.location.href = `{{ route('admin.appointments.index') }}?date=${formattedDate}`;
        }

        // Helper: Format DB time to 12-hour AM/PM
        function formatTo12Hour(datetimeStr) {
            if (!datetimeStr) return 'Not set';
            // Accepts 'YYYY-MM-DD HH:mm:ss' or 'YYYY-MM-DDTHH:mm:ss'
            let dt = datetimeStr.replace(' ', 'T');
            if (dt.length === 16) dt += ':00'; // add seconds if missing
            const dateObj = new Date(dt);
            if (isNaN(dateObj.getTime())) return datetimeStr;
            let hours = dateObj.getHours();
            const minutes = dateObj.getMinutes().toString().padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // 0 should be 12
            return `${hours}:${minutes} ${ampm}`;
        }

        // Show details with 12-hour format
        function showAppointmentDetails(appointment) {
            const start = formatTo12Hour(appointment.start_time);
            const end = formatTo12Hour(appointment.end_time);
            const date = appointment.start_time ? appointment.start_time.split(' ')[0] : '';
            const details = `
            Customer: ${appointment.customer?.name || 'Unknown'}
            Service: ${appointment.service?.name || 'Unknown'}
            Staff: ${appointment.staff?.name || 'Unassigned'}
            Date: ${date}
            Time: ${appointment.start_time} - ${appointment.end_time}
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