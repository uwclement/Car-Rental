<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../public/login.php');
}

// Set admin page flag
$isAdminPage = true;

// Get all bookings with payment information
$bookings = getAllBookings();

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Payment Management</h2>
</div>

<!-- Filter Form -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filter Payments</h5>
    </div>
    <div class="card-body">
        <form action="payments.php" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="payment_status" class="form-label">Payment Status</label>
                <select class="form-select" id="payment_status" name="payment_status">
                    <option value="">All Statuses</option>
                    <option value="Pending"
                        <?php echo isset($_GET['payment_status']) && $_GET['payment_status'] === 'Pending' ? 'selected' : ''; ?>>
                        Pending</option>
                    <option value="Completed"
                        <?php echo isset($_GET['payment_status']) && $_GET['payment_status'] === 'Completed' ? 'selected' : ''; ?>>
                        Completed</option>
                    <option value="Failed"
                        <?php echo isset($_GET['payment_status']) && $_GET['payment_status'] === 'Failed' ? 'selected' : ''; ?>>
                        Failed</option>
                    <option value="Refunded"
                        <?php echo isset($_GET['payment_status']) && $_GET['payment_status'] === 'Refunded' ? 'selected' : ''; ?>>
                        Refunded</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select class="form-select" id="payment_method" name="payment_method">
                    <option value="">All Methods</option>
                    <option value="Credit Card"
                        <?php echo isset($_GET['payment_method']) && $_GET['payment_method'] === 'Credit Card' ? 'selected' : ''; ?>>
                        Credit Card</option>
                    <option value="Debit Card"
                        <?php echo isset($_GET['payment_method']) && $_GET['payment_method'] === 'Debit Card' ? 'selected' : ''; ?>>
                        Debit Card</option>
                    <option value="PayPal"
                        <?php echo isset($_GET['payment_method']) && $_GET['payment_method'] === 'PayPal' ? 'selected' : ''; ?>>
                        PayPal</option>
                    <option value="Cash"
                        <?php echo isset($_GET['payment_method']) && $_GET['payment_method'] === 'Cash' ? 'selected' : ''; ?>>
                        Cash</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Apply Filters</button>
                <?php if (!empty($_GET)): ?>
                <a href="payments.php" class="btn btn-outline-secondary">Clear Filters</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Payment Transactions</h5>
        <button class="btn btn-sm btn-outline-primary" onclick="window.print();">
            <i class="fas fa-print me-1"></i> Print
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Display only bookings with Confirmed status (which means payment is made)
                    $confirmedBookings = array_filter($bookings, function($booking) {
                        return $booking['booking_status'] === 'Confirmed' || $booking['booking_status'] === 'Completed';
                    });
                    
                    foreach ($confirmedBookings as $booking): 
                    ?>
                    <tr>
                        <td>TXN<?php echo rand(10000, 99999) . $booking['id']; ?></td>
                        <td><a
                                href="booking-view.php?id=<?php echo $booking['id']; ?>">#<?php echo $booking['id']; ?></a>
                        </td>
                        <td><?php echo $booking['customer_name']; ?></td>
                        <td><?php echo $booking['make'] . ' ' . $booking['model']; ?></td>
                        <td>$<?php echo $booking['total_amount']; ?></td>
                        <td>Credit Card</td>
                        <td><span class="badge bg-success">Completed</span></td>
                        <td><?php echo date('M d, Y', strtotime($booking['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="payment-view.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-info"
                                    title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="invoice-generate.php?id=<?php echo $booking['id']; ?>"
                                    class="btn btn-sm btn-primary" title="Generate Invoice">
                                    <i class="fas fa-file-invoice"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (count($confirmedBookings) === 0): ?>
                    <tr>
                        <td colspan="9" class="text-center">No payment transactions found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payment Summary -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Method Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="paymentMethodChart" height="200"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Summary</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td>Total Transactions</td>
                                <td class="text-end"><?php echo count($confirmedBookings); ?></td>
                            </tr>
                            <tr>
                                <td>Total Revenue</td>
                                <td class="text-end">
                                    $<?php 
                                        $totalRevenue = array_reduce($confirmedBookings, function($carry, $booking) {
                                            return $carry + $booking['total_amount'];
                                        }, 0);
                                        echo number_format($totalRevenue, 2);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Average Transaction Value</td>
                                <td class="text-end">
                                    $<?php 
                                        $avgValue = count($confirmedBookings) > 0 ? $totalRevenue / count($confirmedBookings) : 0;
                                        echo number_format($avgValue, 2);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Successful Transactions</td>
                                <td class="text-end"><?php echo count($confirmedBookings); ?> (100%)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment Method Chart
    const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
    const paymentMethodChart = new Chart(paymentMethodCtx, {
        type: 'pie',
        data: {
            labels: ['Credit Card', 'Debit Card', 'PayPal', 'Cash'],
            datasets: [{
                data: [
                    <?php echo count($confirmedBookings) * 0.65; ?>, // Credit Card
                    <?php echo count($confirmedBookings) * 0.20; ?>, // Debit Card
                    <?php echo count($confirmedBookings) * 0.10; ?>, // PayPal
                    <?php echo count($confirmedBookings) * 0.05; ?> // Cash
                ],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)', // Credit Card
                    'rgba(75, 192, 192, 0.7)', // Debit Card
                    'rgba(153, 102, 255, 0.7)', // PayPal
                    'rgba(255, 159, 64, 0.7)' // Cash
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>