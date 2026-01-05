<?php
// Database configuration
const DB_HOST = 'localhost';
const DB_USERNAME = 'root';
const DB_PASSWORD = '402069';
const DB_NAME = 'hotel_management';

// Application settings
const CURRENCY = 'GH₵';
const APP_NAME = 'Ghana Hotel Management';
const TIMEZONE = 'Africa/Accra';

// Set timezone
date_default_timezone_set(TIMEZONE);

// Start session
session_start();

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USERNAME,
        DB_PASSWORD,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Function to format currency
function formatCurrency($amount) {
    return CURRENCY . number_format($amount, 2);
}

// Function to sanitize input - UPDATED TO HANDLE NULL
function sanitize($data) {
    if ($data === null) {
        return '';
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to safely escape HTML output - NEW FUNCTION
function escape($data) {
    if ($data === null) {
        return '';
    }
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Redirect function
function redirect($url) {
    header("Location: $url");
    exit();
}

// Get base URL
function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    $base = $protocol . '://' . $host . dirname($script);
    $base = rtrim($base, '/') . '/';
    return $base . ltrim($path, '/');
}
?>