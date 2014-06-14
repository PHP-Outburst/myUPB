<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once('./includes/upb.initialize.php');
$where = "Members List";
require_once('./includes/header.php');
if ($tdb->is_logged_in()) {
	if ($_GET["page"] == "") $_GET["page"] = 1;
	$users = $tdb->listRec("users", ($_GET["page"] * $_CONFIG["topics_per_page"] - $_CONFIG["topics_per_page"] + 1),$_CONFIG["topics_per_page"]);

	foreach ($users as $key => $user)
	{
		if ($user['reg_code'] != '')
		unset($users[$key]);
	}
	$c = $tdb->getNumberOfRecords("users");

	$num_pages = ceil(($c + 1) / $_CONFIG["topics_per_page"]);
	$p = createPageNumbers($_GET["page"], $num_pages, $_SERVER['QUERY_STRING']);
	echo pagination($p,$_GET['page'],$num_pages);

	echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
			<tr>
				<th style='width:3%; text-align:center;'>ID</th>
				<th style='width:14%'>Username</th>
				<th style='width:17%; text-align:center;'>Location</th>
				<th style='width:5%; text-align:center;'>Posts</th>
				<th style='width:12%; text-align:center;'>AIM</th>
				<th style='width:13%; text-align:center;'>MSN</th>
				<th style='width:13%; text-align:center;'>Yahoo!</th>
				<th style='width:12%; text-align:center;'>ICQ</th>
				<th style='text-align:center;'>Skype</th>
			</tr>";
	if ($users[0]["id"] == "") {
		echo "
			<tr>
				<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='8'>No records found</td>
			</tr>";
	} else {
		foreach($users as $user) {
			//$array[0] = ;
			$status_config = status(array(0 => array('level'=>$user['level'],'posts'=>$user['posts'])));
			$status = $status_config['status'];
			$statuscolor = $status_config['statuscolor'];

			/* location, # of posts, aim, msn, yahoo, icq */
			echo "
			<tr>
				<td class='area_1' style='padding:8px;'>".$user["id"]."</td>
				<td class='area_2'><span class='link_1'><a href='profile.php?action=get&amp;id=".$user["id"]."' style='color:#".$statuscolor."'>".$user["user_name"]."</a></span></td>
				<td class='area_2' style='text-align:center;'>".$user["location"]."</td>
				<td class='area_1' style='text-align:center;'>".$user["posts"]."</td>
				<td class='area_2' style='text-align:center;'>";
			if ($user["aim"] != "") echo "<a href='aim:goim?screenname=".$user["aim"]."'><img src='images/aol.gif' border='0'>&nbsp;".$user["aim"]."</a>";
			echo "</td>
				<td class='area_1' style='text-align:center;'>";
			if ($user["msn"] != "") echo "<a href='http://members.msn.com/".$user["msn"]."' target='_blank'><img src='images/msn.gif' border='0'>&nbsp;".$user["msn"]."</a>";
			echo "</td>
				<td class='area_2' style='text-align:center;'>";
			if ($user["yahoo"] != "") echo "<a href='http://edit.yahoo.com/config/send_webmesg?.target=".$user["yahoo"]."&.src=pg'><img border=0 src='images/yahoo.gif'>&nbsp;".$user["yahoo"]."</a>";
			echo "</td>
				<td class='area_1' style='text-align:center;'>";
			if ($user["icq"] != "") echo "<a href='http://wwp.icq.com/scripts/contact.dll?msgto=".$user["icq"]."&action=message'><img src='images/icq.gif' border='0'>&nbsp;".$user["icq"]."</a>";
			echo "</td><td class='area_2' style='text-align:center;'>";
			if ($user["skype"] != "") echo "<img src='images/skype.gif' border='0'>&nbsp;".$user["skype"]."</td>
			</tr>";
		}
	}
	echoTableFooter(SKIN_DIR);
	echo pagination($p,$_GET['page'],$num_pages);
} else {
	echo "<div class='alert'><div class='alert_text'>
<strong>Access Denied!</strong></div><div style='padding:4px;'>you are not authorized to be here.</div></div>";
}
require_once('./includes/footer.php');
?>
