<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2

require_once("./includes/upb.initialize.php");

$where = "<a href='admin.php'>Admin</a> ".$_CONFIG["where_sep"]." <a href='admin_smilies.php'>Manage Smilies</a>";
$bdb = new Tdb(DB_DIR.'/','bbcode.tdb');
$bdb->setFP('smilies','smilies');
if(!(isset($_COOKIE["power_env"]) && isset($_COOKIE["user_env"]) && isset($_COOKIE["uniquekey_env"]) && isset($_COOKIE["id_env"]))) MiscFunctions::redirect("login.php?ref=admin_smilies.php", 2);
if(!($tdb->is_logged_in() && $_COOKIE["power_env"] >= 3)) 
{
	MiscFunctions::exitPage('
		<div class="alert"><div class="alert_text">
		<strong>Access Denied!</strong></div><div style="padding:4px;">you are not authorized to be here.</div></div>', true);
}
require_once('./includes/header.php');

//REMOVE ALL TRACES OF $_GET['word']
if(!isset($_GET["action"])) $_GET["action"] = '';
if($_GET["action"] == "addnew") {

	$bbcodes = $bdb->query('smilies', "id>'0'",1,-1,array('bbcode'));

	foreach ($bbcodes as $value)
	$codes[] = $value['bbcode'];

	$error = $success = array();
	$x = 0;
	if (!empty($_FILES))
	{
		foreach ($_FILES['icon_file']['name'] as $key => $value)
		{
			if ($value != "")
			$x++;
			else
			{
				unset ($_FILES['icon_file']['name'][$key]);
				unset ($_FILES['icon_file']['type'][$key]);
				unset ($_FILES['icon_file']['tmp_name'][$key]);
				unset ($_FILES['icon_file']['error'][$key]);
				unset ($_FILES['icon_file']['size'][$key]);
			}
		}
	}

	if($x != 0)
	{
		foreach ($_FILES['icon_file']['name'] as $key => $value)
		{
			$allowed_types = array('image/gif', 'image/jpeg', 'image/png');
			if (!in_array($_FILES["icon_file"]["type"][$key],$allowed_types))
			$error[$key]['type'] .= "File must be a gif, jpg or png file<br />File uploaded was of type: ".$_FILES["icon_file"]["type"][$key]."<br />";

			if ($_FILES['icon_file']['size'][$key] > 51200 or ($_FILES["icon_file"]["error"][$key] > 0 and $_FILES["icon_file"]["error"][$key] < 3))
			$error[$key]['size'] .= "File size must be under 50KB<br />";

			if ($_FILES["icon_file"]["error"][$key] > 2)
			$error[$key]['unknown'] .= "Upload Error: " . $_FILES["icon_file"]["error"] . "<br />";

			if (in_array($_POST['bbcode'][$key],$codes))
			$error[$key]['bbcode'] .= "A smilie already exists for the text you entered to be replaced<br />";

			if ($_POST['bbcode'][$key] == "")
			$error[$key]['empty'] .= "No bbcode was entered<br />";

			if (!empty($error[$key]))
			$error[$key]['name'] = $_FILES["icon_file"]["name"][$key];
		}

		foreach ($_FILES['icon_file']['name'] as $key => $value)
		{
			if (empty($error[$key]))
			{
				$upload_dir = "./smilies/";
				$upload_filename = $upload_dir.basename($_FILES['icon_file']['name'][$key]);

				if (!file_exists($upload_filename))
				{
					if (@move_uploaded_file($_FILES['icon_file']['tmp_name'][$key], $upload_filename))
					{
						$alt = $_POST['bbcode'][$key];
						if (strlen($_POST['bbcode'][$key]) > 10)
						$alt = basename($_FILES['icon_file']['name'][$key]);

						$replace = "<img src='./smilies/".basename($_FILES['icon_file']['name'][$key])."' border='0' alt='$alt'>";
						$array = array('bbcode'=>$_POST['bbcode'][$key],'replace'=>$replace,'type'=>$_POST['type'][$key]);
						$bdb->add('smilies',$array);
						$success[$key]['name'] = $_FILES['icon_file']['name'][$key];
					}
					else
					$error[$key]['move'] = "Smilie unable to be saved in the smilies directory<br />";
				}
				else
				$error[$key]['exists'] = "Smilie already exists. Please delete it before uploading a new one";
			}

			if (!empty($error[$key]))
			$error[$key]['name'] = $_FILES['icon_file']['name'][$key];
		}

		foreach ($success as $key => $value)
		{
			echo "<div class='alert_confirm'>
					<div class='alert_confirm_text'>
					<strong>Smilie Upload Successful</strong></div>
          <div style='padding:4px;'>";
			echo $success[$key]['name']." has been uploaded and is available for use.<br>";
			echo "</div></div>";

			if (count($success) == count($_FILES["icon_file"]["name"]))
			MiscFunctions::redirect("admin_smilies.php", 2);
		}

		if (!empty($error))
		{
			echo "<div class='alert'>";
			$permission = false; //toggle for whether to show folder permission message
			foreach ($error as $key => $value)
			{
				//print out error messages
				echo "<div class='alert_text'>Errors for ".$value['name']."</div>";
				foreach ($value as $err_key => $msg)
				{
					if ($err_key != "name")
					echo $msg;
					if ($err_key == "move")
					//set toggle to true if there is a problem moving from temp folder to icon folder
					$permission = true;
				}
			}

			if ($permission === true)
			echo "An error has occurred moving one or more icons to the smilies folder.<br>Please check the permissions for this folder. They should be set to 755 or 777";
			echo "<p><a href='admin_smilies.php?action=addnew'>Back to upload form</a></div>";
		}
	} else {
		echo "<form action='admin_smilies.php?action=addnew' name='icon_upload' method='POST' enctype='multipart/form-data'>";
		echo "<input type='hidden' name='MAX_FILE_SIZE' value='250000' />";
		MiscFunctions::echoTableHeading("Add new smilie(s)", $_CONFIG);
		echo "<tr><th colspan='6'>Smilie File Requirements</th>";
		echo "<tr><td class='area_2' style='padding:8px;' colspan='6'>Smiles must be gif/jpg or png files and have a maximum filesize of 50KB each</td></tr>";
		echo "
			<tr>
				<th colspan='6'>&nbsp;</th>
			</tr>
      <tr>
				<td class='area_1'><strong>Smilie File</strong></td>
				<td class='area_2'><input type='file' name='icon_file[]'></td>
				<td class='area_1'><strong>Text Replaced</strong></td>
				<td class='area_2'><input type='text' name='bbcode[]'></td>
				<td class='area_1'><strong>Smilie Type</strong></td>
				<td class='area_2'><input type='radio' name='type[0]' value='main'>Main <input type='radio' name='type[0]' value='more' checked>More</td>
			</tr>
      <tr>
				<td class='area_1'><strong>Smilie File</strong></td>
				<td class='area_2'><input type='file' name='icon_file[]'></td>
				<td class='area_1'><strong>Text Replaced</strong></td>
				<td class='area_2'><input type='text' name='bbcode[]'></td>
				<td class='area_1'><strong>Smilie Type</strong></td>
				<td class='area_2'><input type='radio' name='type[1]' value='main'>Main <input type='radio' name='type[1]' value='more' checked>More</td>
			</tr>
      <tr>
				<td class='area_1'><strong>Smilie File</strong></td>
				<td class='area_2'><input type='file' name='icon_file[]'></td>
				<td class='area_1'><strong>Text Replaced</strong></td>
				<td class='area_2'><input type='text' name='bbcode[]'></td>
				<td class='area_1'><strong>Smilie Type</strong></td>
				<td class='area_2'><input type='radio' name='type[2]' value='main'>Main <input type='radio' name='type[2]' value='more' checked>More</td>
			</tr>
      <tr>
				<td class='area_1'><strong>Smilie File</strong></td>
				<td class='area_2'><input type='file' name='icon_file[]'></td>
				<td class='area_1'><strong>Text Replaced</strong></td>
				<td class='area_2'><input type='text' name='bbcode[]'></td>
				<td class='area_1'><strong>Smilie Type</strong></td>
				<td class='area_2'><input type='radio' name='type[3]' value='main'>Main <input type='radio' name='type[3]' value='more' checked>More</td>
			</tr>
      <tr>
				<td class='area_1'><strong>Smilie File</strong></td>
				<td class='area_2'><input type='file' name='icon_file[]'></td>
				<td class='area_1'><strong>Text Replaced</strong></td>
				<td class='area_2'><input type='text' name='bbcode[]'></td>
				<td class='area_1'><strong>Smilie Type</strong></td>
				<td class='area_2'><input type='radio' name='type[4]' value='main'>Main <input type='radio' name='type[4]' value='more' checked>More</td>
			</tr>
      <tr>
				<td class='footer_3' colspan='6'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='6' style='text-align:center;'><input type=submit value='Add Smilie(s)'></td></tr>";
		MiscFunctions::echoTableFooter(SKIN_DIR);
		echo "</form>";
	}
}
elseif($_GET["action"] == "edit")
{

	$tmp = $tmp2 = $tmp3 = array();

	//process the data for each id to get an array of values for each id
	foreach ($_POST as $key=>$value)
	{
		$tmp_key = explode("_",$key);
		if ($tmp_key[1] != "delete")
		$tmp[$tmp_key[0]][$tmp_key[1]] = $value;

		if ($tmp_key[1] == "delete")
		{
			unset($tmp[$tmp_key[0]]);
			$data = $bdb->query('smilies', "id='{$tmp_key[0]}'", 1, 1, array('replace'));
			$file = $data[0]['replace'];
			$newfile = MiscFunctions::strmstr(MiscFunctions::strstr_after($file, "'"),"'",true);
			@unlink('./'.$newfile);
			$bdb->delete('smilies',$tmp_key[0]);
		}
	}

	foreach ($tmp as $key=>$value)
	{
		$result = $bdb->basicQuery("smilies","id",$key);
		unset($result[0]['replace']);
		//$tmp2[$result[0]['id']] = array('bbcode'=>$result[0]['bbcode'],'type'=>$result[0]['type']);
		$tmp2 = array('bbcode'=>$result[0]['bbcode'],'type'=>$result[0]['type']);
		$diff = array_diff_assoc($value,$tmp2);

		if (count($diff) == 0)
		continue;
		else
		$bdb->edit('smilies',$key,$diff);
	}
	echo "<div class='alert_confirm'>
					<div class='alert_confirm_text'>
					<strong>Smilie Database Edit Successful</strong></div>
          <div style='padding:4px;'>Returning to Smilie Management</div></div>";
	MiscFunctions::redirect('./admin_smilies.php',2);
	require_once('./includes/footer.php');
	//MiscFunctions::redirect("admin_smilies.php", 3);
}
else {
	MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
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
	MiscFunctions::echoTableFooter(SKIN_DIR);

	$smilies = $bdb->query('smilies',"id>'0'");
	//var_dump($smilies);


	echo "<a name='skip_nav'>&nbsp;</a>
				<div id='tabstyle_2'>
				<ul>
				<li><a href='admin_smilies.php?action=addnew' title='Add new smilie(s)?'><span>Add new smilie(s)?</span></a></li>
				</ul>
				</div>
				<div style='clear:both;'></div>";
	MiscFunctions::echoTableHeading("Smilie Control", $_CONFIG);
	echo "<tr><th colspan='4'>Smilie Management</th>";
	if(count($smilies) == 0 or $smilies === false) {
		echo "<tr><td bgcolor='$table1' colspan='4'><font size='$font_m' face='$font_face' color='$font_color_main'>No smilies found.</font></td></tr>";
	} else {
		echo "<tr><td class='area_2' style='padding:8px;' colspan='4'>
    <ul><li>Select <strong>Main</strong> to display the smilie in the box below the message box<li>Select <strong>More</strong> to show the smilie on the <strong>More Smilies</strong> page</ul></td></tr>";
		echo "<form name='smilieupdate' method='POST' action='admin_smilies.php?action=edit'>";
		echo "
			<tr>
				<th style='width:40%;'>Smilie</th>
				<th style='width:35%;text-align:center;'>Text Replaced</th>
				<th style='width:15%;text-align:center;'>Display Type</th>
				<th style='width:10%;text-align:center;'>Delete?</th>
			</tr>";

		$types = array('main','more');
		foreach ($smilies as $smiley)
		{
			$id = $smiley['id'];
			echo "<tr><td class='area_2' style='padding:8px;text-align:center;'>".$smiley['replace']."</td>\n";
			echo "<td class='area_1' style='padding:8px;text-align:center;'><input type='text' size='40' name='{$id}_bbcode' value='".$smiley['bbcode']."'></font></td>\n";
			echo "<td class='area_2' style='padding:8px;text-align:center;'>";
			//echo $smiley['type'];
			echo "<input type='radio' name='{$id}_type' value='main'";
			if ($smiley['type'] == "main")
			echo " checked";
			echo ">Main";
			echo "<input type='radio' name='{$id}_type' value='more'";
			if ($smiley['type'] == "more")
			echo " checked";
			echo ">More";
			/*echo "<select name='{$id}_type' size='1'>";
			 foreach ($types as $type)
			 {
			 echo "<option value='$type'";
			 if ($type == $smiley['type'])
			 {
			 echo " selected ";
			 }
			 echo ">".ucwords($type)."</option>";
			 }
			 echo "</select>";*/
			echo "</td>\n";
			echo "<td class='area_1' style='padding:8px;text-align:center;'><input type='checkbox' name='{$id}_delete'>";
			echo "</tr>\n";
		}
		echo "<tr><td class='area_1' colspan='4' style='padding:8px;text-align:center;'><input type='submit' value='Submit Changes'><input type='reset' value='Reset Form'></td></tr>";
	}
	echo "</table>";
	MiscFunctions::echoTableFooter(SKIN_DIR);
}
require_once("./includes/footer.php");
?>
