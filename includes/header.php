<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
if(!defined('DB_DIR')) die('This is a wrapper script!');
if (!headers_sent()) {
	switch (basename($_SERVER['PHP_SELF'])) {
		case 'register.php':
		case 'profile.php':
		case 'newpost.php':
		case 'newpm.php':
			header ("Cache-control: private");
			break;
		default:
			header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header ("Cache-Control: no-cache, must-revalidate");
			header ("Pragma: no-cache");
			break;
	}
}

$mt = explode(' ', microtime());
$script_start_time = $mt[0] + $mt[1];
if ($tdb->is_logged_in()) {
	$refresh = false;
	if (!isset($_COOKIE["lastvisit"])) {
		$r = $tdb->get("users", $_COOKIE['id_env']);

		//NEW VERSION
		$ses_info = $r['lastvisit'];
		if ($ses_info == 0)
		$ses_info = mkdate();
		$tdb->edit("users",$_COOKIE["id_env"],array('lastvisit'=>mkdate()));

		if (!headers_sent()) {
			$uniquekey = generateUniqueKey();
			$tdb->edit('users', $_COOKIE['id_env'], array('uniquekey' => $uniquekey));
			setcookie("lastvisit", $ses_info); //time of this login/view
			setcookie("previousvisit",$r['lastvisit']); //time of previous login/view
			setcookie("timezone", $_COOKIE["timezone"], (time() + (60 * 60 * 24 * 7)));
			if (isset($_COOKIE["remember"])) {
				setcookie("remember", 1, (time() + (60 * 60 * 24 * 7)));
				setcookie("user_env", $_COOKIE["user_env"], (time() + (60 * 60 * 24 * 7)));
				setcookie("uniquekey_env", $uniquekey, (time() + (60 * 60 * 24 * 7)));
				setcookie("power_env", $_COOKIE["power_env"], (time() + (60 * 60 * 24 * 7)));
				setcookie("id_env", $_COOKIE["id_env"], (time() + (60 * 60 * 24 * 7)));
			}
		}
		$refresh = true;
	}
	if ($refresh && $_GET["a"] != 1 && $_POST["a"] != 1 && $_GET["s"] != 1 && $_POST["s"] != 1) redirect($_SERVER['PHP_SELF']."?".$QUERY_STRING, 0);
	if(!isset($_SESSION['newTopics'])) {
		$user = $tdb->get('users', $_COOKIE['id_env']);
		$_SESSION['newTopics'] = unserialize($user[0]['newTopicsData']);
		$_SESSION['__newTopicsHash'] = md5($user[0]['newTopicsData']);
	}
} else {
	$default_timezone = '0';
	$now = mkdate();
	if (!isset($_COOKIE["timezone"]) && !headers_sent()) setcookie("timezone", $default_timezone, (time() + (60 * 60 * 24 * 7)));
	if (!isset($_COOKIE["lastvisit"]) && !headers_sent()) setcookie("lastvisit", $now, (time() + (60 * 60 * 24 * 7)));
	$_COOKIE['lastvisit'] = $now;
	$_COOKIE['timezone'] = $default_timezone;
}
if (isset($_COOKIE['password_env'])) {
	setcookie('password_env', '', time() - 3600);
	redirect($_SERVER['PHP_SELF']."?".$QUERY_STRING, 0);
}
$h_f = fopen(DB_DIR."/hits_today.dat", "r");
$hits = explode(":", fread($h_f, filesize(DB_DIR."/hits_today.dat")));
fclose($h_f);
$h_f = fopen(DB_DIR."/hits_record.dat", "r");
$hits_r = explode(":", fread($h_f, filesize(DB_DIR."/hits_record.dat")));
fclose($h_f);
$day = date("d");
if (date("d", $hits[0]) != $day) {
	//in place for debugging
	//echo "<font size=1>xxx</font>";
	$hits[0] = time();
	$hits[1] = 0;
}
$hits[1] += 1;
$hits_today = $hits[1];
if ($hits_today > $hits_r[1]) {
	//New record
	$hits_r[0] = date("M j, Y");
	$hits_r[1] = $hits_today;
	$h_f = fopen(DB_DIR."/hits_record.dat", "w");
	fwrite($h_f, implode(":", $hits_r));
	fclose($h_f);
}
$hits_date = $hits_r[0];
$hits_record = $hits_r[1];
$h_f = fopen(DB_DIR."/hits_today.dat", "w");
flock($h_f, 2);
fwrite($h_f, implode(":", $hits));
flock($h_f, 3);
fclose($h_f);
$login = "";
if (!$tdb->is_logged_in()) {
	$login_ref = "";
	
	if( preg_match("/viewforum.php/", $_SERVER["PHP_SELF"]) )
	{
		$login_ref = "viewforum.php?id=". $_GET["id"];
	}
	elseif( preg_match("/viewtopic.php/", $_SERVER["PHP_SELF"]) )
	{
		$login_ref = "viewtopic.php?id=". $_GET["id"] ."&t_id=". $_GET["t_id"];
	}
	
	$login = "You are not logged in.";
	$loginlink = "login.php?ref=". urlencode($login_ref);
	$pm_display = "login.php?ref=pmsystem.php";
} else {
	$login = "Welcome, ".$_COOKIE["user_env"]."!";
	$loginlink = "logoff.php";
	$pm_display = "pmsystem.php";
	$f = fopen(DB_DIR."/new_pm.dat", 'r');
	fseek($f, (((int)$_COOKIE["id_env"] * 2) - 2));
	$new_pm = fread($f, 2);
	fclose($f);
	if ((int)$new_pm != 0) $pm_alert = "-&nbsp;<a href='pmsystem.php?section=inbox'><strong>".$new_pm."</strong> new PMs in your inbox</a>";
	else $pm_alert = "-&nbsp;No new messages";
	$mark_all_read = "<a href='setallread.php'>Mark all posts read</a>";
	if ($_COOKIE["power_env"] >= 3) {
		$adminlink = "<a href='admin.php'>Admin Panel</a>&nbsp;&middot;";
		if($_REGIST['reg_approval']) {
			if($_SESSION['reg_approval_count'] == 0 && mktime() > ($_SESSION['reg_approval_lastcheck']+300)) {//Check every 5 mins IF count == 0
				$users = $tdb->query('users', "reg_code?'reg_'", 1, -1);
				$_SESSION['reg_approval_count'] = ((!empty($users[0])) ? count($users) : 0);
			}
			if($_SESSION['reg_approval_count'] > 0) {
				$adminlink .= "(<a href='admin_members.php?action=confirm#skip_nav'><b>{$_SESSION['reg_approval_count']} Unapproved User</b></a>)&nbsp;&middot;";
			}
		}
	}
}

