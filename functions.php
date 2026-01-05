<?php
require_once 'config.php';

// Room Type Functions
function getAllRoomTypes($pdo) {
    $stmt = $pdo->query("SELECT * FROM room_types ORDER BY type_name");
    return $stmt->fetchAll();
}

function getRoomTypeById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM room_types WHERE id = ?");
    $stmt->execute([$id]);
    $roomType = $stmt->fetch();

    // Ensure all fields have default values
    if ($roomType) {
        $roomType['description'] = $roomType['description'] ?? '';
        $roomType['amenities'] = $roomType['amenities'] ?? '';
    }

    return $roomType;
}

function addRoomType($pdo, $data) {
    $sql = "INSERT INTO room_types (type_name, description, price_per_night, capacity, amenities) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['type_name'],
        $data['description'],
        $data['price_per_night'],
        $data['capacity'],
        $data['amenities']
    ]);
}

function updateRoomType($pdo, $id, $data) {
    $sql = "UPDATE room_types SET type_name = ?, description = ?, price_per_night = ?, capacity = ?, amenities = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['type_name'],
        $data['description'],
        $data['price_per_night'],
        $data['capacity'],
        $data['amenities'],
        $id
    ]);
}

function deleteRoomType($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM room_types WHERE id = ?");
    return $stmt->execute([$id]);
}

// Room Functions
function getAllRooms($pdo) {
    $sql = "SELECT r.*, rt.type_name, rt.price_per_night FROM rooms r 
            JOIN room_types rt ON r.room_type_id = rt.id 
            ORDER BY r.room_number";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getRoomById($pdo, $id) {
    $sql = "SELECT r.*, rt.type_name, rt.price_per_night, rt.capacity FROM rooms r 
            JOIN room_types rt ON r.room_type_id = rt.id 
            WHERE r.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $room = $stmt->fetch();

    // Ensure all fields have default values
    if ($room) {
        $room['features'] = $room['features'] ?? '';
        $room['floor'] = $room['floor'] ?? '';
        $room['status'] = $room['status'] ?? 'available';
    }

    return $room;
}

function getAvailableRooms($pdo, $check_in, $check_out, $room_type_id = null) {
    $sql = "SELECT r.*, rt.type_name, rt.price_per_night FROM rooms r 
            JOIN room_types rt ON r.room_type_id = rt.id 
            WHERE r.status = 'available' 
            AND r.id NOT IN (
                SELECT room_id FROM bookings 
                WHERE status IN ('confirmed', 'checked_in')
                AND (
                    (check_in <= ? AND check_out >= ?) OR
                    (check_in <= ? AND check_out >= ?) OR
                    (check_in >= ? AND check_out <= ?)
                )
            )";

    $params = [$check_out, $check_in, $check_in, $check_out, $check_in, $check_out];

    if ($room_type_id) {
        $sql .= " AND r.room_type_id = ?";
        $params[] = $room_type_id;
    }

    $sql .= " ORDER BY r.room_number";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function addRoom($pdo, $data) {
    $sql = "INSERT INTO rooms (room_number, room_type_id, floor, status, features) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['room_number'],
        $data['room_type_id'],
        $data['floor'],
        $data['status'],
        $data['features']
    ]);
}

function updateRoom($pdo, $id, $data) {
    $sql = "UPDATE rooms SET room_number = ?, room_type_id = ?, floor = ?, status = ?, features = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['room_number'],
        $data['room_type_id'],
        $data['floor'],
        $data['status'],
        $data['features'],
        $id
    ]);
}

function deleteRoom($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE id = ?");
    return $stmt->execute([$id]);
}

// Guest Functions
function getAllGuests($pdo, $search = '') {
    $sql = "SELECT * FROM guests 
            WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?
            ORDER BY last_name, first_name";
    $searchTerm = "%$search%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    return $stmt->fetchAll();
}

function getGuestById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM guests WHERE id = ?");
    $stmt->execute([$id]);
    $guest = $stmt->fetch();

    // Ensure all fields have default values
    if ($guest) {
        $guest['email'] = $guest['email'] ?? '';
        $guest['address'] = $guest['address'] ?? '';
        $guest['id_number'] = $guest['id_number'] ?? '';
        $guest['country'] = $guest['country'] ?? 'Ghana';
    }

    return $guest;
}

function addGuest($pdo, $data) {
    $sql = "INSERT INTO guests (first_name, last_name, email, phone, address, id_type, id_number, country) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'],
        $data['address'],
        $data['id_type'],
        $data['id_number'],
        $data['country']
    ]);
}

function updateGuest($pdo, $id, $data) {
    $sql = "UPDATE guests SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, 
            id_type = ?, id_number = ?, country = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'],
        $data['address'],
        $data['id_type'],
        $data['id_number'],
        $data['country'],
        $id
    ]);
}

function deleteGuest($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM guests WHERE id = ?");
    return $stmt->execute([$id]);
}

// Booking Functions
function getAllBookings($pdo, $search = '') {
    $sql = "SELECT b.*, g.first_name, g.last_name, g.email, g.phone,
            r.room_number, rt.type_name,
            (SELECT SUM(amount) FROM payments p WHERE p.booking_id = b.id AND p.status = 'completed') as paid_amount
            FROM bookings b
            JOIN guests g ON b.guest_id = g.id
            JOIN rooms r ON b.room_id = r.id
            JOIN room_types rt ON r.room_type_id = rt.id
            WHERE g.first_name LIKE ? OR g.last_name LIKE ? OR r.room_number LIKE ?
            ORDER BY b.created_at DESC";
    $searchTerm = "%$search%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    return $stmt->fetchAll();
}

