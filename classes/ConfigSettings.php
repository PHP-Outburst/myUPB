<?php
/**
 * This file contains the ConfigSettings class.
 *
 * @author Jerroyd Moore
 * @author Rouven Wachhaus <rouven@wachhaus.xyz>
 * @license https://creativecommons.org/licenses/by-nc-sa/3.0/
 * @version 2.2.7
 */

/**
 * The ConfigSettings class manages access to the configuration storage within
 * the database. It is used to add, delete, and edit the variables
 * contained there.
 */
class ConfigSettings extends Tdb
{
    /** @var array the class-internal variable cache */
    private $cache = array();
    /** @var array the class-internal cache for optional/extended values */
    private $cacheExt = array();

    /**
     * Calls the constructor of the parent TextDB class and creates file pointers
     * to the 'config' and 'ext_config' tables.
     */
    function __construct()
    {
        parent::__construct(DB_DIR, 'main.tdb');
        $this->setFp('config', 'config');
        $this->setFp('ext_config', 'ext_config');
    }

    /**
     * Resets the instance's variable cache.
     */
    public function clearCache()
    {
        $this->cache = array();
        $this->cacheExt = array();
    }

    /**
     * Requests variables from the storage.
     *
     * Variables may either come from the instance's variable cache ($this->cache and
     * $this->cacheExt) or, if they haven't been stored there yet, from the 'config' and
     * 'ext_config' database tables.
     *
     * @param string $type
     * @param bool $returnOptionalData whether the extended/optional config storage
     *     should be queried. If false, the regular config storage is accessed
     *     (= they are mutually exclusive).
     * @return array the cached or queried variable data.
     */
    public function getVars($type, $returnOptionalData = false)
    {
        $return = array();

        // $this->cacheExt is only accessed when the optional data is requested
        if ($returnOptionalData) {
            if (isset($this->cacheExt[$type])) {
                return $this->cacheExt[$type];
            }

            $this->cacheExt[$type] = $this->query('ext_config', 'type=\'' . $type . '\'');
            return $this->cacheExt[$type];
        } else {
            if (isset($this->cache[$type])) {
                return $this->cache[$type];
            }

            $rawVars = $this->query('config', 'type=\'' . $type . '\'');

            // performs some additional conversions with regards to weak typing. Numbers are cast
            // to int, and empty strings/zeros/false are cast to boolean false.
            foreach ($rawVars as $rawVar) {
                switch ($rawVar['data_type']) {
                    case 'string':
                    case 'text':
                    default:
                        break;
                    case 'bool':
                    case 'boolean':
                        $rawVar['value'] =
                            (($rawVar['value'] == '' || $rawVar['value'] == '0' || !$rawVar['value']) ? false : true);
                        break;
                    case 'number':
                        $rawVar['value'] = (int)$rawVar['value'];
                        break;
                }

                $return[$rawVar['name']] = $rawVar['value'];
            }

            $this->cache[$type] = $return;
            return $return;
        }
    }

