<?php
/**
 * Upgrade tools script contains a set of functions used by the upgrader. This
 * file is seperate from the upgrader in order to keep the code nice and clean.
 * By breaking up the upgrade process in small chunks we can give the end-user
 * very detailed status and progress information as we call all of the AJAX
 * functions.
 *
 * This file is meant to be included in from the upgrade.php script.
 *
 * @author Tim Hoeppner <timhoeppner@gmail.com>
 */

// TODO: Move each validate function to a seperate files since each one could
// get fairly large.

// Grab all the hot fixes
include_once(dirname( __FILE__ )."/upgrade_fixes_2_2_7.php");

function successResponse($msg)
{
	return "<span style=\"color: green\">".$msg."</span>";
}

function failureResponse($msg)
{
	$outputMsg = "<span style=\"color: red\">".$msg."</span>";
	$outputMsg .= "<br /><br />\n\nPlease consult the <a href=\"http://forum.myupb.com\">MyUPB team</a> for further instruction. ";
	$outputMsg .= "Copy and paste the transcript above into another forum post so the team can aid you in the most efficient manner.";
	
	return $outputMsg;
}

function fixedResponse($msg)
{
	$outputMsg = "<span style=\"color: #d2691e\">".$msg."</span>";
	
	return $outputMsg;
}

/**
 * Uses the user API to perform a backup before proceeding
 */
function AJAX_backupDatabase($go = "no")
{
	$response = new xajaxResponse;

	if($go == "no")
	{
		$response->assign("start", "disabled", "true");
		$response->append("progress", "innerHTML", "Performing backup...");
		$response->call("xajax_AJAX_backupDatabase", "yes");
	}
	else 
	{
		$filename = "";
		$backup = new UPB_DatabaseBackup;
		
		if($backup->backup($filename) == true)
		{
			$downloadLink = "No Link";
			$backup->displayDownloadLink($filename, $downloadLink);
			$response->append("progress", "innerHTML", successResponse("Success")."&nbsp;&nbsp;&nbsp;[". $downloadLink ."]");
			$response->call("xajax_AJAX_validateRootConfig");
		}
		else 
		{
			$response->append("progress", "innerHTML", failureResponse("Failure", true));
		}
		
		$response->append("progress", "innerHTML", "<br />");
	}

	return $response;
}

/**
 * Checks over the config.php configuration file and verifies everything is in order
 */
function AJAX_validateRootConfig($go = "no")
{
	$response = new xajaxResponse;
	
	if($go == "no")
	{
		$response->append("progress", "innerHTML", "Validating root config.php...<br />");
		$response->call("xajax_AJAX_validateRootConfig", "yes");
	}
	else 
	{
		// UPB requires the root config.php contain 3 constants; UPB_VERSION,
		// DB_DIR, and ADMIN_EMAIL. Make sure they all exist and are valid.
		// Since we have already included config.php we can just use PHP's defined()
		// function to verify their existance.
		
		$failure = false;
		
		if(defined("UPB_VERSION"))
		{
			$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Found <span style=\"font-style: italic\">UPB_VERSION</span><br />");
		}
		else
		{
			// TODO: add constant to config.php
			//$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Missing <span style=\"font-style: italic\">UPB_VERSION</span>".fixedResponse("Fixed")."<br />");
			$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Missing <span style=\"font-style: italic\">UPB_VERSION</span>... ".fixedResponse("Failure")."<br />");
			$failure = true;
		}
		
		// No validation is needed here since the backup would have failed if this was invalid
		if(defined("DB_DIR"))
		{
			$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Found <span style=\"font-style: italic\">DB_DIR</span><br />");
		}
		else
		{
			// TODO: add constant to config.php
			//$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Missing <span style=\"font-style: italic\">DB_DIR</span>".fixedResponse("Fixed")."<br />");
			$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Missing <span style=\"font-style: italic\">DB_DIR</span>... ".fixedResponse("Failure")."<br />");
			$failure = true;
		}
		
		if(defined("ADMIN_EMAIL"))
		{
			$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Found <span style=\"font-style: italic\">ADMIN_EMAIL</span><br />");
		}
		else
		{
			// TODO: add constant to config.php
			///$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Missing <span style=\"font-style: italic\">ADMIN_EMAIL</span>".fixedResponse("Fixed")."<br />");
			$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Missing <span style=\"font-style: italic\">ADMIN_EMAIL</span>... ".fixedResponse("Failure")."<br />");
			$failure = true;
		}
		
		$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;config.php validation result: ");
		
		if($failure == false)
		{
			$response->append("progress", "innerHTML", successResponse("Success")."<br />");
			$response->call("xajax_AJAX_validateDbConfig");
		}
		else 
		{
			$response->append("progress", "innerHTML", failureResponse("Failure")."<br />");
		}
	}

	return $response;
}

