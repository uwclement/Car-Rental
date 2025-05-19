<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=booking.php');
}

// Check if admin is trying to access
if (isAdmin()) {
    redirect('../admin/index.php');
}

// Check if vehicle ID is provided
if (!isset($_GET['vehicle_id']) || empty($_GET['vehicle_id'])) {
    redirect('vehicles.php');
}

$vehicleId = (int) $_GET['vehicle_id'];
$vehicle = getVehicleById($vehicleId);

// If vehicle not found or not available, redirect to vehicles page
if (!$vehicle || $vehicle['status'] !== 'Available') {
    redirect('vehicles.php');
}

// Get user data
$userId = getCurrentUserId();
$user = getUserById($userId);

// Process booking form
$bookingSuccess = false;
$bookingError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_submit'])) {
    $pickupDate = sanitize($_POST['pickup_date']);
    $returnDate = sanitize($_POST['return_date']);
    $pickupLocation = sanitize($_POST['pickup_location']);
    $returnLocation = sanitize($_POST['return_location']);
    
    // Validate dates
    if (strtotime($pickupDate) < strtotime(date('Y-m-d'))) {
        $bookingError = 'Pickup date cannot be in the past.';
    } elseif (strtotime($returnDate) <= strtotime($pickupDate)) {
        $bookingError = 'Return date must be after pickup date.';
    } else {
        // Check if vehicle is available for the selected dates
        if (isVehicleAvailable($vehicleId, $pickupDate, $returnDate)) {
            $days = calculateDays($pickupDate, $returnDate);
            $totalAmount = calculateTotalAmount($vehicle['daily_rate'], $days);
            
            // Create booking
            $bookingData = [
                'user_id' => $userId,
                'vehicle_id' => $vehicleId,
                'pickup_date' => $pickupDate,
                'return_date' => $returnDate,
                'pickup_location' => $pickupLocation,
                'return_location' => $returnLocation,
                'total_amount' => $totalAmount
            ];
            
            $bookingId = createBooking($bookingData);
            
            if ($bookingId) {
                $bookingSuccess = true;
                // Redirect to payment page
                redirect('payment.php?booking_id=' . $bookingId);
            } else {
                $bookingError = 'Failed to create booking. Please try again.';
            }
        } else {
            $bookingError = 'This vehicle is not available for the selected dates. Please choose different dates.';
        }
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Book a Vehicle</h4>
                </div>
                <div class="card-body">
                    <?php if ($bookingSuccess): ?>
                    <div class="alert alert-success">
                        <h4 class="alert-heading">Booking Successful!</h4>
                        <p>Your booking has been created. Please proceed to payment to confirm your reservation.</p>
                        <hr>
                        <a href="payment.php?booking_id=<?php echo $bookingId; ?>" class="btn btn-success">Proceed to
                            Payment</a>
                    </div>
                    <?php else: ?>
                    <?php if ($bookingError): ?>
                    <div class="alert alert-danger"><?php echo $bookingError; ?></div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-4">
                            <img src="<?php echo !empty($vehicle['image']) ? '/Car-Rental/assets/images/cars/' . $vehicle['image'] : '/Car-Rental/assets/images/cars/default-car.jpg'; ?>"
                                class="img-fluid rounded"
                                alt="<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>">
                        </div>
                        <div class="col-md-8">
                            <h3><?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?></h3>
                            <p class="text-muted"><?php echo $vehicle['year']; ?> |
                                <?php echo $vehicle['transmission']; ?> | <?php echo $vehicle['fuel_type']; ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <h5>Daily Rate: <span class="text-primary">$<?php echo $vehicle['daily_rate']; ?></span>
                                </h5>
                                <span class="badge bg-success">Available</span>
                            </div>
                        </div>
                    </div>

                    <form action="booking.php?vehicle_id=<?php echo $vehicleId; ?>" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pickup_date" class="form-label">Pickup Date</label>
                                <input type="date" class="form-control" id="pickup_date" name="pickup_date" required
                                    min="<?php echo date('Y-m-d'); ?>"
                                    value="<?php echo isset($_POST['pickup_date']) ? $_POST['pickup_date'] : date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="return_date" class="form-label">Return Date</label>
                                <input type="date" class="form-control" id="return_date" name="return_date" required
                                    min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                    value="<?php echo isset($_POST['return_date']) ? $_POST['return_date'] : date('Y-m-d', strtotime('+3 days')); ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pickup_location" class="form-label">Pickup Location</label>
                                <select class="form-select" id="pickup_location" name="pickup_location" required>
                                    <option value="Main Office - 123 Main St">Main Office - 123 Main St</option>
                                    <option value="Airport Branch - Terminal 1">Airport Branch - Terminal 1</option>
                                    <option value="Downtown Branch - 456 Central Ave">Downtown Branch - 456 Central Ave
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="return_location" class="form-label">Return Location</label>
                                <select class="form-select" id="return_location" name="return_location" required>
                                    <option value="Main Office - 123 Main St">Main Office - 123 Main St</option>
                                    <option value="Airport Branch - Terminal 1">Airport Branch - Terminal 1</option>
                                    <option value="Downtown Branch - 456 Central Ave">Downtown Branch - 456 Central Ave
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="renter_name" class="form-label">Renter's Name</label>
                                <input type="text" class="form-control" id="renter_name"
                                    value="<?php echo $user['name']; ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="renter_email" class="form-label">Renter's Email</label>
                                <input type="email" class="form-control" id="renter_email"
                                    value="<?php echo $user['email']; ?>" readonly>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="renter_phone" class="form-label">Renter's Phone</label>
                                <input type="tel" class="form-control" id="renter_phone"
                                    value="<?php echo $user['phone']; ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="driving_license" class="form-label">Driving License</label>
                                <input type="text" class="form-control" id="driving_license"
                                    value="<?php echo $user['driving_license']; ?>" readonly>
                            </div>
                        </div>

                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Rental Summary</h5>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Daily Rate:</span>
                                    <span>$<?php echo $vehicle['daily_rate']; ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Number of Days:</span>
                                    <span id="total_days">
                                        <?php 
                                            $startDate = isset($_POST['pickup_date']) ? $_POST['pickup_date'] : date('Y-m-d');
                                            $endDate = isset($_POST['return_date']) ? $_POST['return_date'] : date('Y-m-d', strtotime('+3 days'));
                                            $days = calculateDays($startDate, $endDate);
                                            echo $days;
                                            ?>
                                    </span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total Amount:</span>
                                    <span>$<span
                                            id="total_amount"><?php echo calculateTotalAmount($vehicle['daily_rate'], $days); ?></span></span>
                                </div>
                            </div>
                        </div>

                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms_agreement" required>
                            <label class="form-check-label" for="terms_agreement">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms and
                                    Conditions</a>
                            </label>
                        </div>

                        <button type="submit" name="booking_submit" class="btn btn-primary btn-lg w-100">Book
                            Now</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Rental Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>1. Rental Requirements</h5>
                <p>To rent a vehicle, you must be at least 21 years of age and possess a valid driver's license.
                    Additional driver requirements may apply based on the vehicle type and rental location.</p>

                <h5>2. Payment and Fees</h5>
                <p>You agree to pay all fees associated with your rental, including the rental rate, insurance, and any
                    additional charges incurred during your rental period, such as late returns, fuel, or damage fees.
                </p>

                <h5>3. Vehicle Use</h5>
                <p>The vehicle may only be driven by the authorized renter or additional drivers approved by the rental
                    company. The vehicle must not be used for any illegal purposes, racing, or off-road driving.</p>

                <h5>4. Cancellation Policy</h5>
                <p>Cancellations made more than 24 hours before the scheduled pickup time will receive a full refund.
                    Cancellations made within 24 hours of the scheduled pickup time may be subject to a cancellation
                    fee.</p>

                <h5>5. Damage and Insurance</h5>
                <p>You are responsible for any damage to the vehicle during the rental period. Insurance coverage is
                    available at an additional cost. If you decline insurance, you will be responsible for the full cost
                    of any damages.</p>

                <h5>6. Late Returns</h5>
                <p>Late returns will be charged at an hourly rate. Returns more than 24 hours late without prior
                    notification may be reported as stolen to local authorities.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Calculate total amount when dates change
    const pickupDateInput = document.getElementById('pickup_date');
    const returnDateInput = document.getElementById('return_date');
    const totalDaysElement = document.getElementById('total_days');
    const totalAmountElement = document.getElementById('total_amount');
    const dailyRate = <?php echo $vehicle['daily_rate']; ?>;

    function calculateTotal() {
        const pickupDate = new Date(pickupDateInput.value);
        const returnDate = new Date(returnDateInput.value);

        // Calculate difference in days
        const diffTime = Math.abs(returnDate - pickupDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (diffDays > 0) {
            const totalAmount = diffDays * dailyRate;
            totalDaysElement.textContent = diffDays;
            totalAmountElement.textContent = totalAmount.toFixed(2);
        }
    }

    if (pickupDateInput && returnDateInput) {
        pickupDateInput.addEventListener('change', function() {
            // Set minimum return date to be the pickup date
            const pickupDate = new Date(this.value);
            const nextDay = new Date(pickupDate);
            nextDay.setDate(pickupDate.getDate() + 1);

            const year = nextDay.getFullYear();
            const month = String(nextDay.getMonth() + 1).padStart(2, '0');
            const day = String(nextDay.getDate()).padStart(2, '0');

            returnDateInput.min = `${year}-${month}-${day}`;

            // If return date is before pickup date, reset it
            if (new Date(returnDateInput.value) <= new Date(this.value)) {
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