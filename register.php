<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
// Ultimate PHP Board Register
require_once('./includes/upb.initialize.php');
$where = "Register";
$required = "#ff0000";
if ($tdb->is_logged_in() && $_COOKIE['power_env'] < 3)
MiscFunctions::exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'You cannot register if you are already logged in.', ALERT_MSG)), true);

if(!isset($_GET['action'])) $_GET['action'] = '';
if($_GET['action'] == 'validate' && !$_REGIST['reg_approval']) {
	if(!isset($_GET['id']) || $_GET['id'] == '' || !ctype_digit($_GET['id'])) {
		MiscFunctions::exitPage(str_replace('__TITLE__', 'Invalid User ID.', str_replace('__MSG__', ALERT_GENERIC_MSG, ALERT_MSG)), true);
	} elseif(FALSE === ($rec = $tdb->get('users', $_GET['id']))) {
		MiscFunctions::exitPage(str_replace('__TITLE__', 'User Does Not Exist.', str_replace('__MSG__', ALERT_GENERIC_MSG, ALERT_MSG)), true);
	}

	if(!isset($_GET['code']) || $_GET['code'] == '' || $_GET['code'] != $rec[0]['reg_code']) {
		MiscFunctions::exitPage(str_replace('__TITLE__', 'Invalid Confirmation Code.', str_replace('__MSG__', ALERT_GENERIC_MSG, ALERT_MSG)), true);
	}

	$tdb->edit('users', $_GET['id'], array('reg_code' => ''));
	require_once('./includes/header.php');
	?>
<div class='alert_confirm'>
<div class='alert_confirm_text'><strong>Attention:</strong></div>
<div style='padding: 4px;'>Your e-mail address was successfully
confirmed. You can now log into the bulletin board.</div>
</div>
	<?php
	require_once('./includes/footer.php');
	exit;
} elseif($_GET['action'] == 'resend' && !$_REGIST['reg_approval']) {
	if(!isset($_GET['id']) || $_GET['id'] == '' || !ctype_digit($_GET['id'])) {
		MiscFunctions::exitPage(str_replace('__TITLE__', 'Invalid User ID.', str_replace('__MSG__', ALERT_GENERIC_MSG, ALERT_MSG)), true);
	} elseif(FALSE === ($rec = $tdb->get('users', $_GET['id']))) {
		MiscFunctions::exitPage(str_replace('__TITLE__', 'User Does Not Exist.', str_replace('__MSG__', ALERT_GENERIC_MSG, ALERT_MSG)), true);
	} elseif($rec[0]['reg_code'] == '') {
		MiscFunctions::exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'Confirmation Code already recieved.  You do not need to resend your Confirmation Code.', ALERT_MSG)), true);
	}
	$reg_code = uniqid('reg_', true);
	// get the user's email address, NOTE: password is not available as it has already been encrypted.
	$details = $tdb->query("users","id='{$_GET['id']}'",1,1,array('user_name','email'));
	$register_msg = str_replace(
	array('<login>', '<password>', '<url>','<x>'),
	array($details[0]['user_name'], 'UNAVAILABLE', "http://{$_SERVER['SERVER_NAME']}{$_SERVER['PHP_SELF']}?action=validate&id={$_GET['id']}&code={$reg_code}",''),
	$_REGISTER['register_msg']);
	if (!@mail($details[0]['email'], $_REGISTER["register_sbj"], $register_msg, "From: ".$_REGISTER["admin_email"])) {
		$email_status = false;
		if($_CONFIG['email_mode']) {
			$config_tdb->editVars('config', array('email_mode' => '0'));
		}
	} else {
		$email_status = true;
		if(!$_CONFIG['email_mode']) $config_tdb->editVars('config', array('email_mode' => '1'));
	}
	$tdb->edit('users', $_GET['id'], array('reg_code' => $reg_code));

	require_once('./includes/header.php');

	if($email_status) {
		?>
<div class ='alert_confirm'>
<div class ='alert_confirm_text'><strong>Attention:</strong></div>
<div style ='padding: 4px;'>A reconfirmation e-mail was successfully sent
to your e-mail address on file. It should arrive in 2 - 5 minutes. If it
doesn't arrive please check your Junk Mail folder. If you haven't
received it after a short while please contact an administrator.</div>
</div>
		<?php
	} else {
		print str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'Unable to resend the confirmation e-mail.  You have to e-mail the bulletin board administrator at <b>'.ADMIN_EMAIL.'</b> and have him or her confirm you.', ALERT_MSG));
	}
	require_once('./includes/footer.php');
	exit;
}