/**
 * Checks to make sure all the correct table fields are present and checks to make
 * sure all expected table rows exist.
 */
function AJAX_validateDbConfig($go = "no")
{
	global $_CONFIG;
	
	$response = new xajaxResponse;
	
	if($go == "no")
	{
		$response->append("progress", "innerHTML", "Validating database configuration...<br />");
		$response->call("xajax_AJAX_validateDbConfig", "yes");
	}
	else 
	{
		$failure = false;
		
		// TODO: Fully validate database configuration, for now we will just make sure the fixes
		// have been installed for 2.2.7
		
		// Check if the necessary configuration variables exist
		if(!isset($_CONFIG["fileupload_types"]))
		{
			$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Missing <span style=\"font-style: italic\">fileupload_types</span>...");
			
			$result = updateFileUploadTypes_v2_2_7();
			
			if($result == true)
			{
				$response->append("progress", "innerHTML", fixedResponse("Fixed").". Configure the valid upload types via Admin Panel -> Manage Settings<br />");
			}
			else
			{
				$response->append("progress", "innerHTML", fixedResponse("Failure")."<br />");
				$failure = true;
			}
		}
		
		$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Database configuation validation result: ");
		
		if($failure == false)
		{
			$response->append("progress", "innerHTML", successResponse("Success")."<br />");
			$response->call("xajax_AJAX_validateDbCategories");
		}
		else 
		{
			$response->append("progress", "innerHTML", failureResponse("Failure")."<br />");
		}
	}

	return $response;
}

/**
 * Checks to make sure all table fields are present in the categories table.
 */
function AJAX_validateDbCategories($go = "no")
{
	$response = new xajaxResponse;
	
	// TODO: validate category database
	
	if($go == "no")
	{
		$response->append("progress", "innerHTML", "Validating forum categories table... ");
		//$response->call("xajax_AJAX_validateDbCategories", "yes");
		
		$response->append("progress", "innerHTML", successResponse("Success")."<br />");
		$response->call("xajax_AJAX_validateDbForums");
	}
	else 
	{
	}

	return $response;
}

/**
 * Checks to make sure all table fields are present in the forums table.
 */
function AJAX_validateDbForums($go = "no")
{
	$response = new xajaxResponse;
	
	// TODO: validate forum database
	
	if($go == "no")
	{
		$response->append("progress", "innerHTML", "Validating forums table... ");
		//$response->call("xajax_AJAX_validateDbForums", "yes");
		
		$response->append("progress", "innerHTML", successResponse("Success")."<br />");
		$response->call("xajax_AJAX_validateDbTopics");
	}
	else 
	{
	}

	return $response;
}

/**
 * Checks to make sure all table fields are present in the topics table.
 */
function AJAX_validateDbTopics($go = "no")
{
	$response = new xajaxResponse;
	
	// TODO: validate topic database
	
	if($go == "no")
	{
		$response->append("progress", "innerHTML", "Validating forum topics table... ");
		//$response->call("xajax_AJAX_validateDbTopics", "yes");
		
		$response->append("progress", "innerHTML", successResponse("Success")."<br />");
		$response->call("xajax_AJAX_validateDbPosts");
	}
	else 
	{
	}

	return $response;
}

/**
 * Checks to make sure all table fields are present in the posts table.
 */
function AJAX_validateDbPosts($go = "no")
{
	$response = new xajaxResponse;
	
	// TODO: validate posts database
	
	if($go == "no")
	{
		$response->append("progress", "innerHTML", "Validating forum posts table... ");
		//$response->call("xajax_AJAX_validateDbPosts", "yes");
		
		$response->append("progress", "innerHTML", successResponse("Success")."<br />");
		$response->call("xajax_AJAX_validateDbUploads");
	}
	else 
	{
	}

	return $response;
}

