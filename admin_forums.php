<?php
/**
 * Administration Panel - Forums
 *
 * @author Tim Hoeppner <tim.hoeppner@gmail.com>
 * @author FixITguy
 *
 *
 */

require_once("./includes/upb.initialize.php");
require_once("./includes/class/upload.class.php");

$where = "<a href='admin.php'>Admin</a> ".$_CONFIG["where_sep"]." <a href='admin_forums.php'>Manage Forums</a>";
require_once('./includes/header.php');
$post_tdb = new functions(DB_DIR, "posts.tdb");
if ($tdb->is_logged_in() && $_COOKIE["power_env"] >= 3) {
	if(!isset($_GET['action'])) $_GET['action'] = '';
	if ($_GET["action"] == "edit_cat") {
		//edit categories
		if (isset($_GET["id"])) {
			if (isset($_POST["u_cat"])) {
				$tdb->edit("cats", $_GET["id"], array("name" => stripslashes($_POST["u_cat"]), "view" => $_POST["u_view"]));
				echo "
						<div class='alert_confirm'>
						<div class='alert_confirm_text'>
						<strong>Redirecting:</div><div style='padding:4px;'>
						Category successfully edited.
						</div>
						</div>";
				redirect($_SERVER['PHP_SELF'], 2);
			} else {
				$cRec = $tdb->get("cats", $_GET["id"]);
				echo "<form action='admin_forums.php?action=edit_cat&id=".$_GET["id"]."' method='POST' name='form'>";
				echoTableHeading("Editing a category", $_CONFIG);
				echo "<input type=\"hidden\" name=\"neworder\" value=\"\">
			<tr>
				<th colspan='2'>&nbsp;</th>
			</tr>
			<tr>
				<td class='area_1' style='width:35%'><strong>Change category's name to</strong></td>
				<td class='area_2'><input type='text' name='u_cat' value='".$cRec[0]["name"]."' size='40'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Who can see this category?</strong></td>
				<td class='area_2'><select size='1' name='u_view'>";
				echo createUserPowerMisc($cRec[0]["view"], 1);
				echo "</select></td>
			</tr>";
				echo "</td></tr><tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr><tr><td class='footer_3a' style='text-align:center;' colspan='2'><input type='submit' value='Submit'></td></tr>";

				echoTableFooter(SKIN_DIR);
				echo "</form>";
			}
		} else {
			echo "No id selected.";
		}
	} elseif($_GET["action"] == "delete_cat") {
		//delete categories
		if (isset($_GET["id"])) {
			if ($_POST["verify"] == "Ok") {
				$sort = explode(",", $_CONFIG['admin_catagory_sorting']);
				if (($i = array_search($_GET["id"], $sort)) !== FALSE) unset($sort[$i]);
				$config_tdb->editVars("config", array("admin_catagory_sorting" => implode(",", $sort)));
					
				$forums = $tdb->query('forums', "cat='{$_GET['id']}'");
				//dump($forums);
				if ($forums !== false) {
					foreach($forums as $forum) {
						$fRec = $tdb->get("forums", $forum["id"]);
						$cRec = $tdb->get("cats", $_GET['id']);
						$posts_tdb = new Posts(DB_DIR."/", "posts.tdb");
						$posts_tdb->setFp("topics", $forum["id"]."_topics");
						$posts_tdb->setFp("posts", $forum["id"]);
						//$posts_tdb->set_topic($tRec);
						$posts_tdb->set_forum($fRec);

						$topic_list = $posts_tdb->listRec('topics',1);
						//dump($topic_list);
						$topics = explode(',',$fRec);
						//dump($topics);
						if (count($topics) > 0)
						{
							foreach ($topic_list as $topic)
							{
								//dump($topics);
								$topic_array[0] = $topic;
								//dump($topic);
								delete_topics($topic_array,$forum["id"]);
							}
						}
						$tdb->delete("forums", $forum["id"]);
						$post_tdb->removeTable($forum["id"]);
						$post_tdb->removeTable($forum["id"]."_topics");
					}
				}
				$tdb->delete("cats", $_GET["id"]);
				$post_tdb->cleanup();
				echo "
						<div class='alert_confirm'>
						<div class='alert_confirm_text'>
						<strong>Redirecting:</div><div style='padding:4px;'>
						Successfully deleted category.
						</div>
						</div>
						";
				redirect($_SERVER['PHP_SELF'], 2);
			} elseif($_POST["verify"] == "Cancel") {
				redirect('admin_forums.php', 0);
			} else {
				ok_cancel("admin_forums.php?action=delete_cat&id=".$_GET["id"], "Are you sure you want to delete this category and all forums in this category?");
			}
		} else {
			echo "No id selected.";
		}
	} elseif($_GET["action"] == "add_cat") {
		//add new category
		if (isset($_POST['command'])) {
			$cat_id = $tdb->add("cats", array("name" => stripslashes($_POST["u_cat"]), "view" => $_POST["u_view"]));
			if($_CONFIG['admin_catagory_sorting'] != '') {
				$sort = $_CONFIG['admin_catagory_sorting'];
				$sort = explode(",",$sort);
				$sort[] = $cat_id;
				$sort = implode(",",$sort);
			} else {
				$sort = $cat_id;
			}
			$config_tdb->editVars('config', array('admin_catagory_sorting' => $sort));
			echo "
					<div class='alert_confirm'>
					<div class='alert_confirm_text'>
					<strong>Successfully added new category:</strong></div><div style='padding:4px;'>
					".$_POST["u_cat"]."
					</div>
					</div>";
			if ($_POST['command'] == 'Add and Add another Category') redirect($_SERVER['PHP_SELF'].'?action=add_cat', 2);
			elseif ($_POST['command'] == 'Add and Add forums to this category') redirect('admin_forums.php?action=add_forum&cat_id='.$cat_id, 2);
			else redirect($_SERVER['PHP_SELF'], 2);
		} else {
			echo "<form action='admin_forums.php?action=add_cat' method=POST>";
			echoTableHeading("Creating a new category", $_CONFIG);
			echo "
			<tr>
				<th colspan='2'>&nbsp;</th>
			</tr>
			<tr>
				<td class='area_1' style='width:20%'><strong>Name of new category</strong></td>
				<td class='area_2'><input type=text name=u_cat size='40'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Who can see the category?</strong></td>
				<td class='area_2'><select size='1' name='u_view'>
					".createUserPowerMisc(0, 1)."</select></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type=submit name='command' value='Add'> <input type=submit name='command' value='Add and Add another Category'> <input type=submit name='command' value='Add and Add forums to this category'></td>
			</tr>";
			echoTableFooter(SKIN_DIR);
			echo "</form>";
		}
	} elseif ($_GET["action"] == "edit_forum") {
		if (isset($_GET["id"])) {
			$fRec = $tdb->get("forums", $_GET["id"]);
			if (isset($_POST["u_forum"])) {
				if ($_POST["cat"] != $fRec[0]["cat"]) {
					$cRec = $tdb->get("cats", $fRec[0]["cat"]);
					$cRec[0]["sort"] = explode(",", $cRec[0]["sort"]);
					$key = array_search($fRec[0]["id"], $cRec[0]["sort"]);
					unset($cRec[0]["sort"][$key]);
					$tdb->edit("cats", $cRec[0]["id"], array("sort" => implode(",", $cRec[0]["sort"])));
					unset($key, $cRec);
					$cRec = $tdb->get("cats", $_POST["cat"]);
					if ($cRec[0]["sort"] != "") $cRec[0]["sort"] .= ",".$fRec[0]["id"];
					else $cRec[0]["sort"] = $fRec[0]["id"];
					$tdb->edit("cats", $_POST["cat"], array("sort" => $cRec[0]["sort"]));
				}
				$tdb->edit("forums", $_GET["id"], array(
						"forum" => stripslashes($_POST["u_forum"]), 
						"cat" => $_POST["cat"], 
						"des" => stripslashes($_POST["des"]), 
						"view" => $_POST["u_view"], 
						"post" => $_POST["u_post"], 
						"reply" => $_POST["u_reply"]));
				echo "
						<div class='alert_confirm'>
						<div class='alert_confirm_text'>
						<strong>Redirecting:</div><div style='padding:4px;'>
						Forum successfully edited.
						</div>
						</div>";
				redirect($_SERVER['PHP_SELF'], 2);
			} else {
				$cRecs = $tdb->listRec("cats", 1);
				$select = "<Select name=cat>\n";
				foreach($cRecs as $cRec) {
					if ($cRec["id"] == $fRec[0]["cat"]) $select .= "<option value='".$cRec["id"]."' selected>".$cRec["name"]."</option>";
					else $select .= "<option value='".$cRec["id"]."'>".$cRec["name"]."</option>";
				}
				$select .= "</select>";
				$whoView = "<select size='1' name='u_view'>".createUserPowerMisc($fRec[0]["view"], 1)."</select>";
				$whoPost = "<select size='1' name='u_post'>".createUserPowerMisc($fRec[0]["post"], 1)."</select>";
				$whoReply = "<select size='1' name='u_reply'>".createUserPowerMisc($fRec[0]["reply"], 1)."</select>";
				echo "<form action='".$_SERVER['PHP_SELF']."?action=edit_forum&id=".$_GET["id"]."' method=POST>";
		  echoTableHeading("Editing a forum", $_CONFIG);
		  echo "
			<tr>
				<th colspan='2'>&nbsp;</th>
			</tr>";
		  echo "
			<tr>
				<td class='area_1' style='width:20%'><strong>Name of forum</strong></td>
				<td class='area_2'><input type=text name=u_forum size='40' maxlength=50 value='".$fRec[0]["forum"]."'></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Parent category</strong></td>
				<td class='area_2'>$select</td>
			</tr>
			<tr>
				<td class='area_1'><strong>Who can see this forum?</strong></td>
				<td class='area_2'>$whoView</td>
			</tr>
			<tr>
				<td class='area_1'><strong>Who can post in this forum?</strong></td>
				<td class='area_2'>$whoPost</td>
			</tr>
			<tr>
				<td class='area_1'><strong>Who can reply in this forum?</strong></td>
				<td class='area_2'>$whoReply</td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Forum description</strong></td>
				<td class='area_2'><textarea cols=30 rows=5 maxlength=105 name=des>".$fRec[0]["des"]."</textarea></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type=submit value='Submit'></td>
			</tr>
		";
		  echoTableFooter(SKIN_DIR);
		  echo "
	   </form>";
			}
		} else {
			echo "No id selected.";
		}
	} elseif($_GET["action"] == "delete_forum") {
		//delete a forum
		if (isset($_GET["id"]))
		{
			if ($_POST["verify"] == "Ok") {
				$fRec = $tdb->get("forums", $_GET["id"]);
				$cRec = $tdb->get("cats", $fRec[0]["cat"]);
				$posts_tdb = new Posts(DB_DIR."/", "posts.tdb");
				$posts_tdb->setFp("topics", $_GET["id"]."_topics");
				$posts_tdb->setFp("posts", $_GET["id"]);
				//$posts_tdb->set_topic($tRec);
				$posts_tdb->set_forum($fRec);
				$sort = explode(",", $cRec[0]["sort"]);
				for($i = 0; $i < count($sort); $i++) {
					if ($sort[$i] == $_GET["id"]) {
						unset($sort[$i]);
						break;
					}
				}
				$sort = implode(",", $sort);

				$topics = $posts_tdb->listRec('topics',1);

				foreach ($topics as $topic)
				{
					$topic_array[0] = $topic;
					delete_topics($topic_array,$_GET['id']);
				}
				 
				$tdb->edit("cats", $cRec[0]["id"], array("sort" => $sort));
				$tdb->delete("forums", $_GET["id"]);
				$post_tdb->removeTable($_GET["id"]);
				$post_tdb->removeTable($_GET["id"]."_topics");
				$post_tdb->cleanup();
				echo "
						<div class='alert_confirm'>
						<div class='alert_confirm_text'>
						<strong>Redirecting:</div><div style='padding:4px;'>
						Successfully deleted forum.
						</div>
						</div>";
				redirect($_SERVER['PHP_SELF'], 2);
			} elseif($_POST['verify'] == "Cancel") {
				redirect('admin_forums.php', 0);
			} else {
				ok_cancel("admin_forums.php?action=delete_forum&id=".$_GET["id"], "Are you sure you want to delete this forum?");
			}
		} else {
			echo "No id selected.";
		}
	} elseif($_GET["action"] == "add_forum") {
		//add new forum
		if (isset($_POST["u_forum"])) {
			$record = array(
				"forum" => stripslashes($_POST["u_forum"]),
					"cat" => $_POST["cat"],
					"view" => $_POST["u_view"],
					"post" => $_POST["u_post"],
					"reply" => $_POST["u_reply"],
					"des" => stripslashes($_POST["des"]),
					"topics" => 0,
					"posts" => 0 );
			$_GET["id"] = $tdb->add("forums", $record);
			$cRec = $tdb->get("cats", $_POST["cat"]);
			if ($cRec[0]["sort"] == "") $sort = $_GET["id"];
			else $sort = $cRec[0]["sort"].",".$_GET["id"];
			$tdb->edit("cats", $_POST["cat"], array("sort" => $sort));
			$post_tdb->createTable($_GET["id"], array(
			array("icon", "string", 10),
			array("user_name", "string", 20),
			array("date", "number", 14),
			array("message", "memo"),
			array("user_id", "number", 7),
			array("t_id", "number", 7),
			array('edited_by', 'string', 20),
			array('edited_by_id', 'number', 7),
			array('edited_date', 'number', 14),
			array("id", "id"),
			array("upload_id", "memo")
			));
			//chown(DB_DIR."/".$_GET["id"].".memo", "nobody");
			//chown(DB_DIR."/".$_GET["id"].".ref", "nobody");
			//chown(DB_DIR."/".$_GET["id"], "nobody");
			$post_tdb->createTable($_GET["id"]."_topics", array(
			array("icon", "string", 10),
			array("subject", "memo"),
			array("topic_starter", "string", 20),
			array("sticky", "number", 1),
			array("replies", "number", 9),
			array("locked", "number", 1),
			array("views", "number", 7),
			array("last_post", "number", 14),
			array("user_name", "string", 20),
			array("user_id", "number", 7),
			array("monitor", "memo"),
			array("p_ids", "memo"),
			array("id", "id")
			), 30);
			//chown(DB_DIR."/".$_GET["id"]."_topics.memo", "nobody");
			//chown(DB_DIR."/".$_GET["id"]."_topics.ref", "nobody");
			//chown(DB_DIR."/".$_GET["id"]."_topics", "nobody");
			echo "
					<div class='alert_confirm'>
					<div class='alert_confirm_text'>
					<strong>Redirecting:</div><div style='padding:4px;'>
					Successfully added new Forum ".$_POST["u_forum"]."
					</div>
					</div>
					";
			if ($_POST['command'] == 'Add and Add another forum to the selected Category') redirect($_SERVER['PHP_SELF'].'?action=add_forum&cat_id='.$_POST['cat'], 2);
			elseif($_POST['command'] == 'Add and Add another forum') redirect($_SERVER['PHP_SELF'].'?action=add_forum', 2);
			else redirect($_SERVER['PHP_SELF'], 2);
		} else {
			$cRecs = $tdb->listRec("cats", 1);
			$select = "<Select name=cat>\n";
			foreach($cRecs as $cat) {
				if (isset($_GET['cat_id']) && $_GET['cat_id'] == $cat['id']) $select .= "<option value='".$cat["id"]."' selected>".$cat["name"]."</option>";
				else $select .= "<option value='".$cat["id"]."'>".$cat["name"]."</option>";
			}
			$select .= "</select>";
			$whoView = "<select size='1' name='u_view'>".createUserPowerMisc(0, 1)."</select>";
			$whoPost = "<select size='1' name='u_post'>".createUserPowerMisc(1, 1)."</select>";
			$whoReply = "<select size='1' name='u_reply'>".createUserPowerMisc(1, 1)."</select>";
			echo "<form action='admin_forums.php?action=add_forum' method=POST>";
			echoTableHeading("Creating a new forum", $_CONFIG);
			echo "
			<tr>
				<th colspan='2'>&nbsp;</th>
			</tr>";
			echo "
			<tr>
				<td class='area_1' style='width:20%'><strong>Name of new forum</strong></td>
				<td class='area_2'><input type=text name=u_forum maxlength=50 size='40'></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Parent category</strong></td>
				<td class='area_2'>$select</td>
			</tr>
			<tr>
				<td class='area_1'><strong>Who can see this forum?</strong></td>
				<td class='area_2'>$whoView</td>
			</tr>
			<tr>
				<td class='area_1'><strong>Who can post in this forum?</strong></td>
				<td class='area_2'>$whoPost</td>
			</tr>
			<tr>
				<td class='area_1'><strong>Who can reply in this forum?</strong></td>
				<td class='area_2'>$whoReply</td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Forum description</strong></td>
				<td class='area_2'><textarea cols=30 rows=5 maxlength=105 name=des></textarea></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type=submit value='Add'> <input type=submit name='command' value='Add and Add another forum' size='10'> <input type=submit name='command' value='Add and Add another forum to the selected Category' size='15'></td>
			</tr>
		";
			echoTableFooter(SKIN_DIR);
			echo "</form>";
		}
	}
	elseif ($_GET['action'] == "shift") {
		if($_GET['what'] == 'cat') {
			$sort = $_CONFIG['admin_catagory_sorting'];
		} elseif($_GET['what'] == 'forum') {
			$fRec = $tdb->get('forums', $_GET['id']);
			$cRec = $tdb->get('cats', $fRec[0]['cat']);
			$sort = $cRec[0]['sort'];
		}
		$sort = explode(',', $sort);
		if(FALSE !== ($index = array_search($_GET['id'], $sort))) {
			if($_GET['where'] == 'up' && $index > 0) {
				$tmp = $sort[$index-1];
				$sort[$index-1] = $sort[$index];
				$sort[$index] = $tmp;
			} elseif($_GET['where'] == 'down' && $index < (count($sort)-1)) {
				$tmp = $sort[$index+1];
				$sort[$index+1] = $sort[$index];
				$sort[$index] = $tmp;
			}
			$sort = implode(',', $sort);
			if($_GET['what'] == 'cat') {
				$config_tdb->editVars('config', array('admin_catagory_sorting' => $sort));
			} elseif($_GET['what'] == 'forum') {
				$tdb->edit('cats', $cRec[0]['id'], array('sort' => $sort));
			}
			redirect('admin_forums.php#skip_nav', 0);
		}

	}
	else {
		//Main
		//
		//
		$cRecs = $tdb->listRec("cats", 1);

		if (empty($cRecs)) redirect('admin_forums.php?action=add_cat', 0);

		// Sort categories in the order that they appear
		$cSorting = explode(",", $_CONFIG["admin_catagory_sorting"]);

		$k = 0;
		$i = 0;
		$sorted = array();
		while ($i < count($cRecs)) {
			if($k >= count($cSorting)) break;
			if ($cSorting[$k] == $cRecs[$i]["id"]) {
				$sorted[] = $cRecs[$i];
				//unset($cRecs[$i]);
				$k++;
				$i = 0;
			} else {
				$i++;
			}
		}
		$cRecs = $sorted;
		unset($sorted, $i, $catdef, $cSorting);
		reset($cRecs);

		#$fRecs = $tdb->listRec("forums", 1);
		#if (empty($fRecs)) redirect('admin_forums.php?action=add_forum', 0);

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
		echo "<a name='skip_nav'>&nbsp;</a>
			<div id='tabstyle_2'>
			    <ul>
			        <li><a href='admin_forums.php?action=add_cat' title='Add a new forum?'><span>Add a new category?</span></a></li>
			        <li><a href='admin_forums.php?action=add_forum' title='Add a new forum?'><span>Add a new forum?</span></a></li>
			    </ul>
			</div>
			<div style='clear:both;'></div><div id='sorting'>";

		echoTableHeading("Forum Control", $_CONFIG);

		echo "
			<tr>
			    <th style='width:7%;'>&nbsp;</th>
				<th style='width:68%;'>Name</th>
				<th style='width:5%;text-align:center;'>View</th>
				<th style='width:5%;text-align:center;'>Post</th>
				<th style='width:5%;text-align:center;'>Reply</th>
				<th style='width:10%;text-align:center;'>Edit?</th>
				<th style='width:10%;text-align:center;'>Delete?</th>
			</tr>";
		if ($cRecs[0]["name"] == "") {
			echo "
			<tr>
				<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='7'>No categories found</td>
			</tr>";
		} else {
			for($i=0,$c1=count($cRecs);$i<$c1;$i++) {
				//show each category
				$view = createUserPowerMisc($cRecs[$i]["view"], 2);
				echo "
			<tr>
			    <td class='area_1' style='padding:8px;text-align:center;'>";
				if ($i>0) {
					echo "<span id='enabled_msg'><a href=\"javascript:forumSort('cat',  'up','".$cRecs[$i]['id']."');\"><img src='./images/up.gif'></a></span>";
					echo "<span id='disabled_msg'><a href='admin_forums.php?action=shift&what=cat&where=up&id=".$cRecs[$i]['id']."'><img src='./images/up.gif'></a></span>";
				}
				else
				echo "&nbsp;&nbsp;&nbsp;";
				if ($i<($c1-1)) {
					echo "<span id='enabled_msg'><a href=\"javascript:forumSort('cat','down','".$cRecs[$i]['id']."');\"><img src='./images/down.gif'></a></span>";
					echo "<span id='disabled_msg'><a href='admin_forums.php?action=shift&what=cat&where=down&id=".$cRecs[$i]['id']."'><img src='./images/down.gif'></a></span>";
				}
				else
				echo "";
				echo "</td>";
				echo "<td class='area_1' style='padding:8px;'><strong>".$cRecs[$i]["name"]."</strong></td>
				<td class='area_1' style='padding:8px;text-align:center;' colspan=3>$view</td>
				<td class='area_1' style='padding:8px;text-align:center;'><a href='admin_forums.php?action=edit_cat&id=".$cRecs[$i]["id"]."'>Edit</a></td>
				<td class='area_1' style='padding:8px;text-align:center;'><a href='admin_forums.php?action=delete_cat&id=".$cRecs[$i]["id"]."'>Delete</a></td>
			</tr>";

				if($cRecs[$i]['sort'] == '') {
					echo "
			<tr>
				<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='7'>No forums exist in this category yet.</td>
			</tr>";
				} else {
					$ids = explode(',', $cRecs[$i]['sort']);
					for($j=0,$c2=count($ids);$j<$c2;$j++) {
						$fRec = $tdb->get('forums', $ids[$j]);
						$post_tdb->setFp("topics", $fRec[0]["id"]."_topics");
						$post_tdb->setFp("posts", $fRec[0]["id"]);
						$whoView = createUserPowerMisc($fRec[0]["view"], 3);
						$whoPost = createUserPowerMisc($fRec[0]["post"], 3);
						$whoReply = createUserPowerMisc($fRec[0]["reply"], 3);
						//show each forum
						echo "
			<tr>
			    <td class='area_2' style='padding:8px;text-align:center;'>";

						if ($j>0) {
							echo "<span id='enabled_msg'><a href=\"javascript:forumSort('forum','up','".$fRec[0]['id']."');\"><img src='./images/up.gif'></a></span>";
							echo "<span id='disabled_msg'><a href='admin_forums.php?action=shift&what=forum&where=up&id=".$fRec[0]['id']."'><img src='./images/up.gif'></a></span>";
						}
						else echo "&nbsp;&nbsp;&nbsp;";

						if ($j<($c2-1)) {
							echo "<span id='enabled_msg'><a href=\"javascript:forumSort('forum','down','".$fRec[0]['id']."');\"><img src='./images/down.gif'></a></span>";
							echo "<span id='disabled_msg'><a href='admin_forums.php?action=shift&what=forum&where=down&id=".$fRec[0]['id']."'><img src='./images/down.gif'></a></span>";

						}
						else echo "&nbsp;&nbsp;&nbsp;";

						echo "</td>
				<td class='area_2' style='padding:8px;'><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$fRec[0]["forum"]."</td>
				<td class='area_2' style='padding:8px;text-align:center;'>$whoView</td>
				<td class='area_2' style='padding:8px;text-align:center;'>$whoPost</td>
				<td class='area_2' style='padding:8px;text-align:center;'>$whoReply</td>
				<td class='area_2' style='padding:8px;text-align:center;'><a href='admin_forums.php?action=edit_forum&id=".$fRec[0]["id"]."'>Edit</a></td>
				<td class='area_2' style='padding:8px;text-align:center;'><a href='admin_forums.php?action=delete_forum&id=".$fRec[0]["id"]."'>Delete</a></td>
			</tr>";
					}
				}
			}
		}
		echoTableFooter(SKIN_DIR);
		echo "</div>";
	}
	require_once("./includes/footer.php");
} else {
	echo "
			<div class='alert'><div class='alert_text'>
			<strong>Access Denied!</strong></div><div style='padding:4px;'>you are not authorized to be here.</div></div>
			<meta http-equiv='refresh' content='2;URL=login.php?ref=admin.php'>";
}
require_once("./includes/footer.php");
?>
