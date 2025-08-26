<?php
class SessionManager {
    private $sessionTimeout = 1800; // 30 minutes in seconds
    private $regenerateInterval = 300; // 5 minutes in seconds
    
    public function __construct() {
        // Only set session parameters if session hasn't started yet
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_secure', $this->isHTTPS());
            ini_set('session.use_strict_mode', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            // Set cookie parameters programmatically
            $lifetime = $this->sessionTimeout;
            $path = '/';
            $domain = '';
            $secure = $this->isHTTPS();
            $httponly = true;
            
            // Apply session cookie parameters
            session_set_cookie_params($lifetime, $path, $domain, $secure, $httponly);
            
            // Now start the session
            session_start();
        }
        
        // Check session timeout
        $this->checkSessionTimeout();
        
        // Regenerate session ID periodically
        $this->regenerateSessionIfNeeded();
    }
    
    /**
     * Handle AJAX session extension request
     */
    public function handleAjaxExtension() {
        // Set content type to JSON
        header('Content-Type: application/json');
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'User not logged in']);
            exit();
        }
        
        // Validate current session
        if (!$this->validateSession()) {
            echo json_encode(['success' => false, 'message' => 'Invalid session']);
            exit();
        }
        
        // Extend session
        $this->extendSession();
        
        // Return success response
        echo json_encode([
            'success' => true, 
            'message' => 'Session extended successfully',
            'remaining_time' => $this->getRemainingTime()
        ]);
    }
    
    /**
     * Check if current session has timed out
     */
    private function checkSessionTimeout() {
        if (isset($_SESSION['last_activity'])) {
            $timeElapsed = time() - $_SESSION['last_activity'];
            
            if ($timeElapsed > $this->sessionTimeout) {
                // Session has timed out
                $this->destroySession();
                $this->redirectToLogin(); // Redirect without error parameter
            }
        }
        
        // Update last activity time
        $_SESSION['last_activity'] = time();
    }
    
    /**
     * Regenerate session ID periodically for security
     */
    private function regenerateSessionIfNeeded() {
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > $this->regenerateInterval) {
            // Regenerate session ID
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
    
    /**
     * Create a new session for user
     */
    public function createSession($userData) {
        // Clear any existing session data
        session_unset();
        
        // Set session data
        $_SESSION['user_id'] = $userData['user_id'];
        $_SESSION['role'] = $userData['role'];
        $_SESSION['email'] = $userData['email'];
        $_SESSION['name'] = $userData['name'];
        if (isset($userData['firstname'])) {
            $_SESSION['firstname'] = $userData['firstname'];
        }
        if (isset($userData['lastname'])) {
            $_SESSION['lastname'] = $userData['lastname'];
        }
        if (isset($userData['department'])) {
            $_SESSION['department'] = $userData['department'];
        }
        $_SESSION['last_activity'] = time();
        $_SESSION['created'] = time();
        $_SESSION['ip_address'] = $this->getClientIP();
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        // Regenerate the session ID for security
        session_regenerate_id(true);
    }
    
    /**
     * Validate current session
     */
    public function validateSession() {
        // Check if session exists
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check if IP address changed (potential session hijacking)
        if (isset($_SESSION['ip_address']) && $_SESSION['ip_address'] !== $this->getClientIP()) {
            $this->destroySession();
            return false;
        }
        
        // Check if user agent changed
        if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== ($_SERVER['HTTP_USER_AGENT'] ?? '')) {
            $this->destroySession();
            return false;
        }
        
        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            $timeElapsed = time() - $_SESSION['last_activity'];
            
            if ($timeElapsed > $this->sessionTimeout) {
                $this->destroySession();
                $this->redirectToLogin(); // Redirect to login without error message
                return false;
            }
            
            // Update last activity
            $_SESSION['last_activity'] = time();
        }
        
        return true;
    }
    
    /**
     * Destroy current session
     */
    public function destroySession() {
        // Unset all session variables
        session_unset();
        
        // Destroy the session
        session_destroy();
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
    }
    
    /**
     * Extend session timeout
     */
    public function extendSession() {
        if (isset($_SESSION['last_activity'])) {
            $_SESSION['last_activity'] = time();
        }
    }
    
    /**
     * Get session timeout in seconds
     */
    public function getSessionTimeout() {
        return $this->sessionTimeout;
    }
    
    /**
     * Get remaining session time in seconds
     */
    public function getRemainingTime() {
        if (!isset($_SESSION['last_activity'])) {
            return 0;
        }
        
        $timeElapsed = time() - $_SESSION['last_activity'];
        return max(0, $this->sessionTimeout - $timeElapsed);
    }
    
    /**
     * Redirect to login with message
     */
    private function redirectToLogin($message = '') {
        // Simple redirect to the login page
        $loginUrl = '/index.php';
        
        // Add timeout error parameter if no specific message
        if (!$message) {
            $loginUrl .= '?error=timeout';
        } elseif ($message) {
            $loginUrl .= '?' . $message;
        }
        
        header('Location: ' . $loginUrl);
        exit();
    }
    
    /**
     * Check if current connection is HTTPS
     */
    private function isHTTPS() {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
               ($_SERVER['SERVER_PORT'] == 443) ||
               (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    }
    
    /**
     * Get client IP address
     */
    private function getClientIP() {
        $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
        
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
}
?>
