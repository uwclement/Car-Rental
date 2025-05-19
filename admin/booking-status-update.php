<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../public/login.php');
}

// Set admin page flag
$isAdminPage = true;

// Check if booking ID and status are provided
if (!isset($_GET['id']) || empty($_GET['id']) || !isset($_GET['status']) || empty($_GET['status'])) {
    $_SESSION['error_message'] = "Missing booking ID or status parameter";
    redirect('bookings.php');
}

$bookingId = (int) $_GET['id'];
$newStatus = sanitize($_GET['status']);

// Validate status value
$validStatuses = ['Pending', 'Confirmed', 'Completed', 'Cancelled'];
if (!in_array($newStatus, $validStatuses)) {
    $_SESSION['error_message'] = "Invalid status value";
    redirect("booking-view.php?id=$bookingId");
}

// Get current booking details
$booking = getBookingById($bookingId);

// If booking not found, redirect to bookings page
if (!$booking) {
    $_SESSION['error_message'] = "Booking not found";
    redirect('bookings.php');
}

// If booking status is already the requested status, redirect back with a message
if ($booking['booking_status'] === $newStatus) {
    $_SESSION['info_message'] = "Booking is already marked as $newStatus";
    redirect("booking-view.php?id=$bookingId");
}

// Process specific actions based on the status change
switch ($newStatus) {
    case 'Confirmed':
        // Update booking status
        if (updateBookingStatus($bookingId, 'Confirmed')) {
            // Set success message
            $_SESSION['success_message'] = "Booking has been confirmed successfully";
            
            // Send confirmation email to customer
            $customer = getUserById($booking['user_id']);
            if ($customer) {
                sendBookingStatusEmail($customer['email'], $booking, 'confirmed');
            }
        } else {
            $_SESSION['error_message'] = "Failed to confirm booking";
        }
        break;
        
    case 'Completed':
        // Update booking status
        if (updateBookingStatus($bookingId, 'Completed')) {
            // Set success message
            $_SESSION['success_message'] = "Booking has been marked as completed";
            
            // Send completion email to customer
            $customer = getUserById($booking['user_id']);
            if ($customer) {
                sendBookingStatusEmail($customer['email'], $booking, 'completed');
            }
        } else {
            $_SESSION['error_message'] = "Failed to complete booking";
        }
        break;
        
    case 'Cancelled':
        // Show confirmation page if not confirmed
        if (!isset($_GET['confirm']) || $_GET['confirm'] !== 'yes') {
            // Include header
            include_once __DIR__ . '/../includes/header.php';
            ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">Confirm Cancellation</h5>
                </div>
                <div class="card-body">
                    <h6 class="card-title">Are you sure you want to cancel booking #<?php echo $bookingId; ?>?</h6>
                    <p>This will:</p>
                    <ul>
                        <li>Update the booking status to "Cancelled"</li>
                        <li>Make the vehicle available for other bookings</li>
                        <li>Notify the customer of the cancellation</li>
                    </ul>

                    <p class="mb-0"><strong>Booking Details:</strong></p>
                    <ul>
                        <li>Customer: <?php echo $booking['customer_name']; ?></li>
                        <li>Vehicle: <?php echo $booking['make'] . ' ' . $booking['model']; ?></li>
                        <li>Pickup: <?php echo date('F j, Y', strtotime($booking['pickup_date'])); ?></li>
                        <li>Total Amount: $<?php echo $booking['total_amount']; ?></li>
                    </ul>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This action cannot be undone. The customer will be notified automatically.
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <a href="booking-status-update.php?id=<?php echo $bookingId; ?>&status=Cancelled&confirm=yes"
                            class="btn btn-danger">
                            <i class="fas fa-check me-1"></i> Yes, Cancel Booking
                        </a>
                        <a href="booking-view.php?id=<?php echo $bookingId; ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> No, Go Back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
            // Include footer
            include_once __DIR__ . '/../includes/footer.php';
            exit;
        }
        
        // Update booking status if confirmation is provided
        if (updateBookingStatus($bookingId, 'Cancelled')) {
            // Optionally: Record cancellation reason if provided
            if (isset($_GET['reason']) && !empty($_GET['reason'])) {
                $reason = sanitize($_GET['reason']);
                // We'll store this in a session message since we may not have a booking_notes table
                $_SESSION['cancellation_reason'] = "Booking #{$bookingId} cancelled. Reason: {$reason}";
            }
            
            // Set success message
            $_SESSION['success_message'] = "Booking has been cancelled successfully";
            
            // Send cancellation email to customer
            $customer = getUserById($booking['user_id']);
            if ($customer) {
                sendBookingStatusEmail($customer['email'], $booking, 'cancelled');
            }
        } else {
            $_SESSION['error_message'] = "Failed to cancel booking";
        }
        break;
        
    case 'Pending':
        // Update booking status
        if (updateBookingStatus($bookingId, 'Pending')) {
            // Set success message
            $_SESSION['success_message'] = "Booking has been marked as pending";
        } else {
            $_SESSION['error_message'] = "Failed to update booking status";
        }
        break;
}

