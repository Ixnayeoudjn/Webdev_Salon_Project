<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CopyCut Salon</title>
    <link rel="stylesheet" href="{{ asset('css/staff.css') }}">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link rel="icon" href="{{ asset('icon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="{{ route('staff.appointments.index') }}"><img src="{{ asset('images/Logo.png') }}" alt="CopyCut Logo"></a>
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
                </form>

                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <!-- Header Section -->
        <div class="dashboard-header"></div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>
                    {{ $appointments->filter(function($appointment) {
                        return $appointment->start_time >= now()->startOfDay() && 
                               $appointment->start_time <= now()->endOfDay();
                    })->count() }}
                </h3>
                <p><i class="fas fa-calendar-day"></i> Today's Appointments</p>
            </div>
            <div class="stat-card">
                <h3>
                    {{ $appointments->filter(function($appointment) {
                        return $appointment->start_time >= now()->startOfWeek() && 
                               $appointment->start_time <= now()->endOfWeek();
                    })->count() }}
                </h3>
                <p><i class="fas fa-calendar-week"></i> This Week</p>
            </div>
            <div class="stat-card">
                <h3>
                    {{ $appointments->where('status', 'pending')->count() }}
                </h3>
                <p><i class="fas fa-clock"></i> Pending Appointments</p>
            </div>
        </div>

        <!-- Appointments Section -->
        <div class="appointments-section">
            <div class="section-header">
                <i class="fas fa-cut"></i>
                <h2>Your Appointments</h2>
                <button class="refresh-btn" onclick="refreshSchedule()" title="Refresh Schedule">
                    <i class="ri-restart-line"></i>
                </button>
            </div>

            <div class="appointments-grid" id="appointmentsList">
                @forelse($appointments as $appointment)
                    <div class="appointment-card" data-status="{{ $appointment->status }}">
                        <div class="appointment-header">
                            <div class="appointment-info">
                                <div class="service-name">{{ $appointment->service->name ?? 'Service Not Found' }}</div>
                                <div class="customer-name">
                                    <i class="fas fa-user"></i>
                                    {{ $appointment->customer->name ?? 'Customer Not Found' }}
                                </div>
                                @if($appointment->customer->phone ?? false)
                                    <div class="customer-phone">
                                        <i class="fas fa-phone"></i>
                                        {{ $appointment->customer->phone }}
                                    </div>
                                @endif
                            </div>
                            <div class="status-badge status-{{ strtolower($appointment->status) }}">
                                {{ ucfirst($appointment->status) }}
                            </div>
                        </div>
                        
                        <div class="appointment-details">
                            <div class="time-info">
                                <i class="fas fa-clock"></i>
                                <span class="date">{{ $appointment->start_time->format('M d, Y') }}</span>
                                <span class="time">{{ $appointment->start_time->format('g:i A') }}</span>
                                @if($appointment->end_time)
                                    - {{ $appointment->end_time->format('g:i A') }}
                                @endif
                            </div>
                            
                            @if($appointment->end_time)
                                <div class="duration-info">
                                    <i class="fas fa-hourglass-half"></i>
                                    {{ $appointment->start_time->diffInMinutes($appointment->end_time) }} minutes
                                </div>
                            @endif
                            
                            @if($appointment->service->price ?? false)
                                <div class="price-info">
                                    <i class="fas fa-peso-sign"></i>
                                    ₱{{ number_format($appointment->service->price, 2) }}
                                </div>
                            @endif
                            
                            @if($appointment->notes)
                                <div class="notes-info">
                                    <i class="fas fa-sticky-note"></i>
                                    <span class="notes-text">{{ Str($appointment->notes) }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Appointment Actions (if needed in future) -->
                        <div class="appointment-actions" style="display: none;">
                            <!-- Actions can be added here later if needed -->
                        </div>
                    </div>
                @empty
                    <div class="no-appointments">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No Appointments Scheduled</h3>
                        <p>You don't have any appointments at the moment.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on load
            const cards = document.querySelectorAll('.appointment-card, .stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Update stats dynamically
            updateStats();
        });

        function updateStats() {
            const appointments = document.querySelectorAll('.appointment-card');
            let todayCount = 0;
            let pendingCount = 0;
            const today = new Date().toDateString();

            appointments.forEach(appointment => {
                const timeElement = appointment.querySelector('.time-info');
                const statusElement = appointment.querySelector('.status-badge');
                
                if (timeElement && timeElement.textContent.includes('2024-06-29')) {
                    todayCount++;
                }
                
                if (statusElement && statusElement.textContent.toLowerCase().includes('pending')) {
                    pendingCount++;
                }
            });

            document.getElementById('todayCount').textContent = todayCount;
            document.getElementById('pendingCount').textContent = pendingCount;
        }

        function refreshSchedule() {
            const refreshBtn = document.querySelector('.refresh-btn i');
            refreshBtn.style.animation = 'spin 1s linear';
            
            setTimeout(() => {
                refreshBtn.style.animation = '';
                // Here you would typically reload the data from your backend
                console.log('Schedule refreshed!');
            }, 1000);
        }

        // Add CSS for spin animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);

        if (!document.querySelector('link[href*="remixicon"]')) {
        const link = document.createElement('link');
        link.href = 'https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css';
        link.rel = 'stylesheet';
        document.head.appendChild(link);
    }

    function toggleDropdown() {
        const dropdown = document.getElementById('user-dropdown');
        const arrow = document.getElementById('dropdown-arrow');
        
        dropdown.classList.toggle('show');
        arrow.style.transform = dropdown.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
    }

    function showLogoutModal() {
        document.getElementById('logout-modal').classList.add('show');
        // Close dropdown when modal opens
        document.getElementById('user-dropdown').classList.remove('show');
        document.getElementById('dropdown-arrow').style.transform = 'rotate(0deg)';
    }

    function hideLogoutModal() {
        document.getElementById('logout-modal').classList.remove('show');
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

    // Close modal when clicking outside
    document.getElementById('logout-modal').addEventListener('click', function(event) {
        if (event.target === this) {
            hideLogoutModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            hideLogoutModal();
        }
    });

    // Handle logout and redirect to landing page
    function handleLogout(event) {
        // Let the form submit normally, but add a redirect parameter
        const form = event.target;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'redirect';
        input.value = '{{ url("/") }}';
        form.appendChild(input);
    }
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
                document.getElementById('logout-form').submit();
            }
        });
    });
</script>
</body>
</html>