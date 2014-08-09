<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2

require_once("./includes/upb.initialize.php");

$where = "<a href='admin.php'>Admin</a> ".$_CONFIG["where_sep"]." <a href='admin_config.php'>Config Settings</a>";
require_once('./includes/header.php');
if(!isset($_GET['action']) || $_GET['action'] == '') $_GET['action'] = 'config';

if(isset($_COOKIE["power_env"]) && isset($_COOKIE["user_env"]) && isset($_COOKIE["uniquekey_env"]) && isset($_COOKIE["id_env"])) {
	if($tdb->is_logged_in() && $_COOKIE["power_env"] >= 3) {
		if(isset($_POST['action']) && $_POST["action"] != "") {
			if(file_exists('./includes/admin/'.$_POST['action'].'.config.php')) include('./includes/admin/'.$_POST['action'].'.config.php');
			if($result = $config_tdb->editVars($_POST["action"], $_POST))
			{
				echo "
	<div class='alert_confirm'>
		<div class='alert_confirm_text'>
		<strong>Redirecting:</div><div style='padding:4px;'>
		Successfully edited.
		</div>
	</div>";

			}
			else echo "
<div class='alert'><div class='alert_text'>
<strong>Error!</strong></div><div style='padding:4px;'>Edit Failed.</div></div>";
			require_once("./includes/footer.php");
			MiscFunctions::redirect($PHP_SELF."?action=".$_POST["action"], 2);
			die();
		}

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

		echo "<a name='skip_nav'>&nbsp;</a>
			<div id='tabstyle_2'>
			    <ul>";

		$cats = $config_tdb->fetchCategories();
		while(list($type, $title) = each($cats)) {
			print "
			        <li><a href='admin_config.php?action=".$type."#skip_nav' title='".$title."'><span>".$title."</span></a></li>";
		}
		echo "    </ul>
			</div>
			<div style='clear:both;'></div>";

		$minicats = $config_tdb->fetchMiniCategories($_GET['action']);
		$configVars = $config_tdb->getVars($_GET["action"], true);
		//MiscFunctions::dump($configVars);
		echo "<form action=\"admin_config.php?action=".$_GET["action"]."\" method='POST' name='form'><input type='hidden' name='action' value='".$_GET["action"]."'>";

		MiscFunctions::echoTableHeading("&nbsp;", $_CONFIG);
		while(list($minicat_id, $minicat_title) = each($minicats)) {
			echo "
		<tr>
			<th colspan='2'>{$minicat_title}</th>
		</tr>";
			for($i=0, $j=1, $max=count($configVars);$j<$max;$i++) {
				if($i>=$max) { $j++; $i=0; }//Current Sorting Rec not found after cycling through all available recs, skipping on to find the next sorting rec
				if($configVars[$i]["minicat"] == $minicat_id && $configVars[$i]["sort"] == $j && $configVars[$i]["form_object"] != "hidden") {
					echo "
		<tr>
			<td class='area_1' style='width:35%;padding:8px;'><strong>".$configVars[$i]["title"]."</strong>";
					if($configVars[$i]["description"] != "") echo "<br />".stripslashes($configVars[$i]["description"]);
					echo "</td>
			<td class='area_2'>";

					switch($configVars[$i]["form_object"]) {
						default:
						case "text":
							echo "<input type=\"text\" name=\"".$configVars[$i]["name"]."\" value=\"".stripslashes($configVars[$i]["value"])."\" size='40'>";
							break 1;
						case "password":
							echo "<input type=\"password\" name=\"".$configVars[$i]["name"]."\" value=\"".$configVars[$i]["value"]."\" size='40'>";
							break 1;
						case "checkbox": //checkbox won't send an empty value, so we use a hidden field and modify it with javascript
							if((bool) $configVars[$i]["value"]) $checked = " checked";
							else $checked = "";
							echo "<input type=\"checkbox\" name=\"".$configVars[$i]["name"]."_checkbox\" onChange=\"changeCheckboxValue(this.checked, document.form.".$configVars[$i]["name"].")\"size='40'".$checked.">";
							echo "<input type=\"hidden\" name=\"".$configVars[$i]["name"]."\" value=\"".(($configVars[$i]["value"]) ? '1':'0')."\">";
							break 1;
						case "textarea":
							echo "<textarea cols=50 rows=10 name=\"".$configVars[$i]["name"]."\">".stripslashes($configVars[$i]["value"])."</textarea>";
							break 1;
						case "link":
						case "url":
						case "URL":
							if($configVars[$i]["data_type"] != "") $target = " target=\"".$configVars[$i]["data_type"]."\"";
							else $target = "";
							echo "<a href=\"".$configVars[$i]["value"]."\"".$target.">".$configVars[$i]["name"]."</a>";
							break 1;
						case "dropdownlist":
						case "dropdown":
						case "list":
							if(FALSE !== ($arr = unserialize($configVars[$i]['data_list']))) {
								print "<select name=\"{$configVars[$i]['name']}\">\n";
								$glb_var = '_'.strtoupper($_GET['action']);
								$glb_var =& $$glb_var;
								while(list($val, $text) = each($arr)) {
									if(preg_match("/^optgroup\d*$/i", $val)) print "<optgroup label=\"$text\">";
									else print "<option value=\"$val\"".(($glb_var[$configVars[$i]['name']] == $val) ? ' SELECTED':'').">$text</option>";
								}
							} else print "<i>Unable to display dropdown list</i>";
							break 1;
						case "skin":
							$skins = array();
							if (is_dir('./skins')) {
								if ($dh = opendir('./skins')) {
									while (($file = readdir($dh)) !== false) {
										if (substr($file,0,1) != "." and is_dir('./skins/'.$file))
										$skins[] = $file;
									}
									closedir($dh);
								}
							}
							echo "<select name=\"{$configVars[$i]['name']}\">\n";
							foreach ($skins as $skin)
							{
								$selected = "";
								$value = "./skins/".$skin;
								if ($value == $configVars[$i]["value"])
								$selected = "selected";
								echo "<option value=\"./skins/$skin\" $selected>".stripslashes($skin)."</option>";
							}
							echo "</select>";
							break 1;

						case "hidden":
							break 1;
					}
					echo "</td>
	  </tr>";
					$i = -1;
					$j++;
				}
			}
		}
		echo "		<tr>
				<td class='footer_3' colspan='2'><img src='./skins/default/images/spacer.gif' alt='' title='' /></td>
			</tr>";
		echo "
			<tr>
				<td class='footer_3a' colspan='2' style='text-align:center;'>";

		echo "<input type=submit value='Submit'>";
		echo "</td>
			</tr>";
		MiscFunctions::echoTableFooter(SKIN_DIR);
		echo "</form>";

		/*
		 print '<pre>'; print_r($configVars);
		 $all_config = $config_tdb->query("config", "type='".$_GET['action']."'");
		 echo '

		 <b>Basic</b>:

		 ';
		 print_r($all_config);
		 $all_config = $config_tdb->query("ext_config", "type='".$_GET['action']."'");
		 echo '

		 <b>Extensive</b>:

		 ';
		 print_r($all_config);
		 $all_config = $config_tdb->listRec("ext_config", 1, -1);
		 echo '

		 <b>All Ext</b>:

		 ';
		 print_r($all_config);
		 print '</pre>';
		 */
	} else {
		echo "
<div class='alert'><div class='alert_text'>
<strong>Access Denied!</strong></div><div style='padding:4px;'>you are not authorized to be here.</div></div>";
	}
} else {
	echo "
<div class='alert'><div class='alert_text'>
<strong>Access Denied!</strong></div><div style='padding:4px;'>you are not logged in.</div></div>
<meta http-equiv='refresh' content='2;URL=login.php?ref=admin.php'>";
}
require_once("./includes/footer.php");
?>