    /**
     * Edits variables from the config storage that already exist there.
     *
     * @param string $type
     * @param array $varArr the array of variable data that will be stored.
     *     Its format is array('var_name' => 'var_value', ...).
     * @param bool $editOptionalData whether the optional/extended variable
     *     storage will be accessed and edited. If false, the regular config
     *     storage is accessed (= they are mutually exclusive).
     * @return bool false if the parameter containing the variable data is
     *     not an array, true if method is executed successfully and didn't
     *     die on the way there.
     */
    public function editVars($type, $varArr, $editOptionalData = false)
    {
        // if($editOptionalData) format is how it is stored in the tdb, array(array('name' => $name, ...)...)
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
            if (!$editOptionalData && !isset($varArr[$oriVar['name']])) {
                continue;
            } elseif ($editOptionalData && !isset($nameRef[$oriVar['name']])) {
                continue;
            }

            if (isset($nameRef[$oriVar['name']]['value'])) {
                if (isset($nameRef[$oriVar['name']]['data_type'])) {
                    $data_type =& $nameRef[$oriVar['name']]['data_type'];
                } else {
                    $data_type =& $oriVar['data_type'];
                }

                switch ($data_type) {
                    case 'number':
                        if ($editOptionalData) {
                            $nameRef[$oriVar['name']]['value'] = preg_replace('/[^0-9.-]/i', '',
                                $nameRef[$oriVar['name']]['value']);
                        } else {
                            $varArr[$oriVar['name']] = preg_replace('/[^0-9.-]/i', '', $varArr[$oriVar['name']]);
                        }

                        break;
                    case 'bool':
                    case 'boolean':
                        if ($editOptionalData) {
                            $nameRef[$oriVar['name']]['value'] = (($nameRef[$oriVar['name']]['value'] == ''
                                || $nameRef[$oriVar['name']]['value'] == '0'
                                || $nameRef[$oriVar['name']]['value'] == false) ? '0' : '1');
                        } else {
                            $varArr[$oriVar['name']] = (($varArr[$oriVar['name']] == ''
                                || $varArr[$oriVar['name']] == '0' || $varArr[$oriVar['name']] == false) ? '0' : '1');
                        }

                    break;
                    case 'text':
                    case 'string':
                    default:
                        if ($editOptionalData) {
                            $nameRef[$oriVar['name']]['value'] = stripslashes($nameRef[$oriVar['name']]['value']);
                        } else {
                            $varArr[$oriVar['name']] = stripslashes($varArr[$oriVar['name']]);
                        }

                        break;
                }
            }

            if ($editOptionalData) {
                if (isset($nameRef[$oriVar['name']]) && is_array($nameRef[$oriVar['name']])) {
                    $this->edit('ext_config', $oriVar['id'],
                        array_diff_assoc($nameRef[$oriVar['name']], $oriVar), false);
                    $this->edit('config', $oriVar['id'],
                        array_diff_assoc($nameRef[$oriVar['name']], $oriVar), false);
                }
            } else {
                if ($varArr[$oriVar['name']] != $oriVar['value']) {
                    $this->edit('config', $oriVar['id'], array('value' => $varArr[$oriVar['name']]), false);
                    $this->edit('ext_config', $oriVar['id'], array('value' => $varArr[$oriVar['name']]), false);
                }
            }
        }

