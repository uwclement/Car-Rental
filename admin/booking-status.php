<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../public/login.php');
}

// Check if booking ID and status are provided
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['status']) || empty($_GET['status'])) {
    redirect('bookings.php');
}

$bookingId = (int) $_GET['id'];
$status = sanitize($_GET['status']);

// Validate status
$validStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
if (!in_array($status, $validStatuses)) {
    redirect('bookings.php');
}

$booking = getBookingById($bookingId);

// If booking not found, redirect to bookings page
if (!$booking) {
    redirect('bookings.php');
}

// Update booking status
if (updateBookingStatus($bookingId, $status)) {
    // Set success message
    $_SESSION['success_message'] = 'Booking status updated to ' . $status . '.';
} else {
    // Set error message
    $_SESSION['error_message'] = 'Failed to update booking status. Please try again.';
}

// Redirect back to booking view page
redirect('booking-view.php?id=' . $bookingId);