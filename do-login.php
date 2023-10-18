<?php
	include_once('include.php');

	// Create a TCP/IP socket
	$socket = createSocket();

	// Check if username and password are received from the POST request
	if (isset($_POST['username']) && isset($_POST['password'])) {
	    $username = $_POST['username'];
	    $password = $_POST['password'];
	
	    // Call loginToServer function
	    if (loginToServer($username, $password, false)) {
	        // If login is successful, set cookies and redirect to index.php
	        setcookie('username', $username, time() + 86400, '/'); // Cookie expires after one day
	        setcookie('password', $password, time() + 86400, '/'); // Cookie expires after one day
	        header('Location: index.php');
	        exit;
	    } else {
	        // If login fails, redirect back to login page with an error message
	        header('Location: login.php?error=1');
	        exit;
	    }
	} else {
	    // If username or password is not set, redirect to login page
	    header('Location: login.php');
	    exit;
	}
?>