<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize user input
 * @param string $data
 * @return string
 */
function sanitize($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

/**
 * Redirect to a URL
 * @param string $url
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 * @return bool
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Get current user ID
 * @return int|null
 */
function getCurrentUserId() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

/**
 * Display error message
 * @param string $message
 */
function showError($message) {
    return "<div class='alert alert-danger'>{$message}</div>";
}

/**
 * Display success message
 * @param string $message
 */
function showSuccess($message) {
    return "<div class='alert alert-success'>{$message}</div>";
}

/**
 * Format date to readable format
 * @param string $date
 * @return string
 */
function formatDate($date) {
    return date("F j, Y", strtotime($date));
}

/**
 * Calculate number of days between two dates
 * @param string $startDate
 * @param string $endDate
 * @return int
 */
function calculateDays($startDate, $endDate) {
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $interval = $start->diff($end);
    return $interval->days;
}

/**
 * Calculate total booking amount
 * @param float $dailyRate
 * @param int $days
 * @return float
 */
function calculateTotalAmount($dailyRate, $days) {
    return $dailyRate * $days;
}

/**
 * Get all vehicles with optional filters
 * @param array $filters
 * @return array
 */
function getVehicles($filters = []) {
    global $conn;
    
    $sql = "SELECT * FROM vehicles WHERE 1=1";
    
    // Apply filters if provided
    if (!empty($filters['status'])) {
        $status = sanitize($filters['status']);
        $sql .= " AND status = '{$status}'";
    }
    
    if (!empty($filters['make'])) {
        $make = sanitize($filters['make']);
        $sql .= " AND make = '{$make}'";
    }
    
    $result = $conn->query($sql);
    $vehicles = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $vehicles[] = $row;
        }
    }
    
    return $vehicles;
}

/**
 * Get vehicle by ID
 * @param int $id
 * @return array|null
 */
function getVehicleById($id) {
    global $conn;
    
    $id = (int) $id;
    $sql = "SELECT * FROM vehicles WHERE id = {$id}";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Check if a vehicle is available for booking in a specific date range
 * @param int $vehicleId
 * @param string $pickupDate
 * @param string $returnDate
 * @return bool
 */
function isVehicleAvailable($vehicleId, $pickupDate, $returnDate) {
    global $conn;
    
    $vehicleId = (int) $vehicleId;
    $pickupDate = sanitize($pickupDate);
    $returnDate = sanitize($returnDate);
    
    $sql = "SELECT * FROM bookings 
            WHERE vehicle_id = {$vehicleId} 
            AND booking_status IN ('Pending', 'Confirmed') 
            AND ((pickup_date BETWEEN '{$pickupDate}' AND '{$returnDate}') 
            OR (return_date BETWEEN '{$pickupDate}' AND '{$returnDate}')
            OR (pickup_date <= '{$pickupDate}' AND return_date >= '{$returnDate}'))";
    
    $result = $conn->query($sql);
    
    return $result->num_rows === 0;
}

/**
 * Get user bookings
 * @param int $userId
 * @return array
 */
function getUserBookings($userId) {
    global $conn;
    
    $userId = (int) $userId;
    $sql = "SELECT b.*, v.make, v.model, v.image, v.daily_rate, v.registration_number 
            FROM bookings b 
            JOIN vehicles v ON b.vehicle_id = v.id 
            WHERE b.user_id = {$userId} 
            ORDER BY b.created_at DESC";
    
    $result = $conn->query($sql);
    $bookings = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    }
    
    return $bookings;
}

/**
 * Get all bookings (Admin function)
 * @param array $filters
 * @return array
 */
function getAllBookings($filters = []) {
    global $conn;
    
    $sql = "SELECT b.*, u.name as customer_name, u.email as customer_email, 
            v.make, v.model, v.registration_number 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN vehicles v ON b.vehicle_id = v.id 
            WHERE 1=1";
    
    // Apply filters if provided
    if (!empty($filters['status'])) {
        $status = sanitize($filters['status']);
        $sql .= " AND b.booking_status = '{$status}'";
    }
    
    if (!empty($filters['user_id'])) {
        $userId = (int) $filters['user_id'];
        $sql .= " AND b.user_id = {$userId}";
    }
    
    if (!empty($filters['vehicle_id'])) {
        $vehicleId = (int) $filters['vehicle_id'];
        $sql .= " AND b.vehicle_id = {$vehicleId}";
    }
    
    $sql .= " ORDER BY b.created_at DESC";
    
    $result = $conn->query($sql);
    $bookings = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    }
    
    return $bookings;
}

