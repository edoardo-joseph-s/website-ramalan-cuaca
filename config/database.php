<?php
// Database configuration
class Database {
    private $db_path;
    private $conn;

    public function __construct() {
        $this->db_path = __DIR__ . '/../weather_app.db';
    }

    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO("sqlite:" . $this->db_path);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Create tables if they don't exist
            $this->createTables();
            
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
    
    private function createTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100) NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS user_settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            theme VARCHAR(20) DEFAULT 'light',
            temperature_unit VARCHAR(1) DEFAULT 'C',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS favorite_locations (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            location_name VARCHAR(255) NOT NULL,
            latitude DECIMAL(10, 8) NOT NULL,
            longitude DECIMAL(11, 8) NOT NULL,
            kecamatan VARCHAR(100),
            kota VARCHAR(100),
            provinsi VARCHAR(100),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS search_history (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            session_id VARCHAR(255),
            location_name VARCHAR(255) NOT NULL,
            latitude DECIMAL(10, 8) NOT NULL,
            longitude DECIMAL(11, 8) NOT NULL,
            kecamatan VARCHAR(100),
            kota VARCHAR(100),
            provinsi VARCHAR(100),
            searched_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );
        
        CREATE TABLE IF NOT EXISTS search_limits (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            session_id VARCHAR(255) UNIQUE NOT NULL,
            search_count INTEGER DEFAULT 0,
            last_search DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        INSERT OR IGNORE INTO users (id, username, email, password, full_name) 
        VALUES (1, 'admin', 'admin@weather.com', '$2y$12$4zH3kbzbo/nQPbZbgVTDnu.QsSPlSOO8tgPyXQE6hOPNeq09rPRt.', 'Administrator');
        
        -- Update admin password if user already exists
        UPDATE users SET password = '$2y$12$4zH3kbzbo/nQPbZbgVTDnu.QsSPlSOO8tgPyXQE6hOPNeq09rPRt.' WHERE username = 'admin';
        
        INSERT OR IGNORE INTO user_settings (user_id, theme, temperature_unit) 
        VALUES (1, 'light', 'C');
        ";
        
        $this->conn->exec($sql);
    }
}

// Session configuration
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getSessionId() {
    return session_id();
}

function redirectTo($url) {
    header("Location: $url");
    exit();
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>