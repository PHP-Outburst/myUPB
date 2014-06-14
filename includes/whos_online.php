<?php
// Ultimate PHP Board
// Whos online System
if (!defined('DB_DIR')) die ("The Whos online script must be run under a wrapper script");
/*
 length:seek:name
 20:00:user_name
 16:20:user_id
 01:36:user_power
 14:37:time
 51:51:total
 */
if ($tdb->is_logged_in()) {
	$wRaw = $_COOKIE['user_env'].str_repeat(' ', 20 - strlen($_COOKIE['user_env'])). $_COOKIE['id_env'].str_repeat(' ', 16 - strlen($_COOKIE['id_env'])). $_COOKIE['power_env'];
	$wSearch = $_COOKIE['id_env'].str_repeat(' ', 16 - strlen($_COOKIE['id_env']));
} else {
	$wRaw = 'guest'.str_repeat(' ', 15). getenv("REMOTE_ADDR").str_repeat(' ', 16 - strlen(getenv("REMOTE_ADDR"))). '0';
	$wSearch = getenv("REMOTE_ADDR");
}
$now = mkdate();
if (strlen($now) > 14) $now = substr($now, 0, 14);
else $now = $now.str_repeat(' ', 14 - strlen($now));
$wRaw .= $now."\n";
if (file_exists(DB_DIR.'/whos_online.dat')) $whos_online_log = file_get_contents(DB_DIR.'/whos_online.dat');
else $whos_online_log = '';
if (FALSE !== ($you_pos = strpos($whos_online_log, $wSearch))) {
	//update timestamp
	$you_pos -= 20;
	$whos_online_log = substr($whos_online_log, 0, $you_pos + 37).$now.substr($whos_online_log, $you_pos + 51);
} else {
	//add user
	$whos_online_log .= $wRaw;
}
$whos_online_array = explode("\n", substr($whos_online_log, 0, -1));
$whos_online_array = array_reverse($whos_online_array);
$whos_online_count = count($whos_online_array);
for($wi = 0; $wi < $whos_online_count; $wi++) {
	if ((int)substr($whos_online_array[$wi], 37 , 14) < (mkdate()-(60 * 15))) unset($whos_online_array[$wi]);
}
$whos_online_log = implode("\n", array_reverse($whos_online_array))."\n";
$f = fopen(DB_DIR.'/whos_online.dat', 'w');
fwrite($f, $whos_online_log);
function whos_online($whos_online_log, $_STATUS) {
	$whos_online_array = array_reverse(explode("\n", substr($whos_online_log, 0, -1)));
	$whos_online_count = count($whos_online_array);
	$return = array('guests' => 0, 'who' => array());
	for($i = 0; $i < $whos_online_count; $i++) {
		$wUser = array('user_name' => rtrim(substr($whos_online_array[$i], 0, 20)),
				'user_id' => rtrim(substr($whos_online_array[$i], 20, 15)),
				'user_power' => rtrim(substr($whos_online_array[$i], 36, 1)),
				'time' => rtrim(substr($whos_online_array[$i], 37, 14))  );
		if ($wUser['user_power'] == '0') {
			$return['guests']++;
		} else {
			if ($wUser['user_power'] == '1') $color = "#".$_STATUS['userColor'];
			elseif($wUser['user_power'] == '2') $color = "#".$_STATUS['modColor'];
			elseif($wUser['user_power'] >= '3') $color = "#".$_STATUS['adminColor'];
			$return['who'][] = '<a href="profile.php?action=get&amp;id='.$wUser['user_id'].'"><span style="color:'.$color.';">'.$wUser['user_name'].'</span></a>';
		}
	}
	if (!empty($return['who'])) {
		$return['users'] = count($return['who']);
		$return['who'] = implode(', ', $return['who']);
	} else {
		$return['users'] = 0;
	}
	return $return;
}
?>