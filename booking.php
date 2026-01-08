<?php
require_once 'config.php';
require_once 'functions.php';

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deleteBooking($pdo, $id)) {
        header('Location: booking.php?success=Booking deleted successfully');
        exit();
    } else {
        header('Location: booking.php?error=Failed to delete booking');
        exit();
    }
}

// Get search term
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$date_filter = $_GET['date'] ?? '';

// Build query with filters
$params = [];
$where_clauses = [];

if (!empty($search)) {
    $where_clauses[] = "(g.first_name LIKE ? OR g.last_name LIKE ? OR r.room_number LIKE ? OR b.id LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $searchTermId = ltrim($search, '#');
    $params[] = "%$searchTermId%";
}

if (!empty($status_filter)) {
    $where_clauses[] = "b.status = ?";
    $params[] = $status_filter;
}

if (!empty($date_filter)) {
    $where_clauses[] = "(b.check_in = ? OR b.check_out = ?)";
    $params[] = $date_filter;
    $params[] = $date_filter;
}

// Build final query
$sql = "SELECT b.*, 
        g.first_name, g.last_name, g.email, g.phone,
        r.room_number, rt.type_name,
        (SELECT SUM(amount) FROM payments p WHERE p.booking_id = b.id AND p.status = 'completed') as paid_amount
        FROM bookings b
        JOIN guests g ON b.guest_id = g.id
        JOIN rooms r ON b.room_id = r.id
        JOIN room_types rt ON r.room_type_id = rt.id";

if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

// Get booking statistics
$stats_stmt = $pdo->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
        SUM(CASE WHEN status = 'checked_in' THEN 1 ELSE 0 END) as checked_in,
        SUM(CASE WHEN status = 'checked_out' THEN 1 ELSE 0 END) as checked_out,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM bookings
