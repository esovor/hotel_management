<?php
require_once 'config.php';
require_once 'functions.php';

// Handle actions
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? 0;

if ($id) {
    switch ($action) {
        case 'checkin':
            updateBookingStatus($pdo, $id, 'checked_in');
            // Update room status
            $stmt = $pdo->prepare("UPDATE rooms r 
                                  JOIN bookings b ON r.id = b.room_id 
                                  SET r.status = 'occupied' 
                                  WHERE b.id = ?");
            $stmt->execute([$id]);
            header('Location: index.php?success=Guest checked in successfully');
            exit();

        case 'checkout':
            updateBookingStatus($pdo, $id, 'checked_out');
            // Update room status to available
            $stmt = $pdo->prepare("UPDATE rooms r 
                                  JOIN bookings b ON r.id = b.room_id 
                                  SET r.status = 'available' 
                                  WHERE b.id = ?");
            $stmt->execute([$id]);
            header('Location: index.php?success=Guest checked out successfully');
            exit();

        case 'cancel':
            updateBookingStatus($pdo, $id, 'cancelled');
            // Update room status to available if it was occupied
            $stmt = $pdo->prepare("UPDATE rooms r 
                                  JOIN bookings b ON r.id = b.room_id 
                                  SET r.status = 'available' 
                                  WHERE b.id = ? AND b.status = 'confirmed'");
            $stmt->execute([$id]);
            header('Location: index.php?success=Booking cancelled successfully');
            exit();

        case 'delete':
            deleteBooking($pdo, $id);
            header('Location: index.php?success=Booking deleted successfully');
            exit();

        default:
            header('Location: index.php?error=Invalid action');
            exit();
    }
} else {
    header('Location: index.php?error=No booking specified');
    exit();
}

