<?php

/**
 * This include file contains special define's used around the forums.
 *
 * Moved from upb.initialize.php
 *
 * @author Original author unknown
 */

define("ALERT_MSG", "
	<div class='alert'>
		<div class='alert_text'><strong>__TITLE__</strong></div>
		<div style='padding:4px;'>__MSG__</div>
	</div><br>", true);
define("CONFIRM_MSG", "
	<div class='alert_confirm'>
		<div class='alert_confirm_text'><strong>__TITLE__</strong></div>
		<div style='padding:4px;'>__MSG__</div>
	</div><br>", true);
define('ALERT_GENERIC_TITLE', 'Attention:', true);
define('ALERT_GENERIC_MSG', 'If you feel you\'ve reached this message in error, contact the forum administrator or web master.', true);
define('MINIMAL_BODY_HEADER', "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<title>MyUPB</title>
<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
<link rel='stylesheet' type='text/css' href='skins/default/css/style.css' />
</head>
<body>
<div id='upb_container'>
	<div class='main_cat_wrapper2'>
		<table class='main_table_2' cellspacing='1'>
			<tr>
				<td id='logo'><img src='skins/default/images/logo.png' alt='' title='' /></td>
			</tr>
		</table>
	</div>
	<br />
	<br />", true);
define('MINIMAL_BODY_FOOTER', "
	<div class='copy'>Powered by myUPB&nbsp;&nbsp;&middot;&nbsp;&nbsp;<a href='http://www.myupb.com/'>PHP Outburst</a>
		&nbsp;&nbsp;&copy;2002 - ".date("Y",time())."</div>
</div>
</body>
</html>", true);

?>