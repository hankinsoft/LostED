<?php
	include_once('include.php');

	// Create a TCP/IP socket
	$socket = createSocket();

	$targetFile = trim($_REQUEST['file']);

	// Login
	loginToServer();
	$result = writeToServer("readfile " . $targetFile . "\n");

	if(is_string($result)) {
		$res = new stdClass();
	    $res->success = true;
	    $res->contents = $result;

		die(json_encode($res));
	}

	die(json_encode($result));