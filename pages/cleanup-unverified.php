<?php
require_once '../config/database.php';

// Delete all unverified users (email_verified = 0)
$result = $conn->query("DELETE FROM users WHERE email_verified = 0 AND role = 'user'");

if ($result) {
    echo "Deleted " . $conn->affected_rows . " unverified user(s).<br>";
    echo '<a href="register.php">Go to Register</a>';
} else {
    echo "Error: " . $conn->error;
}
?>
