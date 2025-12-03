// Firebase Configuration
const firebaseConfig = {
  apiKey: "AIzaSyCs39YGAPOkhVn7OughUIm-R1gfpINffBw",
  authDomain: "iancris-electronics.firebaseapp.com",
  projectId: "iancris-electronics",
  storageBucket: "iancris-electronics.firebasestorage.app",
  messagingSenderId: "1023774984228",
  appId: "1:1023774984228:web:73cda64515649dd2fb30b1",
  measurementId: "G-ZKCL13RTFD"
};

// Export config for use in other files
if (typeof module !== 'undefined' && module.exports) {
  module.exports = firebaseConfig;
}