//Start Header
echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<title>".((!isset($where) || $where == '') ? stripslashes($_CONFIG['title']) : (strip_tags(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], stripslashes($where)))))."</title>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<link rel='stylesheet' type='text/css' href='".SKIN_DIR."/css/style.css' />
<link rel='stylesheet' type='text/css' href='skins/disabled.css' />
<script type='text/javascript' src='./includes/scripts.js'></script>
<script type='text/javascript' src='./includes/301a.js'></script>";

// Check if an xajax object exists
if(isset($xajax))
{
	$xajax->printJavascript();
	
	// TJH: Uncomment this line for xajax debugging
	//echo "<script type=\"text/javascript\" src=\"includes/thirdparty/xajax-0.6beta1/xajax_js/xajax_debug.js\" charset=\"UTF-8\"></script>";
}

echo "</head>
<body>
<div id='upb_container'>
	<div class='main_cat_wrapper2'>
		<table class='main_table_2' cellspacing='1'>
			<tr>
				<td id='logo'><img src='".$_CONFIG['logo']."' alt='' title='' /></td>
			</tr>
		</table>
	</div>
 <br />
	<br />
	<div class='tabstyle_1'>
		<ul>";
if ($tdb->is_logged_in()) echo "
			<li><a href='index.php' title='Forum Home'><span>Forum Home</span></a></li>
			<li><a href='".$_CONFIG["homepage"]."' title='Site Home'><span>Site Home</span></a></li>
			<li><a href='showmembers.php' title='Members'><span>Members</span></a></li>
			<li><a href='search.php' title='Search'><span>Search</span></a></li>
			<li><a href='board_faq.php' title='Help Faq'><span>Help Faq</span></a></li>";
