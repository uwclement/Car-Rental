<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? '../admin/index.php' : 'index.php');
}

// Process registration form
$error = '';
$success = false;
$formData = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'address' => '',
    'driving_license' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $formData = [
        'name' => sanitize($_POST['name']),
        'email' => sanitize($_POST['email']),
        'phone' => sanitize($_POST['phone']),
        'address' => sanitize($_POST['address']),
        'driving_license' => sanitize($_POST['driving_license'])
    ];
    
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate input
    if (empty($formData['name']) || empty($formData['email']) || empty($password) || empty($confirmPassword)) {
        $error = 'Please fill all required fields.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $existingUser = getUserByEmail($formData['email']);
        
        if ($existingUser) {
            $error = 'Email address is already registered. Please use a different email.';
        } else {
            // Register user
            $userData = $formData;
            $userData['password'] = $password;
            
            $userId = registerUser($userData);
            
            if ($userId) {
                $success = true;
                
                // Auto login after registration
                $user = getUserById($userId);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Redirect to appropriate page
                if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                    redirect($_GET['redirect']);
                } else {
                    redirect('index.php');
                }
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="auth-form">
            <h2 class="mb-4">Create an Account</h2>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="alert alert-success">
                Registration successful! You are now logged in.
                <script>
                setTimeout(function() {
                    window.location.href = 'index.php';
                }, 2000);
                </script>
            </div>
            <?php else: ?>
            <form
                action="<?php echo isset($_GET['redirect']) ? 'register.php?redirect=' . urlencode($_GET['redirect']) : 'register.php'; ?>"
                method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name *</label>
                    <input type="text" class="form-control" id="name" name="name"
                        value="<?php echo $formData['name']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address *</label>
                    <input type="email" class="form-control" id="email" name="email"
                        value="<?php echo $formData['email']; ?>" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <small class="text-muted">Password must be at least 6 characters long.</small>
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phone" name="phone"
                        value="<?php echo $formData['phone']; ?>">
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <textarea class="form-control" id="address" name="address"
                        rows="2"><?php echo $formData['address']; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="driving_license" class="form-label">Driving License Number</label>
                    <input type="text" class="form-control" id="driving_license" name="driving_license"
                        value="<?php echo $formData['driving_license']; ?>">
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">I agree to the <a href="#" data-bs-toggle="modal"
                            data-bs-target="#termsModal">Terms and Conditions</a></label>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <p>Already have an account? <a
                        href="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">Login</a>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Terms and Conditions Modal -->
<div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>1. Acceptance of Terms</h5>
                <p>By registering for an account with our Car Rental Service, you agree to comply with and be bound by
                    the following terms and conditions.</p>

                <h5>2. User Account</h5>
                <p>You are responsible for maintaining the confidentiality of your account information and password. You
                    agree to accept responsibility for all activities that occur under your account.</p>

                <h5>3. Rental Requirements</h5>
                <p>To rent a vehicle, you must be at least 21 years of age and possess a valid driver's license.
                    Additional driver requirements may apply based on the vehicle type and rental location.</p>

                <h5>4. Payment and Fees</h5>
                <p>You agree to pay all fees associated with your rental, including the rental rate, insurance, and any
                    additional charges incurred during your rental period, such as late returns, fuel, or damage fees.
                </p>

                <h5>5. Cancellation Policy</h5>
                <p>Cancellations made more than 24 hours before the scheduled pickup time will receive a full refund.
                    Cancellations made within 24 hours of the scheduled pickup time may be subject to a cancellation
                    fee.</p>

                <h5>6. Privacy Policy</h5>
                <p>Your personal information will be handled in accordance with our Privacy Policy. By agreeing to these
                    Terms, you also consent to our collection and use of your data as described in the Privacy Policy.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>