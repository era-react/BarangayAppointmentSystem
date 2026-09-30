<?php

// Start the session
session_start();

// Database settings
$host = "localhost";
$username = "root";
$password = "";
$database = "barangay_appointment";

// Create database connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set character encoding
$conn->set_charset("utf8mb4");

// Helper function for safely displaying text
function e($value)
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Check if user is logged in
function require_login()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: /BarangayAppointmentSystem/login.php");
        exit;
    }
}

// Check if logged-in user is staff or admin
function require_staff()
{
    require_login();

    if (
        $_SESSION['role'] !== 'staff' &&
        $_SESSION['role'] !== 'admin'
    ) {
        header("Location: /BarangayAppointmentSystem/index.php");
        exit;
    }
}

// Check if logged-in user is admin
function require_admin()
{
    require_login();

    if ($_SESSION['role'] !== 'admin') {
        header("Location: /BarangayAppointmentSystem/index.php");
        exit;
    }
}

// Check if logged-in user is a resident
function require_resident()
{
    require_login();

    if ($_SESSION['role'] !== 'resident') {
        header("Location: /BarangayAppointmentSystem/index.php");
        exit;
    }
}
?>