<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2

require_once("./includes/upb.initialize.php");

$where = "<a href='admin.php'>Admin</a> ".$_CONFIG["where_sep"]." <a href='admin_icons.php'>Manage Post Icons</a>";
require_once('./includes/header.php');
$bdb = new Tdb(DB_DIR.'/','bbcode.tdb');
$bdb->setFP('icons','icons');

if(!(isset($_COOKIE["user_env"]) && isset($_COOKIE["uniquekey_env"]) && isset($_COOKIE["power_env"]) && isset($_COOKIE["id_env"]))) {
	echo "you are not even logged in";
	redirect("login.php?ref=admin_smilies.php", 2);
}

if(!($tdb->is_logged_in() && $_COOKIE["power_env"] >= 3)) 
{
	exitPage("<div class='alert'><div class='alert_text'>
		<strong>Access Denied!</strong></div><div style='padding:4px;'>you are not authorized to be here.</div></div>");
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
//REMOVE ALL TRACES OF $_GET['word']

		
if($_GET["action"] == "addnew")
{
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
		//this first section will be the same for smilies
		foreach ($_FILES['icon_file']['name'] as $key => $value)
		{
			if ($_FILES["icon_file"]["type"][$key] != "image/gif")
			$error[$key]['type'] .= "File must be a gif file ".$_FILES["icon_file"]["type"][$key]."<br />";

			if ($_FILES['icon_file']['size'][$key] > 3072 or ($_FILES["icon_file"]["error"][$key] > 0 and $_FILES["icon_file"]["error"][$key] < 3))
			$error[$key]['size'] .= "File size must be under 3KB<br />";

			if ($_FILES["icon_file"]["error"][$key] > 2)
			$error[$key]['unknown'] .= "Upload Error: " . $_FILES["icon_file"]["error"] . "<br />";

			if (!empty($error[$key]))
			$error[$key]['name'] = $_FILES["icon_file"]["name"][$key];
		}

		foreach ($_FILES['icon_file']['name'] as $key => $value)
		{
			if (empty($error[$key]))
			{
				$upload_dir = SKIN_DIR."/icons/post_icons/";
				$upload_filename = $upload_dir.basename($_FILES['icon_file']['name'][$key]);

				if (!file_exists($upload_filename))
				{
					if (@move_uploaded_file($_FILES['icon_file']['tmp_name'][$key], $upload_filename))
					{
						$bdb->add('icons',array("filename"=>$_FILES['icon_file']['name'][$key]));
						$success[$key]['name'] = $_FILES['icon_file']['name'][$key];
					}
					else
					$error[$key]['move'] = "Icon unable to be saved in the icon directory<br />";
				}
				else
				$error[$key]['exists'] = "Icon already exists. Please delete it before uploading a new one";
			}

			if (!empty($error[$key]))
			$error[$key]['name'] = $_FILES['icon_file']['name'][$key];
		}

		foreach ($success as $key => $value)
		{
			echo "<div class='alert_confirm'>
					<div class='alert_confirm_text'>
					<strong>Post Icon(s) Upload Successful</strong></div>
          <div style='padding:4px;'>";
			echo $success[$key]['name']." has been uploaded and is available for use.<br>";
			echo "</div></div>";

			if (count($success) == count($_FILES["icon_file"]["name"]))
			redirect("admin_icons.php", 2);
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
			echo "An error has occurred moving one or more icons to the icon folder.<br>Please check the permissions for this folder. They should be set to 755 or 777";
			echo "<p><a href='admin_icons.php?action=addnew'>Back to upload form</a></div></div>";
		}
	}
	else
	{
		echo "<form action='admin_icons.php?action=addnew' name='icon_upload' method='POST' enctype='multipart/form-data'>";
		echo "<input type='hidden' name='MAX_FILE_SIZE' value='15500' />";
		echoTableHeading("Add new post icon(s)", $_CONFIG);
		echo "<tr><th colspan='2'>Post Icon File Requirements</th>";
		echo "<tr><td class='area_2' style='padding:8px;' colspan='2'>Post Icons must be gif files and have a maximum filesize of 3KB each</td></tr>";
		echo "
			<tr>
				<th colspan='2'>&nbsp;</th>
			</tr>
			<tr>
				<td class='area_1' style='width:20%'><strong>Post Icon File</strong></td>
				<td class='area_2'><input type='file' name='icon_file[]'></td>
			</tr>
			<tr>
				<td class='area_1' style='width:20%'><strong>Post Icon File</strong></td>
				<td class='area_2'><input type='file' name='icon_file[]'></td>
			</tr>
			<tr>
				<td class='area_1' style='width:20%'><strong>Post Icon File</strong></td>
				<td class='area_2'><input type='file' name='icon_file[]'></td>
			</tr>
			<tr>
				<td class='area_1' style='width:20%'><strong>Post Icon File</strong></td>
				<td class='area_2'><input type='file' name='icon_file[]'></td>
			</tr>
			<tr>
				<td class='area_1' style='width:20%'><strong>Post Icon File</strong></td>
				<td class='area_2'><input type='file' name='icon_file[]'></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type=submit value='Add Post Icon(s)'></td>
			</tr>";
		echoTableFooter(SKIN_DIR);
		echo "
	</form>";
	}
}
else if ($_GET['action'] == 'delete')
{
	$delete_array = array();
	foreach ($_POST as $value)
	{
		$delete_array[] = $value;
	}

	foreach ($delete_array as $value)
	{
		$result = $bdb->basicQuery('icons','id',$value);

		$icon_file = $result[0]['filename'];
		if (@file_exists(SKIN_DIR."/icons/post_icons/".$icon_file))
		{
			if (@unlink(SKIN_DIR."/icons/post_icons/".$icon_file))
			{
				$bdb->delete('icons',$value);
				echo "<div class='alert_confirm'>
					<div class='alert_confirm_text'>
					<strong>Post Icon Deletion Successful</strong></div>
          <div style='padding:4px;'>The Post Icon(s) has been deleted.
					</div>
					</div>";
				redirect("admin_icons.php", 2);
			}
			else
			{
				echo "<div class='alert'>
			<div class='alert_text'>
			<strong>Post Icon Deletion Error</strong></div><div style='padding:4px;'>There was a problem deleting the icon(s).<br>Please check the permissions for the 'icon' directory. It should be 777<p><a href='admin_icons.php'>Back to post icons</a></div>
			</div>";
			}
		}
		else
		{
			echo "<div class='alert'>
			<div class='alert_text'>
			<strong>Post Icon Deletion Error</strong></div><div style='padding:4px;'>The file for the icon could not be found.<p>The database entry for this icon has been removed.<p><a href='admin_icons.php'>Back to post icons</a></div>
			</div>";
			$bdb->delete('icons',$value);
		}
	}
}
else {
	$icons = $bdb->query('icons',"id>'0'");
	//var_dump($smilies);


	echo "
				<div id='tabstyle_2'>
				<ul>
				<li><a href='admin_icons.php?action=addnew' title='Add a new post icon?'><span>Add new post icon(s)?</span></a></li>
				</ul>
				</div>
				<div style='clear:both;'></div>";
	echoTableHeading("Post Icon Management", $_CONFIG);
	echo "<tr><th colspan='4'>Post Icon Management</th>";
	echo "<tr><td class='area_2' style='padding:8px;' colspan='4'>
    There must always be at least one post icon.</td></tr>";
	echo "<form name='iconupdate' method='POST' action='admin_icons.php?action=delete'>";
	echo "
			<tr>
				<th style='width:25%;'>Post Icon</th>
				<th style='width:25%;text-align:center;'>Delete?</th>
				<th style='width:25%;text-align:center;'>Post Icon</th>
				<th style='width:25%;text-align:center;'>Delete?</th>
			</tr>";

	echo "<tr>";
	foreach ($icons as $key => $icon)
	{
		$id = $icon['id'];
		echo "<td class='area_2' style='padding:8px;text-align:center;'><img src='".SKIN_DIR."/icons/post_icons/".$icon['filename']."' /><br>".$icon['filename']."</td>\n";
		echo "<td class='area_1' style='padding:8px;text-align:center;'>";
		if (count($icons) > 1)
		echo "<input type='checkbox' name='{$id}_delete' value='$id'>";
		echo "</td>\n";

		if (($key+1)%2 == 0)
		echo "</tr><tr>";
	}
	if (count($icons)%2 != 0)
	{
		echo "<td class='area_2' style='padding:8px;text-align:center;'></td>\n";
		echo "<td class='area_1' style='padding:8px;text-align:center;'></td>\n";
	}
	echo "</tr>\n";
	echo "<tr><td class='area_1' colspan='4' style='padding:8px;text-align:center;'><input type='submit' value='Submit Changes'><input type='reset' value='Reset Form'></td></tr>";

	echo "</table>";
	echoTableFooter(SKIN_DIR);
}
require_once("./includes/footer.php");
?>
