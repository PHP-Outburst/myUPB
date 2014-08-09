<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
require_once("./includes/upb.initialize.php");
$where = "<a href='admin.php'>Admin</a> ".$_CONFIG["where_sep"]." <a href='admin_iplog.php'>Ip Address Logs</a>";
require_once("./includes/header.php");
if (!isset($_COOKIE["user_env"]) || !isset($_COOKIE["uniquekey_env"]) || !isset($_COOKIE["power_env"]) || !isset($_COOKIE["id_env"])) MiscFunctions::exitPage("
	<div class='alert'><div class='alert_text'>
	<strong>Access Denied!</strong></div><div style='padding:4px;'>You are not logged in.</div></div>
	<meta http-equiv='refresh' content='2;URL=login.php?ref=admin_iplog.php'>");
if (!$tdb->is_logged_in() || $_COOKIE["power_env"] < 3) MiscFunctions::exitPage("
	<div class='alert'><div class='alert_text'>
	<strong>Access Denied!</strong></div><div style='padding:4px;'>you are not authorized to be here.</div></div>");
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
print '<a name="skip_nav">&nbsp;</a>';

//create page numbers and retrieve the raw IP log data
if(!isset($_GET['page']) || $_GET['page'] == '') $_GET['page'] = 1;
if(!file_exists(DB_DIR.'/ip.log') || filesize(DB_DIR.'/ip.log') == 0) {
	$pageStr = '';
	$log = "cut\toff\tdata\n---\t---\t---\t---\t---\nsome\tmore\tcut\toff\tdata";
} else {
	$pageStr = MiscFunctions::createPageNumbers($_GET['page'], (filesize(DB_DIR.'/ip.log')/(1024*20)));

	$pageStr =  "<table class='pagenum_container' cellspacing='1'>
			<tr>
				<td style='text-align:left;height:23px;'><span class='pagination_current'>Pages: </span>".$pageStr."</td>
			</tr>
		</table>";
	$f = fopen(DB_DIR."/ip.log", "r");
	fseek($f, filesize(DB_DIR.'/ip.log') - (1024 * 20 * $_GET['page']));
	$log = fread($f, (1024 * 20));
	fclose($f);
}

$pos1 = strpos($log, "\n");
$pos2 = strrpos($log, "\n") - 1;
$log = array_reverse(explode("\n", substr($log, $pos1+1, $pos2 - $pos1)));
$sublog = array_slice($log,($_GET['page']*$_CONFIG["posts_per_page"])-$_CONFIG["posts_per_page"],$_CONFIG["posts_per_page"]);
$num_pages = ceil((count($log) + 1) / $_CONFIG["posts_per_page"]);

$p = MiscFunctions::createPageNumbers($_GET['page'], $num_pages, $_SERVER['QUERY_STRING']);

echo pagination($p,$_GET['page'],$num_pages);


echo "<div style='clear:both;'></div>

    <div class='tabstyle_1'>
        <ul>
			<li><a href='admin_iplog_action.php?action=download' title='Download a copy of the IP Log?'><span>Download IP Log</span></a></li>
        	<li><a href='admin_iplog_action.php?action=clear' title='Clear the IP Log?'><span>Clear the IP Log</span></a></li>
        </ul>
    </div>
";

MiscFunctions::echoTableHeading("Visitor's Log", $_CONFIG);
echo "
	<tr>
    <th style='width:10%;'>REMOTE_HOST</th>
		<th style='width:10%;'>Username</th>
		<th style='width:20%;text-align:center;'>URL</th>
		<th style='width:15%;'>Access Date</th>
		<th style='width:35%;text-align:center;'>HTTP_USER_AGENT</th>
	</tr>";

//bot list format: $i = HTTP_USER_AGENT keyword; $i+1 = user mask
$bot_list = array(
'Yahoo! Slurp'    , 'Y! Web Crawler',
'msnbot'          , 'MSN Bot',
'Teoma'           , 'Ask Jeeves Bot',
'OpenDNS'         , 'OpenDNS Crawler',
'YodaoBot'        , 'Yodao Bot',
'Exabot'          , 'Exa Bot',
'Googlebot'       , 'Google Bot',
'sproose'         , 'Sproose Bot',
'sogou'           , 'Sogou Crawler',
'VoilaBot'        , 'Voila Bot',
'Sensis'          , 'Sensis Web Crawler',
'findlinks'       , 'Findlinks Spider',
'Yahoo-MMCrawler' , 'Yahoo MMCrawler',
'GingerCrawler' , 'GingerCrawler',
'Baiduspider', 'Baiduspider'
);

foreach($sublog as $entry) {
	$entry = explode("\t", $entry, 5);
	//bot detection
	for($i=0,$c=count($bot_list);$i<$c;$i+=2) {
		if(FALSE !== strpos($entry[4], $bot_list[$i])) {
			$entry[1] = "<i>".$bot_list[$i+1]."</i>";
			break 1;
		}
	}
	echo "
	<tr>
    <td class='area_1' style='padding:8px;'><strong>{$entry[0]}</strong></td>
		<td class='area_2'>{$entry[1]}</td>
		<td class='area_1'>{$entry[2]}</td>
		<td class='area_2'>".(ctype_digit(($entry[3])) ? gmdate('r', $entry[3]) : $entry[3])."</td>
		<td class='area_1'>{$entry[4]}</td>
	</tr>";
}
MiscFunctions::echoTableFooter(SKIN_DIR);
echo pagination($p,$_GET['page'],$num_pages);

require_once("./includes/footer.php");
?>
