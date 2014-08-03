<?php
// Ultimate PHP Board
// Author: Tim Hoeppner aka RR_Pilot, FixITguy
// Website: http://www.myupb.com
// Version: 2.0
// Using textdb Version: 4.3.2
//Ending of center Table
if (!defined('DB_DIR')) die('This must be run under a wrapper script!');
if (!isset($script_end_time)) {
	$mt = explode(' ', microtime());
	$script_end_time = $mt[0] + $mt[1];
}
?>
<div class='copy'><a href='http://forum.myupb.com/'> <?php
echo "Powered by myUPB v".UPB_VERSION."</a>&nbsp;&nbsp;&middot;&nbsp;&nbsp;
	&copy; <a href=\"https://github.com/PHP-Outburst\" target=\"_blank\" title=\"at GitHub.com\">PHP Outburst</a> 2002 - ".date("Y",time());
?><br>
<br>
<a rel="license"
	href="http://creativecommons.org/licenses/by-nc-sa/3.0/"><img
	alt="Creative Commons License"
	src="http://i.creativecommons.org/l/by-nc-sa/3.0/80x15.png"/>
	</a></div>
</div>

</body>
</html>
