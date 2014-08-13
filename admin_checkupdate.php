<?php
/**
 * This file sets the administrative update checker up and displays its view.
 *
 * @author Tim Hoeppner
 * @author FixITguy
 * @author Rouven Wachhaus <rouven@wachhaus.xyz>
 * @license https://creativecommons.org/licenses/by-nc-sa/3.0/
 * @version 2.2.7
 */
require_once('./includes/upb.initialize.php');
$where = '<a href="admin.php">Admin</a> ' . $_CONFIG['where_sep'] . ' Checking for updates';
require_once('./includes/header.php');

echo $twig->render('admin/checkupdate.twig', array('_COOKIE' => $_COOKIE, 'tdb' => $tdb,
    'SKIN_DIR' => SKIN_DIR, 'UPB_VERSION' => UPB_VERSION));