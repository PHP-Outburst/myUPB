<?php
//coding.php has gone
//skin.css has gone

if(!function_exists('array_fill_keys')) {
	function array_fill_keys($keys, $value) {
		$return = array();
		if(is_array($keys) && !empty($keys))
		foreach($keys as $key)
		$return[$key] = $value;
		return $return;
	}
}

require_once("./includes/upb.initialize.php");
//FPs already set in func.class.php
//dump($_POST);
$proceed = true;
$last_step = 7;
if (!isset($_POST['next']) or $_POST['next'] == '') $_POST['next'] = 0;
print MINIMAL_BODY_HEADER;
if($_POST['next'] == 1) {
	$_POST['next'] = 2;
	if(FALSE === strpos($_POST['register_msg'], '<login>') || FALSE === strpos($_POST['register_msg'], '<password>') || FALSE === strpos($_POST['register_msg'], '<url>')) {
		print(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'You must use all three options in the register e-mail message', ALERT_MSG)));
		$_POST['next'] = 0;
	} elseif(empty($_POST['superad'])) {
		print(str_replace('__TITLE__', ALERT_GENERIC_TITLE, str_replace('__MSG__', 'You must select at least one administrator to become a super administrator', ALERT_MSG)));
		$_POST['next'] = 0;
	}

}
if($_POST['next'] != $last_step) echo "
	<div class='main_cat_wrapper'>
		<div class='cat_area_1'>Update to v2.2.1</div>
		<form method='POST' action='".$_SERVER['PHP_SELF']."'>
    <table class='main_table'><tbody>";
