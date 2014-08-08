<?php
// install.php
// designed for Ultimate PHP Board
// Author: Clark
// Website: http://www.myupb.com
// Version: 2.2.7
// Using textdb Version: 4.4.1

require_once 'includes/inc/func.inc.php';

ignore_user_abort();
if(!isset($_POST['add'])) $_POST['add'] = '0';
if ($_POST["add"] == "2auth") {
	//Verify admin account
	$error = "";
	if ($_POST["username"] == "" || strlen($_POST["username"]) > 20) $error .= "<div style='text-align:center;font-weight:bold;'>Your Username is either too short or too long (max 20 chars, min 1 char)</div><br /><br />";
	if ($_POST["pass1"] != $_POST["pass2"]) $error .= "<div style='text-align:center;font-weight:bold;'>your password and password confirmation do not match!</div><br /><br />";
	if (strlen($_POST["pass1"]) < 5) $error .= "<div style='text-align:center;font-weight:bold;'>your password has to be longer then 4 characters!</div><br /><br />";
	if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $_POST["email"])) $error .= "<div style='text-align:center;font-weight:bold;'>Not a real e-mail address (ex. admin@host.com)</div><br /><br />";

	// The "view_email" element only is set if the user selects the checkbox
	if (isset($_POST["view_email"]))
	{
		if($_POST["view_email"] == "1")
		{
			$view_email_checked = "CHECKED";
		}
		else
		{
			$_POST["view_email"] = "0";
		}
	}
	else
	{
		$_POST["view_email"] = "0";
	}

	if (strlen($_POST["sig"]) > 200) $error .= "<div style='text-align:center;font-weight:bold;'>Your signature is too long (max 200 chars)</div><br /><br />";
	if ($error != "") {
		$_POST["add"] = '2';
	} else {
		$_POST["add"] = "3adduser";
	}
} else if ($_POST['add'] == "3auth") {
	$error = array();
	if($_POST['title'] == '') $error[] = 'You cannot leave the <b>title</b> field blank.';
	if($_POST['register_sbj'] == '') $error[] = 'You cannot leave the <b>Register E-mail Subject</b> field blank.';
	if($_POST['register_msg'] == '') $error[] = 'You cannot leave the <b>Register E-mail Message</b> field blank.';
	if($_POST['register_msg'] != '' && FALSE === strpos($_POST['register_msg'], '<login>')) $error[] = 'You must include the tag &lt;login&gt; in the <b>Register E-mail Message</b> field.';
	if($_POST['register_msg'] != '' && FALSE === strpos($_POST['register_msg'], '<password>')) $error[] = 'You must include the tag &lt;password&gt; in the <b>Register E-mail Message</b> field.';
	if($_POST['register_msg'] != '' && FALSE === strpos($_POST['register_msg'], '<url>')) $error[] = 'You must include the tag &lt;url&gt; in the <b>Register E-mail Message</b> field.';
	if($_POST['fileupload_size'] == '') $error[] = 'You cannot leave the <b>Size limits for file upload</b> field blank.';
	if($_POST['fileupload_size'] != '' && !ctype_digit($_POST['fileupload_size'])) $error[] = 'You must provide a number to the <b>Size limits for file upload</b> field.';

	// Remove any whitespace between file types, we just want to make it easy for newpost.php to parse the list
	$_POST["fileupload_types"] = trim($_POST["fileupload_types"]);
	if($_POST["fileupload_types"] != "")
	{
		$upload_types = explode(",", $_POST["fileupload_types"]);
		foreach($upload_types as $key => $type)
		{
			$upload_types[$key] = trim($type);
		}
		$_POST["fileupload_types"] = implode(",", $upload_types);
	}

	if($_POST['admin_email'] == '') $error[] = 'You cannot leave the <b>Admin E-mail</b> field blank.';
	if($_POST['admin_email'] != '' && !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $_POST["admin_email"])) $error[] = 'You must provide a <i>valid</i> email for the <b>Admin E-mail</b> field.';
	if($_POST['homepage'] == '') $error[] = 'You cannot leave the <b>Homepage URL</b> field blank.';
	if(!empty($error)) {
		$_POST['add'] = '3';
	} else {
		$_POST['add'] = '4';
	}
}
?>
<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' lang='en'>
<head>
<title>UPB v2.2.5 Installer</title>
<link rel='stylesheet' type='text/css'
	href='skins/default/css/style.css' />
</head>
<body>
<div id='upb_container'>
<div class='main_cat_wrapper2'>
<table class='main_table_2'>
	<tr>
		<td id='logo'><img src='skins/default/images/logo.png' alt='' title='' /></td>
	</tr>
