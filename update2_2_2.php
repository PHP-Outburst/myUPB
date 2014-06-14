<?php
require_once("config.php");
$from_version = UPB_VERSION;
$to_version = "2.2.2";

$where = "Updating $from_version to $to_version";
?>
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
		<td class='footer_3'><img src='./skins/default/images/spacer.gif'
			alt='' title='' /></td>
	</tr>
	<tr>
		<td class='area_2'
			style='text-align: center; font-weight: bold; padding: 12px; line-height: 20px;'>
		<p><?php echo $where; ?>
		
		
		<p><input type='button' onclick="location.href='update2_2_3.php'"
			value='Click here to proceed to next step'>
		
		</td>
	</tr>
	<tr>
		<td class='footer_3'><img src='./skins/default/images/spacer.gif'
			alt='' title='' /></td>
	</tr>
</table>
<div class='footer'><img src='./skins/default/images/spacer.gif' alt=''
	title='' /></div>
</div>
<br />
<div class='copy'>Powered by myUPB&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a
	href='http://www.myupb.com/'>PHP Outburst</a> &nbsp;&nbsp;&copy;2002 -
<?php echo date("Y",time()); ?></div>
</div>
</body>
</html>
