<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    private $table_name = "users";
    private $settings_table = "user_settings";
    private $favorites_table = "favorite_locations";
    private $history_table = "search_history";
    private $limits_table = "search_limits";
    
    public function __construct($pdo = null) {
        if ($pdo) {
            $this->conn = $pdo;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }
    
    // Register new user
    public function register($username, $email, $password, $full_name) {
        try {
            // Check if username or email already exists
            $query = "SELECT id FROM " . $this->table_name . " WHERE username = :username OR email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                return ['success' => false, 'message' => 'Username atau email sudah digunakan'];
            }
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $query = "INSERT INTO " . $this->table_name . " (username, email, password, full_name) VALUES (:username, :email, :password, :full_name)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':full_name', $full_name);
            
            if ($stmt->execute()) {
                $user_id = $this->conn->lastInsertId();
                
                // Create default settings
                $this->createDefaultSettings($user_id);
                
                return ['success' => true, 'message' => 'Akun berhasil dibuat', 'user_id' => $user_id];
            }
            
            return ['success' => false, 'message' => 'Gagal membuat akun'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // Input sanitization method
    private function sanitizeInput($input) {
        return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
    }
    
    // Start secure session with enhanced security
    private function startSecureSession() {
        // Regenerate session ID for security
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
    }
    
    // Generate CSRF token
    private function generateCSRFToken() {
        return bin2hex(random_bytes(32));
    }
    
    // Update last login time
    private function updateLastLogin($user_id) {
        try {
            $query = "UPDATE " . $this->table_name . " SET last_login = datetime('now') WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$user_id]);
        } catch (Exception $e) {
            error_log("Update last login error: " . $e->getMessage());
        }
    }
    
    // Enhanced login function without password hashing
    public function login($username, $password) {
        try {
            // Input validation and sanitization
            $username = $this->sanitizeInput($username);
            $password = trim($password);
            
            if (empty($username) || empty($password)) {
                return ['success' => false, 'message' => 'Username dan password harus diisi'];
            }
            
            // Check for rate limiting
            if ($this->isRateLimited($username)) {
                return ['success' => false, 'message' => 'Terlalu banyak percobaan login. Coba lagi dalam 15 menit.'];
            }
            
            // Enhanced query with SQLite compatibility
            $query = "SELECT id, username, email, password, full_name, created_at, last_login FROM " . $this->table_name . " WHERE (username = ? OR email = ?) AND id > 0 LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$username, $username]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Direct password comparison (no hashing)
            if ($user && isset($user['password']) && $user['password'] === $password) {
                // Start secure session
                $this->startSecureSession();
                
                // Set comprehensive session data
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['login_time'] = time();
                $_SESSION['last_activity'] = time();
                $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                $_SESSION['csrf_token'] = $this->generateCSRFToken();
                
                // Update last login time
                $this->updateLastLogin($user['id']);
                
                // Log successful login
                $this->logLoginAttempt($username, true, $_SERVER['REMOTE_ADDR'] ?? 'unknown');
                
                // Clear failed attempts
                $this->clearFailedAttempts($username);
                
                return [
                    'success' => true, 
                    'message' => 'Login berhasil', 
                    'user' => [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email'],
                        'full_name' => $user['full_name']
                    ]
                ];
            }
            
            // Log failed login attempt
            $this->logLoginAttempt($username, false, $_SERVER['REMOTE_ADDR'] ?? 'unknown');
            $this->recordFailedAttempt($username);
            
            return ['success' => false, 'message' => 'Username/email atau password salah'];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'];
        }
    }
    
    // Logout user
    public function logout() {
        session_destroy();
        return ['success' => true, 'message' => 'Logout berhasil'];
    }
    
    // Rate limiting methods
    private function isRateLimited($username) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = md5($username . $ip);
        
        // Check if attempts table exists, create if not
        $this->createLoginAttemptsTable();
        
        $query = "SELECT COUNT(*) as attempts FROM login_attempts WHERE identifier = ? AND attempt_time > datetime('now', '-15 minutes')";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$key]);
        
        $result = $stmt->fetch();
        return $result['attempts'] >= 5; // Max 5 attempts in 15 minutes
    }
    
    private function recordFailedAttempt($username) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = md5($username . $ip);
        
        $query = "INSERT INTO login_attempts (identifier, username, ip_address, attempt_time, success) VALUES (?, ?, ?, datetime('now'), 0)";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$key, $username, $ip]);
    }
    
    private function clearFailedAttempts($username) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = md5($username . $ip);
        
        $query = "DELETE FROM login_attempts WHERE identifier = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$key]);
    }
    
    private function logLoginAttempt($username, $success, $ip) {
        $query = "INSERT INTO login_attempts (identifier, username, ip_address, attempt_time, success) VALUES (?, ?, ?, datetime('now'), ?)";
        $stmt = $this->conn->prepare($query);
        $key = md5($username . $ip . time()); // Unique key for logging
        $success_value = $success ? 1 : 0;
        $stmt->execute([$key, $username, $ip, $success_value]);
    }
    
    private function createLoginAttemptsTable() {
        $query = "CREATE TABLE IF NOT EXISTS login_attempts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            identifier TEXT NOT NULL,
            username TEXT NOT NULL,
            ip_address TEXT NOT NULL,
            attempt_time DATETIME NOT NULL,
            success INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $this->conn->exec($query);
    }
    
    // Create multiple test users
    public function createTestUsers() {
        $testUsers = [
            ['username' => 'user1', 'email' => 'user1@test.com', 'password' => 'password123', 'full_name' => 'User Satu'],
            ['username' => 'user2', 'email' => 'user2@test.com', 'password' => 'password123', 'full_name' => 'User Dua'],
            ['username' => 'user3', 'email' => 'user3@test.com', 'password' => 'password123', 'full_name' => 'User Tiga'],
            ['username' => 'testuser', 'email' => 'test@example.com', 'password' => 'test123', 'full_name' => 'Test User'],
            ['username' => 'demo', 'email' => 'demo@weather.com', 'password' => 'demo123', 'full_name' => 'Demo User']
        ];
        
        $results = [];
        foreach ($testUsers as $userData) {
            $result = $this->register($userData['username'], $userData['email'], $userData['password'], $userData['full_name']);
            $results[] = $userData['username'] . ': ' . $result['message'];
        }
        
        return $results;
     }
     
     // Get user settings
    public function getSettings($user_id) {
        try {
            $query = "SELECT * FROM " . $this->settings_table . " WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetch() ?: ['theme' => 'light', 'temperature_unit' => 'C'];
            
        } catch (Exception $e) {
            return ['theme' => 'light', 'temperature_unit' => 'C'];
        }
    }
    
    // Update user settings
    public function updateSettings($user_id, $theme, $temperature_unit) {
        try {
            $query = "UPDATE " . $this->settings_table . " SET theme = :theme, temperature_unit = :temperature_unit WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':theme', $theme);
            $stmt->bindParam(':temperature_unit', $temperature_unit);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Create default settings for new user
    private function createDefaultSettings($user_id) {
        try {
            $query = "INSERT INTO " . $this->settings_table . " (user_id, theme, temperature_unit) VALUES (:user_id, 'light', 'C')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
        } catch (Exception $e) {
            // Ignore error
        }
    }
    
    // Add favorite location
    public function addFavorite($user_id, $latitude, $longitude, $location_name, $kecamatan = '', $kota = '', $provinsi = '') {
        try {
            // Check if location already exists for this user
            $check_query = "SELECT id FROM " . $this->favorites_table . " WHERE user_id = :user_id AND latitude = :latitude AND longitude = :longitude";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(':user_id', $user_id);
            $check_stmt->bindParam(':latitude', $latitude);
            $check_stmt->bindParam(':longitude', $longitude);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                return false; // Already exists
            }
            
            $query = "INSERT INTO " . $this->favorites_table . " (user_id, location_name, latitude, longitude, kecamatan, kota, provinsi) VALUES (:user_id, :location_name, :latitude, :longitude, :kecamatan, :kota, :provinsi)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':location_name', $location_name);
            $stmt->bindParam(':latitude', $latitude);
            $stmt->bindParam(':longitude', $longitude);
            $stmt->bindParam(':kecamatan', $kecamatan);
            $stmt->bindParam(':kota', $kota);
            $stmt->bindParam(':provinsi', $provinsi);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Remove favorite location
    public function removeFavorite($user_id, $favorite_id) {
        try {
            $query = "DELETE FROM " . $this->favorites_table . " WHERE id = :id AND user_id = :user_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $favorite_id);
            $stmt->bindParam(':user_id', $user_id);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Get user favorites
    public function getFavorites($user_id) {
        try {
            $query = "SELECT * FROM " . $this->favorites_table . " WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Add search history
    public function addSearchHistory($location_name, $latitude, $longitude, $kecamatan = '', $kota = '', $provinsi = '', $user_id = null) {
        try {
            $session_id = $user_id ? null : getSessionId();
            
            $query = "INSERT INTO " . $this->history_table . " (user_id, session_id, location_name, latitude, longitude, kecamatan, kota, provinsi) VALUES (:user_id, :session_id, :location_name, :latitude, :longitude, :kecamatan, :kota, :provinsi)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':session_id', $session_id);
            $stmt->bindParam(':location_name', $location_name);
            $stmt->bindParam(':latitude', $latitude);
            $stmt->bindParam(':longitude', $longitude);
            $stmt->bindParam(':kecamatan', $kecamatan);
            $stmt->bindParam(':kota', $kota);
            $stmt->bindParam(':provinsi', $provinsi);
            
            return $stmt->execute();
            
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Get search history
    public function getSearchHistory($user_id = null, $limit = 10) {
        try {
            if ($user_id) {
                $query = "SELECT * FROM " . $this->history_table . " WHERE user_id = :user_id ORDER BY searched_at DESC LIMIT :limit";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
            } else {
                $session_id = getSessionId();
                $query = "SELECT * FROM " . $this->history_table . " WHERE session_id = :session_id ORDER BY searched_at DESC LIMIT :limit";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':session_id', $session_id);
            }
            
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    // Check and update search limit for guest users
    public function checkSearchLimit() {
        if (isLoggedIn()) {
            return ['allowed' => true, 'remaining' => 'unlimited'];
        }
        
        try {
            $session_id = getSessionId();
            
            // Get current search count and last search time
            $query = "SELECT search_count, last_search FROM " . $this->limits_table . " WHERE session_id = :session_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();
            
            $result = $stmt->fetch();
            $current_count = $result ? $result['search_count'] : 0;
            $last_search = $result ? $result['last_search'] : null;
            
            // Check if 3 minutes have passed since last search
            if ($last_search) {
                $last_search_time = new DateTime($last_search);
                $now = new DateTime();
                $diff = $now->diff($last_search_time);
                $minutes_passed = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
                
                // Reset if 3 minutes have passed
                if ($minutes_passed >= 3) {
                    $query = "UPDATE " . $this->limits_table . " SET search_count = 0, last_search = CURRENT_TIMESTAMP WHERE session_id = :session_id";
                    $stmt = $this->conn->prepare($query);
                    $stmt->bindParam(':session_id', $session_id);
                    $stmt->execute();
                    $current_count = 0;
                }
            }
            
            // Calculate reset time (3 minutes from last search)
            $reset_time = null;
            $time_remaining = null;
            if ($last_search && $current_count >= 3) {
                $last_search_time = new DateTime($last_search);
                $reset_time = clone $last_search_time;
                $reset_time->add(new DateInterval('PT3M')); // Add 3 minutes
                
                $now = new DateTime();
                if ($reset_time > $now) {
                    $time_diff = $now->diff($reset_time);
                    $time_remaining = [
                        'hours' => $time_diff->h + ($time_diff->days * 24),
                        'minutes' => $time_diff->i,
                        'seconds' => $time_diff->s,
                        'total_seconds' => ($time_diff->h + ($time_diff->days * 24)) * 3600 + $time_diff->i * 60 + $time_diff->s
                    ];
                }
            }
            
            // Check if limit reached BEFORE incrementing
            if ($current_count >= 3) {
                return [
                    'allowed' => false, 
                    'remaining' => 0, 
                    'message' => 'Batas pencarian tercapai. Limit akan reset dalam 3 menit atau silakan login untuk melanjutkan.',
                    'reset_time' => $reset_time ? $reset_time->format('Y-m-d H:i:s') : null,
                    'time_remaining' => $time_remaining
                ];
            }
            
            // Allow search and increment count
            if ($result) {
                $query = "UPDATE " . $this->limits_table . " SET search_count = search_count + 1, last_search = CURRENT_TIMESTAMP WHERE session_id = :session_id";
            } else {
                $query = "INSERT INTO " . $this->limits_table . " (session_id, search_count, last_search) VALUES (:session_id, 1, CURRENT_TIMESTAMP)";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();
            
            // Return remaining searches after increment
            $new_count = $current_count + 1;
            return ['allowed' => true, 'remaining' => 3 - $new_count];
            
        } catch (Exception $e) {
            return ['allowed' => true, 'remaining' => 3];
        }
    }
    
    // Get search limit status without incrementing counter
    public function getSearchLimitStatus() {
        if (isLoggedIn()) {
            return ['allowed' => true, 'remaining' => 'unlimited'];
        }
        
        try {
            $session_id = getSessionId();
            
            // Get current search count and last search time
            $query = "SELECT search_count, last_search FROM " . $this->limits_table . " WHERE session_id = :session_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':session_id', $session_id);
            $stmt->execute();
            
            $result = $stmt->fetch();
            $current_count = $result ? $result['search_count'] : 0;
            $last_search = $result ? $result['last_search'] : null;
            
            // Check if 3 minutes have passed since last search
            if ($last_search) {
                $last_search_time = new DateTime($last_search);
                $now = new DateTime();
                $diff = $now->diff($last_search_time);
                $minutes_passed = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
                
                // Reset if 3 minutes have passed
                if ($minutes_passed >= 3) {
                    $current_count = 0;
                }
            }
            
            // Calculate reset time (3 minutes from last search)
            $reset_time = null;
            $time_remaining = null;
            if ($last_search && $current_count >= 3) {
                $last_search_time = new DateTime($last_search);
                $reset_time = clone $last_search_time;
                $reset_time->add(new DateInterval('PT3M')); // Add 3 minutes
                
                $now = new DateTime();
                if ($reset_time > $now) {
                    $time_diff = $now->diff($reset_time);
                    $time_remaining = [
                        'hours' => $time_diff->h + ($time_diff->days * 24),
                        'minutes' => $time_diff->i,
                        'seconds' => $time_diff->s,
                        'total_seconds' => ($time_diff->h + ($time_diff->days * 24)) * 3600 + $time_diff->i * 60 + $time_diff->s
                    ];
                }
            }
            
            return [
                'allowed' => $current_count < 3,
                'remaining' => max(0, 3 - $current_count),
                'current_count' => $current_count,
                'reset_time' => $reset_time ? $reset_time->format('Y-m-d H:i:s') : null,
                'time_remaining' => $time_remaining
            ];
            
        } catch (Exception $e) {
            return ['allowed' => true, 'remaining' => 3, 'current_count' => 0];
        }
    }
}
?>