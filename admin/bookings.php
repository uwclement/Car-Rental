<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../public/login.php');
}

// Set admin page flag
$isAdminPage = true;

// Process filter parameters
$filters = [];
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['status'] = sanitize($_GET['status']);
}

// Get all bookings based on filters
$bookings = getAllBookings($filters);

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Booking Management</h2>
    <a href="booking-add.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Create New Booking
    </a>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Bookings</h5>
    </div>
    <div class="card-body">
        <form action="bookings.php" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="Pending"
                        <?php echo isset($_GET['status']) && $_GET['status'] === 'Pending' ? 'selected' : ''; ?>>Pending
                    </option>
                    <option value="Confirmed"
                        <?php echo isset($_GET['status']) && $_GET['status'] === 'Confirmed' ? 'selected' : ''; ?>>
                        Confirmed</option>
                    <option value="Completed"
                        <?php echo isset($_GET['status']) && $_GET['status'] === 'Completed' ? 'selected' : ''; ?>>
                        Completed</option>
                    <option value="Cancelled"
                        <?php echo isset($_GET['status']) && $_GET['status'] === 'Cancelled' ? 'selected' : ''; ?>>
                        Cancelled</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                <?php if (!empty($_GET)): ?>
                <a href="bookings.php" class="btn btn-outline-secondary">Clear Filters</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Bookings Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Bookings</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Pickup Date</th>
                        <th>Return Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td>#<?php echo $booking['id']; ?></td>
                        <td>
                            <strong><?php echo $booking['customer_name']; ?></strong><br>
                            <small class="text-muted"><?php echo $booking['customer_email']; ?></small>
                        </td>
                        <td><?php echo $booking['make'] . ' ' . $booking['model']; ?></td>
                        <td><?php echo formatDate($booking['pickup_date']); ?></td>
                        <td><?php echo formatDate($booking['return_date']); ?></td>
                        <td>$<?php echo $booking['total_amount']; ?></td>
                        <td>
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
                            <span
                                class="badge <?php echo $statusClass; ?>"><?php echo $booking['booking_status']; ?></span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="booking-view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info"
                                    title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="booking-edit.php?id=<?php echo $booking['id']; ?>"
                                    class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <div class="dropdown d-inline-block">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                        id="statusDropdown<?php echo $booking['id']; ?>" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu"
                                        aria-labelledby="statusDropdown<?php echo $booking['id']; ?>">
                                        <?php if ($booking['booking_status'] === 'Pending'): ?>
                                        <li><a class="dropdown-item"
                                                href="booking-status-update.php?id=<?php echo $booking['id']; ?>&status=Confirmed">Mark
                                                as Confirmed</a></li>
                                        <li><a class="dropdown-item"
                                                href="booking-status-update.php?id=<?php echo $booking['id']; ?>&status=Cancelled">Mark
                                                as Cancelled</a></li>
                                        <?php elseif ($booking['booking_status'] === 'Confirmed'): ?>
                                        <li><a class="dropdown-item"
                                                href="booking-status-update.php?id=<?php echo $booking['id']; ?>&status=Completed">Mark
                                                as Completed</a></li>
                                        <li><a class="dropdown-item"
                                                href="booking-status-update.php?id=<?php echo $booking['id']; ?>&status=Cancelled">Mark
                                                as Cancelled</a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($bookings) === 0): ?>
                    <tr>
                        <td colspan="8" class="text-center">No bookings found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>