<?php
/**
 *
 * @author Rouven Wachhaus <rouven@wachhaus.xyz>
 * @todo doc
 * @todo cc wrt variable/method names
 * @todo original author?
 */
/**
 * Class TdbFunctions
 */
class TdbFunctions extends tdb
{
    private $_cache = array();

    function __construct($dir, $db)
    {
        $this->cleanup();
        $this->tdb($dir, $db);
    }

    public function login_user($user, $pass, $key, &$error)
    {
        if ($this->fp['users'] != 'members')
            $this->setFp('users', 'members');

        $rec = $this->query('users', 'user_name=\'' . $user . '\'', 1, 1);

        if (strtolower($rec[0]['user_name']) != strtolower($user)) {
            $error = 'Either your Username or your Password was incorrect.';

            return false;
        }

        if ($rec[0]['password']{0} != chr(21)) {
            if ($rec[0]['password'] == generateHash($pass, $rec[0]['password'])) {
                if ($rec[0]['reg_code'] != '') {
                    $error = 'Your account has not been validated yet.';

                    if (!$GLOBALS['_REGIST']['reg_approval'])
                        $error .= '  To resend your confirmation e-mail, <a href="register.php?action=resend&id=' . $rec[0]['id'] . '">click here</a>.';
                    else
                        $error .= '  The forum admin hasn\'t approved your account yet.';

                    return false;
                }

                $this->edit('users', $rec[0]['id'], array('lastvisit' => mkdate()));
                $rec[0]['lastvisit'] = mkdate();

                return $rec[0];
            }
        } elseif (substr($rec[0]['password'], 1) == stripslashes(t_encrypt(substr($pass, 0, (HASH_LENGTH - 1)), $key))) {
            $rec[0]['password'] = generateHash($pass);
            $this->edit('users', $rec[0]['id'], array('password' => $rec[0]['password']));
            $this->edit('users', $rec[0]['id'], array('lastvisit' => mkdate()));

            return $rec[0];
        }

        $error = 'Either your Username or your Password was incorrect.';

        return false;
    }

    public function is_logged_in()
    {
        if (!isset($_COOKIE['user_env']) || $_COOKIE['user_env'] == '' ||
            !isset($_COOKIE['uniquekey_env']) || $_COOKIE['uniquekey_env'] == '' ||
            !isset($_COOKIE['power_env']) || $_COOKIE['power_env'] == '' ||
            !isset($_COOKIE['id_env']) || $_COOKIE['id_env'] == '')
            return false;

        if (!empty($this->_cache['is_logged_in'][$_COOKIE['id_env']])) {
            if ($_COOKIE['user_env'] == $this->_cache['is_logged_in'][$_COOKIE['id_env']]['user']
                && $_COOKIE['uniquekey_env'] == $this->_cache['is_logged_in'][$_COOKIE['id_env']]['uniquekey']
                && $_COOKIE['power_env'] == $this->_cache['is_logged_in'][$_COOKIE['id_env']]['power'])
                    return true;
        }

        if (!isset($this->fp['users']))
            $this->setFp('users', 'members');

        $rec = $this->get('users', $_COOKIE['id_env']);
        /*        if(strlen($_COOKIE['password_env']) != HASH_LENGTH && basename($_SERVER['PHP_SELF']) != 'login.php') {
         redirect('logoff.php?ref=login.php', 0);
         exit;
         } */

        if ($_COOKIE['user_env'] == $rec[0]['user_name'] && $_COOKIE['uniquekey_env'] == $rec[0]['uniquekey'] && $_COOKIE['power_env'] == $rec[0]['level']) {
            $this->_cache['is_logged_in'][$_COOKIE['id_env']] = array(
                'user' => $_COOKIE['user_env'],
                'uniquekey' => $_COOKIE['uniquekey_env'],
                'power' => $_COOKIE['power_env']);

            return true;
        }

        return false;
    }

    public function updateVisitedTopics()
    {
        if (!$this->is_logged_in())
            return false;

        $compact = serialize($_SESSION['newTopics']);
        $hash = md5($compact);

        if ($hash != $_SESSION['__newTopicsHash']) {
            $_SESSION['__newTopicsHash'] = $hash;
            $this->edit('users', $_COOKIE['id_env'], array('newTopicsData' => $compact));

            return true;
        }

        return false;
    }

    public function getID($fp)
    {
        $header = array();
        $this->readHeader($fp, $header);

        return $header['curId'];
    }

    public function getUploads($fid, $tid, $pid, $upload_ids, $location, $userid)
    {
        if ($upload_ids == '' || $upload_ids == '0' || $upload_ids == false)
            return;

        $output =  '';
        $downloads = '';
        $ids = explode(',', $upload_ids);

        foreach ($ids as $id) {
            if ($id > 0) {
                //check information is in the upload database
                $q = $this->get('uploads', $id, array('name', 'downloads', 'file_loca'));

                if (!empty($q[0]) && file_exists($location . '/' . $q[0]['file_loca']))	{
                    $attachName = $q[0]['name'];
                    $attachDownloads = $q[0]['downloads'];
                    $filesize = filesize($location . '/' . $q[0]['file_loca']);

                    if ($filesize < 1024)
                        $attachSize = $filesize . ' bytes';
                    elseif ($filesize > 1048576)
                        $attachSize = round(filesize($location . '/' . $q[0]['file_loca'])/1048576,2) . 'MB';
                    else
                        $attachSize = floor(filesize($location . '/' . $q[0]['file_loca'])/1024) . 'KB';

                    $downloads .= '<a href="downloadattachment.php?upload_id=' . $id . '">' . $attachName . '</a> (' . $attachSize . ' / ' . $attachDownloads . ' Downloads)';

                    if ((int)$_COOKIE['power_env'] >= 3 || $userid == (int)$_COOKIE['id_env'])
                        $downloads .= ' <a href="javascript:deleteFile(' . $fid . ',' . $tid . ',' . $pid . ',' . $id . ',\'' . $attachName . '\',' . (int)$_COOKIE['id_env'] . ',\'' . $fid . '-' . $tid . '-' . $pid . '-attach\')" onMouseOver="window.status=\'Delete ' . $attachName . '\';\">Delete</a>';

                    $downloads .= '<p>';
                }
            }
        }

        if ($downloads != '') {
            $output .= '<p><fieldset><legend>Attached File(s)</legend>';
            $output .= $downloads;
            $output .= '</fieldset>';
        }

        return $output;
    }
}