<?php
session_start();

// Check if user came from login page
if (!isset($_SESSION['verify_email']) || !isset($_SESSION['verify_user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Verify Login";
require_once '../config/database.php';

$error = '';
$resend_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['verify'])) {
        $code = trim($_POST['code']);
        $user_id = $_SESSION['verify_user_id'];
        
        // Check verification code
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, role, verification_code, code_expiry FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // Check if code expired
            if (strtotime($user['code_expiry']) < time()) {
                $error = "Verification code has expired. Please request a new one.";
            } elseif ($user['verification_code'] === $code) {
                // Code is correct, log user in
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_first_name'] = $user['first_name'];
                
                // Clear verification data
                unset($_SESSION['verify_email']);
                unset($_SESSION['verify_user_id']);
                unset($_SESSION['verify_code_display']);
                
                // Clear code from database and update last login
                $clear_stmt = $conn->prepare("UPDATE users SET verification_code = NULL, code_expiry = NULL, last_login = NOW() WHERE id = ?");
                $clear_stmt->bind_param("i", $user['id']);
                $clear_stmt->execute();
                
                header("Location: ../index.php");
                exit();
            } else {
                $error = "Invalid verification code. Please try again.";
            }
        }
    } elseif (isset($_POST['resend'])) {
        // Resend verification code
        $user_id = $_SESSION['verify_user_id'];
        $verification_code = sprintf("%06d", mt_rand(100000, 999999));
        $code_expiry = date('Y-m-d H:i:s', strtotime('+15 minutes'));
        
        $stmt = $conn->prepare("UPDATE users SET verification_code = ?, code_expiry = ? WHERE id = ?");
        $stmt->bind_param("ssi", $verification_code, $code_expiry, $user_id);
        $stmt->execute();
        
        $_SESSION['verify_code_display'] = $verification_code; // For testing
        
        $resend_message = "New verification code generated!";
    }
}

require_once '../includes/header.php';
require_once '../includes/nav.php';
?>

<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-lg shadow-md">
        <div>
            <h2 class="text-center text-3xl font-extrabold text-gray-900">Verify Your Login</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter the 6-digit verification code
            </p>
        </div>
        
        <?php if (isset($_SESSION['verify_code_display'])): ?>
            <div class="bg-yellow-50 border-2 border-yellow-400 rounded-lg p-4">
                <p class="font-semibold text-yellow-800 text-center">🔒 Your Verification Code:</p>
                <p class="text-3xl font-bold text-center mt-3 mb-3 tracking-widest text-yellow-900"><?php echo $_SESSION['verify_code_display']; ?></p>
                <p class="text-xs text-yellow-700 text-center">Copy this code and enter it below</p>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($resend_message): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                <?php echo htmlspecialchars($resend_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="space-y-6">
            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Verification Code</label>
                <input type="text" name="code" id="code" required maxlength="6" pattern="[0-9]{6}"
                    class="block w-full px-4 py-4 border-2 border-gray-300 rounded-lg text-center text-3xl tracking-widest font-bold focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="000000" autocomplete="off">
                <p class="mt-2 text-xs text-gray-500 text-center">Enter the 6-digit code shown above</p>
            </div>
            
            <div>
                <button type="submit" name="verify" class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition font-semibold text-lg">
                    Verify & Sign In
                </button>
            </div>
        </form>
        
        <div class="text-center space-y-3 pt-4 border-t border-gray-200">
            <form method="POST" action="">
                <button type="submit" name="resend" class="text-blue-600 hover:text-blue-700 font-medium text-sm">
                    🔄 Generate New Code
                </button>
            </form>
            
            <div>
                <a href="login.php" class="text-sm text-gray-600 hover:text-gray-700">
                    ← Back to Login
                </a>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-focus on code input
document.getElementById('code').focus();

// Only allow numbers
document.getElementById('code').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
    
    // Auto-submit when 6 digits entered
    if (this.value.length === 6) {
        setTimeout(() => {
            this.form.submit();
        }, 500);
    }
});

// Allow paste
document.getElementById('code').addEventListener('paste', function(e) {
    setTimeout(() => {
        this.value = this.value.replace(/[^0-9]/g, '').substring(0, 6);
        if (this.value.length === 6) {
            setTimeout(() => {
                this.form.submit();
            }, 500);
        }
    }, 10);
});
</script>

<?php require_once '../includes/footer.php'; ?>
