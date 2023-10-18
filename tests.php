<pre>
<?php
set_time_limit(0);

$host = "lost.wishes.net";
$port = 5565;

// Create a TCP/IP socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
    exit;
}

socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 1, "usec"=>0));

echo "Attempting to connect to '$host' on port '$port'...\n";
$result = socket_connect($socket, $host, $port);
if ($result === false) {
    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
    exit;
}

function writeToServer($toServer)
{
	global $socket;

	$out = $toServer . "\n";
	socket_write($socket, $out, strlen($out));

	$logMsg = $toServer;
	if (strpos($logMsg, "login") === 0) {  // Check if the string starts with "login"
	    $lastSpacePos = strrpos($logMsg, ' ');  // Find the last space position
	    
	    if ($lastSpacePos !== false) {
	        $logMsg = substr_replace($logMsg, ' ********', $lastSpacePos);
	    }
	}

	echo "Message sent to server: '" . $logMsg . "'\n";

	$counter = 0;
	while($counter <= 3) {
		if(($in = socket_read($socket, 2048)) !== false && $in !== "") {
		    echo "Received response from server:\n$in\n";
		    echo "\n";
		}

        ++$counter;
	}
}

login();
writeToServer("toollist\n");

writeToServer("quicklist\n");
writeToServer("who\n");
writeToServer("filelist /u/z/zenox/\n");

writeToServer("readfile /u/z/zenox/area_the_rock.txt\n");

writeToServer("format /u/z/zenox/edserv.c\n");




// Close the socket
socket_close($socket);
?>
</pre>