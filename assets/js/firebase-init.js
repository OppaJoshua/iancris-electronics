// Firebase initialization and utilities
// This file is loaded via module script in footer

import { initializeApp } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js';
import { getAuth, signInWithPopup, GoogleAuthProvider, signOut, onAuthStateChanged } from 'https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js';

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
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const provider = new GoogleAuthProvider();

export { auth, provider, signInWithPopup, signOut, onAuthStateChanged };