</table>
</div>
<br />
<br />
<div class='main_cat_wrapper'>
<div class='cat_area_1'><?php
switch($_POST["add"]{0}) {
	case 4: echo "Installation Complete";
	break;
	case 3: echo "Step #2: Setting up the configuration file";
	break;
	case 2: echo "Step #1: Creating your admin account";
	break;
	default: echo "myUPB v2.2.5 Installer";
	break;
}
?></div>
<table class='main_table'>
	<tr>
		<th style='text-align: center;'>&nbsp;</th>
	</tr>
	<tr>
		<td class='area_welcome'>
		<div class='welcome_text'>If you have any problems, please seek
		support at <a href='http://www.myupb.com/'>myupb.com's support forums!</a></div>
		</td>
	</tr>
	<tr>
		<td class='footer_3'><img src='./skins/default/images/spacer.gif'
			alt='' title='' /></td>
	</tr>
	<tr>
		<td class='area_2'
			style='text-align: center; font-weight: bold; padding: 12px; line-height: 20px;'>
			<?php

			if(!is_readable("./") || !is_writable("./")) {
				trigger_error("UPB Root Directory isn't readable or writable.  chmod it to 777 before you can proceed", E_USER_ERROR);
			}
			if(!file_exists("config.php") && !touch("config.php")) {
				trigger_error("Unable to create the file \"config.php\" in the root directory.  Create this file manually before proceeding.", E_USER_ERROR);
			}
			if (!is_writable('config.php')) {
				trigger_error('Unable to continue with the installation process.  "config.php" in the root upb directory MUST exist and MUST BE writable.', E_USER_ERROR);
			}

			if (filesize('config.php') > 0) {
				require_once('config.php');
			}

			if ($_POST["add"] == "0") {
				?> <script type="text/javascript">
		function license_agree(theForm) {
			submitObj = theForm.submit_button;
			checkboxObj = theForm.agree;
			submitObj.disabled = !checkboxObj.checked;
		}
	</script>
		<form action="<?php print $_SERVER['PHP_SELF']; ?>" method="post">
		Thank you for choosing my Ultimate PHP Board.
		<br />
		<br />
		This script will guide you through the process of installing your new
		myUPB bulletin board.
		<p />
		Please read the following license carefully before proceeding
		<p><textarea name='license' style='text-align: left' cols='80'
			rows='25' scrolling='auto'>
			<?php readfile("./license.txt"); ?></textarea>
		<p><label for="agreeID"><input type='checkbox' name='agree'
			id="agreeID" onchange="license_agree(this.form)" /> <b>I agree with
		the Terms and Conditions of this license</b></label></P>

		<br />
		<br />
		<input type='hidden' name='add' value='1' /><input type='submit'
			value='Proceed' name="submit_button" DISABLED />
		
		</form>
		<?php
			} else if($_POST['add'] == '1') {

				if ($_POST['agree'] != 'on')
				{
					?> You must accept the Terms and Conditions of the license before
		proceeding with the installation<br />
		<br />
		<p><a href='<?php echo basename($_SERVER["PHP_SELF"]); ?>'>Return to
		License</a><br />
		<br />
		<br />
		<?php
		die();
				}

				//Set the errorHandler
				$errorHandler = new ErrorHandler();
				set_error_handler(array(&$errorHandler, 'add_error'));
				error_reporting(E_ALL ^ E_NOTICE);

				//define and create the new database folder
				if (!defined('DB_DIR')) {
					define('DB_DIR', './'.uniqid('data_', true), true);
					$f = fopen('./config.php', 'w');
					fwrite($f, "<?php\ndefine('UPB_VERSION', '2.2.7', true);\ndefine('DB_DIR', '".DB_DIR."', true);\n?>");
					fclose($f);
				}

				if (!is_dir(DB_DIR)) {
					if (!mkdir(DB_DIR, 0777)) die('The forum must be able to create a folder in the root forum folder.  Please chmod() the root folder to 777 and rerun the script');
					@mkdir(DB_DIR.'/backup', 0777);
				}
				$uploads_dir = uniqid('uploads_', true);
				if (!is_dir($uploads_dir)) {
					if (!mkdir($uploads_dir, 0777)) die('The forum must be able to create a folder in the root forum folder.  Please chmod() the root folder to 777 and rerun the script');
					touch($uploads_dir. '/index.html');

					// Create a no access file
					$f = fopen($uploads_dir."/.htaccess", "w");
					fwrite($f, "Order deny,allow\nDeny from all");
					fclose($f);
				}
				//end
				//create *.dat files and folders
				touch(DB_DIR.'/banneduser.dat');
				$f = fopen(DB_DIR.'/hits.dat', 'w');
				fwrite($f, '0');
				fclose($f);
				$f = fopen(DB_DIR.'/hits_record.dat', 'w');
				fwrite($f, 'Apr 6, 2005:1');
				fclose($f);
				$f = fopen(DB_DIR.'/hits_today.dat', 'w');
				fwrite($f, '1112852091:1');
				fclose($f);
				touch(DB_DIR.'/ip.log');
				$f = fopen(DB_DIR.'/.htaccess', 'w');
				fwrite($f, 'Order deny,allow
			Deny from all');
				fclose($f);
				$f = fopen(DB_DIR.'/config_org.dat', 'w');
				fwrite($f,
        "config".chr(30)."General".chr(31).
        "status".chr(30)."Members' Statuses".chr(31).
        "regist".chr(30)."New Members".chr(31).
				//.type.chr(30).type name.chr(31)
				chr(29).
        "config".chr(30)."1".chr(30)."Main Forum Config".chr(31).
        "config".chr(30)."9".chr(30)."Posting Settings".chr(31).
        "status".chr(30)."2".chr(30)."Member status".chr(31).
        "status".chr(30)."3".chr(30)."Moderator status".chr(31).
        "status".chr(30)."4".chr(30)."Admin Status".chr(31).
        "status".chr(30)."5".chr(30)."Member status Colors".chr(31).
        "status".chr(30)."6".chr(30)."Who's Online User Colors".chr(31).
        "regist".chr(30)."7".chr(30)."New Users' Confirmation E-mail".chr(31).
        "regist".chr(30)."10".chr(30)."Registration Settings".chr(31).
        "regist".chr(30)."8".chr(30)."Users' Avatars".chr(31));
        //"regist".chr(30)."11".chr(30)."New User Post Settings".chr(31)); //what is this? Maybe I should left it as it is.
				//.type.chr(30).minicat.chr(30).cat name.chr(31)
				fclose($f);
				?><?php
				//allows syntax highlighting to be work again for coding purposes
				touch(DB_DIR.'/whos_online.dat');
				$f = fopen(DB_DIR.'/constants.php', 'w');
				fwrite($f, 'define("TABLE_WIDTH_MAIN", $_CONFIG["table_width_main"], true);'."\n".'define("SKIN_DIR", $_CONFIG["skin_dir"], true);');
				fclose($f);
				//end
				//begin db files
				$tdb = new Tdb('', '');
				$tdb->createDatabase(DB_DIR."/", "main.tdb");
				$tdb->createDatabase(DB_DIR."/", "posts.tdb");
				$tdb->createDatabase(DB_DIR."/", "privmsg.tdb");
				$tdb->createDatabase(DB_DIR."/", "bbcode.tdb");
				$tdb->tdb(DB_DIR."/", "main.tdb");
				$tdb->createTable("members", array(
				array("user_name", "string", 20),
				array("password", "string", 49),
				array("uniquekey", "string", 32),
				array("level", "number", 1),
				array("email", "memo"),
				array("view_email", "number", 1),
				array("status", "memo"),
				array("location", "memo"),
				array("url", "memo"),
				array("avatar", "memo"),
				array("icq", "number", 20),
				array("aim", "string", 24),
				array("yahoo", "memo"),
				array("msn", "memo"),
				array("skype","memo"),
				array("sig", "memo"),
				array("posts", "number", 7),
				array("date_added", "number", 14),
				array("timezone", "string", 3),
				array('newTopicsData', 'memo'),
				array("lastvisit","number",14),
				array('reg_code', 'memo'),
				array("id", "id"),
				), 20);
				$tdb->createTable("forums", array(
				array("forum", "memo"),
				array("cat", "number", 7),
				array("view", "number", 1),
				array("post", "number", 1),
				array("reply", "number", 1),
				array("des", "memo"),
				array("topics", "number", 7),
				array("posts", "number", 7),
				array("id", "id"),
				), 20);
				$tdb->createTable("categories", array(
				array("name", "memo"),
				array("sort", "memo"),
				array("view", "number", 1),
				array("id", "id"),
				), 20);
				$tdb->createTable("getpass", array(
				array("passcode_HASH", "string", 49),
				array("time", "number", 14),
				array("user_id", "number", 7),
				array("id", "id")
				), 30);
				$tdb->createTable("config", array(
				array("name", "memo"),
				array("value", "memo"),
				array("type", "string", 6),
				array('data_type', 'string', 7),
				array("id", "id"),
				), 20);
				$tdb->createTable("ext_config", array(
				array("name", "memo"),
				array("value", "memo"),
				array("type", "string", 6),
				array("title", "memo"),
				array("description", "memo"),
				array("form_object", "string", 8),
				array("data_type", "string", 7),
				array("minicat", "number", 2),
				array("sort", "number", 2),
				array('data_list', 'memo'),
				array("id", "id")
				), 20);
				$tdb->createTable("uploads", array(
				array("name", "string", 80),
				array("size", "number", 9),
				array("downloads", "number", 10),
				array("file_loca", 'string', 80),
				array("id", "id"),
				array('forum_id', 'number', 7),
				array('topic_id', 'number', 7)
				), 2048);
				$tdb->setFp("config", "config");
				$tdb->setFp("ext_config", "ext_config");
				$tdb->tdb(DB_DIR."/", "privmsg.tdb");
				$tdb->createTable("1", array(
				array("box", "string", 6),
				array("from", "number", 7),
				array("to", "number", 7),
				array("icon", "string", 10),
				array("subject", "memo"),
				array("date", "number", 14),
				array("message", "memo"),
				array("id", "id")
				));
				touch(DB_DIR."/blockedlist.dat");
				//$_CONFIG
				?><?php
				$config_tdb = new ConfigSettings();
				$config_tdb->addVar('ver', '2.2.5', 'config', 'text', 'hidden', '','','','');
				$config_tdb->addVar('email_mode', '1', 'config', 'bool', 'hidden','','','','');
				$config_tdb->addVar('admin_catagory_sorting', '', 'config', 'text', 'hidden', '', '', '', '');
				$config_tdb->addVar('banned_words', 'shit,fuck,cunt,pussy,bitch,arse', 'config', 'text', 'hidden', '','','','');
				$config_tdb->addVar('fileupload_location', './'.$uploads_dir, 'config', 'text', 'hidden', '', '', '', ''); //Since upload's name are gone, doesn't make much sense to let user pick the uploads location...

				$config_tdb->addVar('title', 'Discussion Forums', 'config', 'text', 'text', '1', '1', 'Title', 'Title of the forum.');
				$config_tdb->addVar('logo', 'skins/default/images/logo.png', 'config', 'text', 'text', '1', '2', 'Logo Location', 'Can be relative or a URL.');
				$config_tdb->addVar('homepage', 'http://www.myupb.com/', 'config', 'text', 'text', '1', '3', 'Homepage URL', 'Can be relative or a URL.');
				$config_tdb->addVar('skin_dir', './skins/default', 'config', 'text', 'skin', '1', '7', 'Skin Directory', 'Select a skin');
				$config_tdb->addVar('servicemessage', '', 'config', 'text', 'textarea', '1', '8', 'Service Message', 'Service Messages appear above the forum, if nothing input, Announcements will not be displayed. Html is allowed.');

				$config_tdb->addVar('posts_per_page', '20', 'config', 'number', 'text', '9', '1', 'Posts Per Page', 'this is how many posts will be displays on each page for topics.');
				$config_tdb->addVar('topics_per_page', '40', 'config', 'number', 'text', '9', '2', 'Topics Per Page', 'this is how many topics will be displays on each page for forums.');
				$config_tdb->addVar('fileupload_size', '50', 'config', 'number', 'text', '9', '3', 'Size Limits For File Uploads', 'In kilobytes, type in the maximum size allowed for file uploads<br><i>Note: Setting to 0 will <b>disable</b> uploads</i>');
				$config_tdb->addVar('fileupload_types', '', 'config', 'text', 'text', '9', '4', 'File upload allowed types', 'List the allowable file extensions seperated by a comma');
				$config_tdb->addVar('censor', '*censor*', 'config', 'text', 'text', '9', '5', 'Word to replace bad words', 'Words that will replace bad words in a post');
				$config_tdb->addVar('sticky_note', '[Stick Note]', 'config', 'text', 'text', '9', '6', 'Sticky Note Text', 'Text that appends the title indicating it is a \"Stickied Topic\" (HTML Tags Allowed)');
				$config_tdb->addVar('sticky_after', '0', 'config', 'bool', 'checkbox', '9', '7', 'Sticky Note Before or After Title', 'If this is checked, the \"sticky note\" text will appear after the title.  Unchecking this will display it before the title.');

				//$_REGISTER
				$config_tdb->addVar('admin_email', '', 'regist', 'text', 'text', '7', '1', 'Admin E-mail', 'This is the return address for confirmation of registration.');
				$config_tdb->addVar('register_sbj', '', 'regist', 'text', 'text', '7', '2', 'Register Email Subject', 'This is the subject for confirmation of registration.');
				$config_tdb->addVar('register_msg', '', 'regist', 'text', 'textarea', '7', '3', 'Register Email Message', 'This is the message for confirmation of registration.<br>(options: &lt;login&gt;, &lt;password&gt;, and &lt;url&gt;)');

				$config_tdb->addVar('disable_reg', '0', 'regist', 'bool', 'checkbox', '10', '1', 'Disable Registration', 'Checking this will disable public registration (deny access to register.php), and only admins will be able to add users (Add button on "Manage Members" section)');
				//rather useless since we have new captcha since 2.2.8
				//$config_tdb->addVar('security_code', ((extension_loaded('gd')) ? '1' : '0'), 'regist', 'bool', 'checkbox', '10', '2', 'Enable Security Code', 'Enable the CAPTCHA security code image for new user registration<br><strong>Enabling this is recommended.</strong>');
				$config_tdb->addVar('reg_approval', '0', 'regist', 'bool', 'checkbox', '10', '3', 'Approve New Users', 'Checking this will mean after new users register, their account will be disabled until an admin approves their account via "Manage Members"');

				$config_tdb->addVar('newuseravatars', '50', 'regist', 'number', 'text', '8', '1', 'New User Avatars', 'Prevent new users from choosing their own avatars (if "Custom Avatars" is enabled), by defining a minimum post count they must have (Set to 0 to disable)');
				$config_tdb->addVar('avatarupload_size', 10, 'regist', 'number', 'text', '8', '2', 'Size Limits For Avatar Uploads', 'In kilobytes, type in the maximum size allowed for avatar uploads<br><i>Note: Setting to 0 will only allow linked avatars</i>');
				$config_tdb->addVar('avatarupload_dim', 100, 'regist', 'number', 'text', '8', '3', 'Dimension Limits For Avatar Uploads', 'In pixels, type in the maximum size allowed for avatar uploads<br>e.g.100 will allow avatars up to 100x100px. If either the width or height exceeds this limit the avatar will be resized maintaining the correct ratio<br><i>Note: Setting to 0 will only allow linked avatars</i>');
				$config_tdb->addVar('custom_avatars', '1', 'regist', 'number', 'dropdownlist', '8', '4', 'Custom Avatars', 'Allow users to link or upload their own avatars instead of choosing them locally in images/avatars/. Select <b>Both</b> to allow both types of avatar', 'a:4:{i:0;s:7:"Disable";i:1;s:4:"Link";i:2;s:6:"Upload";i:3;s:4:"Both";}');
				//$_STATUS
				$tdb->add("ext_config", array("name" => "member_status1", "value" => "n00b", "type" => "status", "title" => "Member post status 1", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "2", "sort" => "1"));
				$tdb->add("config", array("name" => "member_status1", "value" => "n00b", "type" => "status"));
				$tdb->add("ext_config", array("name" => "member_status2", "value" => "<center>Proby</center>", "type" => "status", "title" => "Member post status 2", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "2", "sort" => "3"));
				$tdb->add("config", array("name" => "member_status2", "value" => "<center>Proby</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "member_status3", "value" => "<center>pro Proby</center>", "type" => "status", "title" => "Member post status 3", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "2", "sort" => "5"));
				$tdb->add("config", array("name" => "member_status3", "value" => "<center>pro Proby</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "member_status4", "value" => "<center>Dude</center>", "type" => "status", "title" => "Member post status 4", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "2", "sort" => "7"));
				$tdb->add("config", array("name" => "member_status4", "value" => "<center>Dude</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "member_status5", "value" => "<center>Experienced Dude</center>", "type" => "status", "title" => "Member post status 5", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "2", "sort" => "9"));
				$tdb->add("config", array("name" => "member_status5", "value" => "<center>Experienced Dude</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "mod_status1", "value" => "<center>Moderator<br /> Jedi Padawan</center>", "type" => "status", "title" => "Moderator post status 1", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "3", "sort" => "1"));
				$tdb->add("config", array("name" => "mod_status1", "value" => "<center>Moderator<br /> Jedi Padawan</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "mod_status2", "value" => "<center>Moderator<br /> Jedi Guardian</center>", "type" => "status", "title" => "Moderator post status 2", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "3", "sort" => "3"));
				$tdb->add("config", array("name" => "mod_status2", "value" => "<center>Moderator<br /> Jedi Guardian</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "mod_status3", "value" => "<center>Moderator<br /> Jedi Knight</center>", "type" => "status", "title" => "Moderator post status 3", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "3", "sort" => "5"));
				$tdb->add("config", array("name" => "mod_status3", "value" => "<center>Moderator<br /> Jedi Knight</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "mod_status4", "value" => "<center>Moderator<br /> Jedi Consular</center>", "type" => "status", "title" => "Moderator post status 4", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "3", "sort" => "7"));
				$tdb->add("config", array("name" => "mod_status4", "value" => "<center>Moderator<br /> Jedi Consular</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "mod_status5", "value" => "<center>Qui-Gon Jinn<br /> Super-Moderator 1000</center>", "type" => "status", "title" => "Moderator post status 5", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "3", "sort" => "9"));
				$tdb->add("config", array("name" => "mod_status5", "value" => "<center>Qui-Gon Jinn<br /> Super-Moderator 1000</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "admin_status1", "value" => "<center>Administrator<br /> Kung Fu Jinn</center>", "type" => "status", "title" => "Admin post status 1", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "4", "sort" => "1"));
				$tdb->add("config", array("name" => "admin_status1", "value" => "<center>Administrator<br /> Kung Fu Jinn</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "admin_status2", "value" => "<center>Administrator<br /> Programming<br /> Kung Fu Qui-Gon Jinn</center>", "type" => "status", "title" => "Admin post status 2", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "4", "sort" => "3"));
				$tdb->add("config", array("name" => "admin_status1", "value" => "<center>Administrator<br /> Programming<br /> Kung Fu Qui-Gon Jinn</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "admin_status3", "value" => "<center>Administrator<br /> Programmer<br /> Extraordinaire</center>", "type" => "status", "title" => "Admin post status 3", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "4", "sort" => "5"));
				$tdb->add("config", array("name" => "admin_status3", "value" => "<center>Administrator<br /> Programmer<br /> Extraordinaire</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "admin_status4", "value" => "<center>Administrator<br /> CompuGlobal<br /> HyperMegaGuy</center>", "type" => "status", "title" => "Admin post status 4", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "4", "sort" => "7"));
				$tdb->add("config", array("name" => "admin_status4", "value" => "<center>Administrator<br /> CompuGlobal<br /> HyperMegaGuy</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "admin_status5", "value" => "<center>Administrator<br /> Programming<br /> Guru</center>", "type" => "status", "title" => "Admin post status 5", "description" => "According to post count", "form_object" => "text", "data_type" => "string", "minicat" => "4", "sort" => "9"));
				$tdb->add("config", array("name" => "admin_status5", "value" => "<center>Administrator<br /> Programming<br /> Guru</center>", "type" => "status"));
				$tdb->add("ext_config", array("name" => "member_post1", "value" => "50", "type" => "status", "title" => "Post count 1", "form_object" => "text", "data_type" => "number", "minicat" => "2", "sort" => "2"));
				$tdb->add("config", array("name" => "member_post1", "value" => "50", "type" => "status"));
				$tdb->add("ext_config", array("name" => "member_post2", "value" => "100", "type" => "status", "title" => "Post count 2", "form_object" => "text", "data_type" => "number", "minicat" => "2", "sort" => "4"));
				$tdb->add("config", array("name" => "member_post2", "value" => "100", "type" => "status"));
				$tdb->add("ext_config", array("name" => "member_post3", "value" => "250", "type" => "status", "title" => "Post count 3", "form_object" => "text", "data_type" => "number", "minicat" => "2", "sort" => "6"));
				$tdb->add("config", array("name" => "member_post3", "value" => "250", "type" => "status"));
				$tdb->add("ext_config", array("name" => "member_post4", "value" => "500", "type" => "status", "title" => "Post count 4", "form_object" => "text", "data_type" => "number", "minicat" => "2", "sort" => "8"));
				$tdb->add("config", array("name" => "member_post4", "value" => "500", "type" => "status"));
				$tdb->add("ext_config", array("name" => "member_post5", "value" => "1000", "type" => "status", "title" => "Post count 5", "form_object" => "text", "data_type" => "number", "minicat" => "2", "sort" => "10"));
				$tdb->add("config", array("name" => "member_post5", "value" => "1000", "type" => "status"));
				$tdb->add("ext_config", array("name" => "mod_post1", "value" => "0", "type" => "status", "title" => "Post count 1", "form_object" => "text", "data_type" => "number", "minicat" => "3", "sort" => "2"));
				$tdb->add("config", array("name" => "mod_post1", "value" => "0", "type" => "status"));
				$tdb->add("ext_config", array("name" => "mod_post2", "value" => "100", "type" => "status", "title" => "Post count 2", "form_object" => "text", "data_type" => "number", "minicat" => "3", "sort" => "4"));
				$tdb->add("config", array("name" => "mod_post2", "value" => "100", "type" => "status"));
				$tdb->add("ext_config", array("name" => "mod_post3", "value" => "250", "type" => "status", "title" => "Post count 3", "form_object" => "text", "data_type" => "number", "minicat" => "3", "sort" => "6"));
				$tdb->add("config", array("name" => "mod_post3", "value" => "250", "type" => "status"));
				$tdb->add("ext_config", array("name" => "mod_post4", "value" => "500", "type" => "status", "title" => "Post count 4", "form_object" => "text", "data_type" => "number", "minicat" => "3", "sort" => "8"));
				$tdb->add("config", array("name" => "mod_post4", "value" => "500", "type" => "status"));
				$tdb->add("ext_config", array("name" => "mod_post5", "value" => "1000", "type" => "status", "title" => "Post count 5", "form_object" => "text", "data_type" => "number", "minicat" => "3", "sort" => "10"));
				$tdb->add("config", array("name" => "mod_post5", "value" => "1000", "type" => "status"));
				$tdb->add("ext_config", array("name" => "admin_post1", "value" => "0", "type" => "status", "title" => "Post count 1", "form_object" => "text", "data_type" => "number", "minicat" => "4", "sort" => "2"));
				$tdb->add("config", array("name" => "admin_post1", "value" => "0", "type" => "status"));
				$tdb->add("ext_config", array("name" => "admin_post2", "value" => "100", "type" => "status", "title" => "Post count 2", "form_object" => "text", "data_type" => "number", "minicat" => "4", "sort" => "4"));
				$tdb->add("config", array("name" => "admin_post2", "value" => "100", "type" => "status"));
				$tdb->add("ext_config", array("name" => "admin_post3", "value" => "250", "type" => "status", "title" => "Post count 3", "form_object" => "text", "data_type" => "number", "minicat" => "4", "sort" => "6"));
				$tdb->add("config", array("name" => "admin_post3", "value" => "250", "type" => "status"));
				$tdb->add("ext_config", array("name" => "admin_post4", "value" => "500", "type" => "status", "title" => "Post count 4", "form_object" => "text", "data_type" => "number", "minicat" => "4", "sort" => "8"));
				$tdb->add("config", array("name" => "admin_post4", "value" => "500", "type" => "status"));
				$tdb->add("ext_config", array("name" => "admin_post5", "value" => "1000", "type" => "status", "title" => "Post count 5", "form_object" => "text", "data_type" => "number", "minicat" => "4", "sort" => "10"));
				$tdb->add("config", array("name" => "admin_post5", "value" => "1000", "type" => "status"));
				$tdb->add("ext_config", array("name" => "membercolor", "value" => "#000000", "type" => "status", "title" => "Member status color", "description" => "The color that the status of a regular user will have", "form_object" => "text", "data_type" => "string", "minicat" => "5", "sort" => "1"));
				$tdb->add("config", array("name" => "membercolor", "value" => "#000000", "type" => "status"));
				$tdb->add("ext_config", array("name" => "moderatorcolor", "value" => "#990099", "type" => "status", "title" => "Moderator status color", "description" => "The color that the status of a moderator will have", "form_object" => "text", "data_type" => "string", "minicat" => "5", "sort" => "2"));
				$tdb->add("config", array("name" => "moderatorcolor", "value" => "#990099", "type" => "status"));
				$tdb->add("ext_config", array("name" => "admcolor", "value" => "#BB0000", "type" => "status", "title" => "Admin status color", "description" => "The color that the status of an administrator will have", "form_object" => "text", "data_type" => "string", "minicat" => "5", "sort" => "3"));
				$tdb->add("config", array("name" => "admcolor", "value" => "#BB0000", "type" => "status"));
				//Who's online hex
				$tdb->add("ext_config", array("name" => "userColor", "value" => "9d865e", "type" => "status", "title" => "User Color", "description" => "The color of usernames of regular users in the who's online box", "form_object" => "text", "data_type" => "string", "minicat" => "6", "sort" => "1"));
				$tdb->add("config", array("name" => "userColor", "value" => "9d865e", "type" => "status"));
				$tdb->add("ext_config", array("name" => "modColor", "value" => "006699", "type" => "status", "title" => "Moderator Color", "description" => "The color of usernames of moderators in the who's online box", "form_object" => "text", "data_type" => "string", "minicat" => "6", "sort" => "2"));
				$tdb->add("config", array("name" => "modColor", "value" => "006699", "type" => "status"));
				$tdb->add("ext_config", array("name" => "adminColor", "value" => "BB0000", "type" => "status", "title" => "Admin Color", "description" => "The color of usernames of administrators in the who's online box", "form_object" => "text", "data_type" => "string", "minicat" => "6", "sort" => "3"));
				$tdb->add("config", array("name" => "adminColor", "value" => "BB0000", "type" => "status"));
				$mini = $config_tdb->addMiniCategory('Profile Settings','config');

				for ($i=1;$i<=5;$i++)
				{
					$config_tdb->addVar("custom_profile$i",'','config','text','text',$mini,$i,"Custom Profile Field $i","");
				}


				$tdb->tdb(DB_DIR.'/', 'bbcode.tdb');
				$tdb->createTable('smilies',array(array('id','id'),array('bbcode','memo'),array('replace','memo'),array('type','string',4)));
				$tdb->createTable('icons',array(array('id','id'),array('filename','memo')));
				$tdb->setFp("smilies","smilies");
				$tdb->setFp("icons","icons");
				for ($i = 1;$i<22;$i++)
				{
					$filename = 'icon'.$i.'.gif';
					$tdb->add('icons',array("filename"=>$filename));
				}
        //$config_table = $config_tdb->query("config","id>'0'");//maybe this will be helpful?
       //
       // dump($config_table);//I'm just copying code without thinking how it works
				//SMILIES
				$tdb->add('smilies',array("bbcode"=>" :)","replace"=> " <img src='./smilies/smile.gif' border='0' alt=':)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" :(", "replace"=>" <img src='./smilies/frown.gif' border='0' alt=':('> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" ;)","replace"=> " <img src='./smilies/wink.gif' border='0' alt=';)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" :P","replace"=> " <img src='./smilies/tongue.gif' border='0' alt=':P'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" :o","replace"=> " <img src='./smilies/eek.gif' border='0' alt=':o'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" :D","replace"=> " <img src='./smilies/biggrin.gif' border='0' alt=':D'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (C)","replace"=> " <img src='./smilies/cool.gif' border='0' alt='(C)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (M)","replace"=> " <img src='./smilies/mad.gif' border='0' alt='(M)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (confused)","replace"=> " <img src='./smilies/confused.gif' border='0' alt='(confused)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (crazy)","replace"=> " <img src='./smilies/crazy.gif' border='0' alt='(crazy)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (hm)","replace"=> " <img src='./smilies/hm.gif' border='0' alt='(hm)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (hmmlaugh)","replace"=> " <img src='./smilies/hmmlaugh.gif' border='0' alt='(hmmlaugh)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (offtopic)","replace"=> " <img src='./smilies/offtopic.gif' border='0' alt='(offtopic)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (blink)","replace"=> " <img src='./smilies/blink.gif' border='0' alt='(blink)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (rofl)","replace"=> " <img src='./smilies/rofl.gif' border='0' alt='(rofl)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (R)","replace"=> " <img src='./smilies/redface.gif' border='0' alt='(R)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (E)","replace"=> " <img src='./smilies/rolleyes.gif' border='0' alt='(E)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (wallbash)","replace"=> " <img src='./smilies/wallbash.gif' border='0' alt='(wallbash)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" (noteeth)","replace"=> " <img src='./smilies/noteeth.gif' border='0' alt='(noteeth)'> ","type" => "main"));
				$tdb->add('smilies',array("bbcode"=>" LOL","replace"=> " <img src='./smilies/lol.gif' border='0' alt='LOL'> ","type" => "main"));

				//MORE SMILIES (more smilies page)
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/action-smiley-035.gif[/img]","replace"=>"<img src='./smilies/action-smiley-035.gif' border='0' alt='action-smiley-035.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/action-smiley-073.gif[/img]","replace"=>"<img src='./smilies/action-smiley-073.gif' border='0' alt='action-smiley-073.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/anti-old.gif[/img]","replace"=>"<img src='./smilies/anti-old.gif' border='0' alt='anti-old.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/blamesheep.gif[/img]","replace"=>"<img src='./smilies/blamesheep.gif' border='0' alt='blamesheep.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/bump.gif[/img]","replace"=>"<img src='./smilies/bump.gif' border='0' alt='bump.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/chainsaw.gif[/img]","replace"=>"<img src='./smilies/chainsaw.gif' border='0' alt='chainsaw.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/closed.gif[/img]","replace"=>"<img src='./smilies/closed.gif' border='0' alt='closed.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/enforcer.gif[/img]","replace"=>"<img src='./smilies/enforcer.gif' border='0' alt='enforcer.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/ernaehrung004.gif[/img]","replace"=>"<img src='./smilies/ernaehrung004.gif' border='0' alt='ernaehrung004.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/flour.gif[/img]","replace"=>"<img src='./smilies/flour.gif' border='0' alt='flour.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/google.gif[/img]","replace"=>"<img src='./smilies/google.gif' border='0' alt='google.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/gramps4.gif[/img]","replace"=>"<img src='./smilies/gramps4.gif' border='0' alt='gramps4.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/gunner.gif[/img]","replace"=>"<img src='./smilies/gunner.gif' border='0' alt='gunner.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/hatespammers.gif[/img]","replace"=>"<img src='./smilies/hatespammers.gif' border='0' alt='hatespammers.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/headshot.gif[/img]","replace"=>"<img src='./smilies/headshot.gif' border='0' alt='headshot.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/hug.gif[/img]","replace"=>"<img src='./smilies/hug.gif' border='0' alt='hug.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/imo.gif[/img]","replace"=>"<img src='./smilies/imo.gif' border='0' alt='imo.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/inq.gif[/img]","replace"=>"<img src='./smilies/inq.gif' border='0' alt='inq.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/jackhammer.gif[/img]","replace"=>"<img src='./smilies/jackhammer.gif' border='0' alt='jackhammer.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/jk.gif[/img]","replace"=>"<img src='./smilies/jk.gif' border='0' alt='jk.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/blahblah.gif[/img]","replace"=>"<img src='./smilies/blahblah.gif' border='0' alt='blahblah.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/machine_Gun.gif[/img]","replace"=>"<img src='./smilies/machine_Gun.gif' border='0' alt='machine_Gun.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/moo.gif[/img]","replace"=>"<img src='./smilies/moo.gif' border='0' alt='moo.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/newbie.gif[/img]","replace"=>"<img src='./smilies/newbie.gif' border='0' alt='newbie.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/offtopic.gif[/img]","replace"=>"<img src='./smilies/offtopic.gif' border='0' alt='offtopic.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/old.gif[/img]","replace"=>"<img src='./smilies/old.gif' border='0' alt='old.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/owned.gif[/img]","replace"=>"<img src='./smilies/owned.gif' border='0' alt='owned.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/ripper.gif[/img]","replace"=>"<img src='./smilies/ripper.gif' border='0' alt='ripper.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/rocker2.gif[/img]","replace"=>"<img src='./smilies/rocker2.gif' border='0' alt='rocker2.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/rocket3.gif[/img]","replace"=>"<img src='./smilies/rocket3.gif' border='0' alt='rocket3.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/smash.gif[/img]","replace"=>"<img src='./smilies/smash.gif' border='0' alt='smash.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/sniper.gif[/img]","replace"=>"<img src='./smilies/sniper.gif' border='0' alt='sniper.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/soon.gif[/img]","replace"=>"<img src='./smilies/soon.gif' border='0' alt='soon.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/spam1.gif[/img]","replace"=>"<img src='./smilies/spam1.gif' border='0' alt='spam1.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/stupid.gif[/img]","replace"=>"<img src='./smilies/stupid.gif' border='0' alt='stupid.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/transporter.gif[/img]","replace"=>"<img src='./smilies/transporter.gif' border='0' alt='transporter.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/ttidead.gif[/img]","replace"=>"<img src='./smilies/ttidead.gif' border='0' alt='ttidead.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/twocents.gif[/img]","replace"=>"<img src='./smilies/twocents.gif' border='0' alt='twocents.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/w00t.gif[/img]","replace"=>"<img src='./smilies/w00t.gif' border='0' alt='w00t.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/weirdo.gif[/img]","replace"=>"<img src='./smilies/weirdo.gif' border='0' alt='weirdo.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/whenitsdone.gif[/img]","replace"=>"<img src='./smilies/whenitsdone.gif' border='0' alt='whenitsdone.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/yeahthat.gif[/img]","replace"=>"<img src='./smilies/yeahthat.gif' border='0' alt='yeahthat.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/zap.gif[/img]","replace"=>"<img src='./smilies/zap.gif' border='0' alt='zap.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/paranoid.gif[/img]","replace"=>"<img src='./smilies/paranoid.gif' border='0' alt='paranoid.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/worthy.gif[/img]","replace"=>"<img src='./smilies/worthy.gif' border='0' alt='worthy.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/signs_word.gif[/img]","replace"=>"<img src='./smilies/signs_word.gif' border='0' alt='signs_word.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/wacko.gif[/img]","replace"=>"<img src='./smilies/wacko.gif' border='0' alt='wacko.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/censored.gif[/img]","replace"=>"<img src='./smilies/censored.gif' border='0' alt='censored.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/drunk.gif[/img]","replace"=>"<img src='./smilies/drunk.gif' border='0' alt='drunk.gif'>","type"=>"more"));
				$tdb->add("smilies",array("bbcode"=>"[img]smilies/finger.gif[/img]","replace"=>"<img src='./smilies/finger.gif' border='0' alt='finger.gif'>","type"=>"more"));

				//$tdb->tdb(DB_DIR.'/', 'privmsg.tdb');
				//$tdb->createTable('1', array(array("box", "string", 6), array("from", "number", 7), array("to", "number", 7), array("icon", "string", 10), array("subject", "memo"), array("date", "number", 14), array("message", "memo"), array("id", "id")));
				?>
		<form action='<?php print $_SERVER['PHP_SELF']; ?>' method='post'>
		Installing the Database...
		<?php
		if($errorHandler->error_exists()) {
			print "<br /><br /><div style='text-align:left'>";
			$errorHandler->print_errors(true);
			print '<p>error(s) have ocurred in the script.  Please see above for details on the error to try to remody the problem.</p></div>';
		} else print '  Done!<br /><br />';
		?>
		<input type='hidden' name='add' value='2' />
		<input type='submit' value='Next'
		<?php print (($errorHandler->error_exists()) ? ' DISABLED' : '') ?>>
		
		</td>
		</form>
		<?php
			} else if ($_POST["add"] == "2") {
				require_once("includes/inc/func.inc.php");
				//Set up admin acccount
				$required = "#ff0000";
				if (!isset($_POST["username"])) $_POST["username"] = "";
				if (!isset($_POST["email"])) $_POST["email"] = "";
				if (!isset($view_email_checked)) $view_email_checked = "";
				if (!isset($_POST["location"])) $_POST["location"] = "";
				if (!isset($_POST["icq"])) $_POST["icq"] = "";
				if (!isset($_POST["aim"])) $_POST["aim"] = "";
				if (!isset($_POST["msn"])) $_POST["msn"] = "";
				if (!isset($_POST["sig"])) $_POST["sig"] = "";
				if (!isset($_POST["homepage"]) || $_POST["homepage"] == "") $_POST["homepage"] = "http://";
				if (!isset($_POST["avatar"]) || $_POST["avatar"] == "") $_POST["avatar"] = "http://";
				if(isset($error) && !empty($error)) echo $error;
				echo "
<form method='POST' action='".$_SERVER['PHP_SELF']."'>";
				echo "
		<table class='main_table'>
			<tr>
				<th colspan='2'><strong><span style='color:$required;'>*</span> is a required field</strong></th>
			</tr>
			<tr>
				<td class='area_1' style='width:35%;'><strong>Username: <span style='color:$required;'>*</span></td>
				<td class='area_2'><input type='text' name='username' size='20' tabindex='1' maxlength='20' value='".$_POST["username"]."'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Password: <span style='color:$required;'>*</span></strong></td>
				<td class='area_2'><input type='password' name='pass1' size='20' tabindex='2' maxlength='20'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Verify Password: <span style='color:$required;'>*</span></strong></td>
				<td class='area_2'><input type='password' name='pass2' size='20' tabindex='3' maxlength='20'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Email: <span style='color:$required;'>*</span></strong></td>
				<td class='area_2'><input type='text' name='email' size='20' value='".$_POST["email"]."' tabindex='4'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Make email visible to the public?</strong></td>
				<td class='area_2'><input type='checkbox' name='view_email' value='1' ".$view_email_checked." tabindex='5'></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Location:</strong></td>
				<td class='area_2'><input type='text' name='location' size='20' value='".$_POST["location"]."' maxlength='25' tabindex='6'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Homepage:</strong></td>
				<td class='area_2'><input type='text' name='homepage' size='20' value='".$_POST["homepage"]."' tabindex='7' maxlength='50'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Avatar:</strong></td>
			<td class='area_2'>Choose an avatar in your UserCP after logging in.</td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>ICQ:</strong></td>
				<td class='area_2'><input type='text' name='icq' size='20' value='".$_POST["icq"]."' tabindex='9' maxlength='20'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>AIM:</strong></td>
				<td class='area_2'><input type='text' name='aim' size='20' value='".$_POST["aim"]."' tabindex='10' maxlength='24'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>MSN:</strong></td>
				<td class='area_2'><input type='text' name='msn' size='20' value='".$_POST["msn"]."' tabindex='11'></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Signature:</strong><br />Your signature is appended to each of your messages</td>
				<td class='area_2'><textarea rows='10' name='sig' cols='45' tabindex='12' maxlength='200'>".$_POST["sig"]."</textarea></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Timezone Setting:</strong></td>
				<td class='area_2'>".timezonelist($_POST["timezone"], "timezone")."</td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type='hidden' name='add' value='2auth'><input type='submit' value='Submit' name='B1'><input type='reset' value='Reset' name='B2'></td>";
			} else if ($_POST["add"]{0} == "3") {
				require_once("./includes/inc/defines.inc.php");

				if($_POST["add"] == "3adduser") {
					//add admin to the db
					require_once("./includes/inc/encode.inc.php");
					require_once("./includes/inc/date.inc.php");

					$admin = array(
						"user_name" => $_POST["username"], 
						"password" => generateHash($_POST["pass1"]), 
						"level" => 9, 
						"email" => $_POST["email"], 
						"view_email" => $_POST["view_email"], 
						"location" => $_POST["location"], 
						"homepage" => $_POST["homepage"], 
						"icq" => $_POST["icq"], 
						"aim" => $_POST["aim"], 
						"msn" => $_POST["msn"], 
						"sig" => $_POST["sig"], 
						"posts" => 0, 
						"date_added" => mkdate(),
						"lastvisit" => mkdate(), 
						"timezone" => $_POST["timezone"]);
					$tdb = new Tdb(DB_DIR, "main");
					$tdb->setFp("users", "members");
					$tdb->add("users", $admin);
					$f = fopen(DB_DIR."/new_pm.dat", 'w');
					fwrite($f, " 0");
					fclose($f);
					if(!defined("ADMIN_EMAIL")) {
						$config_file = file('./config.php');
						unset($config_file[count($config_file) - 1]);
						$config_file[] = "define('ADMIN_EMAIL', '".$_POST["email"]."', true);";
						$config_file[] = '?>';

						$config_file = implode("\n", $config_file);
						$f = fopen('./config.php', 'w');
						fwrite($f, $config_file);
						fclose($f);
						unset($config_file);
					}
				}

				if(!isset($_POST['title'])) $_POST['title'] = 'UPB Forum';
				if(!isset($_POST['register_sbj'])) $_POST['register_sbj'] = 'Welcome to the UPB Forums!';
				if(!isset($_POST['register_msg'])) $_POST['register_msg'] = "Hello <login>!\n\nWelcome to the UPB Forums!  Your password is <password>.  You can change your password and other settings by visiting the user:cp portal after you log in.\nBut before you do that, you must verify your e-mail address by visiting this link: <url>\nSee you on the forums!\n\n--The UPB Team";
				if(!isset($_POST['fileupload_size'])) $_POST['fileupload_size'] = '50';
				if(!isset($_POST['fileupload_types'])) $_POST['fileupload_types'] = 'txt,pdf,zip';
				if(!isset($_POST['admin_email'])) $_POST['admin_email'] = '';
				if(!isset($_POST["homepage"])) $_POST["homepage"] = 'http://www.myupb.com';

				if(isset($error) && !empty($error)) {
					print str_replace('__TITLE__', 'The follow error(s) occurred:', str_replace('__MSG__', implode('<br />', $error), ALERT_MSG));
					print '<br /><br />';
				}
				echo "</table>";
				echo "
<form method='post' action='".$_SERVER['PHP_SELF']."'>";
				echo "<table class='main_table'>
			<tr>
				<th colspan='2'>Basic Forum Settings</td>
			</tr>

      <tr>
				<td class='area_1' style='width:45%;'><strong>Title</strong><br />Title of the forum</td>
				<td class='area_2'><input type='text' name='title' size='40' value='".$_POST["title"]."' tabindex='1'></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Register Email Subject</strong><br />
					This is the subject for confirmation of registration</td>
				<td class='area_2'><input type='text' name='register_sbj' size='40' value='".$_POST["register_sbj"]."' tabindex='2'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Register Email Message</strong><br />
					this is the message for confirmation of registration (options: &lt;login&gt;, &lt;password&gt;, and &lt;url&gt;)</td>
				<td class='area_2'><textarea rows='5' name='register_msg' cols='25' tabindex='3'>".$_POST["register_msg"]."</textarea></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Size limits for file upload</strong><br />
					In kilobytes, type in the maximum size allowed for file uploads. <i>Note: Setting to 0 will <b>disable</b> uploads</i></td>
				<td class='area_2'><input type='text' name='fileupload_size' size='40' value='".$_POST["fileupload_size"]."' tabindex='5'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>File upload allowed types</strong><br />
					List the allowed filetype extensions seperated by a comma.</td>
				<td class='area_2'><input type='text' name='fileupload_types' size='40' value='".$_POST["fileupload_types"]."' tabindex='6'></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Admin E-mail</strong><br />
					this is the return address for confirmation of registration</td>
				<td class='area_2'><input type='text' name='admin_email' size='40' value='".$_POST["admin_email"]."' tabindex='7'></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Homepage URL</strong><br />
					can be relative or a url</td>
				<td class='area_2'><input type='text' name='homepage' size='40' value='".$_POST["homepage"]."' tabindex='8'></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='2'>For more settings, visit the admin Panel after installation.</td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type='hidden' name='add' value='3auth'><input type='submit' value='Submit' name='B1'><input type='reset' value='Reset' name='B2'></td>";
			} else if ($_POST['add'] == "4") {
				$config_tdb = new ConfigSettings();
				$edit_config = array("title" => $_POST["title"], "fileupload_size" => $_POST["fileupload_size"], "fileupload_types" => $_POST["fileupload_types"], "homepage" => $_POST["homepage"]);
				$edit_regist = array("register_sbj" => $_POST["register_sbj"], "register_msg" => $_POST["register_msg"], "admin_email" => $_POST["admin_email"]);
				if ($config_tdb->editVars("config", $edit_config) && $config_tdb->editVars("regist", $edit_regist)) {

					?>
		<div class='alert_confirm'>
		<div class='alert_confirm_text'><strong>MyUPB Installation Complete!</div>
		<div style='padding: 4px;'>If you had any errors or you find that your
		forum is not working correctly, visit myUPB's support forums at <a
			href='http://www.myupb.com/' target='_blank'>www.myupb.com</a><br />
		Delete the install.php and update1.x-2.0.php NOW, as it is a security
		risk to leave it in your server.<br />
		<a href='javascript:window.close()'>Close Window</a> -or- <a
			href='index.php'>Go To Forum</a> -or- <a
			href='login.php?ref=admin_forums.php?action=add_cat'>Login and add
		categories</a></div>
		</div>
		<?php 
	} else {
		?>
		<div class='alert'>
		<div class='alert_text'><strong>Step Five Failed!</strong></div>
		<div style='padding: 4px;'>Please seek help from <a
			href='http://www.myupb.com/' target='_blank'>www.myupb.com</a>.</div>
		</div>
		";
		<?php
	}
}
?>
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
2009</div>
</div>
</body>
</html>
