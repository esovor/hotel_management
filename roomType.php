<?php
require_once 'config.php';
require_once 'functions.php';

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deleteRoomType($pdo, $id)) {
        header('Location: roomType.php?success=Room type deleted successfully');
        exit();
    } else {
        header('Location: roomType.php?error=Failed to delete room type');
        exit();
    }
}

// Get all room types
$roomTypes = getAllRoomTypes($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Types - <?php echo APP_NAME; ?></title>
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
                <li><a href="booking.php"><i class="fas fa-calendar-plus"></i>Bookings</a></li>
                <li><a href="guests.php"><i class="fas fa-users"></i> Guests</a></li>
                <li><a href="rooms.php" ><i class="fas fa-list"></i> Rooms</a></li>
                <li><a href="roomType.php"  class="active"><i class="fas fa-bed"></i> Room Type</a></li>
                <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <div class="table-container">
            <div class="table-header">
                <h2>Room Types</h2>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-input" placeholder="Search room types...">
                </div>
                <a href="roomType_add.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Room Type
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
                <table id="roomTypesTable">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type Name</th>
                        <th>Description</th>
                        <th>Price/Night</th>
                        <th>Capacity</th>
                        <th>Amenities</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($roomTypes)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 2rem;">
                                No room types found. <a href="roomType_add.php">Add your first room type</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($roomTypes as $type): ?>
                            <tr>
                                <td>#<?php echo str_pad($type['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td><strong><?php echo htmlspecialchars($type['type_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($type['description']); ?></td>
                                <td><?php echo formatCurrency($type['price_per_night']); ?></td>
                                <td><?php echo $type['capacity']; ?> person(s)</td>
                                <td>
                                    <?php
                                    $amenities = explode(',', $type['amenities'] ?? '');
                                    foreach ($amenities as $amenity):
                                        if (trim($amenity)): ?>
                                            <span class="tag"><?php echo trim(htmlspecialchars($amenity)); ?></span>
                                        <?php endif;
                                    endforeach; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($type['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="roomType_update.php?id=<?php echo $type['id']; ?>"
                                           class="btn btn-primary btn-small" title="Edit">Edit<i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" onclick="if(confirm('Delete this room type?')) window.location='?delete=<?php echo $type['id']; ?>'"
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

            <!-- Room Type Statistics -->
            <div class="card" style="margin-top: 1rem;">
                <div class="card-header">
                    <h3><i class="fas fa-chart-line"></i> Room Type Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <span class="detail-label">Total Room Types:</span>
                            <span class="detail-value"><?php echo count($roomTypes); ?></span>
                        </div>
                        <div class="form-group">
                            <span class="detail-label">Average Price:</span>
                            <span class="detail-value">
                                    <?php
                                    $totalPrice = array_sum(array_column($roomTypes, 'price_per_night'));
                                    $avgPrice = count($roomTypes) > 0 ? $totalPrice / count($roomTypes) : 0;
                                    echo formatCurrency($avgPrice);
                                    ?>
                                </span>
                        </div>
                        <div class="form-group">
                            <span class="detail-label">Highest Price:</span>
                            <span class="detail-value">
                                    <?php
                                    $maxPrice = count($roomTypes) > 0 ? max(array_column($roomTypes, 'price_per_night')) : 0;
                                    echo formatCurrency($maxPrice);
                                    ?>
                                </span>
                        </div>
                        <div class="form-group">
                            <span class="detail-label">Most Common Capacity:</span>
                            <span class="detail-value">
                                    <?php
                                    $capacities = array_count_values(array_column($roomTypes, 'capacity'));
                                    arsort($capacities);
                                    echo key($capacities) . ' person(s)';
                                    ?>
                                </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php   include 'footer.php'; ?>

<script src="script.js"></script>
</body>
</html>
