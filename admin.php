<?php
/**
 * This file sets the main administration view up and displays it.
 *
 * @author Tim Hoeppner
 * @author FixITguy
 * @author Rouven Wachhaus <rouven@wachhaus.xyz>
 * @license https://creativecommons.org/licenses/by-nc-sa/3.0/
 * @version 2.2.7
 */
require_once('./includes/upb.initialize.php');
$where = 'Admin Panel';
require_once('./includes/header.php');

echo $twig->render('admin/_main.twig', array('_COOKIE' => $_COOKIE, 'tdb' => $tdb, 'SKIN_DIR' => SKIN_DIR, 'UPB_VERSION' => UPB_VERSION));
