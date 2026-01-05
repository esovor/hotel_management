// Hotel Management System JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initSearch();
    initForms();
    initDatePickers();
    initModals();
    initBookingCalculator();
    initToggleGuest();
});

// Search functionality
function initSearch() {
    const searchInputs = document.querySelectorAll('.search-box input');
    searchInputs.forEach(input => {
        input.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const table = this.closest('.table-container').querySelector('table');
            if (table) {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }
        });
    });
}

// Form validation
function initForms() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
                showAlert('Please fill all required fields correctly', 'error');
            }
        });
    });

    // Phone number formatting
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 0) {
                value = value.match(new RegExp('.{1,3}', 'g')).join(' ');
            }
            e.target.value = value;
        });
    });

    // Currency formatting
    const currencyInputs = document.querySelectorAll('input[data-currency]');
    currencyInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const value = parseFloat(this.value.replace(/[^0-9.-]+/g, ''));
            if (!isNaN(value)) {
                this.value = 'GH₵ ' + value.toFixed(2);
            }
        });

        input.addEventListener('focus', function() {
            this.value = this.value.replace(/[^0-9.-]+/g, '');
        });
    });
}

function validateForm(form) {
    let isValid = true;
    const requiredInputs = form.querySelectorAll('input[required], select[required], textarea[required]');

    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            showInputError(input, 'This field is required');
            isValid = false;
        } else {
            clearInputError(input);
        }

        // Email validation
        if (input.type === 'email' && input.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(input.value)) {
                showInputError(input, 'Please enter a valid email address');
                isValid = false;
            }
        }

        // Date validation
        if (input.type === 'date' && input.value) {
            const today = new Date().toISOString().split('T')[0];
            if (input.name === 'check_in' && input.value < today) {
                showInputError(input, 'Check-in date cannot be in the past');
                isValid = false;
            }

            if (input.name === 'check_out' && input.value <= today) {
                showInputError(input, 'Check-out date must be after today');
                isValid = false;
            }

            const checkIn = form.querySelector('input[name="check_in"]');
            const checkOut = form.querySelector('input[name="check_out"]');
            if (checkIn && checkOut && checkIn.value && checkOut.value) {
                if (new Date(checkOut.value) <= new Date(checkIn.value)) {
                    showInputError(checkOut, 'Check-out date must be after check-in date');
                    isValid = false;
                }
            }
        }

        // Number validation
        if (input.type === 'number' && input.min) {
            if (parseFloat(input.value) < parseFloat(input.min)) {
                showInputError(input, `Minimum value is ${input.min}`);
                isValid = false;
            }
        }
    });

    return isValid;
}

function showInputError(input, message) {
    clearInputError(input);

    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.style.color = '#e74c3c';
    errorDiv.style.fontSize = '0.85rem';
    errorDiv.style.marginTop = '0.25rem';
    errorDiv.textContent = message;

    input.parentNode.appendChild(errorDiv);
    input.style.borderColor = '#e74c3c';
}

function clearInputError(input) {
    const errorDiv = input.parentNode.querySelector('.error-message');
    if (errorDiv) {
        errorDiv.remove();
    }
    input.style.borderColor = '#ddd';
}

// Date pickers
function initDatePickers() {
    const today = new Date().toISOString().split('T')[0];
    const tomorrow = new Date(Date.now() + 86400000).toISOString().split('T')[0];

    const checkInInputs = document.querySelectorAll('input[name="check_in"]');
    checkInInputs.forEach(input => {
        input.min = today;
        input.value = today;
    });

    const checkOutInputs = document.querySelectorAll('input[name="check_out"]');
    checkOutInputs.forEach(input => {
        input.min = tomorrow;
        input.value = tomorrow;
    });
}

// Modals
function initModals() {
    // Delete confirmation
    const deleteButtons = document.querySelectorAll('[data-delete]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const item = this.getAttribute('data-delete');
            const url = this.getAttribute('href') || this.getAttribute('data-url');
            if (confirm(`Are you sure you want to delete this ${item}? This action cannot be undone.`)) {
                window.location.href = url;
            }
        });
    });
}

// Booking calculator
function initBookingCalculator() {
    const checkInInput = document.querySelector('input[name="check_in"]');
    const checkOutInput = document.querySelector('input[name="check_out"]');
    const roomSelect = document.querySelector('select[name="room_id"]');

    if (checkInInput && checkOutInput && roomSelect) {
        function updateBookingSummary() {
            const checkIn = checkInInput.value;
            const checkOut = checkOutInput.value;
            const selectedOption = roomSelect.selectedOptions[0];

            if (checkIn && checkOut && selectedOption && selectedOption.value) {
                const roomPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                const nights = calculateNights(checkIn, checkOut);
                const total = roomPrice * nights;

                // Update summary if exists
                const summaryTotal = document.getElementById('summaryTotal');
                const summaryNights = document.getElementById('summaryNights');
                const summaryRoomPrice = document.getElementById('summaryRoomPrice');

                if (summaryTotal) summaryTotal.textContent = 'GH₵ ' + total.toFixed(2);
                if (summaryNights) summaryNights.textContent = nights;
                if (summaryRoomPrice) summaryRoomPrice.textContent = 'GH₵ ' + roomPrice.toFixed(2);

                // Update booking amount field if exists
                const amountField = document.getElementById('booking_amount');
                if (amountField) {
                    amountField.value = total.toFixed(2);
                }
            }
        }

        checkInInput.addEventListener('change', updateBookingSummary);
        checkOutInput.addEventListener('change', updateBookingSummary);
        roomSelect.addEventListener('change', updateBookingSummary);

        // Initial calculation
        updateBookingSummary();
    }
}

