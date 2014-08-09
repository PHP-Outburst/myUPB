<?php
require_once('./includes/upb.initialize.php');
$cols_n = 6; //how many columns per row of the table
require_once('./includes/header_simple.php');
echo "<div id='simple_border'>
			<div class='simple_head'>Viewing additional smilies</div>
			<div class='simple_sub_smilie'>Click on a smilie image below to have it added to your post.</div>
			<table id='simple_table' cellspacing='12'><tr>";

$bdb = new Tdb(DB_DIR.'/','bbcode.tdb');
$bdb->setFp("smilies","smilies");
$smilies = $bdb->query("smilies","id>'0'&&type='more'");

foreach ($smilies as $key => $value)
{
	$name = strmstr(strstr_after($value['replace'], "/"),"'",true);
	echo "<td class='simple_smilie_box'><A HREF=\"javascript:moresmilies('".$value['bbcode']."')\" ONFOCUS=\"filter:blur()\">".$value['replace']."</a></td>\n";
	if ($key%6 == 5)
	echo "</tr><tr>";
}

echo "</tr><tr><td colspan='$cols_n' class='simple_sub_smilie'><a href='javascript:self.close();'>Close Window</a></td></tr></table></div></body></html>";
include_once('./includes/footer_simple.php');

?>

