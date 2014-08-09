<?php
/**
 * User Control Panel / Profile
 *
 * @author Tim Hoeppner <timhoeppner@gmail.com>
 * @author FixITguy
 * @author Jerroyd Moore
 * @author Chris Kent
 */

require_once('./includes/upb.initialize.php');

if(!isset($_GET['action']) || $_GET['action'] == '') $_GET['action'] = 'edit';
if ($_GET['action'] == "get" || $_GET['action'] == 'view') $where = "Member Profile";
elseif ($_GET['action'] == "bookmarks") $where = "Bookmarked Topics";
elseif($_GET['action'] == "edit")$where = "User CP";
if (isset($_POST["u_edit"])) {
	if (!($tdb->is_logged_in())) {
		echo "<html><head><meta http-equiv='refresh' content='2;URL=login.php?ref=profile.php'></head></html>";
		exit;
	} else {
		$rec = array();
		if (!isset($_POST["u_email"])) exitPage("please enter your email!", true);
		if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $_POST["u_email"])) exitPage("please enter a valid email!", true);
		$_POST['u_sig'] = stripslashes($_POST['u_sig']);
		if (strlen($_POST["u_sig"]) > 500) exitPage("You cannot have more than 500 characters in your signature.", true);
		$user = $tdb->get("users", $_COOKIE["id_env"]);
		if (strlen($_POST["u_newpass"]) > 0) {
			if ($user[0]['password'] != Encode::generateHash($_POST['u_oldpass'], $user[0]['password'])) exitPage('You old password does not match the one on file!', true);
			if ($_POST["u_newpass"] != $_POST["u_newpass2"]) exitPage("your pass and pass confirm are not matching!", true);
			if (strlen($_POST["u_newpass"]) < 6) exitPage("your password has to be longer then 6 characters", true);
			$rec["password"] = Encode::generateHash($_POST["u_newpass"]);
			setcookie("user_env", "");
			setcookie("uniquekey_env", "");
			setcookie("power_env", "");
			setcookie("id_env", "");
			$ht = "<meta http-equiv='refresh' content='2;URL=login.php'>";
		}
		else $ht = "<meta http-equiv='refresh' content='2;URL=profile.php'>";
		if ($user[0]["email"] != $_POST["u_email"]) $rec["email"] = $_POST["u_email"];
		if ($user[0]["u_sig"] != encode_text(chop($_POST["u_sig"]))) $rec["sig"] = encode_text(chop($_POST["u_sig"]));
		if ($_POST['u_sig'] == "")
		$rec['sig'] = "";
		if (substr(trim(strtolower($_POST["u_site"])), 0, 7) != "http://") $_POST["u_site"] = "http://".$_POST["u_site"];
		if ($user[0]["url"] != $_POST["u_site"])
		$rec["url"] = xml_clean($_POST["u_site"]);
		if ($_POST['u_site'] == "http://" or $rec['url'] == 'http://')
		$rec['url'] = "";
		for ($i = 1;$i <= 5; $i++)
		{
			if (array_key_exists("custom_profile$i",$_POST))
			{
				if ($user[0]["custom_profile$i"] != $_POST["custom_profile$i"])
				$rec["custom_profile$i"] = xml_clean($_POST["custom_profile$i"]);
			}
		}

		if ($_POST["u_timezone"]{0} == '+') $_POST["u_timezone"] = substr($_POST["u_timezone"], 1);
		if ($_POST["show_email"] != "1") $_POST["show_email"] = "0";
		if ($_POST["email_list"] != "1") $_POST["email_list"] = "0";
		if ($user[0]["view_email"] != $_POST["show_email"]) $rec["view_email"] = $_POST["show_email"];
		if ($user[0]["mail_list"] != $_POST["email_list"]) $rec["mail_list"] = $_POST["email_list"];
		if ($user[0]["location"] != $_POST["u_loca"]) $rec["location"] = xml_clean($_POST["u_loca"]);
		
		$exts = array('gif','jpg','png','jpeg');
		
		//dump($FILES);
		
		if ($FILES !== NULL)
		{
			if (isset($_FILES['avatar2file']['tmp_name']))
			{
				$dim = getimagesize($_FILES['avatar2file']['tmp_name']);
			}
			else
			{
				$dim = array(0, 0);
			}
		
			$exts = array('gif','jpg','png','jpeg');
			$upload_ext = pathinfo($_FILES["avatar2file"]["name"], PATHINFO_EXTENSION);
		
		
			if (isset($_FILES["avatar2file"]["name"]) && trim($_FILES["avatar2file"]["name"]) != "") {
				if (!in_array($upload_ext,$exts) or $upload_ext == "")
					$upload_err = "The file is not a valid image file for an avatar. File must be a gif,jpg,jpeg or png";
				else if($_FILES['avatar2file']['size'] > $_REGIST['avatarupload_size']*1024)
				{
					$upload_err = "The filesize of the uploaded avatar is too big.<br>The maximum filesize is ".$_REGIST['avatarupload_size']."KB<br>The file you uploaded was ".ceil($_FILES['avatar2file']['size']/1024)."KB";
				}
				else if ($dim[0] > $_REGIST["avatarupload_dim"] or $dim[1] > $_REGIST["avatarupload_dim"])
				$upload_err = "The dimensions of the uploaded avatar are too big.<br>The maximum dimensions are ".$_REGIST['avatarupload_dim']."px by ".$_REGIST['avatarupload_dim']."px";
				else
				{
					$upload = new Upload(DB_DIR, $_REGIST["avatarupload_size"], $_CONFIG["fileupload_location"]);
					$uploadId = $upload->storeFile($_FILES["avatar2file"]);
				}
			}
		}
		elseif(isset($_POST['avatar2url']) && $_POST['avatar2url'] != '') {
			$new_av = xml_clean($_POST['avatar2url']);
			$ext = pathinfo($new_av, PATHINFO_EXTENSION);
			//dump($ext);
			//die();
			if (!in_array($ext,$exts) or $ext == "")
				$upload_err = "The url is not a valid image file for an avatar. File must be a gif,jpg, jpeg or png. Avatar has not been updated";
			else
				$rec['avatar'] = $new_av;
		}
		elseif(isset($_POST['avatar']) && $_POST['avatar'] != '') {
			$new_av = xml_clean($_POST['avatar']);
			$ext = pathinfo($new_av, PATHINFO_EXTENSION);
			if (!in_array($new_av,$exts) or $ext == "")
				$upload_err = "The avatar is not a valid image file. File must be a gif,jpg, jpeg or png. Avatar has not been updated";
			else
				$rec['avatar'] = $new_av;
		}
	  
		if(isset($rec['avatar']) && FALSE !== strpos($user[0]['avatar'], 'downloadattachment.php?id=')) {
			$id = substr($user[0]['avatar'], 26);
			if(ctype_digit($id)) {
				if(!isset($upload)) {
					$upload = new Upload(DB_DIR, $_REGIST["avatarupload_size"], $_CONFIG["fileupload_location"]);
				}
				$upload->deleteFile($id);
			}
		}
		if ($user[0]["icq"] != $_POST["u_icq"]) $rec["icq"] = xml_clean($_POST["u_icq"]);
		if ($user[0]["aim"] != $_POST["u_aim"]) $rec["aim"] = xml_clean($_POST["u_aim"]);
		if ($user[0]["yahoo"] != $_POST["u_yahoo"]) $rec["yahoo"] = xml_clean($_POST["u_yahoo"]);
		if ($user[0]["msn"] != $_POST["u_msn"]) $rec["msn"] = xml_clean($_POST["u_msn"]);
		if ($user[0]["skype"] != $_POST["u_skype"]) $rec["skype"] = xml_clean($_POST["u_skype"]);
    if ($user[0]["twitter"] != $_POST["u_twitter"]) $rec["twitter"] = xml_clean($_POST["u_twitter"]);
		if ($user[0]["timezone"] != $_POST["u_timezone"]) {
			$rec["timezone"] = (int) $_POST["u_timezone"];
			setcookie("timezone", $rec["timezone"], (time() + (60 * 60 * 24 * 7)));
		}
		$tdb->edit("users", $_COOKIE["id_env"], $rec);
		require_once('./includes/header.php');
		if (!isset($upload_err))
		echo "<div class='alert_confirm'>
				<div class='alert_confirm_text'>
				<strong>User Profile Update:</strong></div><div style='padding:4px;'>Your user profile has been successfully updated
				</div>
				</div>
				<meta http-equiv='refresh' content='2;URL=".$_GET["ref"]."'>";
		else
		echo "<div class='alert'>
				<div class='alert_text'>
				<strong>Avatar Upload Error:</strong></div><div style='padding:4px;'>$upload_err<br><br>All other changes to your user profile have been made.<br>Click <a href='".$_GET['ref']."'>here</a> to continue.
				</div>
				</div>";

		require_once('./includes/footer.php');
	}
} elseif($_GET["action"] == 'get' || $_GET['action'] == 'view') {
	if (!isset($_GET["id"])) {
		echo "<html><head><meta http-equiv='refresh' content='0;URL=index.php'></head></html>";
		exit;
	} else {
		$rec = $tdb->get("users", $_GET["id"]);
		if($rec === false) {
			exitPage(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'This user was either deleted or not found.', ALERT_MSG)),
			true);
		}
		$status_config = status($rec);
		$status = $status_config['status'];
		$statuscolor = $status_config['statuscolor'];
		$statusrank = $status_config['rank'];
		if ($rec[0]["status"] != "") $status = $rec[0]["status"];
		require_once('./includes/header.php');
		echo "";
		for($i=1;$i<=5;$i++)
		{
			$custom = $config_tdb->basicQuery('config',"name","custom_profile$i");
			if (trim($custom[0]['value']) != "")
			$customs[] = array($custom[0]['value'],$rec[0]["custom_profile$i"]);
		}

		echoTableHeading("Viewing profile for ".$rec[0]["user_name"]."", $_CONFIG);
		echo "
			<tr>
				<td colspan='2' id='topcontent'>

						<span style='color:#".$statuscolor.";font-size:14px;'>".$rec[0]["user_name"]."</span>
						<br />
						<br />
						";
		if (@$rec[0]["avatar"] != "")
		{
			$resize = resize_img($rec[0]['avatar'],$_REGIST["avatarupload_dim"]);
			echo "<img src='".$rec[0]["avatar"]."' $resize border='0' alt='' title='' /><br />";
		}
		echo "<br />
						<img src='$statusrank'>
            <br />
						<div class='link_pm'>";
		if($_COOKIE['power_env'] >= 3 && $rec[0]['level'] <= $_COOKIE['power_env']) print "<a href='admin_members.php?action=edit&id={$_GET['id']}'>Edit Member</a><br/>";
		require_once('./includes/inc/privmsg.inc.php');
		$blockedList = getUsersPMBlockedList($_GET["id"]);
		if ($_GET["id"] == $_COOKIE["id_env"]) {
			echo "";
		} elseif($_COOKIE["id_env"] == "" || $_COOKIE["id_env"] == "0") {
			echo "Login to contact";
		} elseif(in_array($_COOKIE["id_env"], $blockedList)) {
			echo "You are banned from using the PM system";
		} else {
			echo "<a href='newpm.php?to=".$_GET["id"]."' target='_blank'>Send private message?</a>";
		}
		echo "</div></td></tr>";
		echo "<tr><td id='leftcontent' valign='top'>
					<div class='pro_sig_name'>General</div>
					<div class='pro_container'>
						<div class='pro_area_1'><div class='pro_area_2'><strong>Joined: </strong></div>".gmdate("Y-m-d", DateCustom::user_date($rec[0]["date_added"]))."</div>
						<div class='pro_area_1'><div class='pro_area_2'><strong>Posts made: </strong></div>".$rec[0]["posts"]."</div>";


		echo "
			<div class='pro_area_1'><div class='pro_area_2'><strong>Homepage: </strong></div>";
		if (strlen($rec[0]['url']) != 0)
		echo "<a href='".$rec[0]["url"]."' target='_blank'>".$rec[0]["url"]."</a>";
		echo "&nbsp;</div>
						<div class='pro_area_1' style='white-space:nowrap;'><div class='pro_area_2'><strong>Status: </strong></div>
        <span style='color:#".$statuscolor."'><strong>".preg_replace("/<br\s*\/?>/i", " / ", $status)." &nbsp;&nbsp;&nbsp;</strong></span></div>
						<div class='pro_area_1'><div class='pro_area_2'><strong>Email: </strong></div>";
		if ((bool)$rec[0]["view_email"]) echo "<a href='mailto:".$rec[0]["email"]."'>".$rec[0]["email"]."</a>";
		else echo "not public";
		echo "</div>";
		echo "<div class='pro_area_1'><div class='pro_area_2'><strong>Location: </strong></div>".$rec[0]["location"]."&nbsp;</div>";
		echo "</div></td>";
		echo "<td id='rightcontent' valign='top'>
			<div class='pro_sig_name'>Contact</div>
			<div class='pro_container'>";
		echo "<div class='pro_area_1'><div class='pro_area_2'><img src='images/icq.gif' border='0' align='absmiddle'>&nbsp;<strong>ICQ:</strong></div>".$rec[0]["icq"]."&nbsp;</div>";
		echo "<div class='pro_area_1'><div class='pro_area_2'><img src='images/aol.gif' border='0' align='absmiddle'>&nbsp;<strong>AIM:</strong></div>".$rec[0]["aim"]."&nbsp;</div>";
		echo "<div class='pro_area_1'><div class='pro_area_2'><img src='images/yahoo.gif' border='0' align='absmiddle'>&nbsp;<strong>Yahoo!:</strong></div>".$rec[0]["yahoo"]."&nbsp;</div>";
		echo "<div class='pro_area_1'><div class='pro_area_2'><img src='images/msn.gif' border='0' align='absmiddle'>&nbsp;<strong>MSN:</strong></div>".$rec[0]["msn"]."&nbsp;</div>";
    echo "<div class='pro_area_1'><div class='pro_area_2'><img src='images/twitter.png' border='0' align='absmiddle'>&nbsp;<strong>Twitter:</strong></div>".$rec[0]["twitter"]."&nbsp;</div>";
		echo "<div class='pro_area_1'><div class='pro_area_2'><img src='images/skype.gif' border='0' align='absmiddle'>&nbsp;<strong>Skype:</strong></div>".$rec[0]["skype"]."&nbsp;</div>";
		echo "</div></td></tr>

				<tr>
					<td id='bottomcontent' colspan='2'>";
		if(is_array($customs) && !empty($customs)) {
			echo "<div class='pro_sig_name'>More</div>";
			foreach ($customs as $key => $value) {
				echo "
			<div class='pro_area_1'><div class='pro_area_2'><strong>".$value[0].":</strong></div>".$value[1]."&nbsp;</div>\n";
			}
		}

		if (@$rec[0]["sig"] != "") echo "
						<div class='pro_sig_name'>".$rec[0]["user_name"]."'s Signature:</div>
						<div class='pro_sig_area'>
							<div class='pro_signature'>".format_text(UPBcoding(filterLanguage($rec[0]["sig"], $_CONFIG)))."</div>
						</div>"; 
		echo "              </div>
                        </td>
                    </tr>";
		echoTableFooter(SKIN_DIR);

		if(!isset($_GET["showPrevPosts"])) {
			echoTableHeading("View Previous Posts", $_CONFIG);
			echo "<tr><td><div class='pro_area_1' align='center'><a href='./profile.php?action=get&id={$_GET["id"]}&showPrevPosts=1'>Show all posts</a>";
			echoTableFooter(SKIN_DIR);
		} else {
			$fRecs = $tdb->listRec("forums", 1);
			if(!empty($fRecs[0])) {
				$posts_tdb = new Tdb(DB_DIR, "posts");
				foreach($fRecs as $fRec) {
					if ((int)$_COOKIE["power_env"] < $fRec["view"]) {
						continue;
					}
					$posts_tdb->setFp("p", $fRec["id"]);
					$posts_tdb->setFp("t", $fRec["id"] . "_topics");

					$pRecs = $posts_tdb->query("p", "user_id='{$_GET["id"]}'");
					if(!empty($pRecs[0])) {
						$posts = array();
						foreach($pRecs as $pRec) {
							$i = $pRec["t_id"];
							if(!isset($posts[$i]))
							$posts[$i] = array();
							$posts[$i][] = $pRec;
						}
						unset($pRecs);
						echoTableHeading("In forum \"{$fRec["forum"]}\"", $_CONFIG);
						foreach($posts as $pRecs) {
							$tRec = $posts_tdb->get("t", $pRecs[0]["t_id"]);
							$tRec[0]["p_ids"] = ',' . $tRec[0]["p_ids"] . ',';
							echo "<tr><th><div style='float:left;'>In topic \"{$tRec[0]["subject"]}\"</div>";
							foreach($pRecs as $pRec) {
								// display each post in the current topic
								if ($x == 0) {
									$table_color = 'area_1';

									$x++;
								} else {
									$table_color = 'area_2';

									$x--;
								}

								$pos = strpos($tRec[0]["p_ids"], ','.$pRec["id"].',');
								if($pos == 0) $page = 1;
								else {
									$countpost = substr_count($tRec[0]["p_ids"], ',', 0, $pos) + 1;
									$page = ceil($countpost / $_CONFIG["posts_per_page"]);
								}

								$msg = display_msg($pRec['message'], '', true);
								$msg .= $tdb->getUploads($_GET['id'],$_GET['t_id'],$pRec['id'],$pRec['upload_id'],$_CONFIG['fileupload_location'],$pRec['user_id']);
								echo "
                                <tr>
                                <td class='$table_color' valign='top'>
                                <div style='float:right;'><div class='button_pro2'><a href='viewtopic.php?id={$fRec["id"]}&t_id={$pRec["t_id"]}&page=$page#{$pRec["id"]}'>View Post</a></div></div>
                                $msg";
                                echo "</td></tr>";
							}
						}
						echoTableFooter(SKIN_DIR);
					}
				}
			} else echo "<div align='center'>No Posts</div>";
		}
		require_once('./includes/footer.php');
	}
} elseif($_GET['action'] == 'edit') {
	if (!($tdb->is_logged_in())) {
		echo "<html><head><meta http-equiv='refresh' content='2;URL=login.php?ref=profile.php'></head></html>";
		exit;
	} else {
		$rec = $tdb->get("users", $_COOKIE["id_env"]);
		require_once('./includes/header.php');
		@$rec[0]["sig"] = str_replace("<br />", "\n", $rec[0]["sig"]);

		echo "<form action='{$_SERVER['PHP_SELF']}' id='newentry' name='newentry' method='post' enctype=\"multipart/form-data\">";
		echo "
        <div id='tabstyle_2'>
        	<ul>
        		<li><a href='profile.php?action=edit'><span>User CP</span></a></li>
        		<!--<li><a href='profile.php?action=bookmarks'><span>View Bookmarks</span></a></li>-->
        	</ul>
        </div>
        <div style='clear:both;'></div>";
		echoTableHeading("Account settings - Edit profile information", $_CONFIG);
		echo "
			<tr>
				<td class='area_1' style='width:45%;'><strong>Username:</strong></td>
				<td class='area_2'>".$rec[0]["user_name"]."</td>
			</tr>
			<tr>
				<td class='area_1'><strong>Old password:</strong><br /><i>Use only if you are changing your password</i></td>
				<td class='area_2'><input type='password' name='u_oldpass' size='50'/></td>
			</tr>
			<tr>
				<td class='area_1'><strong>New password:</strong></td>
				<td class='area_2'><input type='password' name='u_newpass' size='50' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>New password confirmation:</strong></td>
				<td class='area_2'><input type='password' name='u_newpass2' size='50' /></td>
			</tr>";
		if ($_COOKIE["power_env"] >= 2) {
			echo "
			<tr>
				<td class='area_1'><strong>Email:</strong></td>
				<td class='area_2'><input type='text' name='u_email'  size='50' value='".$rec[0]["email"]."' /><br />Current email address: ".$rec[0]["email"]."</td>
			</tr>";
		} else {
			echo "
			<tr>
				<td class='area_1'><strong>Email:</strong><br /><font size='1' face='$font_face'>Email the Forum Administrator to change your email address.</a></td>
				<td class='area_2'><input type='hidden' name='u_email'  size='50' value='".$rec[0]["email"]."' />&nbsp;".$rec[0]["email"]."</td>
			</tr>";
		}
		if ((bool) $rec[0]["view_email"]) $email_checked = "CHECKED";
		else $email_checked = "";
		echo "
			<tr>
				<td class='area_1'>Make email address public in profile?&nbsp;&nbsp;&nbsp;
					<a href=\"javascript: window.open('privacy.php','','status=no, width=850,height=700'); void('');\">
					Privacy Policy</a></td>
				<td class='area_2'><input type='checkbox' name='show_email' value = '1' $email_checked /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Location:</strong></td>
				<td class='area_2'><input type='text' name='u_loca' value='".$rec[0]["location"]."' /></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>";
		echoTableFooter(SKIN_DIR);

		$custom_avatar = (($rec[0]['posts'] >= $_REGIST['newuseravatars'] || $_COOKIE['power_env'] > 1) && $_REGIST['custom_avatars']);
		echoTableHeading("Avatar Options", $_CONFIG);
		echo "
			<tr>
				<th style='text-align:center;'>Current avatar</th>
				<th style='text-align:center;'>Select a local avatar</th>
			</tr>
			<tr>
				<td class='area_1' valign='middle' style='text-align:center;padding:20px;height:150px;'>";

		if (@$rec[0]["avatar"] != "")
		{
			$resize = resize_img($rec[0]['avatar'],$_REGIST["avatarupload_dim"]);
			echo "<img src='".$rec[0]["avatar"]."' $resize border='0'><br />";
		}
		else echo "<img src='images/avatars/noavatar.gif' alt='' title='' />";
		echo "</td>
				<td class='area_2' valign='middle' style='text-align:center;padding:20px;height:150px;'>
					<table cellspacing='0px' style='width:100%;'>
						<tr>
							<td style='text-align:center;width:50%;'>
								<img src='images/avatars/blank.gif' id='myImage' alt='' title='' /></td>
							<td><select class='select' size='5' name='avatar' onchange='swap(this.options[selectedIndex].value)'>\n";

		returnimages();
		echo "</select></td></tr>
					</table>
				</td></tr>";
		if ($custom_avatar)
		{
			echo "<tr><th style='text-align:center;' colspan='2'>Custom Avatar</th></tr>";
			echo "<tr><td class='area_1'><strong>Custom Avatar:</strong><p>Maximum avatar size is ".$_REGIST["avatarupload_dim"]."px by ".$_REGIST["avatarupload_dim"]."px";

			if ($_REGIST['custom_avatars'] > '2' and $_REGIST['avatarupload_size'] > 0)
			echo '<br>Valid filetypes are jpg, jpeg, png and gif.<br>Maximum filesize is '.$_REGIST["avatarupload_size"].'KB.';
			echo "<td class='area_2' valign='middle' style='text-align:center;padding:20px;height:150px;'>";

			echo "You may upload a custom image using the control(s) below.";

			if ($_REGIST['custom_avatars'] == 1)
			echo "<p>Enter the URL of an image below<br />
        <input type='text' size='40' name='avatar2url' value='' />";
			else if ($_REGIST['custom_avatars'] == 2)
			echo "<p>Upload image from your computer <br />
        <input type='file' size='40' name='avatar2file' />";
			else
			{
				echo "<p>Option 1 - Enter the URL of an image below<br />
        <input type='text' size='40' name='avatar2url' value='' />";
				if ($_REGIST['avatarupload_size'] > 0)
				echo "<p>Option 2 - Upload image from your computer <br />
        <input type='file' size='40' name='avatar2file' />";
			}
			echo "<p>If either the width or height exceeds this limit the avatar will be resized maintaining the correct ratio</td></tr>";
		}
		echo "
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>";
		echoTableFooter(SKIN_DIR);
		echoTableHeading("Other Information", $_CONFIG);
		echo "
			<tr>
				<td class='area_1' style='width:20%;' ><strong>Homepage:</strong></td>
				<td class='area_2' colspan='3'><input type='text' name='u_site' size='50' value='";
		if ($rec[0]["url"] == '')
		echo "http://";
		else
		echo $rec[0]["url"];
		echo "' /></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='4'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><img src='images/icq.gif' border='0' align='absmiddle'>&nbsp;<strong>ICQ:</strong></td>
				<td class='area_2'><input type='text' name='u_icq' size='25' value='".$rec[0]["icq"]."' /></td>
				<td class='area_1'><img src='images/aol.gif' border='0' align='absmiddle'>&nbsp;<strong>AIM:</strong></td>
				<td class='area_2'><input type='text' name='u_aim' size='25' value='".$rec[0]["aim"]."' /> </td>
			</tr>
			<tr>
				<td class='area_1'><img src='images/yahoo.gif' border='0' align='absmiddle'>&nbsp;<strong>Yahoo!:</strong></td>
				<td class='area_2'><input type='text' name='u_yahoo' size='25' value='".$rec[0]["yahoo"]."' /></td>
				<td class='area_1'><img src='images/msn.gif' border='0' align='absmiddle'>&nbsp;<strong>MSN:</strong></td>
				<td class='area_2'><input type='text' name='u_msn' size='25' value='".$rec[0]["msn"]."' /></td>
			</tr>
			<tr>
				<td class='area_1'><img src='images/twitter.png' border='0' align='absmiddle'>&nbsp;<strong>Twitter:</strong></td>
				<td class='area_2'><input type='text' name='u_twitter' size='25' value='".$rec[0]["twitter"]."' /></td>
				<td class='area_1'><img src='images/skype.gif' border='0' align='absmiddle'>&nbsp;<strong>Skype:</strong></td>
				<td class='area_2'><input type='text' name='u_skype' size='25' value='".$rec[0]["skype"]."' /></td>
			</tr>
			";
		for ($i=1;$i<=5;$i++)
		{
			$custom = $config_tdb->basicQuery('config',"name","custom_profile$i");
			if (trim($custom[0]['value']) != "")
			echo "<tr>
				<td class='area_1'><strong>{$custom[0]['value']}</strong></td>
				<td class='area_2' colspan='3'><input size='50' name='custom_profile$i' value='".$rec[0]["custom_profile$i"]."'/></td>
			</tr>";
		}
		echo "<tr>
				<td class='footer_3' colspan='4'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
    <tr>
				<td class='area_1' valign='top'><strong>Signature:</strong></td>
				<td class='area_2'  colspan='3'>".bbcodebuttons('u_sig','sig')."<textarea id='u_sig' name='u_sig' cols='45' rows='10'>".format_text($rec[0]["sig"],'edit')."</textarea><br /><input type='button' onclick=\"javascript:sigPreview(document.getElementById('u_sig'),'".$_COOKIE['id_env']."','set');\" value='Preview Signature' /></td></tr>
      <tr>
				<td class='area_1' valign='top'><div id='sig_title'><strong>Current Signature:</strong></div></td>
				<td class='area_2'  colspan='3'><div style='display:inline;' id='sig_preview'>".display_msg($rec[0]["sig"])."</div></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='4'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1'><strong>Timezone Setting:</strong></td>
				<td class='area_2' colspan='3'>";
		print timezonelist($rec[0]["timezone"]);
		echo "</td></tr>
			<tr>
				<td class='footer_3a' colspan='4' style='text-align:center;'><input type='reset' name='reset' value='Reset' onclick=\"javascript:sigPreview(document.getElementById('u_sig'),'".$_COOKIE['id_env']."','reset');\" /><input type='submit' name='u_edit' value='Submit' /></td>
			</tr>";
		echoTableFooter(SKIN_DIR,4);
		echo "</form>";
		require_once('./includes/footer.php');
	}
} elseif($_GET['action'] == 'bookmarks') {
	require_once('./includes/header.php');
	$topics = array();
	if(isset($_SESSION['newTopics']) && is_array($_SESSION['newTopics'])) while(list($forum, $arr) = each($_SESSION['newTopics'])) {
		if($forum == 'lastVisitForums') continue;
		while(list($topic, $val) = each($arr)) {
			if($val == 2) $topics[] = substr($forum, 1).','.substr($topic, 1);
		}
	}
	echo "
	<div id='tabstyle_2'>
		<ul>
			<li><a href='profile.php?action=edit'><span>User CP</span></a></li>
			<li><a href='profile.php?action=bookmarks'><span>View Bookmarks</span></a></li>
		</ul>
	</div>
	<div style='clear:both;'></div>";
	echoTableHeading("Bookmarked Topics", $_CONFIG);
	echo "
	<tr>
		<th style='width: 75%;'>Topic</th>
		<th style='width:25%;text-align:center;'>Last Post</th>
	</tr>";
	if(empty($topics)) {
		echo "
	<tr>
		<td colspan='6' class='area_2' style='text-align:center;font-weight:bold;padding:20px;'>you have no bookmarked topics</td>
	</tr>";
	} else {
		$posts_tdb = new Posts(DB_DIR."/", "posts.tdb");
		while(list(, $tmp) = each($topics)) {
			list($f_id, $t_id) = explode(',', $tmp);
			$posts_tdb->setFp("topics", $f_id."_topics");
			$fRec = $tdb->get("forums", $f_id);
			$tRec = $posts_tdb->get('topics', $t_id);
			if ($tRec[0]["icon"] == "") continue;
			$tRec[0]['subject'] = "<a href='viewforum.php?id=".$f_id."'>".$fRec[0]["forum"]."</a> " .$_CONFIG["where_sep"] . " <a href='viewtopic.php?id=".$f_id."&amp;t_id=".$tRec[0]["id"]."'>".$tRec[0]["subject"]."</a>";
			settype($tRec[0]["replies"], "integer");
			$total_posts = $tRec["replies"] + 1;
			$num_pages = ceil($total_posts / $_CONFIG["posts_per_page"]);
			if ($num_pages == 1) {
				$r_ext = "";
			} else {
				$r_ext = "<br /><div class='pagination_small'> Pages: ( ";
				for($m = 1; $m <= $num_pages; $m++) {
					$r_ext .= "<a href='viewtopic.php?id=".$f_id."&amp;t_id=".$tRec[0]["id"]."&page=$m'>$m</a> ";
				}
				$r_ext .= ")</div>";
			}
			if ($tRec[0]["topic_starter"] == "guest") $tRec[0]["topic_starter"] = "<i>guest</i>";
			echo "
	<tr>
		<td class='area_2' onmouseover=\"this.className='area_2_over'\" onmouseout=\"this.className='area_2'\">
			<span class='link_1'>".$tRec[0]["subject"].$r_ext."</span>
			<div class='description'>Started By:&nbsp;<span style='color:#".$statuscolor."'>".$tRec[0]["topic_starter"]."</span></div>
			<div class='box_posts'><strong>Views:</strong>&nbsp;".$tRec[0]["views"]."</div>
			<div class='box_posts'><strong>Replies:</strong>&nbsp;".$tRec[0]["replies"]."</div></td>
		<td class='area_1' style='text-align:center;'>
			<img src='icon/".$tRec[0]["icon"]."' class='post_image'>
			<span class='latest_topic'><span class='date'>".gmdate("M d, Y g:i:s a", DateCustom::user_date($tRec[0]["last_post"]))."</span>
			<br />
			<strong>By:</strong> ";
			if ($tRec[0]["user_id"] != "0") echo "<span class='link_2'><a href='profile.php?action=get&id=".$tRec[0]["user_id"]."'>".$tRec[0]["user_name"]."</a></span></td>
	</tr>";
			else echo "a ".$tRec[0]["user_name"]."</span></td>
	</tr>";
		}
	}
	echoTableFooter(SKIN_DIR);
	require_once('./includes/footer.php');
} else redirect('index.php', 0);
?>
