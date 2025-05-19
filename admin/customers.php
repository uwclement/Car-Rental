<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../public/login.php');
}

// Set admin page flag
$isAdminPage = true;

// Get all users (customers)
$customers = getAllUsers();

// Filter only customers (exclude admins)
$customers = array_filter($customers, function($user) {
    return $user['role'] === 'customer';
});

// Search functionality
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = sanitize($_GET['search']);
    $customers = array_filter($customers, function($customer) use ($search) {
        return (stripos($customer['name'], $search) !== false || stripos($customer['email'], $search) !== false);
    });
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Customer Management</h2>
    <a href="customer-add.php" class="btn btn-primary">
        <i class="fas fa-user-plus me-1"></i> Add New Customer
    </a>
</div>

<!-- Search Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Search Customers</h5>
    </div>
    <div class="card-body">
        <form action="customers.php" method="GET" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Search by Name or Email</label>
                <input type="text" class="form-control" id="search" name="search"
                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                    placeholder="Enter name or email">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Search</button>
                <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
                <a href="customers.php" class="btn btn-outline-secondary">Clear Search</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Customers</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>License</th>
                        <th>Registration Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo $customer['id']; ?></td>
                        <td><?php echo $customer['name']; ?></td>
                        <td><?php echo $customer['email']; ?></td>
                        <td><?php echo !empty($customer['phone']) ? $customer['phone'] : '-'; ?></td>
                        <td><?php echo !empty($customer['driving_license']) ? $customer['driving_license'] : '-'; ?>
                        </td>
                        <td><?php echo formatDate($customer['created_at']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="customer-view.php?id=<?php echo $customer['id']; ?>"
                                    class="btn btn-sm btn-info" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="customer-edit.php?id=<?php echo $customer['id']; ?>"
                                    class="btn btn-sm btn-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="customer-delete.php?id=<?php echo $customer['id']; ?>"
                                    class="btn btn-sm btn-danger" title="Delete"
                                    onclick="return confirm('Are you sure you want to delete this customer? This will also delete all their bookings.');">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($customers) === 0): ?>
                    <tr>
                        <td colspan="7" class="text-center">No customers found</td>
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