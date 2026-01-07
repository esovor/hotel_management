<?php
require_once 'config.php';
require_once 'functions.php';

// Initialize variables
$payment = null;
$booking = null;
$isEdit = false;

// Check if editing existing payment
if (isset($_GET['id'])) {
    $payment_id = $_GET['id'];
    $payment = getPaymentById($pdo, $payment_id);
    $isEdit = true;

    if ($payment) {
        // Get booking details
        $bookingStmt = $pdo->prepare("SELECT b.*, g.first_name, g.last_name, r.room_number 
                                    FROM bookings b
                                    JOIN guests g ON b.guest_id = g.id
                                    JOIN rooms r ON b.room_id = r.id
                                    WHERE b.id = ?");
        $bookingStmt->execute([$payment['booking_id']]);
        $booking = $bookingStmt->fetch();
    }
}

// Check if creating payment for specific booking
if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];
    $bookingStmt = $pdo->prepare("SELECT b.*, g.first_name, g.last_name, r.room_number 
                                FROM bookings b
                                JOIN guests g ON b.guest_id = g.id
                                JOIN rooms r ON b.room_id = r.id
                                WHERE b.id = ?");
    $bookingStmt->execute([$booking_id]);
    $booking = $bookingStmt->fetch();

    // Calculate balance
    $paidStmt = $pdo->prepare("SELECT SUM(amount) as total_paid FROM payments 
                              WHERE booking_id = ? AND status = 'completed'");
    $paidStmt->execute([$booking_id]);
    $totalPaid = $paidStmt->fetch()['total_paid'] ?? 0;
    $balance = $booking['total_amount'] - $totalPaid;
}

// Get bookings for dropdown if not specified
$bookings = [];
if (!$booking) {
    $bookingStmt = $pdo->query("SELECT b.id, g.first_name, g.last_name, r.room_number, b.total_amount,
                               (SELECT SUM(amount) FROM payments p WHERE p.booking_id = b.id AND p.status = 'completed') as paid_amount
                               FROM bookings b
                               JOIN guests g ON b.guest_id = g.id
                               JOIN rooms r ON b.room_id = r.id
                               WHERE b.status IN ('confirmed', 'checked_in')
                               ORDER BY b.created_at DESC");
    $bookings = $bookingStmt->fetchAll();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'amount' => floatval($_POST['amount']),
        'payment_method' => sanitize($_POST['payment_method']),
        'transaction_id' => sanitize($_POST['transaction_id']),
        'status' => sanitize($_POST['status']),
        'notes' => sanitize($_POST['notes'])
    ];

    if ($isEdit && $payment) {
        // Update existing payment
        if (updatePayment($pdo, $payment['id'], $data)) {
            header('Location: payments.php?success=Payment updated successfully');
            exit();
        } else {
            $error = "Failed to update payment";
        }
    } else {
        // Create new payment
        $booking_id = intval($_POST['booking_id']);
        if (addPayment($pdo, $booking_id, $data['amount'], $data['payment_method'], $data['transaction_id'])) {
            header('Location: payments.php?success=Payment recorded successfully');
            exit();
        } else {
            $error = "Failed to record payment";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isEdit ? 'Edit' : 'Record'; ?> Payment - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
                <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
                <li><a href="bookings_add.php"><i class="fas fa-calendar-plus"></i> New Booking</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <div class="form-container">
            <div class="card-header">
                <h2>
                    <i class="fas <?php echo $isEdit ? 'fa-edit' : 'fa-plus'; ?>"></i>
                    <?php echo $isEdit ? 'Edit Payment' : 'Record New Payment'; ?>
                </h2>
                <a href="payments.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Payments
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Booking Information -->
            <?php if ($booking): ?>
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar-alt"></i> Booking Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="booking-info">
                            <div class="detail-item">
                                <span class="detail-label">Guest:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Room:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($booking['room_number']); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Check-in:</span>
                                <span class="detail-value"><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Check-out:</span>
                                <span class="detail-value"><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label">Total Amount:</span>
                                <span class="detail-value"><?php echo formatCurrency($booking['total_amount']); ?></span>
                            </div>
                            <?php if (isset($balance)): ?>
                                <div class="detail-item">
                                    <span class="detail-label">Outstanding Balance:</span>
                                    <span class="detail-value" style="color: #e74c3c; font-weight: bold;">
                                    <?php echo formatCurrency($balance); ?>
                                </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" id="paymentForm">
                <?php if (!$booking): ?>
                    <div class="form-group">
                        <label for="booking_id">Select Booking *</label>
                        <select id="booking_id" name="booking_id" required>
                            <option value="">Select a booking</option>
                            <?php foreach ($bookings as $b):
                                $paid = $b['paid_amount'] ?? 0;
                                $remaining = $b['total_amount'] - $paid;
                                ?>
                                <option value="<?php echo $b['id']; ?>" data-balance="<?php echo $remaining; ?>">
                                    #<?php echo str_pad($b['id'], 5, '0', STR_PAD_LEFT); ?> -
                                    <?php echo htmlspecialchars($b['first_name'] . ' ' . $b['last_name']); ?> -
                                    Room <?php echo $b['room_number']; ?> -
                                    Total: <?php echo formatCurrency($b['total_amount']); ?> -
                                    Remaining: <?php echo formatCurrency($remaining); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                <?php endif; ?>

                <div class="form-row">
                    <div class="form-group">
                        <label for="amount">Amount (GH₵) *</label>
                        <input type="number" id="amount" name="amount"
                               min="0" step="0.01" required
                               value="<?php echo $payment ? $payment['amount'] : (isset($balance) ? $balance : ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method *</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="cash" <?php echo ($payment && $payment['payment_method'] == 'cash') ? 'selected' : ''; ?>>Cash</option>
                            <option value="mobile_money" <?php echo ($payment && $payment['payment_method'] == 'mobile_money') ? 'selected' : ''; ?>>Mobile Money</option>
                            <option value="credit_card" <?php echo ($payment && $payment['payment_method'] == 'credit_card') ? 'selected' : ''; ?>>Credit Card</option>
                            <option value="bank_transfer" <?php echo ($payment && $payment['payment_method'] == 'bank_transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="transaction_id">Transaction/Reference ID</label>
                        <input type="text" id="transaction_id" name="transaction_id"
                               placeholder="e.g., MTN123456, VISA789012"
                               value="<?php echo $payment ? htmlspecialchars($payment['transaction_id']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="pending" <?php echo ($payment && $payment['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="completed" <?php echo ($payment && $payment['status'] == 'completed' || !$payment) ? 'selected' : ''; ?>>Completed</option>
                            <option value="failed" <?php echo ($payment && $payment['status'] == 'failed') ? 'selected' : ''; ?>>Failed</option>
                            <option value="refunded" <?php echo ($payment && $payment['status'] == 'refunded') ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notes</label>
                    <textarea id="notes" name="notes" rows="3"
                              placeholder="Any additional notes about this payment..."><?php echo $payment ? htmlspecialchars($payment['notes']) : ''; ?></textarea>
                </div>

                <div class="form-group" style="text-align: center;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <?php echo $isEdit ? 'Update Payment' : 'Record Payment'; ?>
                    </button>
                    <a href="payments.php" class="btn btn-outline">
                        <i class="fas fa-times"></i> Cancel
                    </a>
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
    // Auto-fill amount with booking balance when booking is selected
    document.addEventListener('DOMContentLoaded', function() {
        const bookingSelect = document.getElementById('booking_id');
        const amountInput = document.getElementById('amount');

        if (bookingSelect && amountInput) {
            bookingSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const balance = selectedOption.getAttribute('data-balance');
                if (balance && parseFloat(balance) > 0) {
                    amountInput.value = parseFloat(balance).toFixed(2);
                }
            });
        }
    });
</script>
</body>
</html>
