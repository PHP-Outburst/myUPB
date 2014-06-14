<?php
/**
 * View Forum Topic
 *
 * @author Tim Hoeppner <timhoeppner@gmail.com>
 * @author FixITguy
 * @author Jerroyd Moore
 * @author Chris Kent
 */

require_once('./includes/upb.initialize.php');
require_once('./includes/class/posts.class.php');

$posts_tdb = new posts(DB_DIR."/", "posts.tdb");
$vars['page'] = ((isset($_GET['page'])) ? $_GET['page'] : '');
//check if the id exists
if (!(is_numeric($_GET["id"]) && $posts_tdb->isTable($_GET["id"]))) exitPage("Forum does not exist.", true);
if (FALSE === ($fRec = $tdb->get("forums", $_GET["id"]))) exitPage("Forum does not exist.", true);
$posts_tdb->setFp("topics", $_GET["id"]."_topics");
$posts_tdb->setFp("posts", $_GET["id"]);
if (FALSE === ($tRec = $posts_tdb->get("topics", $_GET["t_id"]))) exitPage("Invalid Topic.", true);

$posts_tdb->set_topic($tRec);
$posts_tdb->set_forum($fRec);
if (!($tdb->is_logged_in())) {
	$posts_tdb->set_user_info("guest", "password", "0", "0");
	$_COOKIE["power_env"] = 0;
}
else $posts_tdb->set_user_info($_COOKIE["user_env"], $_COOKIE["uniquekey_env"], $_COOKIE["power_env"], $_COOKIE["id_env"]);
$where = "<a href='viewforum.php?id=".$_GET["id"]."'>".$fRec[0]["forum"]."</a> ".$_CONFIG["where_sep"]." ".$tRec[0]["subject"];
require_once('./includes/header.php');

if ((int)$_COOKIE["power_env"] < $fRec[0]["view"]) exitPage("You do not have enough Power to view this topic");
if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) exitPage("Invalid Forum ID");
if (!isset($_GET["t_id"]) || !ctype_digit($_GET["t_id"])) exitPage("Invalid Topic ID");

//because session_start() is in header.php CONSIDER MOVING TO FUNC.INC.PHP or FUNC.CLASS.PHP
$sess_name = 'view_'.$_GET['id'].'_'.$_GET['t_id'];
if(!isset($_SESSION[$sess_name]) || $_SESSION[$sess_name]+300 < time()) $posts_tdb->edit("topics", $_GET["t_id"], array("views" => ((int)$tRec[0]["views"] + 1)));
$_SESSION[$sess_name] = time();
if(isset($_SESSION['newTopics']['f'.$_GET['id']]['t'.$_GET['t_id']])) {
	if($_SESSION['newTopics']['f'.$_GET['id']]['t'.$_GET['t_id']] == 1) unset($_SESSION['newTopics']['f'.$_GET['id']]['t'.$_GET['t_id']]);
	if($_SESSION['newTopics']['f'.$_GET['id']]['t'.$_GET['t_id']] == 0 && $_SESSION['newTopics']['lastVisitForums'][$_GET['id']] > $tRec[0]['last_post']) unset($_SESSION['newTopics']['f'.$_GET['id']]['t'.$_GET['t_id']]);
}
if($tRec[0]['last_post'] > $_SESSION['newTopics']['lastVisitForums'][$_GET['id']]) $_SESSION['newTopics']['f'.$_GET['id']]['t'.$_GET['t_id']] = 0;
$tdb->updateVisitedTopics();
//if($tRec[0]['last_post'] > $_SESSION['newTopics']['lastVisitForums'][$_GET['id']]) echo 'true'; else echo  'false';

