<?php
require_once 'config.php';
require_once 'functions.php';

// Get room ID
$id = $_GET['id'] ?? 0;
$room = getRoomById($pdo, $id);

if (!$room) {
    header('Location: rooms.php?error=Room not found');
    exit();
}

// Get room types for dropdown
$roomTypes = getAllRoomTypes($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
            'room_number' => sanitize($_POST['room_number']),
            'room_type_id' => intval($_POST['room_type_id']),
            'floor' => intval($_POST['floor']),
            'status' => sanitize($_POST['status']),
            'features' => sanitize($_POST['features'])
    ];

    if (updateRoom($pdo, $id, $data)) {
        header('Location: rooms.php?success=Room updated successfully');
        exit();
    } else {
        $error = "Failed to update room";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room - <?php echo APP_NAME; ?></title>
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
                <li><a href="rooms.php"><i class="fas fa-bed"></i> Rooms</a></li>
                <li><a href="roomType.php"><i class="fas fa-list"></i> Room Types</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <div class="form-container">
            <div class="card-header">
                <h2><i class="fas fa-edit"></i> Edit Room</h2>
                <a href="rooms.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="roomForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="room_number">Room Number *</label>
                        <input type="text" id="room_number" name="room_number" required
                               value="<?php echo htmlspecialchars($room['room_number']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="room_type_id">Room Type *</label>
                        <select id="room_type_id" name="room_type_id" required>
                            <?php foreach ($roomTypes as $type): ?>
                                <option value="<?php echo $type['id']; ?>"
                                        <?php echo $type['id'] == $room['room_type_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['type_name']); ?> -
                                    <?php echo formatCurrency($type['price_per_night']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="floor">Floor</label>
                        <input type="number" id="floor" name="floor" min="1" max="20"
                               value="<?php echo $room['floor']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="status">Status *</label>
                        <select id="status" name="status" required>
                            <option value="available" <?php echo $room['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="occupied" <?php echo $room['status'] == 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                            <option value="maintenance" <?php echo $room['status'] == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            <option value="cleaning" <?php echo $room['status'] == 'cleaning' ? 'selected' : ''; ?>>Cleaning</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="features">Additional Features</label>
                    <textarea id="features" name="features" rows="3"><?php echo htmlspecialchars($room['features']); ?></textarea>
                </div>

                <div class="form-group" style="text-align: center;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Room
                    </button>
                    <a href="rooms.php" class="btn btn-outline">
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
</body>
</html>