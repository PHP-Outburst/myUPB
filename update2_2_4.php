<?php
require_once("./includes/upb.initialize.php");
$from_version = UPB_VERSION;
$to_version = "2.2.5";

$where = "Updating to $to_version";
if (file_exists('./includes/script-styles.js'))
unlink('./includes/script-styles.js');

$result = $config_tdb->basicQuery('config','name','custom_avatars');

if (!empty($_POST))
{
	$config_tdb->delete('custom_avatars');
	$config_tdb->add('avatarupload_size', $_POST['avatarupload_size'], 'regist', 'number', 'text', '8', '2', 'Size Limits For Avatar Uploads', 'In kilobytes, type in the maximum size allowed for avatar uploads<br><i>Note: Setting to 0 will only allow linked avatars</i>');
	$config_tdb->add('avatarupload_dim', $_POST['avatarupload_dim'], 'regist', 'number', 'text', '8', '3', 'Dimension Limits For Avatar Uploads', 'In pixels, type in the maximum size allowed for avatar uploads<br>e.g.100 will allow avatars up to 100x100px. If either the width or height exceeds this limit the avatar will be resized maintaining the correct ratio<br><i>Note: Setting to 0 will only allow linked avatars</i>');
	$config_tdb->add('custom_avatars', '1', 'regist', 'number', 'dropdownlist', '8', '4', 'Custom Avatars', 'Allow users to link or upload their own avatars instead of choosing them locally in images/avatars/. Select <b>Both</b> to allow both types of avatar', 'a:4:{i:0;s:7:"Disable";i:1;s:4:"Link";i:2;s:6:"Upload";i:3;s:4:"Both";}');
	$config_tdb->editVars('regist',array('custom_avatars'=>$result[0]['value']));
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
		
		
		<p><input type='button' onclick="location.href='update2_2_5.php'"
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
	<?php
}
else
{
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
		<th style='text-align: center;' colspan='2'>&nbsp;</th>
	</tr>
	<tr>
		<td class='area_welcome' colspan='2'>
		<div class='welcome_text'>If you had any problems, please seek support
		at <a href='http://forum.myupb.com/'>myupb.com's support forums!</a></div>
		</td>
	</tr>
	<tr>
		<td class='footer_3' colspan='2'><img
			src='./skins/default/images/spacer.gif' alt='' title='' /></td>
	</tr>
	<tr>
		<form method='POST' action='<?php echo $_SERVER['PHP_SELF'];?>'>
		<td class='area_1' width='40%'>In kilobytes, type in the maximum size
		allowed for avatar uploads<br>
		<i>Note: Setting to 0 will only allow linked avatars</i></td>
		<td class='area_2'><input name='avatarupload_size' value='20'
			size='10'></td>
	
	</tr>
	<td class='area_1'>In pixels, type in the maximum size allowed for
	avatar uploads<br>
	e.g. 100 will allow avatars up to 100x100px.<br>
	If either the width or height exceeds this limit the avatar will be
	resized maintaining the correct ratio<br>
	<i>Note: Setting to 0 will only allow linked avatars</i></td>
	<td class='area_2'><input name='avatarupload_dim' value='100' size='10'><br>
	100px by 100px is the biggest size possible without breaking the
	default forum layout.</td>
	</tr>
	<tr>
		<td class='area_2'
			style='text-align: center; font-weight: bold; padding: 12px; line-height: 20px;'
			colspan='2'>
		<p style='font-weight: normal;'>A new option has been added to custom
		avatar display which will allow users to choose between uploading an
		avatar or linking to one. Choose "Both" from the custom avatar option
		in the Admin Panel to enable this option.</p>
		<p><input type='submit' name='submit' value='Proceed'>&nbsp;<input
			name='reset' type='reset' value='Reset the form'>
		
		
		</form>
		</td>
	</tr>

	<tr>
		<td class='footer_3' colspan='2'><img
			src='./skins/default/images/spacer.gif' alt='' title='' /></td>
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
	<?php

}
?>
