<?php
// managetopic.php
// designed for Ultimate PHP Board
// Author: Jerroyd Moore, aka Rebles
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.4.1
require_once("./includes/upb.initialize.php");
require_once("./includes/class/posts.class.php");
$posts_tdb = new posts(DB_DIR."/", "posts.tdb");

if (!isset($_GET["id"]) || !isset($_GET["t_id"]) || $_GET['id'] == '' || $_GET['t_id'] == '' || !ctype_digit($_GET['id']) || !ctype_digit($_GET['id'])) exitPage(str_replace('__TITLE__', 'Invalid ID:', str_replace('__MSG__', 'Cannot retrieve topic information because not enough information was provided.<br />'.ALERT_GENERIC_MSG, ALERT_MSG)), true);
if (!$tdb->is_logged_in()) exitPage(str_replace('__TITLE__', 'Warning:', str_replace('__MSG__', 'You must be <a href="login.php">logged in</a> to view this page.', ALERT_MSG)), true);
if($_GET['action'] == 'favorite') {
	require_once('./includes/header.php');
	$fav = &$_SESSION['newTopics']['f'.$_GET['id']]['t'.$_GET['t_id']];
	if($fav == 2) {
		$fav = 0;
		print str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'This topic has been deleted from your bookmarks.', ALERT_MSG));
	} else {
		$fav = 2;
		print str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'This topic has been bookmarked.', CONFIRM_MSG));
	}
	$tdb->updateVisitedTopics();
	redirect('viewtopic.php?id='.$_GET['id'].'&t_id='.$_GET['t_id'].'&page='.$_GET['page'], 2);
	exit;
} elseif ($_GET["action"] == "watch") {
	$posts_tdb->setFp("topics", $_GET["id"]."_topics");
	$tRec = $posts_tdb->get("topics", $_GET["t_id"]);
	$where = "<a href='viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."'>".$tRec[0]["subject"]."</a> ".$_CONFIG["where_sep"]." Watch Topic";
	require_once('./includes/header.php');
	$user = $tdb->get("users", $_COOKIE["id_env"]);
	if ($tRec[0]["monitor"] == "") {
		$posts_tdb->edit("topics", $_GET["t_id"], array("monitor" => $user[0]["id"]));
		print str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', "You are now monitoring this topic.", CONFIRM_MSG));
	} elseif(FALSE === (strpos($tRec[0]["monitor"], ','))) {
		if ($tRec[0]["monitor"] == $user[0]["id"]) {
			if ($_POST["verify"] == "Ok") {
				$posts_tdb->edit("topics", $_GET["t_id"], array("monitor" => ""));
				//echo "You are no longer monitoring this topic.";
				print str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', "You are no longer monitoring this topic.", CONFIRM_MSG));
			} elseif($_POST["verify"] != "Cancel") {
				ok_cancel($_SERVER['PHP_SELF']."?action=watch&id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&page=".$_GET["page"], "Are you sure you no longer wish to monitor this topic?");
				exitPage('', false, true);
			}
		} else {
			$posts_tdb->edit("topics", $_GET["t_id"], array("monitor" => $tRec[0]["monitor"].",".$user[0]["id"]));
			print str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'You are now monitoring this topic..', CONFIRM_MSG));
		}
	} else {
		$monitor_list = explode(',', $tRec[0]["monitor"]);
		if (in_array($user[0]["id"], $monitor_list)) {
			if ($_POST["verify"] == "Ok") {
				$id_index = array_search($user[0]["id"], $monitor_list);
				unset($monitor_list[$id_index]);
				$monitor_list = implode(",", $monitor_list);
				$posts_tdb->edit("topics", $_GET["t_id"], array("monitor" => $monitor_list));
				print str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'You are no longer monitoring this topic.', CONFIRM_MSG));
			} elseif($_POST["verify"] != "Cancel") {
				ok_cancel($_SERVER['PHP_SELF']."?action=watch&id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&page=".$_GET["page"], "Are you sure you no longer wish to monitor this topic?");
				exitPage('', false, true);
			}
		} else {
			$posts_tdb->edit("topics", $_GET["t_id"], array("monitor" => $tRec[0]["monitor"].",".$user[0]["id"]));
			print str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', "You are now monitoring this topic.", CONFIRM_MSG));
		}
	}
	require_once("./includes/footer.php");
	redirect("viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&page=".$_GET["page"], 2);
	exit;
} elseif($_COOKIE["power_env"] > 1) {
	$posts_tdb->setFp("topics", $_GET["id"]."_topics");
	$posts_tdb->setFp("posts", $_GET["id"]);
	$tRec = $posts_tdb->get("topics", $_GET["t_id"]);
	$where = "<a href='viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."'>".$tRec[0]["subject"]."</a> ".$_CONFIG["where_sep"]." Topic Properties";
	if ($_POST["move_forum"] == "1") {
		require_once('./includes/header.php');
		if ($_COOKIE["power_env"] < 3) {
			print str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'Unable to move or copy this topic, you do not have enough power.', ALERT_MSG));
			require_once("./includes/footer.php");
			redirect($_SERVER['PHP_SELF']."?id=".$_GET["id"]."&t_id=".$_GET["t_id"], 2);
			exit;
		}
		if ($_POST["newId"] != "") {
			if ($_POST["update_date"] == "1") $tRec[0]["last_post"] = mkdate();
			$tRec[0]["subject"] = str_replace($_CONFIG['sticky_note'], "", $tRec[0]["subject"]);
			if ($_POST["sticky_status"] == "1") $tRec[0]["sticky"] = 1;
			else $tRec[0]["sticky"] = 0;
			if ($_POST["closed_status"] == "1") $tRec[0]["locked"] = 1;
			else $tRec[0]["locked"] = 0;

			$p_ids = explode(",", $tRec[0]["p_ids"]);
			$posts_tdb->setfp("newTopics", $_POST["newId"]."_topics");
			$posts_tdb->setFp("newPosts", $_POST["newId"]);
			if ($_POST["action"] == "redirect") $posts_tdb->edit("topics", $_GET["t_id"], array("subject" => "MOVED: ".$tRec[0]["subject"], "locked" => 1, "p_ids" => $p_ids[0]));
			unset($tRec[0]["p_ids"]);
			$newT_id = $posts_tdb->add("newTopics", $tRec[0]);
			$fNRec = $tdb->get("forums", $_POST["newId"]);
			$fNRec[0]['topics'] = (int)$fNRec[0]['topics'] + 1;
			$fNRec[0]['posts']  = (int)$fNRec[0]['posts'];
			if ($_POST["action"] == "redirect" || $_POST["action"] == "move") {
				$fORec = $tdb->get("forums", $_GET["id"]);
				$fORec[0]['posts']  = (int)$fORec[0]['posts'];
			}
			$newSort = array();
			for($i = 0; $i < count($p_ids); $i++) {
				$pRec = $posts_tdb->get("posts", $p_ids[$i]);
				if ($_POST["action"] == "redirect") {
					if ($pRec[0]["id"] == $p_ids[0]) {
						$posts_tdb->edit("posts", $p_ids[0], array(
							"icon" => "icon1.gif",
								"user_name" => $_COOKIE["user_env"],
								"date" => mkdate(),
								"message" => "Topic was moved to forum : ".$fNRec[0]["forum"]."<br /> You should be redirected in 2 seconds.  If not, <a href='viewtopic.php?id=".$_POST["newId"]."&t_id=".$newT_id."'>click here</a>.<meta http-equiv='refresh' content='1;URL=viewtopic.php?id=".$_POST["newId"]."&t_id=".$newT_id."'>",
								"user_id" => $_COOKIE["id_env"]));
					}
					else $posts_tdb->delete("posts", $pRec[0]["id"], false);
				} elseif ($_POST["action"] == "move") {
					$posts_tdb->delete("posts", $pRec[0]["id"], false);
				}
				$pRec[0]["t_id"] = $newT_id;
				$newSort[] = $posts_tdb->add("newPosts", $pRec[0]);
			}
			if ($_POST["action"] == "redirect") {
				$tdb->edit("forums", $_GET["id"], array("posts" => (($fORec[0]["posts"] - count($p_ids)) + 1)));
				$msg = "Successfully moved the topic and replaced the old topic with a redirection.";
			} elseif($_POST["action"] == "copy") {
				$msg = "Successfully copied topic.";
			} elseif($_POST["action"] == "move") {
				$posts_tdb->delete("topics", $_GET["t_id"]);
				$tdb->edit("forums", $_GET["id"], array("topics" => ($fORec[0]["topics"] - 1), "posts" => ($fORec[0]["posts"] - count($p_ids))));
				$msg = "Successfully moved topic.";
			}
			print str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', $msg, CONFIRM_MSG));
			$posts_tdb->edit("newTopics", $newT_id, array("p_ids" => implode(",", $newSort)));
			$posts_tdb->sort("newTopics", "last_post", "DESC");
			$tdb->edit("forums", $_POST["newId"], array("topics" => $fNRec[0]["topics"], "posts" => ($fNRec[0]["posts"] + count($p_ids))));
			require_once("./includes/footer.php");
			if ($_GET["redirect"] != "") redirect($_GET["redirect"], 2);
			else redirect($_SERVER['PHP_SELF']."?id=".$_POST["newId"]."&t_id=$newT_id", 2);
			exit;
		} else {
			$_GET['action'] = '';
			$_POST['action'] = '';
		}
	} elseif($_POST["action"] == "Modify") {
		require_once('./includes/header.php');
		$tNewRec = array();
		if ($_POST["open_forum"] == "0") $tNewRec["locked"] = 0;
		else $tNewRec["locked"] = 1;
		$tNewRec["subject"] = str_replace($_CONFIG['sticky_note'], "", htmlentities(stripslashes($_POST["subject"])));
		if ($_POST["sticky"] == "1") $tNewRec["sticky"] = 1;
		else $tNewRec["sticky"] = 0;
		$tRec[0]["subject"] = str_replace("[Sticky Note]", "", $tRec[0]["subject"]);
		if ($tNewRec["subject"] == $tRec[0]["subject"]) unset($tNewRec["subject"]);
		else
		{
			$p_ids = explode(",", $tRec[0]["p_ids"]);
			$pRec = $posts_tdb->get("posts", $p_ids[0]);
			$posts_tdb->edit("posts", $pRec[0]["id"], array("subject" => htmlentities(stripslashes($tNewRec["subject"]))));
		}
		$posts_tdb->edit("topics", $_GET["t_id"], $tNewRec);
		print str_replace('__TITLE__', 'Redirecting:', str_replace('__MSG__', 'Successfully edited topic properties', CONFIRM_MSG));
		require_once("./includes/footer.php");
		redirect($_SERVER['PHP_SELF']."?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&s=".$s, "2");
		exit;
	} elseif($_POST['action'] == 'Delete Selected') {
		if(!isset($_POST['ids']) || empty($_POST['ids'])) {
			require_once('./includes/header.php');
			print str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'Cannot Delete any posts.<br />'.ALERT_GENERIC_MSG, ALERT_MSG));
		} else {
			if($_POST['verify'] == 'Ok') {
				$posts_tdb->set_topic($tRec);
				$posts_tdb->setFp("topics", $_GET["id"]."_topics");
				$posts_tdb->setFp("posts", $_GET["id"]);

				$ids = unserialize(stripslashes($_POST['ids']));
				$p_ids = explode(',', $tRec[0]['p_ids']);

				foreach($ids as $id) {
					if(FALSE !== ($key = array_search($id, $p_ids))) unset($p_ids[$key]);
					$posts_tdb->delete('posts', $id);
				}
				$tRec = $posts_tdb->get("topics", $_GET["t_id"]);
				$fRec = $tdb->get("forums", $_GET["id"]);

				$replies = $tRec[0]['replies'] - count($ids);

				$result = $posts_tdb->getPosts('posts');
				foreach ($result as $key => $res)
				{
					if ($res['t_id'] != $_GET['t_id'])
					unset($result[$key]);
				}
				$key = count($result) - 1;
				$latest = $result[$key];

				$tdb->edit("forums", $_GET["id"], array("posts" => ((int)$fRec[0]["posts"] - count($ids))));
				$posts_tdb->edit('topics', $_GET['t_id'], array("p_ids" => implode(',', $p_ids),'replies'=>$replies,'last_post'=>$latest['date'],'user_name'=>$latest['user_name'],'user_id'=>$latest['user_id']));

				require_once('./includes/header.php');
				print str_replace('__TITLE__', 'Redirecting:', str_replace('__MSG__', 'Successfully deleted '.count($ids).' post(s).', CONFIRM_MSG));
				include_once './includes/footer.php';
				redirect("{$_SERVER['PHP_SELF']}?id={$_GET['id']}&t_id={$_GET['t_id']}", 2);
			} elseif($_POST['verify'] == 'Cancel') {
				include_once './includes/footer.php';
				redirect("{$_SERVER['PHP_SELF']}?id={$_GET['id']}&t_id={$_GET['t_id']}", 0);
			} else {
				require_once('./includes/header.php');
				ok_cancel("{$_SERVER['PHP_SELF']}?id={$_GET['id']}&t_id={$_GET['t_id']}", '<input type="hidden" name="action" value="'.$_POST['action'].'"><input type="hidden" name="ids" value=\''.serialize($_POST['ids']).'\'>Are you sure that you want to delete these '.count($_POST['ids']).' post(s)?');
			}
		}
	} elseif($_GET["action"] == "CloseTopic" || $_POST["action"] == "CloseTopic") {
		if ($tRec[0]["locked"] == 1) echo "This Topic is already locked!";
		else {
			$posts_tdb->edit("topics", $_GET["t_id"], array("locked" => "1"));
			redirect("viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"], 0);
		}
	} elseif($_GET["action"] == "OpenTopic" || $_POST["action"] == "OpenTopic") {
		if ($tRec[0]["locked"] == 0) echo "This Topic is already unlocked!";
		else
		{
			$posts_tdb->edit("topics", $_GET["t_id"], array("locked" => "0"));
			redirect("viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"], 0);
		}
	} elseif($_POST["action"] == "Delete") {
		require_once('./includes/header.php');
		if ($_POST["verify"] == "Ok") {
			$p_ids = explode(",", $tRec[0]["p_ids"]);
			$ids = explode(",", $ids);
			$count = count($ids);
			$num = 0;

			foreach($ids as $p_id) {
				$posts_tdb->delete("posts", $p_id, false);
				$key = array_search($p_id, $p_ids);
				unset($p_ids[$key]);
				$num++;
				unset($key);
			}

			//$posts_tdb->reBuild("posts");
			$p_ids = implode(",", $p_ids);
			$posts_tdb->edit("topics", $_GET["t_id"], array("p_ids" => $p_ids));
			echo "Successfully deleted ".$num." Post(s)";
			require_once("./includes/footer.php");
			//redirect($_SERVER['PHP_SELF']."?id=".$_GET["id"]."&t_id=".$_GET["t_id"], "2");
			exit;
		} elseif($_POST["verify"] == "Cancel") {
			unset($_POST["action"]);
		} else {
			require_once('./includes/header.php');
			$posts_tdb->set_topic($tRec);
			$pTmpRecs = $posts_tdb->getPosts("posts");
			$pRecs = array();
			$ids = array();
			foreach($pTmpRecs as $pTmpRec) {
				$del = "del_".$pTmpRec["id"];
				if (isset($_POST[$del])) {
					$pRecs[] = $pTmpRec;
					$ids[] = $pTmpRec["id"];
				}
			}
			$ids = implode(",", $ids);
			echo "<b>Are you sure you want to delete the following posts from this topic?</b>";
			echo "";
			echoTableHeading("Manage Topic:", $_CONFIG);
			echo "
			<tr>
				<td width='22%' bgcolor='$header'><font size='$font_m' face='$font_face' color='$font_color_header'>Name</font></td>
				<td width='78%' bgcolor='$header'><font size='$font_m' face='$font_face' color='$font_color_header'>Message</font></td>
			</tr>";
			$x = 1;
			foreach($pRecs as $pRec) {
				$msg = format_text(UPBcoding(filterLanguage($pRec["message"], $_CONFIG)));
				if ($x == 0) {
					$table_color = $table1;

					$x++;
				} else {
					$table_color = $table2;

					$x--;
				}
				echo "
			<tr>
				<td width='22%' valign='top' bgcolor='$table_color'><font size='$font_m' face='$font_face' color='$table_font'><b>".$pRec["user_name"]."</b></td>
				<td width='78%' bgcolor='$table_color'><font size='$font_m' face='$font_face' color='$table_font'>$msg</font></td>
			</tr>";
			}
			echoTableFooter(SKIN_DIR);
			ok_cancel($_SERVER['PHP_SELF']."id=".$_GET["id"]."&t_id=".$_GET["t_id"], "<input type='hidden' name='action' value='Delete'><input type='hidden' name='ids' value ='".$ids."'>");
		}
	}
	if ($_GET["action"] == "" && $_POST["action"] == "") {
		require_once('./includes/header.php');
		if ($tRec[0]["locked"] == 0) {
			$open = "checked";
			$closed = "";
		} else {
			$open = "";
			$closed = "checked";
		}
		if ($tRec[0]["sticky"] == 1) $sticky_checked = "CHECKED";
		else $sticky_checked = "";
		$p_ids = explode(",", $tRec[0]["p_ids"]);
		$pRec = $posts_tdb->get("posts", $p_ids[0]);
		echo "<form method='POST' action='".$_SERVER['PHP_SELF']."?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."'>";
		echoTableHeading("Topic Properties", $_CONFIG);
		echo "
			<tr>
				<td class='area_1' style='width:22%;'><strong>Topic Name: </strong></td>
				<td class='area_2'><input type='text' name='subject' size='20' value='".$tRec[0]["subject"]."'></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Stickied: </strong></td>
				<td class='area_2'><input type='checkbox' name='sticky' value='1' $sticky_checked></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Created on:</strong></td>
				<td class='area_2'>".gmdate("M d, Y g:i:s a", user_date($pRec[0]["date"]))."</td>
			</tr>
			<tr>
				<td class='area_1'><strong>Created by:</strong></td>
				<td class='area_2'><a href='profile.php?action=get&id=".$pRec[0]["user_id"]."'>".$pRec[0]["user_name"]."</font></td>
			</tr>
			<tr>
				<td class='area_1'><strong>No. of replies:</strong></td>
				<td class='area_2'><font size='$font_m' face='$font_face' color='$font_color_main'>".$tRec[0]["replies"]."</font></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Last post on:</strong></td>
				<td class='area_2'>".gmdate("M d, Y g:i:s a", user_date($tRec[0]["last_post"]))."</td>
			</tr>
			<tr>
				<td class='area_1'><strong>Open/Close Status:</strong></td>
				<td class='area_2'><input type='radio' value='0' name='open_forum' id='fp1' $open><label for='fp1'>Open</label>
				<input type='radio' name='open_forum' value='1' id='fp2' $closed><label for='fp2'>Close</label></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type='submit' value='Modify' name='action'><input type='reset' value='Reset' name='B2'></td>
			</tr>
	</form>";
		echoTableFooter(SKIN_DIR);
		echoTableHeading("Topic Options", $_CONFIG);
		if ($_COOKIE["power_env"] >= 3) {
			echo "
			<tr>
				<td class='area_1'><strong>Delete Topic?</strong></td>
				<td class='area_2'><div class='button_pro1'><a href='delete.php?action=delete&t=1&id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&ref=managetopic.php'>X</a></div></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>";
			$options = "
			<tr>
				<td class='area_1' style='width:22%'><strong>Move Topic To: </strong></td><td class='area_2'><select name=newId><option value='' selected>< Select a Forum ></option>";
			$cRecs = $tdb->listRec("cats", 1);
			$cat_sort = explode(",", $_CONFIG["admin_catagory_sorting"]);
			$index = 0;
			$sorted = array();
			$i = 0;
			$max = count($cRecs);
			while ($i < $max) {
				if ($cat_sort[$index] == $cRecs[$i]["id"]) {
					$sorted[] = $cRecs[$i];
					$index++;
					$i = 0;
				}
				else $i++;
			}
			$cRecs = $sorted;
			reset($cRecs);
			$fRecs = $tdb->listRec("forums", 1);
			foreach($cRecs as $cRec) {
				$options .= '<optgroup label="'.$cRec['name'].'">';
				$sort = explode(',', $cRec['sort']);
				for($i=0,$c=count($fRecs);$i<$c;$i++) {
					if(empty($sort)) break;
					if($fRecs[$i]['id'] != $sort[0]) continue;
					array_shift($sort);
					if ($fRecs[$i]["id"] != $_GET["id"]) $options .= "<option value='".$fRecs[$i]["id"]."'>&nbsp;&nbsp;&nbsp;".$fRecs[$i]["forum"]."</option>\n";
					$i=0;
				}
			}
			$options .= "</select></td>
			</tr>";
			echo "
			<form method='POST' action='".$_SERVER['PHP_SELF']."?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."'><input type='hidden' name='move_forum' value='1'>
			$options
			<tr>
				<td class='area_1' style='text-align:right;'><input type='radio' value='redirect' name='action' checked id='fp6'></td>
				<td class='area_2'><label for='fp6'>Move topic and leave a redirect topic in its place</label></td>
			</tr>
			<tr>
				<td class='area_1' style='text-align:right;'><input type='radio' value='copy' name='action' id='fp5'></td>
				<td class='area_2'><label for='fp5'> Copy topic to another forum</label></td>
			</tr>
			<tr>
				<td class='area_1' style='text-align:right;'><input type='radio' value='move' name='action' id='fp4'></td>
				<td class='area_2'><label for='fp4'> Move topic with out leaving a redirect topic</label></td>
			</tr>
			<tr>
				<td class='area_1' style='text-align:right;'><input type='checkbox' name='update_date' value='1' id='fp3'></td>
				<td class='area_2'><label for='fp3'> Update date of the new topic</label></td>
			</tr>
			<tr>
				<td class='area_1' style='text-align:right;'><input type='checkbox' name='closed_status' value='1' id='fp7'></td>
				<td class='area_2'><label for='fp7'> Close the new topic</label></td>
			</tr>
			<tr>
				<td class='area_1' style='text-align:right;'><input type='checkbox' name='sticky_status' value='1' id='fp8'></td>
				<td class='area_2'><label for='fp8'> Sticky note the new topic</label></td>
			</tr>";
		}
		echo "
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type='submit' value='Submit' name='submit2'><input type='reset' value='Reset' name='Reset'></td>
			</tr>
		</form>";
		echoTableFooter(SKIN_DIR);
		$posts_tdb->set_topic($tRec);
		$pRecs = $posts_tdb->getPosts("posts");
		if (count($pRecs) > 1) {
			echo "
		<form method='POST' action='".$_SERVER['PHP_SELF']."?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."'>";
			echoTableHeading("Delete Multiple Posts", $_CONFIG);

			$x = 1;
			unset($pRecs[0]);
			foreach($pRecs as $pRec) {
				$msg = format_text(UPBcoding(filterLanguage($pRec["message"], $_CONFIG)));
				echo "
			<tr>
				<td class='footer_3' colspan='3'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr
			<tr>
				<td class='area_1' style='width:5%;text-align:center;padding:20px;' valign='top'><input type='checkbox' name='ids[]' value='{$pRec["id"]}'></td>
				<td class='area_1' style='width:20%;padding:20px;' valign='top'><span class='link_2'><a href='profile.php?id=".$pRec["user_id"]."'>".$pRec["user_name"]."</a></span></td>
				<td class='area_2' style='width:75%;padding:20px;'>$msg</td>
			</tr>";
			}
			echo "
			<tr>
				<td class='footer_3a' colspan='3' style='text-align:center;'><input type='submit' value='Delete Selected' name='action'\"/></td>
			</tr>
	</form>";
			echoTableFooter(SKIN_DIR); }
	}
} else {
	require_once('./includes/header.php');
	print str_replace('__TITLE__', 'You are not authorized here.', str_replace('__MSG__', ALERT_GENERIC_MSG, ALERT_MSG));
}
require_once("./includes/footer.php");
?>
