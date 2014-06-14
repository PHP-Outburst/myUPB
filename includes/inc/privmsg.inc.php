<?php
// Ultimate PHP Board
// Author: PHP Outburst
// Website: http://www.myupb.com
// Version: 2.0

//Ultimate PHP Boart Private Messaging Custom Functions
function addUsersPMBlockedList($user_id) {
	$tmp = getUsersPMBlockedList($user_id);
	if(empty($tmp)) {
		$f = fopen(DB_DIR."/blockedlist.dat", 'a+');
		fwrite($f, $user_id.":".chr(31));
		fclose($f);
		return true;
	}
	return false;
}

function editUsersPMBlockedList($user_id, $newIds) {
	if(is_array($newIds)) $newIds = implode(",", $newIds);
	$f = fopen(DB_DIR."/blockedlist.dat", "r");
	$success = FALSE;
	$all = "";
	$rawRec = "";
	while(!feof($f)) {
		$next = fgetc($f);
		if(ord($next) == "31") {
			$rec = explode(":", $rawRec);
			if($rec[0] == $user_id) {
				$all .= $user_id.":".$newIds.chr(31);
				if((filesize(DB_DIR."/blockedlist.dat") - ftell($f)) > 0) $all .= fread($f, (filesize(DB_DIR."/blockedlist.dat") - ftell($f)));
				$success = TRUE;
				break;
			} else $all .= $rawRec.chr(31);
			$rawRec = "";
			unset($rec, $rRec);
		} else {
			$rawRec .= $next;
		}
	}
	fclose($f);
	if($success) {
		$f = fopen(DB_DIR."/blockedlist.dat", "w");
		fwrite($f, $all);
		fclose($f);
	}
	return $success;
}

function getUsersPMBlockedList($user_id) {
	$f = fopen(DB_DIR."/blockedlist.dat", "r");
	$blocked_ids = array();
	while(!feof($f)) {
		$next = fgetc($f);
		if(ord($next) == "31") {
			$rec = explode(":", $rawRec);
			if($rec[0] == $user_id) {
				if($rec[1] == "") $blocked_ids = array();
				elseif(strstr($rec[1], ",")) $blocked_ids = explode(",", $rec[1]);
				else $blocked_ids[0] = $rec[1];
				break;
			}
			$rawRec = "";
		} else {
			$rawRec .= $next;
		}
	}
	unset($rawRec, $rec);
	fclose($f);
	return $blocked_ids;
}
?>