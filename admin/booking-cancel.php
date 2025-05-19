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
    // If admin, log the action
    if (isAdmin()) {
        logAdminAction('Cancelled booking #' . $bookingId);
        
        // Optional: Send notification email to customer
        $userEmail = $booking['customer_email'];
        $subject = "Booking Cancellation - #" . $bookingId;
        $message = "Dear " . $booking['customer_name'] . ",\n\n";
        $message .= "Your booking #" . $bookingId . " for " . $booking['make'] . " " . $booking['model'];
        $message .= " has been cancelled by an administrator.\n\n";
        $message .= "If you have any questions, please contact our customer service team.\n\n";
        $message .= "Thank you for choosing our service.\n";
        
        // Uncomment to enable email sending
        // mail($userEmail, $subject, $message);
        
        // Set success message
        $_SESSION['success_message'] = 'Booking has been cancelled successfully.';
        redirect('booking-details.php?id=' . $bookingId);
    } else {
        // Set success message for customer
        $_SESSION['success_message'] = 'Your booking has been cancelled successfully.';
        redirect('my-bookings.php');
    }
} else {
    // Set error message
    $_SESSION['error_message'] = 'Failed to cancel booking. Please try again or contact support.';
    
    // Redirect based on user role
    if (isAdmin()) {
        redirect('booking-details.php?id=' . $bookingId);
    } else {
        redirect('my-bookings.php');
    }
}

/**
 * Helper function to log admin actions
 * You would need to create this table in your database
 */
function logAdminAction($action) {
    global $conn;
    
    $adminId = getCurrentUserId();
    $action = $conn->real_escape_string($action);
    
    $sql = "INSERT INTO admin_logs (admin_id, action, created_at) 
            VALUES ('{$adminId}', '{$action}', NOW())";
    
    return $conn->query($sql);
}