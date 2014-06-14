<?php
// Ultimate PHP Board Updater to V2.0
// Author: Jerroyd Moore aka Rebles, R_Rebles, or RxRebles
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.4

set_time_limit(300);
$mt = explode(' ', microtime());
$script_start_time = $mt[0] + $mt[1];

header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// always modified
header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
header ("Pragma: no-cache");

if(isset($_GET['step'])) $step = (int) $_GET['step'];
else $step = 0;
$finish = '';

error_reporting(E_ALL ^ E_NOTICE);
ignore_user_abort();
if($_GET['step'] != 0) {
	if(TRUE !== is_writable('config.php')) die('Unable to continue with the installation process.  "config.php" in the root upb directory MUST BE writable.');
	if(filesize('config.php') > 0) {
		require_once('config.php');
		if(INSTALLATION_MODE === FALSE && (int)$_GET['step'] < 7) {
			$msg = '';
			if(isset($_COOKIE['user_env'])) $msg .= $_COOKIE['user_env'];
			else $msg .= 'Someone';
			if(isset($_COOKIE['id_env'])) $msg .= '(User ID:'.$_COOKIE['id_env'].')';
			if(isset($_SERVER['REMOTE_ADDR'])) $msg .= 'with the IP Address "'.$_SERVER['REMOTE_ADDR'].'"';
			$msg .= 'tried to initiate an installation or upgrade file.  Since the installation and upgrade files pose a risk, this could be an attempted hack.  The administrator, you, were notified.  It is advised you delete installation and upgrade files, which are no long use, and ban the IP Address and username, if given.';
			@mail(ADMIN_EMAIL, 'SECURITY ALERT on your forum', $msg);
			die('<b>Security Risk</b>:  Unable to initiate installation. An Administrater must put the forum in Installation Mode.  You\'re IP Address has been sent to the administrater aswell as login information.');
		}
	} else {
		$f = fopen('config.php', 'w');
		fwrite($f, "<?php
define('INSTALLATION_MODE', true, true);
define('UPB_VERSION', '2.0.0', true);
define('DB_DIR', './db', true);
?>");
		fclose($f);
	}
}

//Begin error.class.php
class errorhandler {
	var $howmany = 0;
	var $error = array();
	var $errortype = array (
	E_ERROR           => "Fatal error",
	E_WARNING         => "Warning",
	E_PARSE           => "Parsing Error",
	E_NOTICE          => "Notice",
	E_CORE_ERROR      => "Core Error",
	E_CORE_WARNING    => "Core Warning",
	E_COMPILE_ERROR	 => "Compile Error",
	E_COMPILE_WARNING => "Compile Warning",
	E_USER_ERROR      => "Fatal error",
	E_USER_WARNING    => "Warning",
	E_USER_NOTICE     => "Notice",
	);

	function add_error($errno, $errstr, $errfile='', $errline='') {
		error_log("<b>".$this->errortype[$error[0]]."</b>: ".$errstr." in file ".$errfile." on <b>line ".$errline."</b><br />", 3, 'error_log');
		if($errno == E_NOTICE) return;
		if(!isset($this->errortype[$errno])) return;
		/*
		 [0]$errno = integer;
		 [1]$errstr = string;
		 [2]$errfile = string;
		 [3]$errline = integer;
		 [4]$errcontext = array;
		 */
		$this->howmany++;
		$this->error[] = array($errno, $errstr, $errfile, $errline);
		if($errno == E_ERROR || $errno == E_USER_ERROR) {
			$this->print_errors();
			exit;
		}
	}

	function add_cError($errno, $errstr, $errfile='', $errline='') {
		$this->error[] = array($errno, $errstr, $errfile, $errline);
		if($errno == E_ERROR || $errno == E_USER_ERROR) {
			$this->print_errors();
			exit;
		}
	}

	function print_errors() {
		$die = false;
		reset($this->error);
		if($this->error_exists()) {
			echo '<table border="0" width="100%" cellspacing="0" cellpadding="7" bordercolor="#000000" style="border-style: solid; border-width: 1">
            <tr><td width="100%"><font face="Verdana" size="2"><b>Advisory:</b> The following errors occured when converting this section of your forum.  Please note that IF you can proceed to the next step, then no major errors occured that would prevent your forum from functioning.</font><br><br>';

			echo '<div class="error">';
			foreach($this->error as $error) {
				echo "<b>".$this->errortype[$error[0]]."</b>: ";
				echo $error[1];
				if($error[3] != '') echo " on line <b>".$error[3]."</b>";
				if($error[2] != '') echo " in file <b>".$error[2]."</b>";
				echo '.<br>';

				if($error[0] == E_ERROR || $error[0] == E_USER_ERROR) $die = true;
			}
			echo '<br><font face="Verdana" size="2">For additional support, Contact UPB Team at <a href="http://www.myupb.com" target="_blank">myupb.com</a> or visit their <a href="http://forum.myupb.com" target="_blank">forums</a></font>';
			echo '</td></tr></table><br>';
			if($die) {
				echo'<script language="Javascript">var flag = 0;</script></body></html>';
				exit;
			}
		}
	}

	function error_exists() {
		if(!empty($this->error[0])) return true;
		return false;
	}

	function return_howmany() {
		return $this->howmany;
	}
}
//end error.class.php

$errorHandler = &new errorhandler();
set_error_handler(array(&$errorHandler, 'add_error'));

?>
<html>
<head>
<title>UPB Updater: Step <?php echo $step; ?> of 8 Completed...</title>
<script language="Javascript">
var flag = 1;
function ConfirmUnload()
{
	if(flag == 1) event.returnValue = "The Updater is not finish and will corrupt your forum data.";
}
function submitonce(theform)
{
	if (document.all||document.getElementById)
	{
		for (i=0;i<theform.length;i++)
		{
			var tempobj=theform.elements[i]
			if(tempobj.type.toLowerCase()=="next &gt;&gt;") tempobj.disabled=true
		}
	}
}

<!--
var counter=0;
function check_submit()
{
	counter++;
	if (counter>1)
	{
		alert("You cannot submit the form again! Please Wait.");
		return false;
	}
}
-->
</script>


<body onbeforeunload="ConfirmUnload();">

<?php
//old textdb functions
function def($rec, $db) {
	$r = "";
	if(!file_exists("$db.def")) return false;
	$f = fopen("$db.def", "r");
	$def = fread($f, filesize("$db.def"));
	fclose($f);

	$def = trim($def);
	$def = explode("<~>", $def);
	$rec = explode("<~>", $rec);

	for($i=0;$i<count($rec);$i++) {
		$r[$def[$i]] = $rec[$i];
	}

	return $r;
}

function listall($db) {
	/*
	 String: $db
	 */
	$r = "";
	$rc = 0;
	if(!file_exists("$db.dat")) return false;
	if(filesize($db.".dat") == 0) return "";
	$f = fopen("$db.dat", "r");
	$a = fread($f, filesize("$db.dat"));
	fclose($f);
	$a = trim($a);
	$a = explode("\n", $a);
	for($i=0;$i<count($a);$i++) {
		if($a[$i] != "") {
			$r[$rc] = $a[$i];
			$rc++;
		}
	}
	return $r;
}

function get($id, $db, $upb='') {
	/*
	 Integer: $id
	 String: $db
	 */
	if(!file_exists("$db.dat")) return false;
	$d = listall($db);
	$a = count($d);
	$howmany = explode("<~>", $d[0]);
	$field = count($howmany)-1;
	$q = "$id";
	for($i=0;$i<=$a;$i++) {
		if(empty($d[$i])) continue;
		@$stuff = explode("<~>", $d[$i]);
		if(@$stuff[$field] == $q) {
			$a = $d[$i];
			if(file_exists("$db.def")) $rec = def($a, $db);
			else $rec = def($a, $upb["def"]);
			break;
		}
	}
	if(!isset($rec)) $rec = false;
	return $rec;
}

function getID($db) {
	if(file_exists($db.".id")) {
		$idfile = file($db.".id");
		$id = trim($idfile[0]);
	} else {
		return false;
	}
	return $id;
}

function undo_format($field) {
	$r = $field;
	$r = str_replace("\n", "", $r);
	$r = str_replace("[NL][NL]", "\n", $r);
	$r = str_replace("[NL]", "\n", $r);
	return $r;
}
//end of old tdb functions

function numberEnding($num) {
	if(substr($num, -1) == 1 && $num != 11) $ending = "st";
	if(substr($num, -1) == 2 && $num != 12) $ending = "nd";
	if(substr($num, -1) == 3 && $num != 13) $ending = "rd";
	if(substr($num, -1) > 3 || substr($num, -1)  == 0) $ending = "th";
	if($num == 11 || $num == 12 || $num == 13) $ending = "th";
	return $ending;
}

// Jan 14, 2008 #2:44:10 pm
$ratio = array("corrupt" => 0, "correct" => 0);
if(function_exists('date_default_timezone_set')) date_default_timezone_set(date('e')); //PHP 5.1.0
function convertTimeString($timeString, $format="", &$ratio) {
	$months = array("Jan" => 1, "Feb" => 2, "Mar" => 3, "Apr" => 4, "May" => 5, "Jun" => 6, "Jul" => 7, "Aug" => 8, "Sep" => 9, "Oct" => 10, "Nov" => 11, "Dec" => 12);
	$timeString = trim($timeString);
	if($format == "user" || $format == "users") {
		if(FALSE === strpos($timeString, '-')) {
			$ratio['correct']++;
			return time();
		}
		$arr = explode("-", $timeString);
		$year = $arr[0];
		$month = $arr[1];
		$day = $arr[2];
		$hour = 0;
		$minute = 0;
		$second = 0;
	} else {
		$month = $months[substr($timeString, 0, 3)];
		$day = substr($timeString, 4, 2);
		$year = substr($timeString, 8, 4);
		$hour = trim(substr($timeString, -11, 2));
		if(substr($timeString, -2, 2) == "pm") $hour += 12;
		$minute = substr($timeString, -8, 2);
		$second = substr($timeString, -5, 2);
	}
	$date = @mktime($hour, $minute, $second, $month, $day, $year);

	if($timeString == @date("M d, Y g:i:s a", $date)) $ratio["correct"]++;
	elseif(!($format == "user" || $format == "users")) $ratio["corrupt"]++;
	return $date;
}

//debugging purposes..
if(isset($_GET['debugging'])) {
	define('DEBUGGING_MODE', TRUE, true);
	$debug_to_file_resource = fopen('debugging_log', 'w');
} else define('DEBUGGING_MODE', FALSE, true);
require_once('./includes/debug_print_backtrace.php');

if($step == 0) {
	//Intro, don't execute anything important here!!
	//Future: Maybe add a verification here, to make sure there are no unauthorized entries ?>
<p>Thank you for choosing to upgrade your Ultimate PHP Board to Version
2.0! Version 2.0 offers greater security than before. UPB 2, powered by
the new Text Database class loads your pages in a fraction of a second.</p>
<p>Before continueing, be sure to note the following so that the upgrade
transition is smooth and effortless:</P>
<ul>
	<li>Be sure that you have backed up your db folder</li>
	<li>CHMOD the config.php in the root UPB directory to 666</li>
	<li>CHMOD the db folder (and all files and folders inside, including
	files inside folders) in the root UPB directory to 777</li>
	<li>CHMOD each skin's folder, located in the skins folder (ex:
	/upb/skins/default/), to 777</li>
</ul>
	<?php
} elseif($step == 1) {
	//Create Necessary Directories

	if(@(!is_dir("./db/"))) die("<script language=\"JavaScript\">var flag = 0;</script>Unable to proceed with the update.  Cause: <b>\"./db/\" does NOT exist.</b>");
	if(@(!is_dir("./db/backup/"))) {
		if(@(!mkdir("./db/backup", 0777))) die("<script language=\"JavaScript\">var flag = 0;</script>Unable to create and chmod directory <b>./db/backup/</b>, please do this manually.");
	}

	if(!is_writable("./db/")) die("<script language=\"JavaScript\">var flag = 0;</script>Make sure the folder <b>./db/</b> is chmoded to 0777, the upgrade cannot continue until it can write in these folders.");
	if(!is_readable("./db/")) die("<script language=\"JavaScript\">var flag = 0;</script>Make sure the folder <b>./db/</b> is chmoded to 0777, the upgrade cannot continue until it can read in these folders.");
	//if(!is_writable("./backup/")) die("<script language=\"JavaScript\">var flag = 0;</script>Make sure the folder <b>./backup/</b> is chmoded to 0777, the upgrade cannot continue until it can write in these folders.");
	//if(!is_readable("./backup/")) die("<script language=\"JavaScript\">var flag = 0;</script>Make sure the folder <b>./backup/</b> is chmoded to 0777, the upgrade cannot continue until it can read in these folders.");
	echo "Checking if Directories are created and accessable...Done!";
} elseif($step == 2) {
	//Create TextDBs & necessary Tables

	require_once('./includes/class/tdb.class.php');
	$tdb = new tdb("", "");
	$tdb->createDatabase("./db/", "main.tdb");
	$tdb->tdb("./db/", "main.tdb");

	$_PrivMsg["tdb"] = new tdb("", "");
	$_PrivMsg["tdb"]->createDatabase("./db/", "privmsg.tdb");
	$_PrivMsg["tdb"]->tdb("./db/", "privmsg.tdb");

	$tdb->createDatabase("./db/", "posts.tdb");

	$tdb->createTable("members", array(
	array("user_name", "string", 20),
	array("password", "string", 49),
	array("uniquekey", "string", 32),
	array("level", "number", 1),
	array("email", "memo"),
	array("view_email", "number", 1),
	array("mail_list", "number", 1),
	array("status", "memo"),
	array("location", "memo"),
	array("url", "memo"),
	array("avatar", "memo"),
	array("avatar_height", "number", 3),
	array("avatar_width", "number", 3),
	array("avatar_hash", "string", 32),
	array("icq", "number", 20),
	array("aim", "string", 24),
	array("yahoo", "memo"),
	array("msn", "memo"),
	array("sig", "memo"),
	array("posts", "number", 7),
	array("date_added", "number", 14),
	array("timezone", "string", 3),
	array("id", "id")
	), 20);
	$tdb->createTable("forums", array(
	array("forum", "memo"),
	array("cat", "number", 7),
	array("view", "number", 1),
	array("post", "number", 1),
	array("reply", "number", 1),
	array("des", "memo"),
	array("topics", "number", 7),
	array("posts", "number", 7),
	array("id", "id")
	), 20);
	$tdb->createTable("categories", array(
	array("name", "memo"),
	array("sort", "memo"),
	array("view", "number", 1),
	array("id", "id")
	), 20);

	if(!($user_id = getID("./db/users"))) die("Unable to retrieve User IDs");
	$user_id = (int)$user_id;
	$num_of_pm_tb = ceil($user_id/120) + 1;
	for($pm_tb=1;$pm_tb<$num_of_pm_tb;$pm_tb++) {
		$_PrivMsg["tdb"]->createTable($pm_tb, array(
		array("box", "string", 6),
		array("from", "number", 7),
		array("to", "number", 7),
		array("icon", "string", 10),
		array("subject", "memo"),
		array("date", "number", 14),
		array("message", "memo"),
		array("id", "id")
		));
	}
	$f = fopen("./db/blockedlist.dat", "w");
	fwrite($f, "");
	fclose($f);

	$tdb->createTable("getpass", array(
	array("passcode_HASH", "string", 49),
	array("time", "number", 14),
	array("user_id", "number", 7),
	array("id", "id")
	), 30);
	$tdb->createTable("config", array(
	array("name", "memo"),
	array("value", "memo"),
	array("type", "string", 6),
	array("id", "id"),
	), 20);

	$tdb->createTable("ext_config", array(
	array("name", "memo"),
	array("value", "memo"),
	array("type", "string", 6),
	array("title", "memo"),
	array("description", "memo"),
	array("form_object", "string", 8),
	array("data_type", "string", 7),
	array("minicat", "number", 2),
	array("sort", "number", 2),
	array("id", "id")
	), 20);

	$tdb->setFp("config", "config");
	$tdb->setFp("ext_config", "ext_config");

	require_once("./db/config.dat");
	require_once("./db/config2.dat");
	@include "./db/config_pm.dat";

	$config_file = rtrim(file_get_contents('config.php'), '?>');
	$config_file .= "\ndefine('ADMIN_EMAIL', '".$admin_email."', true);\n?>";
	$f = fopen('config.php', 'w');
	fwrite($f, $config_file);
	fclose($f);
	unset($config_file);

	$f = fopen('./db/constants.php', 'w');
	fwrite($f, 'define("TABLE_WIDTH_MAIN", $_CONFIG["table_width_main"], true);
define("SKIN_DIR", $_CONFIG["skin_dir"], true);');

	$f = fopen("./db/config_org.dat", 'w');
	fwrite($f,
    "config".chr(30)."General Settings".chr(31).
    "status".chr(30)."Members' Statuses".chr(31).
    "regist".chr(30)."Newly Registered Users".chr(31).
	//.type.chr(30).type name.chr(31)
	chr(29).
    "config".chr(30)."1".chr(30)."Main Forum Config".chr(31).
    "status".chr(30)."2".chr(30)."Member status".chr(31).
    "status".chr(30)."3".chr(30)."Moderator status".chr(31).
    "status".chr(30)."4".chr(30)."Admin Status".chr(31).
    "status".chr(30)."5".chr(30)."Member status Colors".chr(31).
    "status".chr(30)."6".chr(30)."Who's Online User Colors".chr(31).
    "regist".chr(30)."7".chr(30)."New Users' Confirmation E-mail".chr(31).
    "regist".chr(30)."8".chr(30)."Users' Avatars".chr(31));
	//.type.chr(30).minicat.chr(30).cat name.chr(31)
	fclose($f);

	//$_CONFIG
	$tdb->add("ext_config", array("name" => "ver", "value" => '2.0B1.4', "type" => "config", "form_object" => "hidden", "data_type" => "string"));
	$tdb->add("config", array("name" => "ver", "value" => '2.0B1.4', "type" => "config"));
	$tdb->add("ext_config", array("name" => "title", "value" => $title, "type" => "config", "title" => "Title", "description" => "Title of the forum", "form_object" => "text", "data_type" => "string", "minicat" => "1", "sort" => "1"));
	$tdb->add("config", array("name" => "title", "value" => $title, "type" => "config"));
	$tdb->add("ext_config", array("name" => "table_width_main", "value" => $table_width_main, "type" => "config", "title" => "Table Width", "description" => "This will change the table width of the main section of the forums", "form_object" => "text", "data_type" => "string", "minicat" => "1", "sort" => "10"));
	$tdb->add("config", array("name" => "table_width_main", "value" => $table_width_main, "type" => "config"));
	$tdb->add("ext_config", array("name" => "posts_per_page", "value" => $posts_per_page, "type" => "config", "title" => "Posts Per Page", "description" => "this is how many posts will be displays on each page for topics",  "form_object" => "text", "data_type" => "number", "minicat" => "1", "sort" => "4"));
	$tdb->add("config", array("name" => "posts_per_page", "value" => $posts_per_page, "type" => "config"));
	$tdb->add("ext_config", array("name" => "topics_per_page", "value" => $topics_per_page, "type" => "config", "title" => "Topics per Page", "description" => "this is how many topics will be displays on each page for forums", "form_object" => "text", "data_type" => "number", "minicat" => "1", "sort" => "5"));
	$tdb->add("config", array("name" => "topics_per_page", "value" => $topics_per_page, "type" => "config"));
	$tdb->add("ext_config", array("name" => "logo", "value" => $logo, "type" => "config", "title" => "Logo Location", "description" => "can be relative or a url", "form_object" => "text", "data_type" => "string", "minicat" => "1", "sort" => "2"));
	$tdb->add("config", array("name" => "logo", "value" => $logo, "type" => "config"));
	$tdb->add("ext_config", array("name" => "homepage", "value" => $homepage, "type" => "config", "title" => "Homepage URL", "description" => "can be relative or a url", "form_object" => "text", "data_type" => "string", "minicat" => "1", "sort" => "3"));
	$tdb->add("config", array("name" => "homepage", "value" => $homepage, "type" => "config"));

	$tdb->add("ext_config", array("name" => "admin_catagory_sorting", "value" => $admin_catagory_sorting, "type" => "config", "title" => "Catagory Sorting", "description" => "Put the id numbers of the catagories that you want sorted; Seperate with commas. (I.E. 1,2,3,4)", "form_object" => "text", "data_type" => "string", "minicat" => "1", "sort" => "16"));
	$tdb->add("config", array("name" => "admin_catagory_sorting", "value" => $admin_catagory_sorting, "type" => "config"));
	$tdb->add("ext_config", array("name" => "servicemessage", "value" => $servicemessage, "type" => "config", "title" => "Service Messages", "description" => "Service Messages appear above the forum, if nothing input, Announcements will not be displayed. Html is allowed.", "form_object" => "textarea", "data_type" => "string", "minicat" => "1", "sort" => "17"));
	$tdb->add("config", array("name" => "servicemessage", "value" => $servicemessage, "type" => "config"));

	$tdb->add("ext_config", array("name" => "skin_dir", "value" => substr($skin_images_dir, 0, -7), "type" => "config", "title" => "Skin Directory", "description" => "leave it unless you upload another skin", "form_object" => "text", "data_type" => "string", "minicat" => "1", "sort" => "12"));
	$tdb->add("config", array("name" => "skin_dir", "value" => substr($skin_images_dir, 0, -7), "type" => "config"));

	$tdb->add("ext_config", array("name" => "fileupload_location", "value" => $fileupload_location, "type" => "config", "title" => "Location for file attachments", "description" => "Put the path to the directory for file attachments.<br>e.g. If your forums are located at http://forum.myupb.com, and your uploads directory is at http://forum.myupb.com/uploads, you would simply put 'uploads' (without quotes) in the box.", "form_object" => "text", "data_type" => "number", "minicat" => "1", "sort" => "6"));
	$tdb->add("config", array("name" => "fileupload_location", "value" => $fileupload_location, "type" => "config"));
	$tdb->add("ext_config", array("name" => "fileupload_size", "value" => $fileupload_size, "type" => "config", "title" => "Size limits for file upload", "description" => "In kilobytes, type in the maximum size allowed for file uploads", "form_object" => "text", "data_type" => "number", "minicat" => "1", "sort" => "7"));
	$tdb->add("config", array("name" => "fileupload_size", "value" => $fileupload_size, "type" => "config"));
	$tdb->add("ext_config", array("name" => "censor", "value" => $censor, "type" => "config", "title" => "Word to replace bad words", "description" => "Words that will replace bad words in a post", "form_object" => "text", "data_type" => "string", "minicat" => "1", "sort" => "13"));
	$tdb->add("config", array("name" => "censor", "value" => $censor, "type" => "config"));
	$tdb->add("ext_config", array("name" => "sticky_note", "value" => "[Stick Note]", "type" => "config", "title" => "Sticky Note Text", "description" => "Text that appends the title indicating it is a \"Stickied Topic\" (HTML Tags Allowed)", "form_object" => "text", "data_type" => "string", "minicat" => "1", "sort" => "14"));
	$tdb->add("config", array("name" => "sticky_note", "value" => "[Stick Note]", "type" => "config"));
	$tdb->add("ext_config", array("name" => "sticky_after", "value" => "1", "type" => "config", "title" => "Sticky Note Before or After Title", "description" => "If this is checked, the \"sticky note\" text will appear after the title.  Unchecking this will display it before the title.", "form_object" => "checkbox", "minicat" => "1", "sort" => "15"));
	$tdb->add("config", array("name" => "sticky_after", "value" => "1", "type" => "config"));

	$tdb->add("ext_config", array("name" => "pm_max_outbox_msg", "value" => $pm_max_outbox_msg, "type" => "config", "title" => "Max Number of Private Msgs in a Users OutBox", "description" => "Can be set to 0 to infinity", "form_object" => "text", "data_object" => "number", "minicat" => "1", "sort" => "18"));
	$tdb->add("config", array("name" => "pm_max_outbox_msg", "value" => $pm_max_outbox_msg, "type" => "config"));
	$tdb->add("ext_config", array("name" => "pm_version", "value" => $pm_version, "type" => "config", "form_object" => "hidden", "data_type" => "string"));
	$tdb->add("config", array("name" => "pm_version", "value" => $pm_version, "type" => "config"));

	$tdb->add("ext_config", array("name" => "avatar_width", "value" => $avatar_width, "type" => "config", "title" => "Avatars' Width", "description" => "The width (with respect to the height) of user avatars you want to be displayed at in pixels (Cannot be higher than 999)", "form_object" => "text", "data_object" => "number", "minicat" => "1", "sort" => "8"));
	$tdb->add("config", array("name" => "avatar_width", "value" => $avatar_width, "type" => "config"));
	$tdb->add("ext_config", array("name" => "avatar_height", "value" => $avatar_height, "type" => "config", "title" => "Avatars' Height", "description" => "The height (with respect to the width) of user avatars you want to be displayed at in pixels (Cannot be higher than 999)", "form_object" => "text", "data_object" => "number", "minicat" => "1", "sort" => "9"));
	$tdb->add("config", array("name" => "avatar_height", "value" => $avatar_height, "type" => "config"));

	$tdb->add("ext_config", array("name" => "Create List", "value" => "more_smilies_create_list.php", "type" => "config", "title" => "Adding More Smilies", "description" => "Click on the link if you have recently added more smilies to your <b>moresmilies</b> directory", "form_object" => "link", "minicat" => "1", "sort" => "11"));
	$tdb->add("config", array("name" => "Create List", "value" => "more_smilies_create_list.php", "type" => "config"));

	//$_REGISTER
	$tdb->add("ext_config", array("name" => "register_sbj", "value" => $register_sbj, "type" => "regist", "title" => "Register Email Subject", "description" => "this is the subject for confirmation of registration", "form_object" => "text", "data_type" => "string", "minicat" => "7", "sort" => "2"));
	$tdb->add("config", array("name" => "register_sbj", "value" => $register_sbj, "type" => "regist"));
	$tdb->add("ext_config", array("name" => "register_msg", "value" => $register_msg, "type" => "regist", "title" => "Register Email Message", "description" => "this is the message for confirmation of registration (options: &lt;login&gt; &lt;password&gt;)", "form_object" => "textarea", "data_type" => "string", "minicat" => "7", "sort" => "3"));
	$tdb->add("config", array("name" => "register_msg", "value" => $register_msg, "type" => "regist"));
	$tdb->add("ext_config", array("name" => "admin_email", "value" => $admin_email, "type" => "regist", "title" => "Admin E-mail", "description" => "this is the return address for confirmation of registration", "form_object" => "text", "data_type" => "string", "minicat" => "7", "sort" => "1"));
	$tdb->add("config", array("name" => "admin_email", "value" => $admin_email, "type" => "regist"));
	$tdb->add("ext_config", array("name" => "avatar1", "value" => $avatar1, "type" => "regist", "title" => "Avatar 1", "description" => "The first avatar on the selection menu for new users", "form_object" => "text", "data_type" => "string", "minicat" => "8", "sort" => "2"));
	$tdb->add("config", array("name" => "avatar1", "value" => $avatar1, "type" => "regist"));
	$tdb->add("ext_config", array("name" => "avatar2", "value" => $avatar2, "type" => "regist", "title" => "Avatar 2", "description" => "The second avatar on the selection menu for new users", "form_object" => "text", "data_type" => "string", "minicat" => "8", "sort" => "3"));
	$tdb->add("config", array("name" => "avatar2", "value" => $avatar2, "type" => "regist"));
	$tdb->add("ext_config", array("name" => "avatar3", "value" => $avatar3, "type" => "regist", "title" => "Avatar 3", "description" => "The third avatar on the selection menu for new users", "form_object" => "text", "data_type" => "string", "minicat" => "8", "sort" => "4"));
	$tdb->add("config", array("name" => "avatar3", "value" => $avatar3, "type" => "regist"));
	$tdb->add("ext_config", array("name" => "avatar4", "value" => $avatar4, "type" => "regist", "title" => "Avatar 4", "description" => "The fourth avatar on the selection menu for new users", "form_object" => "text", "data_type" => "string", "minicat" => "8", "sort" => "5"));
	$tdb->add("config", array("name" => "avatar4", "value" => $avatar4, "type" => "regist"));
	$tdb->add("ext_config", array("name" => "avatar5", "value" => $avatar5, "type" => "regist", "title" => "Avatar 5", "description" => "The fifth avatar on the selection menu for new users", "form_object" => "text", "data_type" => "string", "minicat" => "8", "sort" => "6"));
	$tdb->add("config", array("name" => "avatar5", "value" => $avatar5, "type" => "regist"));
	$tdb->add("ext_config", array("name" => "avatar6", "value" => $avatar6, "type" => "regist", "title" => "Avatar 6", "description" => "The sixth avatar on the selection menu for new users", "form_object" => "text", "data_type" => "string", "minicat" => "8", "sort" => "7"));
	$tdb->add("config", array("name" => "avatar6", "value" => $avatar6, "type" => "regist"));
	$tdb->add("ext_config", array("name" => "avatar7", "value" => $avatar7, "type" => "regist", "title" => "Avatar 7", "description" => "The seventh avatar on the selection menu for new users", "form_object" => "text", "data_type" => "string", "minicat" => "8", "sort" => "8"));
	$tdb->add("config", array("name" => "avatar7", "value" => $avatar7, "type" => "regist"));
	$tdb->add("ext_config", array("name" => "avatar8", "value" => $avatar8, "type" => "regist", "title" => "Avatar 8", "description" => "The eighth avatar on the selection menu for new users", "form_object" => "text", "data_type" => "string", "minicat" => "8", "sort" => "9"));
	$tdb->add("config", array("name" => "avatar8", "value" => $avatar8, "type" => "regist"));
	$tdb->add("ext_config", array("name" => "avatar9", "value" => $avatar9, "type" => "regist", "title" => "Avatar 9", "description" => "The nineth avatar on the selection menu for new users", "form_object" => "text", "data_type" => "string", "minicat" => "8", "sort" => "10"));
	$tdb->add("config", array("name" => "avatar9", "value" => $avatar9, "type" => "regist"));
	$tdb->add("ext_config", array("name" => "newuseravatars", "value" => $newuseravatars, "type" => "regist", "title" => "Avatars for new users", "description" => "Would you like to define the avatars that members under 50 posts can use? (After 50 posts they may use whatever they like)", "form_object" => "checkbox", "minicat" => "8", "sort" => "1"));
	$tdb->add("config", array("name" => "newuseravatars", "value" => $newuseravatars, "type" => "regist"));

	//$_STATUS
	$tdb->add("ext_config", array("name" => "member_status1", "value" => $member_status1, "type" => "status", "title" => "Member post status 1", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "2", "sort" => "1"));
	$tdb->add("config", array("name" => "member_status1", "value" => $member_status1, "type" => "status"));
	$tdb->add("ext_config", array("name" => "member_status2", "value" => $member_status2, "type" => "status", "title" => "Member post status 2", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "2", "sort" => "3"));
	$tdb->add("config", array("name" => "member_status2", "value" => $member_status2, "type" => "status"));
	$tdb->add("ext_config", array("name" => "member_status3", "value" => $member_status3, "type" => "status", "title" => "Member post status 3", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "2", "sort" => "5"));
	$tdb->add("config", array("name" => "member_status3", "value" => $member_status3, "type" => "status"));
	$tdb->add("ext_config", array("name" => "member_status4", "value" => $member_status4, "type" => "status", "title" => "Member post status 4", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "2", "sort" => "7"));
	$tdb->add("config", array("name" => "member_status4", "value" => $member_status4, "type" => "status"));
	$tdb->add("ext_config", array("name" => "member_status5", "value" => $member_status5, "type" => "status", "title" => "Member post status 5", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "2", "sort" => "9"));
	$tdb->add("config", array("name" => "member_status5", "value" => $member_status5, "type" => "status"));
	$tdb->add("ext_config", array("name" => "mod_status1", "value" => $mod_status1, "type" => "status", "title" => "Moderator post status 1", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "3", "sort" => "1"));
	$tdb->add("config", array("name" => "mod_status1", "value" => $mod_status1, "type" => "status"));
	$tdb->add("ext_config", array("name" => "mod_status2", "value" => $mod_status2, "type" => "status", "title" => "Moderator post status 2", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "3", "sort" => "3"));
	$tdb->add("config", array("name" => "mod_status2", "value" => $mod_status2, "type" => "status"));
	$tdb->add("ext_config", array("name" => "mod_status3", "value" => $mod_status3, "type" => "status", "title" => "Moderator post status 3", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "3", "sort" => "5"));
	$tdb->add("config", array("name" => "mod_status3", "value" => $mod_status3, "type" => "status"));
	$tdb->add("ext_config", array("name" => "mod_status4", "value" => $mod_status4, "type" => "status", "title" => "Moderator post status 4", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "3", "sort" => "7"));
	$tdb->add("config", array("name" => "mod_status4", "value" => $mod_status4, "type" => "status"));
	$tdb->add("ext_config", array("name" => "mod_status5", "value" => $mod_status5, "type" => "status", "title" => "Moderator post status 5", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "3", "sort" => "9"));
	$tdb->add("config", array("name" => "mod_status5", "value" => $mod_status5, "type" => "status"));
	$tdb->add("ext_config", array("name" => "admin_status1", "value" => $admin_status1, "type" => "status", "title" => "Admin post status 1", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "4", "sort" => "1"));
	$tdb->add("config", array("name" => "admin_status1", "value" => $admin_status1, "type" => "status"));
	$tdb->add("ext_config", array("name" => "admin_status2", "value" => $admin_status2, "type" => "status", "title" => "Admin post status 2", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "4", "sort" => "3"));
	$tdb->add("config", array("name" => "admin_status1", "value" => $admin_status1, "type" => "status"));
	$tdb->add("ext_config", array("name" => "admin_status3", "value" => $admin_status3, "type" => "status", "title" => "Admin post status 3", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "4", "sort" => "5"));
	$tdb->add("config", array("name" => "admin_status3", "value" => $admin_status3, "type" => "status"));
	$tdb->add("ext_config", array("name" => "admin_status4", "value" => $admin_status4, "type" => "status", "title" => "Admin post status 4", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "4", "sort" => "7"));
	$tdb->add("config", array("name" => "admin_status4", "value" => $admin_status4, "type" => "status"));
	$tdb->add("ext_config", array("name" => "admin_status5", "value" => $admin_status5, "type" => "status", "title" => "Admin post status 5", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "4", "sort" => "9"));
	$tdb->add("config", array("name" => "admin_status5", "value" => $admin_status5, "type" => "status"));
	$tdb->add("ext_config", array("name" => "member_post1", "value" => $member_post1, "type" => "status", "title" => "Post count 1", "form_object" => "text", "data_type" => "number", "minicat" => "2", "sort" => "2"));
	$tdb->add("config", array("name" => "member_post1", "value" => $member_post1, "type" => "status"));
	$tdb->add("ext_config", array("name" => "member_post2", "value" => $member_post2, "type" => "status", "title" => "Post count 2", "form_object" => "text", "data_type" => "number", "minicat" => "2", "sort" => "4"));
	$tdb->add("config", array("name" => "member_post2", "value" => $member_post2, "type" => "status"));
	$tdb->add("ext_config", array("name" => "member_post3", "value" => $member_post3, "type" => "status", "title" => "Post count 3", "form_object" => "text", "data_type" => "number", "minicat" => "2", "sort" => "6"));
	$tdb->add("config", array("name" => "member_post3", "value" => $member_post3, "type" => "status"));
	$tdb->add("ext_config", array("name" => "member_post4", "value" => $member_post4, "type" => "status", "title" => "Post count 4", "form_object" => "text", "data_type" => "number", "minicat" => "2", "sort" => "8"));
	$tdb->add("config", array("name" => "member_post4", "value" => $member_post4, "type" => "status"));
	$tdb->add("ext_config", array("name" => "member_post5", "value" => $member_post5, "type" => "status", "title" => "Post count 5", "form_object" => "text", "data_type" => "number", "minicat" => "2", "sort" => "10"));
	$tdb->add("config", array("name" => "member_post5", "value" => $member_post5, "type" => "status"));
	$tdb->add("ext_config", array("name" => "mod_post1", "value" => $mod_post1, "type" => "status", "title" => "Post count 1", "form_object" => "text", "data_type" => "number", "minicat" => "3", "sort" => "2"));
	$tdb->add("config", array("name" => "mod_post1", "value" => $mod_post1, "type" => "status"));
	$tdb->add("ext_config", array("name" => "mod_post2", "value" => $mod_post2, "type" => "status", "title" => "Post count 2", "form_object" => "text", "data_type" => "number", "minicat" => "3", "sort" => "4"));
	$tdb->add("config", array("name" => "mod_post2", "value" => $mod_post2, "type" => "status"));
	$tdb->add("ext_config", array("name" => "mod_post3", "value" => $mod_post3, "type" => "status", "title" => "Post count 3", "form_object" => "text", "data_type" => "number", "minicat" => "3", "sort" => "6"));
	$tdb->add("config", array("name" => "mod_post3", "value" => $mod_post3, "type" => "status"));
	$tdb->add("ext_config", array("name" => "mod_post4", "value" => $mod_post4, "type" => "status", "title" => "Post count 4", "form_object" => "text", "data_type" => "number", "minicat" => "3", "sort" => "8"));
	$tdb->add("config", array("name" => "mod_post4", "value" => $mod_post4, "type" => "status"));
	$tdb->add("ext_config", array("name" => "mod_post5", "value" => $mod_post5, "type" => "status", "title" => "Post count 5", "form_object" => "text", "data_type" => "number", "minicat" => "3", "sort" => "10"));
	$tdb->add("config", array("name" => "mod_post5", "value" => $mod_post5, "type" => "status"));
	$tdb->add("ext_config", array("name" => "admin_post1", "value" => $admin_post1, "type" => "status", "title" => "Post count 1", "form_object" => "text", "data_type" => "number", "minicat" => "4", "sort" => "2"));
	$tdb->add("config", array("name" => "admin_post1", "value" => $admin_post1, "type" => "status"));
	$tdb->add("ext_config", array("name" => "admin_post2", "value" => $admin_post2, "type" => "status", "title" => "Post count 2", "form_object" => "text", "data_type" => "number", "minicat" => "4", "sort" => "4"));
	$tdb->add("config", array("name" => "admin_post2", "value" => $admin_post2, "type" => "status"));
	$tdb->add("ext_config", array("name" => "admin_post3", "value" => $admin_post3, "type" => "status", "title" => "Post count 3", "form_object" => "text", "data_type" => "number", "minicat" => "4", "sort" => "6"));
	$tdb->add("config", array("name" => "admin_post3", "value" => $admin_post3, "type" => "status"));
	$tdb->add("ext_config", array("name" => "admin_post4", "value" => $admin_post4, "type" => "status", "title" => "Post count 4", "form_object" => "text", "data_type" => "number", "minicat" => "4", "sort" => "8"));
	$tdb->add("config", array("name" => "admin_post4", "value" => $admin_post4, "type" => "status"));
	$tdb->add("ext_config", array("name" => "admin_post5", "value" => $admin_post5, "type" => "status", "title" => "Post count 5", "form_object" => "text", "data_type" => "number", "minicat" => "4", "sort" => "10"));
	$tdb->add("config", array("name" => "admin_post5", "value" => $admin_post5, "type" => "status"));
	$tdb->add("ext_config", array("name" => "membercolor", "value" => $membercolor, "type" => "status", "title" => "Member status color", "description" => "The color that the status of a regular user will have", "form_object" => "text", "data_type" => "string", "minicat" => "5", "sort" => "1"));
	$tdb->add("config", array("name" => "membercolor", "value" => $membercolor, "type" => "status"));
	$tdb->add("ext_config", array("name" => "moderatorcolor", "value" => $moderatorcolor, "type" => "status", "title" => "Moderator status color", "description" => "The color that the status of a moderator will have", "form_object" => "text", "data_type" => "string", "minicat" => "5", "sort" => "2"));
	$tdb->add("config", array("name" => "moderatorcolor", "value" => $moderatorcolor, "type" => "status"));
	$tdb->add("ext_config", array("name" => "admcolor", "value" => $admcolor, "type" => "status", "title" => "Admin status color", "description" => "The color that the status of an administrator will have", "form_object" => "text", "data_type" => "string", "minicat" => "5", "sort" => "3"));
	$tdb->add("config", array("name" => "admcolor", "value" => $admcolor, "type" => "status"));

	//Who's online hex
	$tdb->add("ext_config", array("name" => "userColor", "value" => $userColor, "type" => "status", "title" => "User Color", "description" => "The color of usernames of regular users in the who's online box", "form_object" => "text", "data_type" => "string", "minicat" => "6", "sort" => "1"));
	$tdb->add("config", array("name" => "userColor", "value" => $userColor, "type" => "status"));
	$tdb->add("ext_config", array("name" => "modColor", "value" => $modColor, "type" => "status", "title" => "Moderator Color", "description" => "The color of usernames of moderators in the who's online box", "form_object" => "text", "data_type" => "string", "minicat" => "6", "sort" => "2"));
	$tdb->add("config", array("name" => "modColor", "value" => $modColor, "type" => "status"));
	$tdb->add("ext_config", array("name" => "adminColor", "value" => $adminColor, "type" => "status", "title" => "Admin Color", "description" => "The color of usernames of administrators in the who's online box", "form_object" => "text", "data_type" => "string", "minicat" => "6", "sort" => "3"));
	$tdb->add("config", array("name" => "adminColor", "value" => $adminColor, "type" => "status"));
	//$tdb->add("ext_config", array("name" => "###", "value" => $###, "type" => "###", "title" => "###", "description" => "###", "form_object" => "###", "data_type" => "###", "minicat" => "!!", "sort" => "%%"));
	//$tdb->add("config", array("name" => "###", "value" => $###, "type" => "###"));

	//$tdb->sortAndBuild("ext_config", "sort", "ASC");

	$tdb->tdb('./db/', 'posts.tdb');
	$tdb->createTable('trackforums', array(array('fId', 'number', 7), array('uId', 'number', 7), array('lastvisit', 'number', 14), array('id', 'id')));
	$tdb->createTable('tracktopics', array(array('fId', 'number', 7), array('tId', 'number', 7), array('uId', 'number', 7), array('old', 'number', 1), array('id', 'id')));

	if($errorHandler->return_howmany() != 0) $error_finish = "Unable to create and Modify Databases and Tables.";
	$finish = "Creating and Modifying Text Databases and Tables...Done!";
} elseif($step == 3) {
	//Transfer User Records from the old tdb to the new tdb
	//Including PM Files

	require_once('./includes/class/tdb.class.php');
	$_PrivMsg = array();
	$_FAILED = array();
	include "./db/config_pm.dat";

	$tdb = new tdb("./db/", "main.tdb");
	$_PrivMsg["tdb"] = new tdb("./db/", "privmsg.tdb");
	$tdb->setFp("users", "members");

	$bList = fopen("./db/blockedlist.dat", 'a');
	$lvList = fopen("./db/lastvisit.dat", 'a');
	$newpmList = fopen("./db/new_pm.dat", 'a');

	$recs = array_reverse(listall('./db/users'));
	for($i=0, $id=1, $r_c=count($recs);$i<$r_c;$i++, $id++) {
		$rec = def($recs[$i], './db/users');
		while($id < $rec["id"]) {
			$_FAILED[] = $id;
			fwrite($lvList, str_repeat(' ', 14));
			$tdb->add("users", array());

			$id++;
		}
		if($id > $rec["id"]) $errorHandler->add_Error(E_USER_ERROR, "Current ID surpassed ID in current record");
		if(file_exists("./db/pms/".$id."_block.dat")) {
			if(filesize("./db/pms/".$id."_block.dat") != 0) fwrite($bList, $id.":".trim(file_get_contents("./db/pms/".$id."_block.dat")).chr(31));
		}
		fwrite($newpmList, " 0");
		$_PrivMsg["tdb"]->setFp("CuPrivMsgTb", ceil($id/120));
		for($k = array("inbox", "outbox", "end");current($k) != "end";next($k)) {
			if(file_exists("./db/pms/".$id."_".current($k).".dat")) {
				$_PrivMsg[current($k)] = listall("./db/pms/".$id."_".current($k));
				$_PrivMsg["c_".current($k)] = count($_PrivMsg[current($k)]);
				if(!empty($_PrivMsg[current($k)])) { //if($_PrivMsg[current($k)][0] != "") {
					for($j=0;$j<$_PrivMsg["c_".current($k)];$j++) {
						$_PrivMsg["CuRec"] = def($_PrivMsg[current($k)][$j], "./db/pms/".current($k));
						$_PrivMsg["CuRec"]["box"] = current($k);
						$_PrivMsg["CuRec"]["message"] = undo_format($_PrivMsg["CuRec"]["message"]);
						$_PrivMsg["CuRec"]["date"] = convertTimeString($_PrivMsg["CuRec"]["date"], '', $ratio);
						if(isset($_PrivMsg["CuRec"]["from"])) {
							$_PrivMsg["CuRec"]["to"] = $id;
							$_PrivMsg["CuRec"]["from"] = $_PrivMsg["CuRec"]["from_id"];
						} else {
							$_PrivMsg["CuRec"]["from"] = $id;
							$_PrivMsg["CuRec"]["to"] = $_PrivMsg["CuRec"]["to_id"];
						}
						$_PrivMsg["tdb"]->add("CuPrivMsgTb", $_PrivMsg["CuRec"]);
						unset($_PrivMsg["CuRec"]);
						if($j > ($pm_max_outbox_msg - 1) && current($k) == "outbox") $_PrivMsg["c_".current($k)] = -1;
					}
				}
			} else {
				$errorHandler->add_cError(E_USER_WARNING, "(ID ".$rec["id"].")'s ".current($k)." does not exist");
			}
		}

		$lastvisit = time();
		if(file_exists("./db/lastvisit/".$id.".dat"))
		if(@is_readable("./db/lastvisit/".$id.".dat"))
		if(@filesize("./db/lastvisit/".$id.".dat") != 0) $lastvisit = convertTimeString(trim(file_get_contents("./db/lastvisit/".$id.".dat")), '', $ratio);
		fwrite($lvList, $lastvisit.str_repeat(' ', (14 - strlen($lastvisit))));
		$rec["password"] = chr(21).$rec["password"];
		$rec["date_added"] = convertTimeString($rec["date_added"], "user", $ratio);
		if($rec["view_email"] == 'on') $rec["view_email"] = '1';
		else $rec["view_email"] = '0';
		if($rec["mail_list"] == 'on') $rec["mail_list"] = '1';
		else $rec["mail_list"] = '0';
		$rec["sig"] = undo_format($rec["sig"]);
		$rec["timezone"] = '0';

		$tdb->add("users", $rec);
		unset($lastvisit, $rec);
	}
	fclose($lvList);
	fclose($bList);
	fclose($newpmList);

	foreach($_FAILED as $failed_user_id) {
		$tdb->delete("users", $failed_user_id, false);
	}
	//$tdb->reBuild("users");

	if($errorHandler->return_howmany() != 0) $error_finish = "<b>Unable to transfer User records and PM files to new database.</b>";
	$finish = "Transfering User records and PM files to new database...Done!";
} elseif($step == 4) {
	//Transfering categories, & forums, and prepares to for the next step

	require_once('./includes/class/tdb.class.php');

	$tdb = new tdb("./db/", "main.tdb");
	$_TPosts = array("tdb"=>new tdb("./db/", "posts.tdb"), "fId"=>array(), "p_ids" => array());
	$_TRANSFER = array("cats" => array("sort" => array("old" => array(), "new" => array())), "forums" => array());

	$tdb->setFp("cats", "categories");
	$tdb->setFp("forums", "forums");
	if(DEBUGGING_MODE) fwrite($debug_to_file_resource, "Set ini. arr.(s) and opened tdb class\n");

	if(!($cat_id = (getID("./db/cat")+1))) die("Unable to retrieve Category IDs");
	if(DEBUGGING_MODE) fwrite($debug_to_file_resource, "Retrieved last CAT ID:".$cat_id."\n");

	$permcats = explode("\n", file_get_contents("./db/permcats.dat"));
	if(empty($permcats)) { //if($permcats[0] != "") {
		$pCats = array();
		for($i=0;$i<count($permcats);$i++) {
			list($c_id, $user_id) = explode(",", $permcats[$i]);
			$pCats[$c_id] = $user_id;
		}
	}

	for($id=1;$id<$cat_id;$id++) {
		if(FALSE !== ($rec = get($id, "./db/cat"))) {
			$cId = $rec["id"];
			$_TRANSFER["cats"]["sort"]["old"][$cId] = explode(",", $rec["sort"]);
			unset($rec["id"], $rec["sort"]);
			if(!empty($pCats[$cId])) $rec["view"] = $pCats[$cId];
			else $rec["view"] = 0;
			$_TRANSFER["cats"][$cId] = $tdb->add("cats", $rec);
			$_TRANSFER["cats"]["sort"]["new"][$cId] = array();
			unset($cId);
		}
		unset($rec);
	}
	if(DEBUGGING_MODE) fwrite($debug_to_file_resource, "retrieved category list and sort:".var_dump($_TRANSFER['cats']['sort']['old'])."\n");

	$tdb->setFp("config", "config");
	$admin_catagory_sorting = $tdb->get("config", 8);
	$cSorting = explode(",", $admin_catagory_sorting[0]["value"]);
	$nSort = array();
	for($i=0;$i<count($cSorting);$i++) {
		if($_TRANSFER["cats"][$cSorting[$i]] != "") $nSort[$i] = $_TRANSFER["cats"][$cSorting[$i]];
	}
	$newSorting = implode(",", $nSort);

	$tdb->setFp("config", "config");
	$tdb->setFp('ext_config', 'ext_config');
	//the version stored in here is for the footer, not for update
	$tdb->edit("config", 1, array("value" => "2.0 BETA 1"), false);
	$tdb->edit("ext_config", 1, array("value" => "2.0 BETA 1"), false);
	$tdb->edit("config", 8, array("value" => $newSorting));
	$tdb->edit("ext_config", 8, array("value" => $newSorting));

	$permforum = file("./db/permforums.dat");
	$pForum = array();
	if(empty($permforum)) { //if($permforum[0] != "") {
		for($i=0; $i<count($permforum); $i++) {
			$temp = explode(",", $permforum[$i]);
			$pForum[$temp[0]] = $temp[1];
		}
	}
	if(DEBUGGING_MODE) fwrite($debug_to_file_resource, "Received Permissions for forums file:".var_dump($pForum)."\n");

	$fAll = listall("./db/forum");
	$ending = array();
	$count = array();
	for($i=0, $fMax=count($fAll);$i<$fMax;$i++) {
		$fRec = def($fAll[$i], "./db/forum");
		if($fRec["id"] != "") {
			$fId = $fRec["id"];
			unset($fRec["id"]);
			$count[$fId]["topics"] = 0;
			$count[$fId]["posts"] = 0;
			unset($fRec["topics"], $fRec["posts"]);
			if($_TRANSFER["cats"][$fRec["cat"]] != "") {
				$trans = $fRec["cat"];
				if(!empty($pForum[$fId])) {
					$fRec["view"] = $pForum[$fId];
					$fRec["post"] = $pForum[$fId];
					$fRec["reply"] = $pForum[$fId];
				} else {
					$fRec["view"] = "0";
					$fRec["post"] = "1";
					$fRec["reply"] = "1";
				}
				$fRec["cat"] = $_TRANSFER["cats"][$fRec["cat"]];
				if(!is_array($fRec)) die("<b>Error</b>: \$fRec[".$fId."] needs to be an array in function: \$this->add(\"forums\", \$fRec[".$fId."]) on line <b>245</b>");
				$_TRANSFER["forums"][$fId] = $tdb->add("forums", $fRec);
				$_TPosts["tdb"]->createTable($_TRANSFER["forums"][$fId], array(
				array("icon", "string", 10),
				array("user_name", "string", 20),
				array("date", "number", 14),
				array("message", "memo"),
				array("user_id", "number", 7),
				array("t_id", "number", 7),
				array('edited_by', 'string', 20),
				array('edited_by_id', 'number', 7),
				array('edited_date', 'number', 14),
				array("id", "id")
				));
				$_TPosts["tdb"]->createTable($_TRANSFER["forums"][$fId]."_topics", array(
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
				$_TPosts["fId"][] = $fId;
				$_TPosts["p_ids"][$fId] = array();

				$boolean = false;
				reset($_TRANSFER["cats"]["sort"]["old"][$trans]);
				while(list($key, $val) = each($_TRANSFER["cats"]["sort"]["old"][$trans])) {
					// $val = Forum Id; $key = Forums' Order;
					if($val == $fId) {
						$_TRANSFER["cats"]["sort"]["new"][$trans][$key] = $_TRANSFER["forums"][$fId];
						$boolean = true;
						break 1;
					}
				}
				if(!($boolean)) {
					$errorHandler->add_cError(E_USER_NOTICE, "<i>Mismatching Variables</i>: The forum ".$fRec["forum"]."(ID ".$fId.") Belongs in Category ID ".$fRec["cat"].", but wasn't found in FORUM_SORT.  This forum will be added to the end of FORUM_SORT");
					if($ending[$fRec["cat"]]["old"] != "") $ending[$fRec["cat"]]["forums"][] = $fId;
					else $ending = array($fRec["cat"] => array("old" => $trans, "forums" => array($fId)));
				}
				unset($trans, $boolean, $fId);
			} else {
				$errorHandler->add_cError(E_USER_WARNING, "<b>Unexpected Error</b>(step $step): Unavailable CAT TRANSFER ID available for: CAT #".$fRec["cat"]);
			}
		} else {
			$errorHandler->add_error(E_PARSE,"(step $step) The $i forum record couldn't be parsed<br>");
			$errorHandler->print_errors();
			exit;
		}
	}
	if(DEBUGGING_MODE) fwrite($debug_to_file_resource, "Converted Forums over... \$_TRANSFER['cats'] = ".var_dump($_TRANSFER['cats'])." and \$_TRANSFER['forums'] = ".var_dump($_TRANSFER['forums'])."\n");
	foreach($ending as $missing) {
		for($i=0;$i<count($missing["forums"]);$i++) {
			$_TRANSFER["cats"]["sort"]["new"][$missing["old"]][] = $missing["forums"][$i];
		}
	}

	for($i=0;$i<$cat_id;$i++) {
		$trans = "";
		if(!empty($_TRANSFER["cats"]["sort"]["new"][$i])) {
			ksort($_TRANSFER["cats"]["sort"]["new"][$i]);
			$trans = implode(",", $_TRANSFER["cats"]["sort"]["new"][$i]);
			$tdb->edit("cats", $_TRANSFER["cats"][$i], array("sort" => $trans));
		} elseif(!empty($_TRANSFER["cats"][$i])) {
			$errorHandler->add_cError(E_USER_NOTICE, "Could not find FORUM_SORT for category ".$_TRANSFER["cats"][$i].".  Forums in this Category will be sorted by ALPHA_NUMERIC");
		}
		unset($trans);
	}
	unset($_TRANSFER["cats"], $id);
	if(DEBUGGING_MODE) fwrite($debug_to_file_resource, "Updated FORUM_SORT for category. Now commencing Topic and Posts conversion.\n");

	$fcount = count($_TPosts["fId"]);
	foreach($_TPosts["fId"] as $fId) {
		if(!(file_exists("./db/".$fId."_sorted.dat") || is_dir("./db/".$fId))) {
			$errorHandler->add_cError(E_USER_WARNING, "<b><font color=red>*</font></b> Unable to update forum:$fId.  Topics and/or Posts are missing");
		} else {
			$_TPosts["tdb"]->setFp("CuTopic", $_TRANSFER["forums"][$fId]."_topics");
			$_TPosts["tdb"]->setFp("CuPost", $_TRANSFER["forums"][$fId]);
			//$rebuild = false;

			$tAll = array_reverse(listall("./db/".$fId."_sorted"));
			$tCount = count($tAll);
			$count[$fId]["topics"] = $tCount;
			for($i=0;$i<$tCount;$i++) {
				if(empty($tAll[$i])) continue;
				$tRec = def($tAll[$i], "./db/p_forum");
				if(empty($tRec)) continue;
				$tId = $tRec["id"];
				unset($tRec["id"]);

				if(substr($tRec["subject"], 0, 7) != "MOVED: " && file_exists('./db/'.$fId.'/'.$tId.'.dat')) {
					$tRec["last_post"] = convertTimeString($tRec["last_post"], '', $ratio);
					if(strpos($tRec["subject"], "[Sticky Note]")) {
						$tRec["sticky"] = 1;
						$tRec["subject"] = str_replace("[Sticky Note]", "", $tRec["subject"]);
					} else $tRec["sticky"] = 0;
					if(!is_writable("./db/".$fId."/".$tId.".dat")) $tRec["locked"] = 1;
					else $tRec["locked"] = 0;
					$tRec["topic_starter"] = $tRec["user"];
					$tRec["views"] = 0;
					if($tRec["icon"] == "no_icon.gif") $tRec["icon"] = "noicon.gif";
					$newId = $_TPosts["tdb"]->add("CuTopic", $tRec);
					$pAll = listall("./db/$fId/".$tId);
					$pCount = count($pAll);
					if(!(empty($pAll[0]) && $pCount == 1)) {
						$count[$fId]["posts"] = $count[$fId]["posts"] + count($pAll);
						for($k=0;$k<$pCount;$k++) {
							if(!empty($pAll[$k])) {
								$pRec = def($pAll[$k], "./db/p_topic");
								if(!empty($pRec["id"])) {
									unset($pRec["id"]);
									$pRec["message"] = undo_format($pRec["message"]);
									$pRec["date"] = convertTimeString($pRec["date"], '', $ratio);
									if($pRec["icon"] = "no_icon.gif") $pRec["icon"] = "noicon.gif";
									$pRec["t_id"] = $newId;
									$_TPosts["p_ids"][$fId][$newId][] = $_TPosts["tdb"]->add("CuPost", $pRec);
									unset($pRec);
								} else {
									$count[$fId]["posts"] = $count[$fId]["posts"] - 1;
									$errorHandler->add_cError(E_PARSE, "Parsing Errors for the ".$k.numberEnding($k+1)." post of topic ".$tId." of forum $fId.");
								}
							} else $count[$fId]["posts"] = $count[$fId]["posts"] - 1;
						}
					} else {
						$count[$fId]["topics"] = $count[$fId]["topics"] - 1;
						sleep(1);
						$_TPosts["tdb"]->delete("CuTopic", $tId, false);
						//$rebuild = true;
						$errorHandler->add_cError(E_USER_WARNING, "No Posts found in <b>".$tRec["subject"]."(ID ".$tId.")</b> of forum ".$fId.".This could be because there are corrupted files.  <b>The Topic was deleted.</b>");
					}
				} else {
					$count[$fId]["topics"] = $count[$fId]["topics"] - 1;
				}
				unset($newId, $pAll, $pRec, $tRec);
			}
			//if($rebuild) $_TPosts["tdb"]->reBuild("CuTopic"); //Rebuld obsolete in tdb4.4

			unset($tAll);
		}
		unset($fId);
	}
	if(DEBUGGING_MODE) fwrite($debug_to_file_resource, "Finished Topic and Posts Conversion\n");

	while(list($fId, $edit) = each($count)) {
		$tdb->edit("forums", $_TRANSFER["forums"][$fId], $edit);
	}
	foreach($_TPosts["p_ids"] as $fId => $arr1) {
		$_TPosts["tdb"]->setFp("CuTopic", $_TRANSFER["forums"][$fId]."_topics");
		reset($arr1);
		while(list($tId, $arr2) = each($arr1)) {
			$_TPosts["tdb"]->edit("CuTopic", $tId, array("p_ids" => implode(",", $arr2)));
		}
	}
	if(DEBUGGING_MODE) fwrite($debug_to_file_resource, "Updated topic/post count in forums and saved p_ids to topics.  Step 4 is complete\n");

	if($errorHandler->return_howmany() != 0) $error_finish = "<b>Unable to transfer topics and posts to new database.</b>";
	$finish = "Transfering categories, forums, topics, and posts to new database...Done!";
} elseif($step == 5) {
	//Preparing Topics & pm for use by Sorting them
	//Calculating the dimensions of users' avatars
	echo '<p><IFRAME SRC="./includes/class/mod_avatar.class.php?self_execute=all" WIDTH="100%" HEIGHT="300">Please visit <a href="./includes/class/mod_avatar.class.php?self_execute=all" target="_blank">this page</a> to calculate avatars\' dimensions.</IFRAME></p>';
	require_once('./includes/class/tdb.class.php');

	$posts = new tdb("./db/", "posts.tdb");
	$tables1 = $posts->getTableList();
	foreach($tables1 as $table) {
		if(substr($table, -7) == "_topics") {
			$posts->setFp("topic", $table);
			$posts->sortAndBuild("topic", "last_post", "DESC");
		}
	}

	$privmsg = new tdb("./db/", "privmsg.tdb");
	$tables2 = $privmsg->getTableList();
	foreach($tables2 as $table) {
		$privmsg->setFp("pm", $table);
		$privmsg->sortAndBuild("pm", "date", "DESC");
	}
	if($errorHandler->return_howmany() != 0) $error_finish = "<b>Unable to Sort Topics...</b>";
	else $finish = "Sorting Topics and Private Messages to be used...Done!";
} elseif($step == 6) {
	//Editing skin_config.dat and saving them to /skin/$skin_dir/coding.php
	//Check the index.php before deleting files

	if($handle = opendir('./skins/')) {
		$skin_contents_all = file("./db/skin_config.dat");
		$skin_contents = array();
		$save = false;
		for($i=0,$max=count($skin_contents_all);$i<$max;$i++) {
			if(FALSE !== strpos($skin_contents_all[$i], '$skin_tablefooter')) $save = true;
			if(FALSE !== strpos($skin_contents_all[$i], '$skin_viewpostheading')) $save = false;
			if($save) $skin_contents[] = $skin_contents_all[$i];
		}
		unset($skin_contents_all);

		$skin_contents = implode("\n", $skin_contents);
		$skin_contents = str_replace('$table_width_main', '".TABLE_WIDTH_MAIN."', $skin_contents);
		$skin_contents = str_replace('$skin_images_dir', '".SKIN_DIR."/images/', $skin_contents);
		$skin_contents = '<?php
function echoTableHeading($display) { //set $display to 85
    echo "<table cellspacing=0 cellpadding=0 width=\'".TABLE_WIDTH_MAIN."\' align=center>
                    <tbody>
                    <tr id=cat>
                    <td width=30><img src=\'".SKIN_DIR."/images/cat_top_left.gif\' width=132 border=0></td>
                    <td valign=middle background=".SKIN_DIR."/images/cat_top_bg.gif border=0><p align=center><b><font face=\'Verdana\' size=\'1\' color=\'#ffffff\'><b>".$display."</b></font></b></p></td>
                    <td width=30><p align=right><img src=\'".SKIN_DIR."/images/cat_top_right.gif\' width=134 border=0></p></td>
                    </tr>
                </tbody>
                </table>";
}'.$skin_contents.'
?>';
		$all_footers_saved = TRUE;
		while(false !== ($folder = readdir($handle))) {
			if($folder != "." && $folder != ".." && is_dir('skins/'.$folder)) {
				$folder = './skins/'.$folder.'/';
				if(is_writable($folder)) {
					if(!file_exists($folder.'coding.php')) {
						$f = fopen($folder.'coding.php', 'w');
						fwrite($f, $skin_contents);
						fclose($f);
					}
				} else {
					$all_footers_saved = FALSE;
					$errorHandler->add_cError(E_USER_NOTICE, "Unable to write to ".$folder."coding.php.  Please make sure that <i>".$folder."</i> is writable by CHMOD'ing it to 777");
				}
			}
		}
		closedir($handle);
	} else {
		echo "<script language=\"JavaScript\">var flag = 0;</script>";
		die('The skin directory (./skins/) and the directories in the skin directory (./skins/default/) has to be readable and writable.  Please CHMOD the skin directory to 777.  Then press <a href="Javascript:Window.refresh();">refresh</a>.');
	}
	if($errorHandler->error_exists() || !($all_footers_saved)) {
		$errorHandler->print_errors();
		echo "<script language=\"JavaScript\">var flag = 0;</script>";
		die('Please correct the following errors above, then <a href="JavaScript:Window.Refresh()">refresh</a>.');
	} else {
		$config_file = explode("\n", file_get_contents(('config.php')));
		for($i=0;$i<count($config_file);$i++) {
			if(strchr($config_file[$i], "INSTALLATION_MODE")) {
				$config_file[$i] = "define('INSTALLATION_MODE', false, true);";
				break;
			}
		}
		$config_file = implode("\n", $config_file);
		$f = fopen('config.php', 'w');
		fwrite($f, $config_file);
		fclose($f);
		unset($config_file);

		$finish = "Converting Table Footers to v2.0... Done!<p>The next step will delete your old database files that is not needed for v2.0.  Please check the page displayed below before clicking \"next >>\".  If your page looks in order, go to the next step.  Note that once the old database files are deleted, you will not be able to recover lost or corrupted data.</p><p><IFRAME SRC='index.php' WIDTH='100%' HEIGHT='300'></IFRAME></p><p>Note there are some discrepancies involving users' and administrators' abilities during the upgrade, that will be cleared up after the upgrade is complete.";
	}
} elseif($step == 7) {
	//Deleting Old unnecessary files, and
	//Reformatting Whos Online System

	$exemptFiles = array(
      ".htaccess",
      "badwords.dat",
      "banneduser.dat",
      "blockedlist.dat",
      "chatconfig.dat",
      "config2.php",
      "config_org.dat",
      "constants.php",
      "hits.dat",
      "hits_record.dat",
      "hits_today.dat",
      "iplog",
      "lastvisit.dat",
      "main.tdb",
      "new_pm.dat",
      "paid.dat",
      "posts.tdb",
      "privmsg.tdb",
      "search.def",
      "smilies.dat",
      "rssdata.dat",
      "rssdata.id",
      "team.dat"
      );

      require_once('./includes/class/tdb.class.php');
      $tdb["main"] = new tdb("./db/", "main.tdb");
      $tdb["PrivMsg"] = new tdb("./db/", "privmsg.tdb");
      $tdb["posts"] = new tdb("./db/", "posts.tdb");
      $tmpFiles1 = $tdb["main"]->getTableList();
      $tmpFiles2 = $tdb["PrivMsg"]->getTableList();
      $tmpFiles3 = $tdb["posts"]->getTableList();
      //$tmpFiles = array_merge($tmpFiles1, $tmpFiles2);
      $tmpFiles = $tmpFiles1 + $tmpFiles2 + $tmpFiles3;
      $tdbExt = array(".ta", ".memo", ".ref");
      for($i=0;$i<count($tmpFiles);$i++) {
      	for($k=0;$k<3;$k++) {
      		$exemptFiles[] = $tmpFiles[$i].$tdbExt[$k];
      	}
      }

      $db = "./db/";
      if($handle = opendir($db)) {
      	while(false !== ($file = readdir($handle))) {
      		if($file != "." && $file != "..") {
      			if(!is_dir($db.$file)) {
      				if(!in_array($file, $exemptFiles)) {
      					$delete_file = true;
      					if(substr($file, -3) == '.ta') $delete_file = false;
      					if(substr($file, -4) == '.ref') $delete_file = false;
      					if(substr($file, -5) == '.memo') $delete_file = false;
      					if($delete_file) unlink($db.$file);
      					else $errorHandler->add_cError(E_USER_NOTICE, 'Tried to delete '.$file);
      				}
      			} else {
      				if($file != "backup") {
      					$file .= "/";
      					if($handle2 = opendir($db.$file)) {
      						while(false !== ($file2 = readdir($handle2))) {
      							if($file2 != "." && $file2 != "..") unlink($db.$file.$file2);
      						}
      						rmdir($db.$file);
      						closedir($handle2);
      					}
      				}
      			}
      		}
      	}
      	closedir($handle);
      }

      //deleting old PHP files no longer used
      if(file_exists('open.php'))unlink("open.php");
      if(file_exists('textdb.inc.php')) unlink("textdb.inc.php");
      if(file_exists('upgrade_1.0-to-1.1.php')) unlink("upgrade_1.0-to-1.1.php");
      if(file_exists('viewtopicOLD.php')) unlink("viewtopicOLD.php");
      if(file_exists('pollpost.php')) unlink("pollpost.php");
      if(file_exists('createids.php')) unlink("createids.php");

      //possibly deleting old PM PHP files
      if(file_exists('sendpm.php')) unlink("sendpm.php");
      if(file_exists('pm-alert.php')) unlink("pm-alert.php");

      if($errorHandler->return_howmany() != 0) $error_finish = "<b>Unable to remove old forum files.</b>";
      $finish = "Deleting old forum files...Done!";
} elseif($step == 8) {
	echo "<script language=\"JavaScript\">var flag = 0;</script>";
	die("<font>The update was a success!  Please double check your forum system to ensure the forum is upgraded!<br><b>Delete the install.php and update1.x-2.0.php NOW, as it is a security risk to leave it in your server.</b><br>From here, you can...</font><p><font><a href='logoff.php?ref=login.php'>Login?</a><br><a href='index.php'>Go to Index</a><br><a href='Javascript:CloseWindow()'>Close Window</a></font></p>");
} else {
	die("<b>Fatal:</b> Invalid Step($step)");
}
//end

if(DEBUGGING_MODE) fclose($debug_to_file_resource);

$errorHandler->print_errors();

echo "<script language=\"JavaScript\">
var flag = 0;
</script>";


if($errorHandler->return_howmany() == 0) {
	$ext = "?step=".($step + 1);
	if($step >= 0 && $step < 8) echo $finish."<br><a name=\"end\"><form method=\"POST\" action=\"".$_SERVER['PHP_SELF'].$ext."#end\" name='updater' onSubmit='submitonce(updater)' enctype='multipart/form-data'>  <p><input type=\"submit\" value=\"Next &gt;&gt;\" name=\"submit\" onclick='return check_submit()'></p></form></a>";
} else {
	echo $error_finish."<br>";
}

if($ratio["corrupt"] != 0 || $ratio["correct"] != 0) {
	echo "Number of correctly/corruptly converted Date Strings to Unix Timestamps:<br>
Corrupt: ".$ratio["corrupt"]."<br>
Correct: ".$ratio["correct"]."<br>
Correctly Converted: ".(round(($ratio["correct"] / ($ratio["correct"] + $ratio["corrupt"])), 3) * 100)."%<br>";
}

if(($step == 2 || $step == 3 || $step == 4) && $errorHandler->return_howmany() == 0) echo "<table border='0' width='350' cellspacing='0' cellpadding='7' bordercolor='#000000' style='border-style: solid; border-width: 1'>
  <tr>
    <td width='100%'><font face='Verdana' size='2'><b>Advisory:</b> This next step might take a long time depending on how active your forum is.  Please allow the page to finish loading and DO NOT refresh the page if you get impatient.</font></td>
  </tr>
</table>";
$mt = explode(' ', microtime());
$script_end_time = $mt[0] + $mt[1];
echo "<p><i>Page Rendered in ".round($script_end_time - $script_start_time, 5)." seconds</i></p>";
?>
</body>
</html>
