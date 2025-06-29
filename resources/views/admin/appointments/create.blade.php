<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CopyCut Salon</title>
    <link rel="stylesheet" href="{{ asset('css/a-create.css') }}">
    <link href='https://fonts.googleapis.com/css?family=Poppins' rel='stylesheet'>
    <link rel="icon" href="{{ asset('icon.ico') }}">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
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
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <a href="{{ route('admin.appointments.index') }}" class="back-btn">
            ← Back to Appointments
        </a>

        <div class="header"></div>
        
        @if($errors->any())
            <div class="alert alert-danger">
                <h4>Please fix the following errors:</h4>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="form-container">
            <form id="bookingForm" method="POST" action="{{ route('admin.appointments.store') }}">
                @csrf

                {{-- Hidden fields for real submission --}}
                <input type="hidden" name="customer_id" id="customer_id_input">
                <input type="hidden" name="service_id" id="service_id_input">
                <input type="hidden" name="staff_id" id="staff_id_input">
                <input type="hidden" name="start_time" id="start_time_input">
                <input type="hidden" name="notes" id="notes_input">

                {{-- Customer selection --}}
                <div class="form-group">
                    <label>Select Customer</label>
                    <div class="customer-search">
                        <input type="text" id="customerSearch" placeholder="Search customer by name or email..." class="form-control">
                        <div class="customer-results" id="customerResults" style="display: none;"></div>
                    </div>
                    <div class="selected-customer" id="selectedCustomer" style="display: none;">
                        <div class="customer-info">
                            <div class="customer-name"></div>
                            <div class="customer-email"></div>
                            <button type="button" class="remove-btn" onclick="clearCustomer()">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Service selection --}}
                <div class="form-group">
                    <label>Select Service</label>
                    <div class="service-grid" id="serviceGrid">
                        <!-- populated by JS -->
                    </div>
                    <div class="selected-services-summary" id="selectedServicesSummary" style="display: none;">
                        <h4>Selected Service:</h4>
                        <div class="summary-list" id="summaryList"></div>
                        <div class="summary-total">
                            <strong>Duration: <span id="totalDuration">0</span> minutes</strong>
                            <strong>Price: ₱<span id="totalPrice">0</span></strong>
                        </div>
                    </div>
                </div>

                {{-- Staff assignment --}}
                <div class="form-group">
                    <label>Assign Staff Member</label>
                    <div class="staff-grid" id="staffGrid">
                        <!-- populated by JS -->
                    </div>
                    <div class="selected-staff" id="selectedStaff" style="display: none;">
                        <div class="staff-info">
                            <i class="ri-user-3-fill"></i>
                            <span class="staff-name"></span>
                            <button type="button" class="remove-btn" onclick="clearStaff()">
                                <i class="ri-close-line"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Date & Time --}}
                <div class="form-group">
                    <div class="datetime-container">
                        <div class="input-group">
                            <label for="appointmentDate">Select Date</label>
                            <input type="date" id="appointmentDate" required>
                        </div>
                        <div class="input-group">
                            <label>Available Times</label>
                            <div class="time-slots" id="timeSlots">
                                <!-- populated by JS -->
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="form-group">
                    <label for="notes">Notes (Optional)</label>
                    <textarea id="notes" placeholder="Any special notes or requests for this appointment..."></textarea>
                </div>

                {{-- Submit --}}
                <button type="submit" class="submit-btn" id="submitBtn" disabled>
                    <i class="ri-calendar-check-line"></i>
                    Create Appointment
                </button>
            </form>
        </div>
    </div>

    <script>
        // -- Data & state --
        const customers = @json($customers);
        const services = @json($services);
        const staffs = @json($staffs);
        const timeSlots = ['08:00','09:00','10:00','11:00','13:00','14:00','15:00','16:00','17:00'];

        let selectedCustomer = null;
        let selectedService = null;
        let selectedStaff = null;
        let selectedDate = null;
        let selectedTime = null;

        // -- Initialization --
        document.addEventListener('DOMContentLoaded', () => {
            populateServices();
            populateStaff();
            setupDateInput();
            setupCustomerSearch();
            document.getElementById('bookingForm').addEventListener('submit', handleSubmit);
        });

        // -- Customer search functionality --
        function setupCustomerSearch() {
            const searchInput = document.getElementById('customerSearch');
            const resultsDiv = document.getElementById('customerResults');
            
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                if (query.length < 2) {
                    resultsDiv.style.display = 'none';
                    return;
                }
                
                const filtered = customers.filter(c => 
                    c.name.toLowerCase().includes(query) || 
                    c.email.toLowerCase().includes(query)
                );
                
                if (filtered.length > 0) {
                    resultsDiv.innerHTML = filtered.map(c => `
                        <div class="customer-result" onclick="selectCustomer(${JSON.stringify(c).replace(/"/g, '&quot;')})">
                            <div class="customer-result-name">${c.name}</div>
                            <div class="customer-result-email">${c.email}</div>
                        </div>
                    `).join('');
                    resultsDiv.style.display = 'block';
                } else {
                    resultsDiv.innerHTML = '<div class="no-results">No customers found</div>';
                    resultsDiv.style.display = 'block';
                }
            });
            
            // Hide results when clicking outside
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.customer-search')) {
                    resultsDiv.style.display = 'none';
                }
            });
        }

        function selectCustomer(customer) {
            selectedCustomer = customer;
            document.getElementById('customerSearch').value = customer.name;
            document.getElementById('customerResults').style.display = 'none';
            
            const selectedDiv = document.getElementById('selectedCustomer');
            selectedDiv.querySelector('.customer-name').textContent = customer.name;
            selectedDiv.querySelector('.customer-email').textContent = customer.email;
            selectedDiv.style.display = 'block';
            
            validateForm();
        }

        function clearCustomer() {
            selectedCustomer = null;
            document.getElementById('customerSearch').value = '';
            document.getElementById('selectedCustomer').style.display = 'none';
            validateForm();
        }

        // -- Populate service cards --
        function populateServices() {
            const grid = document.getElementById('serviceGrid');
            grid.innerHTML = '';
            services.forEach(s => {
                const card = document.createElement('div');
                card.className = `service-card ${!s.is_available ? 'unavailable' : ''}`;
                card.innerHTML = `
                    <div class="service-name"><strong>${s.name}</strong></div>
                    <div class="service-category">${s.category}</div>
                    <div class="service-price">₱${s.price}</div>
                    <div class="service-duration">${s.duration} minutes</div>
                    ${!s.is_available ? '<div class="service-status">Unavailable</div>' : ''}
                `;
                if (s.is_available) {
                    card.addEventListener('click', () => toggleService(s, card));
                }
                grid.appendChild(card);
            });
        }

        function toggleService(service, card) {
            if (selectedService?.id === service.id) {
                selectedService = null;
                card.classList.remove('selected');
            } else {
                document.querySelectorAll('.service-card.selected')
                        .forEach(c => c.classList.remove('selected'));
                selectedService = service;
                card.classList.add('selected');
            }
            updateServiceSummary();
            validateForm();
        }

        function updateServiceSummary() {
            const summary = document.getElementById('selectedServicesSummary');
            const list = document.getElementById('summaryList');
            const dur = document.getElementById('totalDuration');
            const price = document.getElementById('totalPrice');

            if (!selectedService) {
                summary.style.display = 'none';
                return;
            }
            
            summary.style.display = 'block';
            list.innerHTML = `
                <div class="summary-item">
                    <div class="summary-service-info">
                        <div class="summary-service-name">${selectedService.name}</div>
                        <div class="summary-service-category">${selectedService.category}</div>
                        <div class="summary-service-duration">${selectedService.duration} minutes</div>
                    </div>
                    <div class="summary-service-price">₱${selectedService.price}</div>
                </div>
            `;
            dur.textContent = selectedService.duration;
            price.textContent = selectedService.price;
        }

        // -- Populate staff cards --
        function populateStaff() {
            const grid = document.getElementById('staffGrid');
            grid.innerHTML = '';
            staffs.forEach(s => {
                const card = document.createElement('div');
                card.className = 'staff-card';
                card.innerHTML = `
                    <div class="staff-avatar">
                        <i class="ri-user-3-fill"></i>
                    </div>
                    <div class="staff-name">${s.name}</div>
                    <div class="staff-role">Staff Member</div>
                `;
                card.addEventListener('click', () => toggleStaff(s, card));
                grid.appendChild(card);
            });
        }

        function toggleStaff(staff, card) {
            if (selectedStaff?.id === staff.id) {
                selectedStaff = null;
                card.classList.remove('selected');
                document.getElementById('selectedStaff').style.display = 'none';
            } else {
                document.querySelectorAll('.staff-card.selected')
                        .forEach(c => c.classList.remove('selected'));
                selectedStaff = staff;
                card.classList.add('selected');
                
                const selectedDiv = document.getElementById('selectedStaff');
                selectedDiv.querySelector('.staff-name').textContent = staff.name;
                selectedDiv.style.display = 'block';
            }
            validateForm();
        }

        function clearStaff() {
            selectedStaff = null;
            document.querySelectorAll('.staff-card.selected')
                    .forEach(c => c.classList.remove('selected'));
            document.getElementById('selectedStaff').style.display = 'none';
            validateForm();
        }

        // -- Date & time setup --
        function setupDateInput() {
            const dateEl = document.getElementById('appointmentDate');
            dateEl.min = new Date().toISOString().split('T')[0];
            dateEl.addEventListener('change', e => {
                selectedDate = e.target.value;
                selectedTime = null;
                populateTimeSlots();
                validateForm();
            });
        }

        function populateTimeSlots() {
            const container = document.getElementById('timeSlots');
            container.innerHTML = '';
            if (!selectedDate) return;
            
            timeSlots.forEach(t => {
                const slot = document.createElement('div');
                slot.className = 'time-slot';
                const [h, m] = t.split(':');
                const hour24 = parseInt(h), hour12 = hour24 % 12 || 12;
                const ampm = hour24 >= 12 ? 'PM' : 'AM';
                slot.textContent = `${hour12}:${m} ${ampm}`;
                slot.dataset.time = t;
                slot.addEventListener('click', () => {
                    document.querySelectorAll('.time-slot.selected')
                            .forEach(s => s.classList.remove('selected'));
                    slot.classList.add('selected');
                    selectedTime = t;
                    validateForm();
                });
                container.appendChild(slot);
            });
        }

        // -- Enable submit when ready --
        function validateForm() {
            const isValid = selectedCustomer && selectedService && selectedStaff && selectedDate && selectedTime;
            document.getElementById('submitBtn').disabled = !isValid;
        }

        // -- On form submit, fill hidden fields --
        function handleSubmit(e) {
            e.preventDefault();
            const notes = document.getElementById('notes').value || '';
            
            // Format time to "YYYY-MM-DD hh:mm A"
            const [h, m] = selectedTime.split(':');
            let hr12 = parseInt(h) % 12 || 12;
            const ampm = parseInt(h) >= 12 ? 'PM' : 'AM';
            const formatted = `${selectedDate} ${hr12.toString().padStart(2, '0')}:${m} ${ampm}`;

            document.getElementById('customer_id_input').value = selectedCustomer.id;
            document.getElementById('service_id_input').value = selectedService.id;
            document.getElementById('staff_id_input').value = selectedStaff.id;
            document.getElementById('start_time_input').value = formatted;
            document.getElementById('notes_input').value = notes;

            e.target.submit();
        }

        // -- Dropdown menu toggle --
        function toggleDropdown() {
            const dd = document.getElementById('user-dropdown');
            const arrow = document.getElementById('dropdown-arrow');
            dd.classList.toggle('show');
            arrow.style.transform = dd.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
        }
        
        document.addEventListener('click', e => {
            const menu = document.querySelector('.user-menu');
            if (!menu.contains(e.target)) {
                document.getElementById('user-dropdown').classList.remove('show');
                document.getElementById('dropdown-arrow').style.transform = 'rotate(0deg)';
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
</body>
</html>