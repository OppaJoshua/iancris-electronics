import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
import { getAuth, signInWithPopup, GoogleAuthProvider, onAuthStateChanged } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js';

// Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyCs39YGAPOkhVn7OughUIm-R1gfpINffBw",
    authDomain: "iancris-electronics.firebaseapp.com",
    projectId: "iancris-electronics",
    storageBucket: "iancris-electronics.firebasestorage.app",
    messagingSenderId: "1023774984228",
    appId: "1:1023774984228:web:73cda64515649dd2fb30b1",
    measurementId: "G-ZKCL13RTFD"
};

// Initialize Firebase
let app, auth, provider;

try {
    app = initializeApp(firebaseConfig);
    auth = getAuth(app);
    provider = new GoogleAuthProvider();
    console.log('Firebase initialized successfully');
} catch (error) {
    console.error('Firebase initialization error:', error);
}

// Google Sign-In function
window.signInWithGoogle = async function() {
    console.log('signInWithGoogle called');
    
    if (!auth || !provider) {
        alert('Firebase is not initialized. Please check your configuration.');
        console.error('Auth or provider not initialized');
        return;
    }

    try {
        console.log('Attempting Google sign-in popup...');
        const result = await signInWithPopup(auth, provider);
        const user = result.user;
        
        console.log('Google sign-in successful:', user.email);
        
        // Get ID token
        const idToken = await user.getIdToken();
        
        console.log('Sending token to backend...');
        
        // Send to backend
        const response = await fetch('/api/auth/google-login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                idToken: idToken,
                email: user.email,
                name: user.displayName,
                photoURL: user.photoURL
            })
        });

        const data = await response.json();
        console.log('Backend response:', data);
        
        if (data.success) {
            console.log('Login successful, redirecting...');
            window.location.href = '/index.php';
        } else {
            alert('Login failed: ' + data.message);
        }
    } catch (error) {
        console.error('Google Sign-In Error:', error);
        console.error('Error code:', error.code);
        console.error('Error message:', error.message);
        
        // User-friendly error messages
        let errorMessage = 'Error signing in with Google';
        
        if (error.code === 'auth/popup-closed-by-user') {
            errorMessage = 'Sign-in cancelled. Please try again.';
        } else if (error.code === 'auth/popup-blocked') {
            errorMessage = 'Popup was blocked. Please allow popups for this site.';
        } else if (error.code === 'auth/cancelled-popup-request') {
            errorMessage = 'Another sign-in popup is already open.';
        } else if (error.code === 'auth/unauthorized-domain') {
            errorMessage = 'This domain is not authorized. Please add it to Firebase Console.';
        }
        
        alert(errorMessage + '\n\nTechnical details: ' + error.message);
    }
};

// Monitor auth state
onAuthStateChanged(auth, (user) => {
    if (user) {
        console.log('User is signed in:', user.email);
    } else {
        console.log('User is signed out');
    }
});