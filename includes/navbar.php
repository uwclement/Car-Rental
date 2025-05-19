<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top">
    <div class="container">
        <a class="navbar-brand"
            href="<?php echo isAdmin() ? '/Car-Rental/admin/index.php' : '/Car-Rental/public/index.php'; ?>">
            <i class="fas fa-car-side me-2"></i> RentWheel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php if (isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"
                        href="/Car-Rental/admin/index.php">
                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'vehicles.php' ? 'active' : ''; ?>"
                        href="/Car-Rental/admin/vehicles.php">
                        <i class="fas fa-car me-1"></i> Vehicles
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>"
                        href="/Car-Rental/admin/bookings.php">
                        <i class="fas fa-calendar-check me-1"></i> Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>"
                        href="/Car-Rental/admin/customers.php">
                        <i class="fas fa-users me-1"></i> Customers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>"
                        href="/Car-Rental/admin/reports.php">
                        <i class="fas fa-chart-bar me-1"></i> Reports
                    </a>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"
                        href="/Car-Rental/public/index.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'vehicles.php' ? 'active' : ''; ?>"
                        href="/Car-Rental/public/vehicles.php">
                        <i class="fas fa-car me-1"></i> Vehicles
                    </a>
                </li>
                <?php if (isLoggedIn()): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my-bookings.php' ? 'active' : ''; ?>"
                        href="/Car-Rental/public/my-bookings.php">
                        <i class="fas fa-calendar-alt me-1"></i> My Bookings
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="#contact">
                        <i class="fas fa-envelope me-1"></i> Contact
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ml-auto">
                <?php if (isLoggedIn()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php 
            // Get current user data to access profile image
            $currentUser = getUserById(getCurrentUserId());
            $profileImage = !empty($currentUser['profile_image']) ? '../assets/images/users/' . $currentUser['profile_image'] : '../assets/images/user-avatar.png';
            ?>
                        <img src="<?php echo $profileImage; ?>" alt="Profile" class="rounded-circle me-2"
                            style="width: 28px; height: 28px; object-fit: cover;">
                        <?php echo $_SESSION['user_name']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <?php if (!isAdmin()): ?>
                        <li>
                            <a class="dropdown-item" href="/Car-Rental/public/profile.php">
                                <i class="fas fa-id-card me-1"></i> Profile
                            </a>
                        </li>
                        <?php endif; ?>
                        <li>
                            <a class="dropdown-item" href="/Car-Rental/public/logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i> Logout
                            </a>
                        </li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>"
                        href="/Car-Rental/public/login.php">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>"
                        href="/Car-Rental/public/register.php">
                        <i class="fas fa-user-plus me-1"></i> Register
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>