<?php
/**
 *
 */
/**
 * Class MiscFunctions
 */
class MiscFunctions
{
    public static function exitPage($text, $include_header = false, $include_footer = true, $footer_simple = false)
    {
        $_CONFIG = &$GLOBALS['_CONFIG'];
        $tdb = &$GLOBALS['tdb'];
        //$tdb->define_error_handler(array(&$errorHandler, 'add_error'));

        if ($include_header)
            require_once('./includes/header.php');

        echo '<br />' . $text;

        if ($footer_simple)
            $footer = 'footer_simple.php';
        else
            $footer = 'footer.php';

        if ($include_footer)
            require_once('./includes/' . $footer);

        exit;
    }

    public static function redirect($where, $time)
    {
        echo '<meta http-equiv="refresh" content="' . $time . ';URL=' . $where . '">';

        exit;
    }

    public static function deleteWhiteIndex(&$array)
    {
        for ($i = 0; $i < count($array); $i++) {
                $array[$i] = trim($array[$i]);

            if ($array[$i] == '') {
                unset($array[$i]);
            }
        }
    }

    //UnTested!!
    public static function array_reset_keys(&$array)
    {
        $keys = array_keys($array);
        sort($keys, SORT_NUMERIC);
        $i = 0;

        foreach ($keys as $key) {
            if (!ctype_digit($key))
                continue;

            if ($key != $i) {
                $array[$i] =& $array[$key];
                unset($array[$key]);
            }

            $i++;
        }
    }

    public static function createUserPowerMisc($user_power, $list_format, $exclude_guests = false)
    {
        //$list_format choices:
        //$list_format = 1; ==> dropdown list of current Power and above
        //$list_format = 2; ==> text only of current power and above
        //$list_format = 3; ==> short text only of current power and above
        //$list_format = 4; ==> text only of current power only
        //$list_format = 5; ==> short text only of current power only
        //$list_format = 6; ==> short dropdown list of current Power and above
        //$list_format = 7; ==> dropdown list of current Power only
        //$list_format = 8; ==> short dropdown list of current Power only

        $dropdown = false;
        $allText = false;
        $allShortText = false;
        $oneText = false;
        $oneShortText = false;
        $shortDropDown = false;
        $oneDropDown = false;
        $oneShortDropDown = false;

        if ($list_format == 1)
            $dropdown = true;
        elseif ($list_format == 2)
            $allText = true;
        elseif ($list_format == 3)
            $allShortText = true;
        elseif ($list_format == 4)
            $oneText = true;
        elseif ($list_format == 5)
            $oneShortText = true;
        elseif ($list_format == 6)
            $shortDropDown = true;
        elseif ($list_format == 7)
            $oneDropDown = true;
        elseif ($list_format == 8)
            $oneShortDropDown = true;
        else {
            echo 'Wrong Selection';
            return false;
        }

        $list  = '';

        if ((bool)$exclude_guests === false) {
            if (($user_power == 0 || $user_power == '')) {
                if ($dropdown)
                    $list .= '<option value="0" selected>Guests and above</option>';
                elseif ($allText)
                    return '<b>guests</b> and above';
                elseif ($allShortText)
                    return 'guest+';
                elseif ($oneText)
                    return 'Guest';
                elseif ($oneShortText)
                    return 'Guest';
                elseif ($shortDropDown)
                    $list .= '<option value="0" selected>Guests+</option>';
                elseif ($oneDropDown)
                    $list .= '<option value="0" selected>Guests</option>';
                elseif ($oneShortDropDown)
                    $list .= '<option value="0" selected>Guest</option>';
            } else {
                if ($dropdown)
                    $list .= '<option value="0">Guests and above</option>';
                elseif ($shortDropDown)
                    $list .= '<option value="0">Guests+</option>';
                elseif ($oneDropDown)
                    $list .= '<option value="0">Guests</option>';
                elseif ($oneShortDropDown)
                    $list .= '<option value="0">Guest</option>';
            }
        }

        if ($user_power == 1) {
            if ($dropdown)
                $list .= '<option value="1" selected>Members and above</option>';
            elseif ($allText)
                return '<b>members</b> and above';
            elseif ($allShortText)
                return 'members+';
            elseif ($oneText)
                return 'Member';
            elseif ($oneShortText)
                return 'Member';
            elseif ($shortDropDown)
                $list .= '<option value="1" selected>Members+</option>';
            elseif ($oneDropDown)
                $list .= '<option value="1" selected>Members</option>';
            elseif ($oneShortDropDown)
                $list .= '<option value="1" selected>Member</option>';
        } else {
            if ($dropdown)
                $list .= '<option value="1">Members and above</option>';
            elseif ($shortDropDown)
                $list .= '<option value="1">Members+</option>';
            elseif ($oneDropDown)
                $list .= '<option value="1">Member</option>';
            elseif ($oneShortDropDown)
                $list .= '<option value="1">Member</option>';
        }

        if ($user_power == 2) {
            if ($dropdown)
                $list .= '<option value="2" selected>Moderators and Administrators</option>';
            elseif ($allText)
                return '<b>mods & admins</b>';
            elseif ($allShortText)
                return 'mods+';
            elseif ($oneText)
                return 'Moderator';
            elseif ($oneShortText)
                return 'Mod';
            elseif ($shortDropDown)
                $list .= '<option value="2" selected>Mods+</option>';
            elseif ($oneDropDown)
                $list .= '<option value="2" selected>Moderator</option>';
            elseif ($oneShortDropDown)
                $list .= '<option value="2" selected>Mod</option>';
        } else {
            if ($dropdown)
                $list .= '<option value="2">Moderators and Administrators</option>';
            elseif ($shortDropDown)
                $list .= '<option value="2">Mods+</option>';
            elseif ($oneDropDown)
                $list .= '<option value="2">Moderator</option>';
            elseif ($oneShortDropDown)
                $list .= '<option value="2">Mod</option>';
        }

        if ($user_power >= 3) {
            if ($dropdown)
                $list .= '<option value="3" selected>Administrators only</option>';
            elseif ($allText)
                return '<b>admins</b> only';
            elseif ($allShortText)
                return 'admins';
            elseif ($oneText)
                return 'Administrator';
            elseif ($oneShortText)
                return 'Admin';
            elseif ($shortDropDown)
                $list .= '<option value="3" selected>Admins</option>';
            elseif ($oneDropDown)
                $list .= '<option value="3" selected>Administrator</option>';
            elseif ($oneShortDropDown)
                $list .= '<option value="3" selected>Admin</option>';
        } else {
            if ($dropdown)
                $list .= '<option value="3">Administrators only</option>';
            elseif ($shortDropDown)
                $list .= '<option value="3">Admins</option>';
            elseif ($oneDropDown)
                $list .= '<option value="3">Administrator</option>';
            elseif ($oneShortDropDown)
                $list .= '<option value="3">Admin</option>';
        }

        if ($list != '')
            return $list;
        else {
            echo 'Error in createUserPowerMisc(): User\'s power unidentifiable (' . $user_power . ')';
            return false;
        }
    }

