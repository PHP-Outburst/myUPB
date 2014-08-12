<?php
/**
 * This file sets the administrative bad word filter up and displays its view.
 *
 * @author Tim Hoeppner
 * @author FixITguy
 * @author Rouven Wachhaus <rouven@wachhaus.xyz>
 * @license https://creativecommons.org/licenses/by-nc-sa/3.0/
 * @version 2.2.7
 */
require_once('./includes/upb.initialize.php');
$where = '<a href="admin.php">Admin</a> ' . $_CONFIG['where_sep'] . ' <a href="admin_badwords.php">Manage Filtered Language</a>';
require_once('./includes/header.php');

if ($_GET['action'] == 'delete' && $_GET['word'] != '') {
    $words = array();

	if ($_POST['verify'] == 'Ok') {
		$words = explode(',', $_CONFIG['banned_words']);

		if (($index = array_search($_GET['word'], $words)) !== false) {
			unset($words[$index]);
			$words = implode(',', $words);
			$config_tdb->editVars('config', array('banned_words' => $words));
		}
    }

    echo $twig->render('admin/badwords_delete.twig', array('words' => $words, '_COOKIE' => $_COOKIE, 'tdb' => $tdb,
        '_POST' => $_POST, '_GET' => $_GET, 'SKIN_DIR' => SKIN_DIR, 'UPB_VERSION' => UPB_VERSION));
} elseif($_GET['action'] == 'addnew') {
    $words = array();

    if ($_POST['newword'] != '') {
        $words = $_CONFIG['banned_words'] . ((strlen($_CONFIG['banned_words']) == 0) ? '' : ',') . htmlentities(stripslashes(trim($_POST['newword'])));
        $config_tdb->editVars('config', array('banned_words' => $words));
    }

    echo $twig->render('admin/badwords_add.twig', array('words' => $words, '_COOKIE' => $_COOKIE, 'tdb' => $tdb,
        '_POST' => $_POST, '_GET' => $_GET, 'SKIN_DIR' => SKIN_DIR, 'UPB_VERSION' => UPB_VERSION));
} else {
	$words = explode(',', $_CONFIG['banned_words']);

    echo $twig->render('admin/badwords.twig', array('words' => $words, '_COOKIE' => $_COOKIE, 'tdb' => $tdb,
        'SKIN_DIR' => SKIN_DIR, 'UPB_VERSION' => UPB_VERSION));
}