<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
if(!defined('DB_DIR')) die('This is a wrapper script!');
$hits = (int)file_get_contents(DB_DIR.'/hits.dat');
$hits++;
$h_f = fopen(DB_DIR."/hits.dat", "w");
fwrite($h_f, $hits);
fclose($h_f);
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
// Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// always modified
header ("Cache-Control: no-cache, must-revalidate");
// HTTP/1.1
header ("Pragma: no-cache");
echo "
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<title>".$_CONFIG["title"]."</title>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<link rel='stylesheet' type='text/css' href='".SKIN_DIR."/css/style_simple.css' />
<script type='text/javascript' src='./includes/scripts.js'></script>
</head>
<body>";
?>