");
$booking_stats = $stats_stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Bookings - <?php echo APP_NAME; ?></title>
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
                <li><a href="booking.php" class="active"><i class="fas fa-calendar-plus"></i>Bookings</a></li>
                <li><a href="guests.php"><i class="fas fa-users"></i> Guests</a></li>
                <li><a href="rooms.php"><i class="fas fa-list"></i> Rooms</a></li>
                <li><a href="roomType.php"><i class="fas fa-bed"></i> Room Type</a></li>
                <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>

            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <!-- Booking Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon booking">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $booking_stats['total'] ?? 0; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #cce5ff;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $booking_stats['confirmed'] ?? 0; ?></h3>
                    <p>Confirmed</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #d1ecf1;">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $booking_stats['checked_in'] ?? 0; ?></h3>
                    <p>Checked-in</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: #d4edda;">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $booking_stats['checked_out'] ?? 0; ?></h3>
                    <p>Checked-out</p>
                </div>
            </div>
        </div>

        <!-- Bookings Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>All Bookings</h2>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <form method="GET" class="search-box" style="min-width: 200px;">
                        <i class="fas fa-search"></i>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Search bookings...">
                    </form>
                    <form method="GET" style="display: flex; gap: 0.5rem;">
                        <select name="status" onchange="this.form.submit()" style="padding: 0.5rem;">
                            <option value="">All Status</option>
                            <option value="confirmed" <?php echo $status_filter == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                            <option value="checked_in" <?php echo $status_filter == 'checked_in' ? 'selected' : ''; ?>>Checked-in</option>
                            <option value="checked_out" <?php echo $status_filter == 'checked_out' ? 'selected' : ''; ?>>Checked-out</option>
                            <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                        <input type="date" name="date" value="<?php echo htmlspecialchars($date_filter); ?>"
                               onchange="this.form.submit()" style="padding: 0.5rem;">
                        <?php if ($search || $status_filter || $date_filter): ?>
                            <a href="booking.php" class="btn btn-outline" style="padding: 0.5rem 1rem;">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
                <a href="bookings_add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> New Booking
                </a>
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
                        <th>Booking ID</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Nights</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Balance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 2rem;">
                                No bookings found.
                                <?php if ($search || $status_filter || $date_filter): ?>
                                    <a href="booking.php">Clear filters</a> or
                                <?php endif; ?>
                                <a href="bookings_add.php">create a new booking</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking):
                            $nights = (strtotime($booking['check_out']) - strtotime($booking['check_in'])) / (60 * 60 * 24);
                            $paid = $booking['paid_amount'] ?? 0;
                            $balance = $booking['total_amount'] - $paid;
                            ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></strong>
                                    <br>
                                    <small style="color: #666;">
                                        <?php echo date('M d, Y', strtotime($booking['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></strong>
                                    <br>
                                    <small style="color: #666;"><?php echo htmlspecialchars($booking['phone']); ?></small>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($booking['room_number']); ?>
                                    <br>
                                    <small style="color: #666;"><?php echo htmlspecialchars($booking['type_name']); ?></small>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($booking['check_in'])); ?>
                                    <br>
                                    <small style="color: #666;"><?php echo date('D', strtotime($booking['check_in'])); ?></small>
                                </td>
                                <td>
                                    <?php echo date('M d, Y', strtotime($booking['check_out'])); ?>
                                    <br>
                                    <small style="color: #666;"><?php echo date('D', strtotime($booking['check_out'])); ?></small>
                                </td>
                                <td><?php echo $nights; ?></td>
                                <td>
                                    <strong><?php echo formatCurrency($booking['total_amount']); ?></strong>
                                </td>
                                <td>
                                    <?php echo formatCurrency($paid); ?>
                                    <br>
                                    <small style="color: <?php echo $paid > 0 ? '#27ae60' : '#666'; ?>;">
                                        <?php echo $booking['total_amount'] > 0 ? round(($paid / $booking['total_amount']) * 100) : 0; ?>%
                                    </small>
                                </td>
                                <td>
                                        <span style="color: <?php echo $balance > 0 ? '#e74c3c' : '#27ae60'; ?>; font-weight: <?php echo $balance > 0 ? 'bold' : 'normal'; ?>;">
                                            <?php echo formatCurrency($balance); ?>
                                        </span>
                                </td>
                                <td>
                                        <span class="status <?php echo $booking['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                                        </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="bookings_show.php?id=<?php echo $booking['id']; ?>"
                                           class="btn btn-primary btn-small" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="bookings_add.php?edit=<?php echo $booking['id']; ?>"
                                           class="btn btn-warning btn-small" title="Edit Booking">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($booking['status'] == 'confirmed'): ?>
                                            <a href="bookings_update.php?action=checkin&id=<?php echo $booking['id']; ?>"
                                               class="btn btn-success btn-small" title="Check-in">
                                                <i class="fas fa-sign-in-alt"></i>
                                            </a>
                                        <?php elseif ($booking['status'] == 'checked_in'): ?>
                                            <a href="bookings_update.php?action=checkout&id=<?php echo $booking['id']; ?>"
                                               class="btn btn-outline btn-small" title="Check-out" style="border-color: #f39c12; color: #f39c12;">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="#" onclick="if(confirm('Are you sure you want to delete booking #<?php echo $booking['id']; ?>?')) window.location='?delete=<?php echo $booking['id']; ?>'"
                                           class="btn btn-danger btn-small" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Booking Summary -->
            <?php if (!empty($bookings)): ?>
                <div class="card" style="margin-top: 1rem;">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-bar"></i> Booking Summary</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        $totalAmount = 0;
                        $totalPaid = 0;
                        $totalBalance = 0;
                        $statusCounts = [];

                        foreach ($bookings as $booking) {
                            $totalAmount += $booking['total_amount'];
                            $totalPaid += $booking['paid_amount'] ?? 0;
                            $totalBalance += ($booking['total_amount'] - ($booking['paid_amount'] ?? 0));
                            $statusCounts[$booking['status']] = ($statusCounts[$booking['status']] ?? 0) + 1;
                        }
                        ?>
                        <div class="form-row">
                            <div class="form-group">
                                <span class="detail-label">Total Bookings:</span>
                                <span class="detail-value"><?php echo count($bookings); ?></span>
                            </div>
                            <div class="form-group">
                                <span class="detail-label">Total Revenue:</span>
                                <span class="detail-value"><?php echo formatCurrency($totalAmount); ?></span>
                            </div>
                            <div class="form-group">
                                <span class="detail-label">Total Paid:</span>
                                <span class="detail-value"><?php echo formatCurrency($totalPaid); ?></span>
                            </div>
                            <div class="form-group">
                                <span class="detail-label">Total Balance:</span>
                                <span class="detail-value" style="color: <?php echo $totalBalance > 0 ? '#e74c3c' : '#27ae60'; ?>;">
                                    <?php echo formatCurrency($totalBalance); ?>
                                </span>
                            </div>
                        </div>
                        <div class="form-row">
                            <?php foreach ($statusCounts as $status => $count): ?>
                                <div class="form-group">
                                <span class="detail-label" style="text-transform: capitalize;">
                                    <?php echo str_replace('_', ' ', $status); ?>:
                                </span>
                                    <span class="detail-value"><?php echo $count; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="action-buttons" style="justify-content: center; gap: 1rem;">
                    <a href="bookings_add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Booking
                    </a>
                    <a href="booking.php?status=checked_in" class="btn btn-success">
                        <i class="fas fa-user-check"></i> View Checked-in Guests
                    </a>
                    <a href="booking.php?date=<?php echo date('Y-m-d'); ?>" class="btn btn-warning">
                        <i class="fas fa-calendar-day"></i> Today's Arrivals/Departures
                    </a>
                    <a href="payments.php" class="btn btn-outline">
                        <i class="fas fa-credit-card"></i> View All Payments
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php   include 'footer.php'; ?>

<script src="script.js"></script>
<script>
    // Add confirmation for check-in/check-out
    document.addEventListener('DOMContentLoaded', function() {
        const checkinLinks = document.querySelectorAll('a[title="Check-in"]');
        const checkoutLinks = document.querySelectorAll('a[title="Check-out"]');

        checkinLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to check-in this guest?')) {
                    e.preventDefault();
                }
            });
        });

        checkoutLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to check-out this guest?')) {
                    e.preventDefault();
                }
            });
        });

        // Add filter functionality
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.addEventListener('keyup', function(e) {
                if (e.key === 'Enter') {
                    this.form.submit();
                }
            });
        }
    });
</script>
</body>
</html>