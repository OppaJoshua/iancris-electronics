<?php
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$page_title = "Register";
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $terms_accepted = isset($_POST['terms']) ? 1 : 0;
    $privacy_accepted = isset($_POST['privacy']) ? 1 : 0;
    
    // Validation
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif (!empty($phone) && !preg_match('/^[0-9]{11}$/', $phone)) {
        $error = "Phone number must be exactly 11 digits";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (!$terms_accepted) {
        $error = "You must accept the Terms and Conditions";
    } elseif (!$privacy_accepted) {
        $error = "You must accept the Privacy Policy";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id, email_verified FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $existing_user = $result->fetch_assoc();
            
            // If email exists but not verified, delete old record and allow re-registration
            if ($existing_user['email_verified'] == 0) {
                $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $delete_stmt->bind_param("i", $existing_user['id']);
                $delete_stmt->execute();
            } else {
                $error = "Email address is already registered and verified";
                $stmt->close();
                goto skip_registration;
            }
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user (email_verified = 0, will be updated after Firebase verification)
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password, email_verified) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $phone, $hashed_password);
        
        if ($stmt->execute()) {
            $user_id = $conn->insert_id;
            
            // Store user data for Firebase registration
            $_SESSION['pending_firebase_registration'] = [
                'user_id' => $user_id,
                'email' => $email,
                'password' => $password,
                'first_name' => $first_name,
                'last_name' => $last_name
            ];
            
            header("Location: firebase-verify.php");
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
        
        skip_registration:
    }
}

require_once '../includes/header.php';
require_once '../includes/nav.php';
?>

<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="mb-8">
            <h2 class="text-center text-3xl font-extrabold text-gray-900">Create your account</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Already have an account? 
                <a href="login.php" class="text-blue-600 hover:text-blue-700 font-medium">Sign in</a>
            </p>
        </div>
        
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
                <span class="px-2 bg-white text-gray-500">Or register with email</span>
            </div>
        </div>
        
        <form method="POST" action="" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700">First Name *</label>
                    <input type="text" name="first_name" id="first_name" required 
                        value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name *</label>
                    <input type="text" name="last_name" id="last_name" required 
                        value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email Address *</label>
                <input type="email" name="email" id="email" required 
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                <input type="tel" name="phone" id="phone" 
                    value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                    pattern="[0-9]{11}"
                    maxlength="11"
                    placeholder="09123456789"
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">Must be exactly 11 digits (e.g., 09123456789)</p>
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password *</label>
                <input type="password" name="password" id="password" required 
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">Minimum 6 characters</p>
            </div>
            
            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password *</label>
                <input type="password" name="confirm_password" id="confirm_password" required 
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="space-y-3 pt-4 border-t border-gray-200">
                <div class="flex items-start">
                    <input type="checkbox" name="terms" id="terms" required
                        class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="terms" class="ml-2 text-sm text-gray-700">
                        I accept the <a href="terms.php" target="_blank" class="text-blue-600 hover:text-blue-700 font-medium">Terms and Conditions</a> *
                    </label>
                </div>
                
                <div class="flex items-start">
                    <input type="checkbox" name="privacy" id="privacy" required
                        class="mt-1 h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="privacy" class="ml-2 text-sm text-gray-700">
                        I accept the <a href="privacy.php" target="_blank" class="text-blue-600 hover:text-blue-700 font-medium">Privacy Policy</a> *
                    </label>
                </div>
            </div>
            
            <div class="pt-4">
                <button type="submit" name="register" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition font-semibold">
                    Create Account
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
        
        console.log('Google Sign-In successful:', user);
        
        // Send user data to PHP for registration/login
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

// Add phone number formatting and validation
document.getElementById('phone').addEventListener('input', function(e) {
    // Remove non-numeric characters
    let value = e.target.value.replace(/\D/g, '');
    
    // Limit to 11 digits
    if (value.length > 11) {
        value = value.slice(0, 11);
    }
    
    e.target.value = value;
    
    // Visual feedback
    if (value.length > 0 && value.length !== 11) {
        e.target.classList.add('border-yellow-400');
        e.target.classList.remove('border-gray-300', 'border-green-400');
    } else if (value.length === 11) {
        e.target.classList.add('border-green-400');
        e.target.classList.remove('border-gray-300', 'border-yellow-400');
    } else {
        e.target.classList.add('border-gray-300');
        e.target.classList.remove('border-yellow-400', 'border-green-400');
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
