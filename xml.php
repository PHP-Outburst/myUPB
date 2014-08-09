<?php
//XML FEED PAGE
//header("Content-type: text/xml charset=utf8");
require_once("./includes/upb.initialize.php");
$xml = "<?xml version=\"1.0\"?>";
$xml .= "<rss version=\"2.0\"><channel>";

if (!isset($_GET["id"]) || !ctype_digit($_GET["id"])) die("Invalid Forum ID");
//if (!isset($_GET["t_id"]) || !ctype_digit($_GET["t_id"])) die("Invalid Topic ID");

$fRec = $tdb->get("forums", $_GET["id"]);
//if ((int)$_COOKIE["power_env"] < $fRec[0]["view"]) die("You do not have enough Power to view this topic");

$posts_tdb = new Posts(DB_DIR."/", "posts.tdb");
$posts_tdb->setFp("topics", $_GET['id']."_topics");
$posts_tdb->setFp("posts", $_GET["id"]);

$posts_tdb->set_forum($fRec);

if (!isset($_GET['t_id'])) {
	$tRecs = $posts_tdb->listRec('topics', 1, -1);

	$xml .= "<title>".MiscFunctions::xml_clean($fRec[0]['forum'])."</title>
	<link>".MiscFunctions::xml_clean($_SERVER['HTTP_REFERER'])."</link>
	<description>".MiscFunctions::xml_clean($fRec[0]['des'])."</description>
	<language>en-us</language>";
	$posts_tdb->set_topic($tRecs);
	foreach ($tRecs as $key => $tRec) {
		$first_comma = strpos($tRec['p_ids'], ',');
		if($first_comma === false) $first_comma = strlen($tRec['p_ids']);
		$first_post_id = substr($tRec['p_ids'], 0, $first_comma);

		$post = $posts_tdb->get('posts', $first_post_id);
		if($post === false)
		$post = array('message'=>array('Unavailable'));
		$url= "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
		$replace = "viewtopic.php?id=".$_GET['id']."&t_id=".$tRec['id'];
		$newurl = str_replace('xml.php',$replace,$url);
		$xml .= "<item>
		<title>".MiscFunctions::xml_clean($tRec['subject'])."</title>
		<link>".MiscFunctions::xml_clean($newurl)."</link>
		<description>".MiscFunctions::xml_clean(PostingFunctions::format_text(PostingFunctions::filterLanguage($post[0]['message'])))."</description>
		<guid isPermaLink=\"false\">".MiscFunctions::xml_clean($url)."</guid>
		</item>";
	}

	$xml .= "</channel></rss>";

} else {
	$tRecs = $posts_tdb->get("topics", $_GET["t_id"]);
	$desc = $tRecs[0]['subject'];

	$posts_tdb->set_topic($tRecs);
	$pRecs = $posts_tdb->getPosts("posts");
	$url= "http://".$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
	$replace = "viewtopic.php?id=".$_GET['id']."&t_id=".$_GET['t_id'];
	$newurl = str_replace('xml.php',$replace,$url);
	$xml .= "<title>".MiscFunctions::xml_clean($desc)."</title>
	<link>".MiscFunctions::xml_clean($_SERVER['HTTP_REFERER'])."</link>
	<description>Topic of ".MiscFunctions::xml_clean($desc)."</description>
	<language>en-us</language>";

	foreach ($pRecs as $key => $pRec) {
		$newurl = str_replace('xml.php',$replace,$url);
		$newurl .= '&page='.$pRec['page']."#".$pRec['id'];
		$xml .= "<item>
	  <title>Post by ".MiscFunctions::xml_clean($pRec['user_name'])." on ".MiscFunctions::xml_clean(gmdate("M d, Y @ g:i:s a", DateCustom::user_date($pRec["date"])))."</title>
	  <link>".MiscFunctions::xml_clean($newurl)."</link>
	  <description>".MiscFunctions::xml_clean(PostingFunctions::format_text(PostingFunctions::filterLanguage($pRec['message'])))."</description>
	  <guid isPermaLink=\"false\">".MiscFunctions::xml_clean($url)."</guid>
	  </item>";
	}

	$xml .= "</channel></rss>";
}

if(!headers_sent()) {
	header("Content-type:application/rss+xml;charset=utf-8");
	echo $xml;
}
?>
