<?php
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$page_title = "Login";
require_once '../config/database.php';

$error = '';
$success = '';

// Check for verification success message
if (isset($_GET['verified']) && $_GET['verified'] == '1') {
    $success = "✓ Email verified successfully! You can now login.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields";
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, status, email_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Check if email is verified
            if ($user['email_verified'] == 0) {
                $error = "Please verify your email address before logging in. Check your Gmail inbox.";
            } elseif ($user['status'] == 'blocked') {
                $error = "Your account has been blocked. Please contact support.";
            } elseif (password_verify($password, $user['password'])) {
                // Update last login
                $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                header("Location: ../index.php");
                exit();
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/nav.php';
?>

<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="mb-8">
            <h2 class="text-center text-3xl font-extrabold text-gray-900">Sign in to your account</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Don't have an account? 
                <a href="register.php" class="text-blue-600 hover:text-blue-700 font-medium">Register</a>
            </p>
        </div>
        
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Google Sign-In Button -->
        <div class="mb-6">
            <button id="googleSignInBtn" class="w-full flex items-center justify-center gap-3 bg-white border-2 border-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-50 transition font-semibold">
                <svg class="w-5 h-5" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                Continue with Google
            </button>
        </div>
        
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Or sign in with email</span>
            </div>
        </div>
        
        <form method="POST" action="" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                <input type="email" name="email" id="email" required 
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required 
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="pt-4">
                <button type="submit" name="login" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition font-semibold">
                    Sign In
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Firebase SDK for Google Sign-In -->
<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
import { getAuth, signInWithPopup, GoogleAuthProvider } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js';

const firebaseConfig = {
    apiKey: "AIzaSyCs39YGAPOkhVn7OughUIm-R1gfpINffBw",
    authDomain: "iancris-electronics.firebaseapp.com",
    projectId: "iancris-electronics",
    storageBucket: "iancris-electronics.firebasestorage.app",
    messagingSenderId: "1023774984228",
    appId: "1:1023774984228:web:73cda64515649dd2fb30b1",
    measurementId: "G-ZKCL13RTFD"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const provider = new GoogleAuthProvider();

document.getElementById('googleSignInBtn').addEventListener('click', async () => {
    try {
        const result = await signInWithPopup(auth, provider);
        const user = result.user;
        
        // Send user data to PHP for login/registration
        const response = await fetch('google-auth.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                firebase_uid: user.uid,
                email: user.email,
                first_name: user.displayName?.split(' ')[0] || 'User',
                last_name: user.displayName?.split(' ').slice(1).join(' ') || '',
                photo_url: user.photoURL,
                email_verified: user.emailVerified
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            window.location.href = '../index.php';
        } else {
            alert('Error: ' + data.error);
        }
        
    } catch (error) {
        console.error('Google Sign-In error:', error);
        alert('Google Sign-In failed: ' + error.message);
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>