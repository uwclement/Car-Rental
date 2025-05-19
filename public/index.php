<?php
require_once __DIR__ . '/../includes/functions.php';

// Get available vehicles for the homepage
$availableVehicles = getVehicles(['status' => 'Available']);

// Include header
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 hero-content">
                <h1>Rent Your Dream Car Today</h1>
                <p>Experience the freedom of the open road with our premium rental fleet. Affordable rates, flexible
                    booking, and exceptional service.</p>
                <a href="vehicles.php" class="btn btn-primary btn-lg">Browse Vehicles</a>
            </div>
            <div class="col-lg-5 offset-lg-1">
                <div class="search-form">
                    <h3>Find Your Perfect Car</h3>
                    <form action="vehicles.php" method="GET">
                        <div class="mb-3">
                            <label for="pickup_date" class="form-label">Pickup Date</label>
                            <input type="date" class="form-control" id="pickup_date" name="pickup_date" required
                                min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="return_date" class="form-label">Return Date</label>
                            <input type="date" class="form-control" id="return_date" name="return_date" required
                                min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>
                        <div class="mb-3">
                            <label for="vehicle_type" class="form-label">Vehicle Type</label>
                            <select class="form-select" id="vehicle_type" name="vehicle_type">
                                <option value="">Any Type</option>
                                <option value="Sedan">Sedan</option>
                                <option value="SUV">SUV</option>
                                <option value="Luxury">Luxury</option>
                                <option value="Sports">Sports</option>
                                <option value="Electric">Electric</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Search Available Cars</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title text-center mb-5">Why Choose Us</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-car"></i>
                    </div>
                    <h3>Premium Fleet</h3>
                    <p>Choose from our wide range of meticulously maintained vehicles to suit every need and budget.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Fully Insured</h3>
                    <p>Drive with peace of mind knowing all our vehicles come with comprehensive insurance coverage.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-box">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Our customer service team is available around the clock to assist you with any queries or issues.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Vehicles Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Featured Vehicles</h2>
        <div class="row">
            <?php foreach (array_slice($availableVehicles, 0, 6) as $vehicle): ?>
            <div class="col-md-4">
                <div class="vehicle-card card">
                    <img src="<?php echo !empty($vehicle['image']) ? '/Car-Rental/assets/images/cars/' . $vehicle['image'] : '/Car-Rental/assets/images/cars/default-car.jpg'; ?>"
                        class="card-img-top vehicle-img"
                        alt="<?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $vehicle['make'] . ' ' . $vehicle['model']; ?> <span
                                class="badge bg-success float-end">Available</span></h5>
                        <div class="vehicle-features">
                            <div class="vehicle-feature">
                                <i class="fas fa-calendar"></i> <?php echo $vehicle['year']; ?>
                            </div>
                            <div class="vehicle-feature">
                                <i class="fas fa-gas-pump"></i> <?php echo $vehicle['fuel_type']; ?>
                            </div>
                            <div class="vehicle-feature">
                                <i class="fas fa-cog"></i> <?php echo $vehicle['transmission']; ?>
                            </div>
                        </div>
                        <p class="vehicle-price">$<?php echo $vehicle['daily_rate']; ?> <span>/ day</span></p>
                        <a href="vehicle-details.php?id=<?php echo $vehicle['id']; ?>"
                            class="btn btn-primary w-100">View Details</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
            <a href="vehicles.php" class="btn btn-outline-primary">View All Vehicles</a>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5">
    <div class="container">
        <h2 class="section-title text-center mb-5">How It Works</h2>
        <div class="row justify-content-center">
            <div class="col-md-3 text-center">
                <div class="mb-4">
                    <i class="fas fa-search fa-3x text-primary"></i>
                </div>
                <h4>1. Choose a Vehicle</h4>
                <p>Browse our wide selection of vehicles and choose the perfect one for your needs.</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="mb-4">
                    <i class="fas fa-calendar-alt fa-3x text-primary"></i>
                </div>
                <h4>2. Book Your Dates</h4>
                <p>Select your pickup and return dates and complete the booking form.</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="mb-4">
                    <i class="fas fa-credit-card fa-3x text-primary"></i>
                </div>
                <h4>3. Make Payment</h4>
                <p>Confirm your booking by making a secure online payment.</p>
            </div>
            <div class="col-md-3 text-center">
                <div class="mb-4">
                    <i class="fas fa-car-side fa-3x text-primary"></i>
                </div>
                <h4>4. Enjoy Your Ride</h4>
                <p>Pick up your vehicle and enjoy your journey with our reliable service.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="section-title text-center mb-5">Customer Testimonials</h2>
        <div class="row">
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"The service was exceptional from start to finish. The car was in perfect condition and the
                        pickup process was quick and hassle-free. Will definitely use again!"</p>
                    <h5>John Smith</h5>
                    <small>Business Traveler</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p>"I rented an SUV for our family vacation and was impressed with the quality and cleanliness of
                        the vehicle. The booking process was straightforward and customer service was excellent."</p>
                    <h5>Sarah Johnson</h5>
                    <small>Family Traveler</small>
                </div>
            </div>
            <div class="col-md-4">
                <div class="testimonial-card">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <p>"Great value for money! The online booking system was easy to use and the staff were very
                        helpful. The car was fuel-efficient and perfect for city driving."</p>
                    <h5>Michael Brown</h5>
                    <small>Tourist</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to Hit the Road?</h2>
        <p class="mb-4">Join thousands of satisfied customers who trust us for their car rental needs.</p>
        <a href="register.php" class="btn btn-primary btn-lg me-3">Register Now</a>
        <a href="vehicles.php" class="btn btn-outline-light btn-lg">Browse Vehicles</a>
    </div>
</section>

<?php
// Include footer
include_once __DIR__ . '/../includes/footer.php';
?>