<?php
/**
 * UPB_DatabaseBackup provides an easy to use interface for backing up and
 * restoring the UPB database.
 * 
 * Note: I am considering having these API's incorporate the authentication API
 * and perform authentication before performing actions, this would centralize
 * authentication checking.
 *
 * @author Tim Hoeppner <timhoeppner@gmail.com> (Design work and implementation)
 * @author ???
 */

// We need the DB_DIR
include_once(dirname( __FILE__ )."/../../../config.php");

if(!defined("DB_DIR"))
{
	die("The UPB_DatabaseBackup class cannot find the database directory and cannot function without it.");
}

class UPB_DatabaseBackup
{
	function UPB_DatabaseBackup()
	{
	}
	
	/**
	 * Using the code from admin_restore.php...
	 * 
	 * @param string* $filename[output] returns the name of the backup filename
	 * 
	 * @return bool true on success, false on failure
	 */
	function backup(&$filename = null)
	{
		$filename = 'upbdatabackup_v'.UPB_VERSION.'_'.date("m").'.'.date("d").'.'.date("Y").'.'.time().'.zip';
		$zip = new PclZip(DB_DIR.'/backup/'.$filename);
		
		// Generate a file list for our backup
		$dir = opendir(DB_DIR);
		$list = "";
		while (false !== ($file = readdir($dir))) {
			if (!is_dir(DB_DIR."/".$file)) $list .= ",".DB_DIR."/".$file;
		}
		$list = substr($list, 1);
		closedir($dir);
		
		$createReturn = $zip->create($list, PCLZIP_OPT_REMOVE_ALL_PATH);
		
		if($createReturn != 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function restore($backup)
	{
	}
	
	function listBackups()
	{
	}
	
	/**
	 * Displays the download link for a backup file
	 *
	 * @param string* $linkData - If this is not null then the link data will be
	 * 	dumped here instead of displayed.
	 *
	 * @return void
	 * 
	 * TODO: should verify the file actually exists
	 */
	function displayDownloadLink($backupFilename, &$linkData = null)
	{
		$msg = "<a href=\"admin_restore.php?action=download&file=".$backupFilename."\">".$backupFilename."</a>";
		
		if($linkData != null)
		{
			$linkData = $msg;
		}
		else
		{
			echo $msg;
		}
	}
}
?>