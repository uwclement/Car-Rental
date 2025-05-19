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

// Process form submission
$success = false;
$error = '';
$uploadError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_vehicle'])) {
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
        'description' => $description
    ];
    
    // Handle image upload if provided
    if (isset($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/cars/';
        $fileName = basename($_FILES['vehicle_image']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        
        // Allow certain file formats
        $allowTypes = ['jpg', 'jpeg', 'png', 'gif'];
       if (isset($_FILES['vehicle_image']) && $_FILES['vehicle_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../assets/images/cars/';
    
    // Create directory if it doesn't exist
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Debug information
    error_log("Upload directory: " . $uploadDir);
    error_log("Temp file: " . $_FILES['vehicle_image']['tmp_name']);
    
    $fileName = basename($_FILES['vehicle_image']['name']);
    $targetFilePath = $uploadDir . $fileName;
    
    error_log("Target path: " . $targetFilePath);
    
    $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
    
    // Allow certain file formats
    $allowTypes = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array(strtolower($fileType), $allowTypes)) {
        // Upload file to server
        if (move_uploaded_file($_FILES['vehicle_image']['tmp_name'], $targetFilePath)) {
            // Add image to vehicle data
            $vehicleData['image'] = $fileName;
            error_log("File uploaded successfully to: " . $targetFilePath);
        } else {
            $error = error_get_last();
            $uploadError = 'Sorry, there was an error uploading your file: ' . $error['message'];
            error_log("Upload error: " . $uploadError);
        }
    } else {
        $uploadError = 'Sorry, only JPG, JPEG, PNG, & GIF files are allowed.';
        error_log("Invalid file type: " . $fileType);
    }
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
        // Update vehicle
        if (updateVehicle($vehicleId, $vehicleData)) {
            $success = true;
            // Refresh vehicle data
            $vehicle = getVehicleById($vehicleId);
        } else {
            $error = 'Failed to update vehicle. Please try again.';
        }
    }
}


/**
 * Add new vehicle
 * @param array $vehicleData
 * @return int|bool
 */
// function addVehicle($vehicleData) {
//     global $conn;
    
//     $make = $conn->real_escape_string($vehicleData['make']);
//     $model = $conn->real_escape_string($vehicleData['model']);
//     $year = (int) $vehicleData['year'];
//     $registrationNumber = $conn->real_escape_string($vehicleData['registration_number']);
//     $color = $conn->real_escape_string($vehicleData['color']);
//     $seatingCapacity = (int) $vehicleData['seating_capacity'];
//     $fuelType = $conn->real_escape_string($vehicleData['fuel_type']);
//     $transmission = $conn->real_escape_string($vehicleData['transmission']);
//     $dailyRate = (float) $vehicleData['daily_rate'];
//     $status = $conn->real_escape_string($vehicleData['status']);
//     $image = $conn->real_escape_string($vehicleData['image']);
//     $description = $conn->real_escape_string($vehicleData['description']);
    
//     // Check if registration number already exists
//     $checkSql = "SELECT * FROM vehicles WHERE registration_number = '{$registrationNumber}'";
//     $checkResult = $conn->query($checkSql);
    
//     if ($checkResult->num_rows > 0) {
//         return false; // Registration number already exists
//     }
    
//     $sql = "INSERT INTO vehicles (make, model, year, registration_number, color, seating_capacity, 
//             fuel_type, transmission, daily_rate, status, image, description) 
//             VALUES ('{$make}', '{$model}', {$year}, '{$registrationNumber}', '{$color}', {$seatingCapacity}, 
//             '{$fuelType}', '{$transmission}', {$dailyRate}, '{$status}', '{$image}', '{$description}')";
    
//     if ($conn->query($sql)) {
//         return $conn->insert_id;
//     }
    
//     return false;
// }

/**
 * Delete vehicle
 * @param int $id
 * @return bool
 */
// function deleteVehicle($id) {
//     global $conn;
    
//     $id = (int) $id;
    
//     // Check if vehicle has any active bookings
//     $checkSql = "SELECT * FROM bookings WHERE vehicle_id = {$id} AND booking_status IN ('Pending', 'Confirmed')";
//     $checkResult = $conn->query($checkSql);
    
//     if ($checkResult->num_rows > 0) {
//         return false; // Cannot delete vehicle with active bookings
//     }
    
//     $sql = "DELETE FROM vehicles WHERE id = {$id}";
    
//     return $conn->query($sql);
// }

// /**
//  * Update vehicle status
//  * @param int $id
//  * @param string $status
//  * @return bool
//  */
// function updateVehicleStatus($id, $status) {
//     global $conn;
    
//     $id = (int) $id;
//     $status = $conn->real_escape_string($status);
    
//     $sql = "UPDATE vehicles SET status = '{$status}' WHERE id = {$id}";
    
//     return $conn->query($sql);
// }

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Edit Vehicle</h2>
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
        <div class="alert alert-success">Vehicle updated successfully!</div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="vehicle-edit.php?id=<?php echo $vehicleId; ?>" method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="make" class="form-label">Make</label>
                        <input type="text" class="form-control" id="make" name="make"
                            value="<?php echo $vehicle['make']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="model" class="form-label">Model</label>
                        <input type="text" class="form-control" id="model" name="model"
                            value="<?php echo $vehicle['model']; ?>" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="year" class="form-label">Year</label>
                        <input type="number" class="form-control" id="year" name="year"
                            value="<?php echo $vehicle['year']; ?>" min="1900" max="<?php echo date('Y') + 1; ?>"
                            required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="registration_number" class="form-label">Registration Number</label>
                        <input type="text" class="form-control" id="registration_number" name="registration_number"
                            value="<?php echo $vehicle['registration_number']; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="text" class="form-control" id="color" name="color"
                            value="<?php echo $vehicle['color']; ?>">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="seating_capacity" class="form-label">Seating Capacity</label>
                        <input type="number" class="form-control" id="seating_capacity" name="seating_capacity"
                            value="<?php echo $vehicle['seating_capacity']; ?>" min="1" max="20">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="fuel_type" class="form-label">Fuel Type</label>
                        <select class="form-select" id="fuel_type" name="fuel_type">
                            <option value="Petrol" <?php echo $vehicle['fuel_type'] === 'Petrol' ? 'selected' : ''; ?>>
                                Petrol</option>
                            <option value="Diesel" <?php echo $vehicle['fuel_type'] === 'Diesel' ? 'selected' : ''; ?>>
                                Diesel</option>
                            <option value="Electric"
                                <?php echo $vehicle['fuel_type'] === 'Electric' ? 'selected' : ''; ?>>Electric</option>
                            <option value="Hybrid" <?php echo $vehicle['fuel_type'] === 'Hybrid' ? 'selected' : ''; ?>>
                                Hybrid</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="transmission" class="form-label">Transmission</label>
                        <select class="form-select" id="transmission" name="transmission">
                            <option value="Automatic"
                                <?php echo $vehicle['transmission'] === 'Automatic' ? 'selected' : ''; ?>>Automatic
                            </option>
                            <option value="Manual"
                                <?php echo $vehicle['transmission'] === 'Manual' ? 'selected' : ''; ?>>Manual</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="daily_rate" class="form-label">Daily Rate ($)</label>
                        <input type="number" class="form-control" id="daily_rate" name="daily_rate"
                            value="<?php echo $vehicle['daily_rate']; ?>" min="0" step="0.01" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Available"
                                <?php echo $vehicle['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                            <option value="Booked" <?php echo $vehicle['status'] === 'Booked' ? 'selected' : ''; ?>>
                                Booked</option>
                            <option value="Maintenance"
                                <?php echo $vehicle['status'] === 'Maintenance' ? 'selected' : ''; ?>>Maintenance
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description"
                    rows="3"><?php echo $vehicle['description']; ?></textarea>
            </div>

            <div class="mb-4">
                <label for="vehicle_image" class="form-label">Vehicle Image</label>
                <?php if (!empty($vehicle['image'])): ?>
                <div class="mb-2">
                    <img src="<?php echo '../assets/images/cars/' . $vehicle['image']; ?>"
                        alt="<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>"
                        class="img-thumbnail vehicle-image-preview" style="max-width: 200px;">
                </div>
                <?php endif; ?>
                <input type="file" class="form-control" id="vehicle_image" name="vehicle_image" accept="image/*">
                <div class="form-text">Upload a new image to replace the current one. Leave empty to keep the current
                    image.</div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" name="update_vehicle" class="btn btn-primary">Update Vehicle</button>
            </div>
        </form>
    </div>
</div>

<script>
// Image preview script
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('vehicle_image');
    const previewImage = document.querySelector('.vehicle-image-preview');

    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                if (previewImage) {
                    previewImage.src = e.target.result;
                } else {
                    const newPreview = document.createElement('img');
                    newPreview.src = e.target.result;
                    newPreview.alt = 'Vehicle Preview';
                    newPreview.className = 'img-thumbnail vehicle-image-preview';
                    newPreview.style.maxWidth = '200px';

                    const container = imageInput.parentElement;
                    container.insertBefore(newPreview, imageInput);
                }
            }

            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>