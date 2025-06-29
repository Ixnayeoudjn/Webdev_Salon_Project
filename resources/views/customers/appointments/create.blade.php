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
            <div class="success-message" id="successMessage">
                <i class="ri-check-circle-fill"></i>
                Your appointment has been booked successfully! Redirecting to confirmation page...
            </div>

            <form id="bookingForm">
                <div class="form-group">
                    <label>Select Services <span class="service-count">(0 selected)</span></label>
                    <div class="service-grid" id="serviceGrid">
                        <!-- Services will be populated by JavaScript -->
                    </div>
                    <div class="selected-services-summary" id="selectedServicesSummary" style="display: none;">
                        <h4>Selected Services:</h4>
                        <div class="summary-list" id="summaryList"></div>
                        <div class="summary-total">
                            <strong>Total Duration: <span id="totalDuration">0</span> minutes</strong>
                            <strong>Total Price: ₱<span id="totalPrice">0</span></strong>
                        </div>
                    </div>
                    <input type="hidden" name="service_id" id="service_id" />
                </div>

                <div class="form-group">
                    <div class="datetime-container">
                        <div class="input-group">
                            <label for="appointmentDate">Select Date</label>
                            <input type="date" id="appointmentDate" required>
                        </div>
                        <div class="input-group">
                            <label>Available Times</label>
                            <div class="time-slots" id="timeSlots">
                                <!-- Time slots will be populated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Special Requests or Notes</label>
                    <textarea id="notes" placeholder="Any specific requests or preferences? (Optional)"></textarea>
                </div>
                <button type="submit" class="submit-btn" id="submitBtn" disabled>
                    Book Appointment
                </button>
            </form>
        </div>
    </div>

    <script>
        // Services data matching your seeder
        const services = [
            { id: 1, name: 'Massage', category: 'Massage', duration: 30, price: 350, is_available: true },
            { id: 2, name: 'Haircut', category: 'Haircut', duration: 40, price: 500, is_available: true },
            { id: 3, name: 'Permanent Wave', category: 'Treatments', duration: 120, price: 1300, is_available: true },
            { id: 4, name: 'Root Perm', category: 'Treatments', duration: 60, price: 800, is_available: true },
            { id: 5, name: 'Hair Relax', category: 'Treatments', duration: 120, price: 2000, is_available: true },
            { id: 6, name: 'Hair Rebond', category: 'Treatments', duration: 120, price: 4000, is_available: false },
            { id: 7, name: 'Hair Spa', category: 'Treatments', duration: 40, price: 700, is_available: true },
            { id: 8, name: 'Full Permanent', category: 'Color Vibrancy', duration: 120, price: 1800, is_available: true },
            { id: 9, name: 'Root Retouch', category: 'Color Vibrancy', duration: 120, price: 1400, is_available: true },
            { id: 10, name: 'Highlights', category: 'Color Vibrancy', duration: 120, price: 1800, is_available: true },
            { id: 11, name: 'Highlights w/ bleach', category: 'Color Vibrancy', duration: 120, price: 2300, is_available: true }
        ];

        // Available time slots (8 AM to 6 PM, excluding 12 PM)
        const timeSlots = [
            '08:00', '09:00', '10:00', '11:00',
            '13:00', '14:00', '15:00', '16:00', '17:00'
        ];

        let selectedServices = [];
        let selectedTime = null;
        let selectedDate = null;

        // Initialize the form
        function initializeForm() {
            populateServices();
            setupDateInput();
            setupFormValidation();
        }

        function populateServices() {
            const serviceGrid = document.getElementById('serviceGrid');
            serviceGrid.innerHTML = '';

            services.forEach(service => {
                const serviceCard = document.createElement('div');
                serviceCard.className = `service-card ${!service.is_available ? 'unavailable' : ''}`;
                serviceCard.innerHTML = `
                    <div class="service-name"><strong>${service.name}</strong></div>
                    <div class="service-category">${service.category}</div>
                    <div class="service-price">₱${service.price}</div>
                    <div class="service-duration">${service.duration} minutes</div>
                    ${!service.is_available ? '<div class="service-status">Unavailable</div>' : ''}
                `;

                if (service.is_available) {
                    serviceCard.addEventListener('click', () => toggleService(service, serviceCard));
                }

                serviceGrid.appendChild(serviceCard);
            });
        }

        function toggleService(service, element) {
            const isSelected = selectedServices.find(s => s.id === service.id);
            
            if (isSelected) {
                selectedServices = selectedServices.filter(s => s.id !== service.id);
                element.classList.remove('selected');
            } else {
                selectedServices.push(service);
                element.classList.add('selected');
            }
            
            updateServicesSummary();
            updateHiddenInput();
            validateForm();
        }

        function updateServicesSummary() {
            const serviceCount = document.querySelector('.service-count');
            const summary = document.getElementById('selectedServicesSummary');
            const summaryList = document.getElementById('summaryList');
            const totalDuration = document.getElementById('totalDuration');
            const totalPrice = document.getElementById('totalPrice');
            
            serviceCount.textContent = `(${selectedServices.length} selected)`;
            
            if (selectedServices.length === 0) {
                summary.style.display = 'none';
                return;
            }
            
            summary.style.display = 'block';
            summaryList.innerHTML = '';
            let totalDurationValue = 0;
            let totalPriceValue = 0;
            
            selectedServices.forEach(service => {
                totalDurationValue += service.duration;
                totalPriceValue += service.price;
                
                const summaryItem = document.createElement('div');
                summaryItem.className = 'summary-item';
                summaryItem.innerHTML = `
                    <div class="summary-service-info">
                        <div class="summary-service-name">${service.name}</div>
                        <div class="summary-service-duration">${service.duration} minutes</div>
                    </div>
                    <div class="summary-service-price">₱${service.price}</div>
                    <button class="remove-service-btn" onclick="removeService(${service.id})">Remove</button>
                `;
                summaryList.appendChild(summaryItem);
            });
            
            totalDuration.textContent = totalDurationValue;
            totalPrice.textContent = totalPriceValue;
        }

        function removeService(serviceId) {
            selectedServices = selectedServices.filter(s => s.id !== serviceId);
            
            document.querySelectorAll('.service-card').forEach((card, index) => {
                if (services[index].id === serviceId) {
                    card.classList.remove('selected');
                }
            });
            
            updateServicesSummary();
            updateHiddenInput();
            validateForm();
        }

        function updateHiddenInput() {
            const serviceIds = selectedServices.map(service => service.id).join(',');
            document.getElementById('service_id').value = serviceIds;
        }

        function setupDateInput() {
            const dateInput = document.getElementById('appointmentDate');
            const today = new Date().toISOString().split('T')[0];
            dateInput.min = today;

            dateInput.addEventListener('change', (e) => {
                selectedDate = e.target.value;
                selectedTime = null;
                populateTimeSlots();
                validateForm();
            });
        }

        function populateTimeSlots() {
            const timeSlotsContainer = document.getElementById('timeSlots');
            timeSlotsContainer.innerHTML = '';

            if (!selectedDate) return;

            timeSlots.forEach(time => {
                const timeSlot = document.createElement('div');
                timeSlot.className = 'time-slot';
                
                // Convert 24-hour format to 12-hour format for display
                const [hours, minutes] = time.split(':');
                const hour24 = parseInt(hours);
                const hour12 = hour24 === 0 ? 12 : hour24 > 12 ? hour24 - 12 : hour24;
                const ampm = hour24 >= 12 ? 'PM' : 'AM';
                const displayTime = `${hour12}:${minutes} ${ampm}`;
                
                timeSlot.textContent = displayTime;
                timeSlot.dataset.time = time; // Store 24-hour format for processing
                
                timeSlot.addEventListener('click', () => selectTime(time, timeSlot));
                timeSlotsContainer.appendChild(timeSlot);
            });
        }

        function selectTime(time, element) {
            document.querySelectorAll('.time-slot').forEach(slot => {
                slot.classList.remove('selected');
            });

            element.classList.add('selected');
            selectedTime = time;
            validateForm();
        }

        function validateForm() {
            const submitBtn = document.getElementById('submitBtn');
            const isValid = selectedServices.length > 0 && selectedTime && selectedDate;
            submitBtn.disabled = !isValid;
        }

        function setupFormValidation() {
            const form = document.getElementById('bookingForm');
            form.addEventListener('submit', handleSubmit);
        }

        async function handleSubmit(e) {
            e.preventDefault();
            
            const notesInput = document.getElementById('notes');
            const submitBtn = document.getElementById('submitBtn');
            
            if (selectedServices.length === 0 || !selectedTime || !selectedDate) {
                alert('Please fill in all required fields.');
                return;
            }
            
            // Convert 24-hour time to 12-hour format with AM/PM (h:i A format)
            const [hours, minutes] = selectedTime.split(':');
            const hour24 = parseInt(hours);
            
            // Convert to 12-hour format (with leading zeros for both hour and minutes)
            let hour12 = hour24 % 12;
            if (hour12 === 0) hour12 = 12;  // Handle midnight (0) and noon (12)
            
            const ampm = hour24 >= 12 ? 'PM' : 'AM';
            
            // Format: h:i A but Laravel seems to expect leading zeros for hour too
            const formattedTime = `${hour12.toString().padStart(2, '0')}:${minutes.padStart(2, '0')} ${ampm}`;
            const appointmentDateTime = `${selectedDate} ${formattedTime}`;
            
            // Debug: Let's see what we're sending
            console.log('Selected Date:', selectedDate);
            console.log('Selected Time (24h):', selectedTime);
            console.log('Formatted Time (12h):', formattedTime);
            console.log('Final DateTime:', appointmentDateTime);
            console.log('Selected Services:', selectedServices);
            
            const formData = {
                service_id: selectedServices.map(service => service.id),
                start_time: appointmentDateTime,
                notes: notesInput.value || '',
                _token: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            };
            
            console.log('Complete form data being sent:', formData);
            
            submitBtn.innerHTML = '<i class="ri-loader-4-line"></i> Booking...';
            submitBtn.disabled = true;
            
            try {
                const response = await fetch('/customer/appointments', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': formData._token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                
                if (response.ok) {
                    console.log('Success response:', result);
                    
                    // Show success message with better styling
                    const successMessage = document.getElementById('successMessage');
                    successMessage.style.display = 'block';
                    successMessage.scrollIntoView({ behavior: 'smooth' });
                    
                    // Hide the form to prevent duplicate submissions
                    document.getElementById('bookingForm').style.display = 'none';
                    
                    // Determine appointment ID and redirect
                    const appointmentId = result.appointment_id || result.id || result.data?.id;
                    
                    if (appointmentId) {
                        console.log('Redirecting to confirm page with ID:', appointmentId);
                        setTimeout(() => {
                            window.location.href = `/customer/appointments/confirm/${appointmentId}`;
                        }, 1500); // Reduced timeout for faster redirect
                    } else {
                        console.log('No appointment ID found, redirecting to appointments list');
                        setTimeout(() => {
                            window.location.href = '/customer/appointments';
                        }, 1500);
                    }
                    
                } else {
                    console.error('HTTP Error:', response.status, response.statusText);
                    console.error('Error response:', result);
                    
                    // Show detailed error information
                    let errorMessage = 'Failed to book appointment:\n\n';
                    
                    if (response.status === 422 && result.errors) {
                        // Validation errors - show detailed field errors
                        errorMessage += 'Validation Errors:\n';
                        Object.keys(result.errors).forEach(field => {
                            errorMessage += `• ${field}: ${result.errors[field].join(', ')}\n`;
                        });
                        
                        // Show what data was sent for debugging
                        errorMessage += '\nData sent:\n';
                        errorMessage += `• Date: ${selectedDate}\n`;
                        errorMessage += `• Time: ${selectedTime} → ${appointmentDateTime}\n`;
                        errorMessage += `• Services: ${selectedServices.map(s => s.name).join(', ')}\n`;
                        
                    } else if (response.status === 419) {
                        errorMessage = 'Session expired. Please refresh the page and try again.';
                        setTimeout(() => window.location.reload(), 2000);
                    } else if (response.status === 401) {
                        errorMessage = 'You are not authorized. Please log in and try again.';
                        setTimeout(() => window.location.href = '/login', 2000);
                    } else {
                        errorMessage += result.message || `HTTP ${response.status}: ${response.statusText}`;
                    }
                    
                    alert(errorMessage);
                    
                    // Reset button state
                    submitBtn.innerHTML = 'Book Appointment';
                    submitBtn.disabled = false;
                }
                
            } catch (error) {
                console.error('Booking error:', error);
                alert('Network error. Please check your connection and try again.');
                
                // Reset button state
                submitBtn.innerHTML = 'Book Appointment';
                submitBtn.disabled = false;
            }
        }

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
            
            if (userMenu && !userMenu.contains(event.target)) {
                dropdown?.classList.remove('show');
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        });

        // Initialize the form when the page loads
        document.addEventListener('DOMContentLoaded', initializeForm);
    </script>
</body>
</html>