    public static function ok_cancel($action, $text)
    {
        echo '<form action="' . $action . '" method="post"><div class="alert"><div class="alert_text">
            <strong>' . $text . '</strong></div><div style="padding:4px;"><input type="submit" name="verify" value="Ok">
            <input type="submit" name="verify" value="Cancel"></div></div></form>';
    }

    public static function createPageNumbers($current_page, $total_number_of_pages, $url_string = '')
    {
        if ($current_page == '')
            $current_page = '1';

        $num_pages = (int)$total_number_of_pages;
        $url_string = str_replace('page=' . $current_page, '', $url_string);

        if ($url_string != '')
            $url_string = '?' . $url_string . '&';
        else
            $url_string = '?';

        $url_string = str_replace('&&', '&', $url_string);

        // look at current page number. If more than three on either side display ...
        $pageStr = '';

        if ($num_pages == 1)
            $pageStr = '<td class="pagination_current">' . $num_pages . '</td>';
        else {
            if ($current_page != 1)
                $pageStr = '<td class="pagination_link"><a href="' . basename($_SERVER['PHP_SELF']) . $url_string . 'page=' . ($current_page - 1) . '"></a></td>';

            if ($current_page - 2 != 1 and $current_page - 2 > 1)
                $pageStr .= '<td class="pagination_link"><a href="' . basename($_SERVER['PHP_SELF']) . $url_string . 'page=1">1</a></td><td>...</td>';

            for ($i = ($current_page - 2); $i <= ($current_page + 2); $i++) {
                if ($i < 1)
                    continue;

                if ($i > $num_pages)
                    continue;

                if ($current_page == $i)
                    $pageStr .= '<td class="pagination_current">' . $i . '</td>';
                else
                    $pageStr .= '<td class="pagination_link"><a href="' . basename($_SERVER['PHP_SELF']) . $url_string . 'page=' . $i . '">' . $i . '</a></td>';
            }

            if ($current_page+2 < $num_pages)
                $pageStr .= '<td>...</td><td class="pagination_link"><a href="' . basename($_SERVER['PHP_SELF']) . $url_string . 'page=' . $num_pages . '">' . $num_pages . '</a></td>';

            if ($current_page != $num_pages)
                $pageStr .= '<td class="pagination_link"><a href="' . basename($_SERVER['PHP_SELF']) . $url_string . 'page=' . ($current_page + 1) . '"></a></td>';
        }

        return $pageStr;
    }

