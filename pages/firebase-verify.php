<?php
session_start();
require_once '../config/database.php';

// Check if registration data exists
if (!isset($_SESSION['pending_firebase_registration'])) {
    header("Location: register.php");
    exit();
}

$reg_data = $_SESSION['pending_firebase_registration'];
$page_title = "Verify Your Email";
require_once '../includes/header.php';
require_once '../includes/nav.php';
?>

<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow-md">
        <div class="text-center mb-6">
            <svg class="mx-auto h-16 w-16 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <h2 class="mt-4 text-2xl font-bold text-gray-900">Verify Your Email</h2>
            <p class="mt-2 text-sm text-gray-600">
                Hello <?php echo htmlspecialchars($reg_data['first_name']); ?>!
            </p>
        </div>
        
        <div id="status" class="mb-6">
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded animate-pulse">
                <p class="text-center">🔄 Creating your account and sending verification email...</p>
            </div>
        </div>
        
        <div class="space-y-3">
            <button id="resendBtn" class="hidden w-full bg-gray-200 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-300 transition font-semibold">
                📧 Resend Verification Email
            </button>
        </div>
        
        <div id="instructions" class="hidden mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-sm text-green-800 font-semibold mb-2">📧 Verification Email Sent!</p>
            <ol class="text-sm text-green-700 space-y-2 ml-4 list-decimal">
                <li>Check your Gmail inbox: <strong><?php echo htmlspecialchars($reg_data['email']); ?></strong></li>
                <li>Open the email from "noreply@iancris-electronics.firebaseapp.com"</li>
                <li><strong>Click the verification link</strong> in the email</li>
                <li>You will be automatically redirected to login ✓</li>
            </ol>
            <p class="text-xs text-green-600 mt-3">💡 Tip: Check your spam/junk folder if you don't see it</p>
            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded">
                <p class="text-xs text-blue-700">🔄 This page is automatically checking for verification...</p>
            </div>
        </div>
        
        <div class="mt-6 text-center text-sm text-gray-600">
            <p>The verification email will arrive in a few seconds.</p>
        </div>
    </div>
</div>

<!-- Firebase SDK -->
<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
import { getAuth, createUserWithEmailAndPassword, sendEmailVerification } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js';

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

const registrationData = <?php echo json_encode($reg_data); ?>;
const statusDiv = document.getElementById('status');
const resendBtn = document.getElementById('resendBtn');
const instructions = document.getElementById('instructions');

// Auto-register with Firebase on page load
window.addEventListener('load', async () => {
    try {
        console.log('Creating Firebase user for:', registrationData.email);
        
        // Create Firebase user
        const userCredential = await createUserWithEmailAndPassword(
            auth, 
            registrationData.email, 
            registrationData.password
        );
        
        console.log('✓ Firebase user created:', userCredential.user.uid);
        
        // Send verification email through Firebase
        await sendEmailVerification(userCredential.user);
        
        console.log('✓ Firebase verification email sent');
        
        // Update database with Firebase UID
        const updateResponse = await fetch('update-firebase-uid.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ 
                user_id: registrationData.user_id,
                firebase_uid: userCredential.user.uid
            })
        });
        
        const updateResult = await updateResponse.json();
        console.log('Database update:', updateResult);
        
        // Show success message
        statusDiv.innerHTML = `
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <p class="font-semibold text-center">✓ Verification Email Sent!</p>
                <p class="text-sm mt-1 text-center">Check your Gmail: ${registrationData.email}</p>
            </div>
        `;
        
        instructions.classList.remove('hidden');
        resendBtn.classList.remove('hidden');
        
    } catch (error) {
        console.error('Firebase error:', error);
        
        let errorMessage = 'Failed to create account';
        let canRetry = true;
        
        // Handle specific Firebase errors
        switch(error.code) {
            case 'auth/email-already-in-use':
                errorMessage = 'This email is already registered in Firebase. Please login instead.';
                canRetry = false;
                break;
            case 'auth/invalid-email':
                errorMessage = 'Invalid email address format';
                break;
            case 'auth/operation-not-allowed':
                errorMessage = 'Email/password sign-in is not enabled. Please contact support.';
                canRetry = false;
                break;
            case 'auth/weak-password':
                errorMessage = 'Password is too weak. Use at least 6 characters.';
                break;
            default:
                errorMessage = error.message;
        }
        
        statusDiv.innerHTML = `
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <p class="font-semibold">❌ Error</p>
                <p class="text-sm mt-1">${errorMessage}</p>
                <div class="mt-3 space-x-2">
                    ${canRetry ? '<button onclick="location.reload()" class="text-sm underline">Try Again</button>' : ''}
                    <a href="register.php" class="text-sm underline">Back to Register</a>
                    <a href="login.php" class="text-sm underline">Login</a>
                </div>
            </div>
        `;
    }
});

// Resend verification email
resendBtn.addEventListener('click', async () => {
    try {
        resendBtn.disabled = true;
        resendBtn.textContent = '📤 Sending...';
        
        if (auth.currentUser) {
            await sendEmailVerification(auth.currentUser);
            alert('✓ Verification email sent! Please check your Gmail.');
            console.log('Verification email resent');
        } else {
            alert('Please refresh the page and try again.');
        }
        
        resendBtn.textContent = '📧 Resend Verification Email';
        resendBtn.disabled = false;
    } catch (error) {
        console.error('Resend error:', error);
        alert('❌ Error: ' + error.message);
        resendBtn.textContent = '📧 Resend Verification Email';
        resendBtn.disabled = false;
    }
});

// Auto-check verification status every 3 seconds
let autoCheckInterval = setInterval(async () => {
    if (auth.currentUser) {
        try {
            await auth.currentUser.reload();
            if (auth.currentUser.emailVerified) {
                clearInterval(autoCheckInterval);
                console.log('✓ Auto-detected email verification!');
                
                // Show verifying message
                statusDiv.innerHTML = `
                    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded animate-pulse">
                        <p class="text-center">🔄 Email verified! Updating your account...</p>
                    </div>
                `;
                
                // Update database
                const response = await fetch('update-email-verification.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        user_id: registrationData.user_id,
                        firebase_uid: auth.currentUser.uid
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    statusDiv.innerHTML = `
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                            <p class="font-semibold text-center">✓ Email Verified Successfully!</p>
                            <p class="text-sm mt-1 text-center">Redirecting to login...</p>
                        </div>
                    `;
                    
                    setTimeout(() => {
                        window.location.href = 'login.php?verified=1';
                    }, 2000);
                } else {
                    alert('❌ Error updating verification status. Please try logging in.');
                    window.location.href = 'login.php';
                }
            }
        } catch (error) {
            console.error('Auto-check error:', error);
        }
    }
}, 3000); // Check every 3 seconds

// Stop auto-checking after 10 minutes
setTimeout(() => {
    clearInterval(autoCheckInterval);
    console.log('Auto-check stopped after 10 minutes');
}, 600000);
</script>

<?php require_once '../includes/footer.php'; ?>
