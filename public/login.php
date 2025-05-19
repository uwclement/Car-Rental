<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is already logged in
if (isLoggedIn()) {
    redirect(isAdmin() ? '../admin/index.php' : 'index.php');
}

// Process login form
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    // Validate input
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        // Check if user exists
        $user = getUserByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect to appropriate page
            if (isset($_GET['redirect']) && !empty($_GET['redirect'])) {
                redirect($_GET['redirect']);
            } else {
                redirect($user['role'] === 'admin' ? '../admin/index.php' : 'index.php');
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="auth-form">
            <h2 class="mb-4">Login to Your Account</h2>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <form
                action="<?php echo isset($_GET['redirect']) ? 'login.php?redirect=' . urlencode($_GET['redirect']) : 'login.php'; ?>"
                method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $email; ?>"
                        required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                    <label class="form-check-label" for="remember_me">Remember me</label>
                </div>
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>

            <div class="mt-4 text-center">
                <p>Don't have an account? <a
                        href="register.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">Register
                        now</a></p>
            </div>
        </div>
    </div>
</section>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>