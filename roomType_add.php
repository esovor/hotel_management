<?php
require_once 'config.php';
require_once 'functions.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'type_name' => sanitize($_POST['type_name']),
        'description' => sanitize($_POST['description']),
        'price_per_night' => floatval($_POST['price_per_night']),
        'capacity' => intval($_POST['capacity']),
        'amenities' => sanitize($_POST['amenities'])
    ];

    if (addRoomType($pdo, $data)) {
        header('Location: roomType.php?success=Room type added successfully');
        exit();
    } else {
        $error = "Failed to add room type";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Room Type - <?php echo APP_NAME; ?></title>
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
                <h2><i class="fas fa-plus"></i> Add New Room Type</h2>
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
                               placeholder="e.g., Standard Room, Deluxe Suite">
                    </div>

                    <div class="form-group">
                        <label for="capacity">Capacity *</label>
                        <select id="capacity" name="capacity" required>
                            <option value="">Select capacity</option>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> person(s)</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="price_per_night">Price Per Night (GH₵) *</label>
                    <input type="number" id="price_per_night" name="price_per_night"
                           min="0" step="0.01" required placeholder="250.00">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Describe the room type..."></textarea>
                </div>

                <div class="form-group">
                    <label for="amenities">Amenities (comma-separated)</label>
                    <textarea id="amenities" name="amenities" rows="3"
                              placeholder="WiFi, TV, Air Conditioning, Mini Bar"></textarea>
                    <small class="form-text">Separate amenities with commas</small>
                </div>

                <div class="form-group" style="text-align: center;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Room Type
                    </button>
                    <button type="reset" class="btn btn-outline">
                        <i class="fas fa-redo"></i> Reset
                    </button>
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
