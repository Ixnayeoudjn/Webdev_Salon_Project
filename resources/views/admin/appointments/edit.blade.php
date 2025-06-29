<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CopyCut Salon</title>
    <link rel="stylesheet" href="{{ asset('css/edit.css') }}">
    <link href='https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700' rel='stylesheet'>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.5.0/fonts/remixicon.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="icon" href="{{ asset('icon.ico') }}">
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
            <i class="ri-arrow-left-line"></i>
            Back to Appointments
        </a>

        <div class="header"></div>

        <div class="form-container">
            @if(session('success'))
                <div class="alert alert-success" id="success-alert">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="appointment-info">
                <h3><i class="ri-calendar-check-line"></i> Current Appointment Details</h3>
                <p><strong>Appointment ID:</strong> #{{ str_pad($appointment->id, 4, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Customer:</strong> {{ $appointment->customer->name ?? 'N/A' }}</p>
                <p><strong>Service:</strong> {{ $appointment->service->name ?? 'N/A' }}</p>
                <p><strong>Created:</strong> {{ $appointment->created_at ? $appointment->created_at->format('F j, Y g:i A') : 'N/A' }}</p>
                <p><strong>Current Status:</strong> 
                    <span class="status-current status-{{ strtolower(str_replace(' ', '-', $appointment->status)) }}">
                        {{ $appointment->status }}
                    </span>
                </p>
            </div>

            <form id="appointment-form" action="{{ route('admin.appointments.update', $appointment->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-grid">
                    <div class="form-group">
                        <label for="customer_id">Customer <span class="required">*</span></label>
                        <select id="customer_id" name="customer_id" class="form-select" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $appointment->customer_id == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="staff_id">Staff Member <span class="required">*</span></label>
                        <select id="staff_id" name="staff_id" class="form-select" required>
                            <option value="">Select Staff</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}" {{ $appointment->staff_id == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="service_id">Service <span class="required">*</span></label>
                        <select id="service_id" name="service_id" class="form-select" required>
                            <option value="">Select Service</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ $appointment->service_id == $service->id ? 'selected' : '' }}>
                                    {{ $service->name }} - ₱{{ number_format($service->price, 0) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <div class="datetime-container">
                            <div class="input-group">
                                <label for="appointmentDate">Select Date <span class="required">*</span></label>
                                <input type="date" id="appointmentDate" required>
                                <div class="error-message" id="date-error" style="display: none;">
                                    <i class="ri-error-warning-line"></i>
                                    Please select a date
                                </div>
                            </div>
                            <div class="input-group">
                                <label>Available Times <span class="required">*</span></label>
                                <div class="time-slots" id="timeSlots">
                                    <!-- populated by JS -->
                                </div>
                                <div class="error-message" id="time-error" style="display: none;">
                                    <i class="ri-error-warning-line"></i>
                                    Please select a time slot
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="notes">Special Notes</label>
                        <textarea id="notes" name="notes" class="form-control" 
                                  placeholder="Add any special requests or notes about the appointment...">{{ old('notes', $appointment->notes) }}</textarea>
                    </div>

                    <div class="form-group full-width">
                        <label>Appointment Status <span class="required">*</span></label>
                        <div class="status-badges">
                            @foreach(['Pending', 'Confirmed', 'Started', 'In Progress', 'Completed', 'Cancelled'] as $status)
                                <div class="status-badge">
                                    <input type="radio" id="status_{{ strtolower(str_replace(' ', '_', $status)) }}" 
                                           name="status" value="{{ $status }}" 
                                           {{ $appointment->status == $status ? 'checked' : '' }}>
                                    <label for="status_{{ strtolower(str_replace(' ', '_', $status)) }}">{{ $status }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="submit" class="btn btn-primary" id="submit-btn">
                        <i class="ri-save-line"></i>
                        Update Appointment
                    </button>
                    <a href="{{ route('admin.appointments.index') }}" class="btn btn-secondary">
                        <i class="ri-close-line"></i>
                        Cancel Changes
                    </a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .required {
            color: #e74c3c;
            font-weight: bold;
        }
        
        .field-required {
            border: 2px solid #e74c3c !important;
            border-radius: 4px;
            padding: 5px;
            background-color: #fdf2f2;
        }
        
        .field-required label {
            color: #e74c3c !important;
        }
        
        .error-message {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .time-slots.field-required::before {
            content: "Please select a time slot";
            color: #e74c3c;
            font-size: 14px;
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            background-color: #95a5a6;
        }
        
        .time-slot {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .time-slot:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .time-slot.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 2px solid #667eea;
        }
        
        .form-validation-summary {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            display: none;
        }
        
        .form-validation-summary.show {
            display: block;
        }
        
        .form-validation-summary ul {
            margin: 0;
            padding-left: 20px;
        }
    </style>

    <script>
        const timeSlots = ['08:00','09:00','10:00','11:00','13:00','14:00','15:00','16:00','17:00'];

        // Declare variables
        let selectedDate = null;
        let selectedTime = null;

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
            
            if (!userMenu.contains(event.target)) {
                dropdown.classList.remove('show');
                document.getElementById('dropdown-arrow').style.transform = 'rotate(0deg)';
            }
        });

        // Enhanced validation function
        function validateForm() {
            const submitBtn = document.getElementById('submit-btn');
            const dateContainer = document.querySelector('.datetime-container');
            const dateError = document.getElementById('date-error');
            const timeError = document.getElementById('time-error');
            
            let isValid = true;
            let errors = [];
            
            // Validate date selection
            if (!selectedDate) {
                isValid = false;
                errors.push('Please select a date');
                if (dateContainer) {
                    dateContainer.classList.add('field-required');
                }
                if (dateError) {
                    dateError.style.display = 'block';
                }
            } else {
                if (dateContainer) {
                    dateContainer.classList.remove('field-required');
                }
                if (dateError) {
                    dateError.style.display = 'none';
                }
            }
            
            // Validate time selection
            if (selectedDate && !selectedTime) {
                isValid = false;
                errors.push('Please select a time slot');
                const timeSlotsContainer = document.getElementById('timeSlots');
                if (timeSlotsContainer) {
                    timeSlotsContainer.classList.add('field-required');
                }
                if (timeError) {
                    timeError.style.display = 'block';
                }
            } else {
                const timeSlotsContainer = document.getElementById('timeSlots');
                if (timeSlotsContainer) {
                    timeSlotsContainer.classList.remove('field-required');
                }
                if (timeError) {
                    timeError.style.display = 'none';
                }
            }
            
            // Update submit button state
            if (submitBtn) {
                submitBtn.disabled = !isValid;
            }
            
            return isValid;
        }

        // Date & time setup
        function setupDateInput() {
            const dateEl = document.getElementById('appointmentDate');
            dateEl.min = new Date().toISOString().split('T')[0];
            
            // Set current appointment date if editing
            let currentDate = '';
            let currentTime = '';
            
            @if(isset($appointment) && $appointment->appointment_date)
                currentDate = '{{ $appointment->appointment_date->format("Y-m-d") }}';
            @elseif(isset($appointment) && $appointment->date)
                currentDate = '{{ \Carbon\Carbon::parse($appointment->date)->format("Y-m-d") }}';
            @elseif(isset($appointment) && $appointment->scheduled_at)
                currentDate = '{{ \Carbon\Carbon::parse($appointment->scheduled_at)->format("Y-m-d") }}';
            @endif
            
            @if(isset($appointment) && $appointment->appointment_time)
                currentTime = '{{ $appointment->appointment_time }}';
            @elseif(isset($appointment) && $appointment->start_time)
                currentTime = '{{ $appointment->start_time }}';
            @elseif(isset($appointment) && $appointment->time)
                currentTime = '{{ $appointment->time }}';
            @elseif(isset($appointment) && $appointment->scheduled_at)
                currentTime = '{{ \Carbon\Carbon::parse($appointment->scheduled_at)->format("H:i") }}';
            @endif
            
            console.log('Current appointment data:', { date: currentDate, time: currentTime });
            
            if (currentDate) {
                dateEl.value = currentDate;
                selectedDate = currentDate;
                populateTimeSlots();
                
                if (currentTime) {
                    selectedTime = currentTime;
                    // Mark the current time slot as selected after a short delay
                    setTimeout(() => {
                        const timeSlot = document.querySelector(`[data-time="${currentTime}"]`);
                        if (timeSlot) {
                            timeSlot.classList.add('selected');
                            console.log('Selected time slot:', currentTime);
                        } else {
                            console.log('Time slot not found for:', currentTime);
                        }
                    }, 200);
                }
            }
            
            dateEl.addEventListener('change', e => {
                selectedDate = e.target.value;
                selectedTime = null;
                // Clear any previously selected time slots
                document.querySelectorAll('.time-slot.selected')
                        .forEach(s => s.classList.remove('selected'));
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
                    
                    // Clear time error when time is selected
                    const timeError = document.getElementById('time-error');
                    if (timeError) {
                        timeError.style.display = 'none';
                    }
                    const timeSlotsContainer = document.getElementById('timeSlots');
                    if (timeSlotsContainer) {
                        timeSlotsContainer.classList.remove('field-required');
                    }
                });
                container.appendChild(slot);
            });
        }

        // Add form validation summary
        function showValidationSummary(errors) {
            let summaryEl = document.querySelector('.form-validation-summary');
            if (!summaryEl) {
                summaryEl = document.createElement('div');
                summaryEl.className = 'form-validation-summary';
                const formContainer = document.querySelector('.form-container');
                formContainer.insertBefore(summaryEl, document.getElementById('appointment-form'));
            }
            
            if (errors.length > 0) {
                summaryEl.innerHTML = `
                    <h4><i class="ri-error-warning-line"></i> Please fix the following errors:</h4>
                    <ul>
                        ${errors.map(error => `<li>${error}</li>`).join('')}
                    </ul>
                `;
                summaryEl.classList.add('show');
                summaryEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                summaryEl.classList.remove('show');
            }
        }

        // Initialize everything when the page loads
        document.addEventListener('DOMContentLoaded', function() {
            setupDateInput();
            validateForm();
            
            // Enhanced form submission handler
            const form = document.getElementById('appointment-form');
            form.addEventListener('submit', function(e) {
                console.log('Form submission attempt:', { date: selectedDate, time: selectedTime });
                
                let errors = [];
                
                // Validate required fields
                if (!selectedDate) {
                    errors.push('Please select an appointment date');
                }
                
                if (!selectedTime) {
                    errors.push('Please select an appointment time');
                }
                
                // Check other required fields
                const customerSelect = document.getElementById('customer_id');
                const staffSelect = document.getElementById('staff_id');
                const serviceSelect = document.getElementById('service_id');
                const statusRadios = document.querySelectorAll('input[name="status"]:checked');
                
                if (!customerSelect.value) {
                    errors.push('Please select a customer');
                }
                
                if (!staffSelect.value) {
                    errors.push('Please select a staff member');
                }
                
                if (!serviceSelect.value) {
                    errors.push('Please select a service');
                }
                
                if (statusRadios.length === 0) {
                    errors.push('Please select an appointment status');
                }
                
                if (errors.length > 0) {
                    e.preventDefault();
                    showValidationSummary(errors);
                    validateForm();
                    return false;
                }
                
                // Remove any existing hidden inputs to avoid duplicates
                const existingInputs = form.querySelectorAll('input[type="hidden"][name^="appointment_"], input[type="hidden"][name="start_time"], input[type="hidden"][name="end_time"], input[type="hidden"][name="date"], input[type="hidden"][name="scheduled_at"]');
                existingInputs.forEach(input => input.remove());
                
                // Create the properly formatted datetime for start_time field
                const formattedDateTime = selectedDate + ' ' + selectedTime;
                
                // Create comprehensive hidden inputs for different possible backend expectations
                const hiddenInputs = [
                    { name: 'appointment_date', value: selectedDate },
                    { name: 'appointment_time', value: selectedTime },
                    { name: 'start_time', value: formattedDateTime }, // Y-m-d H:i format
                    { name: 'date', value: selectedDate },
                    { name: 'scheduled_at', value: formattedDateTime },
                    { name: 'end_time', value: formattedDateTime } // In case backend expects this too
                ];
                
                hiddenInputs.forEach(({ name, value }) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    form.appendChild(input);
                });
                
                console.log('Start time formatted as:', formattedDateTime);
                
                console.log('Form data prepared for submission');
                console.log('Date:', selectedDate);
                console.log('Time:', selectedTime);
                console.log('Combined DateTime (start_time):', formattedDateTime);
                
                // Debug: Log all form data being sent
                const formData = new FormData(form);
                console.log('All form data being submitted:');
                for (let [key, value] of formData.entries()) {
                    console.log(`${key}: ${value}`);
                }
                
                showValidationSummary([]); // Clear any error messages
            });
            
            // Add real-time validation for select fields
            const requiredSelects = ['customer_id', 'staff_id', 'service_id'];
            requiredSelects.forEach(id => {
                const select = document.getElementById(id);
                if (select) {
                    select.addEventListener('change', validateForm);
                }
            });
            
            // Add validation for status radio buttons
            const statusRadios = document.querySelectorAll('input[name="status"]');
            statusRadios.forEach(radio => {
                radio.addEventListener('change', validateForm);
            });
        });

        // Auto-hide success alerts
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('success-alert');
            if (successAlert) {
                setTimeout(() => {
                    successAlert.style.opacity = '0';
                    setTimeout(() => {
                        successAlert.remove();
                    }, 300);
                }, 5000);
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