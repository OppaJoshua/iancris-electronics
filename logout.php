<?php
// Start session
session_start();

// Destroy all session data
$_SESSION = array();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Clear any output buffers
if (ob_get_level()) {
    ob_end_clean();
}

// Set no-cache headers
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logging Out...</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: #ffffff;
            overflow: hidden;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes spin {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
            }
        }
        
        @keyframes checkmark {
            0% {
                stroke-dashoffset: 50;
            }
            100% {
                stroke-dashoffset: 0;
            }
        }
        
        .fade-in {
            animation: fadeIn 0.4s ease-out;
        }
        
        .spinner {
            animation: spin 1s linear infinite;
        }
        
        .checkmark-circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            animation: checkmark 0.5s ease-out forwards;
        }
        
        .checkmark-check {
            stroke-dasharray: 50;
            stroke-dashoffset: 50;
            animation: checkmark 0.5s 0.2s ease-out forwards;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="fade-in text-center px-4">
        <!-- Logo/Brand -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">IanCris Electronics</h1>
        </div>
        
        <!-- Logout Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-10 max-w-md mx-auto">
            <!-- Icon Container -->
            <div id="spinner-container" class="mb-6">
                <div class="w-16 h-16 mx-auto">
                    <svg class="spinner w-full h-full text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
            
            <!-- Success Icon (hidden initially) -->
            <div id="success-icon" class="hidden mb-6">
                <svg class="w-16 h-16 mx-auto" viewBox="0 0 52 52">
                    <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none" stroke="#2563EB" stroke-width="2"/>
                    <path class="checkmark-check" fill="none" stroke="#2563EB" stroke-width="2.5" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                </svg>
            </div>
            
            <!-- Messages -->
            <h2 id="logout-title" class="text-xl font-semibold text-gray-900 mb-2">Signing out</h2>
            <p id="logout-message" class="text-gray-600 text-sm">Please wait a moment...</p>
        </div>
        
        <!-- Footer -->
        <div class="mt-8">
            <p class="text-gray-500 text-xs">
                Your session has been securely terminated
            </p>
        </div>
    </div>

    <script>
        // Clear browser history and cache
        if (window.history && window.history.pushState) {
            window.history.pushState(null, null, window.location.href);
            window.onpopstate = function() {
                window.history.go(1);
            };
        }

        // IMPORTANT: Clear ALL storage
        localStorage.clear();
        sessionStorage.clear();
        
        // Clear cookies
        document.cookie.split(";").forEach(function(c) { 
            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
        });
        
        // Simulate logout process with stages
        setTimeout(() => {
            // Show success state
            document.getElementById('spinner-container').classList.add('hidden');
            document.getElementById('success-icon').classList.remove('hidden');
            document.getElementById('logout-title').textContent = 'Signed out successfully';
            document.getElementById('logout-message').textContent = 'Redirecting to home...';
            
            // Redirect after showing success - FIXED: Now goes to index.php (home)
            setTimeout(() => {
                // USER LOGOUT: Force redirect to HOME PAGE
                window.location.replace(window.location.origin + '/iancris-electronics/index.php?t=' + Date.now());
            }, 1200);
        }, 1500);
    </script>
</body>
</html>
