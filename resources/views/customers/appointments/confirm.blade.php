<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CopyCut Salon</title>
    <link rel="stylesheet" href="{{ asset('css/confirm.css') }}">
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

    <div class="container">
        <a href="{{ route('customer.appointments.create') }}" class="back-btn">
            ← Back to Booking
        </a>

        <div class="header"></div>
        
        <div class="confirmation-content">
            <div class="appointment-details">
                <div class="detail-section">
                    <h3>
                        <i class="ri-calendar-line"></i>
                        Appointment Details
                    </h3>
                    <div class="detail-row">
                        <span class="detail-label">Date</span>
                        <span class="detail-value">{{ \Carbon\Carbon::createFromFormat('Y-m-d h:i A', $summary['start_time'])->format('l, F j, Y') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Time</span>
                        <span class="detail-value">
                          {{ \Carbon\Carbon::createFromFormat('Y-m-d h:i A', $summary['start_time'])->format('g:i A') }} - 
                          {{ \Carbon\Carbon::createFromFormat('Y-m-d h:i A', $summary['end_time'])->format('g:i A') }}
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Duration</span>
                        <span class="detail-value">{{ $summary['service_duration'] }} minutes</span>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>
                        <i class="ri-scissors-line"></i>
                        Selected Service
                    </h3>
                    
                    <div class="service-item">
                        <div>
                            <div class="service-name">{{ $summary['service_name'] }}</div>
                            <div class="service-duration">{{ $summary['service_duration'] }} minutes</div>
                        </div>
                        <div class="service-price">₱{{ number_format($summary['service_price'], 0) }}</div>
                    </div>
                    
                    <div class="total-section">
                        <div class="total-row">
                            <span>Total Amount</span>
                            <span>₱{{ number_format($summary['service_price'], 0) }}</span>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h3>
                        <i class="ri-map-pin-line"></i>
                        Location
                    </h3>
                    <div class="detail-row">
                        <span class="detail-label">Salon Address</span>
                        <span class="detail-value">{{ config('app.salon_address', '123 Beauty Street, Manila, Metro Manila') }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Contact</span>
                        <span class="detail-value">{{ config('app.salon_phone', '(02) 123-4567') }}</span>
                    </div>
                </div>

                @if($summary['notes'])
                <div class="detail-section">
                    <h3>
                        <i class="ri-chat-3-line"></i>
                        Special Requests
                    </h3>
                    <div class="special-requests">
                        {{ $summary['notes'] }}
                    </div>
                </div>
                @endif
            </div>

            <div class="important-notes">
                <h4>
                    <i class="ri-information-line"></i>
                    Important Information
                </h4>
                <ul>
                    <li>Please arrive 10 minutes before your appointment time</li>
                    <li>Bring a valid ID for verification</li>
                    <li>Payment can be made in cash or card at the salon</li>
                    <li>Contact us at (02) 123-4567 for any changes or questions</li>
                </ul>
            </div>

            <div class="action-buttons">
                <form method="POST" action="{{ route('customer.appointments.confirm') }}" style="display: contents;" id="confirmForm">
                    @csrf
                    <input type="hidden" name="service_id" value="{{ $summary['service_id'] }}">
                    <input type="hidden" name="start_time" value="{{ $summary['start_time'] }}">
                    <input type="hidden" name="end_time" value="{{ $summary['end_time'] }}">
                    <input type="hidden" name="notes" value="{{ $summary['notes'] }}">
                    <button type="submit" class="btn btn-primary" id="confirmBtn">
                        <i class="ri-check-double-line"></i>
                        Confirm Your Booking
                    </button>
                </form>
                <a href="{{ route('customer.appointments.create') }}" class="btn btn-secondary">
                    <i class="ri-arrow-left-line"></i>
                    Back to Booking
                </a>
            </div>
        </div>
    </div>

  <script>
    document.addEventListener('DOMContentLoaded', function() {
        const confirmBtn = document.getElementById('confirmBtn');
        const form = document.getElementById('confirmForm');
        
        // Add loading state to confirm button
        form.addEventListener('submit', function(e) {
            confirmBtn.innerHTML = '<span>⏳</span> Processing...';
            confirmBtn.disabled = true;
            confirmBtn.style.opacity = '0.7';
        });

        // Add subtle animations to summary items
        const summaryItems = document.querySelectorAll('.detail-section');
        summaryItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.5s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 100);
        });
    });
    
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
</body>
</html>