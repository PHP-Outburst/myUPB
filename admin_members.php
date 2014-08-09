<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once("./includes/upb.initialize.php");
$where = "<a href='admin.php'>Admin</a> ".$_CONFIG["where_sep"]." <A href='admin_members.php'>Manage Members</a>";
if($_GET['action'] == 'confirm') $where .= " ".$_CONFIG["where_sep"]." Confirm Newly Registered Users";
require_once("./includes/header.php");
if (!$tdb->is_logged_in() || $_COOKIE["power_env"] < 3) exitPage("
		<div class='alert'><div class='alert_text'>
		<strong>Access Denied!</strong></div><div style='padding:4px;'>you are not authorized to be here.</div></div>");
if(!isset($_GET["action"])) $_GET["action"] = '';
if($_GET['action'] == 'confirm') {
	$users = $tdb->query('users', "reg_code?'reg_'", 1, -1);
 
	$_SESSION['reg_approval_count'] = ((!empty($users[0])) ? count($users) : 0);
 
	if(isset($_POST['verify'])) {
		if($_POST['verify'] == 'Cancel') $_POST = array(); //do nothing, show the confirm menu
		elseif($_POST['verify'] == 'Ok') {
			if(isset($_POST['message'])) {
				$bbc = array();
				foreach($users as $user) {
					if(in_array($user['id'], $_POST['ids'])) $bbc[] = $user['email'];
				}
				$bbc = implode(',', $bbc);
				if(!empty($bbc)) {
					$e_hed = "From: ".$_REGISTER["admin_email"]."\r\n";
					$e_hed .= "Bcc: ".$bbc."\r\n"; //More efficient to send one e-mail with everyone on a BLANK CARBON COPY (see php.net's mail())
					@mail("", $_POST['subject'], $_POST['message'], $e_hed);
				}
 
			}
			
			
			for($i=0;$i<count($users);$i++) {
				if(in_array($users[$i]['id'], $_POST['ids'])) {
					if($_POST['a'] == 'Validate') 
						$tdb->edit('users', $users[$i]['id'], array('reg_code'=>''));
					elseif($_POST['a'] == 'Reject') {
						$tdb->delete('users', $users[$i]['id']);
					}
					unset($users[$i]);
					$_SESSION['reg_approval_count']--;
				}
			}
			array_reset_keys($users);
			$msg = "<div class='alert_confirm'>
					<div class='alert_confirm_text'>
					<strong>Attention:</strong></div><div style='padding:4px;'>Successfully ".(($_POST['a']=='Reject')?'rejected':'approved').' '.count($_POST['ids']).' user(s)</div></div><br /><br />';
		}
	} elseif(isset($_POST['a']) && ($_POST['a'] == 'Validate' || $_POST['a'] == 'Reject')) {
		$ids = array();
		reset($_POST);
		
		foreach ($_POST['id'] as $id)
			if(ctype_digit($id)) $ids[] = $id;
		
		if(!empty($ids)) {
			print '<form action="'.$_SERVER['PHP_SELF'].'?action=confirm#skip_nav" method="POST">';
			$hidden = '<input type="hidden" name="a" value="'.$_POST['a'].'">';
			foreach($ids as $id) {
				$hidden .= "<input type='hidden' name='ids[]' value='{$id}'>\n";
			}
			if($_CONFIG['email_mode']) {
				print $hidden;
				echoTableHeading('E-mail format', $_CONFIG);
				print '<tr>
            			    <td class="area_1" style="width:50%;"><strong>Admin E-mail</strong><br />This is the return address for the e-mail.</td>
            				<td class="area_2" style="width:50%;"><input type="text" name="email" value="'.$_REGIST['admin_email'].'" size="40"></td>
            			   </tr>';
				print '<tr>
            			    <td class="area_1" style="width:50%;"><strong>Email Subject</strong><br />This is the subject for the e-mail.</td>
            				<td class="area_2" style="width:50%;"><input type="text" name="subject" value="'.$_CONFIG['title'].' Registration Status Update" size="40"></td>
            			   </tr>';
				print '<tr>
            			    <td class="area_1" style="width:50%;"><strong>Email Message</strong><br />This is the message for confirmation of registration.</td>
            				<td class="area_2" style="width:50%;"><textarea cols=30 rows=10 name="message">';
 
				if($_POST['a'] == 'Validate') {
					print "Hello user,\n\nYou are receiving this e-mail because an administrator at http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF'])." has approved your account!  You may log in at any time!\n\n--UPB Team";
				} elseif($_POST['a'] == 'Reject') {
					print "Greetings!\n\nWe regret to inform your account has not been approved at http://".$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).".\n\n--UPB Team";
				}
				print '</textarea></td></tr>';
				print "<tr><td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td></tr>";
				print "<tr><td class='area_2' colspan=2><input type=submit name='verify' value='Ok'> <input type=reset value='Reset'> <input type=submit name='verify' value='Cancel'></td></tr>";
				echoTableFooter(SKIN_DIR);
				print '</form>';
			} else {
				ok_cancel($_SERVER['PHP_SELF'].'?action=confirm#skip_nav', $hidden.'Are you sure you wish to <b>'.strtolower($_POST['a']).'</b> '.count($ids).' user(s)?');
			}
			require_once('./includes/footer.php');
			exit;
		}
	}
	echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
		<tr>
			<th>Admin Panel Navigation</th>
		</tr>";
	echo "
		<tr>
			<td class='area_2' style='padding:20px;' valign='top'>";
	require_once("admin_navigation.php");
	echo "</td>
		</tr>";
	echoTableFooter(SKIN_DIR);
 
	print '<a name="skip_nav">'.((isset($msg)) ? $msg : '&nbsp;').'</a><form action="'.$_SERVER['PHP_SELF'].'?action=confirm#skip_nav" method="POST">';
	
	
	
	$page = ((isset($_GET['page'])) ? $_GET['page'] : '1');
	if (!empty($users) or count($users) > $_CONFIG['topics_per_page'])
	{
		$page = ((isset($_GET['page'])) ? $_GET['page'] : '1');
		$num_pages = ceil((count($users) + 1) / $_CONFIG['topics_per_page']);
		$p = createPageNumbers($page, $num_pages, $_SERVER['QUERY_STRING']);
		echo "<div id='pagelink1' name='pagelink1'><table><tr><td class='pagination_title'>Pages ($num_pages):</td>$p</tr></table><div style='clear:both;'></div>";
	}
	echo "
        <div id='tabstyle_1' class='tabstyle_1'>
        	<ul>
        		<li><a href='register.php' title='Add Member'><span>Add Member</span></a></li>
                <li><a href='admin_members.php#skip_nav' title='Manage Members'><span>Manage Members</span></a></li>
                <li><a href='admin_banuser.php#skip_nav' title='Manage Banned Members'><span>Manage Banned Members</span></a></li>
        	</ul>
        </div>
        <div style='clear:both;'></div>";
	
	echoTableHeading('Unconfirmed Users', $_CONFIG);
	
	echo "
			<tr>
			    <th style='width:5%;padding:8px;'><input type='checkbox' onClick='check_all_confirm(this)'></th>
				<th style='width:20%;'>Username</th>
				<th style='width:20%;'>Email</th>
				<th style='width:10%;text-align:center;'>Registered</th>
				<th style='width:15%;'>Homepage</th>
				<th style='width:30%;'>Signature</th>
			</tr>";
	if (empty($users)) {
		echo "
			<tr>
				<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='6'>No Users need to be confirmed at this time.</td>
			</tr>";
	} else {
		if ($page == 1)
			$start = 0;
		else
			$start = ($page - 1)*$_CONFIG['topics_per_page'];
		
		$sub_users = array_slice($users,$start,$_CONFIG['topics_per_page']);
		
		
		foreach($sub_users as $user) {
			echo "
			<tr>
				<td class='area_1' style='padding:8px;'><input type='checkbox' name='id[]' value='{$user['id']}'></td>
				<td class='area_2'>{$user["user_name"]}</td>";
			if ($user['view_email']) echo "
				<td class='area_1'>".$user["email"]."</td>";
			else echo "
				<td class='area_1'><i>".$user["email"]."</i></td>";
			echo "
				<td class='area_2' style='text-align:center;'>";
			if (gmdate('Y-m-d', user_date($user['date_added'])) == gmdate('Y-m-d'))
			echo '<i>today</i>';
			else if (gmdate('Y-m-d', user_date($user['date_added'])) == gmdate('Y-m-d', mktime(0, 0, 0, gmdate('m'), ((int)gmdate('d') - 1), gmdate('Y'))))
			echo "<i>yesterday</i>";
			else
			echo gmdate("Y-m-d", user_date($user['date_added']))."</td>";
			$sig = str_replace('<br><br>', '',format_text(filterLanguage($user["sig"])));
			print "<td class='area_1'><a href='{$user['homepage']}' target='_blank'>{$user['homepage']}</a></td>
                <td class='area_2'><i>".substr($sig, 0, (50 - strlen(strip_tags($sig)))).(((50 - strlen(strip_tags($sig))) > 0) ? '': "...")."</i></td>
			</tr>";
		}
		print "<tr>
        			<td class='footer_3' colspan='6'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
        		</tr>";
		print "<tr><td class='area_2' colspan='6'><input type='submit' name='a' value='Validate'>&nbsp;&nbsp;&nbsp;<input type='submit' name='a' value='Reject'></td></tr>";
	}
	echoTableFooter(SKIN_DIR,6);
} elseif ($_GET["action"] == "edit") {
	if (!isset($_GET["id"])) exitPage("
				<div class='alert'><div class='alert_text'>
				<strong>Error!</strong></div><div style='padding:4px;'>No id selected!</div></div>");
	$rec = $tdb->get("users", $_GET["id"]);
	if($_COOKIE['power_env'] < $rec[0]['level']) exitPage("
				<div class='alert'><div class='alert_text'>
				<strong>Attention</strong></div><div style='padding:4px;'>You do not have enough access to edit this user.</div></div>");
	if (isset($_POST["a"])) {
		if (!isset($_POST["email"])) exitPage("
				<div class='alert'><div class='alert_text'>
				<strong>Error!</strong></div><div style='padding:4px;'>Please enter a valid email!</div></div>");
		if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $_POST["email"])) exitPage("
				<div class='alert'><div class='alert_text'>
				<strong>Error!</strong></div><div style='padding:4px;'>Please enter a valid email!</div></div>");
		if (strlen(chop($_POST["sig"])) > 200) exitPage("
				<div class='alert'><div class='alert_text'>
				<strong>Error!</strong></div><div style='padding:4px;'>You cannot have more than 200 characters in the signature.</div></div>");
		if (substr(trim(strtolower($_POST["url"])), 0, 7) != "http://") $_POST["url"] = "http://".$_POST["url"];
		if ($_POST["timezone"] {0} == '+')
		$_POST["u_timezone"] = substr($_POST["u_timezone"], 1);
		$new = array();
		if ($_POST["level"] != $rec[0]["level"]) $new["level"] = $_POST["level"];
		if ($_POST["email"] != $rec[0]["email"]) $new["email"] = xml_clean($_POST["email"]);
		if ($_POST["status"] != $rec[0]["status"]) $new["status"] = xml_clean($_POST["status"]);
		if ($_POST["location"] != $rec[0]["location"]) $new["location"] = xml_clean($_POST["location"]);
		if ($_POST["url"] != $rec[0]["url"]) $new["url"] = xml_clean($_POST["url"]);
		if ($_POST["avatar"] != $rec[0]["avatar"]) $new["avatar"] = xml_clean($_POST["avatar"]);
		if ($_POST["icq"] != $rec[0]["icq"]) $new["icq"] = xml_clean($_POST["icq"]);
		if ($_POST["yahoo"] != $rec[0]["yahoo"]) $new["yahoo"] = xml_clean($_POST["yahoo"]);
		if ($_POST["msn"] != $rec[0]["msn"]) $new["msn"] = xml_clean($_POST["msn"]);
		if ($_POST["aim"] != $rec[0]["aim"]) $new["aim"] = xml_clean($_POST["aim"]);
		if ($_POST["skype"] != $rec[0]["skype"]) $new["skype"] = xml_clean($_POST["skype"]);
    if ($_POST["twitter"] != $rec[0]["twitter"]) $new["twitter"] = xml_clean($_POST["twitter"]);
		if (chop($_POST["sig"]) != $rec[0]["sig"]) $new["sig"] = xml_clean(chop($_POST["sig"]));
		if ($_POST["timezone"] != $rec[0]["timezone"]) $new["timezone"] = (int) $_POST["timezone"];
		if (!empty($new)) $tdb->edit("users", $_GET["id"], $new);
		echo "
				<div class='alert_confirm'>
				<div class='alert_confirm_text'>
				<strong>Successfully edited: ".$rec[0]["user_name"]."!</div><div style='padding:4px;'>
				<a href='admin_members.php?page=".$_GET["page"]."#skip_nav'>Go Back to Member's list</a>
				</div>
				</div>";
	} else {
		echo "<form method='POST' action={$_SERVER['PHP_SELF']}?action=edit&id={$_GET["id"]}&page={$_GET["page"]}><input type='hidden' name='a' value='1'>";
		echoTableHeading("Editing member: ".$rec[0]["user_name"]."", $_CONFIG);
		echo "
			<tr>
				<th colspan='2'>Complete the information below to edit this member</th>
			</tr>
			<tr>
				<td class='area_1' style='width:25%;padding:8px;'><strong>Username:</strong></td>
				<td class='area_2'><span class='link_1'><a href='admin_members.php?action=changeuser&id=".$_GET['id']."'>Change Username?</a></span></td>
			</tr>
			<tr>
				<td class='area_1' style='width:25%;padding:8px;'><strong>Password:</strong></td>
				<td class='area_2'><span class='link_1'><a href='admin_members.php?action=pass&id=".$_GET['id']."'>Change Password?</a></span></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>User group:</strong></td>
				<td class='area_2'>";
		echo "<select size='1' name='level'>".createUserPowerMisc($rec[0]["level"], 7, TRUE);
		echo "</td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>E-mail Address:</strong></td>
				<td class='area_2'><input type='text' name='email' size='20' value='".$rec[0]["email"]."' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Public E-mail?</strong></td>
				<td class='area_2'>";
		if ($rec[0]["view_email"] == 1) echo "YES";
		else echo "NO";
		echo "</td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>";
		$f = fopen(DB_DIR."/new_pm.dat", 'r');
		fseek($f, (((int)$rec[0]["id"] * 2) - 2));
		$tmp_new_pm = fread($f, 2);
		fclose($f);
		$lastvisit = (int)$rec[0]['lastvisit'];
		echo "
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>No. of Unread Private Messages:</strong></td>
				<td class='area_2'>".$tmp_new_pm."</td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Status:</strong></td>
				<td class='area_2'><input type='text' name='status' size='20' value='".$rec[0]["status"]."' /></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Location:</strong></td>
				<td class='area_2'><input type='text' name='location' size='20' value='".$rec[0]["location"]."' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Website:</td>
				<td class='area_2'><input type='text' name='url' size='20' value='".$rec[0]["url"]."' /></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Avatar:</strong></td>
				<td class='area_2'><input type='text' name='avatar' size='20' value='".$rec[0]["avatar"]."' /></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><img src='images/msn.gif' align='absmiddle'/>&nbsp;<strong>MSN:</strong></td>
				<td class='area_2'><input type='text' name='msn' size='20' value='".$rec[0]["msn"]."' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><img src='images/yahoo.gif' align='absmiddle'/>&nbsp;<strong>YIM:</strong></td>
				<td class='area_2'><input type='text' name='yahoo' size='20' value='".$rec[0]["yahoo"]."' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><img src='images/icq.gif' align='absmiddle'/>&nbsp;<strong>ICQ:</strong></td>
				<td class='area_2'><input type='text' name='icq' size='20' value='".$rec[0]["icq"]."' /></td>
			</tr>
      <tr>
				<td class='area_1' style='padding:8px;'><img src='images/aol.gif' align='absmiddle'/>&nbsp;<strong>AOL:</strong></td>
				<td class='area_2'><input type='text' name='icq' size='20' value='".$rec[0]["aim"]."' /></td>
			</tr>
      <tr>
				<td class='area_1' style='padding:8px;'><img src='images/twitter.png' align='absmiddle'/>&nbsp;<strong>Twitter:</strong></td>
				<td class='area_2'><input type='text' name='twitter' size='20' value='".$rec[0]["twitter"]."' /></td>
			</tr>
      <tr>
				<td class='area_1' style='padding:8px;'><img src='images/skype.gif' align='absmiddle'/>&nbsp;<strong>Skype:</strong></td>
				<td class='area_2'><input type='text' name='skype' size='20' value='".$rec[0]["skype"]."' /></td>
			</tr>
      <tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;' valign='top'><strong>Signature:</strong></td>
				<td class='area_2'><textarea rows='10' name='sig' cols='45' rows='10'>".$rec[0]["sig"]."</textarea></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Number of posts:</strong></td>
				<td class='area_2'>".$rec[0]["posts"]."</td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Last login:</strong></td>
				<td class='area_2'>";
		if($lastvisit == 0) print '<i>Never</i>';
		else if (gmdate('Y-m-d', $lastvisit) == gmdate('Y-m-d'))
		echo '<i>today</i>';
		else if (gmdate('Y-m-d', $lastvisit) == gmdate('Y-m-d', mktime(0, 0, 0, gmdate('m'), ((int)gmdate('d') - 1), gmdate('Y'))))
		echo '<i>yesterday</i>';
		else
		echo gmdate("Y-m-d", user_date($lastvisit));
		echo "</td></tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Registered Date:</strong></td>
				<td class='area_2'>".gmdate("Y-m-d", user_date($rec[0]["date_added"]))."</td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Time zone:</strong></td>
				<td class='area_2'>".timezonelist($rec[0]['timezone'], "timezone")."</td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type='submit' value='Submit' name='B1' /><input type='reset' value='Reset' name='B2' /></td>
			</tr>";
		echoTableFooter(SKIN_DIR);
		echo "</form>";
	}
} elseif($_GET["action"] == "pass" && isset($_GET["id"])) {
	$user = $tdb->get("users", $_GET["id"]);
	if (isset($_POST["a"])) {
		if ($_POST["pass"] != $_POST["pass2"]) exitPage("The passwords don't match!");
		if (strlen($_POST["pass"]) < 4) exitPage("The password has to be longer then 4 characters");
		$tdb->edit("users", $_GET["id"], array("password" => generateHash($_POST["pass"])));
		$msg = "You Password was changed by ".$_COOKIE["user_env"]." on the website ".$_CONFIG["homepage"]." to \"".$_POST["pass"]."\"";
		if (isset($_POST["reason"])) $msg .= "\n\n".$_COOKIE["user_env"]."'s reason was this:\n".$_POST["reason"];
 
		if(!@mail($user[0]["email"], "Password Change Notification", "Password Changed by :".$_COOKIE["user_env"]."\n\n".$msg, "From: ".$_REGISTER["admin_email"]))
		if(!$_CONFIG['email_mode']) $config_tdb->editVars('config', array('email_mode' => '0'));
		else
		if($_CONFIG['email_mode']) $config_tdb->editVars('config', array('email_mode' => '1'));
		echoTableHeading("Password changed!", $_CONFIG);
		echo "
		<tr>
			<td class='area_1'><div class='description'><strong>";
		echo "You successfully changed ".$user[0]["user_name"]."'s password to ".$_POST["pass"]."</strong>";
		if ($email_status !== true)
		echo "<p>The automated email was unable to be sent.<p>Please email them at ".$user[0]['email']." to inform them of the change of password";
		echo "</div></td></tr>";
		echoTableFooter(SKIN_DIR);
	} else {
		echo "<script language='javascript' src='includes/pwd_meter.js'></script>";
		echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
		echo "
			<tr>
				<th>Admin Panel Navigation</th>
			</tr>";
		echo "
			<tr>
				<td class='area_2' style='padding:20px;' valign='top'>";
		require_once("admin_navigation.php");
		echo "</td>
			</tr>";
		echoTableFooter(SKIN_DIR);
		echo "<form method='POST' action=".$_SERVER['PHP_SELF']."?action=pass&id=".$_GET["id"]."><input type='hidden' name='a' value='1'>";
		echoTableHeading("Setting a new password for: ".$user[0]["user_name"]."", $_CONFIG);
		echo "
			<tr>
				<th colspan='2'>Complete the information below to change the password for this member</th>
			</tr>
			<tr>
				<td class='area_1' style='width:25%;padding:8px;'><strong>New Password</strong></td>
				<td class='area_2'><input type='password' name='pass' size='40' onkeyup=\"runPassword(this.value);\"><div style=\"font-size: 10px;\">Password Strength: <span id=\"u_pass_text\" style=\"font-size: 10px;\"></span></div></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Confirm Password</strong></td>
				<td class='area_2'><input type='password' name='pass2'></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Reason</strong></td>
				<td class='area_2'><textarea name=reason></textarea></td>
			</tr>";
		if ($_CONFIG['email_mode'])
		echo "<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='2'>An E-mail will be sent, notifying the user about the change of their password by <i>".$_COOKIE["user_env"]."</i></td>
			</tr>";
		echo "
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type='submit' value='Change Password'></td>
			</tr>";
		echoTableFooter(SKIN_DIR);
		echo "</form>";
	}
}
elseif($_GET['action'] == "changeuser" && isset($_GET["id"])) {
	$user = $tdb->get("users", $_GET["id"]);
	if (isset($_POST["a"])) {
		$tdb->edit("users", $_GET["id"], array("user_name" => $_POST["username"]));
		$msg = "Your Username was changed by ".$_COOKIE["user_env"]." on the website ".$_CONFIG["homepage"]." to \"".$_POST["pass"]."\"";
		if (isset($_POST["reason"])) $msg .= "\n\n".$_COOKIE["user_env"]."'s reason was this:\n".$_POST["reason"];
 
		if(!@mail($user[0]["email"], "Username Change Notification", "Username changed by :".$_COOKIE["user_env"]."\n\n".$msg, "From: ".$_REGISTER["admin_email"]))
		if(!$_CONFIG['email_mode']) $config_tdb->editVars('config', array('email_mode' => '0'));
		else
		if($_CONFIG['email_mode']) $config_tdb->editVars('config', array('email_mode' => '1'));
		echoTableHeading("Username changed!", $_CONFIG);
		echo "
		<tr>
			<td class='area_1'><div class='description'><strong>";
		echo "You successfully changed ".$user[0]["user_name"]."'s password to ".$_POST["newname"]."</strong>";
		if ($email_status !== true)
		echo "<p>The automated email was unable to be sent.<p>Please email them at ".$user[0]['email']." to inform them of the change of password";
		echo "</div></td></tr>";
		echoTableFooter(SKIN_DIR);
	} else {
		echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
		echo "
			<tr>
				<th>Admin Panel Navigation</th>
			</tr>";
		echo "
			<tr>
				<td class='area_2' style='padding:20px;' valign='top'>";
		require_once("admin_navigation.php");
		echo "</td>
			</tr>";
		echoTableFooter(SKIN_DIR);
		echo "<form method='POST' action=".$_SERVER['PHP_SELF']."?action=changeuser&id=".$_GET["id"]."><input type='hidden' name='a' value='1'>";
		echoTableHeading("Setting a new username for: ".$user[0]["user_name"]."", $_CONFIG);
		echo "
			<tr>
				<th colspan='2'>Complete the information below to change the username for this member</th>
			</tr>
			<tr>
				<td class='area_1' style='width:25%;padding:8px;'><strong>New Username:</strong></td>
				<td class='area_2'><input type='text' size='40' name='username' onblur=\"getUsername(this.value,'changeuser');\"><span class='err' id='namecheck'></span></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Reason</strong></td>
				<td class='area_2'><textarea name=reason></textarea></td>
			</tr>";
		if ($_CONFIG['email_mode'])
		echo "<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='2'>An E-mail will be sent, notifying the user about the change of their username by <i>".$_COOKIE["user_env"]."</i></td>
			</tr>";
		echo "
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type='submit' name='submit' id='submit' value='Change Password' disabled><input type='reset' name='reset' id='reset' value='Reset'></td>
			</tr>";
		echoTableFooter(SKIN_DIR);
		echo "</form>";
	}
}
elseif($_GET["action"] == "delete") {
	if (!isset($_GET["id"])) exitPage("No id selected.");
	$rec = $tdb->get("users", $_GET["id"]);
	if ($_POST["verify"] == "Ok") {
		$tdb->delete("users", $_GET["id"]);
		echo "
				<div class='alert_confirm'>
				<div class='alert_confirm_text'>
				<strong>Redirecting:</div><div style='padding:4px;'>
				Successfully deleted ".$rec[0]["user_name"].".<br /><a href='admin_members.php?page={$_GET['page']}#skip_nav'>Go Back</a>
				</div>
				</div>";
	} elseif($_POST["verify"] == "Cancel") {
		echo "<meta http-equiv='refresh' content='0;URL=admin_members.php?page={$_GET['page']}'>";
	} else {
		ok_cancel("admin_members.php?action=delete&id={$_GET["id"]}&page={$_GET['page']}", "Are you sure you want to delete <strong><a href='profile.php?action=get&id=".$_GET["id"]."' targer='_blank'>".$rec[0]["user_name"]."</a></strong>?");
	}
} else {
	echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
			<tr>
				<th>Admin Panel Navigation</th>
			</tr>";
	echo "
			<tr>
				<td class='area_2' style='padding:20px;' valign='top'>";
	require_once("admin_navigation.php");
	echo "</td>
			</tr>";
	echoTableFooter(SKIN_DIR);
	print '<a name="skip_nav">&nbsp;</a>';
	echoTableHeading("Search", $_CONFIG);
	?>
<tr>
	<td class='area_1' style='padding: 8px;'>
	<form action="admin_members.php#skip_nav" method="GET">Username: <input
		name="u" type="text"
		value="<?php print ((isset($_GET['u'])) ? $_GET['u'] : ''); ?>">
	<p><input type="submit" name="action" value="Search">&nbsp;&nbsp;<input
		type="submit" name="action" value="Clear"
		<?php print (($_GET['action'] == 'Search') ? '' : ' DISABLED'); ?>>
	
	</form>
	</td>
</tr>
		<?php
		echoTableFooter(SKIN_DIR);
		if (!isset($_GET['page']) || $_GET["page"] == "") $_GET["page"] = 1;
		$start = ($_GET["page"] * $_CONFIG["topics_per_page"] - $_CONFIG["topics_per_page"] + 1);
		if($_GET['action'] != 'Search') {
			$users = $tdb->listRec("users", $start, $_CONFIG["topics_per_page"]);
			$c = $tdb->getNumberOfRecords("users");
		} else {
			$users = $tdb->query('users', "user_name?'{$_GET['u']}'", $start, $_CONFIG['topics_per_page']);
		}
 
		$num_pages = ceil(($c + 1) / $_CONFIG["topics_per_page"]);
 
		$p = createPageNumbers($_GET["page"], $num_pages, $_SERVER['QUERY_STRING']);
		echo pagination($p,$_GET['page'],$num_pages);
 
		echo "<div id='tabstyle_2'>
            <ul>
              <li><a href='register.php' title='Add Member'><span>Add Member</span></a></li>";
		if($_REGIST['reg_approval']) echo "<li><a href='admin_members.php?action=confirm#skip_nav' title='Confirm New Members'><span>Confirm New Members</span></a></li>";
		echo "<li><a href='admin_banuser.php#skip_nav' title='Manage Banned Members'><span>Manage Banned Members</span></a></li>
            </ul>
          </div>
          <div style='clear:both;'></div>";
		echoTableHeading("Current member management options", $_CONFIG);
		echo "
			<tr>
				<th style='width:5%;'>ID#</th>
				<th style='width:20%;'>Username</th>
				<th style='width:15%;text-align:center;'>User group</th>
				<th style='width:20%;'>Email</th>
				<th style='width:7%;text-align:center;'>Posts</th>
				<th style='width:12%;text-align:center;'>Last Login</th>
				<th style='width:12%;text-align:center;'>Registered</th>
				<th style='width:7%;text-align:center;'>Ban</th>
				<th style='width:7%;text-align:center;'>Edit</th>
				<th style='width:7%;text-align:center;'>Delete</th>
			</tr>";
		if ($users[0] == "") {
			echo "
			<tr>
				<td colspan='10' class='area_2' style='padding:8px;text-align:center'>No records found</td>
			</tr>";
		} else {
 
			$bList = file(DB_DIR."/banneduser.dat");
			foreach($users as $user) {
				$lastvisit = $user['lastvisit'];
				$status_config = status(array(0 => array('level'=>$user['level'],'posts'=>$user['posts'])));
				$status = $status_config['status'];
			 $statuscolor = $status_config['statuscolor'];
				//if(gmdate('Y-m-d', $lastvisit) == gmdate('Y-m-d')) $lastvisit =
				//(gmdate('Y-m-d', $lastvisit) == gmdate('Y-m-d') ? '<i>today</i>' : (gmdate('Y-m-d', $lastvisit) == gmdate('Y-m-d', mktime(0, 0, 0, gmdate('m'), ((int)gmdate('d') - 1), gmdate('Y'))) ? '<i>yesterday</i>' : gmdate("Y-m-d", user_date($lastvisit))))
				//show each user
				echo "
			<tr>
				<td class='area_1' style='padding:8px;'><strong>".$user["id"]."</strong></td>
				<td class='area_2'><span class='link_1'><a href='profile.php?action=get&id=".$user["id"]."' style='color:#".$statuscolor."'>".$user["user_name"]."</a></span></td>
				<td class='area_1' style='text-align:center;'>".createUserPowerMisc($user["level"], 4)."</td>";
				if ($user['view_email']) echo "
				<td class='area_2'>".$user["email"]."</td>";
				else echo "
				<td class='area_2'><i>".$user["email"]."</i></td>";
				echo "
				<td class='area_1' style='text-align:center;'>".$user["posts"]."</td>
				<td class='area_2' style='text-align:center;'>";
				if ($lastvisit == 0)
				echo "<i>never</i>";
				else if (gmdate('Y-m-d', $lastvisit) == gmdate('Y-m-d'))
				echo '<i>today</i>';
				else if (gmdate('Y-m-d', $lastvisit) == gmdate('Y-m-d', mktime(0, 0, 0, gmdate('m'), ((int)gmdate('d') - 1), gmdate('Y'))))
				echo "<i>yesterday</i>";
				else
				echo gmdate("Y-m-d", user_date($lastvisit))."</td>";
				echo "<td class='area_2' style='text-align:center;'>".gmdate("Y-m-d", user_date($user['date_added']))."</td>";
				echo "<td class='area_2' style='text-align:center;'>";
				if ($user['level'] != 9)
				{
					echo "<a href='admin_banuser.php?ref=admin_members.php?page=".$_GET["page"]."&action=";
					if (!in_array($user["user_name"], $bList)) echo 'addnew&newword='.$user["user_name"]."&page={$_GET['page']}'>";
					else echo 'delete&word='.$user["user_name"]."&page={$_GET['page']}'><strong>Un</strong>";
					echo "Ban</a>";
				}
				echo "</td>";
				echo "<td class='area_1' style='text-align:center;'>";
				if (($user['level'] == 9 and $user['id'] == $_COOKIE['id_env']) or ($user['level'] != 9))
				echo "<a href='admin_members.php?action=edit&id=".$user["id"]."&page=".$_GET["page"]."'>Edit</a>";
				echo "</td>";
				echo "<td class='area_2' style='text-align:center;'>";
				if ($user['level'] != 9)
				echo "<a href='admin_members.php?action=delete&id=".$user["id"]."&page={$_GET['page']}'>Delete</a>";
				echo "</td>
			</tr>";
			}
		}
		echo "
			<tr>
				<td class='footer_3' colspan='10'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='10'>An <i>italized</i> e-mail states that this member has chosen to have his/her email address non-viewable to all but admins.</td>
			</tr>";
		echoTableFooter(SKIN_DIR);
		echo pagination($p,$_GET['page'],$num_pages);
}
require_once("./includes/footer.php");
?>