if (!isset($_GET['page']) || $_GET["page"] == "") {
	$vars['page'] = ceil((substr_count($tRec[0]['p_ids'], ',') + 1) / $_CONFIG['posts_per_page']);
}
$pRecs = $posts_tdb->getPosts("posts", (($_CONFIG["posts_per_page"] * $vars['page'])-$_CONFIG["posts_per_page"]), $_CONFIG["posts_per_page"]);
if (empty($pRecs)) {
	$msg = 'No posts could be found for this topic';
	if ((int)$_COOKIE["power_env"] >= 2)
	$msg .= "<br>To delete this topic click <a href='managetopic.php?id=2&amp;t_id=2'>here</a>";
	die(str_replace('__TITLE__', 'Fatal Error:', str_replace('__MSG__', $msg, ALERT_MSG)).MINIMAL_BODY_FOOTER);

	require_once('./includes/footer.php');
	die();
}
$num_pages = ceil(($tRec[0]["replies"] + 1) / $_CONFIG["posts_per_page"]);
$p = createPageNumbers($vars["page"], $num_pages, $_SERVER['QUERY_STRING']);
//$isWatching = in_array($useremail, explode(',', $tRec[0]['monitor']));
if($tdb->is_logged_in()) {
	$email_mode = $_CONFIG['email_mode'];
	$thisUser = $tdb->get("users", $_COOKIE["id_env"]);
	$isWatching = in_array($thisUser[0]["id"], explode(',', $tRec[0]['monitor']));
} else {
	$email_mode = false;
	$isWatching = false;
}
echo "<br /><div id='pagelink1' name='pagelink1'>".$posts_tdb->d_posting($email_mode, $isWatching, $p,$vars['page'],$num_pages)."</div>";
if ($vars['page'] == 1) $first_post = $pRecs[0]['id'];
else $first_post = 0;
$x = +1;

echo "<div name='current_posts' id='current_posts'>";

