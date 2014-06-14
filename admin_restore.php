<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once("./includes/upb.initialize.php");
$where = "<a href='admin.php'>Admin</a> ".$_CONFIG["where_sep"]." <a href='admin_restore.php'>Backup and Restore Data</a>";
if (!(isset($_COOKIE["power_env"]) && isset($_COOKIE["user_env"]) && isset($_COOKIE["uniquekey_env"]) && isset($_COOKIE["id_env"]))) exitPage('
		<div class="alert"><div class="alert_text">
		<strong>Access Denied!</strong></div><div style="padding:4px;">you are not logged in</div></div>
		<meta http-equiv="refresh" content="2;URL=login.php?ref=admin.php">', true);
if (!$tdb->is_logged_in() || $_COOKIE["power_env"] < 3) exitPage('
		<div class="alert"><div class="alert_text">
		<strong>Access Denied!</strong></div><div style="padding:4px;">you are not authorized to be here.</div></div>', true);
if (!($_GET['action'] == 'download' && isset($_GET['file'])) && $_POST['verify'] != 'Cancel') {
	require_once('./includes/header.php');
	echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
			<tr>
				<th>Admin Panel Navigation</th>
			</tr>";
	echo "
			<tr>
				<td class='area_2' style='padding:20px;' valign='top'>";
	require_once("admin_navigation.php");
	echo "</td>
			</tr>";
	echoTableFooter(SKIN_DIR);
	echoTableHeading("Backup and Restore Data", $_CONFIG);
	echo "
			<tr>
				<th>Select an option below</th>
			</tr>";
	echo "
			<tr>
				<td class='area_2' style='text-align:center;padding:12px;line-height:20px;' valign='top'><span class='link_1'>";
	echo "
					<a href='admin_restore.php?action=backup'>Backup the current database</a><br />
					<a href='admin_restore.php?action=restore'>Restore a previous version of the current database</a><br />
					<a href='admin_restore.php?action=download'>Download a previously backedup version of the current database</a> (In other words: \"Export\")<br />
					<a href='admin_restore.php?action=import'>Import a foreign database</a><br />
					<a href='admin_restore.php?action=delete'>Delete all backups</a></span></td>
			</tr>";
	if (isset($_GET['action'])) echo '
			<tr>
				<td class="footer_3" colspan="2"><img src="./skins/default/images/spacer.gif" alt="" title="" /></td>
			</tr>
			<tr>
				<td class="area_1" style="text-align:center;padding:12px;line-height:20px;" valign="top">';
}
if ($_GET['action'] == 'backup') {
	// TODO: replace this code with the UPB_BackupDatabase::backup() call
	
	$filename = 'upbdatabackup_v'.UPB_VERSION.'_'.date("m").'.'.date("d").'.'.date("Y").'.'.time().'.zip';
	require_once('./includes/lib/pclzip.lib.php');
	$zip = new PclZip(DB_DIR.'/backup/'.$filename);
	//generate a file list for our backup
	$dir = opendir(DB_DIR);
	$list = "";
	while (false !== ($file = readdir($dir))) {
		if (!is_dir(DB_DIR."/".$file)) $list .= ",".DB_DIR."/".$file;
	}
	$list = substr($list, 1);
	closedir($dir);
	$zip->create($list, PCLZIP_OPT_REMOVE_ALL_PATH);
	echo 'Successfully backed up the current database to <strong>'.DB_DIR.'/backup/'.$filename.'</strong>.  Would you like to <a href="admin_restore.php?action=download&file='.$filename.'">download it?</a>';
} elseif($_GET['action'] == 'restore') {
	if (!isset($_GET['file'])) {
		echo '<p>Which backedup database would you like to restore?<br />';
		$any_files = false;
		if (FALSE !== ($handle = opendir(DB_DIR.'/backup'))) {
			while (FALSE !== ($filename = readdir($handle))) {
				if ($filename != '.' && $filename != '..') {
					$any_files = true;
					echo '<br /><a href="admin_restore.php?action=restore&file='.$filename.'">'.$filename.'</a>';
				}
			}
			if ($any_files == FALSE) echo 'No backup files exist.';
			echo '</p>';
		}
		else echo 'Unable to retrieve backup list: Could not open the directory.';
	} elseif($_GET['file'] != '') {
		if (!isset($_POST['verify'])) ok_cancel('admin_restore.php?action=restore&file='.$_GET['file'], 'Backup the current database before proceeding.  CHMOD upb\'s root directory to 0777 before proceeding.  Are you sure you wish to abandon the current database for an earlier version?');
		elseif($_POST['verify'] == 'Cancel') redirect('admin_restore.php', 0);
		elseif($_POST['verify'] == 'Ok') {
			if (!is_writable('./')) exitPage('Cannot restore database: upb\'s root directory is not writable. (CHMOD to 0777 before preceeding)');
			$new_db_dir = './'.uniqid('data_', true);
			mkdir($new_db_dir, 0770);
			mkdir($new_db_dir."/backup", 0770);
			//copy our other backups into our new folder
			$dir = opendir(DB_DIR."/backup");
			$list = "";
			while (false !== ($file = readdir($dir))) {
				if (!is_dir($file)) {
					copy(DB_DIR."/backup/".$file, $new_db_dir."/backup/".$file);
				}
			}
			$list = substr($list, 1);
			closedir($dir);
			/*require_once 'tar.class.php';
				//we have to copy the tar file to our new db folder because of the limitations of the class
				copy(DB_DIR.'/backup/'.$_GET['file'], $new_db_dir."/".$_GET["file"]);
				$current_dir = getcwd();
				$tar = new tar();
				$tar->new_tar("./", $_GET['file']);
				$tar->current_dir($current_dir);
				$tar->extract_files($new_db_dir);
				chdir($current_dir);*/
			require_once('./includes/lib/pclzip.lib.php');
			$zip = new PclZip(DB_DIR.'/backup/'.$_GET['file']);
			$zip->extract(PCLZIP_OPT_PATH, $new_db_dir);
			$success = false;
			$config_file = file('./config.php');
			//print_r($config_file);
			for($i = 0; $i < count($config_file); $i++) {
				//$config_file[$i] = trim($config_file[$i]);
				//echo "line #$i: ".$config_file[$i]."<br />";
				if (empty($config_file[$i])) unset($config_file[$i]);
				if (strchr($config_file[$i], "DB_DIR")) {
					$success = true;
					$config_file[$i] = "define('DB_DIR', '".$new_db_dir."', true);\n";
					break;
				}
			}
			$config_file = implode("", $config_file);
			$f = fopen('config.php', 'w');
			fwrite($f, $config_file);
			fclose($f);
			unset($config_file);
			if ($success) {
				echo 'Successfully restored a backedup database.  Please restore your default permissions of the upb\' root directory.';
			} else {
				echo 'Error editing config.php.  Please seek support at myupb.com';
				rmdir($new_db_dir);
			}
		}
		else echo 'Cannot process request: Invalid verify';
	}
	else echo 'Cannot process request: Invalid GET file';
} elseif($_GET['action'] == 'download') {
	if (!isset($_GET['file'])) {
		echo '<p>Download:</p><p>';
		$any_files = false;
		if (FALSE !== ($handle = opendir(DB_DIR.'/backup/'))) {
			while (FALSE !== ($filename = readdir($handle))) {
				if ($filename != '.' && $filename != '..') {
					$any_files = true;
					echo '<a href="admin_restore.php?action=download&file='.$filename.'">'.$filename.'</a><br />';
				}
			}
			if ($any_files == FALSE) echo 'No backup files exist';
			echo '</p>';
		}
		else echo 'Unable to retrieve backup list: Could not open the directory.';
	} elseif($_GET['file'] != '' and file_exists(DB_DIR.'/backup/'.$_GET['file']) && check_file(DB_DIR.'/backup/'.$_GET['file'])) {
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Cache-Control: private", false);
		header("Content-Type: application/x-compressed");
		header("Content-Disposition: attachment; filename=\"".basename($_GET['file'])."\";");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".filesize(DB_DIR.'/backup/'.$_GET['file']));
		readfile(DB_DIR.'/backup/'.$_GET['file']);
		exit;
	}
	else echo 'Cannot process request: Invalid GET file.';
} elseif($_GET['action'] == 'import') {
	if (!isset($_GET['file'])) {
		echo '<p>Available backup files to be imported:</p><p>';
		$any_files = false;
		if (FALSE !== ($handle = opendir('./backup'))) {
			while (FALSE !== ($filename = readdir($handle))) {
				if ($filename != '.' && $filename != '..') {
					if (substr($filename, 0, 13) == 'upbdatabackup') {
						$any_files = true;
						echo '<a href="admin_restore.php?action=import&file='.$filename.'">'.$filename.'</a><br />';
					}
				}
			}
			if ($any_files == FALSE) echo 'No available backup files to be imported';
			echo '</p>';
		}
		else echo 'Unable to retrieve backup list: Could not open the directory.';
		echo '<p><i>Note: to import a backedup database, put the file in the backup folder in the upb root directory.  The "backup" folder must be readable and writable (CHMOD to 0777).</p>';
	} elseif($_GET['file'] != '') {
		if (!isset($_POST['verify']) && check_file('./backup/'.$_GET['file'])) ok_cancel('admin_restore.php?action=import&file='.$_GET['file'], 'Are you sure you wish to import <strong>'.$_GET['file'].'</strong>?');
		elseif($_POST['verify'] == 'Cancel') redirect('admin_restore.php', 0);
		elseif($_POST['verify'] == 'Ok') {
			if(!preg_match("/../", $_GET['file']))
			{
				if (rename('./backup/'.$_GET['file'], DB_DIR.'/backup/'.$_GET['file']))
				echo 'Successfully imported <strong>'.$_GET['file'].'</strong><br>
        			Note this backed up database was not "restored" or loaded.<br>To restore this backup, click <a href="admin_restore.php?action=restore&file='.$_GET['file'].'">here</a>';
				else echo 'Importing failed.';
			}
			else 
			{
				echo "Unable to process request: Invalid path";
			}
		}
		else echo 'Unable to process request: Invalid verify';
	}
	else echo 'Unable to process request: Invalid GET file';
} elseif($_GET['action'] == 'delete') {
	if (!isset($_POST['verify'])) ok_cancel('admin_restore.php?action=delete', 'Are you sure you want to delete all backups?');
	elseif($_POST['verify'] == 'Cancel') redirect('admin_restore.php', 0);
	elseif($_POST['verify'] == 'Ok') {
		if (FALSE !== ($handle = opendir(DB_DIR.'/backup'))) {
			while (FALSE !== ($filename = readdir($handle))) {
				if ($filename != '.' && $filename != '..') {
					unlink(DB_DIR.'/backup/'.$filename);
				}
			}
		}
		else echo 'Unable to delete backups: Could not open the directory.';
	}
	else echo 'Cannot process request: Invalid verify';
}
// else echo 'Cannot process request: Invalid action.';
echo "</td>
			</tr>";
echoTableFooter(SKIN_DIR);
require_once('./includes/footer.php');
?>
