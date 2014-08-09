<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
if (!isset($_GET["ref"])) $ref = "index.php";
else $ref = $_GET["ref"];
require_once("./includes/upb.initialize.php");
$where = "Lost Password";
$e = false;
if (isset($_POST["u_name"])) {
	$user = $tdb->query("users", "user_name='".$_POST['u_name']."'", 1, 1);
	if ($user[0]['id'] != '') {
		$results = $tdb->basicQuery("getpass", "user_id", $user[0]['id'], 1, 1);
		if ($results[0]['id'] != '') {
			$expire = DateCustom::alterDate($results[0]['time'], 2, 'days');
			if (DateCustom::mkdate() > $expire) {
				$tdb->delete('getpass', $results[0]['id']);
				unset($results);
			}
		}
		if ($results[0]['id'] == '') {
			$passcode = rand();
			$request_ID = $tdb->add("getpass", array("passcode_HASH" => Encode::generateHash($passcode), time => DateCustom::mkdate(), "user_id" => $user[0]['id']));
			if (FALSE !== ($question_mark_where = strpos($_SERVER['REQUEST_URI'], '?'))) {
				$url = substr($_SERVER['REQUEST_URI'], 0, $question_mark_where);
			}
			else $url = $_SERVER['REQUEST_URI'];
			mail($user[0]["email"], "Lost Password Confirmation", "The IP Address: ".$_SERVER['REMOTE_ADDR']." has requested a password retrieval from an account linked to this e-mail address.  If you did request this, visit here to confirm that you would like to change your password for ".$user[0]["user_name"]."\n\nhttp://".$_SERVER['HTTP_HOST'].$url."?request_ID=".$request_ID."&passcode=".$passcode."\n\nBut you did not request a Password Retrieval, please alert an administrator, and give them the IP Address provided.", "From: ".$_REGIST['admin_email']);
			$error = "A confirmation e-mail has been sent to the e-mail address attached to the username.";
			$e = true;
		}
		else $error = "Unable to send: A confirmation e-mail has already been sent to the e-mail address attched to the username with in the last 48 hours.";
	}
	else $error = "Unable to find the specified username";
}
if (isset($_POST['passcode']) && isset($_POST['request_ID'])) {
	$results = $tdb->get('getpass', $_POST['request_ID']);
	$passcode_HASH = Encode::generateHash($_POST['passcode'], $results[0]['passcode_HASH']);
	if ($passcode_HASH == $results[0]['passcode_HASH']) {
		if ($_POST['pass1'] != $_POST['pass2']) {
			$_GET['passcode'] = $_POST['passcode'];
			$_GET['request_ID'] = $_POST['request_ID'];
			$error = "Passwords do not match";
		} else {
			$tdb->edit('users', $results[0]['user_id'], array("password" => Encode::generateHash($_POST['pass1'])));
			$tdb->delete('getpass', $_POST['request_ID']);
			$where = "Lost Password ".$_CONFIG["where_sep"]." Set New";
			require_once('includes/header.php');
			echo "Your password was successfully changed";
			require_once("includes/footer.php");
			MiscFunctions::redirect('login.php', 2);
			exit;
		}
	} else {
		$error = "Unable to confirm: Unvalid Passcode";
		$e = true;
	}
}
if (isset($_GET['passcode']) && isset($_GET['request_ID'])) {
	$_GET['passcode'] = trim($_GET['passcode']);
	$results = $tdb->get('getpass', $_GET['request_ID']);
	$expire = DateCustom::alterDate($results[0]['time'], 2, 'days');
	if (DateCustom::mkdate() < $expire) {
		$passcode_HASH = Encode::generateHash($_GET['passcode'], $results[0]['passcode_HASH']);
		if ($passcode_HASH == $results[0]['passcode_HASH']) {
			$where = "Lost Password ".$_CONFIG["where_sep"]." Create New";
			require_once('./includes/header.php');
			echo '<form action="'.basename(__FILE__).'" method="POST"><input type="hidden" name="passcode" value="'.$_GET['passcode'].'"><input type="hidden" name="request_ID" value="'.$_GET["request_ID"].'">';
			MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
			echo "
			<tr>
				<td class='area_1' style='text-align:right;'><strong>New Password:</strong></td>
				<td class='area_2'><input type=password name='pass1' size=30></td>
			</tr>
			<tr>
				<td class='area_1' style='text-align:right;'><strong>New Password:</strong></td>
				<td class='area_2'><input type=password name='pass2' size=30></td>
			</tr>
			<tr>
				<td class='footer_3a' style='text-align:center;' colspan='2'><input type=submit value='Submit'></td>
			</tr>
	</form>";
			MiscFunctions::echoTableFooter(SKIN_DIR);
			require_once('includes/footer.php');
			exit;
		} else {
			$error = "Unable to confirm: Unvalid Passcode";
			$e = true;
		}
	} else {
		$tdb->delete('getpass', $_GET['request_ID']);
		$error = "Unable to confirm: The request expired.  Please request again";
	}
}
$where = "Lost Password ".$_CONFIG["where_sep"]." Request";
require_once('./includes/header.php');
if (isset($error)) {
	echo "<div class='alert'><div class='alert_text'>
		<strong>Caution!</strong></div><div style='padding:4px;'>$error</div></div><br />";
	if ($e) {
		require_once('./includes/footer.php');
		exit;
	}
}
if (!$tdb->is_logged_in()) {
	if (!isset($_POST['u_name'])) $_POST['u_name'] = '';
	echo "<form action='".basename(__FILE__)."?ref=$ref' method=POST>";
	MiscFunctions::echoTableHeading(str_replace($_CONFIG["where_sep"], $_CONFIG["table_sep"], $where), $_CONFIG);
	echo "
			<tr>
				<th colspan='2'>Enter your username and a confirmation e-mail will be emailed to you.</th>
			</tr>
			<tr>
				<td class='area_1' style='text-align:right;padding:20px;'><strong>User Name:</strong></td>
				<td class='area_2'><input type=text name=u_name value='".$_POST['u_name']."' size=30> </td>
			</tr>
			<tr>
				<td class='footer_3a' style='text-align:center;' colspan='2'><input type=submit value='Submit'></td>
			</tr>
	</form>";
	MiscFunctions::echoTableFooter(SKIN_DIR);
}
require_once("./includes/footer.php");
?>