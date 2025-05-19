<div class="sidebar bg-dark text-white p-3 h-100">
    <h4 class="mb-4 border-bottom pb-2">Admin Panel</h4>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>"
                href="/Car-Rental/admin/index.php">
                <i class="fas fa-tachometer-alt me-2"></i> Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'vehicles.php' ? 'active' : ''; ?>"
                href="/Car-Rental/admin/vehicles.php">
                <i class="fas fa-car me-2"></i> Vehicles
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>"
                href="/Car-Rental/admin/bookings.php">
                <i class="fas fa-calendar-check me-2"></i> Bookings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>"
                href="/Car-Rental/admin/customers.php">
                <i class="fas fa-users me-2"></i> Customers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>"
                href="/Car-Rental/admin/payments.php">
                <i class="fas fa-credit-card me-2"></i> Payments
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link text-white <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>"
                href="/Car-Rental/admin/reports.php">
                <i class="fas fa-chart-bar me-2"></i> Reports
            </a>
        </li>
        <li class="nav-item mt-4">
            <a class="nav-link text-white" href="/Car-Rental/public/logout.php">
                <i class="fas fa-sign-out-alt me-2"></i> Logout
            </a>
        </li>
    </ul>
</div>