    public static function generateUniqueKey()
    {
        return md5(uniqid(rand(), true));
        /*    $key = '';
         for($i=0;$i<11;$i++) {
         $key .= chr(rand(33,126));
         }
         return $key; */
    }

    public static function strstr_after($haystack, $needle, $case_insensitive = false)
    {
        $strpos = ($case_insensitive) ? 'stripos' : 'strpos';
        $pos = $strpos($haystack, $needle);

        if (is_int($pos)) {
            return substr($haystack, $pos + strlen($needle));
        }

        // Most likely false or null
        return $pos;
    }

    public static function directory($dir, $filters = 'all')
    {
        $files = $filtered = array();

        if (($handle = @opendir($dir)) !== false) {
            while (($file = readdir($handle)) !== false) {
                if ($file != '.' and $file != '..')
                    $files[] = $file;
            }

            closedir($handle);
        }

        if ($filters == 'all')
            $filtered = $files;
        else {
            $filters = explode(',', $filters);

            if (!empty($files)) {
                foreach ($files as $file) {
                    for ($f = 0; $f < sizeof($filters); $f++) {
                        $system = explode('.', $file);

                        if (count($system) > 1) {
                            if (strtolower($system[1]) == $filters[$f])
                                $filtered[] = $file;
                        }
                    }
                }
            }
        }

        return $filtered;
    }

    public static function strmstr($haystack, $needle, $before_needle = false)
    {
        //Find position of $needle or abort
        if (($pos = strpos($haystack, $needle)) === false)
            return false;

        if ($before_needle)
            return substr($haystack, 0, ($pos - 1) + strlen($needle));
        else
            return substr($haystack, $pos);
    }

    //for debugging
    public static function dump($array)
    {
        echo '<pre>';
        var_dump($array);
        echo '</pre>';
    }

    public static function echoTableHeading($display, $_CONFIG)
    {
        //set $display to 85
        echo '<div class="main_cat_wrapper">
            <div class="cat_area_1">' . $display . '</div>
            <table class="main_table">
            <tbody>';
    }

    public static function echoTableFooter($skin_dir)
    {
        echo '</tbody></table><div class="footer"><img src="' . $skin_dir . '/images/spacer.gif" alt="" title="" /></div>
        </div><br />';
    }

    public static function timezonelist($current = 0, $timezone_field_string = 'u_timezone')
    {
        $timezones = array();
        $timezones['-12'] = '(GMT -12:00) Eniwetok, Kwajalein';
        $timezones['-11'] = '(GMT -11:00) Midway Island, Samoa';
        $timezones['-10'] = '(GMT -10:00) Hawaii';
        $timezones['-9'] = '(GMT -9:00) Alaska';
        $timezones['-8'] = '(GMT -8:00) Pacific Time (US &amp; Canada)';
        $timezones['-7'] = '(GMT -7:00) Mountain Time (US &amp; Canada)';
        $timezones['-6'] = '(GMT -6:00) Central Time (US &amp; Canada), Mexico City';
        $timezones['-5'] = '(GMT -5:00) Eastern Time (US &amp; Canada), Bogota, Lima';
        $timezones['-4'] = '(GMT -4:00) Atlantic Time (Canada), Caracas, La Paz';
        $timezones['-3'] = '(GMT -3:00) Brazil, Buenos Aires, Georgetown';
        $timezones['-2'] = '(GMT -2:00) Mid-Atlantic';
        $timezones['-1'] = '(GMT -1:00 hour) Azores, Cape Verde Islands';
        $timezones['0'] = '(GMT) Western Europe Time, London, Lisbon, Casablanca';
        $timezones['1'] = '(GMT +1:00) Brussels, Copenhagen, Madrid, Paris, Rome';
        $timezones['2'] = '(GMT +2:00) Kaliningrad, South Africa';
        $timezones['3'] = '(GMT +3:00) Baghdad, Riyadh, Moscow, St. Petersburg';
        $timezones['3.5'] = '(GMT +3:30) Tehran';
        $timezones['4'] = '(GMT +4:00) Abu Dhabi, Muscat, Baku, Tbilisi';
        $timezones['4.5'] = '(GMT +4:30) Kabul';
        $timezones['5'] = '(GMT +5:00) Ekaterinburg, Islamabad, Karachi, Tashkent';
        $timezones['5.5'] = '(GMT +5:30) Bombay, Calcutta, Madras, New Delhi';
        $timezones['6'] = '(GMT +6:00) Almaty, Dhaka, Colombo';
        $timezones['7'] = '(GMT +7:00) Bangkok, Hanoi, Jakarta';
        $timezones['8'] = '(GMT +8:00) Beijing, Perth, Singapore, Hong Kong';
        $timezones['9'] = '(GMT +9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk';
        $timezones['9.5'] = '(GMT +9:30) Adelaide, Darwin';
        $timezones['10'] = '(GMT +10:00) Eastern Australia, Guam, Vladivostok';
        $timezones['11'] = '(GMT +11:00) Magadan, Solomon Islands, New Caledonia';
        $timezones['12'] = '(GMT +12:00) Auckland, Wellington, Fiji, Kamchatka';

        $output = '<select name="' . $timezone_field_string . '" id="u_timezone">';
        $set = (float)$current; //convert to a float for comparison with keys

        foreach ($timezones as $key => $places) {
            $diff = (float)$key; //set type to float to convert some array keys which are strings.
            $output .= '<option value="' . (float)$diff . '"';

            if ($set == $diff)
                $output .= ' selected="selected"';

            $output .= '>' . $places . '</option>';
        }

        $output .= '</select>';

        return $output;
    }

