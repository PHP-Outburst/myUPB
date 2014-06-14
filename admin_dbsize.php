<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
//list old search files too

require_once("includes/upb.initialize.php");

if(!$tdb->is_logged_in() || $_COOKIE["power_env"] < 3)
{
	die("You are not authorized to be here");
}
else
{
	$size = 0;
	$files = array();
	dbsize($size, $files);
	$size = round($size / 1024, 2);
	echo "Total db size: $size KB<br />";
	$oldsize = 0;
	$oldfiles = array();
	dbsize($oldsize, $oldfiles, true, 1);
	$oldsize = round($oldsize / 1024, 2);
	echo "Files older than 1 days: $oldsize KB<br />";
	for($i = 0; $i < count($oldfiles); $i++) {
		echo "$oldfiles[$i]<br />";
	}
	echo "done!";
	
}

function dbsize(&$size, &$filearr, $getold = false, $days = 30) {
	$dbdir = opendir(DB_DIR);
	while ($p = readdir($dbdir)) {
		if (is_file("./db/".$p)) {
			if ($getold) {
			} else {
				$size += filesize(DB_DIR."/".$p);
				$filearr[] = DB_DIR."/".$p;
			}
		}
		if (is_dir(DB_DIR."/".$p) && $p != "." && $p != "..") {
			$dir = opendir(DB_DIR."/".$p);
			while ($d = readdir($dir)) {
				if (is_file(DB_DIR."/$p/".$d)) {
					if ($getold) {
						if (fileatime(DB_DIR."/$p/".$d) + (60 * 60 * 24 * $days) < time()) {
							if (is_numeric($p)) {
								$size += filesize(DB_DIR."/$p/".$d);
								$filearr[] = DB_DIR."/$p/".$d;
							}
						}
					} else {
						$size += filesize(DB_DIR."/$p/".$d);
						$filearr[] = DB_DIR."/$p/".$d;
					}
				}
			}
			closedir($dir);
		}
	}
	closedir($dbdir);
}
// sec * min * hr * days
?>