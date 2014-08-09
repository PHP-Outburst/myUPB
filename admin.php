<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once("./includes/upb.initialize.php");
$where = "Admin Panel";
require_once('./includes/header.php');
if (isset($_COOKIE["user_env"]) && isset($_COOKIE["uniquekey_env"]) && isset($_COOKIE["power_env"]) && isset($_COOKIE["id_env"])) {
	if ($tdb->is_logged_in() && $_COOKIE["power_env"] >= 3) {
		MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
		echo "
			<tr>
				<th>Admin Options</th>
			</tr>
			<tr>
				<td class='area_2' style='padding:20px;' valign='top'>";
		require_once("admin_navigation.php");
		echo "</td></tr>";
		MiscFunctions::echoTableFooter(SKIN_DIR);
	}
	else echo "<div class='alert'><div class='alert_text'>
<strong>Access Denied!!</strong></div><div style='padding:4px;'>You are not authorized to be here.</div></div>";
}
else echo "<div class='alert'><div class='alert_text'>
<strong>Step Five Failed!</strong></div><div style='padding:4px;'>You are not logged in or not authorized to be here.<meta http-equiv='refresh' content='2;URL=login.php?ref=admin.php'></div></div>";
require_once("./includes/footer.php");
?>
