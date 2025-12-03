<?php
session_start();
echo "Session data before logout:<br>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// Clear session
$_SESSION = array();
session_destroy();

echo "<br>Session destroyed. Redirecting to index.php in 2 seconds...<br>";
echo "<a href='index.php'>Click here if not redirected</a>";
?>

<script>
setTimeout(() => {
    window.location.replace('index.php');
}, 2000);
</script>
