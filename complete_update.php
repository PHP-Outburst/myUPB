<?php
session_start();
ignore_user_abort();
if (TRUE !== is_writable('./config.php')) die('Unable to continue with the installation process.  "config.php" in the root upb directory MUST exist and MUST BE writable.');
if (filesize('./config.php') > 0) {
	require_once('./config.php');
}

$lines = explode("\n", file_get_contents('./config.php'));

for($i=0;$i<count($lines);$i++) {
	if(FALSE !== strpos($lines[$i], 'INSTALLATION_MODE')) unset($lines[$i]);
	if(FALSE === strpos($lines[$i], 'UPB_VERSION')) continue;
	$lines[$i] = "define('UPB_VERSION','2.2.5', true);";
	break;
}

$f = fopen('./config.php', 'w');
fwrite($f, implode("\n", $lines));
fclose($f);
?>
<!DOCTYPE html>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<title>UPB v2.2.5 Updater</title>
<link rel='stylesheet' type='text/css'
	href='./skins/default/css/style.css' />
</head>
<body>
<div id='upb_container'>
<div class='main_cat_wrapper2'>
<table class='main_table_2'>
	<tr>
		<td id='logo'><img src='./skins/default/images/logo.png' alt=''
			title='' /></td>
	</tr>
</table>
</div>
<br />
<br />
<div class='main_cat_wrapper'>
<div class='cat_area_1'>myUPB v2.2.5 Updater</div>
<table class='main_table'>
	<tr>
		<th style='text-align: center;'>&nbsp;</th>
	</tr>
	<tr>
		<td class='area_welcome'>
		<div class='welcome_text'>If you had any problems, please seek support
		at <a href='http://forum.myupb.com/'>myupb.com's support forums!</a></div>
		</td>
	</tr>
	<tr>
		<td class='footer_3'><img src='../skins/default/images/spacer.gif'
			alt='' title='' /></td>
	</tr>
	<tr>
		<td class='area_2'
			style='text-align: center; font-weight: bold; padding: 12px; line-height: 20px;'>
		Congratulations
		<P>Your forum has now been updated to version 2.2.5
		
		
		<p>
		
		
		<p style='font-weight: bold;'>Please remove install.php, all the
		update files and complete_update.php after closing this page.<br>
		Failure to do so is a security risk.</p>
		Keep an eye out for future updates on the myUPB forums or by clicking
		on "Check for updates" in your admin panel.<br />
		Progress about future updates can be found on the <a
			href='http://www.myupb.com/news/'>Blog</a> <br />
		<br />

		</td>
	</tr>
	<tr>
		<td class='footer_3'><img src='./skins/default/images/spacer.gif'
			alt='' title='' /></td>
	</tr>
</table>
<div class='footer'><img src='skins/default/images/spacer.gif' alt=''
	title='' /></div>
</div>
<br />
<div class='copy'>Powered by myUPB&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a
	href='http://www.myupb.com/'>PHP Outburst</a> &nbsp;&nbsp;&copy;2002 -
<?php echo date("Y",time()); ?></div>
</div>
</body>
</html>
