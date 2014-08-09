<?php

/**
 * IP Log Action Script
 *
 * @version $Id$
 * @copyright 2009
 */


require_once("./includes/upb.initialize.php");
$where = "<a href='admin.php'>Admin</a> ".$_CONFIG["where_sep"]." <a href='admin_iplog.php'>Ip Address Logs</a>";

// Make sure user is logged in, otherwise terminate
if (!isset($_COOKIE["user_env"]) || !isset($_COOKIE["uniquekey_env"]) || !isset($_COOKIE["power_env"]) || !isset($_COOKIE["id_env"]))
{
	require_once("./includes/header.php");
	MiscFunctions::exitPage("
	<div class='alert'><div class='alert_text'>
	<strong>Access Denied!</strong></div><div style='padding:4px;'>You are not logged in.</div></div>
	<meta http-equiv='refresh' content='2;URL=login.php?ref=admin_iplog.php'>");
	require_once("./includes/footer.php");
	exit;
}

// Make sure user has admin credentials
if (!$tdb->is_logged_in() || $_COOKIE["power_env"] < 3)
{
	require_once("./includes/header.php");
	MiscFunctions::exitPage("
	<div class='alert'><div class='alert_text'>
	<strong>Access Denied!</strong></div><div style='padding:4px;'>you are not authorized to be here.</div></div>");
	require_once("./includes/footer.php");
	exit;
}

switch($_GET['action'])
{
	case 'download':
		// User wants to download a copy of the IP Log - force a download operation
		header("Content-type: application/octet-stream");
		header('Content-Length: ' . filesize(DB_DIR.'/ip.log'));
		header("Content-disposition: attachment; filename=\"ip.log\"");
		readfile(DB_DIR.'/ip.log');
		break;

	case 'clear':
		// Fopen the logs and write no data in order to clear them
		$fp = fopen(DB_DIR.'/ip.log', 'w');
		fwrite($fp, "");
		fclose($fp);

		require_once("./includes/header.php");
		echo "
	<div class='alert_confirm'>
		<div class='alert_confirm_text'>
			<strong>Redirecting:</div><div style='padding:4px;'>
			Successfully cleared IP Log.
		</div>
	</div>";

		MiscFunctions::redirect("admin_iplog.php", 1);
		require_once("./includes/footer.php");
		break;

	default:
		require_once("./includes/header.php");
		MiscFunctions::exitPage("
	<div class='alert'><div class='alert_text'>
	<strong>No Action Specified!</strong></div><div style='padding:4px;'>An action must be specified to use this page</div></div>");
		require_once("./includes/footer.php");
		exit;
		break;
}
?>