<?php
require_once __DIR__ . '/../includes/functions.php';

// Process filter parameters
$filters = [];
if (isset($_GET['vehicle_type']) && !empty($_GET['vehicle_type'])) {
    $filters['make'] = $_GET['vehicle_type'];
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
} else {
    $filters['status'] = 'Available'; // Default to available vehicles
}

// Get vehicles based on filters
$vehicles = getVehicles($filters);

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Vehicles Page Header -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1>Our Fleet</h1>
                <p class="lead">Choose from our wide selection of quality vehicles for your next journey.</p>
            </div>
            <div class="col-lg-6">
                <form action="vehicles.php" method="GET" class="d-flex flex-wrap gap-2">
                    <div class="flex-grow-1">
                        <select name="vehicle_type" class="form-select">
                            <option value="">All Vehicle Types</option>
                            <option value="Toyota"
                                <?php echo isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Toyota' ? 'selected' : ''; ?>>
                                Toyota</option>
                            <option value="Honda"
                                <?php echo isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Honda' ? 'selected' : ''; ?>>
                                Honda</option>
                            <option value="Tesla"
                                <?php echo isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Tesla' ? 'selected' : ''; ?>>
                                Tesla</option>
                            <option value="Ford"
                                <?php echo isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'Ford' ? 'selected' : ''; ?>>
                                Ford</option>
                            <option value="BMW"
                                <?php echo isset($_GET['vehicle_type']) && $_GET['vehicle_type'] === 'BMW' ? 'selected' : ''; ?>>
                                BMW</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <?php if (!empty($_GET)): ?>
                    <a href="vehicles.php" class="btn btn-outline-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Vehicles Grid -->
<section class="py-5">
    <div class="container">
        <?php if (count($vehicles) > 0): ?>
        <div class="row">
            <?php foreach ($vehicles as $vehicle): ?>
            <div class="col-md-4">
                <div class="vehicle-card card">
                    <img src="<?php echo !empty($vehicle['image']) ? '/Car-Rental/assets/images/cars/' . $vehicle['image'] : '/Car-Rental/assets/images/cars/default-car.jpg'; ?>"
                        class="card-img-top vehicle-img"
                        alt="<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?> <span
                                class="badge bg-success float-end">Available</span></h5>
                        <div class="vehicle-features">
                            <div class="vehicle-feature">
                                <i class="fas fa-calendar"></i> <?php echo $vehicle['year']; ?>
                            </div>
                            <div class="vehicle-feature">
                                <i class="fas fa-gas-pump"></i> <?php echo $vehicle['fuel_type']; ?>
                            </div>
                            <div class="vehicle-feature">
                                <i class="fas fa-cog"></i> <?php echo $vehicle['transmission']; ?>
                            </div>
                        </div>
                        <p class="vehicle-price">$<?php echo $vehicle['daily_rate']; ?> <span>/ day</span></p>
                        <a href="vehicle-details.php?id=<?php echo $vehicle['id']; ?>"
                            class="btn btn-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <h4 class="text-center">No vehicles available for the selected criteria.</h4>
            <p class="text-center">Please try a different search or check back later.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Vehicle Categories Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Browse By Category</h2>
        <div class="row">
            <div class="col-md-3">
                <a href="vehicles.php?vehicle_type=Sedan" class="text-decoration-none">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <h3>Sedans</h3>
                        <p>Comfortable and economical options for everyday driving and business trips.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="vehicles.php?vehicle_type=SUV" class="text-decoration-none">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <h3>SUVs</h3>
                        <p>Spacious vehicles perfect for family trips and adventures with extra luggage.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="vehicles.php?vehicle_type=Luxury" class="text-decoration-none">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-gem"></i>
                        </div>
                        <h3>Luxury</h3>
                        <p>Premium vehicles offering exceptional comfort and advanced features.</p>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="vehicles.php?vehicle_type=Electric" class="text-decoration-none">
                    <div class="feature-box">
                        <div class="feature-icon">
                            <i class="fas fa-charging-station"></i>
                        </div>
                        <h3>Electric</h3>
                        <p>Eco-friendly options with zero emissions for environmentally conscious drivers.</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>