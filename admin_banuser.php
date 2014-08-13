<?php
/**
 * This file sets the administrative user ban interface up and displays its view.
 *
 * @author Tim Hoeppner
 * @author FixITguy
 * @author Rouven Wachhaus <rouven@wachhaus.xyz>
 * @license https://creativecommons.org/licenses/by-nc-sa/3.0/
 * @version 2.2.7
 */
require_once('./includes/upb.initialize.php');
$where = '<a href="admin.php">Admin</a> ' . $_CONFIG['where_sep'] . ' <a href="admin_baduser.php">Manage banned users</a>';
require_once('./includes/header.php');


if (isset($_GET['action'])) {
    if ($_GET['action'] == 'edit' && $_GET['word'] != '') {
        //edit banned user
        $words = explode("\n", file_get_contents(DB_DIR . '/banneduser.dat'));

        if (($index = array_search($_GET['word'], $words)) !== false) {
            if (isset($_POST['newword'])) {
                $words[$index] = trim($_POST['newword']);
                $f = fopen(DB_DIR . '/banneduser.dat', 'w');
                fwrite($f, implode("\n", $words));
                fclose($f);
            }

            echo $twig->render('admin/banuser_edit.twig', array('word' => $words[$index], '_COOKIE' => $_COOKIE, 'tdb' => $tdb,
                '_POST' => $_POST, '_GET' => $_GET, 'SKIN_DIR' => SKIN_DIR, 'UPB_VERSION' => UPB_VERSION));
        } else {
            die('Invalid original username');
        }
    } elseif ($_GET['action'] == 'delete' && $_GET['word'] != '') {
        //delete banned user
        if ($_POST['verify'] == 'Ok') {
            // delete the user
            $words = explode("\n", file_get_contents(DB_DIR . '/banneduser.dat'));

            if (($index = array_search($_GET['word'], $words)) !== false)
                unset($words[$index]);

            $f = fopen(DB_DIR . '/banneduser.dat', 'w');
            fwrite($f, implode("\n", $words));
            fclose($f);
        }

        echo $twig->render('admin/banuser_delete.twig', array('words' => $words, '_COOKIE' => $_COOKIE, 'tdb' => $tdb,
            '_POST' => $_POST, '_GET' => $_GET, 'SKIN_DIR' => SKIN_DIR, 'UPB_VERSION' => UPB_VERSION));
    } elseif ($_GET['action'] == 'addnew') {
        //add new user
        if($_POST['word'] != '') {
            if (filesize(DB_DIR . '/banneduser.dat') > 0) {
                $names = explode("\n", file_get_contents(DB_DIR . '/banneduser.dat'));
            } else
                $names = array();

            $names[] = stripslashes(trim($_POST['word']));
            $f = fopen(DB_DIR . '/banneduser.dat', 'w');
            fwrite($f, implode("\n", $names));
            fclose($f);
        }

        echo $twig->render('admin/banuser_add.twig', array('_GET' => $_GET, '_POST' => $_POST,
            '_COOKIE' => $_COOKIE, 'tdb' => $tdb,
            '_POST' => $_POST, '_GET' => $_GET, 'SKIN_DIR' => SKIN_DIR, 'UPB_VERSION' => UPB_VERSION));
    }
} else {
    $userList = explode("\n", file_get_contents(DB_DIR . '/banneduser.dat'));
    echo $twig->render('admin/banuser.twig', array('userList' => $userList, '_COOKIE' => $_COOKIE, 'tdb' => $tdb,
        'SKIN_DIR' => SKIN_DIR, 'UPB_VERSION' => UPB_VERSION));
}