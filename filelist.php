<?php
	include_once('include.php');

	// Create a TCP/IP socket
	$socket = createSocket();

	$targetFolder = trim($_REQUEST['folder']);
	// Check if the last character is not a '/'
	if (strlen($targetFolder) && substr($targetFolder, -1) !== '/') {
	    // Append a '/' at the end
	    $targetFolder .= '/';
	}

	function parseFileList($data) {
		global $targetFolder;

		$lines = explode("\n", trim($data));
		$entries = [];

	    foreach ($lines as $line) {
		    $vars = explode(";", $line);
	        $name = trim($vars[0]);
	        $type = (int)$vars[1];
	        $spriteCssClass = "";
	        $hasChildren = false;
			$fullPath = $targetFolder . $name;

			if(-2 == $type) {
				$spriteCssClass = "tree-folder";
				$hasChildren = true;
			}
			else
			{
				// Extracting the file extension
				$fileExtension = pathinfo($fullPath, PATHINFO_EXTENSION);
				$spriteCssClass = 'tree-' . strtolower($fileExtension);
			}

			if($name == "..") {
				continue;
			}

	        $entries[] = [
	            "id" => trim($fullPath),
	            "name" => trim($name),
	            "spriteCssClass" => $spriteCssClass,
	            "hasChildren" => $hasChildren
	        ];
	    }

		// Sort the entries using the custom comparison function
		usort($entries, 'compare_tree_entries');

	    return $entries;  // Return the processed data entries if valid, false otherwise
	}

	// Login
	loginToServer();
	$result = writeToServer("filelist " . $targetFolder . "\n");

	die(json_encode(parseFileList($result)));