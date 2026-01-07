<?php
require_once 'config.php';
require_once 'functions.php';

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deleteRoom($pdo, $id)) {
        header('Location: rooms.php?success=Room deleted successfully');
        exit();
    } else {
        header('Location: rooms.php?error=Failed to delete room');
        exit();
    }
}

// Get all rooms
$rooms = getAllRooms($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - <?php echo APP_NAME; ?></title>
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
                <li><a href="roomType.php"><i class="fas fa-list"></i> Room Types</a></li>
                <li><a href="rooms.php" class="active"><i class="fas fa-bed"></i> Rooms</a></li>
                <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <div class="table-container">
            <div class="table-header">
                <h2>Rooms</h2>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-input" placeholder="Search rooms...">
                </div>
                <a href="rooms_add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Room
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
                <table id="roomsTable">
                    <thead>
                    <tr>
                        <th>Room Number</th>
                        <th>Type</th>
                        <th>Floor</th>
                        <th>Status</th>
                        <th>Price/Night</th>
                        <th>Features</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No rooms found. <a href="rooms_add.php">Add your first room</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooms as $room): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($room['room_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($room['type_name']); ?></td>
                                <td><?php echo $room['floor'] ?? 'N/A'; ?></td>
                                <td>
                                        <span class="status <?php echo $room['status']; ?>">
                                            <?php echo ucfirst($room['status']); ?>
                                        </span>
                                </td>
                                <td><?php echo formatCurrency($room['price_per_night']); ?></td>
                                <td><?php echo htmlspecialchars($room['features'] ?? 'None'); ?></td>

                                <td>
                                    <div class="action-buttons">
                                        <a href="rooms_update.php?id=<?php echo $room['id']; ?>"
                                           class="btn btn-primary btn-small" title="Edit">Edit<i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" onclick="if(confirm('Delete this room?')) window.location='?delete=<?php echo $room['id']; ?>'"
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

            <!-- Room Statistics -->
            <div class="card" style="margin-top: 1rem;">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> Room Statistics</h3>
                </div>
                <div class="card-body">
                    <?php
                    $statusCounts = [];
                    $typeCounts = [];
                    foreach ($rooms as $room) {
                        $statusCounts[$room['status']] = ($statusCounts[$room['status']] ?? 0) + 1;
                        $typeCounts[$room['type_name']] = ($typeCounts[$room['type_name']] ?? 0) + 1;
                    }
                    ?>
                    <div class="form-row">
                        <div class="form-group">
                            <span class="detail-label">Total Rooms:</span>
                            <span class="detail-value"><?php echo count($rooms); ?></span>
                        </div>
                        <div class="form-group">
                            <span class="detail-label">Available:</span>
                            <span class="detail-value"><?php echo $statusCounts['available'] ?? 0; ?></span>
                        </div>
                        <div class="form-group">
                            <span class="detail-label">Occupied:</span>
                            <span class="detail-value"><?php echo $statusCounts['occupied'] ?? 0; ?></span>
                        </div>
                        <div class="form-group">
                            <span class="detail-label">Under Maintenance:</span>
                            <span class="detail-value"><?php echo $statusCounts['maintenance'] ?? 0; ?></span>
                        </div>
                    </div>
                    <div class="form-row">
                        <?php foreach ($typeCounts as $type => $count): ?>
                            <div class="form-group">
                                <span class="detail-label"><?php echo htmlspecialchars($type); ?>:</span>
                                <span class="detail-value"><?php echo $count; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
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