else echo "
			<li><a href='index.php' title='Forum Home'><span>Forum Home</span></a></li>
			<li><a href='".$_CONFIG["homepage"]."' title='Site Home'><span>Site Home</span></a></li>
			<li><a href='search.php' title='Search'><span>Search</span></a></li>
			<li><a href='board_faq.php' title='Help Faq'><span>Help Faq</span></a></li>";
echo "
		</ul>
	</div>
	<div style='clear:both;'></div>
		";
echoTableHeading(stripslashes($_CONFIG['title']), $_CONFIG);
echo "
		<tr>
			<td class='area_welcome'><div class='welcome_text'>";
if ($tdb->is_logged_in()) echo "
				<strong>$login</strong>&nbsp;&nbsp;
				$adminlink
				<a href='$loginlink'>Logout</a>
				&middot;&nbsp;<a href='profile.php'>User CP</a>
				&middot;&nbsp;<a href='$pm_display'>Messenger</a>
				$pm_alert";
				else {
	    echo "
				<strong>$login</strong>  Please ";
	    if($_REGIST['disable_reg']) print '';
	    else print "<a href='register.php'><strong>Register</strong></a> or ";
	    print "<a href='$loginlink'><strong>Login</strong></a>.";
				}
				echo "
			</div></td>
		</tr>";
				echoTableFooter(SKIN_DIR);
				//login information

				if (!$tdb->is_logged_in() && isset($_COOKIE['user_env']) && isset($_COOKIE['uniquekey_env']) && isset($_COOKIE['id_env'])) {
					$redirect = urlencode($_SERVER['REQUEST_URI']);
					print str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', "You or another person logged in on a different computer since the last time you've visited.<br /><a href=\"logoff.php?ref={$redirect}\">Don't show this message anymore</a> or <a href=\"login.php?ref={$redirect}\">Login</a>.", ALERT_MSG));
				}
				echo "

	<div class='breadcrumb'><span class='breadcrumb_home'><a href='index.php'>".stripslashes($_CONFIG["title"])."</a></span>";
				if (isset($where)) echo "&nbsp;<span class='breadcrumb_page'>".$_CONFIG["where_sep"]." ".$where."</span>";
				echo "
	</div>";
				//End Header

				if ($_CONFIG["servicemessage"] != "" && ($_SESSION['servicemessage'] != md5($_CONFIG['servicemessage']) || basename($_SERVER['PHP_SELF']) == 'index.php')) {
	    $_SESSION['servicemessage'] = md5($_CONFIG['servicemessage']);
	    echoTableHeading("Announcements", $_CONFIG);
	    echo "
			<tr>
			<td class='area_1' style='text-align:left;'>".$_CONFIG["servicemessage"]."</td>
			</tr>";
	    echoTableFooter(SKIN_DIR);

				}

				/*    if ($_GET['SHOW'] == 'COOKIES') {
				 print '<pre>';
				 foreach($GLOBALS["_COOKIE"] as $varname => $varvalue) {
				 print $varname."\t= ".$varvalue."\n";
				 }
				 print '</pre>';
				 //echo "\$user_env = ".$_COOKIE["user_env"]."<br>";
				 //if(isset($_COOKIE['pass_env'])) echo "\$pass_env = ".$_COOKIE['pass_env']."<br>";
				 //if(isset($_COOKIE['password_env'])) echo "\$password_env = ".$_COOKIE["password_env"]."<br>strlen(\$password_env):".strlen($_COOKIE["password_env"])."<br>";
				 //echo "\$uniquekey_env = ".$_COOKIE["uniquekey_env"]."<br>\$power_env = ".$_COOKIE["power_env"]."<br>\$id_env = ".$_COOKIE["id_env"]."<br><br>";
				 //echo "\$remember = ".$_COOKIE['remember']."<br>";
				 //echo "\$lastvisit = ".gmdate("M d, Y g:i:s a", $_COOKIE["lastvisit"])." (".$_COOKIE["lastvisit"].")<br><br>";
				 }*/
				?>
