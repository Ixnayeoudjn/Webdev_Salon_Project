<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CopyCut Salon</title>
    <link rel="stylesheet" href="{{ asset('css/create.css') }}">
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
        <a href="{{ route('customer.appointments.index') }}" class="back-btn">
            ← Back to Appointments
        </a>

        <div class="header"></div>
        
        <div class="form-container">
            <form id="bookingForm" method="POST" action="{{ route('customer.appointments.store') }}">
                @csrf

                {{-- Hidden fields for real submission --}}
                <input type="hidden" name="service_id" id="service_id">
                <input type="hidden" name="start_time" id="start_time_input">
                <input type="hidden" name="notes" id="notes_input">

                {{-- Service selection --}}
                <div class="form-group">
                    <label>Select Services</label>
                    <div class="service-grid" id="serviceGrid">
                        <!-- populated by JS -->
                    </div>
                    <div class="selected-services-summary" id="selectedServicesSummary" style="display: none;">
                        <h4>Selected Services:</h4>
                        <div class="summary-list" id="summaryList"></div>
                        <div class="summary-total">
                            <strong>Total Duration: <span id="totalDuration">0</span> minutes</strong>
                            <strong>Total Price: ₱<span id="totalPrice">0</span></strong>
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
                    <label for="notes">Special Requests or Notes</label>
                    <textarea id="notes" placeholder="Any specific requests or preferences? (Optional)"></textarea>
                </div>

                {{-- Submit --}}
                <button type="submit" class="submit-btn" id="submitBtn" disabled>
                    Book Appointment
                </button>
            </form>
        </div>
    </div>

    <script>
        // -- Data & state --
        const services = @json($services);
        const timeSlots = ['08:00','09:00','10:00','11:00','13:00','14:00','15:00','16:00','17:00'];

        let selectedService = null;
        let selectedDate = null;
        let selectedTime = null;

        // -- Initialization --
        document.addEventListener('DOMContentLoaded', () => {
            populateServices();
            setupDateInput();
            document.getElementById('bookingForm').addEventListener('submit', handleSubmit);
        });

        // -- Populate service cards --
        function populateServices() {
            const grid = document.getElementById('serviceGrid');
            grid.innerHTML = '';
            services.forEach(s => {
                const card = document.createElement('div');
                card.className = `service-card ${!s.is_available?'unavailable':''}`;
                card.innerHTML = `
                    <div class="service-name"><strong>${s.name}</strong></div>
                    <div class="service-category">${s.category}</div>
                    <div class="service-price">₱${s.price}</div>
                    <div class="service-duration">${s.duration} minutes</div>
                    ${!s.is_available?'<div class="service-status">Unavailable</div>':''}
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
                        .forEach(c=>c.classList.remove('selected'));
                selectedService = service;
                card.classList.add('selected');
            }
            updateServiceSummary();
            validateForm();
        }

        function updateServiceSummary() {
            const summary = document.getElementById('selectedServicesSummary');
            const list   = document.getElementById('summaryList');
            const dur    = document.getElementById('totalDuration');
            const price  = document.getElementById('totalPrice');

            count.textContent = `(${selectedService?1:0} selected)`;
            if (!selectedService) {
                summary.style.display = 'none';
                return;
            }
            summary.style.display = 'block';
            list.innerHTML = `
                <div class="summary-item">
                  <div class="summary-service-info">
                    <div class="summary-service-name">${selectedService.name}</div>
                    <div class="summary-service-duration">${selectedService.duration} minutes</div>
                  </div>
                  <div class="summary-service-price">₱${selectedService.price}</div>
                  <button type="button" class="remove-service-btn" onclick="toggleService(selectedService, this.parentNode.parentNode)">Remove</button>
                </div>`;
            dur.textContent   = selectedService.duration;
            price.textContent = selectedService.price;
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
                const [h,m] = t.split(':');
                const hour24 = parseInt(h), hour12 = hour24%12||12;
                const ampm = hour24>=12?'PM':'AM';
                slot.textContent = `${hour12}:${m} ${ampm}`;
                slot.dataset.time = t;
                slot.addEventListener('click', () => {
                    document.querySelectorAll('.time-slot.selected')
                            .forEach(s=>s.classList.remove('selected'));
                    slot.classList.add('selected');
                    selectedTime = t;
                    validateForm();
                });
                container.appendChild(slot);
            });
        }

        // -- Enable submit when ready --
        function validateForm() {
            document.getElementById('submitBtn').disabled =
                !(selectedService && selectedDate && selectedTime);
        }

        // -- On form submit, fill hidden and let Laravel handle redirect to confirm view --
        function handleSubmit(e) {
            e.preventDefault();
            const notes = document.getElementById('notes').value || '';
            // format to “YYYY-MM-DD hh:mm A”
            const [h,m] = selectedTime.split(':');
            let hr12 = parseInt(h)%12||12;
            const ampm = parseInt(h)>=12?'PM':'AM';
            const formatted = `${selectedDate} ${hr12.toString().padStart(2,'0')}:${m} ${ampm}`;

            document.getElementById('service_id').value       = selectedService.id;
            document.getElementById('start_time_input').value = formatted;
            document.getElementById('notes_input').value      = notes;

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
</body>
</html>
