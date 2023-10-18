<?php
	include_once('include.php');

	// Create a TCP/IP socket
	$socket = createSocket();

	function verifyQuickList($dataString) {
		$lines = explode("\n", trim($dataString));
		$entries = [];

	    foreach ($lines as $line) {
	        $name = explode(";", $line)[1];
	        $entries[] = [
	            "id" => trim($name),
	            "name" => trim($name),
	            "spriteCssClass" => "tree-folder",
	            "hasChildren" => true
	        ];
	    }

		// Sort the entries using the custom comparison function
		usort($entries, 'compare_tree_entries');

	    return $entries;  // Return the processed data entries if valid, false otherwise
	}

	// Login
	loginToServer();
	$result = writeToServer("quicklist\n");

	die(json_encode(verifyQuickList($result)));