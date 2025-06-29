<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CopyCut Salon</title>
    <link rel="stylesheet" href="{{ asset('css/a-dashboard.css') }}">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link rel="icon" href="{{ asset('icon.ico') }}">

    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="{{ url('/') }}"><img src="{{ asset('images/Logo.png') }}" alt="CopyCut Logo"></a>
        </div>
        <div class="nav-links">
            <div class="user-menu">
                <div class="user-greeting" onclick="toggleDropdown()">
                    <i class="ri-user-3-fill"></i>
                    <span>Hello, {{ auth()->user()->name ?? 'Guest' }}</span>
                    <i class="ri-arrow-down-s-line" id="dropdown-arrow"></i>
                </div>
                <div class="dropdown-menu" id="user-dropdown">
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <input type="hidden" name="redirect" value="{{ url('/') }}">
                        <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to sign out?')">
                            <i class="ri-logout-box-line"></i>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Header -->
        <div class="header"></div>
        <div class="header-buttons">
            <a href="{{ route('admin.appointments.calendar') }}" class="btn btn-primary" onclick="showCalendarView()">
                <i class="ri-calendar-check-line"></i>Calendar View
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
                <h3 id="totalAppointments">{{ $appointments->count() }}</h3>
                <p>Total Appointments</p>
            </div>
            <div class="stat-card">
                <i class="ri-time-line"></i>
                <h3 id="todayAppointments">{{ $appointments->filter(function($apt) { return $apt->start_time && $apt->start_time->isToday(); })->count() }}</h3>
                <p>Today's Appointments</p>
            </div>
            <div class="stat-card">
                <i class="ri-checkbox-circle-line"></i>
                <h3 id="completedAppointments">{{ $appointments->where('status', 'Completed')->count() }}</h3>
                <p>Completed</p>
            </div>
            <div class="stat-card">
                <i class="ri-hourglass-fill"></i>
                <h3 id="pendingAppointments">{{ $appointments->where('status', 'Pending')->count() }}</h3>
                <p>Pending</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Appointments Section -->
            <div class="appointments-section">
                <div class="section-header">
                    <h2 class="section-title">Appointments</h2>
                </div>

                <!-- Date Filter Section -->
                <div class="filter-section" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                    <div class="filter-controls" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <div class="filter-group">
                            <label for="dateFilter" style="display: block; margin-bottom: 5px; font-weight: 600; color: #722340;">Filter by Date:</label>
                            <input type="date" 
                                   id="dateFilter" 
                                   class="form-control" 
                                   value="{{ request('date') }}"
                                   style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                        </div>
                        <div class="filter-actions">
                            <button onclick="applyDateFilter()" class="btn btn-primary">
                                <i class="ri-filter-fill"></i>Apply Filter
                            </button>
                            <button onclick="clearDateFilter()" class="btn btn-secondary">
                                <i class="ri-delete-bin-5-line"></i>Clear
                            </button>
                            <button onclick="setToday()" class="btn btn-info">
                                <i class="ri-calendar-event-fill"></i>Today
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Current Filter Display -->
                @if($selectedDate ?? false)
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-filter"></i> Showing appointments for {{ \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') }}</span>
                            <a href="{{ route('admin.appointments.index') }}" class="btn btn-sm btn-outline-secondary">View All Appointments</a>
                        </div>
                    </div>
                @endif

                @if($appointments->count() > 0)
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Service</th>
                                <th>Staff</th>
                                <th>Date & Time</th>
                                <th>Notes</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($appointments as $appointment)
                                <tr>
                                    <td><strong>{{ $appointment->customer ? $appointment->customer->name : 'Unknown' }}</strong></td>
                                    <td>{{ $appointment->service ? $appointment->service->name : 'Unknown' }}</td>
                                    <td>{{ $appointment->staff ? $appointment->staff->name : 'Unassigned' }}</td>
                                    <td>{{ $appointment->start_time ? $appointment->start_time->format('M j, Y g:i A') : 'Not scheduled' }}</td>
                                    <td>{{ $appointment->notes ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $appointment->status === 'Completed' ? 'success' : ($appointment->status === 'Cancelled' ? 'danger' : ($appointment->status === 'Confirmed' ? 'primary' : 'warning')) }}">
                                            {{ $appointment->status }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            @if($appointment->status !== 'Cancelled')
                                                <a href="{{ route('admin.appointments.edit', $appointment->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="ri-edit-2-fill"></i></i> Edit
                                                </a>
                                            @endif
                                            <form action="{{ route('admin.appointments.destroy', $appointment->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this appointment?')">
                                                    <i class="ri-delete-bin-5-fill"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="alert alert-info">
                        @if($selectedDate ?? false)
                            No appointments scheduled for {{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}.
                        @else
                            No appointments found.
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Pass Laravel data to JavaScript
        const services = @json($services);

        function applyDateFilter() {
            const dateInput = document.getElementById('dateFilter');
            const selectedDate = dateInput.value;
            
            if (selectedDate) {
                const url = new URL(window.location);
                url.searchParams.set('date', selectedDate);
                window.location.href = url.toString();
            } else {
                alert('Please select a date to filter by.');
            }
        }

        // Clear date filter
        function clearDateFilter() {
            const url = new URL(window.location);
            url.searchParams.delete('date');
            window.location.href = url.toString();
        }

        // Set date to today
        function setToday() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('dateFilter').value = today;
            applyDateFilter();
        }

        // Allow Enter key to apply filter
        document.getElementById('dateFilter').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyDateFilter();
            }
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
</body>
</html>