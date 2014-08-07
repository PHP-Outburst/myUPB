<?php
/**
 *
 * @author J. Moore, Rouven Wachhaus <rouven@wachhaus.xyz>
 * @todo doc
 * @todo cc wrt variable names
 */
/**
 * Class ConfigSettings
 */
class ConfigSettings extends tdb
{
    private $_cache = array();  //cache the vars
    private $_cache_ext = array();

    function __construct()
    {
        $this->tdb(DB_DIR, 'main.tdb');
        $this->setFp('config', 'config');
        $this->setFp('ext_config', 'ext_config');
    }

    public function clearcache()
    {
        $this->_cache = array();
        $this->_cache_ext = array();
    }

    public function getVars($type, $returnOptionalData=false)
    {
        $return = array();

        if ($returnOptionalData) {
            if (isset($this->_cache_ext[$type]))
                return $this->_cache_ext[$type];

            $this->_cache_ext[$type] = $this->query('ext_config', 'type=\'' . $type . '\'');

            return $this->_cache_ext[$type];
        }

        if (isset($this->_cache[$type]))
            return $this->_cache[$type];

        $rawVars = $this->query('config', 'type=\'' . $type . '\'');

        foreach ($rawVars as $rawVar) {
            switch ($rawVar['data_type']) {
                case 'string':
                case 'text':
                default:
                    //do nothing
                    break;
                case 'bool':
                case 'boolean':
                    $rawVar['value'] = (($rawVar['value'] == '' || $rawVar['value'] == '0' || !$rawVar['value']) ? false : true);
                    break;
                case 'number':
                    $rawVar['value'] = (int)$rawVar['value'];
                    break;
            }
            $return[$rawVar['name']] = $rawVar['value'];
        }

        $this->_cache[$type] = $return;

        return $return;
    }

    public function editVars($type, $varArr, $editOptionalData = false)
    {
        //format for $varArr is array('var_name' => 'var_value', ...)
        //if($editOptionalData) format is how it is stored in the tdb, array(array('name' => $name, ...)...)
        if (!is_array($varArr)) {
            echo '<b>Warning:</b> second argument of editVars() must be an array.  (type: ' . $type . ')';

            return false;
        }

        $oriVars = $this->getVars($type, true);

        if ($editOptionalData) {
            $nameRef = array();

            for ($i = 0; $i < count($varArr); $i++) {
                $nameRef[$varArr[$i]['name']] = $varArr[$i];  //element 'value' is already in $varArr[$i]$varArr
            }
        }

        foreach ($oriVars as $oriVar) {
            if (!$editOptionalData && !isset($varArr[$oriVar['name']]))
                continue;
            elseif ($editOptionalData && !isset($nameRef[$oriVar['name']]))
                continue;

            if (isset($nameRef[$oriVar['name']]['value'])) {
                if (isset($nameRef[$oriVar['name']]['data_type']))
                    $data_type =& $nameRef[$oriVar['name']]['data_type'];
                else
                    $data_type =& $oriVar['data_type'];

                switch ($data_type) {
                    case 'number':
                        if ($editOptionalData) {
                            $nameRef[$oriVar['name']]['value'] = preg_replace('/[^0-9.-]/i', '', $nameRef[$oriVar['name']]['value']);
                        } else
                            $varArr[$oriVar['name']] = preg_replace('/[^0-9.-]/i', '', $varArr[$oriVar['name']]);

                        break;
                    case 'bool':
                    case 'boolean':
                        if ($editOptionalData) {
                            $nameRef[$oriVar['name']]['value'] = (($nameRef[$oriVar['name']]['value'] == ''
                                || $nameRef[$oriVar['name']]['value'] == '0' || $nameRef[$oriVar['name']]['value'] == false) ? '0' : '1');
                        } else
                            $varArr[$oriVar['name']] = (($varArr[$oriVar['name']] == '' || $varArr[$oriVar['name']] == '0' || $varArr[$oriVar['name']] == false) ? '0' : '1');

                    break;
                    case 'text':
                    case 'string':
                    default:
                        if ($editOptionalData)
                            $nameRef[$oriVar['name']]['value'] = stripslashes($nameRef[$oriVar['name']]['value']);
                        else
                            $varArr[$oriVar['name']] = stripslashes($varArr[$oriVar['name']]);

                        break;
                }
            }

            if ($editOptionalData) {
                if (isset($nameRef[$oriVar['name']]) && is_array($nameRef[$oriVar['name']])) {
                    $this->edit('ext_config', $oriVar['id'], array_diff_assoc($nameRef[$oriVar['name']], $oriVar), false);
                    $this->edit('config',     $oriVar['id'], array_diff_assoc($nameRef[$oriVar['name']], $oriVar), false);
                }
            } else {
                //if($varArr[$oriVar['name']] != '' && $varArr[$oriVar['name']] != $oriVar['value']) {
                if ($varArr[$oriVar['name']] != $oriVar['value']) { // Allow entries to be blank, otherwise how set blank announcement?
                    //echo 'Changing Value of '.$oriVar['name'].' from \'<i>'.htmlentities($oriVar['value']).'</i>\' to \'<i>'.htmlentities($varArr[$oriVar['name']]).'</i>\'<br>';
                    $this->edit('config', $oriVar['id'], array('value' => $varArr[$oriVar['name']]), false);
                    $this->edit('ext_config', $oriVar['id'], array('value' => $varArr[$oriVar['name']]), false);
                }
            }
        }

        return true;
    }

