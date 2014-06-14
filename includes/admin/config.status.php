<?php
if(!defined('DB_DIR')) die('This script must be run under a wrapper');
if(!$tdb->is_logged_in() || $_COOKIE['power_env'] < 3) die ('You must be logged in as an administrator to execute this page');

reset($_POST);
while(list($key, $val) = each($_POST)) {
	if(FALSE === strpos($POST[$i], 'status')) {
		continue;
	}
	$_POST[$key] = stripslashes($val);
}
reset($_POST);
?>
