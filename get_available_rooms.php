<?php
require_once 'config.php';
require_once 'functions.php';

header('Content-Type: application/json');

// Get parameters
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$room_type_id = isset($_GET['room_type']) && $_GET['room_type'] !== '' ? $_GET['room_type'] : null;

// Validate dates
if (empty($check_in) || empty($check_out)) {
    echo json_encode(['error' => 'Check-in and check-out dates are required']);
    exit();
}

if (strtotime($check_out) <= strtotime($check_in)) {
    echo json_encode(['error' => 'Check-out date must be after check-in date']);
    exit();
}

try {
    // Get available rooms
    $availableRooms = getAvailableRooms($pdo, $check_in, $check_out, $room_type_id);

    // Format response
    $response = [];
    foreach ($availableRooms as $room) {
        $response[] = [
            'id' => $room['id'],
            'room_number' => $room['room_number'],
            'type_name' => $room['type_name'],
            'floor' => $room['floor'],
            'price_per_night' => $room['price_per_night'],
            'capacity' => $room['capacity'] ?? null,
            'features' => $room['features']
        ];
    }

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error fetching available rooms: ' . $e->getMessage()]);
}

