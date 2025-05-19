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

if (isset($_GET['make']) && !empty($_GET['make'])) {
    $filters['make'] = sanitize($_GET['make']);
}

// Get all vehicles based on filters
$vehicles = getVehicles($filters);

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Vehicle Management</h2>
    <a href="vehicle-add.php" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Add New Vehicle
    </a>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Vehicles</h5>
    </div>
    <div class="card-body">
        <form action="vehicles.php" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Statuses</option>
                    <option value="Available"
                        <?php echo isset($_GET['status']) && $_GET['status'] === 'Available' ? 'selected' : ''; ?>>
                        Available</option>
                    <option value="Booked"
                        <?php echo isset($_GET['status']) && $_GET['status'] === 'Booked' ? 'selected' : ''; ?>>Booked
                    </option>
                    <option value="Maintenance"
                        <?php echo isset($_GET['status']) && $_GET['status'] === 'Maintenance' ? 'selected' : ''; ?>>
                        Maintenance</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="make" class="form-label">Make</label>
                <select class="form-select" id="make" name="make">
                    <option value="">All Makes</option>
                    <option value="Toyota"
                        <?php echo isset($_GET['make']) && $_GET['make'] === 'Toyota' ? 'selected' : ''; ?>>Toyota
                    </option>
                    <option value="Honda"
                        <?php echo isset($_GET['make']) && $_GET['make'] === 'Honda' ? 'selected' : ''; ?>>Honda
                    </option>
                    <option value="Tesla"
                        <?php echo isset($_GET['make']) && $_GET['make'] === 'Tesla' ? 'selected' : ''; ?>>Tesla
                    </option>
                    <option value="Ford"
                        <?php echo isset($_GET['make']) && $_GET['make'] === 'Ford' ? 'selected' : ''; ?>>Ford</option>
                    <option value="BMW"
                        <?php echo isset($_GET['make']) && $_GET['make'] === 'BMW' ? 'selected' : ''; ?>>BMW</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                <?php if (!empty($_GET)): ?>
                <a href="vehicles.php" class="btn btn-outline-secondary">Clear Filters</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Vehicles Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Vehicles</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Vehicle Details</th>
                        <th>Registration</th>
                        <th>Features</th>
                        <th>Daily Rate</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $vehicle): ?>
                    <tr>
                        <td>
                            <img src="<?php echo !empty($vehicle['image']) ? '/Car-Rental/assets/images/cars/' . $vehicle['image'] : '/Car-Rental/assets/images/cars/default-car.jpg'; ?>"
                                alt="<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>"
                                class="admin-img-thumbnail">
                        </td>
                        <td>
                            <strong><?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?></strong><br>
                            <small class="text-muted"><?php echo $vehicle['year']; ?></small>
                        </td>
                        <td><?php echo $vehicle['registration_number']; ?></td>
                        <td>
                            <span class="badge bg-secondary me-1"><?php echo $vehicle['fuel_type']; ?></span>
                            <span class="badge bg-secondary me-1"><?php echo $vehicle['transmission']; ?></span>
                            <span class="badge bg-secondary"><?php echo $vehicle['seating_capacity']; ?> Seats</span>
                        </td>
                        <td>$<?php echo $vehicle['daily_rate']; ?></td>
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
                                <a href="vehicle-view.php?id=<?php echo $vehicle['id']; ?>" class="btn btn-sm btn-info"
                                    title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="vehicle-edit.php?id=<?php echo $vehicle['id']; ?>"
                                    class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="vehicle-delete.php?id=<?php echo $vehicle['id']; ?>"
                                    class="btn btn-sm btn-danger" title="Delete"
                                    onclick="return confirm('Are you sure you want to delete this vehicle?');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($vehicles) === 0): ?>
                    <tr>
                        <td colspan="7" class="text-center">No vehicles found</td>
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