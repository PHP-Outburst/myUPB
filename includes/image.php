<?php
// Private Messaging System
// Add on to Ultimate PHP Board V2.0
// Original PM Version (before _MANUAL_ upgrades): 2.0
// File Created by fraser
// Using textdb Version: 4.2.3
require_once('./inc/encode.inc.php');
$decid = urldecode(md5_decrypt($_REQUEST['id'], $_REQUEST['key']));
header("Content-type: image/jpg");
$img = imagecreatetruecolor(80, 20);
for($i = 0; $i < 3; $i++) {
	$lighthex1 = rand($i, 255);
	$lighthex2 = rand($i, 255);
	$lighthex3 = rand($i, 255);
	$darkhex1 = rand($i, 100);
	$darkhex2 = rand($i, 100);
	$darkhex3 = rand($i, 100);
	$lightcolor = imagecolorallocate($img, $lighthex1, $lighthex2, $lighthex3);
	$darkcolor = imagecolorallocate($img, $darkhex1, $darkhex2, $darkhex3);
	imagefill($img, 0, 0, $lightcolor);
	//$font = 'LucidaConsole.ttf';
	imagestring($img, 5, 10, $i, $decid, $darkcolor);
}
// Call a valid image exporter
if(function_exists("imagegif")) {
	header("Content-type: image/gif");
	imagegif($img);
} elseif(function_exists("imagejpeg")) {
	header("Content-type: image/jpeg");
	imagejpeg($img, "", 100);
} elseif(function_exists("imagepng")) {
	header("Content-type: image/png");
	imagepng($img);
} elseif (function_exists("imagewbmp")) {
	header("Content-type: image/vnd.wap.wbmp");
	imagewbmp($img);
}
else {
	die("No image support on this server");
}
imagedestroy($img);
?>