function getBookingById($pdo, $id) {
    $sql = "SELECT b.*, g.first_name, g.last_name, g.email, g.phone, g.address, g.id_type, g.id_number, g.country,
            r.room_number, r.floor, r.status as room_status,
            rt.type_name, rt.price_per_night, rt.capacity, rt.amenities
            FROM bookings b
            JOIN guests g ON b.guest_id = g.id
            JOIN rooms r ON b.room_id = r.id
            JOIN room_types rt ON r.room_type_id = rt.id
            WHERE b.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $booking = $stmt->fetch();

    // Ensure all fields have default values
    if ($booking) {
        $booking['special_requests'] = $booking['special_requests'] ?? '';
        $booking['amenities'] = $booking['amenities'] ?? '';
        $booking['address'] = $booking['address'] ?? '';
        $booking['id_number'] = $booking['id_number'] ?? '';
    }

    return $booking;
}



function calculateBookingAmount($room_price, $check_in, $check_out) {
    $nights = (strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24);
    return $nights * $room_price;
}

function addBooking($pdo, $data) {
    // Get room price
    $roomStmt = $pdo->prepare("SELECT rt.price_per_night FROM rooms r 
                               JOIN room_types rt ON r.room_type_id = rt.id 
                               WHERE r.id = ?");
    $roomStmt->execute([$data['room_id']]);
    $room = $roomStmt->fetch();

    $total_amount = calculateBookingAmount($room['price_per_night'], $data['check_in'], $data['check_out']);

    $sql = "INSERT INTO bookings (guest_id, room_id, check_in, check_out, adults, children, special_requests, status, total_amount) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $data['guest_id'],
        $data['room_id'],
        $data['check_in'],
        $data['check_out'],
        $data['adults'],
        $data['children'],
        $data['special_requests'],
        $data['status'],
        $total_amount
    ]);

    if ($result && isset($data['payment_method'])) {
        $booking_id = $pdo->lastInsertId();
        $payment_sql = "INSERT INTO payments (booking_id, amount, payment_method, status) VALUES (?, ?, ?, 'completed')";
        $payment_stmt = $pdo->prepare($payment_sql);
        $payment_stmt->execute([$booking_id, $total_amount, $data['payment_method']]);
    }

    return $result;
}

function updateBookingStatus($pdo, $id, $status) {
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    return $stmt->execute([$status, $id]);
}

function deleteBooking($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    return $stmt->execute([$id]);
}

// Payment Functions
function getAllPayments($pdo, $search = '') {
    $sql = "SELECT p.*, b.id as booking_id, b.check_in, b.check_out,
            g.first_name, g.last_name, g.email, r.room_number
            FROM payments p
            JOIN bookings b ON p.booking_id = b.id
            JOIN guests g ON b.guest_id = g.id
            JOIN rooms r ON b.room_id = r.id
            WHERE g.first_name LIKE ? OR g.last_name LIKE ? OR p.transaction_id LIKE ?
            ORDER BY p.payment_date DESC";
    $searchTerm = "%$search%";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
    return $stmt->fetchAll();
}

function getPaymentById($pdo, $id) {
    $sql = "SELECT p.*, b.id as booking_id, b.check_in, b.check_out, b.total_amount,
            g.first_name, g.last_name, g.email, g.phone, r.room_number
            FROM payments p
            JOIN bookings b ON p.booking_id = b.id
            JOIN guests g ON b.guest_id = g.id
            JOIN rooms r ON b.room_id = r.id
            WHERE p.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function addPayment($pdo, $booking_id, $amount, $payment_method, $transaction_id = null) {
    $sql = "INSERT INTO payments (booking_id, amount, payment_method, transaction_id, status) 
            VALUES (?, ?, ?, ?, 'completed')";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$booking_id, $amount, $payment_method, $transaction_id]);
}

function updatePayment($pdo, $id, $data) {
    $sql = "UPDATE payments SET amount = ?, payment_method = ?, transaction_id = ?, status = ?, notes = ? 
            WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([
        $data['amount'],
        $data['payment_method'],
        $data['transaction_id'],
        $data['status'],
        $data['notes'],
        $id
    ]);
}

function deletePayment($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM payments WHERE id = ?");
    return $stmt->execute([$id]);
}

// Dashboard Statistics
function getDashboardStats($pdo) {
    $stats = [];

    // Total bookings today
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE DATE(created_at) = CURDATE()");
    $stats['bookings_today'] = $stmt->fetch()['count'];

    // Current guests
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'checked_in'");
    $stats['current_guests'] = $stmt->fetch()['count'];

    // Available rooms
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM rooms WHERE status = 'available'");
    $stats['available_rooms'] = $stmt->fetch()['count'];

    // Today's revenue
    $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE DATE(payment_date) = CURDATE() AND status = 'completed'");
    $stats['today_revenue'] = $stmt->fetch()['total'] ?? 0;

    // Total bookings
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
    $stats['total_bookings'] = $stmt->fetch()['count'];

    // Total guests
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM guests");
    $stats['total_guests'] = $stmt->fetch()['count'];

    return $stats;
}