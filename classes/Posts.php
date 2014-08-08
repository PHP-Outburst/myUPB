<?php
/**
 *
 * @author Jerroyd Moore, Rouven Wachhaus <rouven@wachhaus.xyz>
 * @todo doc
 * @todo cc wrt variable/method names
 */
/**
 * Class Posts
 */
class Posts extends tdb
{
    //declare vars
    private $tRec;
    private $fRec;
    private $user = array();

    function __construct($dir, $db)
    {
        $this->tdb($dir, $db);
    }

    //Check Functions
    public function set_topic($tRec)
    {
        $this->tRec = $tRec;
    }

    public function set_forum($fRec)
    {
        $this->fRec = $fRec;
    }

    public function set_user_info($username, $password, $power, $id)
    {
        if ($power == 0) {
            $username = 'guest';
            $password = 'password';
            $id = '0';
        }

        $this->user = array('username' => $username, 'password' => $password, 'power' => $power, 'id' => $id);
    }

    //Development Purposes
    public function varDump()
    {
        echo '<pre><b>$tRec:</b><br>';
        var_dump($this->tRec);
        echo '<br><br><b>$fRec:</b><br>';
        var_dump($this->fRec);
        echo '<br><br><b>\$user:</b><br>';
        var_dump($this->user);
        echo '</pre>';
    }

    public function check_user_info()
    {
        if ($this->user['username'] == '' || !isset($this->user['username']))
            return false;

        if ($this->user['password'] == '' || !isset($this->user['password']))
            return false;

        if ($this->user['power'] == '' || !isset($this->user['power']))
            return false;

        if ($this->user['id'] == '' || !isset($this->user['id']))
            return false;

        return true;
    }

    public function check_forum()
    {
        if ($this->fRec[0]['id'] == '' || !isset($this->fRec[0]['id']))
            return false;

        return true;
    }

    public function check_topic()
    {
        if ($this->tRec[0]['id'] == '' || !isset($this->tRec[0]['id']))
            return false;

        //if($this->tRec[0]['p_ids'] == '') return false;
        return true;
    }
    // end check functions

    public function d_topic($p, $page, $num_pages)
    {
        if (!$this->check_user_info())
            return false;

        echo $this->d_posting($p, $page, $num_pages, 'top', 'forum');
        echo '<div class="tabstyle_1"><ul>';

        if ((int)$this->user['power'] >= (int)$this->fRec[0]['reply']) {
            echo '<li><a href="newpost.php?id=' . $this->fRec[0]['id'] . '&amp;t=1&amp;t_id=" title="Create a new topic?"><span>Create New Topic</span></a></li>';
        } else {
            echo '<li></li>';
        }

        echo '</ul></div><div style="clear:both;"></div>';

        return true;
    }

