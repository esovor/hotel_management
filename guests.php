<?php
require_once 'config.php';
require_once 'functions.php';

// Handle delete action
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    if (deleteGuest($pdo, $id)) {
        header('Location: guest.php?success=Guest deleted successfully');
        exit();
    } else {
        header('Location: guest.php?error=Failed to delete guest');
        exit();
    }
}

// Get search term
$search = $_GET['search'] ?? '';
$guests = getAllGuests($pdo, $search);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guests - <?php echo APP_NAME; ?></title>
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
                <li><a href="guest.php" class="active"><i class="fas fa-users"></i> Guests</a></li>
                <li><a href="roomType.php"><i class="fas fa-list"></i> Room Types</a></li>
                <li><a href="rooms.php"><i class="fas fa-bed"></i> Rooms</a></li>
                <li><a href="payments.php"><i class="fas fa-credit-card"></i> Payments</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <div class="table-container">
            <div class="table-header">
                <h2>Guests</h2>
                <form method="GET" class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>"
                           placeholder="Search guests by name, email or phone...">
                </form>
                <a href="bookings_add.php" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add Guest
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
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
<!--                        <th>ID Type</th>-->
                        <th>ID Number</th>
                        <th>Country</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($guests)): ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 2rem;">
                                No guests found. <a href="bookings_add.php">Add your first guest through booking</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($guests as $guest): ?>
                            <tr>
                                <td>#<?php echo str_pad($guest['id'], 5, '0', STR_PAD_LEFT); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($guest['first_name'] . ' ' . $guest['last_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($guest['email'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($guest['phone']); ?></td>
<!--                                <td>-->
<!--                                    <span class="tag">--><?php //echo ucfirst(str_replace('_', ' ', $guest['id_type'])); ?><!--</span>-->
<!--                                </td>-->
                                <td><?php echo htmlspecialchars($guest['id_number'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($guest['country']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($guest['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="guest_update.php?id=<?php echo $guest['id']; ?>"
                                           class="btn btn-primary btn-small" title="Edit">Edit<i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" onclick="if(confirm('Delete this guest?')) window.location='?delete=<?php echo $guest['id']; ?>'"
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

            <!-- Guest Statistics -->
            <div class="card" style="margin-top: 1rem;">
                <div class="card-header">
                    <h3><i class="fas fa-chart-pie"></i> Guest Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group">
                            <span class="detail-label">Total Guests:</span>
                            <span class="detail-value"><?php echo count($guests); ?></span>
                        </div>
                        <div class="form-group">
                            <span class="detail-label">Ghanaian Guests:</span>
                            <span class="detail-value">
                                    <?php
                                    $ghanaian = array_filter($guests, function($g) {
                                        return strtolower($g['country']) === 'ghana';
                                    });
                                    echo count($ghanaian);
                                    ?>
                                </span>
                        </div>
                        <div class="form-group">
                            <span class="detail-label">With Email:</span>
                            <span class="detail-value">
                                    <?php
                                    $withEmail = array_filter($guests, function($g) {
                                        return !empty($g['email']);
                                    });
                                    echo count($withEmail);
                                    ?>
                                </span>
                        </div>
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
