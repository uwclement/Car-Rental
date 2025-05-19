<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../public/login.php');
}

// Set admin page flag
$isAdminPage = true;

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

// Get bookings for this vehicle
$vehicleBookings = getAllBookings(['vehicle_id' => $vehicleId]);

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Vehicle Details</h2>
    <div>
        <a href="vehicle-edit.php?id=<?php echo $vehicleId; ?>" class="btn btn-primary me-2">
            <i class="fas fa-edit me-1"></i> Edit Vehicle
        </a>
        <a href="vehicles.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Vehicles
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Vehicle Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <img src="<?php echo !empty($vehicle['image']) ? '../assets/images/cars/' . $vehicle['image'] : '../assets/images/cars/default-car.jpg'; ?>"
                            class="img-fluid rounded mb-4"
                            alt="<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>">
                    </div>
                    <div class="col-md-6">
                        <h3><?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?> (<?php echo $vehicle['year']; ?>)
                        </h3>

                        <div class="mb-3">
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
                        </div>

                        <p class="lead">Daily Rate: $<?php echo $vehicle['daily_rate']; ?></p>

                        <p><?php echo $vehicle['description']; ?></p>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Basic Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th>Registration Number:</th>
                                            <td><?php echo $vehicle['registration_number']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Color:</th>
                                            <td><?php echo $vehicle['color']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Seating Capacity:</th>
                                            <td><?php echo $vehicle['seating_capacity']; ?> seats</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">Technical Specifications</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-striped">
                                    <tbody>
                                        <tr>
                                            <th>Fuel Type:</th>
                                            <td><?php echo $vehicle['fuel_type']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Transmission:</th>
                                            <td><?php echo $vehicle['transmission']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Year:</th>
                                            <td><?php echo $vehicle['year']; ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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
                    <a href="vehicle-edit.php?id=<?php echo $vehicleId; ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Vehicle
                    </a>

                    <?php if ($vehicle['status'] === 'Available'): ?>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createBookingModal">
                        <i class="fas fa-calendar-plus me-1"></i> Create Booking
                    </button>
                    <?php endif; ?>

                    <?php if ($vehicle['status'] === 'Available'): ?>
                    <a href="vehicle-status.php?id=<?php echo $vehicleId; ?>&status=Maintenance"
                        class="btn btn-warning">
                        <i class="fas fa-tools me-1"></i> Mark as Maintenance
                    </a>
                    <?php elseif ($vehicle['status'] === 'Maintenance'): ?>
                    <a href="vehicle-status.php?id=<?php echo $vehicleId; ?>&status=Available" class="btn btn-success">
                        <i class="fas fa-check me-1"></i> Mark as Available
                    </a>
                    <?php endif; ?>

                    <?php if (count($vehicleBookings) === 0): ?>
                    <a href="vehicle-delete.php?id=<?php echo $vehicleId; ?>" class="btn btn-danger"
                        onclick="return confirm('Are you sure you want to delete this vehicle?');">
                        <i class="fas fa-trash me-1"></i> Delete Vehicle
                    </a>
                    <?php else: ?>
                    <button class="btn btn-danger" disabled data-bs-toggle="tooltip"
                        title="Cannot delete vehicle with bookings">
                        <i class="fas fa-trash me-1"></i> Delete Vehicle
                    </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Booking History</h5>
                <span class="badge bg-primary"><?php echo count($vehicleBookings); ?> Bookings</span>
            </div>
            <div class="card-body">
                <?php if (count($vehicleBookings) > 0): ?>
                <div class="list-group">
                    <?php foreach ($vehicleBookings as $booking): ?>
                    <a href="booking-view.php?id=<?php echo $booking['id']; ?>"
                        class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">Booking #<?php echo $booking['id']; ?></h6>
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
                        </div>
                        <p class="mb-1">Customer: <?php echo $booking['customer_name']; ?></p>
                        <small><?php echo formatDate($booking['pickup_date']); ?> -
                            <?php echo formatDate($booking['return_date']); ?></small>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="alert alert-info mb-0">
                    <p class="mb-0">No booking history for this vehicle.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Create Booking Modal -->
