<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../public/login.php');
}

// Get dashboard statistics
$stats = getDashboardStats();

// Set admin page flag
$isAdminPage = true;

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Admin Dashboard</h2>
    <div>
        <span class="text-muted">Welcome, <?php echo $_SESSION['user_name']; ?></span>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="dashboard-card">
            <div class="icon bg-primary">
                <i class="fas fa-car"></i>
            </div>
            <div class="stat"><?php echo $stats['total_vehicles']; ?></div>
            <div class="label">Total Vehicles</div>
            <div class="mt-2 text-success">
                <span><?php echo $stats['available_vehicles']; ?> Available</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="dashboard-card">
            <div class="icon bg-success">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat"><?php echo $stats['total_bookings']; ?></div>
            <div class="label">Total Bookings</div>
            <div class="mt-2 text-primary">
                <span><?php echo $stats['active_bookings']; ?> Active</span>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="dashboard-card">
            <div class="icon bg-warning">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat"><?php echo $stats['total_customers']; ?></div>
            <div class="label">Registered Customers</div>
            <div class="mt-2">
                <a href="customers.php" class="text-decoration-none">View All</a>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="dashboard-card">
            <div class="icon bg-danger">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat">$<?php echo number_format($stats['total_revenue'], 2); ?></div>
            <div class="label">Total Revenue</div>
            <div class="mt-2">
                <a href="reports.php" class="text-decoration-none">View Reports</a>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>Recent Bookings</h5>
        <a href="bookings.php" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Dates</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $recentBookings = getAllBookings();
                    foreach (array_slice($recentBookings, 0, 5) as $booking):
                    ?>
                    <tr>
                        <td>#<?php echo $booking['id']; ?></td>
                        <td><?php echo $booking['customer_name']; ?></td>
                        <td><?php echo $booking['make'] . ' ' . $booking['model']; ?></td>
                        <td>
                            <?php echo formatDate($booking['pickup_date']); ?> -
                            <?php echo formatDate($booking['return_date']); ?>
                        </td>
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
                        <td>$<?php echo $booking['total_amount']; ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="booking-view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="booking-edit.php?id=<?php echo $booking['id']; ?>"
                                    class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($recentBookings) === 0): ?>
                    <tr>
                        <td colspan="7" class="text-center">No bookings found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recently Added Vehicles -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Recently Added Vehicles</h5>
                <a href="vehicles.php" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>Vehicle</th>
                                <th>Details</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $allVehicles = getVehicles();
                            foreach (array_slice($allVehicles, 0, 3) as $vehicle):
                            ?>
                            <tr>
                                <td>
                                    <img src="<?php echo !empty($vehicle['image']) ? '/Car-Rental/assets/images/cars/' . $vehicle['image'] : '/Car-Rental/assets/images/cars/default-car.jpg'; ?>"
                                        alt="<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>"
                                        class="admin-img-thumbnail">
                                </td>
                                <td>
                                    <strong><?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?></strong><br>
                                    <small class="text-muted"><?php echo $vehicle['year']; ?> |
                                        $<?php echo $vehicle['daily_rate']; ?>/day</small>
                                </td>
                                <td>
                                    <?php
                                        $statusClass = '';
                                        switch ($vehicle['status']) {
                                            case 'Available':
                                                $statusClass = 'status-available';
                                                break;
                                            case 'Booked':
                                                $statusClass = 'status-booked';
                                                break;
                                            case 'Maintenance':
                                                $statusClass = 'status-maintenance';
                                                break;
                                        }
                                        ?>
                                    <span
                                        class="status-badge <?php echo $statusClass; ?>"><?php echo $vehicle['status']; ?></span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="vehicle-view.php?id=<?php echo $vehicle['id']; ?>"
                                            class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="vehicle-edit.php?id=<?php echo $vehicle['id']; ?>"
                                            class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (count($allVehicles) === 0): ?>
                            <tr>
                                <td colspan="4" class="text-center">No vehicles found</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions and System Status -->
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5>Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6">
                        <a href="vehicle-add.php" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-2"></i> Add Vehicle
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="customer-add.php" class="btn btn-success w-100">
                            <i class="fas fa-user-plus me-2"></i> Add Customer
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="booking-add.php" class="btn btn-info w-100 text-white">
                            <i class="fas fa-calendar-plus me-2"></i> Create Booking
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="reports.php" class="btn btn-warning w-100">
                            <i class="fas fa-chart-bar me-2"></i> View Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>System Status</h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Vehicle Availability Rate
                        <span
                            class="badge bg-primary rounded-pill"><?php echo round(($stats['available_vehicles'] / $stats['total_vehicles']) * 100); ?>%</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Active Bookings
                        <span class="badge bg-success rounded-pill"><?php echo $stats['active_bookings']; ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        System Version
                        <span class="badge bg-secondary rounded-pill">1.0.0</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Last Update
                        <span class="text-muted"><?php echo date('F d, Y'); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>