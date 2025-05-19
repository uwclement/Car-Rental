<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=profile.php');
}

// Check if admin is trying to access
if (isAdmin()) {
    redirect('../admin/index.php');
}

// Get user data
$userId = getCurrentUserId();
$user = getUserById($userId);

if (!$user) {
    redirect('logout.php');
}

// Process form submission for profile update
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $drivingLicense = sanitize($_POST['driving_license']);
    
    // Validate input
    if (empty($name)) {
        $error = 'Name cannot be empty.';
    } else {
        // Update user profile
        $userData = [
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'driving_license' => $drivingLicense
        ];
        
        if (updateUserProfile($userId, $userData)) {
            $success = true;
            // Update user data for display
            $user = getUserById($userId);
            // Update session name
            $_SESSION['user_name'] = $name;
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $name = sanitize($_POST['name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $drivingLicense = sanitize($_POST['driving_license']);
    
    // Prepare user data
    $userData = [
        'name' => $name,
        'phone' => $phone,
        'address' => $address,
        'driving_license' => $drivingLicense
    ];
    
    // Handle image upload if provided
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../assets/images/users/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = basename($_FILES['profile_image']['name']);
        $targetFilePath = $uploadDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        
        // Allow certain file formats
        $allowTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($fileType), $allowTypes)) {
            // Upload file to server
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFilePath)) {
                // Add image to user data
                $userData['profile_image'] = $fileName;
            } else {
                $error = error_get_last();
                $error = 'Sorry, there was an error uploading your file: ' . $error['message'];
            }
        } else {
            $error = 'Sorry, only JPG, JPEG, PNG, & GIF files are allowed.';
        }
    }
    
    // Validate input
    if (empty($name)) {
        $error = 'Name cannot be empty.';
    } elseif (!empty($error)) {
        // Error already set for image upload
    } else {
        // Update user profile
        if (updateUserProfile($userId, $userData)) {
            $success = true;
            // Update user data for display
            $user = getUserById($userId);
            // Update session name
            $_SESSION['user_name'] = $name;
        } else {
            $error = 'Failed to update profile. Please try again.';
        }
    }
}

// Get user bookings
$userBookings = getUserBookings($userId);

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <h2 class="section-title mb-5">My Profile</h2>

        <div class="row">
            <div class="col-lg-4">
                <div class="profile-card mb-4">
                    <div class="text-center mb-4">
                        <img src="<?php echo !empty($user['profile_image']) ? '../assets/images/users/' . $user['profile_image'] : '../assets/images/user-avatar.png'; ?>"
                            alt="User Avatar" class="profile-img rounded-circle"
                            style="width: 150px; height: 150px; object-fit: cover;">
                        <h3><?php echo $user['name']; ?></h3>
                        <p class="text-muted">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
                        </p>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <h5><i class="fas fa-envelope me-2"></i> Email</h5>
                        <p><?php echo $user['email']; ?></p>
                    </div>

                    <div class="mb-3">
                        <h5><i class="fas fa-phone me-2"></i> Phone</h5>
                        <p><?php echo !empty($user['phone']) ? $user['phone'] : 'Not provided'; ?></p>
                    </div>

                    <div class="mb-3">
                        <h5><i class="fas fa-map-marker-alt me-2"></i> Address</h5>
                        <p><?php echo !empty($user['address']) ? $user['address'] : 'Not provided'; ?></p>
                    </div>

                    <div class="mb-3">
                        <h5><i class="fas fa-id-card me-2"></i> Driving License</h5>
                        <p><?php echo !empty($user['driving_license']) ? $user['driving_license'] : 'Not provided'; ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Edit Profile</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($success): ?>
                        <div class="alert alert-success">Profile updated successfully!</div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form action="profile.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?php echo $user['name']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email"
                                    value="<?php echo $user['email']; ?>" disabled>
                                <small class="text-muted">Email address cannot be changed.</small>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone"
                                    value="<?php echo $user['phone']; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control" id="address" name="address"
                                    rows="3"><?php echo $user['address']; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="driving_license" class="form-label">Driving License Number</label>
                                <input type="text" class="form-control" id="driving_license" name="driving_license"
                                    value="<?php echo $user['driving_license']; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="profile_image" class="form-label">Profile Image</label>
                                <?php if (!empty($user['profile_image'])): ?>
                                <div class="mb-2">
                                    <img src="<?php echo '../assets/images/users/' . $user['profile_image']; ?>"
                                        alt="<?php echo $user['name']; ?>" class="img-thumbnail profile-image-preview"
                                        style="max-width: 200px;">
                                </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="profile_image" name="profile_image"
                                    accept="image/*">
                                <div class="form-text">Upload a new profile image. Leave empty to keep the current
                                    image.</div>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Recent Bookings</h4>
                        <a href="my-bookings.php" class="btn btn-outline-primary btn-sm">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (count($userBookings) > 0): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Vehicle</th>
                                        <th>Dates</th>
                                        <th>Status</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (array_slice($userBookings, 0, 3) as $booking): ?>
                                    <tr>
                                        <td>
                                            <?php echo $booking['make'] . ' ' . $booking['model']; ?>
                                        </td>
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
                                            <a href="booking-details.php?id=<?php echo $booking['id']; ?>"
                                                class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            You don't have any bookings yet. <a href="vehicles.php">Browse vehicles</a> to make your
                            first booking.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>
<!-- Add this at the bottom of your profile.php file, before the footer include -->
<script>
// Image preview script
document.addEventListener('DOMContentLoaded', function() {
    const imageInput = document.getElementById('profile_image');
    const previewImage = document.querySelector('.profile-image-preview');

    imageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                if (previewImage) {
                    previewImage.src = e.target.result;
                } else {
                    const newPreview = document.createElement('img');
                    newPreview.src = e.target.result;
                    newPreview.alt = 'Profile Preview';
                    newPreview.className = 'img-thumbnail profile-image-preview';
                    newPreview.style.maxWidth = '200px';

                    const container = imageInput.parentElement;
                    container.insertBefore(newPreview, imageInput.nextSibling);
                }
            }

            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>