<?php
// do not cache!
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header ("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header ("Pragma: no-cache");
if (!isset($_GET["ref"])) $_GET["ref"] = "index.php";
//Delete user from whos_online system
require_once('./includes/upb.initialize.php');
require_once('config.php');

if (isset($_COOKIE['id_env'])) $user_id = $_COOKIE['id_env'];
else $user_id = getenv("REMOTE_ADDR");
$old = mkdate() - 3600;
$old = $old.str_repeat(' ', 14 - strlen($old));
//$whos_online_array = explode("\n", substr($whos_online_log, 0, -1));
$whos_online_array = file(DB_DIR.'/whos_online.dat');
$whos_online_array = array_reverse($whos_online_array);
$whos_online_count = count($whos_online_array);
for($wi = 0; $wi < $whos_online_count; $wi++) {
	if ($user_id == trim(substr($whos_online_array[$wi], 20, 16))) {
		unset($whos_online_array[$wi]);
		break;
	}
}
$whos_online_log = implode("\n", array_reverse($whos_online_array))."\n";
$f = fopen(DB_DIR.'/whos_online.dat', 'w');
fwrite($f, $whos_online_log);
setcookie("user_env", "", time() - 3600);
setcookie("uniquekey_env", "", time() - 3600);
setcookie("power_env", "", time() - 3600);
setcookie("id_env", "", time() - 3600);
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<title>Logoff</title>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<link rel='stylesheet' type='text/css'
	href='<?php print SKIN_DIR."/css/style.css";?>' />
<meta http-equiv='refresh' content='2;URL=<?php print $_GET["ref"]; ?>'>

</head>
<body>
<div id='upb_container'>
<div class='main_cat_wrapper2'>
<table class='main_table_2' cellspacing='1'>
	<tr>
		<td id='logo'><img src='<?php print $_CONFIG['logo'];?>' alt=''
			title='' /></td>
	</tr>
</table>
</div>
<br />
<br />
<div class='alert_confirm'>
<div class='alert_confirm_text'><strong>Attention:</strong></div>
<div style='padding: 4px;'>You have successfully logged off.</div>
</div>
<div class='copy'>Powered by myUPB&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a
	href='http://www.myupb.com/'>PHP Outburst</a> &nbsp;&nbsp;&copy;2002 -
<?php print date("Y",time()); ?></div>
</div>
</body>
</html>
