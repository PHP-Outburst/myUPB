<?php
require_once("./includes/upb.initialize.php");
require_once("./includes/api/usermanagement/authentication.class.php");

$auth = new UPB_Authentication($tdb);

$uploadRec = $tdb->get("uploads", $_GET["upload_id"]);

if($uploadRec !== false)
{
	if($auth->access("topic", 'r', $uploadRec[0]["forum_id"], $uploadRec[0]["topic_id"]) == true)
	{
		$upload = new Upload(DB_DIR, 0,$_CONFIG['fileupload_location']);
		$upload->getFile((int) $_GET["upload_id"]);
		
		// Update the download count
		$upload->edit("uploads", (int) $_GET["upload_id"], array("downloads" => ((int)$upload->file["downloads"] + 1)));
		
		$upload->dumpFile();
	}
	else 
	{
		MiscFunctions::exitPage("You do not have permission to download this file", true);
	}
}
else 
{
	MiscFunctions::exitPage("Upload does not exist", true);
}
?>