    public function deleteVar($varName)
    {
        $query = $this->query('config', 'name=\'' . $varName . '\'', 1, 1);

        if (!empty($query[0])) {
            parent::delete('config', $query[0]['id']);

            return parent::delete('ext_config', $query[0]['id']);
        }

        return false;
    }

    public function addVar($varName, $initialValue, $type, $dataOjbect, $formObject,  $category, $sort, $pageTitle, $pageDescription, $dataList = '')
    {
        $query = $this->query('config', 'name=\'' . $varName . '\'', 1, 1);

        if (!empty($query[0]))
            return false;

        $query = $this->query('ext_config', 'minicat=\'' . $category . '\'&&sort=\'' . $sort . '\'');

        if (!empty($query[0])) {
            //query sort-1 in order to bump on the exact match and higher
            $query = $this->query('ext_config', 'minicat=\'' . $category . '\'&&sort>\'' . ($sort - 1) . '\'');

            if (!empty($query[0])) {
                foreach($query as $r) {
                    if (empty($r))
                        continue;

                    $this->edit('ext_config', $r['id'], array('sort' => ($r['sort'] + 1)));
                }
            }
        }

        $recArr = array('name' => $varName,
                'value' => $initialValue,
                'type' => $type,
                'title' => $pageTitle,
                'description' => $pageDescription,
                'form_object' => $formObject,
                'data_type' => $dataOjbect,
                'data_list' => $dataList,
                'minicat' => $category,
                'sort' => $sort
        );
        parent::add('ext_config', $recArr);

        return parent::add('config', $recArr); //This is okay because elements in array NOT in the table are IGNORED
    }

    public function rename($oldVarName, $newVarName)
    {
        $query = $this->query('config', 'name=\'' . $oldVarName . '\'', 1, 1);

        if (!empty($query[0])) {
            $this->edit('config', $query[0]['id'], array('name' => $newVarName));

            return $this->edit('ext_config', $query[0]['id'], array('name' => $newVarName));
        }
        return false;
    }

    public function addCategory()
    {
        //Place Holder
    }

