<?php
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php?redirect=payment.php');
}

// Check if booking ID is provided
if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    redirect('my-bookings.php');
}

$bookingId = (int) $_GET['booking_id'];
$booking = getBookingById($bookingId);

// Verify booking exists and belongs to the current user
if (!$booking || $booking['user_id'] != getCurrentUserId()) {
    redirect('my-bookings.php');
}

// Check if booking is already confirmed
if ($booking['booking_status'] !== 'Pending') {
    redirect('booking-details.php?id=' . $bookingId);
}

// Process payment form
$paymentSuccess = false;
$paymentError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_submit'])) {
    $paymentMethod = sanitize($_POST['payment_method']);
    $cardNumber = isset($_POST['card_number']) ? sanitize($_POST['card_number']) : '';
    $cardName = isset($_POST['card_name']) ? sanitize($_POST['card_name']) : '';
    $cardExpiry = isset($_POST['card_expiry']) ? sanitize($_POST['card_expiry']) : '';
    $cardCvv = isset($_POST['card_cvv']) ? sanitize($_POST['card_cvv']) : '';
    $mobileNumber = isset($_POST['mobile_number']) ? sanitize($_POST['mobile_number']) : '';
    
    // Simple validation
    $error = false;
    
    if ($paymentMethod === 'Credit Card' || $paymentMethod === 'Debit Card') {
        if (empty($cardNumber) || empty($cardName) || empty($cardExpiry) || empty($cardCvv)) {
            $paymentError = 'Please fill all card details.';
            $error = true;
        } elseif (strlen(preg_replace('/\s+/', '', $cardNumber)) < 16) {
            $paymentError = 'Invalid card number.';
            $error = true;
        } elseif (strlen($cardCvv) < 3) {
            $paymentError = 'Invalid CVV.';
            $error = true;
        }
    } elseif ($paymentMethod === 'MTN Mobile Money') {
        if (empty($mobileNumber)) {
            $paymentError = 'Please enter your MTN Mobile Money number.';
            $error = true;
        } elseif (!preg_match('/^\d{10}$/', $mobileNumber)) {
            $paymentError = 'Invalid mobile number. Please enter a 10-digit number.';
            $error = true;
        }
    }
    
    if (!$error) {
        // Generate a random transaction ID
        $transactionId = 'TXN' . time() . rand(1000, 9999);
        
        // Create payment record
        $paymentData = [
            'booking_id' => $bookingId,
            'amount' => $booking['total_amount'],
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId
        ];
        
        if (createPayment($paymentData)) {
            $paymentSuccess = true;
        } else {
            $paymentError = 'Payment processing failed. Please try again or contact support.';
        }
    }
}

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <?php if ($paymentSuccess): ?>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success fa-5x"></i>
                        </div>
                        <h2 class="mb-4">Payment Successful!</h2>
                        <p class="lead mb-4">Your booking has been confirmed and is ready for pickup.</p>
                        <p class="mb-4">A confirmation email with all details has been sent to your registered email
                            address.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="booking-details.php?id=<?php echo $bookingId; ?>" class="btn btn-primary">View
                                Booking Details</a>
                            <a href="my-bookings.php" class="btn btn-outline-primary">My Bookings</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <h2 class="section-title mb-5">Complete Your Payment</h2>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">Payment Method</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($paymentError): ?>
                        <div class="alert alert-danger"><?php echo $paymentError; ?></div>
                        <?php endif; ?>

                        <form action="payment.php?booking_id=<?php echo $bookingId; ?>" method="POST" id="payment-form">
                            <div class="mb-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="credit_card"
                                        value="Credit Card" checked>
                                    <label class="form-check-label" for="credit_card">
                                        <i class="fab fa-cc-visa me-1"></i>
                                        <i class="fab fa-cc-mastercard me-1"></i>
                                        <i class="fab fa-cc-amex me-1"></i>
                                        Credit Card
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="debit_card"
                                        value="Debit Card">
                                    <label class="form-check-label" for="debit_card">
                                        <i class="fab fa-cc-visa me-1"></i>
                                        <i class="fab fa-cc-mastercard me-1"></i>
                                        Debit Card
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="mtn_money"
                                        value="MTN Mobile Money">
                                    <label class="form-check-label" for="mtn_money">
                                        <i class="fas fa-mobile-alt me-1"></i>
                                        MTN Mobile Money
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal"
                                        value="PayPal">
                                    <label class="form-check-label" for="paypal">
                                        <i class="fab fa-paypal me-1"></i>
                                        PayPal
                                    </label>
                                </div>
                            </div>

                            <div id="card-payment-details">
                                <div class="mb-3">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="card_number" name="card_number"
                                        placeholder="XXXX XXXX XXXX XXXX">
                                </div>
                                <div class="mb-3">
                                    <label for="card_name" class="form-label">Cardholder Name</label>
                                    <input type="text" class="form-control" id="card_name" name="card_name"
                                        placeholder="Name on card">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="card_expiry" class="form-label">Expiration Date</label>
                                        <input type="text" class="form-control" id="card_expiry" name="card_expiry"
                                            placeholder="MM/YY">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="card_cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="card_cvv" name="card_cvv"
                                            placeholder="XXX">
                                    </div>
                                </div>
                            </div>

                            <div id="mobile-money-details" style="display: none;">
                                <div class="mb-3">
                                    <label for="mobile_number" class="form-label">MTN Mobile Money Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+250</span>
                                        <input type="text" class="form-control" id="mobile_number" name="mobile_number"
                                            placeholder="7XXXXXXXX">
                                    </div>
                                    <div class="form-text text-muted">Enter your 10-digit mobile number registered with
                                        MTN Mobile Money.</div>
                                </div>
                                <div class="alert alert-info">
                                    <p class="mb-0"><i class="fas fa-info-circle me-2"></i> You will receive an MTN
                                        Mobile Money prompt on your phone to confirm this payment.</p>
                                </div>
                            </div>

                            <div id="paypal-details" style="display: none;">
                                <div class="alert alert-info">
                                    <p>You will be redirected to PayPal to complete your payment.</p>
                                </div>
                            </div>

                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="save_payment" name="save_payment">
                                <label class="form-check-label" for="save_payment">
                                    Save payment method for future bookings
                                </label>
                            </div>

                            <button type="submit" name="payment_submit" class="btn btn-primary btn-lg w-100">Complete
                                Payment</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Booking Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <img src="<?php echo !empty($booking['image']) ? '../assets/images/cars/' . $booking['image'] : '../assets/images/cars/default-car.jpg'; ?>"
                                alt="<?php echo $booking['make'] . ' ' . $booking['model']; ?>"
                                class="img-fluid rounded mb-3">
                            <h5><?php echo $booking['make'] . ' ' . $booking['model']; ?></h5>
                            <p class="text-muted"><?php echo $booking['registration_number']; ?></p>
                        </div>

                        <div class="mb-3">
                            <h6>Pickup Details</h6>
                            <p class="mb-1"><i class="far fa-calendar-alt me-2"></i>
                                <?php echo formatDate($booking['pickup_date']); ?></p>
                            <p><i class="fas fa-map-marker-alt me-2"></i> <?php echo $booking['pickup_location']; ?></p>
                        </div>

                        <div class="mb-3">
                            <h6>Return Details</h6>
                            <p class="mb-1"><i class="far fa-calendar-alt me-2"></i>
                                <?php echo formatDate($booking['return_date']); ?></p>
                            <p><i class="fas fa-map-marker-alt me-2"></i> <?php echo $booking['return_location']; ?></p>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Daily Rate:</span>
                            <span>$<?php echo $booking['daily_rate']; ?></span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Rental Duration:</span>
                            <span><?php echo calculateDays($booking['pickup_date'], $booking['return_date']); ?>
                                days</span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo $booking['total_amount']; ?></span>
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span>$0.00</span>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <strong>Total Amount:</strong>
                            <strong>$<?php echo $booking['total_amount']; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const cardDetails = document.getElementById('card-payment-details');
    const mobileMoneyDetails = document.getElementById('mobile-money-details');
    const paypalDetails = document.getElementById('paypal-details');

    function updatePaymentForm() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;

        // Hide all payment method details first
        cardDetails.style.display = 'none';
        mobileMoneyDetails.style.display = 'none';
        paypalDetails.style.display = 'none';

        // Show the selected payment method details
        if (selectedMethod === 'Credit Card' || selectedMethod === 'Debit Card') {
            cardDetails.style.display = 'block';
        } else if (selectedMethod === 'MTN Mobile Money') {
            mobileMoneyDetails.style.display = 'block';
        } else if (selectedMethod === 'PayPal') {
            paypalDetails.style.display = 'block';
        }
    }

    // Set initial state
    updatePaymentForm();

    // Add event listeners to payment method radio buttons
    paymentMethods.forEach(method => {
        method.addEventListener('change', updatePaymentForm);
    });

    // Format card number with spaces
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\s+/g, '').replace(/\D/g, '');
            let formattedValue = '';

            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) {
                    formattedValue += ' ';
                }
                formattedValue += value[i];
            }

            this.value = formattedValue;
        });
    }

    // Format card expiry date (MM/YY)
    const cardExpiryInput = document.getElementById('card_expiry');
    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            let formattedValue = '';

            if (value.length > 0) {
                formattedValue = value.substring(0, 2);
                if (value.length > 2) {
                    formattedValue += '/' + value.substring(2, 4);
                }
            }

            this.value = formattedValue;
        });
    }

    // Format mobile number
    const mobileNumberInput = document.getElementById('mobile_number');
    if (mobileNumberInput) {
        mobileNumberInput.addEventListener('input', function(e) {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 10) {
                value = value.substring(0, 10);
            }
            this.value = value;
        });
    }
});
</script>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>