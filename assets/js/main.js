/**
 * Main JavaScript file for Car Rental Management System
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltips.length > 0) {
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }

    // Date range picker for booking form
    const pickupDateInput = document.getElementById('pickup_date');
    const returnDateInput = document.getElementById('return_date');
    
    if (pickupDateInput && returnDateInput) {
        pickupDateInput.addEventListener('change', function() {
            // Set minimum return date to be the pickup date
            const pickupDate = new Date(this.value);
            const nextDay = new Date(pickupDate);
            nextDay.setDate(pickupDate.getDate() + 1);
            
            const year = nextDay.getFullYear();
            const month = String(nextDay.getMonth() + 1).padStart(2, '0');
            const day = String(nextDay.getDate()).padStart(2, '0');
            
            returnDateInput.min = `${year}-${month}-${day}`;
            
            // If return date is before pickup date, reset it
            if (new Date(returnDateInput.value) <= new Date(this.value)) {
                returnDateInput.value = `${year}-${month}-${day}`;
            }
            
            calculateTotalAmount();
        });
        
        returnDateInput.addEventListener('change', function() {
            calculateTotalAmount();
        });
    }
    
    // Calculate total amount for booking
    function calculateTotalAmount() {
        const dailyRateElement = document.getElementById('daily_rate');
        const totalAmountElement = document.getElementById('total_amount');
        const totalDaysElement = document.getElementById('total_days');
        
        if (pickupDateInput && returnDateInput && dailyRateElement && totalAmountElement && totalDaysElement) {
            const pickupDate = new Date(pickupDateInput.value);
            const returnDate = new Date(returnDateInput.value);
            const dailyRate = parseFloat(dailyRateElement.dataset.rate);
            
            // Calculate difference in days
            const diffTime = Math.abs(returnDate - pickupDate);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays > 0 && !isNaN(dailyRate)) {
                const totalAmount = diffDays * dailyRate;
                totalDaysElement.textContent = diffDays;
                totalAmountElement.textContent = totalAmount.toFixed(2);
                
                // If there's a hidden input for the form
                const totalAmountInput = document.getElementById('total_amount_input');
                if (totalAmountInput) {
                    totalAmountInput.value = totalAmount.toFixed(2);
                }
            }
        }
    }
    
    // Initialize calculation on page load
    if (pickupDateInput && returnDateInput && document.getElementById('daily_rate')) {
        calculateTotalAmount();
    }
    
    // Vehicle image preview on upload
    const vehicleImageInput = document.getElementById('vehicle_image');
    const imagePreview = document.getElementById('image_preview');
    
    if (vehicleImageInput && imagePreview) {
        vehicleImageInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
    
    // Card payment form validation
    const paymentForm = document.getElementById('payment-form');
    if (paymentForm) {
        paymentForm.addEventListener('submit', function(event) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
            
            if (paymentMethod === 'Credit Card' || paymentMethod === 'Debit Card') {
                const cardNumber = document.getElementById('card_number').value;
                const cardName = document.getElementById('card_name').value;
                const cardExpiry = document.getElementById('card_expiry').value;
                const cardCvv = document.getElementById('card_cvv').value;
                
                if (!cardNumber || !cardName || !cardExpiry || !cardCvv) {
                    event.preventDefault();
                    alert('Please fill all card details.');
                } else if (cardNumber.replace(/\s/g, '').length < 16) {
                    event.preventDefault();
                    alert('Invalid card number. Please enter a valid 16-digit card number.');
                } else if (cardCvv.length < 3) {
                    event.preventDefault();
                    alert('Invalid CVV. Please enter a valid CVV code.');
                }
            }
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
    }
    
    // Admin dashboard responsive sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });
    }
});