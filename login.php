<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
// Ultimate PHP Board Login
require_once("./includes/upb.initialize.php");
$where = "Login";
$show = 0;
$e = 0;
if (isset($_POST["u_name"]) && isset($_POST["u_pass"])) {
	// Attempt to login
	if (($r = $tdb->login_user($_POST["u_name"], $_POST["u_pass"], $key, $error)) === FALSE) {
		$error = "
		<div class='alert'>
			<div class='alert_text'>
				<strong>Access Denied!</strong></div><div style='padding:4px;'>{$error}</div>
		</div><br />";
	} else {
		if (empty($r['lastvisit']))$r['lastvisit'] = mkdate();

		if (headers_sent()) $error_msg = 'Could not login: headers sent.';
		else {
			setcookie("lastvisit", $r['lastvisit']);
			if($r['level'] >= 3) {
				if($_REGIST['reg_approval']) {
					$reg_approvals = $tdb->query('users', "reg_code?'reg_'", 1, -1);
					$_SESSION['reg_approval_count'] = ((!empty($reg_approvals[0])) ? count($reg_approvals) : 0);
					$_SESSION['reg_approval_lastcheck'] = mktime(); //Use only if reg_approval_count == 0
				} else {
					$_SESSION['reg_approval_count'] = 0;
					$_SESSION['reg_approval_lastcheck'] = 0;
				}
			}
			//end lastvisit info
			$_SESSION['newTopics'] = unserialize($r['newTopicsData']);
			$r['uniquekey'] = generateUniqueKey();
			$tdb->edit('users', $r['id'], array('uniquekey' => $r['uniquekey']));
			if ($_POST["remember"] == "YES") {
				setcookie("remember", '1', (time() + (60 * 60 * 24 * 7)));
				setcookie("user_env", $r["user_name"], (time() + (60 * 60 * 24 * 7)));
				setcookie("uniquekey_env", $r["uniquekey"], (time() + (60 * 60 * 24 * 7)));
				setcookie("power_env", $r["level"], (time() + (60 * 60 * 24 * 7)));
				setcookie("id_env", $r["id"], (time() + (60 * 60 * 24 * 7)));
			} else {
				setcookie("remember", '');
				setcookie("user_env", $r["user_name"]);
				setcookie("uniquekey_env", $r["uniquekey"]);
				setcookie("power_env", $r["level"]);
				setcookie("id_env", $r["id"]);
			}
			setcookie("timezone", $r["timezone"], (time() + (60 * 60 * 24 * 7)));
			if ($_GET["ref"] == "") $_GET["ref"] = "index.php";
			$error = "
					<div class='alert_confirm'>
					<div class='alert_confirm_text'>
					<strong>Redirecting:</strong></div><div style='padding:4px;'>Logged on successfully as user:
					<br />
					".$r["user_name"]."
					</div>
					</div>
					<meta http-equiv='refresh' content='2;URL=".urldecode($_GET["ref"])."'>";
		}
		$e = 1;
	}
}
require_once('./includes/header.php');
if (!$tdb->is_logged_in() != "") {
	if (isset($error)) {
		echo "$error";
		if ($e == 1) exitPage("");
	}
	if ($_COOKIE["remember"] != "") $remember = "checked";
	else $remember = "";
	echo "
	<form action='login.php?ref=".urlencode($_GET["ref"])."' method='post'>";
	echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
			<tr>
				<td class='area_1' style='width:40%;text-align:right;'><strong>User Name:</strong></td>
				<td class='area_2'><input class='txtBox' type='text' name='u_name' size='30' value='".$_POST["u_name"]."' /></td>
			</tr>
			<tr>
				<td class='area_1' style='text-align:right;'><strong>Password:</strong></td>
				<td class='area_2'><input class='txtBox' type='password' name='u_pass' size='30' /></td>
			</tr>
			<tr>
				<td class='area_1' style='text-align:right;'>&nbsp;</td>
				<td class='area_2'><input type='checkbox' name='remember' value='YES' id='rememberme' ".$remember." /><label for='rememberme'>&nbsp;&nbsp;Remember me?</label></td>
			</tr>
			<tr>
				<td class='footer_3a' style='text-align:center;' colspan='2'><input type='submit' class='txtBox' value='Login' />&nbsp;&nbsp;&nbsp;<a href='getpass.php'>(Lost Password?)</a>";
	if($_REGIST['disable_reg']) print '';
	else print " <a href='register.php'>(Need to Register?)</a>";
	print "</td>
			</tr>";
	echoTableFooter(SKIN_DIR);
	echo "</form>";
} else {
	echo "
		<div class='alert'>
			<div class='alert_text'>
				<strong>You are already logged in:</strong></div><div style='padding:4px;'><a href='logoff.php'>Log off</a></div>
		</div>";
}
require_once('./includes/footer.php');
?>
