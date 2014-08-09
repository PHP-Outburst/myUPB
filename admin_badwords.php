<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once("./includes/upb.initialize.php");
$words = array();
$where = "<a href='admin.php'>Admin</a> ".$_CONFIG["where_sep"]." <a href='admin_badwords.php'>Manage Filtered Language</a>";
require_once('./includes/header.php');
if (!(isset($_COOKIE["user_env"]) && isset($_COOKIE["uniquekey_env"]) && isset($_COOKIE["power_env"]) && isset($_COOKIE["id_env"]))) {
	echo "
			<div class='alert'><div class='alert_text'>
			<strong>Access Denied!</strong></div><div style='padding:4px;'>you are not logged in!</div></div>";
	MiscFunctions::redirect("login.php?ref=admin_badwords.php", 2);
}
if (!$tdb->is_logged_in() || $_COOKIE["power_env"] < 3) MiscFunctions::exitPage("
		<div class='alert'><div class='alert_text'>
		<strong>Access Denied!</strong></div><div style='padding:4px;'>you are not authorized to be here.</div></div>");
if ($_GET["action"] == "delete" && $_GET["word"] != "") {
	if ($_POST["verify"] == "Ok") {
		echo "
				<div class='alert_confirm'>
				<div class='alert_confirm_text'>
				<strong>Redirecting:</div><div style='padding:4px;'>
				deleting bad word...
				";
		$words = explode(",", $_CONFIG['banned_words']);
		if (($index = array_search($_GET["word"], $words)) !== FALSE) {
			unset($words[$index]);
			$words = implode(',', $words);
			$config_tdb->editVars('config', array('banned_words' => $words));
			echo "Done!</div>
    				</div>";
			MiscFunctions::redirect("admin_badwords.php", 1);
		} else print 'Could not delete "'.$_GET['word'].'"</div></div>';
	} elseif($_POST["verify"] == "Cancel") MiscFunctions::redirect("admin_badwords.php", 1);
	else MiscFunctions::ok_cancel("admin_badwords.php?action=delete&word=".$_GET["word"], "Are you sure you want to remove the <b>".$_GET["word"]."</b> from the filter list?");
} elseif($_GET["action"] == "addnew") {
	if ($_POST["newword"] != "") {
		echo "
				<div class='alert_confirm'>
				<div class='alert_confirm_text'>
				<strong>Redirecting:</div><div style='padding:4px;'>
				adding new word...";
		$words = $_CONFIG['banned_words'] . ((strlen($_CONFIG['banned_words']) == 0) ? '' : ',') . htmlentities(stripslashes(trim($_POST['newword'])));
		$config_tdb->editVars('config', array('banned_words' => $words));
		echo "Done!</div>
				</div>";
		MiscFunctions::redirect("admin_badwords.php", 1);
	} else {
		echo "<form action='admin_badwords.php?action=addnew' method=POST>";
		MiscFunctions::echoTableHeading("Adding a filtered word", $_CONFIG);
		echo "
			<tr>
				<th colspan='2'>Add the word you wish to be censored below</th>
			</tr>
			<tr>
				<td class='area_1' style='width:35%;padding:12px;'><strong>New filtered word</strong></td>
				<td class='area_2'><input type='text' name='newword' size='20'></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type='submit' value='Add'></td>
			</tr>";
		MiscFunctions::echoTableFooter(SKIN_DIR);
		echo "</form>";
	}
} else {

	$words = explode(",", $_CONFIG['banned_words']);
	MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
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
	MiscFunctions::echoTableFooter(SKIN_DIR);
	echo "
			<div id='tabstyle_2'>
			<ul>
			<li><a href='admin_badwords.php?action=addnew' title='Add a new word?'><span>Add a new word?</span></a></li>
			</ul>
			</div>
			<div style='clear:both;'></div>";
	MiscFunctions::echoTableHeading("Filtered Words", $_CONFIG);
	echo "
			<tr>
				<th style='width:65%;'>Word</th>
				<th>Option</th>
			</tr>";
	if (count($words) == 0) {
		echo "
			<tr>
				<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='2'>No words found</td>
			</tr>";
	} else {
		for($i = 0; $i < count($words); $i++) {
			echo "
			<tr>
				<td class='area_1' style='padding:8px;'><strong>$words[$i]</strong></td>
				<td class='area_2' style='padding:8px;'><a href='admin_badwords.php?action=delete&word=$words[$i]'>Delete</a></td>
			</tr>";
		}
	}
	MiscFunctions::echoTableFooter(SKIN_DIR);
}
require_once("./includes/footer.php");
?>
