<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CopyCut Salon</title>
    <link rel="stylesheet" href="{{ asset('css/appt.css') }}">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link rel="icon" href="{{ asset('icon.ico') }}">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="{{ route('customer.appointments.index') }}"><img src="{{ asset('images/Logo.png') }}" alt="CopyCut Logo"></a>
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
    
    <!-- Logout Confirmation Modal -->
    <div class="logout-modal" id="logout-modal">
        <div class="logout-modal-content">
            <h3>Sign Out</h3>
            <p>Are you sure you want to sign out of your CopyCut account?</p>
            <div class="logout-modal-actions">
                <button class="logout-btn cancel" onclick="hideLogoutModal()">Cancel</button>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;" onsubmit="handleLogout(event)">
                    @csrf
                    <button type="submit" class="logout-btn confirm">Sign Out</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="appointments-container">
        <div class="dashboard-header"></div>

        <a href="{{ route('customer.appointments.create') }}" class="create-appointment-btn">
            <i class="ri-add-line"></i>
            Create New Appointment
        </a>
            <div class="appointments-section">
                <h4 class="section-title">Upcoming Appointments</h4>
                @if($upcoming->count() > 0)
                    <ul class="appointments-list">
                        @foreach($upcoming as $appointment)
                            <li class="appointment-item">
                                <div class="appointment-main">
                                    <div class="appointment-service">
                                        <div class="service-icon">
                                            <i class="ri-scissors-line"></i>
                                        </div>
                                        <span>{{ $appointment->service->name }}</span>
                                    </div>
                                    
                                    <div class="appointment-datetime">
                                        <div class="appointment-date">
                                            {{ $appointment->start_time->format('M d, Y') }}
                                        </div>
                                        <div class="appointment-time">
                                            {{ $appointment->start_time->format('h:i A') }}
                                            @if($appointment->end_time)
                                                - {{ $appointment->end_time->format('h:i A') }}
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <div class="appointment-status status-{{ strtolower($appointment->status) }}">
                                        {{ $appointment->status }}
                                    </div>
                                    
                                    <div class="appointment-actions">
                                        @if($appointment->status !== 'Cancelled' && $appointment->start_time >= now())
                                            @php
                                                $canCancel = now()->diffInMinutes($appointment->start_time, false) >= 60;
                                            @endphp
                                            
                                            @if($canCancel)
                                                <form action="{{ route('customer.appointments.destroy', $appointment->id) }}" method="POST" class="cancel-form" data-appointment-id="{{ $appointment->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="cancel-btn sweet-cancel-button">Cancel</button>
                                                </form>
                                            @else
                                                <div class="cancel-disabled" style="color: #999; font-size: 0.85rem; font-style: italic;">
                                                    Cannot cancel (less than 1 hour away)
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                
                                @if($appointment->staff)
                                    <div style="margin-top: 0.5rem; color: #666; font-size: 0.9rem;">
                                        <i class="ri-user-line"></i> Staff: {{ $appointment->staff->name }}
                                    </div>
                                @endif
                                
                                @if($appointment->notes)
                                    <div style="margin-top: 1rem; padding: 1rem; background:rgb(248, 232, 237); border-radius: 8px; border-left: 4px solid #5A1A31;">
                                        <div style="font-size: 0.8rem; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Notes</div>
                                        <div style="color: #333;">{{ $appointment->notes }}</div>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="empty-state">
                        <div class="empty-icon"><i class="ri-booklet-fill"></i></div>
                        <div class="empty-message">No upcoming appointments</div>
                        <div class="empty-submessage">Book your next appointment to get started</div>
                    </div>
                @endif
            </div>
        <div class="appointments-section">
            <h4 class="section-title">Past Appointments</h4>
            @if($past->count() > 0)
                <ul class="appointments-list">
                    @foreach($past as $appointment)
                        <li class="appointment-item">
                            <div class="appointment-main">
                                <div class="appointment-service">
                                    <div class="service-icon">
                                        <i class="ri-scissors-line"></i>
                                    </div>
                                    <span>{{ $appointment->service->name }}</span>
                                </div>
                                
                                <div class="appointment-datetime">
                                    <div class="appointment-date">
                                        {{ $appointment->start_time->format('M d, Y') }}
                                    </div>
                                    <div class="appointment-time">
                                        {{ $appointment->start_time->format('h:i A') }}
                                        @if($appointment->end_time)
                                            - {{ $appointment->end_time->format('h:i A') }}
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="appointment-status status-{{ strtolower($appointment->status) }}">
                                    {{ $appointment->status }}
                                </div>
                                
                                <div class="appointment-actions">
                                    @if($appointment->status === 'Completed')
                                        <button class="create-appointment-btn" style="padding: 0.4rem 0.8rem; font-size: 0.8rem; margin: 0;">
                                            Book Again
                                        </button>
                                    @endif
                                </div>
                            </div>
                            
                            @if($appointment->staff)
                                <div style="margin-top: 0.5rem; color: #666; font-size: 0.9rem;">
                                    <i class="ri-user-line"></i> Staff: {{ $appointment->staff->name }}
                                </div>
                            @endif
                            
                            @if($appointment->notes)
                                <div style="margin-top: 1rem; padding: 1rem; background: #f0f0f0; border-radius: 8px; border-left: 4px solid #667eea;">
                                    <div style="font-size: 0.8rem; color: #666; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">Notes</div>
                                    <div style="color: #333;">{{ $appointment->notes }}</div>
                                </div>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="empty-state">
                    <div class="empty-icon"><i class="ri-booklet-fill"></i></div>
                    <div class="empty-message">No past appointments</div>
                    <div class="empty-submessage">Your appointment history will appear here</div>
                </div>
            @endif
        </div>
    </div>
</body>

<script>
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
            // submit the hidden form
            document.getElementById('logout-form').submit();
        }
        });
    });
    document.querySelectorAll('.sweet-cancel-button').forEach(button => {
        button.addEventListener('click', function () {
            const form = this.closest('form');
            const appointmentId = form.getAttribute('data-appointment-id');

            Swal.fire({
                title: 'Cancel Appointment?',
                text: "Are you sure you want to cancel this appointment?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#aaa',
                confirmButtonText: 'Yes, cancel it',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
</html>