<?php
$loader = require 'vendor/autoload.php';
$loader->add('', 'classes');

$twigLoader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($twigLoader, array());

function RemoveXSS($val) {
	$before_val = $val;
	
	// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
	// this prevents some character re-spacing such as <java\0script>
	// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
	//echo "COMMA COUNT: ".substr_count($val,'\x2C')."<br>";
	$val = preg_replace('/([\x00-\x08\x0b-\x0c\x0e-\x19])/', '', $val);
	// straight replacements, the user should never need these since they're normal characters
	// this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29>
	$search = 'abcdefghijklmnopqrstuvwxyz';
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$search .= '1234567890!@#$%^&*()';
	$search .= '~`";:?+/={}[]-_|\'\\';
	for ($i = 0; $i < strlen($search); $i++) {
		// ;? matches the ;, which is optional
		// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
		 
		// &#x0040 @ search for the hex values
		$val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ;
		// &#00064 @ 0{0,7} matches '0' zero to seven times
		$val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ;
	}
	 
	// now the only remaining whitespace attacks are \t, \n, and \r
	$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
	$ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
	$ra = array_merge($ra1, $ra2);
	 
	$found = true; // keep replacing as long as the previous round replaced something
	while ($found == true) {
		$val_before = $val;
		for ($i = 0; $i < sizeof($ra); $i++) {
			$pattern = '/';
			for ($j = 0; $j < strlen($ra[$i]); $j++) {
				if ($j > 0) {
					$pattern .= '(';
					$pattern .= '(&#[xX]0{0,8}([9ab]);)';
					$pattern .= '|';
					$pattern .= '|(&#0{0,8}([9|10|13]);)';
					$pattern .= ')*';
				}
				$pattern .= $ra[$i][$j];
			}
			$pattern .= '/i';
			// TJH: Removing <x> insertion, this isn't actually needed to protect against XSS attacks
			// and it just creates headaches...
			$replacement = substr($ra[$i], 0, 2).''.substr($ra[$i], 2); // add in <> to nerf the tag
			$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
			if ($val_before == $val) {
				// no replacements were made, so exit the loop
				$found = false;
			}
		}
	}
	
	$after_val = $val;
	
	// TJH: DEBUGING
	//$f = fopen(dirname( __FILE__ )."/../requestlog.log", "a");
	//fwrite($f, "\nbefore: \"$before_val\", after: \"$after_val\"");
	//fclose($f);
	
	return $val;
}

$_SERVER['PHP_SELF'] = $_SERVER['SCRIPT_NAME'];
if(basename($_SERVER['PHP_SELF']) == 'upb.initialize.php') die('This is a wrapper script!');
//Start session for all upb pages
session_start();

//prevents some problems with IIS Servers
if (!isset($_SERVER['REQUEST_URI'])) {
	$_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
	if (isset($_SERVER['QUERY_STRING']) AND $_SERVER['QUERY_STRING'] != "") {
		$_SERVER['REQUEST_URI'] .= "?".$_SERVER['QUERY_STRING'];
	}
}

//php registered_global off
//prevent exploits for users who have registered globals on
foreach($GLOBALS["_GET"] as $varname => $varvalue) {
	if(isset($$varname)) unset($$varname);
	if (((strpos($varname, 'id') !== FALSE) || $varname == 'page') && (!ctype_digit($varvalue) && !empty($varvalue))) die('Possible XSS attack detected');
	$_GET[$varname] = RemoveXSS($varvalue);
}
reset($GLOBALS["_GET"]);
foreach($GLOBALS["_POST"] as $varname => $varvalue) {
	$_POST[$varname] = RemoveXSS($varvalue);
	if(isset($$varname)) unset($$varname);
}
reset($GLOBALS["_POST"]);
//var_dump($GLOBALS["_POST"]);
foreach($GLOBALS["_COOKIE"] as $varname => $varvalue) {
	if(isset($$varname)) unset($$varname);
}
reset($GLOBALS["_COOKIE"]);
foreach($GLOBALS["_SERVER"] as $varname => $varvalue) {
	if(isset($$varname)) unset($$varname);
}
reset($GLOBALS["_SERVER"]);
if(!empty($GLOBALS['_ENV'])) foreach($GLOBALS["_ENV"] as $varname => $varvalue) {
	if(isset($$varname)) unset($$varname);
}
reset($GLOBALS["_SERVER"]);
foreach($GLOBALS["_FILES"] as $varname => $varvalue) {
	if(isset($$varname)) unset($$varname);
}
reset($GLOBALS["_FILES"]);
if(!empty($GLOBALS['_ENV'])) {
	foreach($GLOBALS["_REQUEST"] as $varname => $varvalue) {
		if(isset($$varname)) unset($$varname);
	}
	reset($GLOBALS["_REQUEST"]);
}

// PHP5.1.0 new timezone req's
// This has to be here, because some date() functions are called before user is verified
// This makes UPB's timezone functions obsolete (but we need them for backwards compadibility with PHP4)
if(function_exists("date_default_timezone_set")) {
	$timezone = "Europe/London";
	if(isset($_COOKIE["timezone"]) && $_COOKIE["timezone"] != "")
	$timezone = timezone_name_from_abbr("", (int)$_COOKIE["timezone"]*3600, 0);
	date_default_timezone_set($timezone);

}

