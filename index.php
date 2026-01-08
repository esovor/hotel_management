<?php
require_once 'config.php';
require_once 'functions.php';

$search = $_GET['search'] ?? '';
$bookings = getAllBookings($pdo, $search);
$stats = getDashboardStats($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<header>
    <div class="container header-container">
        <div class="logo">
            <i class="fas fa-hotel"></i>

<!--                <img src="assest/grapes.png" height="40" alt="Grapes Hotel Logo">-->

            <span><?php echo APP_NAME; ?></span>
        </div>
        <nav>
            <ul>
                <li><a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="booking.php"><i class="fas fa-calendar-plus"></i>Bookings</a></li>
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
        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon booking">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $stats['bookings_today']; ?></h4>
                    <p>Today's Bookings</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon guest">
                    <i class="fas fa-user-check"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $stats['current_guests']; ?></h4>
                    <p>Current Guests</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon room">
                    <i class="fas fa-bed"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo $stats['available_rooms']; ?></h4>
                    <p>Available Rooms</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-info">
                    <h4><?php echo formatCurrency($stats['today_revenue']); ?></h4>
                    <p>Today's Revenue</p>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="table-container">
            <div class="table-header">
                <h2>Recent Bookings</h2>
                <form method="GET" class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search bookings...">
                </form>
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
                        <th>Guest Name</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Amount</th>
                        <th>Paid</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 2rem;">
                                No bookings found. <a href="bookings_add.php">Create your first booking</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>#<?php echo str_pad($booking['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($booking['first_name'] . ' ' . $booking['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['room_number'] . ' (' . $booking['type_name'] . ')'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['check_in'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['check_out'])); ?></td>
                                <td><?php echo formatCurrency($booking['total_amount']); ?></td>
                                <td><?php echo formatCurrency($booking['paid_amount'] ?? 0); ?></td>
                                <td>
                                        <span class="status <?php echo $booking['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $booking['status'])); ?>
                                        </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="bookings_show.php?id=<?php echo $booking['id']; ?>"
                                           class="btn btn-primary btn-small" title="View">View<i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($booking['status'] == 'confirmed'): ?>
                                            <a href="bookings_update.php?action=checkin&id=<?php echo $booking['id']; ?>"
                                               class="btn btn-success btn-small" title="Check-in">Check-in<i class="fas fa-sign-in-alt"></i>
                                            </a>
                                        <?php elseif ($booking['status'] == 'checked_in'): ?>
                                            <a href="bookings_update.php?action=checkout&id=<?php echo $booking['id']; ?>"
                                               class="btn btn-warning btn-small" title="Check-out">Check-out<i class="fas fa-sign-out-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="#" onclick="if(confirm('Delete this booking?')) window.location='bookings_update.php?action=delete&id=<?php echo $booking['id']; ?>'"
                                           class="btn btn-danger btn-small" title="Delete">Delete<i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-grid">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-bar"></i> Quick Stats</h3>
                </div>
                <div class="card-body">
                    <div class="detail-item">
                        <span class="detail-label">Total Bookings:</span>
                        <span class="detail-value"><?php echo $stats['total_bookings']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Guests:</span>
                        <span class="detail-value"><?php echo $stats['total_guests']; ?></span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Rooms:</span>
                        <span class="detail-value">
                                <?php
                                $roomStmt = $pdo->query("SELECT COUNT(*) as count FROM rooms");
                                echo $roomStmt->fetch()['count'];
                                ?>
                            </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Revenue:</span>
                        <span class="detail-value">
                                <?php
                                $revenueStmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
                                echo formatCurrency($revenueStmt->fetch()['total'] ?? 0);
                                ?>
                            </span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-calendar-day"></i> Today's Schedule</h3>
                </div>
                <div class="card-body">
                    <?php
                    $today = date('Y-m-d');
                    $scheduleStmt = $pdo->prepare("SELECT b.*, g.first_name, g.last_name, r.room_number 
                                                      FROM bookings b
                                                      JOIN guests g ON b.guest_id = g.id
                                                      JOIN rooms r ON b.room_id = r.id
                                                      WHERE b.check_in = ? OR b.check_out = ?
                                                      ORDER BY b.check_in");
                    $scheduleStmt->execute([$today, $today]);
                    $schedule = $scheduleStmt->fetchAll();

                    if (empty($schedule)): ?>
                        <p>No check-ins or check-outs scheduled for today.</p>
                    <?php else: ?>
                        <?php foreach ($schedule as $item): ?>
                            <div class="schedule-item">
                                <strong><?php echo htmlspecialchars($item['first_name'] . ' ' . $item['last_name']); ?></strong>
                                <br>
                                Room <?php echo $item['room_number']; ?> -
                                <?php echo $item['check_in'] == $today ? 'Check-in' : 'Check-out'; ?>
                                <br>
                                <small>Status: <?php echo ucfirst($item['status']); ?></small>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<?php   include 'footer.php'; ?>

<script src="script.js"></script>
</body>
</html>