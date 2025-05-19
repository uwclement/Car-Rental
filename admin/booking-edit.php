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

// Get vehicles for dropdown
$vehicles = getAllVehicles();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $customerName = trim($_POST['customer_name']);
    $customerEmail = trim($_POST['customer_email']);
    $vehicleId = (int) $_POST['vehicle_id'];
    $pickupDate = $_POST['pickup_date'];
    $pickupLocation = trim($_POST['pickup_location']);
    $returnDate = $_POST['return_date'];
    $returnLocation = trim($_POST['return_location']);
    $bookingStatus = $_POST['booking_status'];
    
    // Validate form data
    $errors = [];
    
    if (empty($customerName)) {
        $errors[] = "Customer name is required";
    }
    
    if (empty($customerEmail) || !filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email address is required";
    }
    
    if (empty($vehicleId)) {
        $errors[] = "Vehicle selection is required";
    }
    
    if (empty($pickupDate)) {
        $errors[] = "Pickup date is required";
    }
    
    if (empty($pickupLocation)) {
        $errors[] = "Pickup location is required";
    }
    
    if (empty($returnDate)) {
        $errors[] = "Return date is required";
    }
    
    if (empty($returnLocation)) {
        $errors[] = "Return location is required";
    }
    
    // Check if return date is after pickup date
    if (!empty($pickupDate) && !empty($returnDate) && strtotime($returnDate) <= strtotime($pickupDate)) {
        $errors[] = "Return date must be after pickup date";
    }
    
    // If no errors, update the booking
    if (empty($errors)) {
        // Get vehicle details to calculate the total amount
        $vehicle = getVehicleById($vehicleId);
        $dailyRate = $vehicle['daily_rate'];
        $numDays = calculateDays($pickupDate, $returnDate);
        $totalAmount = $dailyRate * $numDays;
        
        // Update booking
        $updateResult = updateBooking(
            $bookingId,
            $customerName,
            $customerEmail,
            $vehicleId,
            $pickupDate,
            $pickupLocation,
            $returnDate,
            $returnLocation,
            $totalAmount,
            $bookingStatus
        );
        
        if ($updateResult) {
            // Set success message
            $_SESSION['success_message'] = "Booking updated successfully";
            
            // Redirect to booking view page
            redirect("booking-view.php?id=$bookingId");
        } else {
            $errors[] = "Failed to update booking. Please try again.";
        }
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Booking</h2>
    <div>
        <a href="booking-view.php?id=<?php echo $bookingId; ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Booking Details
        </a>
    </div>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?php echo $error; ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Booking Information</h5>
    </div>
    <div class="card-body">
        <form action="booking-edit.php?id=<?php echo $bookingId; ?>" method="POST">
            <div class="row mb-4">
                <div class="col-md-6">
                    <h6>Customer Information</h6>
                    <div class="mb-3">
                        <label for="customer_name" class="form-label">Customer Name</label>
                        <input type="text" class="form-control" id="customer_name" name="customer_name"
                            value="<?php echo htmlspecialchars($booking['customer_name']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="customer_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="customer_email" name="customer_email"
                            value="<?php echo htmlspecialchars($booking['customer_email']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6>Vehicle Selection</h6>
                    <div class="mb-3">
                        <label for="vehicle_id" class="form-label">Select Vehicle</label>
                        <select class="form-select" id="vehicle_id" name="vehicle_id" required>
                            <option value="">Choose a vehicle</option>
                            <?php foreach ($vehicles as $vehicle): ?>
                            <option value="<?php echo $vehicle['id']; ?>"
                                data-daily-rate="<?php echo $vehicle['daily_rate']; ?>"
                                <?php echo ($vehicle['id'] == $booking['vehicle_id']) ? 'selected' : ''; ?>>
                                <?php echo $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['registration_number'] . ')'; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="booking_status" class="form-label">Booking Status</label>
                        <select class="form-select" id="booking_status" name="booking_status" required>
                            <option value="Pending"
                                <?php echo ($booking['booking_status'] == 'Pending') ? 'selected' : ''; ?>>Pending
                            </option>
                            <option value="Confirmed"
                                <?php echo ($booking['booking_status'] == 'Confirmed') ? 'selected' : ''; ?>>Confirmed
                            </option>
                            <option value="Completed"
                                <?php echo ($booking['booking_status'] == 'Completed') ? 'selected' : ''; ?>>Completed
                            </option>
                            <option value="Cancelled"
                                <?php echo ($booking['booking_status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Pickup Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="pickup_date" class="form-label">Pickup Date</label>
                                <input type="date" class="form-control" id="pickup_date" name="pickup_date"
                                    value="<?php echo date('Y-m-d', strtotime($booking['pickup_date'])); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="pickup_location" class="form-label">Pickup Location</label>
                                <input type="text" class="form-control" id="pickup_location" name="pickup_location"
                                    value="<?php echo htmlspecialchars($booking['pickup_location']); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Return Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="return_date" class="form-label">Return Date</label>
                                <input type="date" class="form-control" id="return_date" name="return_date"
                                    value="<?php echo date('Y-m-d', strtotime($booking['return_date'])); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="return_location" class="form-label">Return Location</label>
                                <input type="text" class="form-control" id="return_location" name="return_location"
                                    value="<?php echo htmlspecialchars($booking['return_location']); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0">Booking Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Daily Rate:</strong> $<span
                                    id="daily_rate"><?php echo $booking['daily_rate']; ?></span></p>
                            <p><strong>Number of Days:</strong> <span
                                    id="num_days"><?php echo calculateDays($booking['pickup_date'], $booking['return_date']); ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Amount:</strong> $<span
                                    id="total_amount"><?php echo $booking['total_amount']; ?></span></p>
                            <p class="text-muted small">Note: Total will be automatically calculated when you save.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Save Changes
                </button>
                <a href="booking-view.php?id=<?php echo $bookingId; ?>" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Calculate and update booking summary on date or vehicle change
document.addEventListener('DOMContentLoaded', function() {
    const vehicleSelect = document.getElementById('vehicle_id');
    const pickupDateInput = document.getElementById('pickup_date');
    const returnDateInput = document.getElementById('return_date');
    const dailyRateSpan = document.getElementById('daily_rate');
    const numDaysSpan = document.getElementById('num_days');
    const totalAmountSpan = document.getElementById('total_amount');

    // Function to update the booking summary
    function updateBookingSummary() {
        // Get the selected vehicle's daily rate
        const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
        const dailyRate = selectedOption ? parseFloat(selectedOption.dataset.dailyRate) : 0;

        // Get the pickup and return dates
        const pickupDate = new Date(pickupDateInput.value);
        const returnDate = new Date(returnDateInput.value);

        // Calculate the number of days
        let numDays = 0;
        if (!isNaN(pickupDate.getTime()) && !isNaN(returnDate.getTime()) && returnDate > pickupDate) {
            const diffTime = Math.abs(returnDate - pickupDate);
            numDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        }

        // Calculate the total amount
        const totalAmount = dailyRate * numDays;

        // Update the display
        dailyRateSpan.textContent = dailyRate.toFixed(2);
        numDaysSpan.textContent = numDays;
        totalAmountSpan.textContent = totalAmount.toFixed(2);
    }

    // Add event listeners to update summary when inputs change
    vehicleSelect.addEventListener('change', updateBookingSummary);
    pickupDateInput.addEventListener('change', updateBookingSummary);
    returnDateInput.addEventListener('change', updateBookingSummary);

    // Initialize the booking summary
    updateBookingSummary();
});
</script>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>