<?php
// Private Messaging System
// Add on to Ultimate PHP Board V2.0
// Original PM Version (before _MANUAL_ upgrades): 2.0
// Addon Created by J. Moore aka Rebles
// Using textdb Version: 4.2.3
require_once('./includes/upb.initialize.php');
if (!isset($_COOKIE["user_env"]) || !isset($_COOKIE["uniquekey_env"]) || !isset($_COOKIE["power_env"]) || !isset($_COOKIE["id_env"])) MiscFunctions::exitPage('You are not logged in.', true);
if (!$tdb->is_logged_in()) MiscFunctions::exitPage('Invalid Login!', true);
$PrivMsg = new TdbFunctions(DB_DIR."/", "privmsg.tdb");
$PrivMsg->setFp("CuBox", ceil($_COOKIE["id_env"]/120));
if ($_GET["action"] == "add") {
	$where = "<a href='pmsystem.php'>Private Msg</a> ".$_CONFIG["where_sep"]." Manage Blocked Users";
	$echo = "<br />";
	$void = "";
	if (!isset($_GET["user_id"]) && isset($_GET["id"])) {
		$rec = $PrivMsg->get("CuBox", $_GET["id"]);
		$_GET["user_id"] = $rec[0]["from"];
	}
	if ($_GET["user_id"] == "" || !isset($_GET["user_id"])) MiscFunctions::exitPage("You must select a name!", true);
	$user = $tdb->get("users", $_GET["user_id"]);
	if ($user[0]["level"] != 1) {
		$void .= '1';
		$echo .= "You cannot Block ".$user[0]["user_name"].", He/She is an Administrator/Moderator<br />";
	}
	$blockedIds = PrivateMessaging::getUsersPMBlockedList($_COOKIE["id_env"]);
	if (!empty($blockedIds)) {
		if (true === (in_array($user[0]["id"], $blockedIds))) {
			$void .= '1';
			$echo .= $user[0]["user_name"]." is already blocked<br />";
		}
	} else {
		if ($void == "") {
			$blockedIds = array();
			iPrivateMessaging::addUsersPMBlockedList($_COOKIE["id_env"]);
		}
	}
	if ($void == "") {
		if ($blockedIds[0] == "") $new = $user[0]["id"];
		else $new = implode (",", $blockedIds).",".$user[0]["id"];
		//print_r("<br />".$new);
		if (!PrivateMessaging::editUsersPMBlockedList($_COOKIE["id_env"], $new)) {
			MiscFunctions::exitPage("<strong>Error</strong>:  An unexpected error occured when editing PMBlockedList file, <strong>USER NOT FOUND</strong><br />", true);
		}
		$echo = "Successfully Blocked <strong>".$user[0]["user_name"]."</strong>!<br />";
		$action = 'done';
		if ($after == "done") {
			MiscFunctions::exitPage($echo, true);
		} elseif($action == "close" || $after == "close") {
			irequire_once('./includes/header.php');
			echo $echo;
			require_once('./includes/footer.php');
			MiscFunctions::redirect("viewpm.php?action=close", "3");
		} elseif($ref != "") {
			require_once('./includes/header.php');
			echo $echo;
			require_once('./includes/footer.php');
			MiscFunctions::redirect($ref."?section=$section&id=".$_GET["id"], "2");
		} else {
			$action = "";
		}
	} else {
		MiscFunctions::exitPage(strlen($void)." error(s) occured:<br />".$echo, true);
	}
	unset($rec, $user, $ck, $k, $void, $new, $f, $i);
} elseif($_POST["action"] == "unblock") {
	$blockedIds = PrivateMessaging::getUsersPMBlockedList($_COOKIE["id_env"]);
	MiscFunctions::deleteWhiteIndex($blockedIds);
	$keep = array();
	$count = count($blockedIds);
	$num = 0;
	for($i = 0; $i < $count; $i++) {
		if (!isset($_POST[$blockedIds[$i]])) {
			$keep[] = $blockedIds[$i];
		} elseif(isset($_POST[$blockedIds[$i]])) {
			$num++;
		}
	}
	if ($keep[0] == "") $new = "";
	elseif($keep[1] == "") $new = $keep[0];
	else $new = implode(",", $keep);
	$blockedIds = $keep;
	PrivateMessaging::editUsersPMBlockedList($_COOKIE["id_env"], $new);
	if ($num != 0) {
		$echo = "Successfully unblocked <strong>$num</strong> user";
		if ($num > 1) $echo .= "s";
		$echo .= "";
	} else {
		$echo .= "<strong>No users were unblocked!</strong>";
	}
	$action = "";
} elseif($_GET["action"] == "adduser") {
	$where = "<a href='pmsystem.php'>Private Msg</a> ".$_CONFIG["where_sep"]." <a href='pmblocklist.php'>Manage Blocked Users</a> ".$_CONFIG["where_sep"]." Add User";
	require_once('./includes/header.php');
	//PM using blocking commented out
	echo "
			<div id='tabstyle_2'>
				<ul>
					<li><a href='pmsystem.php?section=inbox'><span>View Inbox</span></a></li>
					<li><a href='pmsystem.php?section=outbox'><span>View Outbox</span></a></li>
					<li><a href='pmblocklist.php'><span>Manage Blocked Users</span></a></li>
					<!--<li><a href='pmblocklist.php?action=adduser'><span>Block a User</span></a></li>-->
				</ul>
			</div>
			<div style='clear:both;'></div>";
	MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
				<tr>
					<td colspan='2' bgcolor='white'>";
	echo $error;
	$blockedIds = explode(",", PrivateMessaging::getUsersPMBlockedList($_COOKIE["id_env"]));
	$select = $tdb->createUserSelectFormObject("user_id", true, true, true, "", $blockedIds);

	echo "
				<form action='".$PHP_SELF."' method='GET' onSubmit='submitonce(this)' enctype='multipart/form-data'>
					<input type='hidden' name='action' value='add'>
					<br />
					$select
					<input type='submit' value='Add User'>
				</form>
				<br />
				You are not allowed to block Administrators/Moderators";
					echo "</td>";
					MiscFunctions::echoTableFooter(SKIN_DIR);
}
if ($_GET["action"] == "" || 1==1) {
	$where = "<a href='pmsystem.php'>Private Msg</a> ".$_CONFIG["where_sep"]." Manage Blocked Users";
	require_once('./includes/header.php');
	if (!isset($echo)) $echo = "<br />";
	echo $echo;
	echo "
			<div id='tabstyle_2'>
				<ul>
					<li><a href='pmsystem.php?section=inbox'><span>View Inbox</span></a></li>
					<li><a href='pmsystem.php?section=outbox'><span>View Outbox</span></a></li>
					<li><a href='pmblocklist.php'><span>Manage Blocked Users</span></a></li>
					<!--<li><a href='pmblocklist.php?action=adduser'><span>Block a User</span></a></li>-->
	i			</ul>
			</div>
			<div style='clear:both;'></div>";
	MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
				<tr>
					<th style='width:80%'><font face='$font_face' size='$font_m' color='$font_color_header'>Users</th>
					<th style='width:20%;text-align:center;'>UnBlock?</th>
				</tr>
			<form action='$PHP_SELF' method='POST' onSubmit='submitonce(this)' enctype='multipart/form-data'><input type='hidden' name='action' value='unblock'>";
	$none = 0;
	$count = 0;
	if (FALSE !== ($blockedIds = PrivateMessaging::getUsersPMBlockedList($_COOKIE["id_env"]))) {
		$count = count($blockedIds);
		for($i = 0; $i < $count; $i++) {
			if ($blockedIds[$i] != "") {
				$user = $tdb->get("users", $blockedIds[$i]);
				echo "
				<tr>
					<td class='area_2' style='padding:8px;'><span class='link_1'><a href='profile.php?action=get&id=".$user[0]["id"]."' target='_blank'>".$user[0]["user_name"]."</a></span></td>
					<td class='area_1' style='padding:8px;text-align:center;'><input type='checkbox' name='$blockedIds[$i]' value='CHECKED'></td>
				</tr>";
			} else {
				$none++;
			}
		}
	}
	if ($none == $count) {
		echo "
				<tr>
					<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='2'>No Blocked Users</td>
				</tr>";
		$disable = "DISABLED";
	}
	else $disable = "";
	echo "
				<tr>
					<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
				</tr>
				<tr>
					<td class='footer_3a' colspan='2' style='text-align:center;'><input type='submit' name='action' value='Unblock' $disable></form></td>
				</tr>";
	MiscFunctions::echoTableFooter(SKIN_DIR);
}
require_once('./includes/footer.php');
?>