        return true;
    }

    /**
     * Deletes a variable from the configuration storage.
     *
     * This is basically an abstraction on top of Tdb::delete(), which just deletes a record
     * from a table. The only value this method adds is that it specifies the 'config' and
     * 'ext_config' tables.
     *
     * NB: this method is defective by design. There's no way to specify deletion from the
     * 'ext_config' storage. Yet it is assumed that the variable will exist in both the
     * regular and the extended storage (because only the result from delete('ext_config')
     * is returned). Meaning that if a variable is deleted, the return value might be false
     * even if everythng went right (because it doesn't necessarily have to exist in
     * 'ext_config' as well!).
     *
     * @param string $varName the name of the variable that will be deleted
     * @return bool true if the variable was successfully deleted, false if there was a problem
     * @todo see note - severely flawed!
     */
    public function deleteVar($varName)
    {
        $query = $this->query('config', 'name=\'' . $varName . '\'', 1, 1);

        if (!empty($query[0])) {
            parent::delete('config', $query[0]['id']);

            return parent::delete('ext_config', $query[0]['id']);
        }

        return false;
    }

    /**
     * Adds a variable to the configuration variable storage
     *
     * @param $varName
     * @param $initialValue
     * @param $type
     * @param $dataObject
     * @param $formObject
     * @param $category
     * @param $sort
     * @param $pageTitle
     * @param $pageDescription
     * @param string $dataList
     * @return bool
     * @todo doc
     */
    public function addVar($varName, $initialValue, $type, $dataObject, $formObject,  $category, $sort, $pageTitle,
                           $pageDescription, $dataList = '')
    {
        $query = $this->query('config', 'name=\'' . $varName . '\'', 1, 1);

        if (!empty($query[0])) {
            return false;
        }

        $query = $this->query('ext_config', 'minicat=\'' . $category . '\'&&sort=\'' . $sort . '\'');

        if (!empty($query[0])) {
            //query sort-1 in order to bump on the exact match and higher
            $query = $this->query('ext_config', 'minicat=\'' . $category . '\'&&sort>\'' . ($sort - 1) . '\'');

            if (!empty($query[0])) {
                foreach($query as $r) {
                    if (empty($r)) {
                        continue;
                    }

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
                'data_type' => $dataObject,
                'data_list' => $dataList,
                'minicat' => $category,
                'sort' => $sort
        );
        parent::add('ext_config', $recArr);
        return parent::add('config', $recArr); //This is okay because elements in array NOT in the table are IGNORED
    }

    /**
     * Rename a variable in the configuration variable storage.
     *
     * @param string $oldVarName the name of the variable to be renamed
     * @param string $newVarName the new name of the variable
     * @return bool true if the renaming was successful, false if not
     */
    public function rename($oldVarName, $newVarName)
    {
        $query = $this->query('config', 'name=\'' . $oldVarName . '\'', 1, 1);

        if (!empty($query[0])) {
            $this->edit('config', $query[0]['id'], array('name' => $newVarName));

            return $this->edit('ext_config', $query[0]['id'], array('name' => $newVarName));
        }

        return false;
    }

    /**
     * STUB
     */
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

        //Make sure there isn't any record injection
        $title = str_replace(array(chr(29), chr(30), chr(31)), array('', '', ''), $title);
        $file = file_get_contents(DB_DIR . '/config_org.dat');
        $raws = explode(chr(29), $file);
        $raws2 = explode(chr(31), rtrim($raws[1], chr(31)));
        $minicat_id = 1;

        foreach ($raws2 as $rawRec) {  // Find the next $minicat_id available
            list($cat_type, $id) = explode(chr(30), $rawRec, 3);
            if ($id >= $minicat_id) {
                $minicat_id = $id + 1;
            }

            if ($placeBeforeMiniCat == $id && $cat_type != $type) {
                trigger_error('The minicat_id(' . $placeBeforeMiniCat . ') provided as the third argument
                    for addMiniCategory() does not belong to the same configVar type as the one added.(' . $title . ')',
                    E_USER_NOTICE);

                return false;
            }
        }

        //Prepare $whereToWrite
        if ($placeBeforeMiniCat == '') {
            $whereToWrite = filesize(DB_DIR.'/config_org.dat');
            $raws2 = array_reverse($raws2);
        } else {
            if (($whereToWrite = strpos($file, chr(29))) === false) {
                return false;
            }

            $whereToWrite += 1; //for the chr(29)
        }

        // Find out where to write it
        foreach ($raws2 as $rawRec) {
            list($cat_type, $id) = explode(chr(30), $rawRec, 3);

            if ($cat_type == $type && // Check Conditions to BREAK out
                ($placeBeforeMiniCat == '' || // Place at the end
                $placeBeforeMiniCat == '0' || // Place at the beginning
                $id == $placeBeforeMiniCat) // Place in the middle
            ) {
                break;
            } else { // Else Add/Subtract the record
                if ($placeBeforeMiniCat == '') { // Place at the end
                    $whereToWrite -= strlen($rawRec) + 1; //1 for the chr(31);
                } else { // Place in the middle or beginning
                    $whereToWrite += strlen($rawRec) + 1; //1 for the chr(31);
                }
            }
        }

        $rec = $type . chr(30) . $minicat_id . chr(30) . $title . chr(31);

        if ($addingMoreMiniCats) {
            clearstatcache(); //FAILURE TO DO THIS RESULTS IN LOSS OF MINI-CATEGORIES
        }

        $f = fopen(DB_DIR . '/config_org.dat', 'r+');
        fseek($f, $whereToWrite);
        $restOfFileSize = filesize(DB_DIR . '/config_org.dat') - $whereToWrite;

        if ($restOfFileSize > 0) {
            $restOfFile = fread($f, $restOfFileSize);
            fseek($f, $whereToWrite);
        } else {
            $restOfFile = '';
        }

        $success = fwrite($f, $rec.$restOfFile);
        fclose($f);
        return (($success === false) ? false : $minicat_id);
    }

    /**
     * STUB
     */
    public function deleteCategory()
    {
        //Place Holder
    }

    /**
     * STUB
     */
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

        //Make sure there isn't any record injection
        $title = str_replace(array(chr(29), chr(30), chr(31)), array('', '', ''), $title);
        clearstatcache();
        $file = file_get_contents(DB_DIR . '/config_org.dat');
        $raws = explode(chr(29), $file);
        $raws2 = explode(chr(31), rtrim($raws[0], chr(31)));

        for ($i = 0, $c = count($raws2); $i < $c; $i++) {
            if (strpos($raws2[$i], $type . chr(30)) === false) {
                continue;
            }

            $raws2[$i] = $type . chr(30) . $title;
            $f = fopen(DB_DIR . '/config_org.dat', 'w');
            fwrite($f, implode(chr(31), $raws2) . chr(29) . $raws[1]);
            fclose($f);
            return true;
        }

        return false;
    }

    /**
     * STUB
     */
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

            if ($key != $category) {
                continue;
            }

            $minicats[$id] = $title;
        }

        return $minicats;
    }
}
