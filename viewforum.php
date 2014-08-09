<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once('./includes/upb.initialize.php');
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) MiscFunctions::exitPage(str_replace('__TITLE__', "Invalid Forum", str_replace('__MSG__', ALERT_GENERIC_MSG, ALERT_MSG)), true);

$fRec = $tdb->get("forums", $_GET["id"]);
if(empty($fRec[0])) MiscFunctions::exitPage(str_replace('__TITLE__', "Forum Does Not Exist", str_replace('__MSG__', ALERT_GENERIC_MSG, ALERT_MSG)), true);

$access = false;
if($tdb->is_logged_in() == false)
{
    if($fRec[0]["view"] <= 0)
    {
        $access = true;
    }
}
else
{
    if($fRec[0]["view"] <= $_COOKIE["power_env"])
    {
        $access = true;
    }
}

if($access == false)
{
    MiscFunctions::exitPage(str_replace('__TITLE__', "Permission Denied", str_replace('__MSG__', "You do not have enough Power to view this forum.<br>".ALERT_GENERIC_MSG, ALERT_MSG)), true);
}

$posts_tdb = new Posts(DB_DIR."/", "posts.tdb");
$posts_tdb->setFp("topics", $_GET["id"]."_topics");
$posts_tdb->set_forum($fRec);
if (!($tdb->is_logged_in())) {
	$posts_tdb->set_user_info("guest", "password", "0", "0");
	$_COOKIE["power_env"] = 0;
}
else $posts_tdb->set_user_info($_COOKIE["user_env"], $_COOKIE["uniquekey_env"], $_COOKIE["power_env"], $_COOKIE["id_env"]);
$where = $fRec[0]["forum"];
$vars["cTopics"] = $posts_tdb->getNumberOfRecords("topics");
if (!isset($_GET["page"]) or $_GET['page'] == "") $vars["page"] = 1;
else
$vars["page"] = $_GET["page"];
$start = ($vars["page"] * $_CONFIG["topics_per_page"] - $_CONFIG["topics_per_page"]) + 1;
$tRecs1 = $posts_tdb->query("topics", "sticky='1'", 1);
if (!empty($tRecs1)) $c_total_stickies = count($tRecs1);
else $c_total_stickies = 0;
if ($c_total_stickies >= $start + $_CONFIG['topics_per_page']) {
	//if greater than how many will be displayed
	//delete extra records off the end
	for($i = ($start - 1); $i <= $c_total_stickies; $i++) {
		unset($tRecs1[$i]);
	}
}
//delete records off the beginning
if ($vars['page'] != 1) {
	for($i = 0; $i < ($start - 1); $i++) {
		unset($tRecs1[$i]);
	}
	//Clark's error fix
	if ($tRecs1 !== false)
	$tRecs1 = array_merge(array(), $tRecs1);
	//reindex
}
if (!empty($tRecs1)) $c_cur_stickies = count($tRecs1);
else $c_cur_stickies = 0;
if ($c_cur_stickies != $_CONFIG['topics_per_page']) {
	if ($vars['page'] == 1) {
		$tRecs2 = $posts_tdb->query('topics', "sticky='0'", $start, $_CONFIG['topics_per_page'] - $c_cur_stickies);
	} elseif($vars['page'] > 1) {
		$tRecs2 = $posts_tdb->query('topics', "sticky='0'", $start - $c_total_stickies, $_CONFIG['topics_per_page'] - $c_cur_stickies);
	}
}
if ($tRecs2 !== FALSE) {
	if (!empty($tRecs1)) $tRecs = array_merge($tRecs1, $tRecs2);
	else $tRecs = $tRecs2;
} elseif(!empty($tRecs1)) $tRecs = $tRecs1;
else $tRecs = array();
if ($vars["cTopics"] <= $_CONFIG["topics_per_page"]) $num_pages = 1;
elseif (($vars["cTopics"] % $_CONFIG["topics_per_page"]) == 0) $num_pages = ($vars["cTopics"] / $_CONFIG["topics_per_page"]);
else $num_pages = ($vars["cTopics"] / $_CONFIG["topics_per_page"]) + 1;
$num_pages = (int) $num_pages;
$p = MiscFunctions::createPageNumbers($vars["page"], $num_pages, $_SERVER['QUERY_STRING']);
require_once('./includes/header.php');
//$_SESSION['newTopics'] = array('lastVisitForums' => array());
//print '<pre>'; print_r($_SESSION['newTopics']['f'.$_GET['id']]); print '</pre>';
$newVisitedTime = (int)$_SESSION['newTopics']['lastVisitForums'][$_GET['id']];
//print "\nfirst: {$newVisitedTime}";
for($i=0,$c=count($tRecs);$i<$c;$i++) {
	if(empty($tRecs[$i])) continue;
	//print "\n{$tRecs[$i]['last_post']} > {$newVisitedTime}";
	if($tRecs[$i]['last_post'] > $newVisitedTime) $newVisitedTime = $tRecs[$i]['last_post'];
	//print "\n{$_SESSION['newTopics']['lastVisitForums'][$_GET['id']]} < {$tRecs[$i]['last_post']} && {$_SESSION['newTopics']['f'.$_GET['id']]['t'.$tRecs[$i]['id']]} != 0";
	if($_SESSION['newTopics']['lastVisitForums'][$_GET['id']] < $tRecs[$i]['last_post']
	&& (!isset($_SESSION['newTopics']['f'.$_GET['id']]['t'.$tRecs[$i]['id']]))) {
		//               && $_SESSION['newTopics']['f'.$_GET['id']]['t'.$tRecs[$i]['id']] != 0)) {
		//print "\nset";
		$_SESSION['newTopics']['f'.$_GET['id']]['t'.$tRecs[$i]['id']] = 1;
	} elseif($tRecs[$i]['sticky'] == 0) break; //Since its sorted, once we find an old topic, they are all old (excluding stickied)
}
//print "\nfinal: {$newVisitedTime}</pre>";
$_SESSION['newTopics']['lastVisitForums'][$_GET['id']] = $newVisitedTime;
if(!empty($_SESSION['newTopics']['f'.$_GET['id']])) while(list($key, $val) = each($_SESSION['newTopics']['f'.$_GET['id']])) {
	//Have to move it here, or else it would be marked unread
	if($val == 0) unset($_SESSION['newTopics']['f'.$_GET['id']][$key]);
}
$tdb->updateVisitedTopics();
echo "<br>";
$posts_tdb->d_topic($p,$vars['page'],$num_pages);

