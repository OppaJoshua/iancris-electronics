<?php
session_start();

// If already logged in, redirect to dashboard - THIS MUST COME FIRST
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$page_title = "Login";
require_once '../config/database.php';

$error = '';

// Check if user was redirected due to being blocked
if (isset($_GET['blocked']) && $_GET['blocked'] == 1) {
    $error = "Your account has been blocked. Please contact support for assistance.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Get user with status check - MAKE SURE TO SELECT ROLE
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, role, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Check if user is blocked
        if (isset($user['status']) && $user['status'] === 'blocked') {
            $error = "Your account has been blocked. Please contact support for assistance.";
        } elseif (password_verify($password, $user['password'])) {
            // Password is correct, set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_role'] = $user['role']; // CRITICAL: Set the role
            $_SESSION['user_first_name'] = $user['first_name'];
            
            // Update last login
            $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $user['id']);
            $update_stmt->execute();
            
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    } else {
        $error = "Invalid email or password";
    }
}

// NOW include header and nav - AFTER all potential redirects
require_once '../includes/header.php';
require_once '../includes/nav.php';
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md">
        <div>
            <h2 class="text-center text-3xl font-extrabold text-gray-900">Sign in to your account</h2>
        </div>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"></div>
        
        <!-- Google Sign In Button -->
        <div>
            <button id="google-signin-btn" class="w-full flex items-center justify-center gap-3 bg-white border border-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-50 transition font-semibold">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M19.8 10.2273C19.8 9.51819 19.7364 8.83637 19.6182 8.18182H10.2V12.05H15.6109C15.3727 13.3 14.6636 14.3591 13.6045 15.0682V17.5773H16.8273C18.7091 15.8364 19.8 13.2727 19.8 10.2273Z" fill="#4285F4"/>
                    <path d="M10.2 20C12.9 20 15.1636 19.1045 16.8273 17.5773L13.6045 15.0682C12.7091 15.6682 11.5636 16.0227 10.2 16.0227C7.59091 16.0227 5.38182 14.2636 4.58636 11.9H1.25455V14.4909C2.90909 17.7591 6.29091 20 10.2 20Z" fill="#34A853"/>
                    <path d="M4.58636 11.9C4.38636 11.3 4.27273 10.6591 4.27273 10C4.27273 9.34091 4.38636 8.7 4.58636 8.1V5.50909H1.25455C0.572727 6.85909 0.2 8.38636 0.2 10C0.2 11.6136 0.572727 13.1409 1.25455 14.4909L4.58636 11.9Z" fill="#FBBC05"/>
                    <path d="M10.2 3.97727C11.6864 3.97727 13.0182 4.48182 14.0591 5.47273L16.9091 2.62273C15.1591 0.986364 12.8955 0 10.2 0C6.29091 0 2.90909 2.24091 1.25455 5.50909L4.58636 8.1C5.38182 5.73636 7.59091 3.97727 10.2 3.97727Z" fill="#EA4335"/>
                </svg>
                Continue with Google
            </button>
        </div>
        
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Or continue with email</span>
            </div>
        </div>
        
        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <input type="email" name="email" id="email" required 
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" required 
                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div>
                <button type="submit" name="login" class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition font-semibold">
                    Sign In
                </button>
            </div>
            
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account? 
                    <a href="register.php" class="text-blue-600 hover:text-blue-700 font-medium">Sign up</a>
                </p>
            </div>
        </form>
    </div>
</div>

