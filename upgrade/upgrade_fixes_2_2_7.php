<?php
/**
 * Upgrades/Fixes for UPB version 2.2.7
 * 
 * These functions are included and called from upgrade_tools.php. The function
 * naming convention update<Module>_v<major>_<minor>_<revision>() is used in case
 * the subsequent version updates the same module and has a naming conflict.
 * 
 * @author Tim Hoeppner <timhoeppner@gmail.com>
 * @author Chris Kent <online@chris-kent.co.uk>
 */

include_once(dirname( __FILE__ )."/../includes/class/posts.class.php");

/**
 * This function kind of has blind success. It attempts to scan through the topics
 * and find any that have users monitoring it. Once found, makes a query to match
 * the email address with a user id. The user id is then put in place of the email
 * address.
 * 
 * This function should have no negative affect if run twice.
 * 
 * @return always returns bool true
 */
function updateMonitorTopics_v2_2_7()
{
	global $tdb;
	
	$success = true;
	
	$posts_tdb = new posts(DB_DIR."/", "posts.tdb");
	$fRecs = $tdb->listRec("forums", 1);
	
	if($fRecs !== false)
	{
		foreach ($fRecs as $fRec)
		{
		
			$posts_tdb->setFp("topics", $fRec["id"]."_topics");
			$posts_tdb->set_forum($fRec);
			
			$tRecs = $posts_tdb->listRec("topics", 1);
			
			if($tRecs !== false)
			{
				foreach ($tRecs as $tRec)
				{
					if ($tRec['monitor'] != "")
					{
						$id_array = array();
						$monitors = explode(",",$tRec['monitor']);
						//dump($monitors);
						
						if(!empty($monitors))
						{
							foreach ($monitors as $key => $monitor)
							{
								// Make sure we are not discarding a user id already fixed. I am
								// assuming if there is a '@' symbol in the field then it is an email
								// address otherwise it has already been fixed.
								if(strpos($monitor, '@') !== false)
								{
									$user_id = $tdb->basicQuery('users','email',$monitor);
									
									// If the query failed then we have to just move on because
									// it sounds like the user has already changed their email address
									// and there is nothing we can do.
									if($user_id !== false)
									{
										$id_array[] = $user_id[0]['id'];
									}
								}
								else
								{
									$id_array[] = $monitor;
								}
							}
						}
						
						$monitor_ids = implode(",",$id_array);
						$posts_tdb->edit("topics", $tRec["id"], array("monitor" => $monitor_ids));
					}
				}
			}
		
		}
	}
	
	return $success;
}

/**
 * Adds the fileupload_types configuration variable
 * 
 * @return bool true on success, false on failure
 */
function updateFileUploadTypes_v2_2_7()
{
	global $config_tdb;
	global $_CONFIG;
	
	$success = true;
	
	// Add the fileupload_types config variable if it doesn't already exist
	if( !isset($_CONFIG["fileupload_types"]) )
	{
		$result = $config_tdb->addVar('fileupload_types', '', 'config', 'text', 'text', '9', '4', 'File upload allowed types', 'List the allowable file extensions seperated by a comma');
		
		if( $result === FALSE )
		{
			$success = false;
		}
	}
	
	return $success;
}

/**
 * Adds the new required forum_id and topic_id to the upload table
 * 
 * @return bool true on success, false on failure
 */
function updateUploadDb_v2_2_7()
{
	global $tdb;
	
	$success = true;
	
	// Add the new fields
	$fid_result = $tdb->addField('uploads',array('forum_id', 'number', 7));
	$tid_result = $tdb->addField('uploads',array('topic_id', 'number', 7));
	
	if($fid_result == false || $tid_result == false)
	{
		$success = false;
	}
	
	return $success;
}

/**
 * Attempts to find the origin of an upload and populates the forum_id
 * and topic_id of the upload database
 * 
 * @return bool true on success, false on failure
 */
function populateUploadDbFileOrigin_v2_2_7()
{
	global $tdb;
	
	$success = true;
	
	$posts_tdb = new posts(DB_DIR."/", "posts.tdb");
	$fRecs = $tdb->listRec("forums", 1);
	
	foreach ($fRecs as $fRec)
	{
		$posts_tdb->setFp("topics", $fRec["id"]."_topics");
		$posts_tdb->setFp("posts", $fRec["id"]);
		
		$tRecs = $posts_tdb->listRec("topics", 1);
		//dump($tRecs);
		
		if($tRecs !== false)
		{
			foreach ($tRecs as $tRec)
			{
				$posts_tdb->set_topic(array($tRec));
				$posts_tdb->set_forum(array($fRec));
	
				ob_start();
				$pRecs = $posts_tdb->getPosts("posts");
				ob_end_clean();
				//echo "topicid:".$tRec["id"];
				//dump($pRecs);
				//$posts_tdb->varDump();
				
				if($pRecs !== false)
				{
					foreach ($pRecs as $key => $pRec)
					{
		
						if ($pRec['upload_id'] != "")
						{
							
							$uploadIds = explode(",", $pRec["upload_id"]);
							
							foreach($uploadIds as $uId)
							{
								$uploadRec = $tdb->get("uploads", $uId);
								
								if($uploadRec !== false)
								{
									$uploadRec["forum_id"] = $fRec["id"];
									$uploadRec["topic_id"] = $tRec["id"];
									
									//echo "modifying uploadId:$uId forum_id:".$fRec["id"]." topic_id:".$tRec["id"]."\n";
									
									$modify_result = $tdb->edit("uploads", $uId, $uploadRec);
									
									if($modify_result == false)
									{
										$succes = false;
									}
								}
							}
						}
					}
				}
			}
		}
	}
	
	return $success;
}

?>
