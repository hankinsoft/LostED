<?php
	include_once('include.php');

	// Create a TCP/IP socket
	$socket = createSocket();

	// Set our values
	$targetFile   = trim($_REQUEST['targetFile']);

	// Login
	loginToServer();

    $toSend = "checkfile " . $targetFile . "\n";
	$output =  writeToServer($toSend);

	$res = new stdClass();
    $res->success = false;
    $res->message = $output;

	die(json_encode($res));