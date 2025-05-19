<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../public/login.php');
}

// Set admin page flag
$isAdminPage = true;

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('bookings.php');
}

$bookingId = (int) $_GET['id'];
$booking = getBookingById($bookingId);

// If booking not found, redirect to bookings page
if (!$booking) {
    redirect('bookings.php');
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Booking Details</h2>
    <div>
        <a href="booking-edit.php?id=<?php echo $bookingId; ?>" class="btn btn-primary me-2">
            <i class="fas fa-edit me-1"></i> Edit Booking
        </a>
        <a href="bookings.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Bookings
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Booking #<?php echo $booking['id']; ?></h5>
                <div>
                    <?php
                    $statusClass = '';
                    switch ($booking['booking_status']) {
                        case 'Pending':
                            $statusClass = 'bg-warning';
                            break;
                        case 'Confirmed':
                            $statusClass = 'bg-primary';
                            break;
                        case 'Completed':
                            $statusClass = 'bg-success';
                            break;
                        case 'Cancelled':
                            $statusClass = 'bg-danger';
                            break;
                    }
                    ?>
                    <span class="badge <?php echo $statusClass; ?>"><?php echo $booking['booking_status']; ?></span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Customer Information</h6>
                        <p class="mb-1"><strong>Name:</strong> <?php echo $booking['customer_name']; ?></p>
                        <p class="mb-1"><strong>Email:</strong> <?php echo $booking['customer_email']; ?></p>
                        <p class="mb-0"><strong>Booking Date:</strong>
                            <?php echo date('F j, Y', strtotime($booking['created_at'])); ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Vehicle Information</h6>
                        <p class="mb-1"><strong>Vehicle:</strong>
                            <?php echo $booking['make'] . ' ' . $booking['model']; ?></p>
                        <p class="mb-1"><strong>Registration:</strong> <?php echo $booking['registration_number']; ?>
                        </p>
                        <p class="mb-0"><strong>Daily Rate:</strong> $<?php echo $booking['daily_rate']; ?></p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Pickup Details</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Date:</strong>
                                    <?php echo date('F j, Y', strtotime($booking['pickup_date'])); ?></p>
                                <p class="mb-0"><strong>Location:</strong> <?php echo $booking['pickup_location']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Return Details</h6>
                            </div>
                            <div class="card-body">
                                <p class="mb-1"><strong>Date:</strong>
                                    <?php echo date('F j, Y', strtotime($booking['return_date'])); ?></p>
                                <p class="mb-0"><strong>Location:</strong> <?php echo $booking['return_location']; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0">Payment Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Daily Rate:</strong> $<?php echo $booking['daily_rate']; ?></p>
                                <p class="mb-1"><strong>Number of Days:</strong>
                                    <?php echo calculateDays($booking['pickup_date'], $booking['return_date']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Total Amount:</strong> $<?php echo $booking['total_amount']; ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Payment Status:</strong>
                                    <span
                                        class="badge <?php echo ($booking['booking_status'] === 'Confirmed' || $booking['booking_status'] === 'Completed') ? 'bg-success' : 'bg-warning'; ?>">
                                        <?php echo ($booking['booking_status'] === 'Confirmed' || $booking['booking_status'] === 'Completed') ? 'Paid' : 'Pending'; ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($booking['booking_status'] === 'Pending'): ?>
                <div class="card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">Payment Required</h6>
                    </div>
                    <div class="card-body">
                        <p>This booking requires payment to be confirmed. You can:</p>
                        <div class="d-flex gap-2">
                            <a href="booking-payment.php?id=<?php echo $bookingId; ?>" class="btn btn-success">
                                <i class="fas fa-credit-card me-1"></i> Process Payment
                            </a>
                            <a href="booking-status-update.php?id=<?php echo $bookingId; ?>&status=Confirmed"
                                class="btn btn-primary">
                                <i class="fas fa-check me-1"></i> Mark as Confirmed
                            </a>
                            <a href="booking-status-update.php?id=<?php echo $bookingId; ?>&status=Cancelled"
                                class="btn btn-danger">
                                <i class="fas fa-times me-1"></i> Cancel Booking
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="booking-edit.php?id=<?php echo $bookingId; ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Booking
                    </a>

                    <?php if ($booking['booking_status'] === 'Pending'): ?>
                    <a href="booking-status-update.php?id=<?php echo $bookingId; ?>&status=Confirmed"
                        class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Mark as Confirmed
                    </a>
                    <?php elseif ($booking['booking_status'] === 'Confirmed'): ?>
                    <a href="booking-status-update.php?id=<?php echo $bookingId; ?>&status=Completed"
                        class="btn btn-success">
                        <i class="fas fa-check-double me-1"></i> Mark as Completed
                    </a>
                    <?php endif; ?>

                    <?php if ($booking['booking_status'] !== 'Cancelled' && $booking['booking_status'] !== 'Completed'): ?>
                    <a href="booking-status-update.php?id=<?php echo $bookingId; ?>&status=Cancelled"
                        class="btn btn-danger">
                        <i class="fas fa-times me-1"></i> Cancel Booking
                    </a>
                    <?php endif; ?>

                    <button class="btn btn-outline-primary" onclick="window.print();">
                        <i class="fas fa-print me-1"></i> Print Booking
                    </button>

                    <a href="invoice-generate.php?id=<?php echo $bookingId; ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-file-invoice me-1"></i> Generate Invoice
                    </a>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Booking Timeline</h5>
            </div>
            <div class="card-body">
                <ul class="timeline">
                    <li class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Booking Created</h6>
                            <p class="timeline-date">
                                <?php echo date('F j, Y, g:i a', strtotime($booking['created_at'])); ?></p>
                        </div>
                    </li>

                    <?php if ($booking['booking_status'] !== 'Pending'): ?>
                    <li class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Booking <?php echo $booking['booking_status']; ?></h6>
                            <p class="timeline-date"><?php echo date('F j, Y', strtotime($booking['updated_at'])); ?>
                            </p>
                        </div>
                    </li>
                    <?php endif; ?>

                    <li class="timeline-item">
                        <div
                            class="timeline-marker <?php echo strtotime($booking['pickup_date']) <= strtotime('now') ? 'bg-success' : 'bg-secondary'; ?>">
                        </div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Pickup Date</h6>
                            <p class="timeline-date"><?php echo date('F j, Y', strtotime($booking['pickup_date'])); ?>
                            </p>
                        </div>
                    </li>

                    <li class="timeline-item">
                        <div
                            class="timeline-marker <?php echo strtotime($booking['return_date']) <= strtotime('now') ? 'bg-success' : 'bg-secondary'; ?>">
                        </div>
                        <div class="timeline-content">
                            <h6 class="timeline-title">Return Date</h6>
                            <p class="timeline-date"><?php echo date('F j, Y', strtotime($booking['return_date'])); ?>
                            </p>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<style>
/* Timeline styles */
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
    margin: 0;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 11px;
    width: 2px;
    background-color: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-left: 30px;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    top: 0;
    left: 0;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background-color: #3498db;
    z-index: 100;
}

.timeline-content {
    padding-bottom: 5px;
}

.timeline-title {
    margin-bottom: 0;
}

.timeline-date {
    margin-bottom: 0;
    color: #6c757d;
    font-size: 0.85rem;
}
</style>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>