    public function d_posting($email_mode, $is_watching, $page_string, $page,$num_pages, $position = 'top', $type = 'topic')
    {
        if (($type == 'topic' && !$this->check_topic()) || !$this->check_forum() || !$this->check_user_info()) {
            return false;
        }

        $output = '';

        if ($num_pages != 1) {
            $output .= '<table><tr><td class="pagination_title">Pages (' . $num_pages . '):</td>' . $page_string . '</tr></table><div style="clear:both;"></div>';
        }

        if ($position == 'top' && $type=='topic') {
            $output .= '<div class="tabstyle_1"><ul>';

            if ((int)$this->user['power'] >= (int)$this->fRec[0]['post']) {
                $output .= '<li><a href="newpost.php?id=' . $this->fRec[0]['id'] . '&amp;t=1&amp;t_id=" title="Create a new topic?"><span>Create New Topic</span></a></li>';
            } elseif ((int)$this->user['power'] == 0) {
                $output .= '<li><a href="login.php?ref=' . urlencode('newpost.php?id=' . $this->fRec[0]['id'] . '&amp;t=1&amp;t_id=') . '" title="Create a new topic?"><span>Create New Topic</span></a></li>';
            }

            if ((int)$this->user['power'] >= (int)$this->fRec[0]['reply']) {
                if (!(bool)$this->tRec[0]['locked'])
                    $output .= '<li><a href="newpost.php?id=' . $this->fRec[0]['id'] . '&amp;t=0&amp;t_id=' . $this->tRec[0]['id'] . '&amp;page=' . $page . '" title="Add a reply?"><span>Add Reply</span></a></li>';
                else
                    $output .= '<li><a href="#" title="Topic Is Locked"><span>Topic Is Locked</span></a></li>';
            } elseif ((int)$this->user['power'] == 0) {
                if (!(bool)$this->tRec[0]['locked'])
                    $output .= '<li><a href="login.php?ref=' . urlencode('newpost.php?id=' . $this->fRec[0]['id'] . '&amp;t=0&amp;t_id=' . $this->tRec[0]['id'] . '&amp;page=' . $page) . '" title="Add a reply?"><span>Add Reply</span></a></li>';
                else
                    $output .= '<li><a href="#" title="Topic Is Locked"><span>Topic Is Locked</span></a></li>';
            }

            if ((int)$this->user['power'] > 0) {
                if ($email_mode) {
                    $msg = 'Watch';

                    if ($is_watching)
                        $msg = 'Un-Watch';

                    $output .= '<li><a href="managetopic.php?action=watch&amp;id=' . $this->fRec[0]['id'] . '&amp;t_id=' . $this->tRec[0]['id'] . '&amp;page=' . $_GET['page'] . '" title="' . $msg . 'This Topic?"><span>' . $msg . ' Topic</span></a></li>';
                }

                //$output .= '<li><a href="managetopic.php?action=favorite&amp;id=' . $this->fRec[0]['id'] . '&amp;t_id=' . $this->tRec[0]['id'] . '&amp;page=' . $_GET['page'] . '" title="Bookmark this Topic?"><span>Bookmark Topic</span></a></li>';
            } else {
                if ($email_mode) {
                    $msg = 'Watch';
                    $output .= '<li><a href="login.php?ref=' . urlencode('managetopic.php?action=watch&amp;id=' . $this->fRec[0]['id'] . '&amp;t_id=' . $this->tRec[0]['id'] . '&amp;page=' . $_GET['page']) . '" title="' . $msg . ' This Topic?"><span>$msg Topic</span></a></li>';
                }

                //$output .= '<li><a href="login.php?ref=' . urlencode('managetopic.php?action=favorite&id=' . $this->fRec[0]['id'] . '&t_id=' . $this->tRec[0]['id'] . '&page=' . $_GET['page']) . '" title="Bookmark this Topic?"><span>Bookmark Topic</span></a></li>';
            }

            if ((int)$_COOKIE['power_env'] >= 2) {
                $output .= '<li><a href="managetopic.php?id=' . $this->fRec[0]['id'] . '&amp;t_id=' . $this->tRec[0]['id'] . '"><span>Options</span></a></li>';
            }

            $output .= '</ul></div>';
        } else {
            $output .= '<div style="clear:both;"></div>';
        }

        return $output;
    }

    public function getPosts($fp, $start = 0, $howmany = -1)
    {
        if (!$this->check(__LINE__) || !$this->check_topic())
            return false;

        $header = array();
        $this->readHeader($fp, $header);
        $f = fopen($this->fp[$fp] . '.ta', 'r');
        $p_ids = explode(',', $this->tRec[0]['p_ids']);
        $return = array();
        $tmp = array();

        foreach ($p_ids as $p_id) {
            if ($start > 0) {
                $start--;
                continue;
            }
            if ($howmany == 0) {
                break;
                continue;
            }

            if (($fileId = $this->fileIdById($fp, $p_id)) === false && substr_count($_SERVER['PHP_SELF'],'managetopic') != 1) {
                echo '<b><font color="red">ERROR</font></b>: Unable to find the p_id ' . $p_id. '(\$p_ids = <br />';
                print_r($p_ids);
                echo ') <br />';
                continue;
            }

            if(($seekto = $this->bytesToSeek($fp, $header, $fileId)) === false) {
                echo 'tdb::bytestoseek() failed in posts::getPosts()...';
                continue;
            }

            fseek($f, $seekto);
            $return[] = $this->parseRecord($fp, fread($f, $header['recLen']), $header);
            $howmany--;
        }

        fclose($f);

        return $return;
    }
}