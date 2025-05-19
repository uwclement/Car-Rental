<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('my-bookings.php');
}

$bookingId = (int) $_GET['id'];
$booking = getBookingById($bookingId);

// Verify booking exists and belongs to the current user or is admin
if (!$booking || ($booking['user_id'] != getCurrentUserId() && !isAdmin())) {
    redirect('my-bookings.php');
}

// Check if booking can be cancelled (only Pending bookings can be cancelled by customers)
if (!isAdmin() && $booking['booking_status'] !== 'Pending') {
    // Set error message
    $_SESSION['error_message'] = 'Only pending bookings can be cancelled.';
    redirect('booking-details.php?id=' . $bookingId);
}

// Process cancellation
if (updateBookingStatus($bookingId, 'Cancelled')) {
    // Set success message
    $_SESSION['success_message'] = 'Your booking has been cancelled successfully.';
} else {
    // Set error message
    $_SESSION['error_message'] = 'Failed to cancel booking. Please try again or contact support.';
}

// Redirect back to bookings page
redirect('my-bookings.php');