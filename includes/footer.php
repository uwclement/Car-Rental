<?php if (isset($isAdminPage) && $isAdminPage): ?>
</div>
</div>
<?php endif; ?>
</div>

<footer class="bg-dark text-white py-4 mt-5" id="contact">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>Car Rental System</h5>
                <p>Your trusted partner for quality car rentals. We provide a wide range of vehicles for all your needs
                    at competitive prices.</p>
            </div>
            <div class="col-md-4">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="/Car-Rental/public/index.php" class="text-white">Home</a></li>
                    <li><a href="/Car-Rental/public/vehicles.php" class="text-white">Vehicles</a></li>
                    <li><a href="/Car-Rental/public/register.php" class="text-white">Register</a></li>
                    <li><a href="/Car-Rental/public/login.php" class="text-white">Login</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5>Contact Us</h5>
                <address>
                    <p><i class="fas fa-map-marker-alt me-2"></i> 123 Main Street, City, Country</p>
                    <p><i class="fas fa-phone me-2"></i> +1 (123) 456-7890</p>
                    <p><i class="fas fa-envelope me-2"></i> info@carental.com</p>
                </address>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p>&copy; <?php echo date('Y'); ?> Car Rental System. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Custom JS -->
<script src="/Car-Rental/assets/js/main.js"></script>
</body>

</html>