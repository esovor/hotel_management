<?php
require_once 'config.php';
require_once 'functions.php';

// Get booking ID
$id = $_GET['id'] ?? 0;
$booking = getBookingById($pdo, $id);

if (!$booking) {
    header('Location: index.php?error=Booking not found');
    exit();
}

// Get payments for this booking
$paymentStmt = $pdo->prepare("SELECT * FROM payments WHERE booking_id = ? ORDER BY payment_date DESC");
$paymentStmt->execute([$id]);
$payments = $paymentStmt->fetchAll();

// Calculate totals
$totalPaid = 0;
foreach ($payments as $payment) {
    if ($payment['status'] == 'completed') {
        $totalPaid += $payment['amount'];
    }
}
$balance = $booking['total_amount'] - $totalPaid;
$nights = (strtotime($booking['check_out']) - strtotime($booking['check_in'])) / (60 * 60 * 24);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking #<?php echo str_pad($id, 5, '0', STR_PAD_LEFT); ?> - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<header>
    <div class="container header-container">
        <div class="logo">
            <i class="fas fa-hotel"></i>
            <span><?php echo APP_NAME; ?></span>
        </div>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="bookings_add.php"><i class="fas fa-calendar-plus"></i> New Booking</a></li>
                <li><a href="guest.php"><i class="fas fa-users"></i> Guests</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <div class="card-header">
            <h2>
                <i class="fas fa-file-invoice"></i>
                Booking Details - #<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?>
            </h2>
            <div style="display: flex; gap: 1rem;">
                <a href="index.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button onclick="window.print()" class="btn" style="background: #6c757d; color: white;">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>

        <div class="booking-details">
            <!-- Guest Information -->
            <div class="detail-card">
                <h3><i class="fas fa-user"></i> Guest Information</h3>
                <div class="detail-item">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['email']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['phone']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['address']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">ID Type:</span>
                    <span class="detail-value"><?php echo ucfirst(str_replace('_', ' ', $booking['id_type'])); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">ID Number:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['id_number']); ?></span>
                </div>
            </div>

            <!-- Booking Information -->
            <div class="detail-card">
                <h3><i class="fas fa-calendar-alt"></i> Booking Information</h3>
                <div class="detail-item">
                    <span class="detail-label">Booking ID:</span>
                    <span class="detail-value">#<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                            <span class="status <?php echo $booking['status']; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                            </span>
                        </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Check-in:</span>
                    <span class="detail-value"><?php echo date('F d, Y', strtotime($booking['check_in'])); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Check-out:</span>
                    <span class="detail-value"><?php echo date('F d, Y', strtotime($booking['check_out'])); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Duration:</span>
                    <span class="detail-value"><?php echo $nights; ?> night(s)</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Guests:</span>
                    <span class="detail-value">
                            <?php echo $booking['adults']; ?> Adult(s),
                            <?php echo $booking['children']; ?> Child(ren)
                        </span>
                </div>
            </div>

            <!-- Room Information -->
            <div class="detail-card">
                <h3><i class="fas fa-bed"></i> Room Information</h3>
                <div class="detail-item">
                    <span class="detail-label">Room Number:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['room_number']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Room Type:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($booking['type_name']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Floor:</span>
                    <span class="detail-value"><?php echo $booking['floor']; ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Capacity:</span>
                    <span class="detail-value"><?php echo $booking['capacity']; ?> person(s)</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Price per Night:</span>
                    <span class="detail-value"><?php echo formatCurrency($booking['price_per_night']); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Room Status:</span>
                    <span class="detail-value">
                            <span class="status <?php echo $booking['room_status']; ?>">
                                <?php echo ucfirst($booking['room_status']); ?>
                            </span>
                        </span>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="detail-card">
                <h3><i class="fas fa-money-bill-wave"></i> Payment Information</h3>
                <div class="detail-item">
                    <span class="detail-label">Total Amount:</span>
                    <span class="detail-value total-amount"><?php echo formatCurrency($booking['total_amount']); ?></span>
                </div>

                <?php if (!empty($payments)): ?>
                    <?php foreach ($payments as $payment): ?>
                        <div class="detail-item">
                            <span class="detail-label">
                                Payment (<?php echo date('M d', strtotime($payment['payment_date'])); ?>):
                            </span>
                            <span class="detail-value">
                                <?php echo formatCurrency($payment['amount']); ?>
                                <small class="status <?php echo $payment['status']; ?>" style="margin-left: 10px;">
                                    <?php echo ucfirst($payment['status']); ?>
                                </small>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="detail-item">
                        <span class="detail-label">Payments:</span>
                        <span class="detail-value">No payments recorded</span>
                    </div>
                <?php endif; ?>

                <div class="detail-item">
                    <span class="detail-label">Total Paid:</span>
                    <span class="detail-value"><?php echo formatCurrency($totalPaid); ?></span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Balance:</span>
                    <span class="detail-value <?php echo $balance > 0 ? 'text-danger' : 'text-success'; ?>"
                          style="color: <?php echo $balance > 0 ? '#e74c3c' : '#27ae60'; ?>; font-weight: bold;">
                            <?php echo formatCurrency($balance); ?>
                        </span>
                </div>
            </div>
        </div>

        <!-- Special Requests -->
        <?php if (!empty($booking['special_requests'])): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-sticky-note"></i> Special Requests</h3>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Amenities -->
        <?php if (!empty($booking['amenities'])): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-concierge-bell"></i> Room Amenities</h3>
                </div>
                <div class="card-body">
                    <?php
                    $amenities = explode(',', $booking['amenities']);
                    foreach ($amenities as $amenity):
                        if (trim($amenity)): ?>
                            <span class="tag"><?php echo trim($amenity); ?></span>
                        <?php endif;
                    endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="card" style="text-align: center;">
            <div class="card-body">
                <div class="action-buttons" style="justify-content: center;">
                    <?php if ($booking['status'] == 'confirmed'): ?>
                        <a href="bookings_update.php?action=checkin&id=<?php echo $booking['id']; ?>"
                           class="btn btn-success">
                            <i class="fas fa-sign-in-alt"></i> Check-in Guest
                        </a>
                    <?php elseif ($booking['status'] == 'checked_in'): ?>
                        <a href="bookings_update.php?action=checkout&id=<?php echo $booking['id']; ?>"
                           class="btn btn-warning">
                            <i class="fas fa-sign-out-alt"></i> Check-out Guest
                        </a>
                    <?php endif; ?>

                    <?php if ($balance > 0): ?>
                        <a href="payment_update.php?booking_id=<?php echo $booking['id']; ?>"
                           class="btn btn-primary">
                            <i class="fas fa-credit-card"></i> Record Payment
                        </a>
                    <?php endif; ?>

                    <a href="#" onclick="if(confirm('Are you sure you want to cancel this booking?')) window.location='bookings_update.php?action=delete&id=<?php echo $booking['id']; ?>'"
                       class="btn btn-danger">
                        <i class="fas fa-times"></i> Cancel Booking
                    </a>
                </div>
            </div>
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
</body>
</html>