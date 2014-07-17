<?php
// Private Messaging System
// Add on to Ultimate PHP Board V2.0
// Original PM Version (before _MANUAL_ upgrades): 2.0
// Addon Created by J. Moore aka Rebles
// Using textdb Version: 4.2.3
require_once('./includes/upb.initialize.php');
if (!$tdb->is_logged_in()) die('You are not properly logged in.');
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) die('Invalid ID');
require_once('./includes/header_simple.php');
$where = 'PM:';
$PrivMsg = new functions(DB_DIR."/", "privmsg.tdb");
$PrivMsg->setFp("CuBox", ceil($_COOKIE["id_env"]/120));
$pmRec = $PrivMsg->get("CuBox", $_GET["id"]);
echo "
		<div class='simple_head' colspan='2'><div style='float:left;margin-right:4px;'><img src='./skins/default/icons/post_icons/".$pmRec[0]["icon"]."'></div><div style='line-height:15px;'>".$pmRec[0]["subject"]."</div></td>";
$table_color = "area_1";

$user = $tdb->get("users", $pmRec[0]["from"]);

$status_config = status($user);
$status = $status_config['status'];
$statuscolor = $status_config['statuscolor'];

$message = display_msg($pmRec[0]["message"]);
echo "
		<table id='simple_table' style='background-color:#ffffff;' cellspacing='12'>
			<tr>
				<td valign='top'>
					<div class='simple_date' style='float:left;'>Message Sent: ".gmdate("M d, Y g:i:s a", user_date($pmRec[0]["date"]))."</div>
					
					<div class='simple_content'><div style='margin-bottom:20px;'>$message</div></div></td>
			</tr>
		</table>";
require_once('./includes/footer_simple.php');
?>
