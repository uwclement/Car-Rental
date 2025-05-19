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

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Booking Details</h4>
                    <a href="my-bookings.php" class="btn btn-light btn-sm">
                        <i class="fas fa-arrow-left me-1"></i> Back to Bookings
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <h3>Booking #<?php echo $booking['id']; ?></h3>
                            <div class="mb-3">
                                <span class="badge 
                                    <?php 
                                    switch ($booking['booking_status']) {
                                        case 'Pending':
                                            echo 'bg-warning';
                                            break;
                                        case 'Confirmed':
                                            echo 'bg-primary';
                                            break;
                                        case 'Completed':
                                            echo 'bg-success';
                                            break;
                                        case 'Cancelled':
                                            echo 'bg-danger';
                                            break;
                                    }
                                    ?>">
                                    <?php echo $booking['booking_status']; ?>
                                </span>
                            </div>
                            <p class="text-muted">Booking Date:
                                <?php echo date('F j, Y', strtotime($booking['created_at'])); ?></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <?php if ($booking['booking_status'] === 'Pending'): ?>
                            <a href="booking-cancel.php?id=<?php echo $booking['id']; ?>" class="btn btn-danger"
                                onclick="return confirm('Are you sure you want to cancel this booking?');">
                                <i class="fas fa-times me-1"></i> Cancel Booking
                            </a>
                            <?php elseif ($booking['booking_status'] === 'Confirmed'): ?>
                            <button class="btn btn-success" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Print Receipt
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Vehicle Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex mb-3">
                                        <div class="me-3">
                                            <img src="<?php echo !empty($booking['image']) ? '../assets/images/cars/' . $booking['image'] : '../assets/images/cars/default-car.jpg'; ?>"
                                                class="img-fluid rounded"
                                                alt="<?php echo $booking['make'] . ' ' . $booking['model']; ?>"
                                                style="width: 120px;">
                                        </div>
                                        <div>
                                            <h5><?php echo $booking['make'] . ' ' . $booking['model']; ?></h5>
                                            <p class="mb-1">Registration: <?php echo $booking['registration_number']; ?>
                                            </p>
                                            <p class="mb-0">Daily Rate: $<?php echo $booking['daily_rate']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Rental Period</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <h6>Pickup Date</h6>
                                            <p class="mb-0">
                                                <?php echo date('F j, Y', strtotime($booking['pickup_date'])); ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Return Date</h6>
                                            <p class="mb-0">
                                                <?php echo date('F j, Y', strtotime($booking['return_date'])); ?></p>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Pickup Location</h6>
                                            <p class="mb-0"><?php echo $booking['pickup_location']; ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Return Location</h6>
                                            <p class="mb-0"><?php echo $booking['return_location']; ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Customer Information</h5>
                                </div>
                                <div class="card-body">
                                    <p class="mb-1"><strong>Name:</strong> <?php echo $booking['customer_name']; ?></p>
                                    <p class="mb-1"><strong>Email:</strong> <?php echo $booking['customer_email']; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Payment Summary</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Daily Rate:</span>
                                        <span>$<?php echo $booking['daily_rate']; ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Number of Days:</span>
                                        <span><?php echo calculateDays($booking['pickup_date'], $booking['return_date']); ?></span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-2">
                                        <strong>Total Amount:</strong>
                                        <strong>$<?php echo $booking['total_amount']; ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>Payment Status:</span>
                                        <span
                                            class="badge <?php echo $booking['booking_status'] === 'Confirmed' || $booking['booking_status'] === 'Completed' ? 'bg-success' : 'bg-warning'; ?>">
                                            <?php echo $booking['booking_status'] === 'Confirmed' || $booking['booking_status'] === 'Completed' ? 'Paid' : 'Pending'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Rental Terms & Policy</h5>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li>Please bring your driver's license and credit card when picking up the vehicle.</li>
                                <li>Fuel policy: Same-to-same (return with the same fuel level).</li>
                                <li>Mileage: Unlimited.</li>
                                <li>Insurance: Basic insurance is included in the rental price.</li>
                                <li>Cancellation: Free up to 24 hours before pickup. Late cancellations may incur a fee.
                                </li>
                                <li>For any issues or emergencies during your rental, please call our customer service
                                    at 1-800-CARRENT.</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">Thank you for choosing our car rental service!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>