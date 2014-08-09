<?php
// Private Messaging System
// Add on to Ultimate PHP Board V2.0
// Original PM Version (before _MANUAL_ upgrades): 2.0
// Addon Created by J. Moore aka Rebles
// Using textdb Version: 4.2.3
require_once('./includes/upb.initialize.php');
$where = "<a href='pmsystem.php'>Messenger</a>";
if (isset($_GET["section"]) && $_GET["section"] != "") $where .= " ".$_CONFIG["where_sep"]." ".ucfirst($_GET["section"]);
require_once('./includes/header.php');
if (!isset($_COOKIE["user_env"]) || !isset($_COOKIE["uniquekey_env"]) || !isset($_COOKIE["power_env"]) || !isset($_COOKIE["id_env"])) MiscFunctions::exitPage("
		<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>You are not logged in.</div></div>");
if (!$tdb->is_logged_in()) MiscFunctions::exitPage("
		<div class='alert'><div class='alert_text'>
		<strong>Access Denied!</strong></div><div style='padding:4px;'>Invalid Login!</div></div>");
$PrivMsg = new TdbFunctions(DB_DIR."/", "privmsg.tdb");
$PrivMsg->setFp("CuBox", ceil($_COOKIE["id_env"]/120));
if ($_GET["section"] != "outbox") $pmRecs = $PrivMsg->query("CuBox", "box='inbox'&&to='".$_COOKIE["id_env"]."'");
else $pmRecs = $PrivMsg->query("CuBox", "box='outbox'&&from='".$_COOKIE["id_env"]."'");

if (!empty($pmRecs) && $pmRecs[0] != '') $pmRecs = array_reverse($pmRecs);
elseif($_GET['section'] != '') {
	//PM Blocking system commented out
	echo "
			<div id='tabstyle_2'>
				<ul>
					<li><a href='pmsystem.php?section=inbox'><span>View Inbox</span></a></li>
					<li><a href='pmsystem.php?section=outbox'><span>View Outbox</span></a></li>
					<li><a href='pmblocklist.php'><span>Manage Blocked Users</span></a></li>
					<li><a href='newpm.php'><span>Send a PM</span></a></li>
					<!--<li><a href='pmblocklist.php?action=adduser'><span>Block a User</span></a></li>-->
				</ul>
			</div>
			<div style='clear:both;'></div>";
	MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
				<tr>
					<th>&nbsp;</th>
				</tr>
				<tr>
					<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;'>No Messages in your ".$_GET["section"]."</td>
				</tr>";
	MiscFunctions::echoTableFooter(SKIN_DIR);
	require_once('./includes/footer.php');
	exit;
}

if ($pmRecs === false) //if $pmRecs is false, count still evaluates to 1
$count = 0;
else
$count = count($pmRecs);

if ($_GET["section"] == "inbox") {
	if ($new_pm != 0) {
		$f = fopen(DB_DIR."/new_pm.dat", 'r+');
		fseek($f, (((int)$_COOKIE["id_env"] * 2) - 2));
		fwrite($f, " 0");
		fclose($f);
	}
	if ($_GET['action'] == "delete") {
		$num = 0;
		$delete = array();
		for($i = 0; $i < $count; $i++) {
			if (isset($_POST[$pmRecs[$i]["id"]."_del"])) {
				$PrivMsg->delete("CuBox", $pmRecs[$i]["id"], false);
				$num++;
				$delete[] = $i;
			}
		}
		//$PrivMsg->reBuild("CuBox"); // Not needed with new version of TextDB?
		if ($num > 0) {
			echo "<p align='center'>Successfully Deleted $num Private Msg(s)</p>";
			$count -= $num;
			for($i = 0; $i < count($delete); $i++) {
				unset($pmRecs[$delete[$i]]);
			}
		} else {
			echo "<p align='center'>No Private Msg(s) Successfully Deleted...</p>";
		}
		unset($num);
	}
	$none = TRUE;
	$echo = "";
	$blockedids = PrivateMessaging::getUsersPMBlockedList($_COOKIE["id_env"]);
	foreach($pmRecs as $pmRec) {
		if ($pmRec["id"] != "") {
			if ($none) $none = FALSE;
			if ($pmRec["date"] > $_COOKIE["lastvisit"]) $new = "<img src='".SKIN_DIR."/icons/post_icons/new.gif' alt='' title='' />";
			else $new = "&nbsp;";
			$user = $tdb->get("users", $pmRec["from"]);
			if ($user[0]["level"] == "1") {
				if (TRUE !== (in_array($pmRec["from"], $blockedids))) $ban_text = "<a href='pmblocklist.php?action=add&amp;user_id=".$pmRec["from"]."'>Block</a>";
				else $ban_text = "<span style='color:#ff0000'><strong>BLOCKED!</strong></span>";
			} else {
				$ban_text = "<span style='color:#ff0000'><strong>Admin/Mod</strong></span>";
			}
			$echo .= "
				<tr>
					<td class='area_1' style='text-align:center;padding:8px;'>$new</td>
					<td class='area_1' style='text-align:center;padding:8px;'><img src='".SKIN_DIR."/icons/post_icons/".$pmRec["icon"]."'></td>
					<td class='area_2'><span class='link_1'><a href='viewpm.php?section=".$_GET["section"]."&id=".$pmRec["id"]."'>".$pmRec["subject"]."</a></span></td>
					<td class='area_1'><a href='profile.php?action=get&id=".$pmRec["from"]."'>".$user[0]["user_name"]."</a> on ".gmdate("M d, Y g:i:s a", DateCustom::user_date($pmRec["date"]))."</td>
					<td class='area_2' style='text-align:center;padding:8px;'>$ban_text</td>
					<td class='area_1' style='text-align:center;padding:8px;'><input type='checkbox' name='".$pmRec["id"]."_del' value='CHECKED'></td>
				</tr>";
			unset($new, $ban_text);
		} else {
			$none++;
		}
	}
	if ($none) {
		$echo = "
				<tr>
					<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='6'>No Messages in your ".$_GET["section"]."</td>
				</tr>";
		$disable = "DISABLED";
	}
	else $disable = "";
	echo "
			<div id='tabstyle_2'>
				<ul>
					<li><a href='pmsystem.php?section=inbox'><span>View Inbox</span></a></li>
					<li><a href='pmsystem.php?section=outbox'><span>View Outbox</span></a></li>
					<li><a href='pmblocklist.php'><span>Manage Blocked Users</span></a></li>
					<li><a href='newpm.php'><span>Send a PM</span></a></li>
					<!--<li><a href='pmblocklist.php?action=adduser'><span>Block a User</span></a></li>-->
				</ul>
			</div>
			<div style='clear:both;'></div>";
	echo "<form name='main' action='pmsystem.php?section=inbox&amp;action=delete' method='post' onSubmit='submitonce(this)' enctype='multipart/form-data'>";
	MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
				<tr>
					<th style='width:5%;text-align:center;'>&nbsp;</th>
					<th style='width:5%;text-align:center;'>&nbsp;</th>
					<th style='width:45%;'>Title</th>
					<th style='width:30%;'>From</th>
					<th style='width:10%;text-align:center;'>Action</th>
					<th style='width:5%;text-align:center;'>&nbsp;</th>
				</tr>";
	echo $echo;
	echo "
				<tr>
					<td class='footer_3' colspan='6'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
				</tr>
				<tr>
					<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='6'>You are not allowed to block Administrators/Moderators</td>
				</tr>
				<tr>
					<td class='footer_3' colspan='6'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
				</tr>
				<tr>
					<td class='footer_3a' colspan='6' style='text-align:center;'><input type='submit' name='action' value='Delete Selected PMs' $disable></td>
				</tr>
		</form>";
	MiscFunctions::echoTableFooter(SKIN_DIR);
} elseif($_GET["section"] == "outbox") {
	if ($_GET['action'] == "delete") {
		//MiscFunctions::dump($_POST);
		$num = 0;
		$delete = array();
		for($i = 0; $i < $count; $i++) {
			if (isset($_POST[$pmRecs[$i]["id"]."_del"])) {
				$PrivMsg->delete("CuBox", $pmRecs[$i]["id"], false);
				$num++;
				$delete[] = $i;
			}
		}
		//$PrivMsg->reBuild("CuBox"); // Not needed with new version of TextDB?
		if ($num > 0) {
			echo "<p align='center'>Successfully Deleted $num Private Msg(s)</p>";
			$count -= $num;
			for($i = 0; $i < count($delete); $i++) {
				unset($pmRecs[$delete[$i]]);
			}
		} else {
			echo "<p align='center'>No Private Msg(s) Successfully Deleted...</p>";
		}
		unset($num);
	}
	$none = 0;
	echo "
			<div id='tabstyle_2'>
				<ul>
					<li><a href='pmsystem.php?section=inbox'><span>View Inbox</span></a></li>
					<li><a href='pmsystem.php?section=outbox'><span>View Outbox</span></a></li>
					<li><a href='pmblocklist.php'><span>Manage Blocked Users</span></a></li>
					<li><a href='newpm.php'><span>Send a PM</span></a></li>
					<!--<li><a href='pmblocklist.php?action=adduser'><span>Block a User</span></a></li>-->
				</ul>
			</div>
			<div style='clear:both;'></div>";
	echo "<form name='main' action='pmsystem.php?section=outbox&amp;action=delete' method='post' onSubmit='submitonce(this)' enctype='multipart/form-data'>";
	MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
				<tr>
					<th style='width:5%;'>&nbsp;</th>
					<th style='width:40%;'>Title:</th>
					<th style='width:50%;'>By:</th>
					<th style='width:5%';>Delete</th>
				</tr>";
	foreach($pmRecs as $pmRec) {
		if ($pmRec["id"] != "") {
			$user = $tdb->get("users", $pmRec["to"]);
			echo "
				<tr>
					<td class='area_1' style='text-align:center;padding:8px;'><img src='".SKIN_DIR."/icons/post_icons/".$pmRec["icon"]."' alt='' title='' /></td>
					<td class='area_2'> <span class='link_1'><a href='viewpm.php?section=".$_GET["section"]."&id=".$pmRec["id"]."'>".$pmRec["subject"]."</a></span></td>
					<td class='area_1'>Sent to <a href='profile.php?action=get&id=".$user[0]['id']."'>".$user[0]["user_name"]."</a> on ".gmdate("M d, Y g:i:s a", DateCustom::user_date($pmRec["date"]))."</td>
				  <td class='area_1' style='text-align:center;padding:8px;'><input type='checkbox' name='".$pmRec["id"]."_del' value='CHECKED'></td>
        </tr>";
			unset($pmRec);
		} else {
			$none++;
		}
		unset($pmRec);
	}

	if ($none == $count) {
		echo "
				<tr>
					<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='4'>No Messages in your ".$_GET["section"]."</td>
				</tr>";
		$disable = "DISABLED";
	}
	echo "<tr>
					<td class='footer_3a' colspan='4' style='text-align:center;'><input type='submit' name='action' value='Delete Selected PMs' $disable></td>
				</tr>";
	MiscFunctions::echoTableFooter(SKIN_DIR);
} else {

	$old_pm = ($count - $new_pm);
	echo "
			<div id='tabstyle_2'>
				<ul>
					<li><a href='pmsystem.php?section=inbox'><span>View Inbox</span></a></li>
					<li><a href='pmsystem.php?section=outbox'><span>View Outbox</span></a></li>
					<li><a href='pmblocklist.php'><span>Manage Blocked Users</span></a></li>
					<li><a href='newpm.php'><span>Send a PM</span></a></li>
					<!--<li><a href='pmblocklist.php?action=adduser'><span>Block a User</span></a></li>-->
				</ul>
			</div>
			<div style='clear:both;'></div>";
	MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
				<tr>
					<th><strong>Messenger status</strong></th>
				</tr>
				<tr>
					<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;'>$new_pm New Private Msg(s) and <strong>$old_pm</strong> Old Private Msg(s)</td>
				</tr>";
	MiscFunctions::echoTableFooter(SKIN_DIR);
}
require_once("./includes/footer.php");
?>