foreach($pRecs as $pRec) {
	// display each post in the current topic
	//deleted "<a name='{$pRec['id']}'>" from first echo line, seems unused
	echo "	
      <div name='post{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}' id='post{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}'>
      <div class='main_cat_wrapper'>
			<div class='cat_area_1' style='text-align:center;'>Posted: ".gmdate("M d, Y g:i:s a", user_date($pRec["date"]))."</div>
			<table class='main_table'>";
	if ($x == 0) {
		$table_color = 'area_1';

		$x++;
	} else {
		$table_color = 'area_2';

		$x--;
	}
	unset($user, $status, $statuscolor,$statusrank);
	$sig = '';
	$status = '';
	$statuscolor = '';
	$statusrank = '';
	$pm = "";
	if ($pRec["user_id"] != "0") {
		$user = $tdb->get("users", $pRec["user_id"]);
		if($user === false) {
			$user = array(array('level'=>0, 'status'=>'<i>Deleted Member</i>'));
			$pRec['user_id'] = '0';
		}
		if ($user[0]["sig"] != "") {
			$sig = display_msg($user[0]["sig"]);
			$sig = "<div class='signature'>$sig</div>";
		}
		$status_config = status($user);
		$status = $status_config['status'];
		$statuscolor = $status_config['statuscolor'];
		$statusrank = $status_config['rank'];

		if ($user[0]["status"] != "") $status = $user[0]["status"];
		if (isset($_COOKIE["id_env"]) && $pRec["user_id"] != $_COOKIE["id_env"] && $pRec['user_id'] != 0) {
			$user_blList = getUsersPMBlockedList($pRec["user_id"]);
			if (TRUE !== (in_array($_COOKIE["id_env"], $user_blList))) $pm = "<div class='button_pro2'><a href='newpm.php?to=".$pRec["user_id"]."'>Send ".$pRec["user_name"]." a PM</a></div>";
		}
	}
	if (($_COOKIE["id_env"] == $pRec["user_id"] && $tdb->is_logged_in()) || (int)$_COOKIE["power_env"] >= 2)
	{
		$edit = "<div class='button_pro1' id='enabled_msg'>";
		$edit .= "<a href=\"javascript:getPost('{$pRec["user_id"]}','{$_GET["id"]}-{$_GET["t_id"]}-{$pRec["id"]}','edit');\">";
		$edit .= "Edit</a></div>";
		$edit .= "<div class='button_pro1' id='disabled_msg'>";
		$edit .= "<a href='editpost.php?id=".$_GET["id"]."&amp;t_id=".$_GET["t_id"]."&amp;p_id=".$pRec["id"]."'>";
		$edit .= "Edit</a></div>
      ";

	}
	else $edit = "";
	if ((($_COOKIE["id_env"] == $pRec["user_id"] && $tdb->is_logged_in()) || (int)$_COOKIE["power_env"] >= 2) && $pRec['id'] != $first_post) $delete = "<div class='button_pro1'><a href='delete.php?action=delete&amp;t=0&id=".$_GET["id"]."&amp;t_id=".$_GET["t_id"]."&amp;p_id=".$pRec["id"]."'>X</a></div>";
	else $delete = "";

	if ((int)$_COOKIE["power_env"] >= (int)$fRec[0]["reply"] and $tRec[0]['locked'] != 1)
	$quote = "<div class='button_pro1'><a href='newpost.php?id=".$_GET["id"]."&amp;t_id=".$_GET["t_id"]."&amp;q_id=".$pRec['id']."&amp;page=".$vars["page"]."'>Quote</a></div>";
	else $quote = "";

	if ((int)$_COOKIE["power_env"] >= (int)$fRec[0]["reply"] and $tRec[0]['locked'] != 1) $reply = "<div class='button_pro1'><a href='newpost.php?id=".$_GET["id"]."&amp;t=0&amp;t_id=".$_GET["t_id"]."&amp;page=".$vars['page']."'>Add Reply</a></div>";
	else $reply = "";

	$msg = display_msg($pRec['message']);
	$msg .= "<div id='{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}-attach'>".$tdb->getUploads($_GET['id'],$_GET['t_id'],$pRec['id'],$pRec['upload_id'],$_CONFIG['fileupload_location'],$pRec['user_id'])."</div>";
	echo "
			<tr>
				<th><div class='post_name'>";
	if ($pRec["user_id"] != "0") echo "<a href='profile.php?action=get&amp;id=".$pRec["user_id"]."'>".$pRec["user_name"]."</a>";
	else echo $pRec["user_name"];
	echo "</div></th>
				<th><div style='float:left;'><img src='".SKIN_DIR."/icons/post_icons/".$pRec["icon"]."' alt=''></div><div style='align:right;'>$delete $edit $quote $reply</div></th>
			</tr>
			<tr>
				<td class='$table_color' style='width:15%; vertical-align:top;'>";
	if (@$user[0]["avatar"] != "")
	{
		$resize = resize_img($user[0]['avatar'],$_REGIST["avatarupload_dim"]);
		echo "<br /><style='align:center'><img src=\"".$user[0]["avatar"]."\" border='0' $resize alt='' title=''></style><br />";
	}
	else if ($pRec["user_id"] != "0")
	echo "<br /><br />";
	print "<div class='post_info' style='align:center'><span style='color:#".$statuscolor."'><img src='".$statusrank."'><br><strong>".$status."</strong></span></div>";
	if ($pRec["user_id"] != "0") echo "
					<div class='post_info'>
						<strong>Posts:</strong> ".$user[0]["posts"]."
						<br />
						<strong>Registered:</strong>
						<br />
						".gmdate("Y-m-d", user_date($user[0]["date_added"]))."
					</div>
					<br />
					<div class='post_info_extra'>";
	if ($user[0]["aim"] != "") echo "&nbsp;<a href='aim:goim?screenname=".$user[0]["aim"]."'><img src='images/aol.gif' border='0' alt='AIM: ".$user[0]["aim"]."'></a>&nbsp;&nbsp;";
	if ($user[0]["msn"] != "") echo "&nbsp;<a href='http://members.msn.com/".$user[0]["msn"]."' target='_blank'><img src='images/msn.gif' border='0' alt='MSN: ".$user[0]["msn"]."'></a>&nbsp;&nbsp;";
	if ($user[0]["icq"] != "") echo "&nbsp;<a href='http://wwp.icq.com/scripts/contact.dll?msgto=".$user[0]["icq"]."&action=message'><img src='images/icq.gif' border='0' alt='ICQ: ".$user[0]["icq"]."'></a>&nbsp;&nbsp;";
	if ($user[0]["yahoo"] != "") echo "&nbsp;<a href='http://edit.yahoo.com/config/send_webmesg?.target=".$user[0]["yahoo"]."&.src=pg'><img border=0 src='http://opi.yahoo.com/online?u=".$user[0]["yahoo"]."&amp;m=g&t=0' alt='Y!: ".$user[0]["yahoo"]."'></a>";

	echo"</div>";
	echo "</td>
				<td class='$table_color' style='vertical-align:top;'>
					<div class='msg_block' id='{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}' name='{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}'>$msg</div>
					<div style='padding:12px;'>".$sig."</div></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2'>";
	if ($pRec["user_id"] != "0") echo "";
	if ($pm != "") echo $pm."";

	//echo "<div name='edit{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}' id='edit{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}' style='float: right;'>";
	if (!empty($pRec['edited_by']) && !empty($pRec['edited_by_id']) && !empty($pRec['edited_date'])) echo "
					<div class='post_edited' name='edit{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}' id='edit{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}'>Last edited by: <a href='profile.php?action=get&amp;id=".$pRec['edited_by_id']." target='_blank'><strong>".$pRec['edited_by']."</strong></a> on ".gmdate("M d, Y g:i:s a", user_date($pRec['edited_date']))."</div>";
	else
	echo "<div name='edit{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}' id='edit{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}' class='post_edited'></div>";
	if ($pRec['user_id'] != 0)
	{
		echo "
					<div class='button_pro2'><a href='profile.php?action=get&amp;id=".$pRec["user_id"]."'>Profile</a></div>";
		if (isValidUrl($user[0]['url']))
		echo "<div class='button_pro2'><a href='".$user[0]["url"]."' target = '_blank'>Homepage</a></div>";
		if ($_CONFIG['email_mode'])
		echo "
					<div class='button_pro2'><a href='email.php?id=".$pRec["user_id"]."'>email ".$pRec["user_name"]."</a></div>";
	}
	echo "</td></tr>".echoTableFooter(SKIN_DIR)."</div>";

}
echo "</div>";

