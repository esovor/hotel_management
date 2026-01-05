<?php
require_once 'config.php';
require_once 'functions.php';

// Get data for dropdowns
$roomTypes = getAllRoomTypes($pdo);
$guests = getAllGuests($pdo, '');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if new guest needs to be created
    if ($_POST['guest_type'] == 'new') {
        $guestData = [
            'first_name' => sanitize($_POST['first_name']),
            'last_name' => sanitize($_POST['last_name']),
            'email' => sanitize($_POST['email']),
            'phone' => sanitize($_POST['phone']),
            'address' => sanitize($_POST['address']),
            'id_type' => sanitize($_POST['id_type']),
            'id_number' => sanitize($_POST['id_number']),
            'country' => sanitize($_POST['country'])
        ];

        if (addGuest($pdo, $guestData)) {
            $guest_id = $pdo->lastInsertId();
        } else {
            $error = "Failed to create guest";
        }
    } else {
        $guest_id = intval($_POST['guest_id']);
    }

    if (!isset($error)) {
        $bookingData = [
            'guest_id' => $guest_id,
            'room_id' => intval($_POST['room_id']),
            'check_in' => sanitize($_POST['check_in']),
            'check_out' => sanitize($_POST['check_out']),
            'adults' => intval($_POST['adults']),
            'children' => intval($_POST['children']),
            'special_requests' => sanitize($_POST['special_requests']),
            'status' => 'confirmed',
            'payment_method' => sanitize($_POST['payment_method'])
        ];

        if (addBooking($pdo, $bookingData)) {
            header('Location: index.php?success=Booking created successfully');
            exit();
        } else {
            $error = "Failed to create booking. Room might not be available.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <div class="container header-container">
        <div class="logo">
            <i class="fas fa-hotel"></i>
            <img src="assest/grapes.png" height="40" alt="Grapes Hotel Logo">
            <span><?php echo APP_NAME; ?></span>
        </div>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="bookings_add.php" class="active"><i class="fas fa-calendar-plus"></i> New Booking</a></li>
                <li><a href="guest.php"><i class="fas fa-users"></i> Guests</a></li>
                <li><a href="rooms.php"><i class="fas fa-bed"></i> Rooms</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <div class="form-container">
            <div class="card-header">
                <h2><i class="fas fa-calendar-plus"></i> Create New Booking</h2>
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="bookingForm">
                <!-- Guest Information -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> Guest Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Guest Type *</label>
                            <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                                <label style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="radio" name="guest_type" value="existing" checked
                                           onchange="toggleGuestFields()"> Existing Guest
                                </label>
                                <label style="display: flex; align-items: center; gap: 0.5rem;">
                                    <input type="radio" name="guest_type" value="new"
                                           onchange="toggleGuestFields()"> New Guest
                                </label>
                            </div>
                        </div>

                        <!-- Existing Guest -->
                        <div id="existingGuestSection">
                            <div class="form-group">
                                <label for="guest_id">Select Guest *</label>
                                <select id="guest_id" name="guest_id" required>
                                    <option value="">Select a guest</option>
                                    <?php foreach ($guests as $guest): ?>
                                        <option value="<?php echo $guest['id']; ?>">
                                            <?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?>
                                            - <?php echo htmlspecialchars($guest['phone']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- New Guest -->
                        <div id="newGuestSection" style="display: none;">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input type="text" id="first_name" name="first_name">
                                </div>

                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input type="text" id="last_name" name="last_name">
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email">
                                </div>

                                <div class="form-group">
                                    <label for="phone">Phone Number *</label>
                                    <input type="tel" id="phone" name="phone">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="address">Address</label>
                                <textarea id="address" name="address" rows="2"></textarea>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="id_type">ID Type</label>
                                    <select id="id_type" name="id_type">
                                        <option value="national_id">National ID</option>
                                        <option value="passport">Passport</option>
                                        <option value="driver_license">Driver's License</option>
                                        <option value="voter_id">Voter ID</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="id_number">ID Number</label>
                                    <input type="text" id="id_number" name="id_number">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" id="country" name="country" value="Ghana">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-alt"></i> Booking Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="check_in">Check-in Date *</label>
                                <input type="date" id="check_in" name="check_in" required
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="form-group">
                                <label for="check_out">Check-out Date *</label>
                                <input type="date" id="check_out" name="check_out" required
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="adults">Adults *</label>
                                <select id="adults" name="adults" required>
                                    <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <option value="<?php echo $i; ?>" <?php echo $i == 1 ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> Adult(s)
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="children">Children</label>
                                <select id="children" name="children">
                                    <?php for ($i = 0; $i <= 4; $i++): ?>
                                        <option value="<?php echo $i; ?>"><?php echo $i; ?> Child(ren)</option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="special_requests">Special Requests</label>
                            <textarea id="special_requests" name="special_requests" rows="3"
                                      placeholder="Any special requests..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Room Selection -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bed"></i> Room Selection</h3>
                        <button type="button" id="check_availability" class="btn btn-outline">
                            <i class="fas fa-search"></i> Check Availability
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="room_type_filter">Filter by Room Type</label>
                            <select id="room_type_filter" name="room_type_filter">
                                <option value="">All Room Types</option>
                                <?php foreach ($roomTypes as $type): ?>
                                    <option value="<?php echo $type['id']; ?>">
                                        <?php echo htmlspecialchars($type['type_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="room_id">Select Room *</label>
                            <select id="room_id" name="room_id" required>
                                <option value="">Select a room</option>
                                <?php
                                // Get initially available rooms for today
                                $today = date('Y-m-d');
                                $tomorrow = date('Y-m-d', strtotime('+1 day'));
                                $availableRooms = getAvailableRooms($pdo, $today, $tomorrow);
                                foreach ($availableRooms as $room): ?>
                                    <option value="<?php echo $room['id']; ?>"
                                            data-price="<?php echo $room['price_per_night']; ?>">
                                        Room <?php echo $room['room_number']; ?> -
                                        <?php echo $room['type_name']; ?>
                                        (Floor <?php echo $room['floor']; ?>) -
                                        <?php echo formatCurrency($room['price_per_night']); ?>/night
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="roomDetails" style="display: none;">
                            <div class="room-info">
                                <h4>Room Information</h4>
                                <p id="roomTypeInfo"></p>
                                <p id="roomPriceInfo"></p>
                                <p id="bookingAmount"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Information -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-credit-card"></i> Payment Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="payment_method">Payment Method *</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="cash">Cash</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <div class="payment-summary">
                                <h4>Booking Summary</h4>
                                <div class="detail-item">
                                    <span>Room Price per Night:</span>
                                    <span id="summaryRoomPrice">GH₵ 0.00</span>
                                </div>
                                <div class="detail-item">
                                    <span>Number of Nights:</span>
                                    <span id="summaryNights">0</span>
                                </div>
                                <div class="detail-item total-amount">
                                    <strong>Total Amount:</strong>
                                    <strong id="summaryTotal">GH₵ 0.00</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="text-align: center;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Confirm Booking
                    </button>
                    <button type="reset" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Reset Form
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<footer>
    <div class="container">
        <div class="copyright">
            &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. All rights reserved.
        </div>
    </div>
</footer>

<script src="script.js"></script>
<script>
    function toggleGuestFields() {
        const guestType = document.querySelector('input[name="guest_type"]:checked').value;
        const existingSection = document.getElementById('existingGuestSection');
        const newSection = document.getElementById('newGuestSection');

        if (guestType === 'existing') {
            existingSection.style.display = 'block';
            newSection.style.display = 'none';
            document.getElementById('guest_id').required = true;
            document.getElementById('first_name').required = false;
            document.getElementById('last_name').required = false;
            document.getElementById('phone').required = false;
        } else {
            existingSection.style.display = 'none';
            newSection.style.display = 'block';
            document.getElementById('guest_id').required = false;
            document.getElementById('first_name').required = true;
            document.getElementById('last_name').required = true;
            document.getElementById('phone').required = true;
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        toggleGuestFields();
    });
</script>
</body>
</html>