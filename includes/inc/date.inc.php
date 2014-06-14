<?php
// Author: PHP Outburst
// Website: http://www.myupb.com

function mkdate() {
	return time();
	//return gmmktime()+43200; //GMT time is 12 hours behind
}

function user_date($timestamp) {
	return $timestamp+($_COOKIE['timezone']*3600);
}

function alterDate($date, $offset, $time_unit, $positive=true) {
	$offset = (int)$offset;
	if(!$positive) $offset = $offset*-1;
	if($offset == 0) return $date;
	switch(substr($time_unit, 0 , 2)) {
		case 'se': return $date+$offset;
		case 'mi': return $date+($offset*60);
		case 'hr':
		case 'ho': return $date+($offset*3600);
		case 'da': return $date+($offset*86400);
		case 'mo':
			$d = (int)date('j', $date);
			if($offset > 0) {
				$date += 86400*(findTheDaysOfTheMonth($date) - $d + 1);
				for($i=$offset-1;$i>0;$i--)
				$date += 86400*findTheDaysOfTheMonth($date);
				$date += 86400*($d - 1);
			} else {
				$date -= 86400*($d);
				for($i=$offset+1;$i<0;$i++)
				$date -= 86400*findTheDaysOfTheMonth($date);
				$date -= 86400*(findTheDaysOfTheMonth($date) - $d);
			}
			return $date;
		case 'yr':
		case 'ye':
			$d = (int)date('z', $date);
			if($offset > 0) {
				$date += 86400*(howManyDaysInAYear($date) - $d + 1);
				for ($i=$offset-1;$i>0;$i--)
				$date += 86400*(gmdate('L', $date) ? 366 : 365);
				$date += 86400*($d - 1);
			} else {
				$date -= 86400*$d;
				for ($i=$offset+1;$i<0;$i++)
				$date -= 86400*(gmdate('L', $date) ? 366 : 365);
				$date -= 86400*(howManyDaysInAYear($date) - $d);
			}
			return $date;
		default: echo 'Time unit not recognized'; return;
	}
}

function findTheDaysOfTheMonth($timestamp) {
	switch(date('n', $timestamp)) {
		case  1: return 31;
		case  2: return ((date('L', $timestamp)) ? 29 : 28);
		case  3: return 31;
		case  4: return 30;
		case  5: return 31;
		case  6: return 30;
		case  7: return 31;
		case  8: return 31;
		case  9: return 30;
		case 10: return 31;
		case 11: return 30;
		case 12: return 31;
		default: return false;
	}
}

function howManyDaysInAYear($date) {
	return (gmdate('L', $date) ? 366 : 365);
}
?>
