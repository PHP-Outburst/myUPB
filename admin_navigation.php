<?php
if(!defined("DB_DIR")) exit('This page must be run under a script wrapper');

if (isset($_COOKIE["user_env"]) && isset($_COOKIE["uniquekey_env"]) && isset($_COOKIE["power_env"]) && isset($_COOKIE["id_env"])) {
	if ($tdb->is_logged_in() && $_COOKIE["power_env"] >= 3) {
		echo "
				<div style='width:50%;float:left;line-height:20px;text-align:center;'><span class='link_1'>
				<a href='admin_forums.php#skip_nav' target = '_parent'>Manage Forums</a><br />
				<a href='admin_config.php#skip_nav' target = '_parent'>Manage Settings</a><br />
				<a href='admin_members.php#skip_nav' target = '_parent'>Manage Members</a><br />
				<a href='admin_smilies.php#skip_nav' target = '_parent'>Manage Smilies</a><br />
			<a href='admin_icons.php#skip_nav' target = '_parent'>Manage Post Icons</a></div>
				<div style='width:50%;float:right;line-height:20px;text-align:center;'><span class='link_1'>
		        <a href='admin_checkupdate.php#skip_nav' target = '_parent'>Check for Updates</a><br />
				<a href='admin_banuser.php#skip_nav' target = '_parent'>Manage Banned users</a><br />
				<a href='admin_badwords.php#skip_nav' target = '_parent'>Manage Filtered Language</a><br />
				<a href='admin_iplog.php#skip_nav' target = '_parent'>View the IP Address Log</a><br />
				<a href='admin_restore.php#skip_nav' target = '_parent'>Backup/Restore the database</a><br /></span></div>";
	}
}
?>