    //replaces characters in strings to make xml compatible
    public static function xml_clean($string)
    {
        $original = array("&", "\"", "\'", ">", "<");
        $replace = array('&amp;', '&quot;', '&apos;', '&gt;', '&lt;');
        $new = str_replace($original,$replace,$string);

        return $new;
    }

    public static function returnimages($dirname = 'images/avatars/')
    {
        $pattern = '/\.(jpg|jpeg|png|gif|bmp)$/i';
        $files = array();
        $curimage = 0;

        if ($handle = opendir($dirname)) {
            while (false !== ($file = readdir($handle))) {
                if (preg_match($pattern, $file)) {
                    echo '<option value ="images/avatars/' . $file . '">' . $file . '</option>';
                    $curimage++;
                }
            }

            closedir($handle);
        }

        return($files);
    }

    public static function isValidURL($url)
    {
        return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
    }

    public static function pagination($page_string, $page, $num_pages)
    {
        $output = '';

        if ($num_pages != 1) {
            $output .= '<table><tr><td class="pagination_title">Pages (' . $num_pages . '):</td>' . $page_string . '</tr></table><div style="clear:both;"></div>';
            $output .= '<div style="clear:both;"></div>';
        }

        return $output;
    }

    public static function resize_img($image,$target)
    {
        if (substr_count($image, 'downloadattachment.php') > 0)
            return;

        $sizes = @getimagesize($image);
        $width = $sizes[0];
        $height = $sizes[1];

        if ($width < $target && $height < $target)
            return;

        if ($width > $height) {
            $percentage = ($target / $width);
        } else {
            $percentage = ($target / $height);
        }

        $width = round($width * $percentage);
        $height = round($height * $percentage);

        return 'width="' . $width . '" height="' . $height . '"';
    }

    public static function delete_topics($tRec, $forumid)
    {
        global $tdb, $posts_tdb, $_CONFIG;

        $p_ids = explode(',', $tRec[0]['p_ids']);
        $subtract_user_post_count = array();

        foreach ($p_ids as $p_id) {
            $pRec = $posts_tdb->get('posts', $p_id);

            if ($pRec[0]['upload_id'] != 0) {
                $upload_ids = explode(',', $pRec[0]['upload_id']);
                $upload = new Upload(DB_DIR, $_CONFIG['fileupload_size'], $_CONFIG['fileupload_location']);

                foreach ($upload_ids as $upload_id)
                    $upload->deleteFile($upload_id);
            }

            if (!isset($subtract_user_post_count[$pRec[0]['user_id']])) {
                $subtract_user_post_count[$pRec[0]['user_id']] = 1;
            } else
                $subtract_user_post_count[$pRec[0]['user_id']]++;

            $posts_tdb->delete('posts', $p_id, false);
        }

        while (list($user_id, $post_count) = each($subtract_user_post_count)) {
            $user = $tdb->get('users', $user_id);
            $tdb->edit('users', $user_id, array('posts' => (int)$user[0]['posts'] - $post_count));
        }

        $posts_tdb->delete('topics', $tRec[0]['id']);
        $fRec = $tdb->get('forums', $forumid);
        $tdb->edit('forums', $forumid, array('topics' => ((int)$fRec[0]['topics'] - 1), 'posts' => ((int)$fRec[0]['posts'] - count($p_ids))));
    }

    public static function removeRedirect($string) {
        $pos = strpos($string, '<meta');

        if ($pos !== false) {
            $pos2 = strpos($string, '>', ($pos+1));
            $string = substr($string, 0, $pos) . substr($string, ($pos2+1));
        }

        return $string;
    }

    public static function check_file($file)
    {
        //EXAMPLE OF HACK FILE
        //http://localhost/upb/admin_restore.php?action=download&file=../../../../../../../etc/passwd
        //echo $file;
        if (file_exists($file) && substr_count($file, '..') == 0)
            return true;
        else
            return false;
    }
}