echo "<div id='pagelink2' name='pagelink2'>" . $posts_tdb->d_posting($email_mode, $isWatching, $p,$vars['page'],$num_pages,"bottom") . "</div>";
if (!($_COOKIE["power_env"] < $fRec[0]["post"] && $_GET["t"] == 1 || $_COOKIE["power_env"] < $fRec[0]["reply"] && $_GET["t"] == 0 ) and $tRec[0]['locked'] != 1)
{
	echo "<br><div id='enabled_msg'><div id='quickreplyform' name='quickreplyform'>";
	echo "<form name='quickreplyfm' action='newpost.php?id=".$_GET["id"]."&amp;t_id=".$_GET["t_id"]."&amp;page=".$vars["page"]."' id='quickreplyfm' method='POST'>\n";
	echoTableHeading("Quick Reply", $_CONFIG);
	echo "<table class='main_table'>";
	foreach ($_GET as $key => $value)
	{
		if ($key != 'page')
		echo "<input type='hidden' id='$key' name='$key' value='$value'>\n";
	}
	echo "<input type='hidden' id='page' name='page' value='".$vars['page']."'>\n";
	echo "<input type='hidden' id='user_id' name='user_id' value='{$_COOKIE['id_env']}'>\n";
	echo "<input type='hidden' id='icon' name='icon' value='icon1.gif' alt=''>\n";
	echo "<input type='hidden' id='username' name='username' value='{$_COOKIE["user_env"]}'>\n";
	echo "
		<tr><td class='area_1' style='padding:8px;'><strong>User Name:</strong></td><td class='area_2'>".$_COOKIE["user_env"]."</td></tr>\n
		<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
		<tr><td class='area_1' style='padding:8px; vertical-align:top;'><strong>Message:</strong></td>
    <td class='area_2'>\n
    <textarea id=\"newentry\" name=\"newentry\" value=\"\" cols=\"60\" rows=\"18\"></textarea>\n
    </td></tr>\n";
	echo "<tr><td class='footer_3a' style='text-align:center;' colspan='2'>\n
    <input type='button' name='quickreply' value='Quick Reply' onclick=\"document.quickreplyfm.quickreply.disabled=true;javascript:getReply(document.getElementById('quickreply'));\">\n
    <input type='submit' name='submit' value='Advanced'>\n</td></tr></form>";
	echoTableFooter(SKIN_DIR);
	echo "</div></div>";
}
//END QUICK REPLY SEGMENT
$tdb->cleanup();
unset($tdb);
require_once('./includes/footer.php');
?>
