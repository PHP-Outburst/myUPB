<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once("./includes/upb.initialize.php");
$post_tdb = new Posts(DB_DIR, "posts.tdb");
$post_tdb->setFp("topics", $_GET["id"]."_topics");
$post_tdb->setFp("posts", $_GET["id"]);
if ($_GET["t"] == 1) $where = "Delete a Topic";
else $where = "Delete a Post";
require_once("./includes/header.php");
if ($tdb->is_logged_in() === false) MiscFunctions::exitPage("<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>You are not logged in, therefore unable to perform this action.</div></div>");
if (!isset($_GET["id"]) || !isset($_GET["t_id"]) || ($_GET["t"] == 0 && !isset($_GET["p_id"]))) MiscFunctions::exitPage("<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>Not enough information to perform this function.</div></div>");
if ($_COOKIE["power_env"] < 2 && $_GET['t'] != 0) MiscFunctions::exitPage("<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>You do not have enough power to delete this topic.</div></div>");
if ($_GET['action'] != "delete") MiscFunctions::exitPage("<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>Unknown Action.  Seek Administrative Help.</div></div>");
$tRec = $post_tdb->get("topics", $_GET["t_id"]);
if ($_GET["t"] == 1) {
	if ($_POST["verify"] == "Ok") {
		if (isset($_GET["t_id"])) {
			$p_ids = explode(",", $tRec[0]["p_ids"]);
			$subtract_user_post_count = array();

			foreach($p_ids as $p_id) {
				$pRec = $post_tdb->get('posts', $p_id);

				$upload_ids = [];
				if($pRec[0]['upload_id']!="") {
					$upload_ids = explode(",",$pRec[0]['upload_id']);
				}

				$upload = new Upload(DB_DIR, $_CONFIG["fileupload_size"],$_CONFIG["fileupload_location"]);

				foreach ($upload_ids as $upload_id)
					$upload->deleteFile($upload_id);

				if (!isset($subtract_user_post_count[$pRec[0]['user_id']])) {
					$subtract_user_post_count[$pRec[0]['user_id']] = 1;
				}
				else $subtract_user_post_count[$pRec[0]['user_id']]++;
				$post_tdb->delete("posts", $p_id, false);
			}

			while (list($user_id, $post_count) = each($subtract_user_post_count)) {
				$user = $tdb->get('users', $user_id);
				$tdb->edit('users', $user_id, array('posts' => (int)$user[0]['posts'] - $post_count));
			}
			$post_tdb->delete("topics", $_GET["t_id"]);
			$fRec = $tdb->get("forums", $_GET["id"]);
			$tdb->edit("forums", $_GET["id"], array("topics" => ((int)$fRec[0]["topics"] - 1), "posts" => ((int)$fRec[0]["posts"] - count($p_ids))));
			echo "
					<div class='alert_confirm'><div class='alert_confirm_text'>
					<strong>Redirecting:</strong></div><div style='padding:4px;'>Successfully deleted \"".$tRec[0]["subject"]."\"(T_ID:".$_GET["t_id"].")<br />from ".$fRec[0]["forum"]." (F_ID:".$_GET["id"].").</div></div>";
			MiscFunctions::redirect("viewforum.php?id=".$_GET["id"], "2");
			exit;
		}
	} elseif($_POST["verify"] == "Cancel") {
		if ($_GET["ref"] == "") $_GET["ref"] = "viewtopic.php";
		MiscFunctions::redirect($_GET["ref"]."?id=".$_GET["id"]."&t_id=".$_GET["t_id"], "0");
	} else {
		MiscFunctions::ok_cancel($_SERVER['PHP_SELF']."?action=".$_GET['action']."&t=".$_GET["t"]."&id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&ref=".$_GET["ref"], "Are you sure you want to delete a topic?");
	}
} elseif($_GET["t"] == 0) {
	$p_ids = explode(",", $tRec[0]["p_ids"]);
	if ($_GET["p_id"] == $p_ids[0]) {
		echo "<div class='alert'><div class='alert_text'>
                <strong>The topic is dependent on the first post, therefore you cannot delete it. The topic must be deleted in order to remove this post.</strong></div><div style='padding:4px;'>
                </div></div>";
	}
	$pRec = $post_tdb->get("posts", $_GET["p_id"]);
	if (!(($pRec[0]["user_id"] == $_COOKIE["id_env"]) || ($_COOKIE["power_env"] >= 2))) MiscFunctions::exitPage("<div class='alert'><div class='alert_text'><strong>You are not authorized to delete this post</strong></div><div style='padding:4px;'></div></div>");

	if ($_POST["verify"] == "Ok") {
		if (($key = array_search($_GET["p_id"], $p_ids)) === FALSE) {
			print "<div class='alert'><div class='alert_text'><strong>Unable to find the post the topic's record.  The post was NOT deleted.</strong></div><div style='padding:4px;'></div></div>";
		} else {


			$upload_ids = explode(",",$pRec[0]['upload_id']);

			$upload = new Upload(DB_DIR, $_CONFIG["fileupload_size"],$_CONFIG["fileupload_location"]);

			foreach ($upload_ids as $upload_id)
			$upload->deleteFile($upload_id);

			$update_topic = array("replies" => ((int)$tRec[0]["replies"] - 1));

			if ($key == (count($p_ids) - 1)) {
				//last post, update last_post of topic
				if (FALSE !== ($last_post = $post_tdb->get('posts', $p_ids[($key - 1)]))) {
					$update_topic['last_post'] = $last_post[0]['date'];
					$update_topic['user_name'] = $last_post[0]['user_name'];
					$update_topic['user_id'] = $last_post[0]['user_id'];
				}
			}
			unset($p_ids[$key]);
			$update_topic["p_ids"] = implode(",", $p_ids);

			$fRec = $tdb->get("forums", $_GET["id"]);
			$tdb->edit("forums", $_GET["id"], array("posts" => ((int)$fRec[0]["posts"] - 1)));
			$post_tdb->edit("topics", $_GET["t_id"], $update_topic);
			$post_tdb->delete("posts", $_GET["p_id"]);
			if ($pRec[0]['user_id'] != 0) {
				$user = $tdb->get('users', $pRec[0]['user_id']);
				$tdb->edit('users', $pRec[0]['user_id'], array('posts' => (int)$user[0]['posts'] - 1));
			}
			echo "<div class='alert_confirm'>
					<div class='alert_confirm_text'>
					<strong>Post Successfully Deleted</strong></div><div style='padding:4px;'>Redirecting user back to thread
					</div>
					</div>";
			require_once("./includes/footer.php");
			MiscFunctions::redirect("viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"], "2");
			exit;
		}
	} elseif($_POST["verify"] == "Cancel") MiscFunctions::redirect("viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"], 0);
	else {
		MiscFunctions::ok_cancel("delete.php?action=".$_GET["action"]."&id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&p_id=".$_GET["p_id"], "Are you sure you want to delete this post?");
	}
} else {
	echo "<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>Corrupt Information.  Seek Administrative Help.</div></div>";
}
require_once("./includes/footer.php");
?>
