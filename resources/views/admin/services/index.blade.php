<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CopyCut Salon</title>
    <link rel="stylesheet" href="{{ asset('css/service.css') }}">
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
                <i class="ri-tools-fill"></i> View Dashboard
            </a>
        </div>
    </div>
    
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-primary"><i class="ri-scissors-cut-line me-2"></i>Manage Services</h2>

        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-checkbox-circle-line me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Service</th>
                                <th scope="col">Price</th>
                                <th scope="col">Duration</th>
                                <th scope="col">Availability</th>
                                <th scope="col" class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($services as $service)
                                <tr>
                                    <td class="fw-medium text-dark">{{ $service->name }}</td>
                                    <td>₱{{ number_format($service->price, 2) }}</td>
                                    <td>{{ $service->duration }} min</td>
                                    <td>
                                        @if($service->is_available)
                                            <span class="badge bg-success-subtle text-success">Available</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Unavailable</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('admin.services.toggle', $service->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm rounded-pill {{ $service->is_available ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                <i class="ri-refresh-line me-1"></i> 
                                                {{ $service->is_available ? 'Set Unavailable' : 'Set Available' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">
                                        <i class="ri-information-line"></i> No services found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
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