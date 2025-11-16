<?php
// Database configuration
// NOTE: For production, move these credentials to environment variables
// Example: define('DB_HOST', getenv('DB_HOST'));
define('DB_HOST', 'srv941.hstgr.io');
define('DB_NAME', 'u237055794_comp');
define('DB_USER', 'u237055794_comp');
define('DB_PASS', ';zX#rHaV6');

// Get database connection
function getDB() {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        $conn->set_charset("utf8mb4");
    }
    
    return $conn;
}

// Execute a query and return the result
function query($sql, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }
    
    if ($params) {
        $types = '';
        $values = [];
        
        foreach ($params as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_double($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }
        
        $stmt->bind_param($types, ...$values);
    }
    
    $stmt->execute();
    return $stmt->get_result();
}

// Fetch all rows
function fetchAll($sql, $params = []) {
    $result = query($sql, $params);
    
    if ($result === false || $result === true) {
        return [];
    }
    
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

// Fetch one row
function fetchOne($sql, $params = []) {
    $result = query($sql, $params);
    
    if ($result === false || $result === true) {
        return null;
    }
    
    return $result->fetch_assoc();
}

// Execute a statement without returning results
function execute($sql, $params = []) {
    $db = getDB();
    $stmt = $db->prepare($sql);
    
    if ($stmt === false) {
        die("Prepare failed: " . $db->error);
    }
    
    if ($params) {
        $types = '';
        $values = [];
        
        foreach ($params as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_double($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }
        
        $stmt->bind_param($types, ...$values);
    }
    
    return $stmt->execute();
}

// Get last inserted ID
function lastInsertId() {
    $db = getDB();
    return $db->insert_id;
}

// Start session if not already started
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

// Get current user
function getCurrentUser() {
    startSession();
    if (!isLoggedIn()) {
        return null;
    }
    
    return fetchOne("SELECT user_id, username, email, created_at FROM users WHERE user_id = ?", [$_SESSION['user_id']]);
}

// Login user
function loginUser($userId, $username) {
    startSession();
    // Regenerate session ID to prevent session fixation
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;
    $_SESSION['logged_in_at'] = time();
}

// Logout user
function logoutUser() {
    startSession();
    session_destroy();
    session_start();
}
?>
