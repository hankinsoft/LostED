<?php
	include_once('include.php');

// Check if the cookies for username and password are set
if (!isset($_COOKIE['username']) || !isset($_COOKIE['password'])) {
    // If the cookies are not set, redirect to the login page
    header('Location: login.php');
    exit;
}

	// Create a TCP/IP socket
	$socket = createSocket(false);

	if ($socket === false) {
	    // If the cookies are not set, redirect to the login page
	    header('Location: login.php');
	    exit;
	}

	if(!loginToServer(getUser(), getPassword(), false)) {
	    // If the cookies are not set, redirect to the login page
	    header('Location: login.php');
	    exit;
	}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>LostED</title>

    <!-- Kendo UI Styles -->
    <link rel="stylesheet" href="https://kendo.cdn.telerik.com/2022.1.301/styles/kendo.common.min.css" />
    <link rel="stylesheet" href="https://kendo.cdn.telerik.com/2022.1.301/styles/kendo.default.min.css" />
    <link rel="stylesheet" href="https://kendo.cdn.telerik.com/2022.1.301/styles/kendo.default.mobile.min.css" />

    <!-- CodeMirror Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/theme/dracula.min.css">

    <script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
    <script src="https://kendo.cdn.telerik.com/2022.1.301/js/kendo.all.min.js"></script>

    <!-- CodeMirror Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.15/mode/clike/clike.min.js"></script>

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        #splitview {
            height: 100%;
        }

	    .toolbar-spacer {
	          float: right;

	    }

		#tabstrip
		{
		    height: calc(100% - 42px); /* Assuming the toolbar height is 40px */
		    border-width:0;
		}
        
        .welcomeTab {
	        padding: 5px;
        }
        
        .k-link .k-icon {
			margin-left: 10px;
		}

		.tab-modified::after {
		    content: ' (modified)';
		    color: grey;
		}
        
        .errorMessage {
	        color: rgba(255, 0, 0, 1);
        }
        
        .loading-indicator {
	        padding: 5px;
        }

        .CodeMirror {
            height: 100%;
            font-size: 14px;
        }
        
		.k-tabstrip .k-content {
		    padding: 0 !important; /* Remove padding from tab content */
		}


		.tree-folder {
		    background-image: url('images/folder-16.png');
		    background-repeat: no-repeat;
		    padding-left: 3px; // Adjusted for the displayed size of the image
		    background-size: 16px 16px; // Display the image at 16x16 dimensions
		    background-position: left center;
		}

		.tree-c {
		    background-image: url('images/extension-c-16.png');
		    background-repeat: no-repeat;
		    padding-left: 3px; // Adjusted for the displayed size of the image
		    background-size: 16px 16px; // Display the image at 16x16 dimensions
		    background-position: left center;
		}

		.tree-h {
		    background-image: url('images/extension-h-16.png');
		    background-repeat: no-repeat;
		    padding-left: 3px; // Adjusted for the displayed size of the image
		    background-size: 16px 16px; // Display the image at 16x16 dimensions
		    background-position: left center;
		}

		.tree-txt {
		    background-image: url('images/extension-txt-16.png');
		    background-repeat: no-repeat;
		    padding-left: 3px; // Adjusted for the displayed size of the image
		    background-size: 16px 16px; // Display the image at 16x16 dimensions
		    background-position: left center;
		}

	    .cm-function {
	        color: #8210c9; /* Change the color according to your preference */
	    }
    </style>
</head>

<body>
<div id="messageDialog"></div>

<span id="notification"></span>

<div id="splitview">
    <div>
	    <div id="treeviewToolbar"></div>
        <div id="treeview"></div>
    </div>
    <div id="tabsSplitPortion">
	    <div id="filesToolbar"></div>
        <div id="tabstrip">
            <ul>
                <li class="k-state-active">Welcome <span class="k-icon k-i-close"></span></li>
            </ul>
            <div class="welcomeTab">
	            <h1>LostED webUI</h1>
	            <pre>Navigate through the left file tree. Expand a folder to load its contents, or click on a file to view in a tab.</pre>
            </div>
        </div>
    </div>
</div>

