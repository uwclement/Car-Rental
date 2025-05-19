<?php
/**
 * Payment Class for payment processing
 */
class Payment {
    private $db;
    
    /**
     * Constructor
     * @param Database $database
     */
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create payment record
     * @param array $paymentData
     * @return int|bool
     */
    public function createPayment($paymentData) {
        $bookingId = (int) $paymentData['booking_id'];
        $amount = (float) $paymentData['amount'];
        $paymentMethod = $this->db->escapeString($paymentData['payment_method']);
        $transactionId = $this->db->escapeString($paymentData['transaction_id']);
        $paymentStatus = isset($paymentData['payment_status']) ? $this->db->escapeString($paymentData['payment_status']) : 'Completed';
        $mobileNumber = isset($paymentData['mobile_number']) ? $this->db->escapeString($paymentData['mobile_number']) : '';
        
        // Add notes for MTN Mobile Money transactions
        $notes = '';
        if ($paymentMethod === 'MTN Mobile Money' && !empty($mobileNumber)) {
            $notes = "MTN Mobile Money transaction. Number: +250" . $mobileNumber;
        }
        
        $sql = "INSERT INTO payments (booking_id, amount, payment_method, payment_status, transaction_id, notes) 
                VALUES ({$bookingId}, {$amount}, '{$paymentMethod}', '{$paymentStatus}', '{$transactionId}', '{$notes}')";
        
        if ($this->db->query($sql)) {
            // Update booking status to 'Confirmed'
            $updateSql = "UPDATE bookings SET booking_status = 'Confirmed' WHERE id = {$bookingId}";
            $this->db->query($updateSql);
            
            return $this->db->getLastId();
        }
        
        return false;
    }
    
    /**
     * Get payment by ID
     * @param int $paymentId
     * @return array|null
     */
    public function getPaymentById($paymentId) {
        $paymentId = (int) $paymentId;
        
        $sql = "SELECT p.*, b.user_id, b.vehicle_id, b.pickup_date, b.return_date, 
                u.name as customer_name, u.email as customer_email,
                v.make, v.model, v.registration_number
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                JOIN users u ON b.user_id = u.id
                JOIN vehicles v ON b.vehicle_id = v.id
                WHERE p.id = {$paymentId}";
        
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get payment by booking ID
     * @param int $bookingId
     * @return array|null
     */
    public function getPaymentByBookingId($bookingId) {
        $bookingId = (int) $bookingId;
        
        $sql = "SELECT * FROM payments WHERE booking_id = {$bookingId}";
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Get all payments
     * @param array $filters
     * @return array
     */
    public function getAllPayments($filters = []) {
        $sql = "SELECT p.*, b.user_id, b.vehicle_id, b.pickup_date, b.return_date, 
                u.name as customer_name, u.email as customer_email,
                v.make, v.model, v.registration_number
                FROM payments p
                JOIN bookings b ON p.booking_id = b.id
                JOIN users u ON b.user_id = u.id
                JOIN vehicles v ON b.vehicle_id = v.id
                WHERE 1=1";
        
        // Apply filters if provided
        if (!empty($filters['payment_status'])) {
            $status = $this->db->escapeString($filters['payment_status']);
            $sql .= " AND p.payment_status = '{$status}'";
        }
        
        if (!empty($filters['payment_method'])) {
            $method = $this->db->escapeString($filters['payment_method']);
            $sql .= " AND p.payment_method = '{$method}'";
        }
        
        if (!empty($filters['date_from'])) {
            $dateFrom = $this->db->escapeString($filters['date_from']);
            $sql .= " AND DATE(p.payment_date) >= '{$dateFrom}'";
        }
        
        if (!empty($filters['date_to'])) {
            $dateTo = $this->db->escapeString($filters['date_to']);
            $sql .= " AND DATE(p.payment_date) <= '{$dateTo}'";
        }
        
        if (!empty($filters['user_id'])) {
            $userId = (int) $filters['user_id'];
            $sql .= " AND b.user_id = {$userId}";
        }
        
        $sql .= " ORDER BY p.payment_date DESC";
        
        $result = $this->db->query($sql);
        $payments = [];
        
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
        }
        
        return $payments;
    }
    
    /**
     * Update payment status
     * @param int $paymentId
     * @param string $status
     * @return bool
     */
    public function updatePaymentStatus($paymentId, $status) {
        $paymentId = (int) $paymentId;
        $status = $this->db->escapeString($status);
        
        $sql = "UPDATE payments SET payment_status = '{$status}' WHERE id = {$paymentId}";
        
        return $this->db->query($sql);
    }
    
    /**
     * Process refund
     * @param int $paymentId
     * @param float $amount
     * @param string $reason
     * @return bool
     */
    public function processRefund($paymentId, $amount, $reason) {
        $paymentId = (int) $paymentId;
        $amount = (float) $amount;
        $reason = $this->db->escapeString($reason);
        
        // Get payment details
        $payment = $this->getPaymentById($paymentId);
        
        if (!$payment || $payment['payment_status'] !== 'Completed') {
            return false;
        }
        
        // Update payment status to 'Refunded'
        $sql = "UPDATE payments SET 
                payment_status = 'Refunded', 
                notes = CONCAT(IFNULL(notes, ''), ' Refund reason: {$reason}')
                WHERE id = {$paymentId}";
        
        return $this->db->query($sql);
    }
    
    /**
     * Get payment statistics
     * @return array
     */
    public function getPaymentStats() {
        $stats = [
            'total_payments' => 0,
            'total_amount' => 0,
            'credit_card_payments' => 0,
            'debit_card_payments' => 0,
            'mtn_mobile_money_payments' => 0,
            'paypal_payments' => 0,
            'cash_payments' => 0
        ];
        
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(amount) as total_amount,
                SUM(CASE WHEN payment_method = 'Credit Card' THEN 1 ELSE 0 END) as credit_card,
                SUM(CASE WHEN payment_method = 'Debit Card' THEN 1 ELSE 0 END) as debit_card,
                SUM(CASE WHEN payment_method = 'MTN Mobile Money' THEN 1 ELSE 0 END) as mtn_mobile_money,
                SUM(CASE WHEN payment_method = 'PayPal' THEN 1 ELSE 0 END) as paypal,
                SUM(CASE WHEN payment_method = 'Cash' THEN 1 ELSE 0 END) as cash
                FROM payments 
                WHERE payment_status = 'Completed'";
        
        $result = $this->db->query($sql);
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stats['total_payments'] = $row['total'];
            $stats['total_amount'] = $row['total_amount'];
            $stats['credit_card_payments'] = $row['credit_card'];
            $stats['debit_card_payments'] = $row['debit_card'];
            $stats['mtn_mobile_money_payments'] = $row['mtn_mobile_money'];
            $stats['paypal_payments'] = $row['paypal'];
            $stats['cash_payments'] = $row['cash'];
        }
        
        return $stats;
    }
}