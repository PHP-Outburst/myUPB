<?php
// Private Messaging System
// Add on to Ultimate PHP Board V2.0
// Original PM Version (before _MANUAL_ upgrades): 2.0
// Addon Created by J. Moore aka Rebles
// Using textdb Version: 4.4.2
require_once("./includes/upb.initialize.php");
require_once("./includes/inc/post.inc.php");
$where = "<a href='pmsystem.php'>Messenger</a> ".$_CONFIG["where_sep"]." New message";
if ($tdb->is_logged_in() === false) exitPage("You are not even Logged in.");
$PrivMsg = new TdbFunctions(DB_DIR."/", "privmsg.tdb");
$PrivMsg->setFp("CuBox", ceil($_COOKIE["id_env"]/120));
if ($_GET["action"] == "ClearOutBox") {
	require_once("./includes/header.php");
	$recs = $PrivMsg->query("CuBox", "box='outbox'&&from='".$_COOKIE["id_env"]."'", 1);
	$recs = array_reverse($recs);
	$c_outbox_recs = count($recs); //extra one for the pm just added to the outbox.
	if ($c_outbox_recs > 50 && $recs[0]["id"] != "") {
		for($i = 50; $i < ($c_outbox_recs); $i++) {
			$PrivMsg->delete("CuBox", $recs[$i]["id"], false);
		}
		//$PrivMsg->reBuild("CuBox");
	}
	echo str_replace('__TITLE__','Redirecting:',str_replace('__MSG__',"Message successfully sent!",CONFIRM_MSG));
	require_once("./includes/footer.php");
	if ($_GET["ref"] != "" && $_GET["section"] != "" && $_GET["r"] != "") redirect($_POST["ref"]."?section=".$_GET["section"]."&id=".$_GET["r"], "2");
	else redirect("pmsystem.php", "2");
	exit;
} elseif($_POST["s"] == 1) {

	if (isset($_POST['pm_recip']))
	{
		$q = $tdb->query("users", "user_name='".strtolower($_POST["pm_recip"])."'", 1, 1,array('id'));
		$_POST['to'] = $q[0]['id'];
	}

	$error_msg = "";
	if (!isset($_POST["icon"])) {
		$error_msg .= str_replace('__TITLE__','Caution!',str_replace('__MSG__',"Must be submitted through the form.",ALERT_MSG));
	}
	if (chop($_POST['subject']) == "") {
		$error_msg .= str_replace('__TITLE__','Caution!',str_replace('__MSG__',"You must provide a subject.",ALERT_MSG));
	}
	if (chop($_POST["message"]) == "") {
		$error_msg .= str_replace('__TITLE__','Caution!',str_replace('__MSG__',"You must provide a message.",ALERT_MSG));
	}
	if ($_POST["to"] == "" || $_POST["to"] == "0") {
		$error_msg .= "Select a Username<br />";
	} elseif($_POST["to"] == $_COOKIE["id_env"]) {
		$error_msg .= str_replace('__TITLE__','Caution!',str_replace('__MSG__',"You cannot send yourself a Private Message.",ALERT_MSG));

	} else {
		$ids = getUsersPMBlockedList($_POST["to"]);
		if (in_array($_COOKIE['id_env'], $ids)) {
			$error_msg .= str_replace('__TITLE__','Denied!',str_replace('__MSG__',"The User you are sending does not wish to recieve messages from you. (You are blocked)",ALERT_MSG));
				
		}
	}
	if ($error_msg == "") {
		require_once('./includes/header.php');
		$to_info = $tdb->get("users", $_POST["to"]);
		echo str_replace('__TITLE__','Redirecting:',str_replace('__MSG__',"Sending Private Message",CONFIRM_MSG));
		$PrivMsg->setFp("ToBox", ceil($_POST["to"]/120));
		if ($_POST["icon"] == "") $_POST["icon"] = "icon1.gif";
		if (trim($_POST["subject"]) == "") $_POST["subject"] = "No Subject";
		if (isset($_POST["del"]) && isset($_POST["r"])) $PrivMsg->delete("CuBox", $_POST["r"]);
		$PrivMsg->add("ToBox", array("box" => "inbox", "from" => $_COOKIE["id_env"], "to" => $_POST["to"], "icon" => $_POST["icon"], "subject" => $_POST["subject"], "date" => mkdate(), "message" => chop($_POST["message"])));
		$PrivMsg->add("CuBox", array("box" => "outbox", "from" => $_COOKIE["id_env"], "to" => $_POST["to"], "icon" => $_POST["icon"], "subject" => $_POST["subject"], "date" => mkdate(), "message" => chop($_POST["message"])));
		$f = fopen(DB_DIR."/new_pm.dat", 'r+');
		fseek($f, (((int)$_POST["to"] * 2) - 2));
		$new_pm = trim(fread($f, 2));
		(int)$new_pm++;
		if (strlen($new_pm) == 3) $new_pm = 99;
		elseif(strlen($new_pm) == 1) $new_pm = " ".$new_pm;
		fseek($f, (((int)$_POST["to"] * 2) - 2));
		fwrite($f, $new_pm);
		fclose($f);
		require_once('./includes/footer.php');
		redirect("newpm.php?action=ClearOutBox&ref=".$_POST["ref"]."&section=".$_POST["section"]."&r=".$_POST["r"], '2');
		exit;
	} else {
		if ($_POST["r"] != "") $_GET["r_id"] = $_POST["r"];
		$sbj = $_POST['subject'];
		$msg = $_POST['message'];
	}
}
require_once('./includes/header.php');