if($_REGIST['disable_reg'] && $_COOKIE['power_env'] < 3)
MiscFunctions::exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'Registration is disabled for this bulletin board.', ALERT_MSG)), true);
if (empty($_POST["show_email"])) $_POST["show_email"] = "";
if (empty($_POST["email_list"])) $_POST["email_list"] = "";
if (!isset($_POST["submit"])) $_POST["submit"] = "";
//---bot honeypot start
if(isset($_POST['email']) && $_POST['email']) {  };
//---bot honeypot end

if (isset($_POST['submit']) && $_POST["submit"] == "Submit") {
	if (!$tdb->is_logged_in() && (empty($_SESSION['captcha']) || strtolower(trim($_REQUEST['captcha'])) != $_SESSION['captcha'])) //checks cool php captcha, repaired registering as admin/mod
	MiscFunctions::exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'You failed the CAPTCHA check.  Please enter the code <b>exactly</b> as it appears.', ALERT_MSG)), true);//captcha failed
	$_POST["u_login"] = strip_tags($_POST["u_login"]);
	$_POST["u_login"] = trim($_POST["u_login"]);


	if ($_POST["u_login"] == '' || $_POST["u_email"] == '')
	MiscFunctions::exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'You did not fill in all required fields. (*)', ALERT_MSG)), true);


	if($_POST['u_email'] != $_POST['u_email2'])
	MiscFunctions::exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'Your e-mails do not match.', ALERT_MSG)), true);

	if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*(\+[_a-z0-9-]+(\.[_a-z0-9-]+)*)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $_POST["u_email"]))
	MiscFunctions::exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'Please enter a valid e-mail (ex: you@host.com).', ALERT_MSG)), true);

	$q = $tdb->query("users", "user_name='".strtolower($_POST["u_login"])."'", 1, 1);
	if (strtolower($_POST["u_login"]) == strtolower($q[0]["user_name"]))
	MiscFunctions::exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'The username you chose is already in use.', ALERT_MSG)), true);
	unset($q);

	$q = $tdb->query("users", "email='".$_POST["u_email"]."'", 1, 1);
	if ($_POST["u_email"] == $q[0]["email"])
	MiscFunctions::exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'Somebody has already registered on this bulletin board with your e-mail address.', ALERT_MSG)), true);
	unset($q);

	if($tdb->is_logged_in() || !isset($_POST['u_pass']) || !isset($_POST['u_pass2'])) {
		$length = "3";
		$vowels = array("a", "e", "i", "o", "u");
		$cons = array("b", "c", "d", "g", "h", "j", "k", "l", "m", "n", "p", "r", "s", "t", "u", "v", "w", "tr", "cr", "br", "fr", "th", "dr", "ch", "ph", "wr", "st", "sp", "sw", "pr", "sl", "cl");
		$num_vowels = count($vowels);
		$num_cons = count($cons);
		for($i = 0; $i < $length; $i++) {
			$u_pass .= $cons[rand(0, $num_cons - 1)].$vowels[rand(0, $num_vowels - 1)];
		}
	} else {
		if($_POST['u_pass'] != $_POST['u_pass2'])
		MiscFunctions::exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'Your passwords do not match.', ALERT_MSG)), true);

		elseif(strlen($_POST['u_pass']) < 6)
		MiscFunctions::exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'Your password has to be at least six characters long.', ALERT_MSG)), true);

		$u_pass = $_POST['u_pass'];
	}
	if ($_POST["show_email"] != "1") $_POST["show_email"] = 0;
	if (strlen($_POST["u_sig"]) > 200)
	MiscFunctions::exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'You cannot have more than 200 characters in your signature.', ALERT_MSG)), true);

	//call to checkdnsrr removed due to false negatives occuring. Some hosts use mail servers that use different domain names to the user email address.

	if (substr(trim(strtolower($_POST["u_site"])), 0, 7) != "http://") $_POST["u_site"] = "http://" . $_POST["u_site"];

	$reg_code = (((!$_CONFIG['email_mode'] && !$_REGIST['reg_approval']) || $tdb->is_logged_in()) ? '' : uniqid('reg_', true)); //create reg_code

	$id = $tdb->add("users",
	array("user_name" => $_POST["u_login"],
		    "password" => Encode::generateHash($u_pass),
		    "level" => 1,
		    "email" => $_POST["u_email"],
		    "view_email" => $_POST["show_email"],
		    "mail_list" => $_POST["email_list"],
		    "location" => $_POST["u_loca"],
		    "url" => $_POST["u_site"],
		    "avatar" => $_POST["avatar"],
		    "icq" => $_POST["u_icq"],
		    "aim" => $_POST["u_aim"],
		    "yahoo" => $_POST["u_yahoo"],
		    "msn" => $_POST["u_msn"],
		    "sig" => chop($_POST["u_sig"]),
		    "posts" => 0,
		    "date_added" => DateCustom::mkdate(),
		    "lastvisit" => DateCustom::mkdate(),
		    "timezone" => $_POST["u_timezone"],
		    'newTopicsData' => serialize(array('lastVisitForums' => array()))
	));

	$register_msg = $_REGISTER['register_msg'];
	$register_msg = str_replace("<login>", $_POST['u_login'], $register_msg);
	$register_msg = str_replace("<password>", $u_pass, $register_msg);
	$register_msg = str_replace("<url>", 'http://'.$_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']) . (($reg_code != '') ? '/register.php?action=validate&id='.$id : '').'&code='.$reg_code, $register_msg);
	if (!@mail($_POST["u_email"], $_REGISTER["register_sbj"], $register_msg, "From: ".$_REGISTER["admin_email"])) {
		$email_status = false;
		if($_CONFIG['email_mode']) $config_tdb->editVars('config', array('email_mode' => '0'));
		if(!$_REGIST['reg_approval']) $reg_code = ''; //remove reg_code if email not sent
	} else {
		$email_status = true;
		if(!$_CONFIG['email_mode']) $config_tdb->editVars('config', array('email_mode' => '1'));
	}
	$_CONFIG['email_mode'] = $email_status;

	//Set reg_code if e-mail is sent out
	if($reg_code != '') {
		clearstatcache();
		$tdb->edit('users', $id, array('reg_code' => $reg_code));
	}


	// If each user sends and receives one PM a day, their table will last 67.2 years
	$temp_tdb = new Tdb(DB_DIR."/", "privmsg.tdb");
	$pmT_num = ceil($id / 100);
	if (FALSE === $temp_tdb->isTable($pmT_num)) $temp_tdb->createTable($pmT_num, array(array("box", "string", 6), array("from", "number", 7), array("to", "number", 7), array("icon", "string", 10), array("subject", "memo"), array("date", "number", 14), array("message", "memo"), array("id", "id")));
	$temp_tdb->cleanup();
	unset($temp_tdb);

	$f = fopen(DB_DIR."/new_pm.dat", 'a');
	fwrite($f, " 0");
	fclose($f);

	require_once('./includes/header.php');
	print "<div class='alert_confirm'>
			<div class='alert_confirm_text'>
			  <strong>Thank you for registering:</strong></div>
			<div style='padding:4px;'>";
	if($tdb->is_logged_in()) {
		print "The user, <b>{$_POST['u_login']}</b>, has been registered.&nbsp;&nbsp;";
		if($email_status)
		print "An e-mail was sent to them, with their username and password.";
		else print "An e-mail could not be sent to them, so you must give them their username and password:
                <br /><strong>Username:</strong> ".$_POST['u_login'];
		echo "<br /><strong>Password:</strong> $u_pass";
	} else {
		echo "<strong>You are now registered!</strong>";
		if($email_status && $_REGIST['reg_approval']) {
			print "<br />An email has been sent to your email account with your username and password.
                You won't be able to log in until an administrator approves your registration.
                <br />It should arrive within 2 - 5 minutes.";
		} else if($email_status && !$_REGIST['reg_approval']) {
			print "<br />A confirmation email has been sent to your email account with your username and password.
                You must click on the URL in the e-mail to verify your e-mail address before you can log in.
                <br />It should arrive within 2 - 5 minutes. If you don't receive it please check your Junk Mail folder.
                <br />If you haven't received your e-mail after a significant amount of time please contact an administrator.";
		} else if(!$email_status && $_REGIST['reg_approval']) {
			print "You won't be able to log in until an administrator approves your registration.";
		} else {
			echo "<br />Your login details:<br /><strong>Username:</strong> ".$_POST['u_login'];
			echo "<br /><strong>Password:</strong> $u_pass";
			echo "<br />Please make a note of your password and then login to change it<br />Click <a href='login.php'><strong>here</strong></a></a>";
		}
	}
	print "
			</div>
		  </div>";
	require_once('./includes/footer.php');
	exit;
} else {
	require_once('./includes/header.php');
	echo "<script language='javascript' src='includes/pwd_meter.js'></script>";
	// security mod if enabled
	if ((bool) $_REGIST['security_code'] === true && !$tdb->is_logged_in())
	{
		$string = md5(rand(0, microtime() * 1000000));
		$verify_string = substr($string, 3, 7);
		$key = md5(rand(0, 999));
		$encid = urlencode(Encode::md5_encrypt($verify_string, $key));
		// rather than the hidden field we have
		$_SESSION['u_keycheck'] = $verify_string;
	}
	echo "<form name='registration' id='registration' action='register.php' method='POST'>";
	MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
			<tr>
				<th colspan='2' style='text-align:left;'><span style='color:$required;'>*</span> is a required field</th>
			</tr>
			<tr>
				<td class='area_1' style='width:45%;'> <strong>User Name:</strong> <span style='color:$required;'>*</span><br />Your identity throughout the bulletin board.</td>
				<td class='area_2'><input type=text name='u_login' size=40 onblur=\"getUsername(this.value,'changeuser');\"><span class='err' id='namecheck'></span></td>
			</tr>
			<tr>
				<td class='area_1'>
					<strong>E-mail Address:</strong> <span style='color:$required;'>*</span>";
	if ((bool)$_REGIST['security_code'] && !$tdb->is_logged_in())
	echo "<br /><span class='description'>A confirmation e-mail is sent to the email address that you provide.</span>";
	echo "</td>
				<td class='area_2'>";
//-----bot honeypot mail start
echo "
<p style='display:none;'>
  <label for='email'>youre Mail be not ask, please write there nothing!:</label>
  <input id='email' name='email' size='60' value='' />
</p>";
//-----bot honeypot mail end
echo "<input type=\"email\" name='u_email' size='40' onblur=\"ValidEmail(this.value);\"><span class='err' id='emailvalid'></span></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Confirm E-mail Address:</strong> <span style='color:$required;'>*</span></td>
				<td class='area_2'><input type=\"text\" name='u_email2' size='40' onblur=\"CheckEmail(document.registration.u_email.value,this.value);\"><span class='err' id='emailcheck'></span></td>
			</tr>
			<tr>
				<td class='area_1'>
					<strong>Make email address visible to everyone?</strong></td>
				<td class='area_2'><input type=checkbox name=show_email value='1'></td>
			</tr>";
	if (!$tdb->is_logged_in())
	{
		echo "<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Security Code:</strong> <span style='color:$required;'>*</span><br />Please enter the code in the image:<br />
          <a href=\"#\" onclick=\"document.getElementById('captcha').src='./includes/thirdparty/cool-php-captcha-0.3.1/captcha.php?'+Math.random();
		  document.getElementById('captcha-form').focus();\" 
		  id=\"change-image\">Load new image?</a></td>
				<td class='area_2'><div><img src=\"./includes/thirdparty/cool-php-captcha-0.3.1/captcha.php\" id=\"captcha\"/></div>
				<input type=\"text\" name=\"captcha\" id=\"captcha-form\" autocomplete=\"off\" size=\"40\"/></td>
			</tr>";
	}
	if(!$tdb->is_logged_in()) echo "<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'>
					<strong>Password:</strong> <span style='color:$required;'>*</span><br />
					<span class='description'>Your Password must be at least 6 characters long.<br>The strength meter is for guidance only</span>
			    </td>
				<td class='area_2'><input type='password' name='u_pass' size='40' onkeyup=\"runPassword(this.value);\"><div style=\"font-size: 10px;\">Password Strength: <span id=\"u_pass_text\" style=\"font-size: 10px;\"></span></div></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Confirm Password:</strong> <span style='color:$required;'>*</span></td>
				<td class='area_2'><input type='password' name='u_pass2' size='40'></td>
			</tr>";
	echo "<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>";



    MiscFunctions::echoTableFooter(SKIN_DIR);
    MiscFunctions::echoTableHeading("Other Information", $_CONFIG);
	echo "
			<tr>
				<th colspan='2' style='text-align:left;'>These areas can also be completed at a later time in your account settings.</th>
			</tr>
			<tr>
				<td class='area_1' style='width:45%;'><strong>Location:</strong><br />Where are you from? (it can be anything)</td>
				<td class='area_2'><input type=text name='u_loca' size='40';</td>
			</tr>
			<tr>
				<td class='area_1'><strong>Website URL:</strong><br />please include the http:// in front of url</td>
				<td class='area_2'><input type=text name=u_site size=40></td>
			</tr>
			<tr>
				<td class='area_1'>
					<strong>Avatar:</strong></td>
				<td class='area_2'>Choose an avatar in your UserCP after logging in.</td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>ICQ:</strong><br />If you have ICQ put your number here</td>
				<td class='area_2'><input type=text name=u_icq size=40></td>
			</tr>
			<tr>
				<td class='area_1'><strong>AIM:</strong><br />If you have AOL Instant messanger, please type your SN</td>
				<td class='area_2'><input type=text name=u_aim size=40></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Yahoo!:</strong><br />If you have Yahoo! messanger, please type your SN</td>
				<td class='area_2'><input type=text name=u_yahoo size=40></td>
			</tr>
			<tr>
				<td class='area_1'><strong>MSN:</strong><br />If you have MSN Instant messanger please type your SN</td>
				<td class='area_2'><input type=text name=u_msn size=40></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Signature:</strong><br />Your signature is appended to each of your messages</td>
				<td class='area_2'><textarea name=u_sig cols=45 rows=10></textarea></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Timezone Setting:</strong></td>
				<td class='area_2'>".MiscFunctions::timezonelist()."</td></tr>";
	echo "
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type='submit' id='submit' name='submit' value='Submit' disabled>&nbsp;<input type='reset' id='reset' name='reset' value='Reset'></td>
			</tr>";
    MiscFunctions::echoTableFooter(SKIN_DIR);
	echo "</form>";
	require_once('./includes/footer.php');
	if(!isset($_SESSION['iplogged']) || ($_SESSION['iplogged']+300) < time()) {
		$_SESSION['iplogged'] = time();
		$user = ((empty($_COOKIE["user_env"])) ? "guest" : $_COOKIE["user_env"]);;
		$visitor_info = ((!isset($_SERVER['REMOTE_HOST']) || $_SERVER['REMOTE_HOST'] == "") ? $_SERVER['REMOTE_ADDR'] : $_SERVER['REMOTE_HOST']);
		$base = "http://" . $_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF'];
		$date = date("r", time());

		$fp = fopen(DB_DIR."/ip.log", "a");
		fputs($fp, $visitor_info."\t".$user."\t".$base."\t".time()."\t".$_SERVER['HTTP_USER_AGENT']."\n");
		//fputs($fp, "<strong>$visitor_info</strong> -<i>".$_SERVER['HTTP_USER_AGENT']."</i>- <strong>$user</strong>- <br />Accessed \"$base\" on: $date.--------------------------------Next Person<p><br />\r\n");
		fclose($fp);

		if(filesize(DB_DIR."/ip.log") > (1024 * 1024)) {
			$fp = fopen(DB_DIR."/ip.log", 'r');
			fseek($fp, (filesize(DB_DIR."/ip.log") - (1024 * 1024)));
			$log = fread($fp, (1024 * 1024));
			fclose($fp);
			$fp = fopen(DB_DIR."/ip.log", 'w');
			fwrite($fp, $log);
			fclose($fp);
		}
	}
}
?>