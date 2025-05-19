<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../public/login.php');
}

// Check if vehicle ID and status are provided
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['status']) || empty($_GET['status'])) {
    redirect('vehicles.php');
}

$vehicleId = (int) $_GET['id'];
$status = sanitize($_GET['status']);

// Validate status
$validStatuses = ['Available', 'Booked', 'Maintenance'];
if (!in_array($status, $validStatuses)) {
    redirect('vehicles.php');
}

$vehicle = getVehicleById($vehicleId);

// If vehicle not found, redirect to vehicles 