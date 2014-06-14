<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once("./includes/upb.initialize.php");
$where = "<a href='admin.php'>Admin</a> ".$_CONFIG["where_sep"]." <a href='admin_baduser.php'>Manage banned users</a>";
require_once("./includes/header.php");

if(isset($_COOKIE["user_env"]) && isset($_COOKIE["uniquekey_env"]) && isset($_COOKIE["power_env"]) && isset($_COOKIE["id_env"])) {
	if($tdb->is_logged_in() && $_COOKIE["power_env"] >= 3) {
		if (isset($_GET["action"])) {
			if($_GET["action"] == "edit" && $_GET["word"] != "") {
				//edit banned user
				$words = explode("\n", file_get_contents(DB_DIR."/banneduser.dat"));
				if(($index = array_search($_GET["word"], $words)) !== FALSE) {
					if(isset($_POST["newword"])) {
						echo "
	<div class='alert_confirm'>
		<div class='alert_confirm_text'>
		<strong>Editing banned user: ".$rec[0]["user_name"]."!</div><div style='padding:4px;'>";
						$words[$index] = trim($_POST["newword"]);
						$f = fopen(DB_DIR."/banneduser.dat", 'w');
						fwrite($f, implode("\n", $words));
						fclose($f);

						echo "Done!</div>
	</div>";
						redirect("admin_banuser.php", 1);
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

						echo "<form action='admin_banuser.php?action=edit&word=".((isset($_POST["word"])) ? $_POST['word'] : $_GET['word'])."' method=POST>";

						echoTableHeading("Changing banned username", $_CONFIG);

						echo "
			<tr>
				<th colspan='2'>&nbsp;</th>
			</tr>
			<tr>
				<td class='area_1' style='width:25%;padding:8px;'><strong>Change a banned username to</strong></td>
				<td class='area_2'><input type=text name=newword value='$words[$index]' size=20></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type=submit value='Edit'></td>
			</tr>";
						echoTableFooter(SKIN_DIR);
						echo "</form>";
					}
				} else {
					echo $_GET["word"]." was not found in the banned users list.";
				}
			} elseif($_GET["action"] == "delete" && $_GET["word"] != "") {
				//delete banned user
				if($_POST["verify"] == "Ok") {
					// delete the user
					echo "
	<div class='alert_confirm'>
		<div class='alert_confirm_text'>
		<strong>deleting user from ban list</div><div style='padding:4px;'>";
					$words = explode("\n", file_get_contents(DB_DIR."/banneduser.dat"));
					if(($index = array_search($_GET["word"], $words)) !== FALSE) unset($words[$index]);
					$f = fopen(DB_DIR."/banneduser.dat", 'w');
					fwrite($f, implode("\n", $words));
					fclose($f);
					echo "Done!</div>
	</div>";
					if($_POST["ref"] != "") redirect($_POST["ref"], 1);
					else redirect("admin_banuser.php", 1);
				} elseif($verify == "Cancel") {
					if($_POST["ref"] != "") redirect($_POST["ref"], 1);
					else redirect("admin_banuser.php", 1);
				} else {
					ok_cancel("admin_banuser.php?action=delete&word=".$_GET["word"], "Are you sure you want to delete <b>".$_GET["word"]."</b> from the banned users list?<input type='hidden' name='ref' value='".$_GET["ref"]."'>");
				}
			} elseif($_GET["action"] == "addnew") {
				//add new user
				if($_POST["word"] != "") {
						

					echo "
	<div class='alert_confirm'>
		<div class='alert_confirm_text'>
		<strong>Adding banned user: ".$_POST['word']."!</div><div style='padding:4px;'>";
					if(filesize(DB_DIR.'/banneduser.dat') > 0) {
						$names = explode("\n", file_get_contents(DB_DIR."/banneduser.dat"));
					} else $names = array();


					$names[] = stripslashes(trim($_POST['word']));

					$f = fopen(DB_DIR."/banneduser.dat", 'w');
					fwrite($f, implode("\n", $names));
					fclose($f);
					echo "Done!</div>
	</div>";
					if($_POST["ref"] != "") redirect($_POST["ref"], 1);
					else redirect("admin_banuser.php", 1);
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

					echo "<form action='admin_banuser.php?action=addnew' method=POST><input type='hidden' name='ref' value='".$_GET["ref"]."'>";

					echoTableHeading("Banning a member", $_CONFIG);

					echo "
			<tr>
				<th colspan='2'>&nbsp;</th>
			</tr>
			<tr>
				<td class='area_1' style='width:25%;padding:8px;'><strong>Enter user name to be banned</strong></td>
				<td class='area_2'><input type=text name='word' size=20 value='".$_GET['newword']."'></td>
			</tr>
			<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'><input type=submit value='Add to ban list'></td>
			</tr>";
					echoTableFooter(SKIN_DIR);
					echo "</form>";
				}
			}
		} else {
			$list = explode("\n", file_get_contents(DB_DIR."/banneduser.dat"));

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

			echo "
	<div id='tabstyle_2'>
		<ul>
			<li><a href='admin_banuser.php?action=addnew' title='Add a banned user?'><span>Add a banned user?</span></a></li>
		</ul>
	</div>
	<div style='clear:both;'></div>";

			echoTableHeading("Manage banned users", $_CONFIG);

			echo "
			<tr>
				<th colspan='3'>Managing your banned members.</th>
			</tr>";
			if(trim($list[0]) == "") echo "
			<tr>
				<td class='area_2' style='text-align:center;font-weight:bold;padding:12px;line-height:20px;' colspan='3'>No members banned.</td>
			</tr>";
			else {
				for($i=0;$i<count($list);$i++) {
					echo "
			<tr>
				<td class='area_1' style='width:80%'><strong>$list[$i]</strong></td>
				<td class='area_2' style='width:10%'><a href='admin_banuser.php?action=edit&word=$list[$i]'>Edit</a></td>
				<td class='area_2' style='width:10%'><a href='admin_banuser.php?action=delete&word=$list[$i]'>Delete</a></td>
			</tr>";
				}
			}
			echoTableFooter(SKIN_DIR);
		}
	} else {
		echo "
<div class='alert'><div class='alert_text'>
<strong>Access Denied!</strong></div><div style='padding:4px;'>you are not authorized to be here.</div></div>";
	}
} else {
	echo "
<div class='alert'><div class='alert_text'>
<strong>Caution!</strong></div><div style='padding:4px;'>You are not logged in!.</div></div>";
	redirect("login.php?ref=admin_basuser.php", 2);
}

require_once("./includes/footer.php");
?>