// Redirect back to booking view page
redirect("booking-view.php?id=$bookingId");

/**
 * Send email notification about booking status change
 * @param string $email
 * @param array $booking
 * @param string $status
 * @return bool
 */
function sendBookingStatusEmail($email, $booking, $status) {
    $to = $email;
    $subject = "Car Rental Booking #" . $booking['id'] . " - Status Update";
    
    // Set email content based on status
    switch ($status) {
        case 'confirmed':
            $message = "Dear " . $booking['customer_name'] . ",\n\n";
            $message .= "Your car rental booking has been confirmed!\n\n";
            $message .= "Booking Details:\n";
            $message .= "Booking ID: " . $booking['id'] . "\n";
            $message .= "Vehicle: " . $booking['make'] . " " . $booking['model'] . "\n";
            $message .= "Pickup Date: " . date('F j, Y', strtotime($booking['pickup_date'])) . "\n";
            $message .= "Pickup Location: " . $booking['pickup_location'] . "\n";
            $message .= "Return Date: " . date('F j, Y', strtotime($booking['return_date'])) . "\n";
            $message .= "Return Location: " . $booking['return_location'] . "\n";
            $message .= "Total Amount: $" . $booking['total_amount'] . "\n\n";
            $message .= "Please arrive on time for your pickup. Don't forget to bring your valid driving license and the payment card used for booking.\n\n";
            $message .= "Thank you for choosing our service!\n\n";
            $message .= "Best regards,\n";
            $message .= "Car Rental Team";
            break;
            
        case 'completed':
            $message = "Dear " . $booking['customer_name'] . ",\n\n";
            $message .= "Your car rental booking has been marked as completed.\n\n";
            $message .= "Booking Details:\n";
            $message .= "Booking ID: " . $booking['id'] . "\n";
            $message .= "Vehicle: " . $booking['make'] . " " . $booking['model'] . "\n";
            $message .= "Pickup Date: " . date('F j, Y', strtotime($booking['pickup_date'])) . "\n";
            $message .= "Return Date: " . date('F j, Y', strtotime($booking['return_date'])) . "\n";
            $message .= "Total Amount: $" . $booking['total_amount'] . "\n\n";
            $message .= "Thank you for choosing our service. We hope you had a great experience and look forward to serving you again soon!\n\n";
            $message .= "We would appreciate it if you could take a moment to rate your experience and provide any feedback.\n\n";
            $message .= "Best regards,\n";
            $message .= "Car Rental Team";
            break;
            
        case 'cancelled':
            $message = "Dear " . $booking['customer_name'] . ",\n\n";
            $message .= "Your car rental booking has been cancelled.\n\n";
            $message .= "Booking Details:\n";
            $message .= "Booking ID: " . $booking['id'] . "\n";
            $message .= "Vehicle: " . $booking['make'] . " " . $booking['model'] . "\n";
            $message .= "Pickup Date: " . date('F j, Y', strtotime($booking['pickup_date'])) . "\n";
            $message .= "Return Date: " . date('F j, Y', strtotime($booking['return_date'])) . "\n\n";
            $message .= "If you've already made any payments, they will be refunded according to our cancellation policy.\n\n";
            $message .= "If you did not request this cancellation or if you have any questions, please contact our customer support.\n\n";
            $message .= "Best regards,\n";
            $message .= "Car Rental Team";
            break;
            
        default:
            $message = "Dear " . $booking['customer_name'] . ",\n\n";
            $message .= "There has been an update to your car rental booking.\n\n";
            $message .= "Booking Details:\n";
            $message .= "Booking ID: " . $booking['id'] . "\n";
            $message .= "Vehicle: " . $booking['make'] . " " . $booking['model'] . "\n";
            $message .= "Current Status: " . $booking['booking_status'] . "\n\n";
            $message .= "If you have any questions, please contact our customer support.\n\n";
            $message .= "Best regards,\n";
            $message .= "Car Rental Team";
    }
    
    $headers = "From: Car Rental <noreply@carrentals.com>\r\n";
    
    // For a real application, you would set up a proper email system
    // This is just a placeholder
    return true; // Simulate successful email sending
    
    // Uncomment the line below to actually send email in a production environment
    // return mail($to, $subject, $message, $headers);
}
?>