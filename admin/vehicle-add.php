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
$uploadError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_vehicle'])) {
    // Get form data
    $make = sanitize($_POST['make']);
    $model = sanitize($_POST['model']);
    $year = (int) $_POST['year'];
    $registrationNumber = sanitize($_POST['registration_number']);
    $color = sanitize($_POST['color']);
    $seatingCapacity = (int) $_POST['seating_capacity'];
    $fuelType = sanitize($_POST['fuel_type']);
    $transmission = sanitize($_POST['transmission']);
    $dailyRate = (float) $_POST['daily_rate'];
    $status = sanitize($_POST['status']);
    $description = sanitize($_POST['description']);
    
    // Prepare vehicle data
    $vehicleData = [
        'make' => $make,
        'model' => $model,
        'year' => $year,
        'registration_number' => $registrationNumber,
        'color' => $color,
        'seating_capacity' => $seatingCapacity,
        'fuel_type' => $fuelType,
        'transmission' => $transmission,
        'daily_rate' => $dailyRate,
        'status' => $status,
        'description' => $description,
        'image' => '' // Default empty image
    ];
    
    // Handle image upload if provided
    if (isset($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/cars/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = basename($_FILES['vehicle_image']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        
        // Allow certain file formats
        $allowTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($fileType), $allowTypes)) {
            // Upload file to server
            if (move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $targetFilePath)) {
                // Add image to vehicle data
                $vehicleData['image'] = $fileName;
            } else {
                $uploadError = 'Sorry, there was an error uploading your file.';
            }
        } else {
            $uploadError = 'Sorry, only JPG, JPEG, PNG, & GIF files are allowed.';
        }
    }
    
    // Validate input
    if (empty($make) || empty($model) || empty($registrationNumber)) {
        $error = 'Make, model, and registration number are required.';
    } elseif ($year < 1900 || $year > date('Y') + 1) {
        $error = 'Please enter a valid year.';
    } elseif ($dailyRate <= 0) {
        $error = 'Daily rate must be greater than zero.';
    } elseif ($uploadError) {
        $error = $uploadError;
    } else {
        // Add vehicle
        $vehicleId = addVehicle($vehicleData);
        
        if ($vehicleId) {
            $success = true;
            
            // Reset form after successful submission
            $vehicleData = [
                'make' => '',
                'model' => '',
                'year' => date('Y'),
                'registration_number' => '',
                'color' => '',
                'seating_capacity' => 5,
                'fuel_type' => 'Petrol',
                'transmission' => 'Automatic',
                'daily_rate' => 0,
                'status' => 'Available',
                'description' => '',
                'image' => ''
            ];
        } else {
            $error = 'Failed to add vehicle. Please try again.';
        }
    }
} else {
    // Default values for new vehicle
    $vehicleData = [
        'make' => '',
        'model' => '',
        'year' => date('Y'),
        'registration_number' => '',
        'color' => '',
        'seating_capacity' => 5,
        'fuel_type' => 'Petrol',
        'transmission' => 'Automatic',
        'daily_rate' => 0,
        'status' => 'Available',
        'description' => '',
        'image' => ''
    ];
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Add New Vehicle</h2>
    <a href="vehicles.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Vehicles
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Vehicle Information</h5>
    </div>
    <div class="card-body">
        <?php if ($success): ?>
        <div class="alert alert-success">
            <h4 class="alert-heading">Vehicle added successfully!</h4>
            <p>The new vehicle has been added to your inventory.</p>
            <hr>
            <div class="d-flex gap-2">
                <a href="vehicles.php" class="btn btn-primary">View All Vehicles</a>
                <a href="vehicle-add.php" class="btn btn-outline-primary">Add Another Vehicle</a>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form action="vehicle-add.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="make" class="form-label">Make</label>
                        <input type="text" class="form-control" id="make" name="make"
                            value="<?php echo $vehicleData['make']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="model" class="form-label">Model</label>
                        <input type="text" class="form-control" id="model" name="model"
                            value="<?php echo $vehicleData['model']; ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="year" class="form-label">Year</label>
                        <input type="number" class="form-control" id="year" name="year"
                            value="<?php echo $vehicleData['year']; ?>" min="1900" max="<?php echo date('Y') + 1; ?>"
                            required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="registration_number" class="form-label">Registration Number</label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number"
                            value="<?php echo $vehicleData['registration_number']; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="text" class="form-control" id="color" name="color"
                            value="<?php echo $vehicleData['color']; ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="seating_capacity" class="form-label">Seating Capacity</label>
                        <input type="number" class="form-control" id="seating_capacity" name="seating_capacity"
                            value="<?php echo $vehicleData['seating_capacity']; ?>" min="1" max="20">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="fuel_type" class="form-label">Fuel Type</label>
                        <select class="form-select" id="fuel_type" name="fuel_type">
                            <option value="Petrol"
                                <?php echo $vehicleData['fuel_type'] === 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
                            <option value="Diesel"
                                <?php echo $vehicleData['fuel_type'] === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
                            <option value="Electric"
                                <?php echo $vehicleData['fuel_type'] === 'Electric' ? 'selected' : ''; ?>>Electric
                            </option>
                            <option value="Hybrid"
                                <?php echo $vehicleData['fuel_type'] === 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="transmission" class="form-label">Transmission</label>
                        <select class="form-select" id="transmission" name="transmission">
                            <option value="Automatic"
                                <?php echo $vehicleData['transmission'] === 'Automatic' ? 'selected' : ''; ?>>Automatic
                            </option>
                            <option value="Manual"
                                <?php echo $vehicleData['transmission'] === 'Manual' ? 'selected' : ''; ?>>Manual
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="daily_rate" class="form-label">Daily Rate ($)</label>
                        <input type="number" class="form-control" id="daily_rate" name="daily_rate"
                            value="<?php echo $vehicleData['daily_rate']; ?>" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Available"
                                <?php echo $vehicleData['status'] === 'Available' ? 'selected' : ''; ?>>Available
                            </option>
                            <option value="Booked" <?php echo $vehicleData['status'] === 'Booked' ? 'selected' : ''; ?>>
                                Booked</option>
                            <option value="Maintenance"
                                <?php echo $vehicleData['status'] === 'Maintenance' ? 'selected' : ''; ?>>Maintenance
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"
                    rows="3"><?php echo $vehicleData['description']; ?></textarea>
            </div>

            <div class="mb-4">
                <label for="vehicle_image" class="form-label">Vehicle Image</label>
                <input type="file" class="form-control" id="vehicle_image" name="vehicle_image" accept="image/*">
                <div class="form-text">Upload an image of the vehicle. Recommended size: 800x600 pixels.</div>
                <div id="image-preview" class="mt-2" style="display: none;">
                    <img src="" alt="Vehicle Preview" class="img-thumbnail" style="max-width: 200px;">
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="add_vehicle" class="btn btn-primary">Add Vehicle</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
// Image preview script
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('vehicle_image');
    const previewContainer = document.getElementById('image-preview');
    const previewImage = previewContainer.querySelector('img');

    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                previewImage.src = e.target.result;
                previewContainer.style.display = 'block';
            }

            reader.readAsDataURL(this.files[0]);
        } else {
            previewContainer.style.display = 'none';
        }
    });
});
</script>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>