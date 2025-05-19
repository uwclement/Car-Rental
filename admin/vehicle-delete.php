<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../public/login.php');
}

// Check if vehicle ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('vehicles.php');
}

$vehicleId = (int) $_GET['id'];
$vehicle = getVehicleById($vehicleId);

// If vehicle not found, redirect to vehicles page
if (!$vehicle) {
    redirect('vehicles.php');
}

// Check if vehicle has active bookings
$activeBookings = false;
$bookings = getAllBookings(['vehicle_id' => $vehicleId, 'status' => 'Confirmed']);
if (count($bookings) > 0) {
    $activeBookings = true;
}

// Process deletion request
if (!$activeBookings) {
    // Delete vehicle image if exists
    if (!empty($vehicle['image'])) {
        $imagePath = __DIR__ . '/../assets/images/cars/' . $vehicle['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }
    
    // Delete vehicle
    if (deleteVehicle($vehicleId)) {
        // Set success message
        $_SESSION['success_message'] = 'Vehicle has been deleted successfully.';
    } else {
        // Set error message
        $_SESSION['error_message'] = 'Failed to delete vehicle. Please try again.';
    }
} else {
    // Set error message
    $_SESSION['error_message'] = 'Cannot delete vehicle with active bookings. Please cancel or complete those bookings first.';
}

// Redirect back to vehicles page
redirect('vehicles.php');