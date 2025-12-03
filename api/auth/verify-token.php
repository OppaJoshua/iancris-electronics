<?php
require_once '../../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['idToken']) || !isset($input['email'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$firebase_uid = sanitizeInput($input['uid'] ?? '');
$email = sanitizeInput($input['email']);
$display_name = sanitizeInput($input['displayName'] ?? '');
$photo_url = sanitizeInput($input['photoURL'] ?? '');

try {
    $conn = getDBConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }

    // Check if user exists
    $check_query = "SELECT * FROM users WHERE firebase_uid = $1 OR email = $2";
    $result = pg_query_params($conn, $check_query, array($firebase_uid, $email));
    
    if (pg_num_rows($result) > 0) {
        // Update existing user
        $user = pg_fetch_assoc($result);
        $update_query = "UPDATE users SET email = $1, display_name = $2, photo_url = $3, updated_at = CURRENT_TIMESTAMP WHERE id = $4 RETURNING id";
        $update_result = pg_query_params($conn, $update_query, array($email, $display_name, $photo_url, $user['id']));
        
        if ($update_result) {
            $user_data = pg_fetch_assoc($update_result);
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['email'] = $email;
            $_SESSION['display_name'] = $display_name;
            $_SESSION['photo_url'] = $photo_url;
            
            echo json_encode([
                'success' => true,
                'message' => 'User logged in successfully',
                'user' => [
                    'id' => $user_data['id'],
                    'email' => $email,
                    'display_name' => $display_name
                ]
            ]);
        }
    } else {
        // Create new user
        $insert_query = "INSERT INTO users (firebase_uid, email, display_name, photo_url) VALUES ($1, $2, $3, $4) RETURNING id";
        $insert_result = pg_query_params($conn, $insert_query, array($firebase_uid, $email, $display_name, $photo_url));
        
        if ($insert_result) {
            $user_data = pg_fetch_assoc($insert_result);
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['email'] = $email;
            $_SESSION['display_name'] = $display_name;
            $_SESSION['photo_url'] = $photo_url;
            
            echo json_encode([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => [
                    'id' => $user_data['id'],
                    'email' => $email,
                    'display_name' => $display_name
                ]
            ]);
        }
    }
    
    closeDBConnection($conn);
    
} catch (Exception $e) {
    error_log('Error in verify-token: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
}
?>