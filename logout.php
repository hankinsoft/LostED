<?php
// Check if the username cookie is set
if(isset($_COOKIE['username'])) {
    // Unset the username cookie by setting its expiration time to an hour ago (3600 seconds)
    setcookie('username', '', time() - 3600, '/');
}

// Check if the password cookie is set
if(isset($_COOKIE['password'])) {
    // Unset the password cookie by setting its expiration time to an hour ago (3600 seconds)
    setcookie('password', '', time() - 3600, '/');
}

// Redirect to login.php
header('Location: login.php');
exit;
?>