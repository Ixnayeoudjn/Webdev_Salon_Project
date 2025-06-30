<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CopyCut Salon</title>
    <link rel="stylesheet" href="{{ asset('css/a-calendar.css') }}">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link rel="icon" href="{{ asset('icon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="{{ route('admin.appointments.calendar') }}"><img src="{{ asset('images/Logo.png') }}" alt="CopyCut Logo"></a>
        </div>
        <div class="nav-links">
            <div class="user-menu">
                <div class="user-greeting" onclick="toggleDropdown()">
                    <i class="ri-user-3-fill"></i>
                    <span>Hello, {{ auth()->user()->name ?? 'Guest' }}</span>
                    <i class="ri-arrow-down-s-line" id="dropdown-arrow"></i>
                </div>
                <div class="dropdown-menu" id="user-dropdown">
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="redirect" value="{{ url('/') }}">
                    <button type="button" class="dropdown-item" id="logout-button">
                        <i class="ri-logout-box-line"></i>
                        Sign Out
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="header"></div>
        <div class="header-buttons">
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-primary" onclick="showCalendarView()">
                <i class="ri-calendar-check-line"></i>View Appointments
            </a>
            <a href="{{ route('admin.appointments.create') }}" class="btn btn-secondary" onclick="createAppointment()">
                <i class="ri-file-add-line"></i> New Appointment
            </a>
            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary" onclick="manageServices()">
                <i class="ri-tools-fill"></i> Manage Services
            </a>
        </div>
        
        <div class="container">
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <i class="ri-calendar-check-line"></i>
                    <h3>12</h3>
                    <p>Total Appointments</p>
                </div>
                <div class="stat-card">
                    <i class="ri-time-line"></i>
                    <h3>3</h3>
                    <p>Today's Appointments</p>
                </div>
                <div class="stat-card">
                    <i class="ri-checkbox-circle-line"></i>
                    <h3>8</h3>
                    <p>Completed</p>
                </div>
                <div class="stat-card">
                    <i class="ri-hourglass-fill"></i>
                    <h3>4</h3>
                    <p>Pending</p>
                </div>
            </div>

            <!-- Calendar -->
            <div class="calendar-container">
                <div class="calendar-header">
                    <h2 class="calendar-title">
                        <i class="fas fa-calendar-check" style="color: #5A1A31; margin-right: 0.5rem;"></i>
                        Schedule Overview
                    </h2>
                </div>

                <div id="calendar">
                    <div class="calendar-nav">
                    <button class="nav-btn" type="button" onclick="changeMonth(-1)">
                        <i class="ri-arrow-left-line"></i> Previous
                    </button>
                    <div class="current-month" id="currentMonth">June 2025</div>
                    <button class="nav-btn" type="button" onclick="changeMonth(1)">
                        Next <i class="ri-arrow-right-line"></i>
                    </button>
                    </div>
                    <div class="calendar-grid" id="calendarGrid">
                        <!-- Calendar will be generated here -->
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
                    <div class="legend-item">
                        <div class="legend-color legend-completed"></div>
                        Completed Appointments
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    const sampleAppointments = @json($appointments);

    let currentDate = new Date();
    const today = new Date();

    function generateCalendar(year, month) {
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const daysInMonth = lastDay.getDate();
        const startingDayOfWeek = firstDay.getDay();

        const calendarGrid = document.getElementById('calendarGrid');
        calendarGrid.innerHTML = '';

        // Add day headers
        const dayHeaders = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayHeaders.forEach(day => {
            const dayHeader = document.createElement('div');
            dayHeader.className = 'calendar-day-header';
            dayHeader.textContent = day;
            calendarGrid.appendChild(dayHeader);
        });

        // Add empty cells for days before the first day of the month
        for (let i = 0; i < startingDayOfWeek; i++) {
            const prevMonthDate = new Date(year, month, -startingDayOfWeek + i + 1);
            const emptyDay = createDayCell(prevMonthDate, true);
            calendarGrid.appendChild(emptyDay);
        }

        // Add days of the month
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(year, month, day);
            const dayCell = createDayCell(date, false);
            calendarGrid.appendChild(dayCell);
        }

        // Add empty cells to complete the last week
        const totalCells = calendarGrid.children.length - 7; // Subtract header row
        const remainingCells = 42 - totalCells; // 6 rows × 7 days - existing days
        for (let i = 1; i <= remainingCells; i++) {
            const nextMonthDate = new Date(year, month + 1, i);
            const emptyDay = createDayCell(nextMonthDate, true);
            calendarGrid.appendChild(emptyDay);
        }
    }

    function createDayCell(date, isOtherMonth) {
        const dayCell = document.createElement('div');
        dayCell.className = 'calendar-day';
        
        if (isOtherMonth) {
            dayCell.classList.add('other-month');
        }
        
        if (isSameDay(date, today)) {
            dayCell.classList.add('today');
        }

        const dayNumber = document.createElement('div');
        dayNumber.className = 'day-number';
        dayNumber.textContent = date.getDate();
        dayCell.appendChild(dayNumber);

        // Add appointments for this day
        const dayAppointments = getAppointmentsForDate(date);
        dayAppointments.forEach(appointment => {
            const eventElement = document.createElement('div');
            eventElement.className = `appointment-event ${appointment.status.toLowerCase()}`;
            eventElement.textContent = `${appointment.customer.name} - ${appointment.service.name}`;
            eventElement.onclick = () => showAppointmentDetails(appointment);
            dayCell.appendChild(eventElement);
        });

        return dayCell;
    }

    function getAppointmentsForDate(date) {
        return sampleAppointments.filter(appointment => {
            const appointmentDate = new Date(appointment.start_time);
            return isSameDay(appointmentDate, date);
        });
    }

    function isSameDay(date1, date2) {
        return date1.getFullYear() === date2.getFullYear() &&
                date1.getMonth() === date2.getMonth() &&
                date1.getDate() === date2.getDate();
    }

    function changeMonth(direction) {
        currentDate.setMonth(currentDate.getMonth() + direction);
        updateCalendar();
    }

    function updateCalendar() {
        const monthNames = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        
        document.getElementById('currentMonth').textContent = 
            `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
            
        generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
    }

    function showAppointmentDetails(appointment) {
        const start = formatTo12Hour(appointment.start_time);
        const end = formatTo12Hour(appointment.end_time);
        const date = appointment.start_time.split(' ')[0];
        
        const modal = document.createElement('div');
        modal.className = 'appointment-modal';
        modal.innerHTML = `
            <div class="appointment-modal-content">
                <div class="modal-header">
                    <h3><i class="ri-calendar-event-line"></i> Appointment Details</h3>
                    <button class="close-btn" onclick="this.closest('.appointment-modal').remove()">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="detail-row">
                        <strong><i class="ri-user-line"></i> Customer:</strong>
                        <span>${appointment.customer.name}</span>
                    </div>
                    <div class="detail-row">
                        <strong><i class="ri-scissors-line"></i> Service:</strong>
                        <span>${appointment.service.name}</span>
                    </div>
                    <div class="detail-row">
                        <strong><i class="ri-team-line"></i> Staff:</strong>
                        <span>${appointment.staff.name}</span>
                    </div>
                    <div class="detail-row">
                        <strong><i class="ri-calendar-line"></i> Date:</strong>
                        <span>${date}</span>
                    </div>
                    <div class="detail-row">
                        <strong><i class="ri-time-line"></i> Time:</strong>
                        <span>${start} - ${end}</span>
                    </div>
                    <div class="detail-row">
                        <strong><i class="ri-information-line"></i> Status:</strong>
                        <span class="status-badge status-${appointment.status.toLowerCase()}">${appointment.status}</span>
                    </div>
                    <div class="detail-row notes">
                        <strong><i class="ri-sticky-note-line"></i> Notes:</strong>
                        <span>${appointment.notes}</span>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        
        // Add styles for modal
        if (!document.getElementById('modal-styles')) {
            const modalStyles = document.createElement('style');
            modalStyles.id = 'modal-styles';
            modalStyles.textContent = `
                .appointment-modal {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    z-index: 2000;
                    animation: fadeIn 0.3s ease;
                }

                .appointment-modal-content {
                    background: #FFFBFA;
                    border-radius: 20px;
                    max-width: 500px;
                    width: 90%;
                    max-height: 80vh;
                    overflow-y: auto;
                    box-shadow: 0 20px 40px rgba(90, 26, 49, 0.3);
                    animation: slideIn 0.3s ease;
                }

                .modal-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 1.5rem 2rem;
                    border-bottom: 1px solid rgba(193, 155, 168, 0.2);
                    background: linear-gradient(135deg, #D8BEC7, #C19BA8);
                    border-radius: 20px 20px 0 0;
                }

                .modal-header h3 {
                    color: #5A1A31;
                    font-size: 1.2rem;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    margin: 0;
                }

                .close-btn {
                    background: none;
                    border: none;
                    font-size: 1.5rem;
                    color: #5A1A31;
                    cursor: pointer;
                    padding: 0.25rem;
                    border-radius: 50%;
                    transition: background 0.2s ease;
                }

                .close-btn:hover {
                    background: rgba(90, 26, 49, 0.1);
                }

                .modal-body {
                    padding: 2rem;
                }

                .detail-row {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    margin-bottom: 1rem;
                    padding: 0.75rem;
                    background: rgba(216, 190, 199, 0.1);
                    border-radius: 10px;
                }

                .detail-row strong {
                    color: #5A1A31;
                    font-weight: 600;
                    display: flex;
                    align-items: center;
                    gap: 0.5rem;
                    min-width: 100px;
                }

                .detail-row span {
                    color: #722340;
                    text-align: right;
                    flex: 1;
                }

                .detail-row.notes {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .detail-row.notes span {
                    text-align: left;
                    margin-top: 0.5rem;
                    padding: 0.5rem;
                    background: rgba(255, 255, 255, 0.5);
                    border-radius: 8px;
                    width: 100%;
                }

                .status-badge {
                    padding: 0.25rem 0.75rem;
                    border-radius: 20px;
                    font-size: 0.85rem;
                    font-weight: 500;
                }

                .status-confirmed {
                    background: #e8f5e8;
                    color: #1b5e20;
                }

                .status-pending {
                    background: #fff3e0;
                    color: #e65100;
                }

                .status-cancelled {
                    background: #ffebee;
                    color: #b71c1c;
                }

                .status-completed {
                    background: #e3f2fd;
                    color: #0d47a1;
                }

                .modal-actions {
                    padding: 1rem 2rem 2rem;
                    display: flex;
                    gap: 1rem;
                    justify-content: flex-end;
                }

                .btn-sm {
                    padding: 0.5rem 1rem;
                    font-size: 0.9rem;
                }

                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }

                @keyframes slideIn {
                    from {
                        transform: translateY(-50px);
                        opacity: 0;
                    }
                    to {
                        transform: translateY(0);
                        opacity: 1;
                    }
                }
            `;
            document.head.appendChild(modalStyles);
        }
        
        // Add click outside to close
        setTimeout(() => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }, 100);
    }

    function formatTo12Hour(datetimeStr) {
        if (!datetimeStr) return 'Not set';
        const dateObj = new Date(datetimeStr);
        if (isNaN(dateObj.getTime())) return datetimeStr;
        let hours = dateObj.getHours();
        const minutes = dateObj.getMinutes().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        return `${hours}:${minutes} ${ampm}`;
    }

    // View selector functionality
    document.querySelectorAll('.view-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const view = btn.dataset.view;
            // For now, all views show the month view
            // You can implement week and day views later
            console.log(`Switched to ${view} view`);
        });
    });

    // Initialize calendar
    document.addEventListener('DOMContentLoaded', function() {
        updateCalendar();
    });

     // Dropdown functionality
    function toggleDropdown() {
        const dropdown = document.getElementById('user-dropdown');
        const arrow = document.getElementById('dropdown-arrow');
        
        dropdown.classList.toggle('show');
        arrow.style.transform = dropdown.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userMenu = document.querySelector('.user-menu');
        const dropdown = document.getElementById('user-dropdown');
        const arrow = document.getElementById('dropdown-arrow');
        
        if (!userMenu.contains(event.target)) {
            dropdown.classList.remove('show');
            arrow.style.transform = 'rotate(0deg)';
        }
    });
</script>
<script>
  document.getElementById('logout-button').addEventListener('click', function(){
    Swal.fire({
      title: 'Sign Out',
      text: "Are you sure you want to sign out?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      confirmButtonText: 'Yes, sign me out',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        // submit the hidden form
        document.getElementById('logout-form').submit();
      }
    });
  });
</script>
</html>