function calculateNights(checkIn, checkOut) {
    const oneDay = 24 * 60 * 60 * 1000;
    const start = new Date(checkIn);
    const end = new Date(checkOut);
    return Math.round(Math.abs((end - start) / oneDay));
}

// Toggle guest type (new/existing)
function initToggleGuest() {
    const guestSelector = document.getElementById('guest_selector');
    if (guestSelector) {
        guestSelector.addEventListener('change', function() {
            const isNewGuest = this.value === 'new';
            const existingSection = document.getElementById('existingGuestSection');
            const newSection = document.getElementById('newGuestSection');

            if (existingSection) existingSection.style.display = isNewGuest ? 'none' : 'block';
            if (newSection) newSection.style.display = isNewGuest ? 'block' : 'none';

            // Toggle required fields
            const existingFields = document.querySelectorAll('#existingGuestSection [required]');
            const newFields = document.querySelectorAll('#newGuestSection [required]');

            existingFields.forEach(field => field.required = !isNewGuest);
            newFields.forEach(field => field.required = isNewGuest);
        });
    }
}

// Load available rooms
function loadAvailableRooms(checkIn, checkOut, roomTypeId = null) {
    const url = new URL('get_available_rooms.php', window.location.href);
    url.searchParams.set('check_in', checkIn);
    url.searchParams.set('check_out', checkOut);
    if (roomTypeId) {
        url.searchParams.set('room_type', roomTypeId);
    }

    return fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            return data;
        });
}

// Alert system
function showAlert(message, type = 'success') {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.custom-alert');
    existingAlerts.forEach(alert => alert.remove());

    const alertDiv = document.createElement('div');
    alertDiv.className = `custom-alert alert alert-${type}`;
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
        <button class="alert-close">&times;</button>
    `;

    document.body.appendChild(alertDiv);

    // Position at top
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '20px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    alertDiv.style.maxWidth = '400px';
    alertDiv.style.animation = 'slideIn 0.3s ease';

    // Close button
    alertDiv.querySelector('.alert-close').addEventListener('click', () => {
        alertDiv.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => alertDiv.remove(), 300);
    });

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => alertDiv.remove(), 300);
        }
    }, 5000);
}

// Add animation styles
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .alert-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: inherit;
        opacity: 0.7;
        margin-left: auto;
    }
    
    .alert-close:hover {
        opacity: 1;
    }
    
    .custom-alert {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .error-message {
        color: #e74c3c;
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }
`;
document.head.appendChild(style);

// Export booking as PDF
function exportBookingToPDF(bookingId) {
    window.open(`export_pdf.php?id=${bookingId}`, '_blank');
}

// Print booking
function printBooking(bookingId) {
    const printWindow = window.open(`bookings_show.php?id=${bookingId}&print=1`, '_blank');
    printWindow.onload = function() {
        printWindow.print();
    };
}

// Check room availability
async function checkRoomAvailability() {
    const checkIn = document.getElementById('check_in')?.value;
    const checkOut = document.getElementById('check_out')?.value;
    const roomType = document.getElementById('room_type_filter')?.value;

    if (!checkIn || !checkOut) {
        showAlert('Please select check-in and check-out dates first', 'error');
        return;
    }

    try {
        const rooms = await loadAvailableRooms(checkIn, checkOut, roomType || null);
        const roomSelect = document.getElementById('room_id');

        if (roomSelect) {
            roomSelect.innerHTML = '<option value="">Select a room</option>';
            rooms.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = `Room ${room.room_number} - ${room.type_name} (Floor ${room.floor || 'N/A'})`;
                option.setAttribute('data-price', room.price_per_night);
                roomSelect.appendChild(option);
            });

            if (rooms.length === 0) {
                showAlert('No rooms available for selected dates', 'warning');
            } else {
                showAlert(`Found ${rooms.length} available room(s)`, 'success');
            }
        }
    } catch (error) {
        showAlert(error.message, 'error');
    }
}

// Initialize room filter
const roomTypeFilter = document.getElementById('room_type_filter');
const checkAvailabilityBtn = document.getElementById('check_availability');
if (roomTypeFilter) {
    roomTypeFilter.addEventListener('change', checkRoomAvailability);
}
if (checkAvailabilityBtn) {
    checkAvailabilityBtn.addEventListener('click', checkRoomAvailability);
}

// Initialize date change listeners for availability check
const dateInputs = document.querySelectorAll('input[name="check_in"], input[name="check_out"]');
dateInputs.forEach(input => {
    input.addEventListener('change', function() {
        const checkIn = document.querySelector('input[name="check_in"]')?.value;
        const checkOut = document.querySelector('input[name="check_out"]')?.value;
        if (checkIn && checkOut) {
            setTimeout(checkRoomAvailability, 100);
        }
    });
});