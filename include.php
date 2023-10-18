<?php
	set_time_limit(0);
	ini_set('display_errors', 1); // Display errors to the output
	error_reporting(E_ALL);

	$host = "lost.wishes.net";
	$port = 5565;

	function getUser() {
		return $_COOKIE['username'];
	}
	
	function getPassword() {
		return $_COOKIE['password'];
	}
	
	function createSocket($dieOnFailure = true) {
		global $host, $port;

		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket === false) {
			if($dieOnFailure) {
				$res = new stdClass();
			    $res->success = false;
			    $res->message = "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
			    die(json_encode($res));
			}
			
			return false;
		}
	
		socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 0, "usec" => 250000));
	
		$result = socket_connect($socket, $host, $port);
		if ($result === false) {
			if($dieOnFailure) {
				$res = new stdClass();
			    $res->success = false;
			    $res->message = "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
			    die(json_encode($res));
			}
			
			return false;
		}
		
		return $socket;
	}

	function loginToServer($username = "", $password = "", $dieOnFailure = true)
	{
	    global $socket;

	    // If username is empty, get it from getUser()
	    if (empty($username)) {
	        $username = getUser();
	    }
	
	    // If password is empty, get it from getPassword()
	    if (empty($password)) {
	        $password = getPassword();
	    }
	
	    // Creating the login message
	    $loginMsg = "login " . $username . " " . $password . "\n";
	    
	    // Writing the login message to the server
	    socket_write($socket, $loginMsg, strlen($loginMsg));
	    
	    $response = socket_read($socket, 2048); // Reading the response from the server

	    // Check if the response is "OK\n"
	    if ($response === "OK\n") {
	        return true; // Login successful
	    } else {
		    if($dieOnFailure) {
		        // Handling error, you might want to customize the error handling here
		        $res = new stdClass();
		        $res->success = false;
		        $res->message = $response ? $response : "No response from server";
		        die(json_encode($res));
		    }
	    }
	    
	    return false;
	}

	function writeToServer($toServer, $requireDataCheck = true)
	{
	    global $socket;
	    $timeout = 10; // Set timeout to 10 seconds

		$out = $toServer;

		$log = str_replace("\n", "\\n", $out);
//		echo("Writing: " . $log . "<br />");

	    socket_write($socket, $out, strlen($out));

	    // ... (Your existing logging code here)
	    $startTime = time();

	    $expectedLength = null;
		$received = "";
	    while (true) {
	        $in = socket_read($socket, 2048);
	        if ($in !== false && $in !== "") {
	            $received .= $in;
	
	            if ($requireDataCheck && !isset($expectedLength) && preg_match('/DATA ([0-9]+)/', $received, $matches)) {
	                $expectedLength = intval($matches[1]);
	
	                // Remove the 'DATA x' part from the received string
	                $received = preg_replace('/DATA ([0-9]+)\n/', '', $received);
	            }
	
	            // Check if we've received the expected amount of data
	            if (isset($expectedLength) && strlen($received) >= $expectedLength) {
	                break;
	            }
	        }

			if(!isset($expectedLength)) {
				break;
			}

	        if (time() - $startTime > $timeout) {
	            // Timeout reached, exit the loop
	            break;
	        }
	    }

	    return $received;
	}

	// Function used to sorting tree entry results.
	function compare_tree_entries($a, $b) {
	    // If one has children and the other doesn't, prioritize the one with children
	    if ($a['hasChildren'] && !$b['hasChildren']) {
	        return -1;
	    } elseif (!$a['hasChildren'] && $b['hasChildren']) {
	        return 1;
	    }
	    
	    // If both have or don't have children, sort by name
	    return strcasecmp($a['name'], $b['name']);
	}