<script>
	function showMessage(title, data) {
		// Replace newlines with <br />'s.
		data = data.replace(/\n/g, "<br />");

	    var dialog = $("#messageDialog").data("kendoDialog");

	    dialog.title(title);

	    // Update the content of the dialog with your results
	    dialog.content("<pre>" + data + "</pre>");
	
	    // Open the dialog
	    dialog.open();
	}

	function initializeCodeMirror() {
		var currentEndString = "";
		CodeMirror.defineMode("clike_with_foreach", function(config, parserConfig) {
		    var clikeMode = CodeMirror.getMode(config, "text/x-c++src");
		    return {
		        startState: function() {
		            return {
		                clikeState: clikeMode.startState()
		            };
		        },
		        token: function(stream, state) {
			        	if(currentEndString.length) {

				        	var temp = clikeMode.token(stream, state.clikeState);
					        var matchedToken = stream.current(); // Get the current matched token
					        if(matchedToken == currentEndString) {
						        // Clear the currentEndString. Were done.
						        currentEndString = "";
					        }

							return "string";
			        	}

				      if (stream.match("foreach")) {
				        return "keyword";
				      }
				      if (stream.match("string") || stream.match("array") || stream.match("mixed") || stream.match("object") || stream.match("mapping")) {
				        return "type"; // or return "keyword" if you prefer
				      }

				      // Check for @WORD ... WORD patterns
				      if (stream.match(/@\w+/)) {
				        const matchedWord = stream.current().slice(1); // Get the matched word without "@"
						currentEndString = matchedWord;

				        return "string"; // Return a custom CSS class
				      }

				      const currentPos = stream.pos; // Save the current position
				      var tokenType = clikeMode.token(stream, state.clikeState);

					  if(tokenType === "keyword") {
			            return tokenType;
					  }

					stream.pos = currentPos;

						if (stream.match(/\b[\w$_]+\s*\(/)) {
							stream.pos -= 1;

//							        var matchedToken = stream.current(); // Get the current matched token

//							console.log("Found a function match: " + matchedToken);
						    return "function"; // Customize the CSS class as needed
						}

		            return clikeMode.token(stream, state.clikeState);
		        }
		    };
		});
		
		CodeMirror.defineMIME("fluffOS", "clike_with_foreach");
	}
	
	function createNotification() {
        var notification = $("#notification").kendoNotification({
            stacking: "down",
            templates: [{
                type: "info",
                template: $("#infoTemplate").html()
            }]
        }).data("kendoNotification");
        
        return notification;		
	}

    $(document).ready(function () {
		$("#messageDialog").kendoDialog({
		    width: "650px",
		    title: "Results",
		    closable: true,
		    modal: true,
		    content: "<p>Your results will be displayed here.</p>",
		    actions: [
		        { text: 'Close' }
		    ],
		    visible: false // Initially hidden; use .open() to show
		});

	    // Initialize the toolbar
	    $("#filesToolbar").kendoToolBar({
	        items: [
	            { type: "button", text: "Save", enable: false, attributes: { id: "saveButton" },
                    click: function () {
						saveFile();
                    } },
	            { type: "button", text: "Checkit", enable: false, attributes: { id: "checkitButton" },
                    click: function () {
                        runCheckit();
                    } },
/*
	            { type: "button", text: "Format", enable: false, attributes: { id: "formatButton" },
                    click: function () {
                        formatCode();
                    } },
*/
		        { template: "<div class='toolbar-spacer'></div>" },

		        { 
		            type: "button", 
		            text: "Logout", 
		            align: "right", 
		            attributes: { id: "logoutButton" },
		            click: function() {
		                window.location.href = 'logout.php'; // You can modify this URL if you have a different logout mechanism
		            }
		        }
	        ]
	    });

        $("#treeviewToolbar").kendoToolBar({
            items: [
                {
                    type: "button",
                    text: "New File",
                    click: function(e) {
						alert('Not implemented');
                    }
                }
            ]
        });

        // Initialize Splitter
        $("#splitview").kendoSplitter({
            panes: [
                {collapsible: true, size: "30%"},
                {collapsible: false}
            ]
        });

	    // Define the HierarchicalDataSource for lazy loading
	    var remoteDataSource = new kendo.data.HierarchicalDataSource({
	        transport: {
	            read: {
	                url: function(options) {		                
		                // Check if the options contain the fullPath property
		                var fullPath = options["id"] || "";
		                var result = "quicklist.php";
		                if(fullPath.length) {
			                result = "filelist.php?folder=" + encodeURIComponent(fullPath);
			            }

						// Append timestamp to the URL to prevent browser caching
		                result += (result.includes('?') ? '&' : '?') + '_=' + new Date().getTime();

	                    // If there's an ID, append it to the endpoint to fetch children of the specific folder
	                    return result;
	                },
	                dataType: "json",
                    cache: false // Prevent jQuery from caching the request

	            }
	        },
	        schema: {
	            model: {
	                id: "id",
		            hasChildren: "hasChildren",
		            fullPath: "fullPath",
	            }
	        }
	    });

        // Initialize TreeView
        var treeview = $("#treeview").kendoTreeView({
	        dataSource: remoteDataSource,
	        dataTextField: "name",  // Replace with the field you want to display
	        expand: function(e) {
	            // When a node is expanded, the data source will automatically fetch the children 
	            // based on the node's ID due to the url function defined above
	        },
            select: function (e) {
                var node = treeview.dataItem(e.node);

                if (!node.hasChildren) {
                    // Open editor in a new tab
                    var tabstrip = $("#tabstrip").data("kendoTabStrip");
					var existingTab = tabstrip.tabGroup.find('li[file-id="' + node.id + '"]');

				    if (existingTab.length > 0) {
				        // Select the existing tab
				        tabstrip.select(existingTab);
				    } else {
	                    // Add a new tab
	                    tabstrip.append({
							text: '',
	                        content: '<div class="loading-indicator">Loading...</div><div class="editor" id="' + node.id + '"></div>',
	                    });
	
	                    // Add close icon to the new tab
	                    var newTab = tabstrip.tabGroup.children("li:last");
	                    var kLinkSpan = newTab.find('.k-link');
	                    
	                    kLinkSpan.append('<span class="tab-title">' + node.name + '</span>');
	                    
	                    kLinkSpan.append('<span class="k-icon k-i-close"></span>');
						newTab.attr("file-id", node.id);

			            var loadingIndicator = $(".loading-indicator:last")[0];
	
			            // Fetch file content from the server and set it as the value of the CodeMirror instance
			            $.ajax({
				            cache: false,
							url: "readfile.php?file=" + encodeURIComponent(node.id) + "&_=" + new Date().getTime(),
			                type: "GET",
			                success: function (response) {
						        var data;
						
						        // Try to parse the response; if it fails, use the response as it is
						        try {
						            data = JSON.parse(response);
						        } catch (e) {
						            data = response;
						        }

								setTimeout(function() {
									loadingIndicator.remove(); // Remove the loading indicator

						            // Initialize CodeMirror
							        if(data.success) { // Check if success attribute is true
								        	// Initialize code mirror
									        initializeCodeMirror();

							                // Initialize CodeMirror
							                var editor = CodeMirror($(".editor:last")[0], {
							                    mode: "fluffOS",
							                    matchBrackets: true,
							                    lineNumbers: true,
							                    styleActiveLine: true,
							                });

							                editor.setValue(data.contents); // Set CodeMirror value to contents attribute in response
											editor.initialValue = editor.getValue();

							                editor.refresh();

											editor.on("change", function(cm, change) {
  											  // Mark our tab as modified
												  var titleElement = kLinkSpan.find(".tab-title");

											    // Compare with the initial content stored in the custom property
											    if(cm.getValue() === cm.initialValue) {
													titleElement.removeClass('tab-modified');
											    } else {
													titleElement.addClass('tab-modified');
											    }
											});

											// Update tabs afterwards
										    setTimeout(function() {
										        updateToolbarState();
										    }, 0);
							        } else {
								        var failureReason = "Unknown reason";
								        if(data.message) {
									        failureReason = data.message;
								        } else {
									        failureReason = data;
								        }

							            // If success is not true, append an error message to the tab content
							            $(".editor:last").before('<div class="errorMessage">Failed to load file. ' + failureReason + '</div>');
							        }
			                    				}, 250);
			                },
			                error: function () {
			                    alert("Failed to load file content.");
			                }
			            });

	                    // Select and activate the new tab
	                    tabstrip.select(newTab);

					}
                }
            }
        }).data("kendoTreeView");

        // Initialize TabStrip
        $("#tabstrip").kendoTabStrip({
            animation: {
                open: {
                    effects: "fadeIn"
                }
            },
		    activate: function(e) {
	            setTimeout(function() {
		            updateToolbarState();
				}, 0);
		    }
        });

		// Add close tab functionality
		$("#tabstrip").on("click", ".k-icon.k-i-close", function (e) {
		    var item = $(this).closest("li");
		    var tabstrip = $("#tabstrip").data("kendoTabStrip");
		    var currentActiveTabIndex = tabstrip.select().index(); // Get the index of the currently active tab
		    var closedTabIndex = item.index(); // Get the index of the closed tab
		
		    // Remove the tab
		    tabstrip.remove(item);
		
		    // If the closed tab was the active tab, decide which tab to activate next
		    if (currentActiveTabIndex === closedTabIndex) {
		        if (closedTabIndex > 0) { // If it's not the first tab
		            tabstrip.select(closedTabIndex - 1); // Select the previous tab
		        } else if (tabstrip.items().length > 0) { // If it's the first tab and there are still tabs remaining
		            tabstrip.select(0); // Select the first tab
		        }
		    }
		
		    e.preventDefault();  // To prevent tab selection
		
		    setTimeout(function() {
		        updateToolbarState();
		    }, 0);
		});

    });
    
    function updateToolbarState() {
	    // Assuming the TabStrip is initialized on an element with the ID "tabs":
	    var tabStrip = $("#tabstrip").data("kendoTabStrip");
	    var toolbar = $("#filesToolbar").data("kendoToolBar");

	    // Check if any tab is selected
	    var selectedTab = tabStrip.select();
	    if (selectedTab.length === 0) {
		    // Disable all
	        toolbar.enable("#saveButton", false);
	        toolbar.enable("#checkitButton", false);
	        toolbar.enable("#formatButton", false);
	        return;
	    }

	    // Get the content of the selected tab
	    var activeTabContent = tabStrip.contentElement(selectedTab.index());
	
	    // Get the CodeMirror instance from the active tab
	    var codeMirrorElement = activeTabContent.querySelector(".CodeMirror");
	    var errorElement = activeTabContent.querySelector('.errorMessage');

		// If we have an error, or no codeMirror element, then we will disable the UI buttons.
		if (null !== errorElement || null === codeMirrorElement) {
	        toolbar.enable("#saveButton", false);
	        toolbar.enable("#checkitButton", false);
	        toolbar.enable("#formatButton", false);
	    } else {
	        toolbar.enable("#saveButton", true);
	        toolbar.enable("#checkitButton", true);
	        toolbar.enable("#formatButton", true);
	    }
    }
    
    function saveFile() {
	    var tabStrip = $("#tabstrip").data("kendoTabStrip");

	    // Check if any tab is selected
	    var selectedTab = tabStrip.select();
	    if (selectedTab.length === 0) {
	        alert("No tab is selected.");
	        return;
	    }
	
	    // Get the content of the selected tab
	    var activeTabContent = tabStrip.contentElement(selectedTab.index());
	
	    // Get the CodeMirror instance from the active tab
	    var codeMirrorElement = activeTabContent.querySelector(".CodeMirror");

	    if (!codeMirrorElement) {
	        alert("No editor is found in the selected tab.");
	        return;
	    }
	    
	    var editor = codeMirrorElement.CodeMirror;
	    
	    // Get code from the current CodeMirror instance
        var fileContents = editor.getValue();
        var targetFile = selectedTab.attr('file-id');

        $.ajax({
            cache: false,
            url: 'writefile.php?' + "&_=" + new Date().getTime(),
            type: 'POST',
            data: {
                targetFile: targetFile,
                fileContents: fileContents
            },
            success: function(response) {
		        var data;
		
		        // Try to parse the response; if it fails, use the response as it is
		        try {
		            data = JSON.parse(response);
		        } catch (e) {
		            data = response;
		        }

		        var notification = createNotification();
				if(data.success) {
		            editor.setValue(data.contents);
		            editor.initialValue = editor.getValue();

	                notification.show({
	                    kendoTitle: "Save Successful",
	                    description: "File has been saved successfully."
	                }, "info");

					var titleElement = selectedTab.find(".tab-title");
					
					// Clear modified
					titleElement.removeClass('tab-modified');
	            }
	            else {
	                notification.show({
	                    kendoTitle: "Save Failed",
	                    description: "An error occurred while saving your data."
	                }, "error");
	            }
            },
            error: function(xhr, status, error) {
	            var notification = createNotification();
                notification.show({
                    kendoTitle: "Save Failed",
                    description: "An error occurred while saving your data."
                }, "error");
            }
        });
    }


	function runCheckit() {
	    // Get the TabStrip object
	    var tabStrip = $("#tabstrip").data("kendoTabStrip");

	    // Check if any tab is selected
	    var selectedTab = tabStrip.select();
	    if (selectedTab.length === 0) {
	        alert("No tab is selected.");
	        return;
	    }

        var targetFile = selectedTab.attr('file-id');

	    // Get the content of the selected tab
	    var activeTabContent = tabStrip.contentElement(selectedTab.index());

	    // Get the CodeMirror instance from the active tab
	    var codeMirrorElement = activeTabContent.querySelector(".CodeMirror");

	    if (!codeMirrorElement) {
	        alert("No editor is found in the selected tab.");
	        return;
	    }

		var titleElement = selectedTab.find(".tab-title");
		var modified = titleElement.hasClass('tab-modified');

		if(modified) {
			alert('You must save the file before running checkit.');
			return;
		}

	    var editor = codeMirrorElement.CodeMirror;
	    
	    // Get code from the current CodeMirror instance
        var fileContents = editor.getValue();

        $.ajax({
            cache: false,
            url: 'checkfile.php?' + "&_=" + new Date().getTime(),
            type: 'POST',
            data: {
                targetFile: targetFile
            },
            success: function(response) {
		        var data;
		
		        // Try to parse the response; if it fails, use the response as it is
		        try {
		            data = JSON.parse(response);
		        } catch (e) {
		            data = response;
		        }
		        
		        // Show the response
		        showMessage('Checkit', data.message);
            },
            error: function(xhr, status, error) {
	            var notification = createNotification();
                notification.show({
                    kendoTitle: "Save Failed",
                    description: "An error occurred while saving your data."
                }, "error");
            }
        });
	} // End of runCheckit

	function formatCode() {
	    // Get the TabStrip object
	    var tabStrip = $("#tabstrip").data("kendoTabStrip");

	    // Check if any tab is selected
	    var selectedTab = tabStrip.select();
	    if (selectedTab.length === 0) {
	        alert("No tab is selected.");
	        return;
	    }

        var targetFile = selectedTab.attr('file-id');

	    // Get the content of the selected tab
	    var activeTabContent = tabStrip.contentElement(selectedTab.index());

	    // Get the CodeMirror instance from the active tab
	    var codeMirrorElement = activeTabContent.querySelector(".CodeMirror");

	    if (!codeMirrorElement) {
	        alert("No editor is found in the selected tab.");
	        return;
	    }
	    
	    var editor = codeMirrorElement.CodeMirror;
	    
	    // Get code from the current CodeMirror instance
        var fileContents = editor.getValue();

        $.ajax({
            cache: false,
            url: 'format.php?' + "&_=" + new Date().getTime(),
            type: 'POST',
            data: {
                targetFile: targetFile,
                fileContents: fileContents
            },
            success: function(response) {
		        var data;
		
		        // Try to parse the response; if it fails, use the response as it is
		        try {
		            data = JSON.parse(response);
		        } catch (e) {
		            data = response;
		        }

		        var notification = createNotification();
				if(data.success) {
		            editor.setValue(data.contents);
	                notification.show({
	                    kendoTitle: "Format successful",
	                    description: "File contents has been updated."
	                }, "info");
	            }
	            else {
	                notification.show({
	                    kendoTitle: "Format failed",
	                    description: "An error occurred while formatting your data."
	                }, "error");
	            }
            },
            error: function(xhr, status, error) {
	            var notification = createNotification();
                notification.show({
                    kendoTitle: "Save Failed",
                    description: "An error occurred while saving your data."
                }, "error");
            }
        });

	}
</script>
<script type="text/x-kendo-template" id="infoTemplate">
    <div class="info">
        <h3>#= kendoTitle #</h3>
        <p>#= description #</p>
    </div>
</script>

</body>

</html>
