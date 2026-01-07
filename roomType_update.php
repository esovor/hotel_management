<?php
require_once 'config.php';
require_once 'functions.php';

// Get room type ID
$id = $_GET['id'] ?? 0;
$roomType = getRoomTypeById($pdo, $id);

if (!$roomType) {
    header('Location: roomType.php?error=Room type not found');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'type_name' => sanitize($_POST['type_name']),
        'description' => sanitize($_POST['description']),
        'price_per_night' => floatval($_POST['price_per_night']),
        'capacity' => intval($_POST['capacity']),
        'amenities' => sanitize($_POST['amenities'])
    ];

    if (updateRoomType($pdo, $id, $data)) {
        header('Location: roomType.php?success=Room type updated successfully');
        exit();
    } else {
        $error = "Failed to update room type";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Room Type - <?php echo APP_NAME; ?></title>
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
                <li><a href="roomType.php"><i class="fas fa-list"></i> Room Types</a></li>
                <li><a href="rooms.php"><i class="fas fa-bed"></i> Rooms</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <div class="form-container">
            <div class="card-header">
                <h2><i class="fas fa-edit"></i> Edit Room Type</h2>
                <a href="roomType.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="roomTypeForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="type_name">Type Name *</label>
                        <input type="text" id="type_name" name="type_name" required
                               value="<?php echo htmlspecialchars($roomType['type_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="capacity">Capacity *</label>
                        <select id="capacity" name="capacity" required>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $i == $roomType['capacity'] ? 'selected' : ''; ?>>
                                    <?php echo $i; ?> person(s)
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="price_per_night">Price Per Night (GH₵) *</label>
                    <input type="number" id="price_per_night" name="price_per_night"
                           min="0" step="0.01" required
                           value="<?php echo $roomType['price_per_night']; ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($roomType['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="amenities">Amenities (comma-separated)</label>
                    <textarea id="amenities" name="amenities" rows="3"><?php echo htmlspecialchars($roomType['amenities']); ?></textarea>
                    <small class="form-text">Separate amenities with commas</small>
                </div>

                <div class="form-group" style="text-align: center;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Room Type
                    </button>
                    <a href="roomType.php" class="btn btn-outline">
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
