<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=my-bookings.php');
}

// Check if admin is trying to access
if (isAdmin()) {
    redirect('../admin/index.php');
}

// Get user bookings
$userId = getCurrentUserId();
$userBookings = getUserBookings($userId);

// Process filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
if (!empty($statusFilter)) {
    $userBookings = array_filter($userBookings, function($booking) use ($statusFilter) {
        return $booking['booking_status'] === $statusFilter;
    });
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">My Bookings</h2>
            <div>
                <a href="vehicles.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> New Booking
                </a>
            </div>
        </div>

        <!-- Display success or error messages if set -->
        <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; ?></div>
        <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; ?></div>
        <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <!-- Filter Controls -->
        <div class="card mb-4">
            <div class="card-body">
                <form action="my-bookings.php" method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Filter by Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Bookings</option>
                            <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending
                            </option>
                            <option value="Confirmed" <?php echo $statusFilter === 'Confirmed' ? 'selected' : ''; ?>>
                                Confirmed</option>
                            <option value="Completed" <?php echo $statusFilter === 'Completed' ? 'selected' : ''; ?>>
                                Completed</option>
                            <option value="Cancelled" <?php echo $statusFilter === 'Cancelled' ? 'selected' : ''; ?>>
                                Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                        <?php if (!empty($statusFilter)): ?>
                        <a href="my-bookings.php" class="btn btn-outline-secondary">Clear Filters</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <?php if (count($userBookings) > 0): ?>
        <div class="row">
            <?php foreach ($userBookings as $booking): ?>
            <div class="col-lg-6 mb-4">
                <div class="booking-card">
                    <div class="row">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <img src="<?php echo !empty($booking['image']) ? '../assets/images/cars/' . $booking['image'] : '../assets/images/cars/default-car.jpg'; ?>"
                                alt="<?php echo $booking['make'] . ' ' . $booking['model']; ?>"
                                class="img-fluid rounded">
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h4><?php echo $booking['make'] . ' ' . $booking['model']; ?></h4>
                                <?php
                                        $statusClass = '';
                                        switch ($booking['booking_status']) {
                                            case 'Pending':
                                                $statusClass = 'status-pending';
                                                break;
                                            case 'Confirmed':
                                                $statusClass = 'status-confirmed';
                                                break;
                                            case 'Completed':
                                                $statusClass = 'status-completed';
                                                break;
                                            case 'Cancelled':
                                                $statusClass = 'status-cancelled';
                                                break;
                                        }
                                        ?>
                                <span
                                    class="booking-status <?php echo $statusClass; ?>"><?php echo $booking['booking_status']; ?></span>
                            </div>

                            <p class="mb-1"><strong>Booking #:</strong> <?php echo $booking['id']; ?></p>
                            <p class="mb-1"><strong>Pickup:</strong> <?php echo formatDate($booking['pickup_date']); ?>
                                (<?php echo $booking['pickup_location']; ?>)</p>
                            <p class="mb-1"><strong>Return:</strong> <?php echo formatDate($booking['return_date']); ?>
                                (<?php echo $booking['return_location']; ?>)</p>
                            <p class="mb-3"><strong>Total Amount:</strong> $<?php echo $booking['total_amount']; ?></p>

                            <div class="d-flex">
                                <a href="booking-details.php?id=<?php echo $booking['id']; ?>"
                                    class="btn btn-outline-primary me-2">View Details</a>
                                <?php if ($booking['booking_status'] === 'Pending'): ?>
                                <a href="booking-cancel.php?id=<?php echo $booking['id']; ?>"
                                    class="btn btn-outline-danger"
                                    onclick="return confirm('Are you sure you want to cancel this booking?');">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <h4 class="text-center">No bookings found</h4>
            <p class="text-center">You don't have any bookings yet. <a href="vehicles.php">Browse vehicles</a> to make
                your first booking.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>