/**
 * Get booking by ID
 * @param int $id
 * @return array|null
 */
function getBookingById($id) {
    global $conn;
    
    $id = (int) $id;
    $sql = "SELECT b.*, u.name as customer_name, u.email as customer_email, 
            v.make, v.model, v.image, v.daily_rate, v.registration_number 
            FROM bookings b 
            JOIN users u ON b.user_id = u.id 
            JOIN vehicles v ON b.vehicle_id = v.id 
            WHERE b.id = {$id}";
    
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Create a new booking
 * @param array $data
 * @return int|bool
 */
function createBooking($data) {
    global $conn;
    
    $userId = (int) $data['user_id'];
    $vehicleId = (int) $data['vehicle_id'];
    $pickupDate = sanitize($data['pickup_date']);
    $returnDate = sanitize($data['return_date']);
    $pickupLocation = sanitize($data['pickup_location']);
    $returnLocation = sanitize($data['return_location']);
    $totalAmount = (float) $data['total_amount'];
    
    $sql = "INSERT INTO bookings (user_id, vehicle_id, pickup_date, return_date, 
            pickup_location, return_location, total_amount) 
            VALUES ({$userId}, {$vehicleId}, '{$pickupDate}', '{$returnDate}', 
            '{$pickupLocation}', '{$returnLocation}', {$totalAmount})";
    
    if ($conn->query($sql)) {
        $bookingId = $conn->insert_id;
        
        // Update vehicle status to 'Booked'
        $updateSql = "UPDATE vehicles SET status = 'Booked' WHERE id = {$vehicleId}";
        $conn->query($updateSql);
        
        return $bookingId;
    }
    
    return false;
}

/**
 * Update booking status
 * @param int $bookingId
 * @param string $status
 * @return bool
 */
function updateBookingStatus($bookingId, $status) {
    global $conn;
    
    $bookingId = (int) $bookingId;
    $status = sanitize($status);
    
    $sql = "UPDATE bookings SET booking_status = '{$status}' WHERE id = {$bookingId}";
    
    if ($conn->query($sql)) {
        // If status is 'Completed' or 'Cancelled', update vehicle status to 'Available'
        if ($status === 'Completed' || $status === 'Cancelled') {
            $booking = getBookingById($bookingId);
            $vehicleId = $booking['vehicle_id'];
            
            $updateSql = "UPDATE vehicles SET status = 'Available' WHERE id = {$vehicleId}";
            $conn->query($updateSql);
        }
        
        return true;
    }
    
    return false;
}

/**
 * Create payment record
 * @param array $data
 * @return int|bool
 */
/**
 * Create payment record (No notes column version)
 * @param array $data
 * @return int|bool
 */
function createPayment($data) {
    global $conn;
    
    $bookingId = (int) $data['booking_id'];
    $amount = (float) $data['amount'];
    $paymentMethod = sanitize($data['payment_method']);
    $transactionId = sanitize($data['transaction_id']);
    
    // If it's an MTN Mobile Money payment, append the mobile number to the transaction ID
    if ($paymentMethod === 'MTN Mobile Money' && isset($data['mobile_number'])) {
        $mobileNumber = sanitize($data['mobile_number']);
        $transactionId .= "-MTN" . $mobileNumber;
    }
    
    $sql = "INSERT INTO payments (booking_id, amount, payment_method, payment_status, transaction_id) 
            VALUES ({$bookingId}, {$amount}, '{$paymentMethod}', 'Completed', '{$transactionId}')";
    
    if ($conn->query($sql)) {
        // Update booking status to 'Confirmed'
        updateBookingStatus($bookingId, 'Confirmed');
        
        return $conn->insert_id;
    }
    
    return false;
}
/**
 * Get user by email
 * @param string $email
 * @return array|null
 */
