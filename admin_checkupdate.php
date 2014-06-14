<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once("./includes/upb.initialize.php");
$where = "<a href='admin.php'>Admin</a> ".$_CONFIG["where_sep"]." Checking for updates";
require_once('./includes/header.php');
if ($tdb->is_logged_in() && $_COOKIE["power_env"] >= 3) {
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
	echoTableHeading("Checking for myUPB updates", $_CONFIG);
	echo "
		<tr>
			<td class='review_container'><div class='review_sub'><iframe src='http://www.myupb.com/upbcheckupdate.php?ver=".UPB_VERSION."' class='review_frame' scrolling='auto' frameborder='0'></iframe></div></td>
		</tr>";
	echoTableFooter(SKIN_DIR);
} else {
	echo "
			<div class='alert'><div class='alert_text'>
			<strong>Access Denied!</strong></div><div style='padding:4px;'>you are not authorized to be here.</div></div>";
}
require_once("./includes/footer.php");
?>
