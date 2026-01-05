<?php
require_once 'config.php';
require_once 'functions.php';

// Get guest ID
$id = $_GET['id'] ?? 0;
$guest = getGuestById($pdo, $id);

if (!$guest) {
    header('Location: guest.php?error=Guest not found');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'first_name' => sanitize($_POST['first_name']),
        'last_name' => sanitize($_POST['last_name']),
        'email' => sanitize($_POST['email']),
        'phone' => sanitize($_POST['phone']),
        'address' => sanitize($_POST['address']),
        'id_type' => sanitize($_POST['id_type']),
        'id_number' => sanitize($_POST['id_number']),
        'country' => sanitize($_POST['country'])
    ];

    if (updateGuest($pdo, $id, $data)) {
        header('Location: guest.php?success=Guest updated successfully');
        exit();
    } else {
        $error = "Failed to update guest";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Guest - <?php echo APP_NAME; ?></title>
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
                <li><a href="guest.php"><i class="fas fa-users"></i> Guests</a></li>
                <li><a href="bookings_add.php"><i class="fas fa-calendar-plus"></i> New Booking</a></li>
            </ul>
        </nav>
    </div>
</header>

<main>
    <div class="container">
        <div class="form-container">
            <div class="card-header">
                <h2><i class="fas fa-user-edit"></i> Edit Guest</h2>
                <a href="guest.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Guests
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="guestForm">
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required
                               value="<?php echo htmlspecialchars($guest['first_name']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required
                               value="<?php echo htmlspecialchars($guest['last_name']); ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email"
                               value="<?php echo htmlspecialchars($guest['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" required
                               value="<?php echo htmlspecialchars($guest['phone']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($guest['address']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="id_type">ID Type</label>
                        <select id="id_type" name="id_type">
                            <option value="national_id" <?php echo $guest['id_type'] == 'national_id' ? 'selected' : ''; ?>>National ID</option>
                            <option value="passport" <?php echo $guest['id_type'] == 'passport' ? 'selected' : ''; ?>>Passport</option>
                            <option value="driver_license" <?php echo $guest['id_type'] == 'driver_license' ? 'selected' : ''; ?>>Driver's License</option>
                            <option value="voter_id" <?php echo $guest['id_type'] == 'voter_id' ? 'selected' : ''; ?>>Voter ID</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="id_number">ID Number</label>
                        <input type="text" id="id_number" name="id_number"
                               value="<?php echo htmlspecialchars($guest['id_number']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="country">Country</label>
                    <input type="text" id="country" name="country"
                           value="<?php echo htmlspecialchars($guest['country']); ?>">
                </div>

                <div class="form-group" style="text-align: center;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Guest
                    </button>
                    <a href="guest.php" class="btn btn-outline">
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
