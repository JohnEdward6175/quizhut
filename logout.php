<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// ✅ Clear all session data
session_unset();
session_destroy();

// ✅ Redirect back to the homepage
header("Location: home.php");
exit;
?>
