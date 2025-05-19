-- Create database
CREATE DATABASE IF NOT EXISTS car_rental;
USE car_rental;

-- Users table
CREATE TABLE IF NOT EXISTS users (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
phone VARCHAR(20),
address TEXT,
driving_license VARCHAR(50),
role ENUM('admin', 'customer') DEFAULT 'customer',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

--change on user table
ALTER TABLE users 
ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER driving_license;

-- Vehicles table
CREATE TABLE IF NOT EXISTS vehicles (
id INT AUTO_INCREMENT PRIMARY KEY,
make VARCHAR(50) NOT NULL,
model VARCHAR(50) NOT NULL,
year INT NOT NULL,
registration_number VARCHAR(20) NOT NULL UNIQUE,
color VARCHAR(30),
seating_capacity INT,
fuel_type ENUM('Petrol', 'Diesel', 'Electric', 'Hybrid'),
transmission ENUM('Automatic', 'Manual'),
daily_rate DECIMAL(10, 2) NOT NULL,
status ENUM('Available', 'Booked', 'Maintenance') DEFAULT 'Available',
image VARCHAR(255),
description TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bookings table
CREATE TABLE IF NOT EXISTS bookings (
id INT AUTO_INCREMENT PRIMARY KEY,
user_id INT NOT NULL,
vehicle_id INT NOT NULL,
pickup_date DATE NOT NULL,
return_date DATE NOT NULL,
pickup_location VARCHAR(100),
return_location VARCHAR(100),
booking_status ENUM('Pending', 'Confirmed', 'Cancelled', 'Completed') DEFAULT 'Pending',
total_amount DECIMAL(10, 2) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
id INT AUTO_INCREMENT PRIMARY KEY,
booking_id INT NOT NULL,
amount DECIMAL(10, 2) NOT NULL,
payment_method ENUM('Credit Card', 'Debit Card', 'PayPal', 'Cash'),
payment_status ENUM('Pending', 'Completed', 'Failed', 'Refunded') DEFAULT 'Pending',
transaction_id VARCHAR(100),
payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

-- changes on table -- Updated Payments table with MTN Mobile Money option
 ALTER TABLE payments 
MODIFY COLUMN payment_method ENUM('Credit Card', 'Debit Card', 'PayPal', 'Cash', 'MTN Mobile Money') DEFAULT 'Credit Card';

ALTER TABLE payments 
ADD COLUMN notes TEXT AFTER payment_date;

-- Maintenance records table
CREATE TABLE IF NOT EXISTS maintenance (
id INT AUTO_INCREMENT PRIMARY KEY,
vehicle_id INT NOT NULL,
maintenance_type VARCHAR(100) NOT NULL,
description TEXT,
cost DECIMAL(10, 2),
start_date DATE,
end_date DATE,
status ENUM('Scheduled', 'In Progress', 'Completed') DEFAULT 'Scheduled',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

-- Insert sample admin user
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@carental.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample vehicles
INSERT INTO vehicles (make, model, year, registration_number, color, seating_capacity, fuel_type, transmission,
daily_rate, image, description) VALUES
('Toyota', 'Camry', 2023, 'ABC123', 'White', 5, 'Petrol', 'Automatic', 75.00, 'toyota-camry.jpg', 'Comfortable sedan
with excellent fuel efficiency and modern features.'),
('Honda', 'CR-V', 2023, 'DEF456', 'Silver', 5, 'Hybrid', 'Automatic', 85.00, 'honda-crv.jpg', 'Spacious SUV with
advanced safety features and ample cargo space.'),
('Tesla', 'Model 3', 2023, 'GHI789', 'Red', 5, 'Electric', 'Automatic', 120.00, 'tesla-model3.jpg', 'High-performance
electric car with cutting-edge technology and autopilot capabilities.'),
('Ford', 'Mustang', 2022, 'JKL012', 'Blue', 4, 'Petrol', 'Manual', 110.00, 'ford-mustang.jpg', 'Iconic sports car with
powerful engine and classic American styling.'),
('BMW', 'X5', 2022, 'MNO345', 'Black', 7, 'Diesel', 'Automatic', 150.00, 'bmw-x5.jpg', 'Luxury SUV with premium
features, spacious interior, and excellent performance.');