<!-- Firebase SDK -->
<script type="module">
  import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
  import { getAuth, signInWithPopup, GoogleAuthProvider } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

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

  document.getElementById('google-signin-btn').addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Signing in...';
    
    signInWithPopup(auth, provider)
      .then((result) => {
        const user = result.user;
        console.log('Firebase user:', user);
        
        // Send user data to backend
        return fetch('firebase-login-handler.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            email: user.email,
            name: user.displayName,
            uid: user.uid,
            photoURL: user.photoURL
          })
        });
      })
      .then(res => {
        console.log('Response status:', res.status);
        return res.json();
      })
      .then(data => {
        console.log('Backend response:', data);
        if (data.success) {
          console.log('Redirecting...');
          // Handle redirect properly
          const redirectUrl = data.redirect || 'dashboard.php';
          window.location.href = redirectUrl;
        } else {
          btn.disabled = false;
          btn.innerHTML = `<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M19.8 10.2273C19.8 9.51819 19.7364 8.83637 19.6182 8.18182H10.2V12.05H15.6109C15.3727 13.3 14.6636 14.3591 13.6045 15.0682V17.5773H16.8273C18.7091 15.8364 19.8 13.2727 19.8 10.2273Z" fill="#4285F4"/>
                    <path d="M10.2 20C12.9 20 15.1636 19.1045 16.8273 17.5773L13.6045 15.0682C12.7091 15.6682 11.5636 16.0227 10.2 16.0227C7.59091 16.0227 5.38182 14.2636 4.58636 11.9H1.25455V14.4909C2.90909 17.7591 6.29091 20 10.2 20Z" fill="#34A853"/>
                    <path d="M4.58636 11.9C4.38636 11.3 4.27273 10.6591 4.27273 10C4.27273 9.34091 4.38636 8.7 4.58636 8.1V5.50909H1.25455C0.572727 6.85909 0.2 8.38636 0.2 10C0.2 11.6136 0.572727 13.1409 1.25455 14.4909L4.58636 11.9Z" fill="#FBBC05"/>
                    <path d="M10.2 3.97727C11.6864 3.97727 13.0182 4.48182 14.0591 5.47273L16.9091 2.62273C15.1591 0.986364 12.8955 0 10.2 0C6.29091 0 2.90909 2.24091 1.25455 5.50909L4.58636 8.1C5.38182 5.73636 7.59091 3.97727 10.2 3.97727Z" fill="#EA4335"/>
                </svg> Continue with Google`;
          document.getElementById('error-message').textContent = 'Login failed: ' + (data.message || 'Unknown error');
          document.getElementById('error-message').classList.remove('hidden');
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = `<svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M19.8 10.2273C19.8 9.51819 19.7364 8.83637 19.6182 8.18182H10.2V12.05H15.6109C15.3727 13.3 14.6636 14.3591 13.6045 15.0682V17.5773H16.8273C18.7091 15.8364 19.8 13.2727 19.8 10.2273Z" fill="#4285F4"/>
                    <path d="M10.2 20C12.9 20 15.1636 19.1045 16.8273 17.5773L13.6045 15.0682C12.7091 15.6682 11.5636 16.0227 10.2 16.0227C7.59091 16.0227 5.38182 14.2636 4.58636 11.9H1.25455V14.4909C2.90909 17.7591 6.29091 20 10.2 20Z" fill="#34A853"/>
                    <path d="M4.58636 11.9C4.38636 11.3 4.27273 10.6591 4.27273 10C4.27273 9.34091 4.38636 8.7 4.58636 8.1V5.50909H1.25455C0.572727 6.85909 0.2 8.38636 0.2 10C0.2 11.6136 0.572727 13.1409 1.25455 14.4909L4.58636 11.9Z" fill="#FBBC05"/>
                    <path d="M10.2 3.97727C11.6864 3.97727 13.0182 4.48182 14.0591 5.47273L16.9091 2.62273C15.1591 0.986364 12.8955 0 10.2 0C6.29091 0 2.90909 2.24091 1.25455 5.50909L4.58636 8.1C5.38182 5.73636 7.59091 3.97727 10.2 3.97727Z" fill="#EA4335"/>
                </svg> Continue with Google`;
        document.getElementById('error-message').textContent = 'Error: ' + error.message;
        document.getElementById('error-message').classList.remove('hidden');
      });
  });
</script>

<?php require_once '../includes/footer.php'; ?>