<?php
require_once 'config.php';
require_once 'functions.php';

// Handle delete action
if (isset($_GET['delete'])) {
    $payment_id = $_GET['delete'];
    if (deletePayment($pdo, $payment_id)) {
        header('Location: payments.php?success=Payment deleted successfully');
        exit();
    } else {
        header('Location: payments.php?error=Failed to delete payment');
        exit();
    }
}

// Get search term
$search = $_GET['search'] ?? '';
$payments = getAllPayments($pdo, $search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>
<body>
<header>
    <div class="container header-container">
        <div class="logo">
            <i class="fas fa-hotel"></i>
<!--            <img src="assest/grapes.png" height="40" alt="Grapes Hotel Logo">-->
            <span><?php echo APP_NAME; ?></span>
        </div>
        <nav>
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="bookings_add.php"><i class="fas fa-calendar-plus"></i> New Booking</a></li>
                <li><a href="guests.php"><i class="fas fa-users"></i> Guests</a></li>
                <li><a href="rooms.php"><i class="fas fa-bed"></i> Rooms</a></li>
                <li><a href="payments.php" class="active"><i class="fas fa-credit-card"></i> Payments</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <div class="table-container">
            <div class="table-header">
                <h2>Payments</h2>
                <form method="GET" class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search payments by guest name or transaction ID...">
                </form>
                <div>
                    <a href="payment_update.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Record Payment
                    </a>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <div style="overflow-x: auto;">
                <table>
                    <thead>
                    <tr>
                        <th>Payment ID</th>
                        <th>Guest Name</th>
                        <th>Room</th>
                        <th>Booking Period</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Transaction ID</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 2rem;">
                                No payments found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td>#<?php echo str_pad($payment['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($payment['first_name'] . ' ' . $payment['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($payment['room_number']); ?></td>
                                <td>
                                    <?php echo date('M d', strtotime($payment['check_in'])); ?> -
                                    <?php echo date('M d, Y', strtotime($payment['check_out'])); ?>
                                </td>
                                <td><?php echo formatCurrency($payment['amount']); ?></td>
                                <td>
                                        <span class="tag">
                                            <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                        </span>
                                </td>
                                <td>
                                        <span class="status <?php echo $payment['status']; ?>">
                                            <?php echo ucfirst($payment['status']); ?>
                                        </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="payment_update.php?id=<?php echo $payment['id']; ?>"
                                           class="btn btn-primary btn-small" title="Edit">
                                            <i class="fas fa-edit">Edit</i>
                                        </a>
                                        <a href="#" onclick="if(confirm('Delete this payment?')) window.location='?delete=<?php echo $payment['id']; ?>'"
                                           class="btn btn-danger btn-small" title="Delete">
                                            <i class="fas fa-trash">Delete</i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Payment Summary -->
            <?php if (!empty($payments)): ?>
                <div class="card" style="margin-top: 1rem;">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> Payment Summary</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $totalAmount = 0;
                        $completedAmount = 0;
                        $pendingAmount = 0;
                        $methodCounts = [];
                        $dailyAmounts = [];

                        foreach ($payments as $payment) {
                            $totalAmount += $payment['amount'];
                            if ($payment['status'] == 'completed') {
                                $completedAmount += $payment['amount'];
                            } elseif ($payment['status'] == 'pending') {
                                $pendingAmount += $payment['amount'];
                            }

                            $method = $payment['payment_method'];
                            $methodCounts[$method] = ($methodCounts[$method] ?? 0) + 1;

                            $date = date('Y-m-d', strtotime($payment['payment_date']));
                            $dailyAmounts[$date] = ($dailyAmounts[$date] ?? 0) + $payment['amount'];
                        }

                        arsort($dailyAmounts);
                        $topDay = key($dailyAmounts) ?? 'N/A';
                        $topDayAmount = $dailyAmounts[$topDay] ?? 0;
                        ?>
                        <div class="form-row">
                            <div class="form-group">
                                <span class="detail-label">Total Payments:</span>
                                <span class="detail-value"><?php echo formatCurrency($totalAmount); ?></span>
                            </div>
                            <div class="form-group">
                                <span class="detail-label">Completed Payments:</span>
                                <span class="detail-value"><?php echo formatCurrency($completedAmount); ?></span>
                            </div>
                            <div class="form-group">
                                <span class="detail-label">Pending Payments:</span>
                                <span class="detail-value"><?php echo formatCurrency($pendingAmount); ?></span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <span class="detail-label">Total Transactions:</span>
                                <span class="detail-value"><?php echo count($payments); ?></span>
                            </div>
                            <div class="form-group">
                                <span class="detail-label">Best Day (<?php echo $topDay; ?>):</span>
                                <span class="detail-value"><?php echo formatCurrency($topDayAmount); ?></span>
                            </div>
                            <div class="form-group">
                                <span class="detail-label">Most Used Method:</span>
                                <span class="detail-value">
                                    <?php
                                    arsort($methodCounts);
                                    echo ucfirst(str_replace('_', ' ', key($methodCounts) ?? 'N/A'));
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