require_once("./includes/inc/defines.inc.php");
$errorHandler = new ErrorHandler();
//set_error_handler(array(&$errorHandler, 'add_error'));
error_reporting(E_ALL ^ E_NOTICE);

//Verify that we're not using a ver. 1 database, otherwise prompt the admin to run the updater
if (!file_exists("./db/main.tdb") && file_exists("./db/config2.php")) die(MINIMAL_BODY_HEADER.str_replace('__TITLE__', 'Update Available:', str_replace('__MSG__', 'An update has not been run yet.  Please follow the directions in the readme file to run it to continue.', ALERT_MSG)).MINIMAL_BODY_FOOTER);
if (file_exists("config.php")) {
	require_once("config.php");
}
//Verify that a database exists, otherwise prompt the admin to run the installer
if (!defined('DB_DIR')) die(MINIMAL_BODY_HEADER.str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'The installer has not been run it.  Please <a href="install.php">run this</a> to continue.', ALERT_MSG)).MINIMAL_BODY_FOOTER);
if(!is_dir(DB_DIR)) die(MINIMAL_BODY_HEADER.str_replace('__TITLE__', 'Fatal:', str_replace('__MSG__', 'The data directory is missing.', ALERT_MSG)).MINIMAL_BODY_FOOTER);
if (UPB_VERSION != "2.2.7" && (FALSE === strpos($_SERVER['PHP_SELF'], 'update') && FALSE === strpos($_SERVER['PHP_SELF'], 'upgrade'))) die(MINIMAL_BODY_HEADER.str_replace('__TITLE__', 'Update Available:', str_replace('__MSG__', 'An update has not been run yet.  Please follow the directions in the readme file to run it to continue.', ALERT_MSG)).MINIMAL_BODY_FOOTER);

//Check to see if User is banned
if(file_exists(DB_DIR.'/banneduser.dat')) {
	$banned_addresses = explode("\n", file_get_contents(DB_DIR.'/banneduser.dat'));
	if((isset($_COOKIE["user_env"]) && in_array($_COOKIE["user_env"], $banned_addresses)) || in_array($_SERVER['REMOTE_ADDR'], $banned_addresses))
	die(MINIMAL_BODY_HEADER.str_replace('__TITLE__', 'Notice:', str_replace('__MSG__', 'You have been banned from this bulletin board.<br>'.ALERT_GENERIC_MSG, ALERT_MSG)).MINIMAL_BODY_FOOTER);
}

//whos_online.php included at last line

//installation precausion
//globalize resource $tdb to prevent multiple occurances
if(!file_exists(DB_DIR."/main.tdb"))
{
	echo "File missing";
	die();
}

if(file_exists(DB_DIR."/main.tdb")) {
	$tdb = new TdbFunctions(DB_DIR.'/', 'main.tdb');
	//$tdb->define_error_handler(array(&$errorHandler, 'add_error'));
	$tdb->setFp('users', 'members');
	$tdb->setFp('forums', 'forums');
	$tdb->setFp('cats', 'categories');
	$tdb->setFp('getpass', 'getpass');
	$tdb->setFp("uploads", "uploads");

	//UPB's main Vars
	$config_tdb = new ConfigSettings();
	$config_tdb->setFp("config", "config");
	$config_tdb->setFp("ext_config", "ext_config");

	$_CONFIG = $config_tdb->getVars("config");

	$_REGISTER = $config_tdb->getVars("regist");
	$_REGIST = &$_REGISTER;
	$_STATUS = $config_tdb->getVars("status");

	//integrate into admin_config
	$_CONFIG["where_sep"] = "<b>&gt;</b>";
	$_CONFIG["table_sep"] = "<b>::</b>";

	define('SKIN_DIR', $_CONFIG['skin_dir'], true);

	if (!defined('DB_DIR')) die(MINIMAL_BODY_HEADER.str_replace('__TITLE__', 'Fatal Error:', str_replace('__MSG__', 'The DB_DIR constant is undefined.<br>Please go to <a href="http://myupb.com/" target="_blank">MyUPB.com</a> for support.', ALERT_MSG)).MINIMAL_BODY_FOOTER);
	if (!is_array($_CONFIG)) die(MINIMAL_BODY_HEADER.str_replace('__TITLE__', 'Fatal Error:', str_replace('__MSG__', 'Unable to correctly access UPB\'s configuration.<br>Please go to <a href="http://forum.myupb.com/" target="_blank">forum.myupb.com</a> for support.', ALERT_MSG)).MINIMAL_BODY_FOOTER);
	if (SKIN_DIR == '' || !defined('SKIN_DIR')) die(MINIMAL_BODY_HEADER.str_replace('__TITLE__', 'Fatal Error:', str_replace('__MSG__', 'The SKIN_DIR constant is undefined.<br>This may be an indication UPB was unable to correctly access its configuration.<br>Please go to <a href="http://forum.myupb.com/" target="_blank">forum.myupb.com</a> for support.', ALERT_MSG)).MINIMAL_BODY_FOOTER);

	require_once('./includes/whos_online.php');
}
?>