<?php if ($vehicle['status'] === 'Available'): ?>
<div class="modal fade" id="createBookingModal" tabindex="-1" aria-labelledby="createBookingModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createBookingModalLabel">Create New Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="booking-add.php" method="POST" id="quickBookingForm">
                    <input type="hidden" name="vehicle_id" value="<?php echo $vehicleId; ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="customer" class="form-label">Customer</label>
                            <select class="form-select" id="customer" name="customer_id" required>
                                <option value="">Select Customer</option>
                                <?php
                                $customers = getAllUsers();
                                foreach ($customers as $customer) {
                                    if ($customer['role'] === 'customer') {
                                        echo "<option value=\"{$customer['id']}\">{$customer['name']} ({$customer['email']})</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="new_customer" class="form-label">Or Add New Customer</label>
                                <a href="customer-add.php?redirect=vehicle-view.php?id=<?php echo $vehicleId; ?>"
                                    class="btn btn-outline-primary">Add New Customer</a>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="pickup_date" class="form-label">Pickup Date</label>
                            <input type="date" class="form-control" id="pickup_date" name="pickup_date"
                                min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="return_date" class="form-label">Return Date</label>
                            <input type="date" class="form-control" id="return_date" name="return_date"
                                min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="pickup_location" class="form-label">Pickup Location</label>
                            <select class="form-select" id="pickup_location" name="pickup_location" required>
                                <option value="Main Office - 123 Main St">Main Office - 123 Main St</option>
                                <option value="Airport Branch - Terminal 1">Airport Branch - Terminal 1</option>
                                <option value="Downtown Branch - 456 Central Ave">Downtown Branch - 456 Central Ave
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="return_location" class="form-label">Return Location</label>
                            <select class="form-select" id="return_location" name="return_location" required>
                                <option value="Main Office - 123 Main St">Main Office - 123 Main St</option>
                                <option value="Airport Branch - Terminal 1">Airport Branch - Terminal 1</option>
                                <option value="Downtown Branch - 456 Central Ave">Downtown Branch - 456 Central Ave
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="card bg-light mb-3">
                        <div class="card-body">
                            <h6 class="card-title">Booking Summary</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Vehicle:</strong>
                                        <?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?></p>
                                    <p class="mb-1"><strong>Daily Rate:</strong> $<?php echo $vehicle['daily_rate']; ?>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Number of Days:</strong> <span id="days_count">0</span></p>
                                    <p class="mb-1"><strong>Total Amount:</strong> $<span id="total_amount">0.00</span>
                                    </p>
                                    <input type="hidden" name="total_amount" id="total_amount_input" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="quickBookingForm" class="btn btn-primary">Create Booking</button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enable tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltips.forEach(tooltip => {
        new bootstrap.Tooltip(tooltip);
    });

    // Calculate booking amount
    const pickupDateInput = document.getElementById('pickup_date');
    const returnDateInput = document.getElementById('return_date');
    const daysCountElement = document.getElementById('days_count');
    const totalAmountElement = document.getElementById('total_amount');
    const totalAmountInput = document.getElementById('total_amount_input');
    const dailyRate = <?php echo $vehicle['daily_rate']; ?>;

    function calculateTotal() {
        if (pickupDateInput && returnDateInput && pickupDateInput.value && returnDateInput.value) {
            const pickupDate = new Date(pickupDateInput.value);
            const returnDate = new Date(returnDateInput.value);

            // Calculate difference in days
            const diffTime = Math.abs(returnDate - pickupDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays > 0) {
                const totalAmount = diffDays * dailyRate;
                daysCountElement.textContent = diffDays;
                totalAmountElement.textContent = totalAmount.toFixed(2);
                totalAmountInput.value = totalAmount.toFixed(2);
            }
        }
    }

    if (pickupDateInput && returnDateInput) {
        pickupDateInput.addEventListener('change', function() {
            // Set minimum return date to be the pickup date plus one day
            const pickupDate = new Date(this.value);
            const nextDay = new Date(pickupDate);
            nextDay.setDate(pickupDate.getDate() + 1);

            const year = nextDay.getFullYear();
            const month = String(nextDay.getMonth() + 1).padStart(2, '0');
            const day = String(nextDay.getDate()).padStart(2, '0');

            returnDateInput.min = `${year}-${month}-${day}`;

            // If return date is before pickup date, reset it
            if (returnDateInput.value && new Date(returnDateInput.value) <= new Date(this.value)) {
                returnDateInput.value = `${year}-${month}-${day}`;
            }

            calculateTotal();
        });

        returnDateInput.addEventListener('change', calculateTotal);
    }
});
</script>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>