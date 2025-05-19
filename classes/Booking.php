<?php
/**
 * Booking Class for booking management
 */
class Booking {
    private $db;
    
    /**
     * Constructor
     * @param Database $database
     */
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create a new booking
     * @param array $bookingData
     * @return int|bool
     */
    public function createBooking($bookingData) {
        $userId = (int) $bookingData['user_id'];
        $vehicleId = (int) $bookingData['vehicle_id'];
        $pickupDate = $this->db->escapeString($bookingData['pickup_date']);
        $returnDate = $this->db->escapeString($bookingData['return_date']);
        $pickupLocation = $this->db->escapeString($bookingData['pickup_location']);
        $returnLocation = $this->db->escapeString($bookingData['return_location']);
        $totalAmount = (float) $bookingData['total_amount'];
        
        $sql = "INSERT INTO bookings (user_id, vehicle_id, pickup_date, return_date, 
                pickup_location, return_location, total_amount) 
                VALUES ({$userId}, {$vehicleId}, '{$pickupDate}', '{$returnDate}', 
                '{$pickupLocation}', '{$returnLocation}', {$totalAmount})";
        
        if ($this->db->query($sql)) {
            $bookingId = $this->db->getLastId();
            
            // Update vehicle status to 'Booked'
            $updateSql = "UPDATE vehicles SET status = 'Booked' WHERE id = {$vehicleId}";
            $this->db->query($updateSql);
            
            return $bookingId;
        }
        
        return false;
    }
    
    /**
     * Get booking by ID
     * @param int $id
     * @return array|null
     */
    public function getBookingById($id) {
        $id = (int) $id;
        
        $sql = "SELECT b.*, u.name as customer_name, u.email as customer_email, 
                v.make, v.model, v.image, v.daily_rate, v.registration_number 
                FROM bookings b 
                JOIN users u ON b.user_id = u.id 
                JOIN vehicles v ON b.vehicle_id = v.id 
                WHERE b.id = {$id}";
        
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get user bookings
     * @param int $userId
     * @return array
     */
    public function getUserBookings($userId) {
        $userId = (int) $userId;
        
        $sql = "SELECT b.*, v.make, v.model, v.image, v.daily_rate, v.registration_number 
                FROM bookings b 
                JOIN vehicles v ON b.vehicle_id = v.id 
                WHERE b.user_id = {$userId} 
                ORDER BY b.created_at DESC";
        
        $result = $this->db->query($sql);
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
    public function getAllBookings($filters = []) {
        $sql = "SELECT b.*, u.name as customer_name, u.email as customer_email, 
                v.make, v.model, v.registration_number 
                FROM bookings b 
                JOIN users u ON b.user_id = u.id 
                JOIN vehicles v ON b.vehicle_id = v.id 
                WHERE 1=1";
        
        // Apply filters if provided
        if (!empty($filters['status'])) {
            $status = $this->db->escapeString($filters['status']);
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
        
        if (!empty($filters['date_from'])) {
            $dateFrom = $this->db->escapeString($filters['date_from']);
            $sql .= " AND b.pickup_date >= '{$dateFrom}'";
        }
        
        if (!empty($filters['date_to'])) {
            $dateTo = $this->db->escapeString($filters['date_to']);
            $sql .= " AND b.return_date <= '{$dateTo}'";
        }
        
        $sql .= " ORDER BY b.created_at DESC";
        
        $result = $this->db->query($sql);
        $bookings = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
        }
        
        return $bookings;
    }
    
    /**
     * Update booking status
     * @param int $bookingId
     * @param string $status
     * @return bool
     */
    public function updateBookingStatus($bookingId, $status) {
        $bookingId = (int) $bookingId;
        $status = $this->db->escapeString($status);
        
        $sql = "UPDATE bookings SET booking_status = '{$status}' WHERE id = {$bookingId}";
        
        if ($this->db->query($sql)) {
            // If status is 'Completed' or 'Cancelled', update vehicle status to 'Available'
            if ($status === 'Completed' || $status === 'Cancelled') {
                $booking = $this->getBookingById($bookingId);
                if ($booking) {
                    $vehicleId = $booking['vehicle_id'];
                    
                    $updateSql = "UPDATE vehicles SET status = 'Available' WHERE id = {$vehicleId}";
                    $this->db->query($updateSql);
                }
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Delete booking
     * @param int $bookingId
     * @return bool
     */
    public function deleteBooking($bookingId) {
        $bookingId = (int) $bookingId;
        
        // Get booking info before deletion
        $booking = $this->getBookingById($bookingId);
        if (!$booking) {
            return false;
        }
        
        // Delete booking
        $sql = "DELETE FROM bookings WHERE id = {$bookingId}";
        
        if ($this->db->query($sql)) {
            // Update vehicle status to 'Available' if booking was active
            if ($booking['booking_status'] === 'Pending' || $booking['booking_status'] === 'Confirmed') {
                $vehicleId = $booking['vehicle_id'];
                
                $updateSql = "UPDATE vehicles SET status = 'Available' WHERE id = {$vehicleId}";
                $this->db->query($updateSql);
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Calculate booking statistics
     * @return array
     */
    public function getBookingStats() {
        $stats = [
            'total_bookings' => 0,
            'active_bookings' => 0,
            'completed_bookings' => 0,
            'cancelled_bookings' => 0,
            'pending_bookings' => 0,
            'total_revenue' => 0
        ];
        
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN booking_status = 'Pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN booking_status = 'Confirmed' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN booking_status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN booking_status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN booking_status = 'Completed' THEN total_amount ELSE 0 END) as revenue
                FROM bookings";
        
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stats['total_bookings'] = $row['total'];
            $stats['pending_bookings'] = $row['pending'];
            $stats['active_bookings'] = $row['active'];
            $stats['completed_bookings'] = $row['completed'];
            $stats['cancelled_bookings'] = $row['cancelled'];
            $stats['total_revenue'] = $row['revenue'];
        }
        
        return $stats;
    }
}