if ($error_msg != "") echo $error_msg;

if (isset($_GET["r_id"]) && is_numeric($_GET["r_id"])) {
	$reply = $PrivMsg->get("CuBox", $_GET["r_id"]);
	$u_reply = $tdb->get("users", $reply[0]["from"]);
	$ids = getUsersPMBlockedList($u_reply[0]['id']);
	if(in_array($_COOKIE['id_env'], $ids)) {
		echo str_replace('__TITLE__','Denied!',str_replace('__MSG__',"The User you are sending does not wish to recieve messages from you. (You are blocked)",ALERT_MSG));
		require_once('./includes/footer.php');
		exit;
	}
	$send_to = $u_reply[0]['user_name']."<input type='hidden' name='to' value='".$reply[0]["from"]."'>";
	if (!isset($sbj)) {
		while (substr($reply[subject], 0, 4) == "RE: ") {
			$reply[0]["subject"] = substr($reply[0]["subject"], 5);
		}
		$sbj = "RE: ".str_replace('<x>','',$reply[0]["subject"]);
	}
	$hed = "Replying to ".$u_reply[0]["user_name"]."'s message";
	$iframe = "
			<tr>
				<td class='review_container'><div class='review_sub'>
					<iframe src='viewpm_simple.php?id=".$_GET["r_id"]."' class='review_frame' scrolling='auto' frameborder='0'></iframe></div></td>
			</tr>";
} else {
	if (isset($_GET['to']) && !is_numeric($_GET['to']))
	{
		echo str_replace('__TITLE__','Error:',str_replace('__MSG__',"Invalid User ID",ALERT_MSG));
		require_once('./includes/footer.php');
		exit();
	}
	if (isset($_GET['to']))
	{
		$send_to = $tdb->get('users', $_GET['to']);
		$send_to = $send_to[0]['user_name'].'<input type="hidden" name="to" value="'.$_GET['to'].'">';
	}
	else
	$send_to = false;
	$hed = "New Topic";
	$iframe = "";
}
$icons = message_icons();

//commented out PM blocking system
if($_GET["to"] == $_COOKIE["id_env"])
{
	echo str_replace('__TITLE__','Caution!',str_replace('__MSG__',"You cannot send yourself a Private Message.",ALERT_MSG));
	require_once('./includes/footer.php');
	exit();
}
else
{
	echo "
		<div id='tabstyle_2'>
		<ul>
		<li><a href='pmsystem.php?section=inbox'><span>View Inbox</span></a></li>
		<li><a href='pmsystem.php?section=outbox'><span>View Outbox</span></a></li>
		<!--<li><a href='pmblocklist.php'><span>Manage Blocked Users</span></a></li>
		<li><a href='pmblocklist.php?action=adduser'><span>Block a User</span></a></li>-->
		</ul>
		</div>
		<div style='clear:both;'></div>";


	echo "<form action='".$_SERVER['PHP_SELF'].(isset($_GET['to']) ? "?to=".$_GET['to'] : '')."' method='POST' name='newentry' onSubmit='return validate_topic();' enctype='multipart/form-data'><input type='hidden' name='s' value='1'><input type='hidden' name='r' value='".$_GET["r_id"]."'>";

	echoTableHeading($hed, $_CONFIG);
	echo "
			<tr>
				<th colspan='2'>$hed</th>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>User Name:</strong></td>
				<td class='area_2'>".$_COOKIE["user_env"]."</td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Send to:</strong></td>
				<td class='area_2'>";
	if ($send_to !== false)
	echo $send_to;
	else
	echo "<input type='text' name='pm_recip' size=40 onblur=\"getUsername(this.value,'pm');\"><span class='err' id='namecheck'></span>";
	echo "</td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Subject:</strong></td>
				<td class='area_2'><input type='text' name='subject' size='40' value='".$sbj."'> <span id='sub_err' class='err'></span></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Message Icon:</strong></td>
				<td class='area_2'><div style='width:610px;'>$icons</div></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;' valign='top'><strong>Message:</strong>
					<div style='text-align:center;margin-top:20px;margin-bottom:20px;'>";
	echo "</div>
					<div style='text-align:center;'></div></td>
				<td class='area_2'>".bbcodebuttons('look1')."
        <textarea name='message' id='look1'>".$msg."</textarea><br>
					<span id='msg_err' class='err'></span>
					<div style='padding:8px;'>".getSmilies('look1')."</div></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='6'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='6' style='text-align:center;'><input name='submit' id='submit' type='submit' value='Send PM' ";
	if ($send_to === false)
	echo "disabled";
	echo "> <input name='reset' id='reset' type='reset' value='Reset'></td>
			</tr>
	</form>";
	echoTableFooter(SKIN_DIR);
}
if (isset($_GET["r_id"]) && is_numeric($_GET["r_id"])) {
	echoTableHeading("".$u_reply[0]["user_name"]."'s Message to you:", $_CONFIG);
	echo "
	$iframe";
	echoTableFooter(SKIN_DIR);
}
require_once("./includes/footer.php");
?>