function getUserByEmail($email) {
    global $conn;
    
    $email = sanitize($email);
    $sql = "SELECT * FROM users WHERE email = '{$email}'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get user by ID
 * @param int $id
 * @return array|null
 */
function getUserById($id) {
    global $conn;
    
    $id = (int) $id;
    $sql = "SELECT * FROM users WHERE id = {$id}";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Register new user
 * @param array $data
 * @return int|bool
 */
function registerUser($data) {
    global $conn;
    
    $name = sanitize($data['name']);
    $email = sanitize($data['email']);
    $password = password_hash($data['password'], PASSWORD_DEFAULT);
    $phone = isset($data['phone']) ? sanitize($data['phone']) : '';
    $address = isset($data['address']) ? sanitize($data['address']) : '';
    $drivingLicense = isset($data['driving_license']) ? sanitize($data['driving_license']) : '';
    
    $sql = "INSERT INTO users (name, email, password, phone, address, driving_license) 
            VALUES ('{$name}', '{$email}', '{$password}', '{$phone}', '{$address}', '{$drivingLicense}')";
    
    if ($conn->query($sql)) {
        return $conn->insert_id;
    }
    
    return false;
}

/**
 * Update user profile
 * @param int $userId
 * @param array $userData
 * @return bool
 */
function updateUserProfile($userId, $userData) {
    global $conn;
    
    $userId = (int) $userId;
    $name = $conn->real_escape_string($userData['name']);
    $phone = $conn->real_escape_string($userData['phone']);
    $address = $conn->real_escape_string($userData['address']);
    $drivingLicense = $conn->real_escape_string($userData['driving_license']);
    
    // Add profile image field if provided
    $profileImageSql = '';
    if (isset($userData['profile_image'])) {
        $profileImage = $conn->real_escape_string($userData['profile_image']);
        $profileImageSql = ", profile_image = '{$profileImage}'";
    }
    
    $sql = "UPDATE users SET name = '{$name}', phone = '{$phone}', 
            address = '{$address}', driving_license = '{$drivingLicense}'{$profileImageSql} 
            WHERE id = {$userId}";
    
    return $conn->query($sql);
}

/**
 * Get all users (Admin function)
 * @return array
 */
function getAllUsers() {
    global $conn;
    
    $sql = "SELECT * FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);
    $users = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }
    
    return $users;
}

/**
 * Get statistics for dashboard (Admin function)
 * @return array
 */
function getDashboardStats() {
    global $conn;
    
    $stats = [
        'total_vehicles' => 0,
        'available_vehicles' => 0,
        'total_bookings' => 0,
        'active_bookings' => 0,
        'total_customers' => 0,
        'total_revenue' => 0
    ];
    
    // Get vehicle stats
    $vehicleSql = "SELECT COUNT(*) as total, 
                   SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available 
                   FROM vehicles";
    $vehicleResult = $conn->query($vehicleSql);
    if ($vehicleResult->num_rows > 0) {
        $vehicleRow = $vehicleResult->fetch_assoc();
        $stats['total_vehicles'] = $vehicleRow['total'];
        $stats['available_vehicles'] = $vehicleRow['available'];
    }
    
    // Get booking stats
    $bookingSql = "SELECT COUNT(*) as total, 
                  SUM(CASE WHEN booking_status IN ('Pending', 'Confirmed') THEN 1 ELSE 0 END) as active,
                  SUM(CASE WHEN booking_status = 'Completed' THEN total_amount ELSE 0 END) as revenue 
                  FROM bookings";
    $bookingResult = $conn->query($bookingSql);
    if ($bookingResult->num_rows > 0) {
        $bookingRow = $bookingResult->fetch_assoc();
        $stats['total_bookings'] = $bookingRow['total'];
        $stats['active_bookings'] = $bookingRow['active'];
        $stats['total_revenue'] = $bookingRow['revenue'];
    }
    
    // Get customer stats
    $customerSql = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
    $customerResult = $conn->query($customerSql);
    if ($customerResult->num_rows > 0) {
        $customerRow = $customerResult->fetch_assoc();
        $stats['total_customers'] = $customerRow['total'];
    }
    
    return $stats;
}

/**
 * Add new vehicle
 * @param array $vehicleData
 * @return int|bool
 */
function addVehicle($vehicleData) {
    global $conn;
    
    $make = sanitize($vehicleData['make']);
    $model = sanitize($vehicleData['model']);
    $year = (int) $vehicleData['year'];
    $registrationNumber = sanitize($vehicleData['registration_number']);
    $color = sanitize($vehicleData['color']);
    $seatingCapacity = (int) $vehicleData['seating_capacity'];
    $fuelType = sanitize($vehicleData['fuel_type']);
    $transmission = sanitize($vehicleData['transmission']);
    $dailyRate = (float) $vehicleData['daily_rate'];
    $status = sanitize($vehicleData['status']);
    $image = sanitize($vehicleData['image']);
    $description = sanitize($vehicleData['description']);
    
    // Check if registration number already exists
    $checkSql = "SELECT * FROM vehicles WHERE registration_number = '{$registrationNumber}'";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult->num_rows > 0) {
        return false; // Registration number already exists
    }
    
    $sql = "INSERT INTO vehicles (make, model, year, registration_number, color, seating_capacity, 
            fuel_type, transmission, daily_rate, status, image, description) 
            VALUES ('{$make}', '{$model}', {$year}, '{$registrationNumber}', '{$color}', {$seatingCapacity}, 
            '{$fuelType}', '{$transmission}', {$dailyRate}, '{$status}', '{$image}', '{$description}')";
    
    if ($conn->query($sql)) {
        return $conn->insert_id;
    }
    
    return false;
}

