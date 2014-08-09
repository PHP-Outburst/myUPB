<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once("./includes/upb.initialize.php");
$rec = $tdb->get("users", $_GET["id"]);
require_once('./includes/header.php');
if (!(isset($_COOKIE["power_env"]) && isset($_COOKIE["user_env"]) && isset($_COOKIE["uniquekey_env"]) && isset($_COOKIE["id_env"]))) MiscFunctions::exitPage("<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>You are not logged in.</div></div><meta http-equiv='refresh' content='2;URL=login.php?ref=email.php?id=$id'>");
if (!$tdb->is_logged_in()) MiscFunctions::exitPage("<div class='alert'><div class='alert_text'>
		<strong>Access Denied!</strong></div><div style='padding:4px;'>You are not authorized to be here.</div></div>");
if (isset($_POST["subject"]) && isset($_POST["message"])) {
	$where = "<a href='showmembers.php'>Members List</a> ".$_CONFIG["where_sep"]." Send email";
	require_once('./includes/header.php');
	$self = $tdb->get("users", $_COOKIE["id_env"]);
	$from = @$self[0]["email"]; //Email address you want it to 'appear' to come from
	$mailheader = "From: $from\r\n";
	$mailheader .= "Reply-To: $from\r\n";
	$mailbody = $_POST["message"];
	$mailforms = mail(@$rec[0]["email"], $_POST["subject"], $mailbody, $mailheader);
	if ($mailforms) {
		echo "
						<div class='alert_confirm'>
						<div class='alert_confirm_text'>
						<strong>Redirecting:</div><div style='padding:4px;'>
						The email was sent successfully to ".$rec[0]["user_name"]."
						</div>
						</div>";
		require_once("./includes/footer.php");
		MiscFunctions::redirect("index.php", 2);
		exit;
	}
	else echo "<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>The email was NOT sent to ".$rec[0]["user_name"]." due to an error.</div></div>";
} else {
	$where = "<a href='showmembers.php'>Members List</a> ".$_CONFIG["where_sep"]." Send email";
	require_once('./includes/header.php');
	echo "<form name='form1' method='post' action='$PHP_SELF' ><input type='hidden' name='id' value='".$_GET["id"]."'>";
	MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
			<tr>
				<th colspan='2'>Composing an email</th>
			</tr>
			<tr>
				<td class='area_1'><strong>To:</strong></td>
				<td class='area_2'>".$rec[0]["user_name"]."</td>
			</tr>
			<tr>
				<td class='area_1'><strong>Your subject:</strong></td>
				<td class='area_2'><input type='text' name='subject' value='".$_POST["subject"]."'></td>
			</tr>
			<tr>
				<td class='area_1' valign='top'><strong>Your message:</strong></td>
				<td class='area_2'><textarea name='message' cols='30' rows='7' id='look1'>".$_POST["message"]."</textarea></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type='submit' value='Send'><input type='reset' value='Reset' name='Reset'></td>
			</tr>
	</form>";
	MiscFunctions::echoTableFooter(SKIN_DIR);
}
require_once("./includes/footer.php");
?>