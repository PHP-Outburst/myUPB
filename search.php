<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
/*
 forum list - main.tdb :: forums (forum, cat, view, des, topics, posts, mod, id)
 topic lists - posts.tdb :: [FORUM_ID]_topics (icon, subject, topic_starter, sticky, replies, locked, last_post, user_name, user_id, p_ids, id)
 posts - posts.tdb :: [FORUM_ID] (icon, user_name, date, message, user_id, t_id, id)
 */
require_once('./includes/upb.initialize.php');
$where = "Search";
require_once('./includes/header.php');
$posts_tdb = new functions(DB_DIR.'/', "posts.tdb");
$sText = '';
if (isset($_GET['q'])) $sText = $_GET['q'];
if (!$tdb->is_logged_in()) $_COOKIE["power_env"] = 0;
//build our forum list for selecting which forums to search from
$form_cats = $tdb->listRec("cats", 1);
$form_select = "";
foreach($form_cats as $form_c) {
	if (FALSE !== ($form_forums = $tdb->query("forums", "cat='".$form_c["id"]."'"))) {
		foreach($form_forums as $form_f) {
			if ($form_f["view"] <= $_COOKIE["power_env"]) $form_select .= "<option value='".$form_f["id"]."'>".$form_c["name"]." -&#62; ".$form_f["forum"]."</option>\n";
		}
	}
}
//form
echo "<form action='search.php' method='get'>";
echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
echo "
		<tr>
			<td class='area_1' style='width:40%;text-align:right;'><strong>Search Text:</strong></td>
			<td class='area_2'><input type='text' name='q' size='30' value='".$sText."' /></td>
		</tr>
		<tr>
			<td class='area_1' style='text-align:right;'><strong>Made by User:</strong></td>
			<td class='area_2'><input type='text' name='user' size='30' /></td>
		</tr>
		<tr>
			<td class='area_1' style='text-align:right;'><strong>Require:</strong></td>
			<td class='area_2'><select name='req'>
				<option value='OR'>Any of the words</option>
				<option value='AND' selected='selected'>All of the words</option>
				</select></td>
		</tr>
		<tr>
			<td class='area_1' style='text-align:right;'><strong>Which forums to search:</strong></td>
			<td class='area_2'><select name='forums_req'>
				<option value='all' selected='selected'>All Forums</option>
				$form_select
			</select></td>
		</tr>
		<tr>
			<td class='area_1' style='text-align:right;'><strong>Additional options:</strong></td>
			<td class='area_2'><input type='checkbox' name='intopic' />Search in posts</td>
		</tr>
		<tr>
			<td class='footer_3a' style='text-align:center;' colspan='2'><input type='submit' value='Search' /></td>
		</tr>";
				echoTableFooter(SKIN_DIR);
				echo "</form>";
				//end form
				if (isset($_GET['q']) && trim($_GET['q']) != "" || trim($_GET["q"]) == "" && trim($_GET["user"]) != "") {
					$forums = array();
					$fRecs = $tdb->listRec("forums", 1);
					if ($_GET["forums_req"] == "all") {
						for($i = 0, $fmax = count($fRecs); $i < count($fRecs); $i++) {
							if ($fRecs[$i]["view"] <= $_COOKIE["power_env"]) $forums[] = $fRecs[$i];
						}
					} else {
						for($i = 0, $fmax = count($fRecs); $i < count($fRecs); $i++) {
							if ($fRecs[$i]["view"] <= $_COOKIE["power_env"] && $fRecs[$i]["id"] == $_GET["forums_req"]) $forums[] = $fRecs[$i];
						}
					}
					if (isset($_GET["intopic"])) $intopic = TRUE;
					else $intopic = FALSE;
					$sText = $_GET['q'];
					$sText = str_replace(",", "", $sText);
					$sText = str_replace(".", "", $sText);
					$sText = str_replace(";", "", $sText);
					$sText = str_replace("?", "", $sText);
					$sText = str_replace("\"", "", $sText);
					$sText = str_replace("\'", "", $sText);
					$sText = str_replace("+", "", $sText);
					$sText = str_replace("-", "", $sText);
					$words = explode(" ", $sText);
					$userParam = $_GET["user"];
					$sTopics = array();
					foreach($words as $word) {
						if ($_GET["req"] == "OR" && $userParam != "") $sTopics[] = "subject?'{$word}'&&user_name='{$userParam}'";
						else $sTopics[] = "subject?'{$word}'";
					}
					if ($intopic) {
						$sPosts = array();
						foreach($words as $word) {
							if ($_GET["req"] == "OR" && $userParam != "") $sPosts[] = "message?'{$word}'&&user_name='{$userParam}'";
							else $sPosts[] = "message?'{$word}'";
						}
					}
					if ($_GET['req'] != 'OR') {
						$sTopics = implode("&&", $sTopics);
						if ($userParam != "") $sTopics .= "&&user_name='{$userParam}'";
						if ($intopic) {
							$sPosts = implode("&&", $sPosts);
							if ($userParam != "") $sPosts .= "&&user_name='{$userParam}'";
						}
					} else {
						$sTopics = implode("||", $sTopics);
						if ($intopic) $sPosts = implode("||", $sPosts);
					}
					if (trim($sText) == "" && $userParam != "") {
						$sTopics = "user_name='{$userParam}'";
						if ($intopic) $sPosts = "user_name='{$userParam}'";
					}
					$MAX_TOPIC_RESULTS = 10;
					$MAX_POSTS_RESULTS = 10;
					//query time...
					$result = array();
					foreach($forums as $fRec) {
						//run on each forum
						$posts_tdb->setFp("topics", $fRec["id"]."_topics");
						if (FALSE !== ($r = $posts_tdb->query("topics", $sTopics, 1, $MAX_TOPIC_RESULTS))) {
							$MAX_TOPIC_RESULTS -= count($r);
							$resultTopics[$fRec["id"]]["forumName"] = $fRec["forum"];
							$resultTopics[$fRec["id"]]["catID"] = $fRec["cat"];
							//first 10 results...
							foreach($r as $sRec) {
								$resultTopics[$fRec["id"]]["records"][] = array("topicID" => $sRec["id"], "topicName" => $sRec["subject"]);
							}
						}
						unset($r);
						//dump($sPosts);
						if ($intopic) {
							$posts_tdb->setFp("posts", $fRec["id"]);
							if (FALSE !== ($r = $posts_tdb->query("posts", $sPosts, 1, $MAX_POSTS_RESULTS))) {
								$MAX_POSTS_RESULTS -= count($r);
								$resultPosts[$fRec["id"]]["forumName"] = $fRec["forum"];
								$resultPosts[$fRec["id"]]["catID"] = $fRec["cat"];
								//first 10 results...
								foreach($r as $sRec) {
									//need to get the topic name...
									$topic_query = $posts_tdb->get("topics", $sRec["t_id"]);
									$sRec["topicName"] = $topic_query[0]["subject"];
									$resultPosts[$fRec["id"]]["records"][] = $sRec;
								}
							}
						}
					}
				}
				//Lets query this
				/*
				$resultTopics {
				forumID {
				forumName
				catID
				records {
				index {
				topicID
				topicName [the search text should be bolded, maybe not...]
				}
				}
				}
				}
				$resultPosts {
				forumID {
				forumName
				catID
				records {
				index {
				COMPLETE RESULT
				}
				}
				}
				}
				*/
				//results here
				if (!empty($resultTopics)) {
					echo "<br /><br />";
					echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], "First 10 Results..."), $_CONFIG);
					echo "";
					//while(list($fId, $result) = each($results)) {
					foreach($resultTopics as $fID => $result) {
						if (empty($result)) continue;
						$cRec = $tdb->get('cats', $result["catID"]);
						echo "
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Results in ".$cRec[0]['name']." ".$_CONFIG['table_sep']." <a href=\"viewforum.php?id={$fID}\" target=_blank>{$result['forumName']}</a></strong>:</td>
			</tr>";
						foreach($result["records"] as $topic) {
							echo "
			<tr>
				<td class='area_2' style='padding:8px;'><span class='link_1'><a href='viewtopic.php?id={$fID}&t_id={$topic['topicID']}' target=_blank>{$topic['topicName']}</a></span></td>
			</tr>";
						}
					}
					echoTableFooter(SKIN_DIR);
					flush();
				}
				if (!empty($resultPosts)) {
					echo "<div style='padding:8px;'>Showing the first 10 posts in topic results...</div>";
					$table_color = $table1;

					foreach($resultPosts as $fID => $result) {
						foreach($result["records"] as $post) {
							$msg = format_text(filterLanguage(UPBcoding($post["message"]), $_CONFIG));
							$msg = removeRedirect($msg);
							echo "";
							echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], "Result from: <a href='viewforum.php?id=".$fID."'>".$result["forumName"]."</a> ".$_CONFIG["where_sep"]." <a href='viewtopic.php?id=".$fID."&t_id=".$post["t_id"]."'>".$post["topicName"]."</a>"), $_CONFIG);
							echo "
					<tr>
						<th>Created by: ".$post["user_name"]."</th>
					</tr>";
							echo "
					<tr>
						<td class='area_2'><div style='padding:12px;margin-bottom:20px;'>$msg</div></td>
					</tr>";
							echoTableFooter(SKIN_DIR);
						}
					}
				}
				if (empty($resultTopics) && empty($resultPosts) && isset($_GET["q"]) && strlen(trim($_GET["q"])) > 0) {
					echo "<div class='alert'><div class='alert_text'>
<strong>Search failed!</strong></div><div style='padding:4px;'>......No results found......</div></div>";
				}
				require_once('./includes/footer.php');
				?>