/**
 * Update vehicle
 * @param int $id
 * @param array $vehicleData
 * @return bool
 */
function updateVehicle($id, $vehicleData) {
    global $conn;
    
    $id = (int) $id;
    $make = sanitize($vehicleData['make']);
    $model = sanitize($vehicleData['model']);
    $year = (int) $vehicleData['year'];
    $registrationNumber = sanitize($vehicleData['registration_number']);
    $color = sanitize($vehicleData['color']);
    $seatingCapacity = (int) $vehicleData['seating_capacity'];
    $fuelType = sanitize($vehicleData['fuel_type']);
    $transmission = sanitize($vehicleData['transmission']);
    $dailyRate = (float) $vehicleData['daily_rate'];
    $status = sanitize($vehicleData['status']);
    $description = sanitize($vehicleData['description']);
    
    // Check if registration number already exists for other vehicles
    $checkSql = "SELECT * FROM vehicles WHERE registration_number = '{$registrationNumber}' AND id != {$id}";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult->num_rows > 0) {
        return false; // Registration number already exists for another vehicle
    }
    
    $sql = "UPDATE vehicles SET 
            make = '{$make}', 
            model = '{$model}', 
            year = {$year}, 
            registration_number = '{$registrationNumber}', 
            color = '{$color}', 
            seating_capacity = {$seatingCapacity}, 
            fuel_type = '{$fuelType}', 
            transmission = '{$transmission}', 
            daily_rate = {$dailyRate}, 
            status = '{$status}', 
            description = '{$description}'";
    
    // Update image only if provided
    if (isset($vehicleData['image']) && !empty($vehicleData['image'])) {
        $image = sanitize($vehicleData['image']);
        $sql .= ", image = '{$image}'";
    }
    
    $sql .= " WHERE id = {$id}";
    
    return $conn->query($sql);
}

/**
 * Delete vehicle
 * @param int $id
 * @return bool
 */
function deleteVehicle($id) {
    global $conn;
    
    $id = (int) $id;
    
    // Check if vehicle has any active bookings
    $checkSql = "SELECT * FROM bookings WHERE vehicle_id = {$id} AND booking_status IN ('Pending', 'Confirmed')";
    $checkResult = $conn->query($checkSql);
    
    if ($checkResult->num_rows > 0) {
        return false; // Cannot delete vehicle with active bookings
    }
    
    $sql = "DELETE FROM vehicles WHERE id = {$id}";
    
    return $conn->query($sql);
}

/**
 * Update vehicle status
 * @param int $id
 * @param string $status
 * @return bool
 */
function updateVehicleStatus($id, $status) {
    global $conn;
    
    $id = (int) $id;
    $status = sanitize($status);
    
    $sql = "UPDATE vehicles SET status = '{$status}' WHERE id = {$id}";
    
    return $conn->query($sql);
}