<?php
require_once __DIR__ . '/../includes/functions.php';

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

// Check if form is submitted for booking
$bookingSuccess = false;
$bookingError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_submit'])) {
    if (!isLoggedIn()) {
        redirect('login.php?redirect=vehicle-details.php?id=' . $vehicleId);
    }
    
    $pickupDate = $_POST['pickup_date'];
    $returnDate = $_POST['return_date'];
    $pickupLocation = $_POST['pickup_location'];
    $returnLocation = $_POST['return_location'];
    
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
                'user_id' => getCurrentUserId(),
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

<!-- Vehicle Details Section -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <img src="<?php echo !empty($vehicle['image']) ? '/Car-Rental/assets/images/cars/' . $vehicle['image'] : '/Car-Rental/assets/images/cars/default-car.jpg'; ?>"
                    class="vehicle-detail-img mb-4" alt="<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>">

                <div class="vehicle-info mb-4">
                    <h2><?php echo $vehicle['make'] . ' ' . $vehicle['model'] . ' (' . $vehicle['year'] . ')'; ?></h2>

                    <div class="row mb-4">
                        <div class="col-md-3 col-6 text-center mb-3">
                            <div class="feature-icon mb-2">
                                <i class="fas fa-gas-pump"></i>
                            </div>
                            <h6><?php echo $vehicle['fuel_type']; ?></h6>
                            <small class="text-muted">Fuel Type</small>
                        </div>
                        <div class="col-md-3 col-6 text-center mb-3">
                            <div class="feature-icon mb-2">
                                <i class="fas fa-cog"></i>
                            </div>
                            <h6><?php echo $vehicle['transmission']; ?></h6>
                            <small class="text-muted">Transmission</small>
                        </div>
                        <div class="col-md-3 col-6 text-center mb-3">
                            <div class="feature-icon mb-2">
                                <i class="fas fa-users"></i>
                            </div>
                            <h6><?php echo $vehicle['seating_capacity']; ?> Seats</h6>
                            <small class="text-muted">Capacity</small>
                        </div>
                        <div class="col-md-3 col-6 text-center mb-3">
                            <div class="feature-icon mb-2">
                                <i class="fas fa-palette"></i>
                            </div>
                            <h6><?php echo $vehicle['color']; ?></h6>
                            <small class="text-muted">Color</small>
                        </div>
                    </div>

                    <h4>Vehicle Description</h4>
                    <p><?php echo $vehicle['description']; ?></p>

                    <h4>Vehicle Features</h4>
                    <ul>
                        <li>Air Conditioning</li>
                        <li>Power Steering</li>
                        <li>Power Windows</li>
                        <li>Bluetooth Connectivity</li>
                        <li>GPS Navigation</li>
                        <li>Backup Camera</li>
                    </ul>
                </div>

                <div class="vehicle-info">
                    <h4>Rental Terms & Conditions</h4>
                    <ul>
                        <li>Minimum rental period: 1 day</li>
                        <li>Valid driver's license required</li>
                        <li>Security deposit may be required</li>
                        <li>Fuel policy: Same-to-same (return with the same fuel level)</li>
                        <li>Mileage: Unlimited</li>
                        <li>Insurance: Included in the rental price</li>
                        <li>Cancellation: Free up to 24 hours before pickup</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="booking-form sticky-top" style="top: 100px;">
                    <h3>Book This Vehicle</h3>
                    <div class="booking-price mb-4">$<?php echo $vehicle['daily_rate']; ?> <span>per day</span></div>

                    <?php if ($bookingError): ?>
                    <div class="alert alert-danger"><?php echo $bookingError; ?></div>
                    <?php endif; ?>

                    <form action="vehicle-details.php?id=<?php echo $vehicleId; ?>" method="POST">
                        <div class="mb-3">
                            <label for="pickup_date" class="form-label">Pickup Date</label>
                            <input type="date" class="form-control" id="pickup_date" name="pickup_date" required
                                min="<?php echo date('Y-m-d'); ?>"
                                value="<?php echo isset($_POST['pickup_date']) ? $_POST['pickup_date'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="return_date" class="form-label">Return Date</label>
                            <input type="date" class="form-control" id="return_date" name="return_date" required
                                min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                value="<?php echo isset($_POST['return_date']) ? $_POST['return_date'] : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="pickup_location" class="form-label">Pickup Location</label>
                            <select class="form-select" id="pickup_location" name="pickup_location" required>
                                <option value="Main Office - 123 Main St">Main Office - 123 Main St</option>
                                <option value="Airport Branch - Terminal 1">Airport Branch - Terminal 1</option>
                                <option value="Downtown Branch - 456 Central Ave">Downtown Branch - 456 Central Ave
                                </option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="return_location" class="form-label">Return Location</label>
                            <select class="form-select" id="return_location" name="return_location" required>
                                <option value="Main Office - 123 Main St">Main Office - 123 Main St</option>
                                <option value="Airport Branch - Terminal 1">Airport Branch - Terminal 1</option>
                                <option value="Downtown Branch - 456 Central Ave">Downtown Branch - 456 Central Ave
                                </option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="booking_submit" class="btn btn-primary btn-lg">Book Now</button>
                        </div>

                        <?php if (!isLoggedIn()): ?>
                        <div class="mt-3 text-center">
                            <small class="text-muted">You need to <a
                                    href="login.php?redirect=vehicle-details.php?id=<?php echo $vehicleId; ?>">login</a>
                                to book a vehicle.</small>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Similar Vehicles Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Similar Vehicles</h2>
        <div class="row">
            <?php
            // Get similar vehicles (same make or same type)
            $similarVehicles = getVehicles(['status' => 'Available']);
            $similarVehicles = array_filter($similarVehicles, function($v) use ($vehicle, $vehicleId) {
                return $v['id'] != $vehicleId && ($v['make'] == $vehicle['make'] || $v['fuel_type'] == $vehicle['fuel_type']);
            });
            
            // Display up to 3 similar vehicles
            $count = 0;
            foreach ($similarVehicles as $similarVehicle):
                if ($count >= 3) break;
                $count++;
            ?>
            <div class="col-md-4">
                <div class="vehicle-card card">
                    <img src="<?php echo !empty($similarVehicle['image']) ? '/Car-Rental/assets/images/cars/' . $similarVehicle['image'] : '/Car-Rental/assets/images/cars/default-car.jpg'; ?>"
                        class="card-img-top vehicle-img"
                        alt="<?php echo $similarVehicle['make'] . ' ' . $similarVehicle['model']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $similarVehicle['make'] . ' ' . $similarVehicle['model']; ?>
                            <span class="badge bg-success float-end">Available</span>
                        </h5>
                        <div class="vehicle-features">
                            <div class="vehicle-feature">
                                <i class="fas fa-calendar"></i> <?php echo $similarVehicle['year']; ?>
                            </div>
                            <div class="vehicle-feature">
                                <i class="fas fa-gas-pump"></i> <?php echo $similarVehicle['fuel_type']; ?>
                            </div>
                            <div class="vehicle-feature">
                                <i class="fas fa-cog"></i> <?php echo $similarVehicle['transmission']; ?>
                            </div>
                        </div>
                        <p class="vehicle-price">$<?php echo $similarVehicle['daily_rate']; ?> <span>/ day</span></p>
                        <a href="vehicle-details.php?id=<?php echo $similarVehicle['id']; ?>"
                            class="btn btn-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if ($count === 0): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    No similar vehicles available at the moment.
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>