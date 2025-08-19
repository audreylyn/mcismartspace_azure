<?php
class RateLimiter {
    private $db;
    private $maxAttempts = 5; // Maximum login attempts
    private $lockoutTime = 900; // 15 minutes in seconds
    private $ipAddress;
    
    public function __construct($db) {
        $this->db = $db;
        $this->ipAddress = $this->getClientIP();
    }
    
    /**
     * Check if login attempts are allowed
     */
    public function isAllowed() {
        // Clean up old records
        $this->cleanupOldRecords();
        
        // Check current attempts
        $query = "SELECT COUNT(*) as attempts, MAX(attempt_time) as last_attempt 
                    FROM login_attempts 
                    WHERE ip_address = ?
                    AND success = 0
                    AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('si', $this->ipAddress, $this->lockoutTime);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        $attempts = $result['attempts'];
        $lastAttempt = $result['last_attempt'];
        
        // If max attempts reached, check if lockout period has passed
        if ($attempts >= $this->maxAttempts) {
            if ($lastAttempt) {
                // Force the lockout time to be exactly 15 minutes (900 seconds)
                $remainingTime = 900;
                
                return [
                    'allowed' => false,
                    'remaining_time' => $remainingTime,
                    'message' => "Account temporarily locked. Try again in 15 minutes."
                ];
            }
        }
        
        return ['allowed' => true];
    }
    
    /**
     * Record a failed login attempt
     */
    public function recordFailedAttempt($email) {
        $query = "INSERT INTO login_attempts (ip_address, email, attempt_time, success) 
                    VALUES (?, ?, NOW(), 0)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ss', $this->ipAddress, $email);
        $stmt->execute();
    }
    
    /**
     * Record a successful login attempt
     */
    public function recordSuccessfulAttempt($email) {
        $query = "INSERT INTO login_attempts (ip_address, email, attempt_time, success) 
                    VALUES (?, ?, NOW(), 1)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('ss', $this->ipAddress, $email);
        $stmt->execute();
        
        // Reset failed attempts for this IP
        $this->resetAttempts();
    }
    
    /**
     * Reset failed attempts for this IP
     */
    private function resetAttempts() {
        $query = "DELETE FROM login_attempts 
                    WHERE ip_address = ? AND success = 0";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('s', $this->ipAddress);
        $stmt->execute();
    }
    
    /**
     * Clean up old records
     */
    private function cleanupOldRecords() {
        $query = "DELETE FROM login_attempts 
                    WHERE attempt_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute();
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
    
    /**
     * Get remaining attempts
     */
    public function getRemainingAttempts() {
        $query = "SELECT COUNT(*) as attempts 
                    FROM login_attempts 
                    WHERE ip_address = ? 
                    AND success = 0 
                    AND attempt_time > DATE_SUB(NOW(), INTERVAL ? SECOND)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param('si', $this->ipAddress, $this->lockoutTime);
        $stmt->execute();
        
        $result = $stmt->get_result()->fetch_assoc();
        return max(0, $this->maxAttempts - $result['attempts']);
    }
}
?>
