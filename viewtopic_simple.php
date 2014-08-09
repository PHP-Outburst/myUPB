<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
// Ultimate PHP Board Topic display
require_once('./includes/upb.initialize.php');
require_once('./includes/header_simple.php');
$posts_tdb = new Posts(DB_DIR."/", "posts.tdb");
//check if the id exists
if (!(is_numeric($_GET["id"]) && $posts_tdb->isTable($_GET["id"]))) die("Invalid Id");
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) MiscFunctions::exitPage("Invalid Forum ID", false, true, true);
if (!isset($_GET["t_id"]) || !is_numeric($_GET["t_id"])) MiscFunctions::exitPage("Invalid Topic ID", false, true, true);
$posts_tdb->setFp("topics", $_GET["id"]."_topics");
$posts_tdb->setFp("posts", $_GET["id"]);
$tRec = $posts_tdb->get("topics", $_GET["t_id"]);
$posts_tdb->set_topic($tRec);
$fRec = $tdb->get("forums", $_GET["id"]);
if (!($tdb->is_logged_in())) {
	$posts_tdb->set_user_info("guest", "password", "0", "0");
	$_COOKIE["power_env"] = 0;
}
else $posts_tdb->set_user_info($_COOKIE["user_env"], $_COOKIE["uniquekey_env"], $_COOKIE["power_env"], $_COOKIE["id_env"]);
if ((int)$_COOKIE["power_env"] < $fRec[0]["view"]) MiscFunctions::exitPage("You do not have enough Power to view this topic");
if ($_GET["page"] == "") $_GET["page"] = 1;
$pRecs = $posts_tdb->getPosts("posts", (($_CONFIG["posts_per_page"] * $_GET["page"])-$_CONFIG["posts_per_page"]), $_CONFIG["posts_per_page"]);
$num_pages = ceil(($tRec[0]["replies"] + 1) / $_CONFIG["posts_per_page"]);
if ($pRecs[0]["id"] == "") {
	echo "";
} else {
	if ($num_pages == 1) {
		$p = "<div class='simple_pages'>Pages: $num_pages";
	} else {
		$p = "<div class='simple_pages'>Pages: ";
		for($m = 1; $m <= $num_pages; $m++) {
			if ($_GET["page"] == $m) $p .= "$m &nbsp;";
			else $p .= "<a href='viewtopic_simple.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&page=$m'>$m</a>&nbsp;&nbsp;";
		}
	}
	echo "
			<div id='simple_border'>";
	echo $p;
	$x = +1;
	$y = 0;
	echo "</div>";
	foreach($pRecs as $pRec) {
		// display each post in the current topic
		if ($x == 0) {
			$table_color = area_1;

			$x++;
		} else {
			$table_color = area_2;

			$x--;
		}
		$msg = PostingFunctions::format_text(PostingFunctions::filterLanguage(PostingFunctions::UPBcoding($pRec["message"]), $_CONFIG));
		echo "
				<div class='simple_head' style='text-align:left;'>".$pRec["user_name"]."</div>";
		echo "
				<div class='simple_content'>$msg</div>";
		$y++;
	}
	echo "</div>";
}
require_once('./includes/footer_simple.php');
?>