    //if $placeBeforeMiniCat == '', place at the end
    //if $placeBeforeMiniCat == '0', place at the beginning
    //else use fetchMiniCategories, and use the $sort of the minicat as the third argument
    public function addMiniCategory($title, $type, $placeBeforeMiniCat = '', $addingMoreMiniCats = true)
    {
        switch ($type) { //TEMPORARY $type validation,
            case 'config':
            case 'status':
            case 'regist':
                break;
            default:
                trigger_error('Invalid configVar type provided to addMiniCategory(' . $title . ')', E_USER_NOTICE);
                return false;
        }

        $title = str_replace(array(chr(29), chr(30), chr(31)), array('', '', ''), $title); //Make sure there isn't any record injection
        $file = file_get_contents(DB_DIR . '/config_org.dat');
        $raws = explode(chr(29), $file);
        $raws2 = explode(chr(31), rtrim($raws[1], chr(31)));
        $minicat_id = 1;

        foreach ($raws2 as $rawRec) {  // Find the next $minicat_id available
            list($cat_type, $id) = explode(chr(30), $rawRec, 3);
            if ($id >= $minicat_id)
                $minicat_id = $id + 1;
            if ($placeBeforeMiniCat == $id && $cat_type != $type) {
                trigger_error('The minicat_id(' . $placeBeforeMiniCat . ') provided as the third argument for addMiniCategory() does not belong to the same configVar type as the one added.(' . $title . ')', E_USER_NOTICE);

                return false;
            }
        }

        //Prepare $whereToWrite
        if ($placeBeforeMiniCat == '') {
            $whereToWrite = filesize(DB_DIR.'/config_org.dat');
            $raws2 = array_reverse($raws2);
        } else {
            if (($whereToWrite = strpos($file, chr(29))) === false)
                return false;

            $whereToWrite += 1; //for the chr(29)
        }

        // Find out where to write it
        foreach ($raws2 as $rawRec) {
            list($cat_type, $id) = explode(chr(30), $rawRec, 3);

            if ($cat_type == $type && // Check Conditions to BREAK out
                ($placeBeforeMiniCat == '' || // Place at the end
                $placeBeforeMiniCat == '0' || // Place at the beginning
                $id == $placeBeforeMiniCat) // Place in the middle
            )
                break;
            else { // Else Add/Subtract the record
                if ($placeBeforeMiniCat == '' // Place at the end
                    )
                    $whereToWrite -= strlen($rawRec) + 1; //1 for the chr(31);
                else // Place in the middle or beginning
                    $whereToWrite += strlen($rawRec) + 1; //1 for the chr(31);
            }
        }

        $rec = $type . chr(30) . $minicat_id . chr(30) . $title . chr(31);

        if ($addingMoreMiniCats)
            clearstatcache(); //FAILURE TO DO THIS RESULTS IN LOSS OF MINI-CATEGORIES

        $f = fopen(DB_DIR . '/config_org.dat', 'r+');
        fseek($f, $whereToWrite);
        $restOfFileSize = filesize(DB_DIR . '/config_org.dat') - $whereToWrite;

        if ($restOfFileSize > 0) {
            $restOfFile = fread($f, $restOfFileSize);
            fseek($f, $whereToWrite);
        } else
            $restOfFile = '';

        //Good tool to debug:
        //print '\n'.str_replace(array(chr(29), chr(30), chr(31)), array('&lt;29&gt;'.'\n', '&lt;30&gt;', '&lt;31&gt;'.'\n'), $tmp.$restOfFile);

        $success = fwrite($f, $rec.$restOfFile);
        fclose($f);

        return (($success === false) ? false : $minicat_id);
    }

    public function deleteCategory()
    {
        //Place Holder
    }

    public function deleteMiniCategory()
    {
        //Place Holder
    }

    public function renameCategory ($type, $title)
    {
        switch ($type) { //TEMPORARY $type validation,
            case 'config':
            case 'status':
            case 'regist':
                break;
            default:
                trigger_error('Invalid configVar type provided to addMiniCategory('.$title.')', E_USER_NOTICE);

                return false;
        }

        $title = str_replace(array(chr(29), chr(30), chr(31)), array('', '', ''), $title); //Make sure there isn't any record injection
        clearstatcache();
        $file = file_get_contents(DB_DIR . '/config_org.dat');
        $raws = explode(chr(29), $file);
        $raws2 = explode(chr(31), rtrim($raws[0], chr(31)));

        for ($i = 0, $c = count($raws2); $i < $c; $i++) {
            if(strpos($raws2[$i], $type . chr(30)) === false)
                continue;

            $raws2[$i] = $type . chr(30) . $title;
            $f = fopen(DB_DIR . '/config_org.dat', 'w');
            fwrite($f, implode(chr(31), $raws2) . chr(29) . $raws[1]);
            fclose($f);

            return true;
        }

        return false;
    }

    public function renameMiniCategory()
    {
        //Place Holder
    }

    public function fetchCategories()
    {
        $raws = explode(chr(29), file_get_contents(DB_DIR . '/config_org.dat'));
        $raws = explode(chr(31), rtrim($raws[0], chr(31)));
        $cats = array();

        foreach ($raws as $rawRec) {
            list($key, $title) = explode(chr(30), $rawRec, 2);
            $cats[$key] = $title;
        }

        return $cats;
    }

    public function fetchMiniCategories($category)
    {
        $raws = explode(chr(29), file_get_contents(DB_DIR . '/config_org.dat'));
        $raws = explode(chr(31), rtrim($raws[1], chr(31)));
        $minicats = array();

        foreach ($raws as $rawRec) {
            list($key, $id, $title) = explode(chr(30), $rawRec, 3);

            if ($key != $category)
                continue;

            $minicats[$id] = $title;
        }

        return $minicats;
    }
}
