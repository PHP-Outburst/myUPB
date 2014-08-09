<?php
require_once('./includes/upb.initialize.php');

$ajax_type = $_POST['type'];

switch ($ajax_type)
{
	case "getpost" :
		//GETS THE POST INFORMATION FROM THE DATABASE AND PLACES IN TEXT AREA FOR EDITING
		$posts_tdb = new Posts(DB_DIR, "posts.tdb");
		$posts_tdb->setFp("topics", $_POST["forumid"]."_topics");
		$posts_tdb->setFp("posts", $_POST["forumid"]);
		$pRec = $posts_tdb->get("posts", $_POST["postid"]);
		if ($_POST['method'] != 'cancel')
		{
			$output = "";
			$output .= "<form action='editpost.php?id=".$_POST["forumid"]."&t_id=".$_POST["threadid"]."&p_id=".$_POST["postid"]."' method='POST' id='quickedit' name='quickedit'>";
			$output .= "<input type='hidden' id='forumid' name='userid' value='".$_POST["forumid"]."'>";
			$output .= "<input type='hidden' id='userid' name='userid' value='".$_POST["userid"]."'>";
			$output .= "<input type='hidden' id='threadid' name='threadid' value='".$_POST["threadid"]."'>";
			$output .= "<input type='hidden' id='postid' name='postid' value='".$_POST["postid"]."'>";
			$output .= "<textarea name='newedit' id='newedit' cols='60' rows='18'>".format_text(encode_text($pRec[0]['message']),'edit')."</textarea><br>";
			$output .= "\n<input type='button' onclick='javascript:getEdit(document.getElementById(\"quickedit\"),\"".$_POST['divname']."\");'\' name='qedit' value='Save'>";
			$output .= "\n<input type='button' name='cancel_edit' onClick=\"javascript:getPost('".$_POST["userid"]."','".$_POST["forumid"]."-".$_POST["threadid"]."-".$_POST["postid"]."','cancel');\" value='Cancel'>";
			$output .= "\n<input type='submit' name='submit' value='Advanced'>";
			$output .= "</form>";
		}
		else
		$output = display_msg($pRec[0]['message']);

		echo $output;

		break 1;

	case "edit" :
		$posts_tdb = new Posts(DB_DIR, "posts.tdb");
		$posts_tdb->setFp("topics", $_POST["forumid"]."_topics");
		$posts_tdb->setFp("posts", $_POST["forumid"]);
		$pRec = $posts_tdb->get("posts", $_POST["postid"]);
		//STORES THE EDITED VERSION OF THE POST IN THE DATABASE AND RETURNS THE EDITED PAGE TO THE USER
		if(!(isset($_POST["userid"]) && isset($_POST["forumid"]) && isset($_POST["threadid"]) && isset($_POST["postid"]))) MiscFunctions::exitPage("Not enough information to perform this function.");
		if(!($tdb->is_logged_in())) MiscFunctions::exitPage("You are not logged in, therefore unable to perform this action.");

		if($pRec[0]["user_id"] != $_COOKIE["id_env"] && $_COOKIE["power_env"] < 2) MiscFunctions::exitPage("You are not authorized to edit this post.");
		$msg = "";

		$msg = display_msg(encode_text($_POST['newedit']));
		$msg = display_msg($_POST['newedit']);
		$msg .= "<div id='{$_POST["forumid"]}-{$_POST['threadid']}-{$_POST['postid']}-attach'>".$tdb->getUploads($_POST["forumid"],$_POST['threadid'],$pRec[0]['id'],$pRec[0]['upload_id'],$_CONFIG['fileupload_location'],$pRec[0]['user_id'])."</div>";
		$dbmsg = encode_text(stripslashes($attach_msg.$_POST["newedit"]),ENT_NOQUOTES);

		$posts_tdb->edit("posts", $_POST["postid"], array("message" => $dbmsg, "edited_by_id" => $_COOKIE["id_env"], "edited_by" => $_COOKIE["user_env"], "edited_date" => DateCustom::mkdate()));
		//clearstatcache();
		$posts_tdb->cleanup();
		$posts_tdb->setFp("posts", $_POST["forumid"]);
		$pRec2 = $posts_tdb->get("posts", $_POST["postid"]);

		//$div = $_POST['forumid']."-".$_POST['threadid']."-".$_POST['postid'];


		if(!empty($pRec2[0]['edited_by']) && !empty($pRec2[0]['edited_by_id']) && !empty($pRec2[0]['edited_date']))
		$edited = "Last edited by: <a href='profile.php?action=get&id=".$pRec2[0]['edited_by_id']."' target='_new'>".$pRec2[0]['edited_by']."</a> on ".gmdate("M d, Y g:i:s a", DateCustom::user_date($pRec2[0]['edited_date']));
		echo "$msg<!--divider-->$edited";

		break 1;

	case "reply" :
		//QUICK REPLY TO TOPIC, STORES POST IN DATABASE AND RETURNS THE USER TO THE NEW POST AND ADDS NEW QUICK REPLY FORM
		$fRec = $tdb->get("forums", $_POST["id"]);
		$posts_tdb = new Posts(DB_DIR."/", "posts.tdb");
		$posts_tdb->setFp("topics", $_POST["id"]."_topics");
		$posts_tdb->setFp("posts", $_POST["id"]);
		$tRec = $posts_tdb->get("topics", $_POST["t_id"]);
		$posts_tdb->set_topic($tRec);
		$posts_tdb->set_forum($fRec);
		$tdb->setFp('users', 'members');

		if(!($tdb->is_logged_in()))
		{
			$_COOKIE["user_env"] = "guest";
			$_COOKIE["power_env"] = 0;
			$_COOKIE["id_env"] = 0;
		}

		if($tdb->is_logged_in()) {
			$email_mode = $_CONFIG['email_mode'];
			$thisUser = $tdb->get("users", $_COOKIE["id_env"]);
			$isWatching = in_array($thisUser[0]["id"], explode(',', $tRec[0]['monitor']));
		} else {
			$email_mode = false;
			$isWatching = false;
		}
		$msg = encode_text(stripslashes($_POST["newentry"]));
		$tdb->edit("forums", $_POST["id"], array("posts" => ((int)$fRec[0]["posts"] + 1)));

		$p_id = $posts_tdb->add("posts", array(
        "icon" => $_POST["icon"],
        "user_name" => $_COOKIE["user_env"],
        "date" => DateCustom::mkdate(),
        "message" => $msg,
        "user_id" => $_COOKIE["id_env"],
        "t_id" => $_POST["t_id"],
        "upload_id" => 0
		));

    if ($tRec[0]["monitor"] != "") {
			//CONVERT IDS TO EMAIL ADDRESSES
			$monitor_ids = explode(",",$tRec[0]['monitor']);
			$monitor_emails = array();
      foreach ($monitor_ids as $monitor_id)
			{
        if ($monitor_id == $_COOKIE["id_env"])
          continue;
        $user_details = $tdb->basicQuery('users','id',$monitor_id); 
        $monitor_emails[] = $user_details[0]['email'];    
      }
      $monitors = implode(",",$monitor_emails);
      $msg2 = str_replace(array("<x>","&lt;x&gt;"),"",$msg); // strip <x> from email
      $local_dir = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']);
			$e_sbj = "New Reply in \"".$tRec[0]["subject"]."\"";
			$e_msg = "You, or someone else using this e-mail address has requested to watch this topic: ".$tRec[0]["subject"]." at ".$local_dir."/index.php\n\n".$_COOKIE["user_env"]." wrote:\n".$msg2."\n\n- - - - -\nTo read the rest of this topic, visit ".$local_dir."/viewtopic.php?id=".$_POST["id"]."&t_id=".$_POST["t_id"]."&page=".$_POST["page"]."\nOr you can reply immediately if you forum cookies are valid by visiting ".$local_dir."/newpost.php?id=".$_GET["id"]."&t=0&t_id=".$_GET["t_id"]."&page=".$vars['page'];
			$e_hed = "From: ".$_REGISTER["admin_email"]."\r\n";
			$e_hed .= "Bcc: ".$monitors."\r\n"; //More efficient to send one e-mail with everyone on a BLANK CARBON COPY (see php.net's mail())
			@mail("", $e_sbj, $e_msg, $e_hed);
		}

		$rec = $posts_tdb->get("topics", $_POST["t_id"]);
		$posts_tdb->edit("topics", $_POST["t_id"], array("replies" => ((int)$rec[0]["replies"] + 1), "last_post" => DateCustom::mkdate(), "user_name" => $_COOKIE["user_env"], "user_id" => $_COOKIE["id_env"], "p_ids" => $rec[0]["p_ids"].",".$p_id));
		clearstatcache();
		$posts_tdb->sort("topics", "last_post", "DESC");
		clearstatcache();

		if($_COOKIE["power_env"] != "0")
		{
			$user = $tdb->get("users",$_COOKIE["id_env"]);
			$tdb->edit("users", $_COOKIE["id_env"], array("posts" => ((int)$user[0]["posts"] + 1)));
		}

		$posts_tdb->cleanUp();
		$fRec = $tdb->get("forums", $_POST["id"]);
		$posts_tdb->setFp("topics", $_POST["id"]."_topics");
		$posts_tdb->setFp("posts", $_POST["id"]);
		$tRec = $posts_tdb->get("topics", $_POST["t_id"]);
		$posts_tdb->set_topic($tRec);
		$posts_tdb->set_forum($fRec);
		$tdb->setFp('users', 'members');
		if(!($tdb->is_logged_in()))
		{
			$posts_tdb->set_user_info("guest", "password", "0", "0");
			$_COOKIE["power_env"] = 0;
		}
		else $posts_tdb->set_user_info($_COOKIE["user_env"], $_COOKIE["uniquekey_env"], $_COOKIE["power_env"], $_COOKIE["id_env"]);
		$_SESSION['newTopics']['f'.$_POST['id']]['t'.$_POST['t_id']] = 0;
		$_SESSION['view_'.$_POST['id'].'_'.$_POST['t_id']] = time();
		$page=1;

		$postids = $tRec[0]['p_ids'];
		$postnums = explode(",",$postids);

		$count = count($postnums);

		$num_pages = ceil($count/$_CONFIG["posts_per_page"]);
		$page = $num_pages;

		$pRecs = $posts_tdb->getPosts("posts", (($_CONFIG["posts_per_page"] * $page)-$_CONFIG["posts_per_page"]), $_CONFIG["posts_per_page"]);

		$query = "id={$_POST['id']}&t_id={$_POST['t_id']}";

		$p = MiscFunctions::createPageNumbers($page, $num_pages, $query,true);
		$p = str_replace('ajax.php', 'viewtopic.php', $p);
		$pagelinks1 = $posts_tdb->d_posting($email_mode, $isWatching,$p,$page,$num_pages);
		$pagelinks2 = $posts_tdb->d_posting($email_mode, $isWatching,$p,$page,$num_pages,"bottom") . "</div>";

		//BEGIN NEW REPLY OUTPUT
		$x = +1;
		$output = "";

		foreach($pRecs as $key => $pRec)
		{
			// display new reply
			$output .= "<a name='{$pRec['id']}'>
      <div name='post{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}' id='post{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}'>
      <div class='main_cat_wrapper'>
			<div class='cat_area_1' style='text-align:center;'>Posted: ".gmdate("M d, Y g:i:s a", DateCustom::user_date($pRec["date"]))."</div>
			<table class='main_table'>";
			if ($x == 0)
			{
				$table_color = "area_1";
				$x++;
			} else
			{
				$table_color = "area_2";

				$x--;
			}
			unset($user, $status, $statuscolor,$statusrank);
			$sig = '';
			$status = '';
			$statuscolor = '';
			$statusrank = '';
			$pm = "";
			if ($pRec["user_id"] != "0") {
				$user = $tdb->get("users", $pRec["user_id"]);
				if($user === false) {
					$user = array(array('level'=>0, 'status'=>'<i>Deleted Member</i>'));
					$pRec['user_id'] = '0';
				}
				if ($user[0]["sig"] != "") {
					$sig = format_text(filterLanguage(UPBcoding($user[0]["sig"]), $_CONFIG));
					$sig = "<div class='signature'>$sig</div>";
				}
				$status_config = status($user);
				$status = $status_config['status'];
				$statuscolor = $status_config['statuscolor'];
				$statusrank = $status_config['rank'];
				if ($user[0]["status"] != "") $status = $user[0]["status"];
				if (isset($_COOKIE["id_env"]) && $pRec["user_id"] != $_COOKIE["id_env"]) {
					$user_blList = PrivateMessaging::getUsersPMBlockedList($pRec["user_id"]);
					if (TRUE !== (in_array($_COOKIE["id_env"], $user_blList))) $pm = "<div class='button_pro2'><a href='newpm.php?to=".$pRec["user_id"]."'>Send ".$pRec["user_name"]." a PM</a></div>";
				}
			}
			if (($_COOKIE["id_env"] == $pRec["user_id"] && $tdb->is_logged_in()) || (int)$_COOKIE["power_env"] >=2)
			$edit = "<div class='button_pro1'><a href=\"javascript:getPost('{$pRec["user_id"]}','{$_POST["id"]}-{$_POST["t_id"]}-{$pRec["id"]}','edit');\">Edit</a></div>";
			//$edit = "<div class='button_pro1'><a href=\"editpost.php?id={$_POST["id"]}&t_id={$_POST["t_id"]}&p_id={$pRec["id"]}\">Edit</a></div>";
			else $edit = "";
			if ((($_COOKIE["id_env"] == $pRec["user_id"] && $tdb->is_logged_in()) || (int)$_COOKIE["power_env"] >= 2) && $pRec['id'] != $postnums[0])
			$delete = "<div class='button_pro1'><a href='delete.php?action=delete&t=0&id=".$_POST["id"]."&t_id=".$_POST["t_id"]."&p_id=".$pRec["id"]."'>X</a></div>";
			else $delete = "";


			if ((int)$_COOKIE["power_env"] >= (int)$fRec[0]["reply"] and $tRec[0]['locked'] != 1) $quote = "<div class='button_pro1'><a href=\"javascript:addQuote('".$pRec["user_name"]."-".$pRec["id"]."-".$pRec['date']."','".$pRec["message"]."')\">\"Quote\"</a></div>";
			else $quote = "";

			if ((int)$_COOKIE["power_env"] >= (int)$fRec[0]["reply"] and $tRec[0]['locked'] != 1) $reply = "<div class='button_pro1'><a href='newpost.php?id=".$_POST["id"]."&t=0&t_id=".$_POST["t_id"]."&page=$page'>Add Reply</a></div>";
			else $reply = "";

			$msg = display_msg($pRec["message"]);
			$msg .= "<div id='{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}-attach'>".$tdb->getUploads($_GET['id'],$_GET['t_id'],$pRec['id'],$pRec['upload_id'],$_CONFIG['fileupload_location'],$pRec['user_id'])."</div>";

			$output .= "
			<tr>
				<th><div class='post_name'>";
			if ($pRec["user_id"] != "0") $output .= "<a href='profile.php?action=get&id=".$pRec["user_id"]."'>".$pRec["user_name"]."</a>";
			else $output .= $pRec["user_name"];
			$output .= "</div></th>
				<th><div style='float:left;'><img src='".SKIN_DIR."/icons/post_icons/".$pRec["icon"]."'></div><div align='right'>$delete $edit $quote $reply</div></th>
			</tr>
			<tr>
				<td class='$table_color' valign='top' style='width:15%;'>";
			if (@$user[0]["avatar"] != "")
			{
				$resize = MiscFunctions::resize_img($user[0]['avatar'],$_REGIST["avatarupload_dim"]);
				$output .= "<br /><center><img src=\"".$user[0]["avatar"]."\" border='0' $resize alt='' title=''></center><br />";
			}
			else $output .= "<br /><br />";
			$output .= "<div class='post_info'><center><span style='color:#".$statuscolor."'><img src='".$statusrank."'><br><strong>".$status."</strong></span></center></div>";
			if ($pRec["user_id"] != "0") $output .= "

					<div class='post_info'>
						<strong>Posts:</strong> ".$user[0]["posts"]."
						<br />
						<strong>Registered:</strong>
						<br />
						".gmdate("Y-m-d", DateCustom::user_date($user[0]["date_added"]))."
					</div>
					<br />
					<div class='post_info_extra'>";
			if ($user[0]["aim"] != "") $output .= "&nbsp;<a href='aim:goim?screenname=".$user[0]["aim"]."'><img src='images/aol.gif' border='0' alt='AIM: ".$user[0]["aim"]."'></a>&nbsp;&nbsp;";
			if ($user[0]["msn"] != "") $output .= "&nbsp;<a href='http://members.msn.com/".$user[0]["msn"]."' target='_blank'><img src='images/msn.gif' border='0' alt='MSN: ".$user[0]["msn"]."'></a>&nbsp;&nbsp;";
			if ($user[0]["icq"] != "") $output .= "&nbsp;<a href='http://wwp.icq.com/scripts/contact.dll?msgto=".$user[0]["icq"]."&action=message'><img src='images/icq.gif' border='0' alt='ICQ: ".$user[0]["icq"]."'></a>&nbsp;&nbsp;";
			if ($user[0]["yahoo"] != "") $output .= "&nbsp;<a href='http://edit.yahoo.com/config/send_webmesg?.target=".$user[0]["yahoo"]."&.src=pg'><img border=0 src='http://opi.yahoo.com/online?u=".$user[0]["yahoo"]."&m=g&t=0' alt='Y!: ".$user[0]["yahoo"]."'></a>";

			$output .= "</div>";
			$output .= "</td>
				<td class='$table_color' valign='top'>
					<div style='padding:12px;margin-bottom:20px;' id='{$_POST['id']}-{$_POST['t_id']}-{$pRec['id']}' name='{$_POST['id']}-{$_POST['t_id']}-{$pRec['id']}'>$msg</div>
					<div style='padding:12px;'>".$sig."</div></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2'>";
			if ($pRec["user_id"] != "0") $output .= "";
			if ($pm != "") $output .= $pm."";

			//echo "<div name='edit{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}' id='edit{$_GET['id']}-{$_GET['t_id']}-{$pRec['id']}' style='float: right;'>";
			if (!empty($pRec['edited_by']) && !empty($pRec['edited_by_id']) && !empty($pRec['edited_date']))
			$output .= "<div class='post_edited' name='edit{$_POST['id']}-{$_POST['t_id']}-{$pRec['id']}' id='edit{$_POST['id']}-{$_POST['t_id']}-{$pRec['id']}'>Last edited by: <a href='profile.php?action=get&id=".$pRec['edited_by_id']." target='_new'><strong>".$pRec['edited_by']."</strong></a> on ".gmdate("M d, Y g:i:s a", DateCustom::user_date($pRec['edited_date']))."</div>";
			else
			$output .= "<div name='edit{$_POST['id']}-{$_POST['t_id']}-{$pRec['id']}' id='edit{$_POST['id']}-{$_POST['t_id']}-{$pRec['id']}' class='post_edited'></div>";
			if ($pRec['user_id'] != 0)
			{
				$output .= "
					<div class='button_pro2'><a href='profile.php?action=get&id=".$pRec["user_id"]."'>Profile</a></div>";
				if (MiscFunctions::isValidURL($user[0]['url']))
				$output .= "<div class='button_pro2'><a href='".$user[0]["url"]."' target = '_blank'>Homepage</a></div>";
				if ($_CONFIG['email_mode'])
				$output .= "<div class='button_pro2'><a href='email.php?id=".$pRec["user_id"]."'>email ".$pRec["user_name"]."</a></div>";
			}
			$output .= "</td>
			</tr>
		</tbody>
		</table>
		<div class='footer'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></div>
	</div>
	<br />";
		}

		$qrform = ""; //NEW QUICK REPLY FORM
		if ($tRec[0]['locked'] != 1)
		{
			$qrform .= "<form name='quickreplyfm' action='newpost.php?id=".$_POST['id']."&t_id=".$_POST['id']."&page=".$page."' method='POST' name='quickreply'>\n";
			$qrform .= "<div class='main_cat_wrapper'>
		<div class='cat_area_1'>Quick Reply</div>
		<table class='main_table'>
		<tbody>";
			$qrform .= "<table class='main_table'>";
			$qrform .= "<input type='hidden' id='id' name='id' value='".$_POST['id']."'>\n";
			$qrform .= "<input type='hidden' id='t_id' name='t_id' value='".$_POST['t_id']."'>\n";
			$qrform .= "<input type='hidden' id='page' name='page' value='".$_POST['page']."'>\n";
			$qrform .= "<input type='hidden' id='user_id' name='user_id' value='{$_COOKIE['id_env']}'>\n";
			$qrform .= "<input type='hidden' id='icon' name='icon' value='icon1.gif'>\n";
			$qrform .= "<input type='hidden' id='username' name='username' value='{$_COOKIE["user_env"]}'>\n";
			$qrform .= "
		<tr><td class='area_1' style='padding:8px;'><strong>User Name:</strong></td><td class='area_2'>".$_COOKIE["user_env"]."</td></tr>\n
		<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
		<tr><td class='area_1' style='padding:8px;' valign='top'><strong>Message:</strong></td>
    <td class='area_2'>\n
    <textarea id=\"newentry\" value=\"\" name=\"newentry\" cols=\"60\" rows=\"18\"></textarea>\n
    </td></tr>\n";
			$qrform .= "<tr><td class='footer_3a' style='text-align:center;' colspan='2'>\n
    <input type='button' name='quickreply' value='Quick Reply' onclick=\"document.quickreplyfm.quickreply.disabled=true;javascript:getReply(document.getElementById('quickreply'))\">\n
    <input type='submit' name='submit' value='Advanced'>\n</td></tr></form></font>";
			$qrform .= "</tbody>
		</table>
		<div class='footer'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></div>
	</div>
	<br />";
		}
		$output .= "<!--divider-->$pagelinks1<!--divider-->$pagelinks2<!--divider-->$qrform";
		echo $output;
		break 1;

	case "sort" :
		//SORTING OF FORUMS AND CATEGORIES
		$output = "";
		if($_POST['what'] == 'cat')
		$sort = $_CONFIG['admin_catagory_sorting'];
		elseif($_POST['what'] == 'forum')
		{
			$fRec = $tdb->get('forums', $_POST['id']);
			$cRec = $tdb->get('cats', $fRec[0]['cat']);
			$sort = $cRec[0]['sort'];
		}

		$sort = explode(',', $sort);

		if(FALSE !== ($index = array_search($_POST['id'], $sort)))
		{
			if($_POST['where'] == 'up' && $index > 0)
			{
				$tmp = $sort[$index-1];
				$sort[$index-1] = $sort[$index];
				$sort[$index] = $tmp;
			}
			elseif($_POST['where'] == 'down' && $index < (count($sort)-1))
			{
				$tmp = $sort[$index+1];
				$sort[$index+1] = $sort[$index];
				$sort[$index] = $tmp;
			}
			$sort = implode(',', $sort);

			if($_POST['what'] == 'cat')
			$config_tdb->editVars('config', array('admin_catagory_sorting' => $sort));
			elseif($_POST['what'] == 'forum')
			$tdb->edit('cats', $cRec[0]['id'], array('sort' => $sort));
		}

		$tdb->cleanUp();
		$tdb->setFp('forums', 'forums');
		$tdb->setFp('cats', 'categories');

		$cRecs = $tdb->listRec("cats", 1);
		$config_tdb->clearcache();

		$query = $config_tdb->basicQuery('config','name',"admin_catagory_sorting");

		$cSorting = explode(",", $query[0]['value']);

		$k = 0;
		$i = 0;
		$sorted = array();
		while ($i < count($cRecs)) {
			if ($cSorting[$k] == $cRecs[$i]["id"])
			{
				$sorted[] = $cRecs[$i];
				//unset($cRecs[$i]);
				$k++;
				$i = 0;
			}
			else
			$i++;
		}

		$cRecs = $sorted;
		unset($sorted, $i, $catdef, $cSorting);
		reset($cRecs);

		$output .= "<div class='main_cat_wrapper'>
		<div class='cat_area_1'>Forum Control</div>
		<table class='main_table'>
		<tbody>";

		$output .= "
			<tr>
			    <th style='width:7%;'>&nbsp;</th>
				<th style='width:68%;'>Name</th>
				<th style='width:5%;text-align:center;'>View</th>
				<th style='width:5%;text-align:center;'>Post</th>
				<th style='width:5%;text-align:center;'>Reply</th>
				<th style='width:10%;text-align:center;'>Edit?</th>
				<th style='width:10%;text-align:center;'>Delete?</th>
			</tr>";
		if ($cRecs[0]["name"] == "") {
			$output .= "
			<tr>
				<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='6'>No categories found</td>
			</tr>";
		} else {
			for($i=0,$c1=count($cRecs);$i<$c1;$i++) {
				//show each category
				$view = MiscFunctions::createUserPowerMisc($cRecs[$i]["view"], 2);
				$output .= "
			<tr>
			    <td class='area_1' style='padding:8px;text-align:center;'>".(($i>0) ? "<a href=\"javascript:forumSort('cat','up','".$cRecs[$i]['id']."');\"><img src='./images/up.gif'></a>" : "&nbsp;&nbsp;&nbsp;").(($i<($c1-1)) ? "<a href=\"javascript:forumSort('cat','down','".$cRecs[$i]['id']."');\"><img src='./images/down.gif'></a>" : "")."</td>
				<td class='area_1' style='padding:8px;'><strong>".$cRecs[$i]["name"]."</strong></td>
				<td class='area_1' style='padding:8px;text-align:center;' colspan=3>$view</td>
				<td class='area_1' style='padding:8px;text-align:center;'><a href='admin_forums.php?action=edit_cat&id=".$cRecs[$i]["id"]."'>Edit</a></td>
				<td class='area_1' style='padding:8px;text-align:center;'><a href='admin_forums.php?action=delete_cat&id=".$cRecs[$i]["id"]."'>Delete</a></td>
			</tr>";

				if($cRecs[$i]['sort'] == '') {
					$output .= "
			<tr>
				<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='7'>No forums exist in this category yet.</td>
			</tr>";
				} else {
					$ids = explode(',', $cRecs[$i]['sort']);
					for($j=0,$c2=count($ids);$j<$c2;$j++) {
						$fRec = $tdb->get('forums', $ids[$j]);
						//$post_tdb->setFp("topics", $fRec[0]["id"]."_topics");
						//$post_tdb->setFp("posts", $fRec[0]["id"]);
						$whoView = MiscFunctions::createUserPowerMisc($fRec[0]["view"], 3);
						$whoPost = MiscFunctions::createUserPowerMisc($fRec[0]["post"], 3);
						$whoReply = MiscFunctions::createUserPowerMisc($fRec[0]["reply"], 3);
						//show each forum
						$output .= "
			<tr>
			    <td class='area_2' style='padding:8px;text-align:center;'>".(($j>0) ? "<a href=\"javascript:forumSort('forum','up','".$fRec[0]['id']."');\"><img src='./images/up.gif'></a>" : "&nbsp;&nbsp;&nbsp;").(($j<($c2-1)) ? "<a href=\"javascript:forumSort('forum','down','".$fRec[0]['id']."');\"><img src='./images/down.gif'></a>" : "&nbsp;&nbsp;&nbsp;")."</td>
				<td class='area_2' style='padding:8px;'><strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$fRec[0]["forum"]."</td>
				<td class='area_2' style='padding:8px;text-align:center;'>$whoView</td>
				<td class='area_2' style='padding:8px;text-align:center;'>$whoPost</td>
				<td class='area_2' style='padding:8px;text-align:center;'>$whoReply</td>
				<td class='area_2' style='padding:8px;text-align:center;'><a href='admin_forums.php?action=edit_forum&id=".$fRec[0]["id"]."'>Edit</a></td>
				<td class='area_2' style='padding:8px;text-align:center;'><a href='admin_forums.php?action=delete_forum&id=".$fRec[0]["id"]."'>Delete</a></td>
			</tr>";
					}
				}
			}
		}
		$output .= "
		</tbody>
		</table>
		<div class='footer'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></div>
	</div>
	<br />";
		echo $output;

		break 1;

	case "username" :
		if ($_POST['area'] == "pm")
		{
			$q = $tdb->query("users", "user_name='".strtolower($_POST["username"])."'", 1, 1);

			if ($q === false)
			{
				$reply = "<img src='images/cross.gif' alt='' title='' style='vertical-align: middle;'>User doesn't exist";
				$valid = "false";
			}
			else if ($_COOKIE['id_env'] == $q[0]['id'])
			{
				$reply = "<br /><img src='images/cross.gif' alt='' title='' style='vertical-align: middle;'>You can't send a PM to yourself";
				$valid = "false";
			}
			else
			{
				$reply = "<img src='images/tick.gif' alt='' title='' style='vertical-align: middle;'>";
				$valid = "true";
			}

		}
		else
		{
			$_POST['username'] = format_text(encode_text(trim($_POST['username'])));
			if ($_POST['area'] == 'changeuser')
			$newline = '&nbsp';
			else
			$newline = "<br />";
			if (trim($_POST['username']) == "")
			{
				$reply = "<br /><img src='images/cross.gif' alt='' title='' style='vertical-align: middle;'>Username Required";
				$valid = "false";
			}
			else
			{
				$q = $tdb->query("users", "user_name='".strtolower($_POST["username"])."'", 1, 1);
				if (strtolower($_POST["username"]) == strtolower($q[0]["user_name"]))
				{
					$reply = "$newline<img src='images/cross.gif' alt='' title='' style='vertical-align: middle;'>Username already exists";
					$valid = "false";
				}
				else
				{
					$reply = "<img src='images/tick.gif' alt='' title='' style='vertical-align: middle;'>";
					$valid = "true";
				}
			}
		}
		echo $valid."<!--divider-->".$reply;
		break 1;

	case "emailvalid" :
		$_POST['email'] = format_text(encode_text(trim($_POST['email'])));
		if (trim($_POST['email']) == "")
		{
			$reply .= "<br><img src='images/cross.gif' alt='' title='' style='vertical-align: middle;'>Email Address Required";
			$valid = "false";
		}
		else if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*(\+[_a-z0-9-]+(\.[_a-z0-9-]+)*)*@[a-z0-9-]+(\.[a-z0-9-]+)*$/i", $_POST["email"]))
		{
			$reply = "<br><img src='images/cross.gif' alt='' title='' style='vertical-align: middle;'>Invalid Email Address";
			$valid = "false";
		}
		else
		{
			$q = $tdb->query("users", "email='".$_POST["email"]."'", 1, 1);
			if ($_POST["email"] == $q[0]["email"])
			{
				$reply = "<br><img src='images/cross.gif' alt='' title='' style='vertical-align: middle;'>Email address already used";
				$valid = "false";
			}
			else
			{
				$reply = "&nbsp;<img src='images/tick.gif' alt='' title='' style='vertical-align: middle;'>";
				$valid = "true";
			}
		}
		echo $valid."<!--divider-->".$reply;
		break 1;

	case "emailcheck" :
		$_POST['email1'] = format_text(encode_text(trim($_POST['email1'])));
		$_POST['email2'] = format_text(encode_text(trim($_POST['email2'])));
		 
		if (trim($_POST['email1']) != trim($_POST['email2']))
		{
			$reply = "<br><img src='images/cross.gif' alt='' title='' style='vertical-align: middle;'>Email Addresses don't match";
			$valid = "false";
		}
		else
		{
			$reply = "&nbsp;<img src='images/tick.gif' alt='' title='' style='vertical-align: middle;'>";
			$valid = "true";
		}
		echo $valid."<!--divider-->".$reply;
		break 1;

	case "sig":
		if ($_POST['status'] == "set")
		{
			$sig = display_msg(encode_text($_POST["sig"]));
			$sig_title = "<strong>Signature Preview:</strong><br>To save this signature press Submit below";
		}
		else
		{
			$rec = $tdb->get("users", $_POST["id"]);
			$sig = display_msg($rec[0]['sig']);
			$sig_title = "<strong>Current Signature:</strong>";
		}
		echo $sig."<!--divider-->".$sig_title;
		break 1;

	case "delfile":
		$fRec = $tdb->get("forums", $_POST["forumid"]);
		$posts_tdb = new Posts(DB_DIR."/", "posts.tdb");
		$posts_tdb->setFp("topics", $_POST["forumid"]."_topics");
		$posts_tdb->setFp("posts", $_POST["forumid"]);
		$upload = new Upload(DB_DIR, $_CONFIG["fileupload_size"],$_CONFIG["fileupload_location"]);

		$upload->deleteFile($_POST['fileid']);
		$pRec = $posts_tdb->get("posts", $_POST["postid"]);

		$split = explode(",",$pRec[0]['upload_id']);
		$key = array_search($_POST['fileid'],$split);
		unset($split[$key]);
		$new = implode(',',$split);
		$posts_tdb->edit("posts", $_POST["postid"], array("message" => $dbmsg, "edited_by_id" => $_COOKIE["id_env"], "edited_by" => $_COOKIE["user_env"], "edited_date" => DateCustom::mkdate(),'upload_id'=>$new));
		$pRec2 = $posts_tdb->get("posts", $_POST["postid"]);
		$output .= $tdb->getUploads($_POST['forumid'],$_POST['threadid'],$_POST["postid"],$pRec2[0]['upload_id'],$_CONFIG['fileupload_location'],$_POST['userid']);
		$edited = "Last edited by: <a href='profile.php?action=get&id=".$pRec2[0]['edited_by_id']."' target='_new'>".$pRec2[0]['edited_by']."</a> on ".gmdate("M d, Y g:i:s a", DateCustom::user_date($pRec2[0]['edited_date']));
		echo $output."<!--divider-->".$edited;
		break;

	case "preview":
		if (trim($_POST['message']) == '' or empty($_POST['message']))
		echo "";
		else
		{
			MiscFunctions::echoTableHeading("Post Preview", $_CONFIG);
			$msg = display_msg(encode_text($_POST["message"]));
			echo "<tr><td class='area_2'><div class='msg_block'>".$msg."</div></td></tr>";
			MiscFunctions::echoTableFooter(SKIN_DIR);
		}
		break 1;

	case "validate":
		break 1;

	default:
		echo "Something has gone horribly wrong. You should never see this text";
		break 1;
}
?>
