<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once("./includes/upb.initialize.php");
$posts_tdb = new Posts(DB_DIR, "posts.tdb");
$posts_tdb->setFp("topics", $_GET["id"]."_topics");
$posts_tdb->setFp("posts", $_GET["id"]);
//$fRec = $tdb->get("forums", $_GET["id"]);
//$tRec = $posts_tdb->get("topics", $_GET["t_id"]);
$pRec = $posts_tdb->get("posts", $_GET["p_id"]);
$where = "Edit Post";
//$where = "<a href='viewforum.php?id=".$_GET["id"]."'>".$fRec[0]["forum"]."</a> ".$_CONFIG["where_sep"]." <a href='viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."'>".$tRec[0]["subject"]."</a> ".$_CONFIG["where_sep"]." Edit Post";
require_once("./includes/header.php");
if (!(isset($_GET["id"]) && isset($_GET["t_id"]) && isset($_GET["p_id"]))) MiscFunctions::exitPage("<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>Not enough information to perform this function.</div></div>");
if (!($tdb->is_logged_in())) MiscFunctions::exitPage("<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>You are not logged in, therefore unable to perform this action.</div></div>");
if ($pRec[0]["user_id"] != $_COOKIE["id_env"] && $_COOKIE["power_env"] < 2) MiscFunctions::exitPage("<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>You do not have the rights to perform this action.</div></div>");
if (isset($_POST["message"])) {
	$posts_tdb->edit("posts", $_GET["p_id"], array("message" => encode_text(stripslashes($_POST["message"])), "edited_by_id" => $_COOKIE["id_env"], "edited_by" => $_COOKIE["user_env"], "edited_date" => DateCustom::mkdate()));
	echo "
						<div class='alert_confirm'>
						<div class='alert_confirm_text'>
						<strong>Redirecting:</div><div style='padding:4px;'>
						Successfully edited post.
						</div>
						</div>";
	require_once("./includes/footer.php");
	MiscFunctions::redirect("viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&page=".$_GET["page"], 2);
	exit;
} else {
	echo "
	<form action='editpost.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&p_id=".$_GET["p_id"]."' METHOD=POST name='newentry'>";
	MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "

			<tr>
				<td class='area_1' style='padding:8px;' valign='top'><strong>Message:</strong>";

	echo "<div style='text-align:center;'></div></td>
				<td class='area_2'>".bbcodebuttons('look1')."<textarea name='message' id='look1'>".format_text(encode_text($pRec[0]['message']),'edit')."</textarea>
					<div style='padding:8px;'>".getSmilies('look1')."</div></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' style='text-align:center;' colspan='2'>
        <input type=submit value='Edit'><input type='button' onclick='postPreview()' value='Preview'>";
	echo "<input type=button onClick=\"javascript:window.location='viewtopic.php?id=".$_GET['id']."&t_id=".$_GET['t_id']."#".$_GET['p_id']."'\" value='Cancel Edit'>
        </td>
			</tr>
      <tr></tr>";
	MiscFunctions::echoTableFooter(SKIN_DIR);
	echo "
	</form><div id='preview'></div>";
}
require_once("./includes/footer.php");
?>
