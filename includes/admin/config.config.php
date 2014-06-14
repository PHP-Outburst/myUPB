<?php
if(!defined('DB_DIR')) die('This script must be run under a wrapper');
if(!$tdb->is_logged_in() || $_COOKIE['power_env'] < 3) die ('You must be logged in as an administrator to execute this page');

$_POST['title'] = stripslashes($_POST['title']);
$_POST['servicemessage'] = stripslashes($_POST['servicemessage']);
$_POST['censor'] = stripslashes($_POST['censor']);
$_POST['sticky_note'] = stripslashes($_POST['sticky_note']);

?>
