<?php
/**
 * This file was previously used to output the footer.
 *
 * DON'T USE THIS ANYMORE - IT IS SEVERELY DEPRECATED. Use our twig templates instead, all
 * of this is included in the master layout, which is automatically pulled when you render
 * any other of the templates.
 *
 * @author Tim Hoeppner
 * @author FixITguy
 * @author Piotr Halama <halamix2@o2.pl>
 * @author Rouven Wachhaus <rouven@wachhaus.xyz>
 * @license https://creativecommons.org/licenses/by-nc-sa/3.0/
 * @version 2.2.7
 */

//Ending of center Table
if (!defined('DB_DIR')) die('This must be run under a wrapper script!');
if (!isset($script_end_time)) {
	$mt = explode(' ', microtime());
	$script_end_time = $mt[0] + $mt[1];
}
?>
<footer>
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
</footer>
</div>

</body>
</html>