/**
 * Checks to make sure all the upload database table fields are in order
 */
function AJAX_validateDbUploads($go = "no")
{
	global $tdb;
	
	$response = new xajaxResponse;
	
	if($go == "no")
	{
		$response->append("progress", "innerHTML", "Validating forum uploads table...<br />");
		$response->call("xajax_AJAX_validateDbUploads", "yes");
	}
	else 
	{
		$failure = false;
		
		// TODO: validate the entire upload table
		
		// Check if the forum_id and topic_id fields exist in the upload database
		$uploadFieldList = $tdb->getFieldList("uploads");
		$foundTopicId = false;
		$foundForumId = false;
		
		if($uploadFieldList !== false)
		{
			foreach($uploadFieldList as $field)
			{
				if($field["fName"] == "forum_id")
				{
					$foundForumId = true;
				}
				elseif($field["fName"] == "topic_id")
				{
					$foundTopicId = true;
				}
			}
			
			// Add the new fields if either of them didn't exist
			if($foundForumId == false || $foundTopicId == false)
			{
				$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Missing forum_id or topic_id fields... ");
				
				$result = updateUploadDb_v2_2_7();
				
				if($result == true)
				{
					$response->append("progress", "innerHTML", fixedResponse("Fixed")."<br />");
					$foundForumId = true;
					$foundTopicId = true;
				}
				else
				{
					$response->append("progress", "innerHTML", fixedResponse("Failure")."<br />");
					$failure = true;
				}
			}
		}
		
		if($foundForumId == true && $foundTopicId == true)
		{
			// Check if we have populated the topic_id and forum_id fields for the upload database
			$uploadList = $tdb->listRec("uploads", 1, 1);
			
			if($uploadList !== false)
			{
				if($uploadList[0]["topic_id"] == "" || $uploadList[0]["forum_id"] == "")
				{
					$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Populating forum_id and topic_id fields... ");
						
					// Populate the forum_id and topic_id fields
					$populateResult = populateUploadDbFileOrigin_v2_2_7();
					
					if($populateResult == true)
					{
						$response->append("progress", "innerHTML", fixedResponse("Done")."<br />");
					}
					else 
					{
						$response->append("progress", "innerHTML", fixedResponse("Failure")."<br />");
						$failure = true;
					}
				}
			}
		}
		
		$response->append("progress", "innerHTML", "&nbsp;&nbsp;&nbsp;Database upload validation result: ");
		
		if($failure == false)
		{
			$response->append("progress", "innerHTML", successResponse("Success")."<br />");
			$response->call("xajax_AJAX_updateUPBVersion");
		}
		else 
		{
			$response->append("progress", "innerHTML", failureResponse("Failure")."<br />");
		}
	}

	return $response;
}

/**
 * Updates config.php with the new version number
 */
function AJAX_updateUPBVersion($go = "no")
{
	$response = new xajaxResponse;
	
	if($go == "no")
	{
		$response->append("progress", "innerHTML", "Updating UPB Version number to 2.2.7...");
		$response->call("xajax_AJAX_updateUPBVersion", "yes");
	}
	else 
	{
		$failure = false;
		
		// Find the line that contains the UPB version
		$f = fopen(dirname( __FILE__ )."/../config.php", "r+");
		
		if($f !== false)
		{
			$foundVersionLine = false;
			$versionLine = "";
			$buffer = "";
			
			while( ($line = fgets($f, 1024)) !== false )
			{
				if(strpos($line, UPB_VERSION) !== false)
				{
					$foundVersionLine = true;
					$buffer .= str_replace(UPB_VERSION, UPB_NEW_VERSION, $line);
				}
				else
				{
					$buffer .= $line;
				}
			}
			
			if($foundVersionLine == true)
			{
				fseek($f, 0);
				ftruncate($f, 0);
				fwrite($f, $buffer);
			}
			else
			{
				$failure = true;
			}
		}
		else
		{
			$failure = true;
		}
		
		fclose($f);
		
		if($failure == false)
		{
			$response->append("progress", "innerHTML", successResponse("Success")."<br />");
			$response->append("progress", "innerHTML", "<br />Upgrade is complete! Enjoy UPB v".UPB_NEW_VERSION);
		}
		else 
		{
			$response->append("progress", "innerHTML", failureResponse("Failure")."<br />");
		}
	}

	return $response;
}
?>
