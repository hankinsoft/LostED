<?php
	include_once('include.php');

	// Create a TCP/IP socket
	$socket = createSocket();

	// Set our values
	$targetFile   = trim($_REQUEST['targetFile']);
	$fileContents = trim($_REQUEST['fileContents']);

	// Login
	loginToServer();

    $toSend = "data " . strlen($fileContents) . "\n";
	$output =  writeToServer($toSend, false);

	sleep(1);
    $toSend = $fileContents . "writefile " . $targetFile . "\n";
	$output =  writeToServer($toSend, false);

	if("OK" == trim($output)) {
    	$toSend = "format " . $targetFile . "\n";
		$output =  writeToServer($toSend, false);
		sleep(0.5);

		$readResult = writeToServer("readfile " . $targetFile . "\n");
		if(is_string($readResult)) {
			$res = new stdClass();
		    $res->success = true;
		    $res->contents = $readResult;
	
			die(json_encode($res));
		}

		$res = new stdClass();
	    $res->success = true;
	    $res->contents = "Failed to read file after writing";

		die(json_encode($res));
	}

	$res = new stdClass();
    $res->success = false;
    $res->message = $result;

	die(json_encode($res));