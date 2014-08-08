<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once('./includes/upb.initialize.php');
$fRec = $tdb->get("forums", $_GET["id"]);
$posts_tdb = new functions(DB_DIR."/", "posts.tdb");
$posts_tdb->setFp("topics", $_GET["id"]."_topics");
$posts_tdb->setFp("posts", $_GET["id"]);
$message = "";
if (!empty($_POST))
{
	$message = stripslashes(format_text(encode_text($_POST['newentry']),'edit'));
	$message = str_replace(array("<x>","&lt;x&gt;"),"",$message);
  foreach ($_POST as $key => $value)
	{
		$_GET[$key] = $value;
	}
}
$vars['page'] = $_GET['page'];
$where = "<a href='viewforum.php?id=".$_GET["id"]."'>".$fRec[0]["forum"]."</a> ".$_CONFIG["where_sep"];
if ($_GET["t_id"] == "") {
	$where .= " New Topic";
} else {
	$tRec = $posts_tdb->get("topics", $_GET["t_id"]);
	$where .= " <a href='viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&page=".$vars['page']."'>".$tRec[0]["subject"]."</a> ".$_CONFIG["where_sep"]." Post Reply";
}
if (!isset($a)) $a = 0;
require_once('./includes/header.php');
if (!($tdb->is_logged_in())) {
	$_COOKIE["user_env"] = "guest";
	$_COOKIE["power_env"] = 0;
	$_COOKIE["id_env"] = 0;
}
if ($_COOKIE["power_env"] < $fRec[0]["post"] && $_GET["t"] == 1 || $_COOKIE["power_env"] < $fRec[0]["reply"] && $_GET["t"] == 0) exitPage("<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>You do not have the rights to perform this action.</div></div>");
if (!($_GET["id"] != "" && ctype_digit($_GET["id"]))) exitPage("<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>Invalid Forum ID/Information.</div></div>");
if (!($_GET["t_id"] != "" && ctype_digit($_GET["t_id"]) || $_GET["t"] != 0)) exitPage("<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>Invalid Topic ID/Information.</div></div>");
if ($_POST["a"] == "1") {
	if (isset($_POST['subject'])) $_POST['subject'] = encode_text(stripslashes($_POST["subject"]));
	$_POST['message'] = encode_text(stripslashes($_POST["message"]));
	if ($_POST["icon"] == "") exitPage("<div class='alert'><div class='alert_text'>
			<strong>Caution!</strong></div><div style='padding:4px;'>Please select a message icon.</div></div>");
	if ($_GET["t"] == 1 && trim($_POST["subject"]) == "") exitPage("<div class='alert'><div class='alert_text'>
			<strong>Caution!</strong></div><div style='padding:4px;'>You must enter a subject!</div></div>");
	if ($_POST["message"] == "") exitPage("<div class='alert'><div class='alert_text'>
			<strong>Caution!</strong></div><div style='padding:4px;'>You must type in a message!</div></div>");
	if ($_GET["t"] != 1 && isset($_GET["t_id"]) && (bool) $tRec[0]["locked"]) exitPage("<div class='alert'><div class='alert_text'>
			<strong>Caution!</strong></div><div style='padding:4px;'>The topic is closed to further posting.</div></div>");
	//FILE UPLOAD BEGIN

	$uploadText = '';
	$uploadId = array();
	$maxsize = $_CONFIG['fileupload_size'] * 1024; //convert KB to bytes
	$filetypes = explode(",",$_CONFIG['fileupload_types']);

	$names = $_FILES['upload']['name'];
	$enable_upload = false;

	foreach ($names as $name)
	{
		if (trim($name) != "")
		{
			$enable_upload = true;
			break;
		}
	}

	if ($enable_upload === true)
	{
		for ($i = 0;$i < count($_FILES['upload']['name']);$i++)
		{
			if (trim($_FILES['upload']['name'][$i]) == '')
			continue;

			$type = substr(strrchr($_FILES['upload']['name'][$i], '.'),1);

			if (!in_array(strtolower($type),$filetypes))
			{
				$type_key = $_FILES['upload']['name'][$i]."|".$type;
				$error[$type_key] = 'type';
				continue;
			}
			if ($_FILES['upload']['size'][$i] > $maxsize)
			{
				$error[$_FILES['upload']['name'][$i]] ='size';
				echo "file is too big";
        continue;
			}
			$files[$i]['name'] = $_FILES['upload']['name'][$i];
			$files[$i]['type'] = $_FILES['upload']['type'][$i];
			$files[$i]['tmp_name'] = $_FILES['upload']['tmp_name'][$i];
			$files[$i]['error'] = $_FILES['upload']['error'][$i];
			$files[$i]['size'] = $_FILES['upload']['size'][$i];
		}

		//dump($error);
    
    $error_msg = "";
    
		if (!empty($error))
		{
			$error_msg = "The following files will not be uploaded:<p> ";
			foreach ($error as $key => $err)
			{
				if ($err == 'size')
				$error_msg .= $key." is too big<p>";
				if ($err == 'type')
				{
					list($err_file,$file_type) = explode("|",$key);
					$error_msg .= "$err_file - $file_type is not an allowed filetype<p>";
				}
			}
		}

		if (count($files) > 0)
		{
			foreach ($files as $file)
			{
				if($_file['upload']['error'][$i] == UPLOAD_ERR_OK) {
					$upload = new Upload(DB_DIR, $_CONFIG["fileupload_size"],$_CONFIG["fileupload_location"]);
						
					$result = $upload->storeFile($file, $_GET["id"], $_GET["t_id"]);
					if (!is_int($result))
					echo $result;
					else
					$uploadId[] = $result;
					//if ($uploadId === false) $uploadId = 0;
				}
			}
		}
	}
	
	//FILE UPLOAD END
	
  if ($_GET["t"] == 1) {
		if (!isset($_POST["sticky"])) $_POST["sticky"] = "0";
		if (!isset($_POST["locked"])) $_POST["locked"] = "0";
		$_POST["subject"] = trim($_POST["subject"], $_CONFIG['stick_note']);
		if (trim($_POST["subject"]) == "") exitPage("<div class='alert'><div class='alert_text'>
				<strong>Caution!</strong></div><div style='padding:4px;'>You must enter a subject!</div></div>");
		$_GET['t_id'] = $posts_tdb->add("topics", array(
			"icon" => $_POST["icon"],
				"subject" => $_POST["subject"],
				"topic_starter" => $_COOKIE["user_env"],
				"sticky" => $_POST["sticky"],
				"replies" => "0",
				"views" => "0",
				"locked" => $_POST["locked"],
				"last_post" => mkdate(),
				"user_name" => $_COOKIE["user_env"],
				"user_id" => $_COOKIE["id_env"] ));
		echo "
	<div class='alert_confirm'>
		<div class='alert_confirm_text'>
		<strong>Redirecting:</div><div style='padding:4px;'>
		Making new topic....
		</div>
	</div>";
		$tdb->edit("forums", $_GET["id"], array("topics" => ((int)$fRec[0]["topics"] + 1), "posts" => ((int)$fRec[0]["posts"] + 1)));
		$redirect = "viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET['t_id'];
		$pre = "";
	} else {
		echo "
	<div class='alert_confirm'>
		<div class='alert_confirm_text'>
		<strong>Redirecting:</div><div style='padding:4px;'>
		Adding Reply....
		</div>
	</div>";
		$tdb->edit("forums", $_GET["id"], array("posts" => ((int)$fRec[0]["posts"] + 1)));
		$rec = $posts_tdb->get("topics", $_GET["t_id"]);
		if (isset($_POST["unstick"])) $rec[0]["sticky"] = "0";
		
    if ($rec[0]["monitor"] != "") {
			//CONVERT IDS TO EMAIL ADDRESSES
			$monitor_ids = explode(",",$rec[0]['monitor']);
			$monitor_emails = array();
      foreach ($monitor_ids as $monitor_id)
			{
        if ($monitor_id == $_COOKIE["id_env"])
          continue;
        $user_details = $tdb->basicQuery('users','id',$monitor_id);
        $monitor_emails[] = $user_details[0]['email'];
      }
      $monitors = implode(",",$monitor_emails);
      $msg = str_replace(array("<x>","&lt;x&gt;"),"",$_POST['message']); //strip <x> from message
      $local_dir = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['SCRIPT_NAME']);
			$e_sbj = "New Reply in \"".$rec[0]["subject"]."\"";
			$e_msg = "You, or someone else using this e-mail address has requested to watch this topic: ".$rec[0]["subject"]." at ".$local_dir."/index.php\n\n".$_COOKIE["user_env"]." wrote:\n".$msg."\n\n- - - - -\nTo read the rest of this topic, visit ".$local_dir."/viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&page=".$_GET["page"]."\nOr you can reply immediately if you forum cookies are valid by visiting ".$local_dir."/newpost.php?id=".$_GET["id"]."&t=0&t_id=".$_GET["t_id"]."&page=".$vars['page'];
			$e_hed = "From: ".$_REGISTER["admin_email"]."\r\n";
			$e_hed .= "Bcc: ".$monitors."\r\n"; //More efficient to send one e-mail with everyone on a BLANK CARBON COPY (see php.net's mail())
			@mail("", $e_sbj, $e_msg, $e_hed);
		}
		$posts_tdb->edit("topics", $_GET["t_id"], array("replies" => ((int)$rec[0]["replies"] + 1), "last_post" => mkdate(), "user_name" => $_COOKIE["user_env"], "sticky" => $rec[0]["sticky"], "user_id" => $_COOKIE["id_env"]));
		if ($_GET["page"] == "") $vars['page'] = 1;
		$redirect = "viewtopic.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&page=".$vars['page'];
		$pre = $rec[0]["p_ids"].",";
	}
	clearstatcache();
	$posts_tdb->sort("topics", "last_post", "DESC");
	clearstatcache();
	$p_id = $posts_tdb->add("posts", array(
			"icon" => $_POST["icon"],
			"user_name" => $_COOKIE["user_env"],
			"date" => mkdate(),
			"message" => $uploadText.$_POST["message"],
			"user_id" => $_COOKIE["id_env"],
			"t_id" => $_GET["t_id"],
			"upload_id" => implode(',',$uploadId) ));

	$posts_tdb->edit("topics", $_GET["t_id"], array("p_ids" => $pre.$p_id));

	if ($_COOKIE["power_env"] != "0") {
		$user = $tdb->get("users", $_COOKIE["id_env"]);
		$tdb->edit("users", $_COOKIE["id_env"], array("posts" => ((int)$user[0]["posts"] + 1)));
	}
	$_SESSION['newTopics']['f'.$_GET['id']]['t'.$_GET['t_id']] = 0;
	$_SESSION['view_'.$_GET['id'].'_'.$_GET['t_id']] = time();
	
  if ($error_msg == "")
    redirect($redirect.'#'.$p_id, 1);
  else
    {
    $error_msg .= "<br><a href='viewtopic.php?id=".$_GET['id']."&t_id=".$_GET['t_id']."&page=".$vars['page']."'>Click here to continue</a>";
    echo "<div class='alert'><div class='alert_text'><strong>Warning!</strong></div><div style='padding:4px;'>$error_msg</div></div>";
    }
} else {

	if (!isset($_GET["page"]) or $_GET['page'] == "") $vars['page'] = 1;

	if ($_GET["t"] == 1) {
		$tpc = "
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Subject:</strong></td>
				<td class='area_2'><input type='text' name='subject' size='40'> <span id='sub_err' class='err'></span></td>
			</tr>";
		if ($_COOKIE["power_env"] >= 3) $sticky = "
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Sticky:</strong></td>
				<td class='area_2'><input type=checkbox name=sticky size=40 value=\"1\"></td>
			</tr>";
		$hed = "New Topic";
		$iframe = "";
		$check = "topic";
	} else {
		if (isset($_GET['q_id'])) {
			$hed = "Reply Quote";
			$reply = $posts_tdb->get("posts", $_GET['q_id']);
			$message = "[quote=".$reply[0]['user_name'].";".$_GET['q_id'].";".$reply[0]['date']."]".$reply[0]["message"]."[/quote]";
		}
		else $hed = "Reply";
		$tpc = "";
		$check = "reply";
		if ($_COOKIE["power_env"] >= 3) $sticky = "
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Un-Sticky:</strong></td>
				<td class='area_2'><input type=checkbox name=unstick size=40 value=\"1\"></td>
			</tr>";
		$iframe = "<br />
					<div class='main_cat_wrapper'>
						<div class='cat_area_1'>Topic overview:</div>
						<table class='main_table'>
							<tr>
								<td class='review_container'><div class='review_sub'>
									<iframe src='viewtopic_simple.php?id=".$_GET["id"]."&t_id=".$_GET["t_id"]."&page=".$_GET["page"]."' class='review_frame' scrolling='auto' frameborder='0'></iframe></div></td>
							</tr>
						</table>
						<div class='footer'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></div>
					</div><br />";
	}
	$icons = message_icons();

	echo "
			<form action='newpost.php?id=".$_GET["id"]."&t=".$_GET["t"]."&quote=".$_GET["quote"]."&t_id=".$_GET["t_id"]."&page=".$_GET["page"]."' method='POST' name='newentry' enctype='multipart/form-data' onSubmit='return validate_$check();'>
			<input type='hidden' name='a' value='1'>";
	echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
			<tr>
				<td class='area_1' style='padding:8px;'><strong>User Name:</strong></td>
				<td class='area_2'>".$_COOKIE["user_env"]."</td>
			</tr>
			$tpc
			$sticky
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Message Icon:</strong></td>
				<td class='area_2'><div style='width:610px;'>$icons</div></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='area_1' style='padding:8px;' valign='top'><strong>Message:</strong>";
			echo "
					<div style='text-align:center;'></div></td>
				<td class='area_2'>
        ".bbcodebuttons('look1')."<textarea name='message' id='look1'>".$message."</textarea><br>
					<span id='msg_err' class='err'></span><div style='padding:8px;'><fieldset><legend>Smilies</legend><div style='padding:8px;'>".getSmilies('look1')."</fieldset></div></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='".SKIN_DIR."/images/spacer.gif' alt='' title='' /></td>
			</tr>";
			//if filesize set to 0 or filesize set to nothing and uploadlocation exists

			if ($_CONFIG["fileupload_size"] != "0" && $_CONFIG["fileupload_size"] != "" && is_numeric($_CONFIG["fileupload_size"]) && $_CONFIG["fileupload_location"] != "")
			{
				echo "
			<tr>
				<td class='area_1' style='padding:8px;'><strong>Attach file(s):</strong></td>
				<td class='area_2'>";
				$allowed_size = ($_CONFIG['fileupload_size'] > 1024) ? round(($_CONFIG['fileupload_size'] / 1024),2). "MB" : $_CONFIG['fileupload_size']. "KB";
				for ($i = 1;$i <= 5;$i++)
				{
					echo "File $i: <input type=\"file\" name=\"upload[]\" size=\"25\"><br /><br />";
				}
				echo "Valid file types: ".$_CONFIG['fileupload_types']."
					<br />Maximum file size is ".$allowed_size." per file.</td>
			</tr>";
			}
			echo "
			<tr>
				<td class='footer_3a' style='text-align:center;' colspan='2'><input type='reset' name='reset' value='Reset'><input type='button' onclick='postPreview()' value='Preview Post'><input type='submit' name='submit' value='Submit'></td>
			</tr>";
			echoTableFooter(SKIN_DIR);
			echo "
	</form> <div id='preview'></div>
	".$iframe."";
}
require_once('./includes/footer.php');
?>