if ($_POST['next'] == 0) {
	echo "<tr>
				<th colspan='2'><strong>Welcome to myUPB v2.2.1</strong></th>
			</tr>
			<tr>
			<td class='area_2' colspan='2'>";
	if(UPB_VERSION == '2.2.1') {
		echo '<P>The bulletin board is already using <b>v2.2.1</b>';
		$proceed = false;
	} else if (UPB_VERSION != "2.1.1b" && UPB_VERSION != "2.1.1") {
		echo "<p>You will need to update to <b>v2.1.1</b> first in order to update to <b>v2.2.1</b><br>This is due to configuration changes that have been implemented";
		$proceed = false;
	} else if(!is_writable('config.php') || !is_readable('config.php')) {
		$proceed = false;
		print '<P>You must chmod your config.php to 666 to proceed.';
	} elseif(!is_writable(DB_DIR) || !is_readable(DB_DIR)) {
		$proceed = false;
		print '<P>Your DATA directory must be chmoded to 777 to proceed.';
	} else {
		echo "<p>This release contains many new features,bug fixes and a new skin system.<br><b>Before you proceed, backup your skin and data directory before continuing</b><br>For the changelog, please see the <a href='readme.txt' target='_blank'>readme</a> or visit <a href='http:/www.myupb.com/' target='_blank'>MyUPB.com</a>";
		echo "</td>
		</tr>";
		echo "<tr>
			<th colspan='2'><strong>Super Administrator Creation</strong></th>
		</tr>
		<tr>
		<td colspan='2' class='area_2'>Please choose the administrators to be super administrators.<p>
		A super administrator's account cannot be deleted or banned and it's usergroup can't be changed.<p>This will prevent other administrators from demoting the board owner and hijacking the forum.<p><strong>Once selected it can't be changed.</strong>
		</td></tr>";
		echo "
      <tr><td class='area_1' style='width:35%;padding:8px;'><b>Super Administrator Account</b><br>Use Click to select one user<br>Use Ctrl+Click to select one users at a time<br>Use Shift+Click to select a row of users</td>
      <td class='area_2'>";

		$members = $tdb->query('users',"level='3'");
		echo "<select id='superad' name='superad[]' multiple='multiple' size=5>";
		foreach ($members as $member) {
			echo "<option value='".$member['id']."'>".$member['user_name']."</option>";
		}
		echo "</select>";
		//dump($members);
		echo "</td></tr>";
		echo "<tr>
			<th colspan='2'><strong>Update Register E-mail Message</strong></th>
		</tr>
		<tr>
		<td colspan='2' class='area_2'>A new option has been added to the E-mail message sent to newly registered users.<p>The new option is <b>&lt;url&gt;</b>, which will be the link users naviate to, to verify their e-mail address, instead of recieving a generated password.</b>
		</td></tr>
			<tr>
				<td class='area_1'><strong>Register Email Message</strong><br />
					This is the message for confirmation of registration (options: &lt;login&gt;, &lt;password&gt;, and &lt;url&gt;)</td>
				<td class='area_2'><textarea rows='5' name='register_msg' cols='25' tabindex='3'>".$_REGIST["register_msg"]."</textarea></td>
			</tr>";
	}
} else if($_POST['next'] == 2) {
	echo "<tr>
				<th colspan='2'><strong>Updating Database</strong></th>
			</tr>
			<tr>
			<td colspan='2' class='area_2'>";
	if(!$tdb->isTable('uploads')) {
		//For 2.2.1(a) compadibility
		$tdb->createTable("uploads", array(
		array("name", "string", 80),
		array("size", "number", 9),
		array("downloads", "number", 10),
		array("data", "memo"),
		array("id", "id")
		), 2048);
	}

	$post_tdb = new tdb(DB_DIR, 'posts');
	$tableList = $post_tdb->getTableList();
	foreach($tableList as $table) {
		// Remove the database name from the tablename
		$table = str_replace("posts_", "", $table);

		// Make sure we don't get any topic tables
		if(substr($table, -6) == "topics" || is_numeric($table)) continue;
		$post_tdb->setFp("posts", $table);
		$fields = $post_tdb->getFieldList('posts');
		if(!in_array('upload_id', $fields)) {
			$post_tdb->addField("posts", array(
				"upload_id",
				"number",
			10
			));
		}
	}
	unset($post_tdb, $tableList, $table);

	$tdb->createDatabase(DB_DIR."/", "bbcode.tdb");
	$tdb->addField('users', array('newTopicsData', 'memo'));
	$tdb->addField('users', array('lastvisit', 'number', 14));
	$tdb->addField('users', array('reg_code', 'memo'));
	$tdb->addField('uploads', array('file_loca', 'string', 80));
	$config_tdb->addField('ext_config', array('data_list', 'memo'));
	$config_tdb->addField('config', array('data_type', 'string', 7));
	print "<P>New fields added to the tables.";

	$post_tdb = new Posts(DB_DIR, 'main');
	if($post_tdb->isTable('trackforums')) $post_tdb->removeTable('trackforums');
	if($post_tdb->isTable('tracktopics')) $post_tdb->removeTable('tracktopics');

	//move lastvisit information to the member database & propogate newTopicsData
	$forums = $tdb->listRec('forums', 1, -1);
	$f_ids = array();
	foreach($forums as $forum) {
		$f_ids[] = $forum['id'];
	}
	$lastvisit_file = file_get_contents(DB_DIR . '/lastvisit.dat');
	$id = 1;
	while(strlen($lastvisit_file) > 0) {
		$lastvisit = substr($lastvisit_file, 0, 14);
		$lastvisit_file = substr($lastvisit_file, 14);
		if(FALSE === $tdb->fileIdById('users', $id)) continue;
		$newTopics = serialize(array('lastVisitForums'=> array_fill_keys($f_ids, $lastvisit)));
		$tdb->edit('users', $id, array('lastvisit' => $lastvisit, 'newTopicsData' => $newTopics));
		$id++;
	}
	echo "<P>Last Visit & new Topic information inserted.";

	//create superuser
	if(!empty($_POST['superad'])) foreach($_POST['superad'] as $id) {
		$tdb->edit('users', $id, array('level' => 9));
	}
	echo "<P>Super Admin Set.";
	$config_tdb->editVars('regist', array('register_msg' => $_POST['register_msg']));
	print '<P>Edited Register E-mail Message';
	echo "</td></tr>";
} else if($_POST['next'] == 3) {
	print '<tr><td class="area_2">';
	//Move the file OUT of the database and into the uploads directory
	$uploads = $tdb->listRec('uploads', 1, -1);
	$uploads_dir = uniqid('uploads_', true);
	if (!is_dir($uploads_dir)) {
		if (!mkdir($uploads_dir, 0777)) die('Unable to create an uploads directory.  The forum must be able to create a folder in the root forum folder.  Please chmod() the root folder to 777 and refresh the page.');
		touch($uploads_dir. '/index.html');

		// Create a no access file
		$f = fopen($uploads_dir."/.htaccess", "w");
		fwrite($f, "Order deny,allow\nDeny from all");
		fclose($f);
	}
	print "<P>Created a new uploads directory";
	if (count($uploads) > 0)
	{
		foreach($uploads as $file) {
			$file_name = md5(uniqid(rand(), true));
			$f = fopen($uploads_dir.'/'.$file_name, 'xb');
			fwrite($f, $file['data']);
			fclose($f);
			$tdb->edit('uploads', $file['id'], array('user_level' => 0, 'file_loca' => $file_name));
		}
	}
	$tdb->removeField('uploads', 'data');

	$old_upload = directory('./uploads/');
	if (!empty($old_upload)) {
		foreach ($old_upload as $file)
		unlink($_CONFIG['fileupload_location'].'/'.$file);
	}
	print "<P>Moving uploads files into the uploads directory";
	if(!@rmdir($_CONFIG['fileupload_location'])) print "<P>Unable to remove the \"./uploads\" directory";

	$config_types = $config_tdb->listRec('ext_config', 1, -1);
	foreach($config_types as $config_type) {
		$config_tdb->edit('config', $config_type['id'], array('data_type' => $config_type['data_type']));
	}

	//checks that the same number of cats are in admin_catagory_sorting and database
	//2.1.1b had a bug that didn't create a value in admin_catagory_sorting if only one category existed

	$cats = $tdb->listRec("cats",1,-1);
	$catsort = explode(',',$_CONFIG['admin_catagory_sorting']);
	if ($catsort[0] == "")
	unset($catsort[0]);

	if (count($cats) != count($catsort)) {
		foreach ($cats as $value) {
			if (!in_array($value['id'],$catsort))
			$catsort[] = $value['id']; //adds missing category id to catsort array
		}
	}

	$cat_sort = implode(",",$catsort);

	//need to pass through two stages before editing
	echo "<input type='hidden' name='cat_sort' value='$cat_sort'>";
	echo "<input type='hidden' name='uploads_dir' value='$uploads_dir'>";

	print "<P>Added 'data_type' field to the fast access config table";
} else if($_POST['next'] == 4) {
	print '<tr><td class="area_2">';
	$del_list = array('pm_version', 'avatar1', 'avatar2', 'avatar3', 'avatar4', 'avatar5', 'avatar6', 'avatar7', 'avatar8', 'avatar9', 'pm_max_outbox_msg', 'Create List', 'avatar_width', 'avatar_height', 'table_width_main');
	foreach($del_list as $string) {
		$config_tdb->delete($string);
	}
	print "<P>Deleted unneeded configVars";
	//need to pass through to next stage for editing
	echo "<input type='hidden' name='cat_sort' value='".$_POST['cat_sort']."'>";
	echo "<input type='hidden' name='uploads_dir' value='".$_POST['uploads_dir']."'>";

} else if($_POST['next'] == 5) {
	print '<tr><td class="area_2">';
	$config_tdb->renameCategory('config', 'General');
	$config_tdb->renameCategory('status', 'Members Statuses');
	$config_tdb->renameCategory('regist', 'New Members');
	//How to add more Mini Categories to the config_org.dat file
	$post_settings_id = $config_tdb->addMiniCategory('Posting Settings', 'config');
	$reg_setting_id = $config_tdb->addMiniCategory('Registration Settings', 'regist', '8');
	$avatar_setting_id = $config_tdb->addMiniCategory("Users' Avatars", 'regist', false);

	/*  Correct way to edit values in config */
	//rather useless since we have new captcha since 2.2.8
	//$config_tdb->add('security_code', '1', 'regist', 'bool', 'checkbox', $reg_setting_id, '2', 'Enable Security Code', 'Enable the CAPTCHA security code image for new user registration<br><strong>Enabling this is recommended</strong>');
	$config_tdb->add('banned_words', 'shit,fuck,cunt,pussy,bitch,arse', 'config', 'text', 'hidden', '', '', '', '');
	$config_tdb->add('email_mode', '1', 'config', 'bool', 'hidden', '', '', '', '');
	$config_tdb->add('custom_avatars', '1', 'regist', 'number', 'dropdownlist', $avatar_setting_id, '2', 'Custom Avatars', 'Allow users to link or upload their own avatars instead of choosing them locally in images/avatars/', 'a:3:{i:0;s:7:"Disable";i:1;s:4:"Link";i:2;s:6:"Upload";}');

	$config_tdb->add('disable_reg', '0', 'regist', 'bool', 'checkbox', $reg_setting_id, '1', 'Disable Registration', 'Checking this will disable public registration (deny access to register.php), and only admins will be able to add users (Add button on "Manage Members" section)');
	$config_tdb->add('reg_approval', '0', 'regist', 'bool', 'checkbox', $reg_setting_id, '3', 'Approve New Users', 'Checking this will mean after new users register, their account will be disabled until an admin approves their account via "Manage Members"');

	$config = array();
	$regist = array();
	$config[] = array('name' => 'ver', 'value' => '2.2.1');
	$config[] = array('name' => 'skin_dir', 'value' => './skins/default', 'sort' => '4');
	$config[] = array('name' => 'logo', 'value' => './skins/default/images/logo.png');
	$config[] = array('name' => 'servicemessage', 'sort' => '5');
	$config[] = array("name" => "admin_catagory_sorting", "form_object" => "hidden", "data_type" => "string","value" => $_POST['cat_sort'], 'minicat' => '', 'sort' => '');
	$config[] = array("name" => "posts_per_page", 'minicat'=>$post_settings_id,'sort'=>1);
	$config[] = array("name" => "topics_per_page", 'minicat'=>$post_settings_id,'sort'=>2);
	$config[] = array('name' => 'fileupload_location', 'value' => $_POST['uploads_dir'], "form_object" => "hidden", "data_type" => "string");
	$config[] = array('name' => 'fileupload_size', 'description' => 'In kilobytes, type in the maximum size allowed for file uploads<br><i>Note: Setting to 0 will <b>disable</b> uploads</i>', 'minicat'=>$post_settings_id,'sort'=>4);
	$config[] = array('name' => 'censor', 'minicat'=>$post_settings_id,'sort'=>5);
	$config[] = array('name' => 'sticky_note', 'minicat'=>$post_settings_id,'sort'=>6);
	$config[] = array('name' => 'sticky_after', 'minicat'=>$post_settings_id,'sort'=>7, 'data_type'=>'bool');
	$config[] = array('name' => 'newuseravatars', 'value' => '50', 'type' => 'regist', 'data_type' => 'number', 'form_object' => 'text', 'minicat' => $avatar_setting_id, 'sort' => '1', 'title' => 'New User Avatars', 'description' => 'Prevent new users from choosing their own avatars (if "Custom Avatars" is enabled), by defining a minimum post count they must have (Set to 0 to disable)');
	$regist[] = array('name' => 'register_msg', 'description' => 'This is the message for confirmation of registration.<br>(options: &lt;login&gt;, &lt;password&gt;, and &lt;url&gt;)');
	$config_tdb->editVars('config', $config, true);
	$config_tdb->editVars('regist', $regist, true);
	print "<P>Completed Modifying the extended config table";

	$tdb->tdb(DB_DIR.'/', 'bbcode.tdb');
	$tdb->createTable('smilies', array(array('id', 'id'), array('bbcode', 'memo'),array('replace','memo'),array('type','string',4)));
	$tdb->createTable('icons',array(array('id','id'),array('filename','memo')));
	$tdb->removeField('users', 'mail_list');
	$tdb->removeField('users', 'avatar_width');
	$tdb->removeField('users', 'avatar_height');
	$tdb->removeField('users', 'avatar_hash');
	$tdb->cleanUp();
	$tdb->setFp("smilies","smilies");
	$tdb->setFp("icons","icons");
	for ($i = 1;$i<22;$i++) {
		$filename = 'icon'.$i.'.gif';
		$tdb->add('icons',array("filename"=>$filename));
	}

	//type has two possible values
	//main is shown on main page, more is shown on more smilies page
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

	//MORE SMILIES -- need to add code to check for custom smilies added to more smilies folder.
	$more = directory("./smilies/moresmilies/","gif,jpg");
	if (count($more) > 0) {
		foreach ($more as $smiley) {
			$tdb->add("smilies",array("bbcode"=>"[img]".$smiley."[/img]","replace"=>"<img src='./smilies/".$smiley."' border='0' alt='".$smiley."'>","type"=>"more"));
			if (!file_exists('./smilies/'.$smiley))
			rename('./smilies/moresmilies/'.$smiley,'./smilies/'.$smiley);
		}
		$contents = directory("./smilies/moresmilies/");
		foreach ($contents as $file)
		unlink("./smilies/moresmilies/".$file);
	}

	if(is_dir('./smilies/moresmilies')) rmdir("./smilies/moresmilies");

	if (file_exists(DB_DIR.'/smilies.dat')) unlink(DB_DIR.'/smilies.dat');
	echo "<p>Smilie database created";
	echo "<p>More Smilies files converted";
} else if($_POST['next'] == 6) {
	print '<tr><td class="area_2">';
	$delete_array = array('admin_forum.php', 'admin_cat.php', 'admin_reset_stats.php', 'install-uploads.php', 'more_smilies_create_list.php', 'setallread.php', './includes/wrapper_scripts_names.txt', './includes/class/mod_avatar.class.php','./includes/board_help.php','./includes/board_post.php','./includes/board_view.php','./skins/default/coding.php','./skins/default/icons/deletetopic.gif','./skins/default/icons/head_but_pms.JPG','./skins/default/icons/closetopic.gif','./skins/default/icons/lastpost.jpg','./skins/default/icons/manage.gif','./skins/default/icons/monitor.gif','./skins/default/icons/nav.gif','./skins/default/icons/off.gif','./skins/default/icons/on.gif','./skins/default/icons/opentopic.gif','./skins/default/icons/pb_delete.JPG','./skins/default/icons/pb_edit.JPG','./skins/default/icons/pb_email.JPG','./skins/default/icons/pb_profile.JPG','./skins/default/icons/pb_quote.JPG','./skins/default/icons/pb_www.JPG','./skins/default/icons/redirect.png','./skins/default/icons/sendpm.jpg','./skins/default/icons/reply.gif','./skins/default/icons/replylocked.gif','./skins/default/icons/topic.gif','./skins/default/icons/stats.gif','./skins/default/icons/user.gif','./skins/default/images/footer_bg.JPG','./skins/default/images/footer_bg.PNG','./skins/default/images/head_but_donations.JPG','./skins/default/images/head_but_faq.JPG','./skins/default/images/head_but_loginout.JPG','./skins/default/images/head_but_members.JPG','./skins/default/images/head_but_pms.JPG','./skins/default/images/head_but_register.JPG','./skins/default/images/head_but_search.JPG','./skins/default/images/bead_but_usercp.JPG','./skins/default/images/head_logo.JPG','./skins/default/images/head_logo_right.JPG','./skins/default/images/head_top_left_bg.JPG','./skins/default/images/head_top_middle.JPG','./skins/default/images/head_top_right_bg.JPG','./skins/default/images/on.gif','./skins/default/images/sound.wav','./skins/default/images/cat_bottom_bg.jpg','./skins/default/images/cat_bottom_left.jpg','./skins/default/images/cat_bottom_left.gif','./skins/default/images/cat_bottom_right.gif','./skins/default/images/cat_bottom_right.JPG','./skins/default/images/cat_top_bg.gif','./skins/default/images/cat_top_left.gif','./skins/default/images/cat_top_right.gif','./skins/default/images/top_leftc.gif','./skins/default/images/top_rightc.gif', DB_DIR.'/constants.php');

	$icons_dir = directory("./icon/");
	foreach ($icons_dir as $icon)
	$delete_array[] = './icon/'.$icon;

	$c = count($delete_array);
	for($i=0;$i<$c;$i++) {
		if(!file_exists($delete_array[$i]) ||
		@unlink($delete_array[$i])) unset($delete_array[$i]);
	}


	print '<P>Deleted obsolete files';
	if(!empty($delete_array)) {
		print '<P><b>Unable to delete the following files<b>:<i><br>';
		print implode('<br>', $delete_array);
		print '</i><p>It is recommended to delete these files.';
	}

	if(is_dir('./icon') && @rmdir('./icon/')) print "<p>Unable to remove the \"./icon\" directory.";
} else if($_POST['next'] == $last_step) {
	?>
<center><input type="button" onclick="location.href='update2_2_2.php';"
	value='Click to Proceed to next section' name='next step'> <?php
}
if($_POST['next'] != $last_step) {
	echo "<tr>
    			<td colspan='2' class='footer_3'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
    		</tr>";
	if ($proceed === true) {
		$next = (int) $_POST['next'] + 1;

		echo "<tr>
    			<td colspan='2' class='footer_3a' style='text-align:center;'><input type='hidden' name='next' value='$next'><input type='submit' value='Next >>' name='submit'></td>
    		</tr>";
	}
	echoTableFooter(SKIN_DIR);
}
echo "</form>";
include_once('./includes/footer.php');
?>
