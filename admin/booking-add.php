<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../public/login.php');
}

// Set admin page flag
$isAdminPage = true;

// Process form submission
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $vehicleId = isset($_POST['vehicle_id']) ? (int) $_POST['vehicle_id'] : 0;
    $customerId = isset($_POST['customer_id']) ? (int) $_POST['customer_id'] : 0;
    $pickupDate = sanitize($_POST['pickup_date']);
    $returnDate = sanitize($_POST['return_date']);
    $pickupLocation = sanitize($_POST['pickup_location']);
    $returnLocation = sanitize($_POST['return_location']);
    $totalAmount = isset($_POST['total_amount']) ? (float) $_POST['total_amount'] : 0;
    
    // Get vehicle and customer data
    $vehicle = getVehicleById($vehicleId);
    $customer = getUserById($customerId);
    
    // Validate input
    if (!$vehicle || !$customer) {
        $error = 'Invalid vehicle or customer.';
    } elseif (strtotime($pickupDate) < strtotime(date('Y-m-d'))) {
        $error = 'Pickup date cannot be in the past.';
    } elseif (strtotime($returnDate) <= strtotime($pickupDate)) {
        $error = 'Return date must be after pickup date.';
    } elseif ($vehicle['status'] !== 'Available') {
        $error = 'This vehicle is not available for booking.';
    } else {
        // Check if vehicle is available for the selected dates
        if (isVehicleAvailable($vehicleId, $pickupDate, $returnDate)) {
            // If total amount is not provided, calculate it
            if ($totalAmount <= 0) {
                $days = calculateDays($pickupDate, $returnDate);
                $totalAmount = calculateTotalAmount($vehicle['daily_rate'], $days);
            }
            
            // Create booking
            $bookingData = [
                'user_id' => $customerId,
                'vehicle_id' => $vehicleId,
                'pickup_date' => $pickupDate,
                'return_date' => $returnDate,
                'pickup_location' => $pickupLocation,
                'return_location' => $returnLocation,
                'total_amount' => $totalAmount
            ];
            
            $bookingId = createBooking($bookingData);
            
            if ($bookingId) {
                $success = true;
                $bookingUrl = 'booking-view.php?id=' . $bookingId;
            } else {
                $error = 'Failed to create booking. Please try again.';
            }
        } else {
            $error = 'This vehicle is not available for the selected dates. Please choose different dates.';
        }
    }
}

// Get available vehicles for the dropdown
$availableVehicles = getVehicles(['status' => 'Available']);

// Get customers for the dropdown
$customers = getAllUsers();
$customers = array_filter($customers, function($user) {
    return $user['role'] === 'customer';
});

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Create New Booking</h2>
    <a href="bookings.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Bookings
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Booking Information</h5>
    </div>
    <div class="card-body">
        <?php if ($success): ?>
        <div class="alert alert-success">
            <h4 class="alert-heading">Booking Created Successfully!</h4>
            <p>The booking has been created and the vehicle status has been updated.</p>
            <hr>
            <div class="d-flex gap-2">
                <a href="<?php echo $bookingUrl; ?>" class="btn btn-primary">View Booking Details</a>
                <a href="booking-add.php" class="btn btn-outline-primary">Create Another Booking</a>
            </div>
        </div>
        <?php else: ?>
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="booking-add.php" method="POST">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="vehicle_id" class="form-label">Select Vehicle</label>
                        <select class="form-select" id="vehicle_id" name="vehicle_id" required>
                            <option value="">-- Select Vehicle --</option>
                            <?php foreach ($availableVehicles as $vehicle): ?>
                            <option value="<?php echo $vehicle['id']; ?>"
                                data-rate="<?php echo $vehicle['daily_rate']; ?>">
                                <?php echo $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ') - $' . $vehicle['daily_rate'] . '/day'; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="customer_id" class="form-label">Select Customer</label>
                        <select class="form-select" id="customer_id" name="customer_id" required>
                            <option value="">-- Select Customer --</option>
                            <?php foreach ($customers as $customer): ?>
                            <option value="<?php echo $customer['id']; ?>">
                                <?php echo $customer['name'] . ' (' . $customer['email'] . ')'; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="pickup_date" class="form-label">Pickup Date</label>
                        <input type="date" class="form-control" id="pickup_date" name="pickup_date"
                            min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="return_date" class="form-label">Return Date</label>
                        <input type="date" class="form-control" id="return_date" name="return_date"
                            min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="pickup_location" class="form-label">Pickup Location</label>
                        <select class="form-select" id="pickup_location" name="pickup_location" required>
                            <option value="Main Office - 123 Main St">Main Office - 123 Main St</option>
                            <option value="Airport Branch - Terminal 1">Airport Branch - Terminal 1</option>
                            <option value="Downtown Branch - 456 Central Ave">Downtown Branch - 456 Central Ave</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label for="return_location" class="form-label">Return Location</label>
                        <select class="form-select" id="return_location" name="return_location" required>
                            <option value="Main Office - 123 Main St">Main Office - 123 Main St</option>
                            <option value="Airport Branch - Terminal 1">Airport Branch - Terminal 1</option>
                            <option value="Downtown Branch - 456 Central Ave">Downtown Branch - 456 Central Ave</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card bg-light mb-4">
                <div class="card-body">
                    <h5 class="card-title">Booking Summary</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Daily Rate:</strong> $<span id="daily_rate">0.00</span></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Number of Days:</strong> <span id="days_count">0</span></p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Total Amount:</strong> $<span id="total_amount">0.00</span></p>
                            <input type="hidden" name="total_amount" id="total_amount_input" value="0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" onclick="window.history.back();">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Booking</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const vehicleSelect = document.getElementById('vehicle_id');
    const pickupDateInput = document.getElementById('pickup_date');
    const returnDateInput = document.getElementById('return_date');
    const dailyRateElement = document.getElementById('daily_rate');
    const daysCountElement = document.getElementById('days_count');
    const totalAmountElement = document.getElementById('total_amount');
    const totalAmountInput = document.getElementById('total_amount_input');

    // Function to calculate total
    function calculateTotal() {
        if (vehicleSelect.value && pickupDateInput.value && returnDateInput.value) {
            const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
            const dailyRate = parseFloat(selectedOption.dataset.rate);

            const pickupDate = new Date(pickupDateInput.value);
            const returnDate = new Date(returnDateInput.value);

            // Calculate difference in days
            const diffTime = Math.abs(returnDate - pickupDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays > 0 && !isNaN(dailyRate)) {
                dailyRateElement.textContent = dailyRate.toFixed(2);
                daysCountElement.textContent = diffDays;

                const totalAmount = diffDays * dailyRate;
                totalAmountElement.textContent = totalAmount.toFixed(2);
                totalAmountInput.value = totalAmount.toFixed(2);
            }
        }
    }

    // Set up event listeners
    vehicleSelect.addEventListener('change', calculateTotal);

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
});
</script>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>