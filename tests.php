<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once('include.php');

// Create a TCP/IP socket
$socket = createSocket();
?>
<pre>
<?php
// Login
loginToServer();

//	print_r(writeToServer("toollist\n"));
	print_r(writeToServer("checkfile /u/z/zenox/SSandsOld/obj/eye_patch.c\n"));
/*	
	writeToServer("quicklist\n");
	writeToServer("who\n");
	writeToServer("filelist /u/z/zenox/\n");
	
	writeToServer("readfile /u/z/zenox/area_the_rock.txt\n");
	
	// writeToServer("format /u/z/zenox/edserv.c\n");
	

	
*/
	// Close the socket
	socket_close($socket);
?>