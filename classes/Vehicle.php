<?php
/**
 * Vehicle Class for vehicle management
 */
class Vehicle {
    private $db;
    
    /**
     * Constructor
     * @param Database $database
     */
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Get all vehicles with optional filters
     * @param array $filters
     * @return array
     */
    public function getVehicles($filters = []) {
        $sql = "SELECT * FROM vehicles WHERE 1=1";
        
        // Apply filters if provided
        if (!empty($filters['status'])) {
            $status = $this->db->escapeString($filters['status']);
            $sql .= " AND status = '{$status}'";
        }
        
        if (!empty($filters['make'])) {
            $make = $this->db->escapeString($filters['make']);
            $sql .= " AND make = '{$make}'";
        }
        
        if (!empty($filters['model'])) {
            $model = $this->db->escapeString($filters['model']);
            $sql .= " AND model = '{$model}'";
        }
        
        if (!empty($filters['year'])) {
            $year = (int) $filters['year'];
            $sql .= " AND year = {$year}";
        }
        
        if (!empty($filters['fuel_type'])) {
            $fuelType = $this->db->escapeString($filters['fuel_type']);
            $sql .= " AND fuel_type = '{$fuelType}'";
        }
        
        if (!empty($filters['transmission'])) {
            $transmission = $this->db->escapeString($filters['transmission']);
            $sql .= " AND transmission = '{$transmission}'";
        }
        
        if (isset($filters['max_price']) && $filters['max_price'] > 0) {
            $maxPrice = (float) $filters['max_price'];
            $sql .= " AND daily_rate <= {$maxPrice}";
        }
        
        $sql .= " ORDER BY id DESC";
        
        $result = $this->db->query($sql);
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
    public function getVehicleById($id) {
        $id = (int) $id;
        
        $sql = "SELECT * FROM vehicles WHERE id = {$id}";
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Add new vehicle
     * @param array $vehicleData
     * @return int|bool
     */
    public function addVehicle($vehicleData) {
        $make = $this->db->escapeString($vehicleData['make']);
        $model = $this->db->escapeString($vehicleData['model']);
        $year = (int) $vehicleData['year'];
        $registrationNumber = $this->db->escapeString($vehicleData['registration_number']);
        $color = $this->db->escapeString($vehicleData['color']);
        $seatingCapacity = (int) $vehicleData['seating_capacity'];
        $fuelType = $this->db->escapeString($vehicleData['fuel_type']);
        $transmission = $this->db->escapeString($vehicleData['transmission']);
        $dailyRate = (float) $vehicleData['daily_rate'];
        $status = $this->db->escapeString($vehicleData['status']);
        $image = isset($vehicleData['image']) ? $this->db->escapeString($vehicleData['image']) : '';
        $description = $this->db->escapeString($vehicleData['description']);
        
        // Check if registration number already exists
        $checkSql = "SELECT * FROM vehicles WHERE registration_number = '{$registrationNumber}'";
        $checkResult = $this->db->query($checkSql);
        
        if ($checkResult->num_rows > 0) {
            return false; // Registration number already exists
        }
        
        $sql = "INSERT INTO vehicles (make, model, year, registration_number, color, seating_capacity, 
                fuel_type, transmission, daily_rate, status, image, description) 
                VALUES ('{$make}', '{$model}', {$year}, '{$registrationNumber}', '{$color}', {$seatingCapacity}, 
                '{$fuelType}', '{$transmission}', {$dailyRate}, '{$status}', '{$image}', '{$description}')";
        
        if ($this->db->query($sql)) {
            return $this->db->getLastId();
        }
        
        return false;
    }
    
    /**
     * Update vehicle
     * @param int $id
     * @param array $vehicleData
     * @return bool
     */
    public function updateVehicle($id, $vehicleData) {
        $id = (int) $id;
        $make = $this->db->escapeString($vehicleData['make']);
        $model = $this->db->escapeString($vehicleData['model']);
        $year = (int) $vehicleData['year'];
        $registrationNumber = $this->db->escapeString($vehicleData['registration_number']);
        $color = $this->db->escapeString($vehicleData['color']);
        $seatingCapacity = (int) $vehicleData['seating_capacity'];
        $fuelType = $this->db->escapeString($vehicleData['fuel_type']);
        $transmission = $this->db->escapeString($vehicleData['transmission']);
        $dailyRate = (float) $vehicleData['daily_rate'];
        $status = $this->db->escapeString($vehicleData['status']);
        $description = $this->db->escapeString($vehicleData['description']);
        
        // Check if registration number already exists for other vehicles
        $checkSql = "SELECT * FROM vehicles WHERE registration_number = '{$registrationNumber}' AND id != {$id}";
        $checkResult = $this->db->query($checkSql);
        
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
            $image = $this->db->escapeString($vehicleData['image']);
            $sql .= ", image = '{$image}'";
        }
        
        $sql .= " WHERE id = {$id}";
        
        return $this->db->query($sql);
    }
    
    /**
     * Delete vehicle
     * @param int $id
     * @return bool
     */
    public function deleteVehicle($id) {
        $id = (int) $id;
        
        // Check if vehicle has any active bookings
        $checkSql = "SELECT * FROM bookings WHERE vehicle_id = {$id} AND booking_status IN ('Pending', 'Confirmed')";
        $checkResult = $this->db->query($checkSql);
        
        if ($checkResult->num_rows > 0) {
            return false; // Cannot delete vehicle with active bookings
        }
        
        $sql = "DELETE FROM vehicles WHERE id = {$id}";
        
        return $this->db->query($sql);
    }
    
    /**
     * Update vehicle status
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateVehicleStatus($id, $status) {
        $id = (int) $id;
        $status = $this->db->escapeString($status);
        
        $sql = "UPDATE vehicles SET status = '{$status}' WHERE id = {$id}";
        
        return $this->db->query($sql);
    }
    
    /**
     * Check if vehicle is available for booking
     * @param int $vehicleId
     * @param string $pickupDate
     * @param string $returnDate
     * @return bool
     */
    public function isVehicleAvailable($vehicleId, $pickupDate, $returnDate) {
        $vehicleId = (int) $vehicleId;
        $pickupDate = $this->db->escapeString($pickupDate);
        $returnDate = $this->db->escapeString($returnDate);
        
        // Check vehicle status
        $statusSql = "SELECT status FROM vehicles WHERE id = {$vehicleId}";
        $statusResult = $this->db->query($statusSql);
        
        if ($statusResult->num_rows === 0 || $statusResult->fetch_assoc()['status'] !== 'Available') {
            return false;
        }
        
        // Check existing bookings
        $sql = "SELECT * FROM bookings 
                WHERE vehicle_id = {$vehicleId} 
                AND booking_status IN ('Pending', 'Confirmed') 
                AND ((pickup_date BETWEEN '{$pickupDate}' AND '{$returnDate}') 
                OR (return_date BETWEEN '{$pickupDate}' AND '{$returnDate}')
                OR (pickup_date <= '{$pickupDate}' AND return_date >= '{$returnDate}'))";
        
        $result = $this->db->query($sql);
        
        return $result->num_rows === 0;
    }
}