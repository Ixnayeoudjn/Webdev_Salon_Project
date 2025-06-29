<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CopyCut Salon</title>
    <link rel="stylesheet" href="{{ asset('css/a-dashboard.css') }}">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link rel="icon" href="{{ asset('icon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div class="header-title">
                    <div class="header-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h1>Appointment Calendar</h1>
                </div>
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">24</div>
                        <div class="stat-label">Today's Appointments</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">156</div>
                        <div class="stat-label">This Month</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">3</div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Create Appointment
                </a>
                <a href="#" class="btn btn-secondary">
                    <i class="fas fa-list"></i>
                    View All Appointments
                </a>
                <a href="#" class="btn btn-secondary">
                    <i class="fas fa-cog"></i>
                    Manage Services
                </a>
            </div>
        </div>

        <!-- Calendar -->
        <div class="calendar-container">
            <div class="calendar-header">
                <h2 class="calendar-title">
                    <i class="fas fa-calendar-check" style="color: #667eea; margin-right: 0.5rem;"></i>
                    Schedule Overview
                </h2>
                <div class="view-selector">
                    <button class="view-btn active" data-view="month">
                        <i class="fas fa-calendar"></i> Month
                    </button>
                    <button class="view-btn" data-view="week">
                        <i class="fas fa-calendar-week"></i> Week
                    </button>
                    <button class="view-btn" data-view="day">
                        <i class="fas fa-calendar-day"></i> Day
                    </button>
                </div>
            </div>

            <div id="calendar">
                <div class="loading">
                    <div class="spinner"></div>
                    Loading calendar...
                </div>
            </div>

            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color legend-active"></div>
                    Active Appointments
                </div>
                <div class="legend-item">
                    <div class="legend-color legend-cancelled"></div>
                    Cancelled Appointments
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule-X JavaScript Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/preact@10.23.2/dist/preact.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/preact@10.23.2/hooks/dist/hooks.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@preact/signals-core@1.8.0/dist/signals-core.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@preact/signals@1.3.0/dist/signals.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/preact@10.23.2/jsx-runtime/dist/jsxRuntime.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/preact@10.23.2/compat/dist/compat.umd.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@schedule-x/calendar@2.2.0/dist/core.umd.js"></script>

    <script>
        // Sample appointment data (in production, this would come from Laravel)
        const sampleAppointments = [
            {
                id: 1,
                customer: { name: 'John Smith' },
                service: { name: 'Haircut & Style' },
                staff: { name: 'Sarah Johnson' },
                start_time: '2025-06-30 09:00:00',
                end_time: '2025-06-30 10:00:00',
                status: 'Confirmed',
                notes: 'Regular customer, prefers short cut'
            },
            {
                id: 2,
                customer: { name: 'Emma Wilson' },
                service: { name: 'Color Treatment' },
                staff: { name: 'Mike Davis' },
                start_time: '2025-06-30 14:00:00',
                end_time: '2025-06-30 16:00:00',
                status: 'Confirmed',
                notes: 'First time color treatment'
            },
            {
                id: 3,
                customer: { name: 'Robert Brown' },
                service: { name: 'Beard Trim' },
                staff: { name: 'Alex Thompson' },
                start_time: '2025-07-01 11:00:00',
                end_time: '2025-07-01 11:30:00',
                status: 'Cancelled',
                notes: 'Client requested cancellation'
            },
            {
                id: 4,
                customer: { name: 'Lisa Garcia' },
                service: { name: 'Full Service' },
                staff: { name: 'Sarah Johnson' },
                start_time: '2025-07-02 13:00:00',
                end_time: '2025-07-02 15:30:00',
                status: 'Confirmed',
                notes: 'Wedding preparation'
            }
        ];

        document.addEventListener('DOMContentLoaded', function() {
            const { createCalendar, createViewMonthGrid, createViewWeek, createViewDay } = window.SXCalendar;

            // Convert appointments to calendar events
            const events = sampleAppointments.map(appointment => ({
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
                        showNotification(`Viewing appointments for ${date}`);
                    },
                    onClickDateTime(dateTime) {
                        const date = dateTime.split(' ')[0];
                        showNotification(`Viewing appointments for ${date}`);
                    },
                    onEventClick(calendarEvent) {
                        const appointmentId = calendarEvent.id;
                        const appointment = sampleAppointments.find(app => app.id == appointmentId);
                        if (appointment) {
                            showAppointmentDetails(appointment);
                        }
                    }
                }
            });

            // Remove loading and render calendar
            setTimeout(() => {
                document.getElementById('calendar').innerHTML = '';
                calendar.render(document.getElementById('calendar'));
            }, 1000);

            // View selector functionality
            document.querySelectorAll('.view-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    
                    const view = btn.dataset.view;
                    let viewName = 'month-grid';
                    if (view === 'week') viewName = 'week';
                    if (view === 'day') viewName = 'day';
                    
                    calendar.setView(viewName);
                });
            });

            // Helper functions
            function formatTo12Hour(datetimeStr) {
                if (!datetimeStr) return 'Not set';
                let dt = datetimeStr.replace(' ', 'T');
                if (dt.length === 16) dt += ':00';
                const dateObj = new Date(dt);
                if (isNaN(dateObj.getTime())) return datetimeStr;
                let hours = dateObj.getHours();
                const minutes = dateObj.getMinutes().toString().padStart(2, '0');
                const ampm = hours >= 12 ? 'PM' : 'AM';
                hours = hours % 12;
                hours = hours ? hours : 12;
                return `${hours}:${minutes} ${ampm}`;
            }

            function showAppointmentDetails(appointment) {
                const start = formatTo12Hour(appointment.start_time);
                const end = formatTo12Hour(appointment.end_time);
                const date = appointment.start_time ? appointment.start_time.split(' ')[0] : '';
                const details = `
Customer: ${appointment.customer?.name || 'Unknown'}
Service: ${appointment.service?.name || 'Unknown'}
Staff: ${appointment.staff?.name || 'Unassigned'}
Date: ${date}
Time: ${start} - ${end}
Status: ${appointment.status}
Notes: ${appointment.notes || 'No notes'}
                `;
                alert(details);
            }

            function showNotification(message) {
                const notification = document.createElement('div');
                notification.className = 'notification';
                notification.innerHTML = `
                    <i class="fas fa-info-circle" style="color: #667eea; margin-right: 0.5rem;"></i>
                    ${message}
                `;
                document.body.appendChild(notification);
                
                setTimeout(() => notification.classList.add('show'), 100);
                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => document.body.removeChild(notification), 300);
                }, 3000);
            }
        });
    </script>
</body>
</html>