MiscFunctions::echoTableHeading($fRec[0]["forum"], $_CONFIG);
echo "
		<tr>
			<th style='width: 75%;'>Topic</th>
			<th style='width:25%;text-align:center;'>Last Post</th>
		</tr>";
if (empty($tRecs[0]['id'])) {
	echo "
		<tr>
			<td colspan='2' class='area_2' style='text-align:center;font-weight:bold;padding:20px;'>no posts</td>
		</tr>";
} else {
	foreach($tRecs as $tRec) {
		if ($tRec["icon"] != "") {
			$posts_tdb->set_topic(array($tRec));
			if(($tRec['last_post'] > $_SESSION['newTopics']['lastVisitForums'][$_GET['id']] && !isset($_SESSION['newTopics']['f'.$_GET['id']]['t'.$tRec['id']]))) {
				$tRec['icon'] = 'new.gif';
			} elseif(isset($_SESSION['newTopics']['f'.$_GET['id']]['t'.$tRec['id']])) {
				if($_SESSION['newTopics']['f'.$_GET['id']]['t'.$tRec['id']] == 1) {
					$tRec['icon'] = 'new.gif';
				} elseif($_SESSION['newTopics']['f'.$_GET['id']]['t'.$tRec['id']] == 2) {
					$tRec['icon'] = 'star.gif';
				}
			}
			if ($tRec["sticky"] == "1") {
				if ($_CONFIG["sticky_after"] == "1") $tRec["subject"] = "<a href='viewtopic.php?id=".$_GET["id"]."&amp;t_id=".$tRec["id"]."'>".$tRec["subject"]."</a>&nbsp;".stripslashes($_CONFIG["sticky_note"]);
				else $tRec["subject"] = stripslashes($_CONFIG["sticky_note"])."&nbsp;<a href='viewtopic.php?id=".$_GET["id"]."&amp;t_id=".$tRec["id"]."'>".$tRec["subject"]."</a>";
			}
			else $tRec["subject"] = "<a href='viewtopic.php?id=".$_GET["id"]."&amp;t_id=".$tRec["id"]."'>".$tRec["subject"]."</a>";
			settype($tRec["replies"], "integer");
			$total_posts = $tRec["replies"] + 1;

			$num_t_pages = ceil($total_posts / $_CONFIG["posts_per_page"]);
			if ($num_t_pages == 1) {
				$r_ext = "";
			} else {
				$r_ext = "<br /><div class='pagination_small'> Pages: ( ";
				for($m = 1; $m <= $num_t_pages; $m++) {
					$r_ext .= "<a href='viewtopic.php?id=".$_GET["id"]."&amp;t_id=".$tRec["id"]."&page=$m'>$m</a> ";
				}
				$r_ext .= ")</div>";
			}
			if ($tRec["topic_starter"] == "guest") {
				$tRec["topic_starter"] = "<i>a guest</i>";
				$statuscolor = '9d865e';
			} else {
				$user_id = $tdb->basicQuery('users','user_name',$tRec['topic_starter'],1,-1,array('id','level'));
				if($user_id === false) $status_config = array('statuscolor' => '9d865e');
				else
				{
					$status_config = PostingFunctions::status($user_id);
					$statuscolor = $status_config['statuscolor'];
				}
			}
			$user_data = $tdb->get('users', $tRec["user_id"], array('level','posts'));
			if($user_data === false) $status_config = array('statuscolor' => '9d865e');
			else $status_config = PostingFunctions::status($user_data);
			echo "
		<tr>
			<td class='area_2' onmouseover=\"this.className='area_2_over'\" onmouseout=\"this.className='area_2'\">
				<span class='link_1'><a href='xml.php?id=".$_GET["id"]."&amp;t_id=".$tRec["id"]."'><img src='images/rss.png' class='rss' alt='RSS Feed' title='RSS Feed'></a> ".$tRec["subject"].$r_ext."</span>
				<div class='description'>Started By:&nbsp;<span style='color:#".$statuscolor."'>".$tRec["topic_starter"]."</span></div>
				<div class='box_posts'><strong>Views:</strong>&nbsp;".$tRec["views"]."</div>
				<div class='box_posts'><strong>Replies:</strong>&nbsp;".$tRec["replies"]."</div></td>
			<td class='area_1' style='text-align:center;'>
				<img src='".SKIN_DIR."/icons/post_icons/".$tRec["icon"]."' class='post_image' alt=''/>
				<span class='latest_topic'><span class='date'>".gmdate("M d, Y g:i:s a", DateCustom::user_date($tRec["last_post"]))."</span>
				<br />
				<strong>By:</strong> ";
			if ($tRec["user_id"] != "0") echo "<span class='link_2'><a href='profile.php?action=get&amp;id=".$tRec["user_id"]."' style='color : #".$status_config['statuscolor'].";'>".$tRec["user_name"]."</a></span></span></td>
		</tr>";
			else echo "a ".$tRec["user_name"]."</span></td>
		</tr>";
		}
	}
}
MiscFunctions::echoTableFooter(SKIN_DIR);
echo "<br />".$posts_tdb->d_posting(false, false, $p, $vars['page'], $num_pages, 'bottom', 'forum');

require_once('./includes/footer.php');
?>
