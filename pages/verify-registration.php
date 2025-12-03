<?php
session_start();
require_once '../config/database.php';

// Check if registration data exists
if (!isset($_SESSION['pending_registration'])) {
    header("Location: register.php");
    exit();
}

$reg_data = $_SESSION['pending_registration'];
$page_title = "Verify Your Email";
require_once '../includes/header.php';
require_once '../includes/nav.php';
?>

<!-- Firebase SDK -->
<script type="module">
import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
import { getAuth, createUserWithEmailAndPassword, sendEmailVerification, onAuthStateChanged } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js';

// Your Firebase configuration
const firebaseConfig = {
    apiKey: "YOUR_API_KEY",
    authDomain: "YOUR_PROJECT_ID.firebaseapp.com",
    projectId: "YOUR_PROJECT_ID",
    storageBucket: "YOUR_PROJECT_ID.appspot.com",
    messagingSenderId: "YOUR_MESSAGING_SENDER_ID",
    appId: "YOUR_APP_ID"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

const registrationData = <?php echo json_encode($reg_data); ?>;
const statusDiv = document.getElementById('status');
const resendBtn = document.getElementById('resendBtn');
const checkBtn = document.getElementById('checkBtn');

// Register user with Firebase on page load
window.addEventListener('load', async () => {
    try {
        statusDiv.innerHTML = '<p class="text-blue-600">Creating your account...</p>';
        
        // Create Firebase user
        const userCredential = await createUserWithEmailAndPassword(
            auth, 
            registrationData.email, 
            registrationData.password
        );
        
        // Send verification email
        await sendEmailVerification(userCredential.user);
        
        statusDiv.innerHTML = `
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <p class="font-semibold">Verification email sent!</p>
                <p class="text-sm mt-1">Please check your Gmail inbox and click the verification link.</p>
            </div>
        `;
        
        resendBtn.classList.remove('hidden');
        checkBtn.classList.remove('hidden');
        
    } catch (error) {
        console.error('Firebase error:', error);
        statusDiv.innerHTML = `
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <p>Error: ${error.message}</p>
                <p class="text-sm mt-1">Please try again or contact support.</p>
            </div>
        `;
    }
});

// Resend verification email
resendBtn.addEventListener('click', async () => {
    try {
        resendBtn.disabled = true;
        resendBtn.textContent = 'Sending...';
        
        await sendEmailVerification(auth.currentUser);
        
        alert('Verification email sent! Please check your Gmail.');
        resendBtn.textContent = 'Resend Email';
        resendBtn.disabled = false;
    } catch (error) {
        alert('Error: ' + error.message);
        resendBtn.disabled = false;
    }
});

// Check verification status
checkBtn.addEventListener('click', async () => {
    try {
        await auth.currentUser.reload();
        
        if (auth.currentUser.emailVerified) {
            // Update database
            const response = await fetch('update-verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    user_id: registrationData.user_id,
                    firebase_uid: auth.currentUser.uid
                })
            });
            
            if (response.ok) {
                window.location.href = 'login.php?verified=1';
            }
        } else {
            alert('Email not verified yet. Please check your Gmail and click the verification link.');
        }
    } catch (error) {
        alert('Error checking verification: ' + error.message);
    }
});
</script>

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
            <p class="text-gray-600 text-center">Please wait...</p>
        </div>
        
        <div class="space-y-3">
            <button id="checkBtn" class="hidden w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition font-semibold">
                I've Verified My Email
            </button>
            
            <button id="resendBtn" class="hidden w-full bg-gray-200 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-300 transition font-semibold">
                Resend Verification Email
            </button>
        </div>
        
        <div class="mt-6 text-center text-sm text-gray-600">
            <p>Didn't receive the email? Check your spam folder or click resend.</p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
