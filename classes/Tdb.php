<?php
/**
 *
 * @author Rouven Wachhaus <rouven@wachhaus.xyz>
 * @todo doc
 * @todo cc wrt variable/method names
 * @todo cc in general from fileIdById() on
 * @todo original author?
 */
/**
 * Class Tdb
 */
class Tdb
{
    const TDB_PRINT_ERRORS = false; //TDB will print errors instead of using trigger_error() or a user defined error handler
    const TDB_ERROR_INCLUDE_ORIGIN = true; //TDB will include the file and line number of your script that led to the error

	var $fp = array();     // allowing for multiply file pointers
	var $workingDir;       // working directory
	var $Db;               // database.tdb
	var $Tables;           // list of tables in the database
	var $error_handler = false; // user defined error handler

	var $_header = array(); // cache publics
	var $_query = array();
	var $_fileId = array();
	var $_ref = array();
	var $_firstBlankMemoBlockRef = array();

	//Does not store FPs but physical file addy, does not clear for cleanUp() etc.
	//prompts statsclearcache()
	var $editedTable = array();

	/*
	 Functions:
	 tdb($dir,$db) - the working directory, must be a valid directory, must be set before anything else is used. Format /path/to/folder/ (note the end slash)

	 createDatabase($dir, $filename) - .tdb is appended automatically if you did not add it
	 removeDatabase() - it will remove the database as well as ALL the tables, be careful

	 createTable($fp,array(array(fieldname, type, size)), memo_block_size) - creates a table using $fp and the field names
	 removeTable($tableName) - removes a table from a database

	 addField($fp, array(fieldname, type, size)) - creates a new field in the table
	 editField($fp, $field, array(newname, newtype, newsize)) - edits an existing field
	 removeField($fp, $field) - removes a field from a table

	 reBuild($fp) - rebuilds the database after any editing
	 sortAndBuild($fp, $fieldName, $direction="ASC") - sorts the records and rebuilds the table

	 basicQuery($fp, $field, $value, $start=1, $howmany=-1, $fields) - searches for $value in $field
	 query($fp, $query[[[, $start], $howmany], $fields]) - runs a query, syntax will be documented
	 listRec($fp, $start[[, $howmany], $fields]) - returns a specific amount of records
	 get($fp, $id, $fields) - returns the record with the id of $id (file id from table.ref)

	 getNumberOfRecords() - returns the number of records in the table
	 getTableList() - returns a list of avaiable tables in the database
	 getFieldList($fp) - returns a list of all the fields in a table

	 add($fp, array("fieldname" => "value", ...)) - add a record
	 edit($fp, $id, array("fieldname" => "newvalue")[, $needRWMemo) - edits a record
	 delete($fp, $id) - deletes a record

	 readMemo($fp, $index, $header) - retrieves memo data in $fp starting from $index
	 writeMemo($fp, $data, $header) - writes in memo file of $fp
	 deleteMemo($fp, $index, $header) - deletes memo data in $fp, using $index as a referance
	 isTable($table) - returns true if $table is in $db
	 fileIdById($fp, $id) - gets the file id by the record id
	 parseRecord($rawRecord, $header) - parses a raw record
	 readHeader() - reads the header.
	 cleanUp() - releases all the fp vars
	 setFp($fp, $table) - sets a filepointer up
	 check() - validates Db, workingDir, tables, and $fp
	 sendError($errMsg) - sends a error message
	 define_error_handler(&$object, $function) - Defines an error handler
	 version() - returns the version
	 */


	/**
	 * Defines the directory and database.  Must be ran before most functions can function.
	 *
	 * @param string $dir
	 * @param string $db
	 * @return bool
	 */
	function __construct($dir = '', $db='')
    {
		if ($dir == "" && $db == "")
            return false;

		$dir = str_replace("../", "", $dir);

		if (substr($dir, -1) != "/")
            $dir .= "/";

        if (substr($db, -4) != '.tdb')
            $db .= '.tdb';

        if (is_dir($dir)) {
			if (!is_writable($dir)) {
				$this->sendError(E_ERROR, "Fatal: Working directory ($dir) is not writable, try chmod 777 if 666 does not work.", __LINE__);
			} else {
				if (is_writable($dir.$db)) {
					if (filesize($dir.$db) != 0) {
						$f = fopen($dir.$db, "rb");
						@$this->Tables = trim(@fread($f, filesize($dir.$db)));
						fclose($f);
						$this->Tables = explode("\n", $this->Tables);
					} else
                        $this->Tables = array();

                    $this->Db = $db;
					$this->workingDir = $dir;

                    return true;
				} else {
					$this->sendError(E_ERROR, "Fatal: Database ($db) is not writable, try chmod 777 if 666 does not work.", __LINE__);

                    return false;
				}
			}
		} else {
			$this->sendError(E_USER_ERROR, "Failed setting working directory ($dir), not a valid path.", __LINE__);

            return false;
		}
		return true;
	}

	/**
	 * Creates a database.  Run tdb() before handling the database
	 *
	 * @param string $dir
	 * @param string $name
	 * @return bool
	 */
	public function createDatabase($dir, $filename)
    {
		$dir = str_replace("../", "", $dir);

        if (substr($dir, -1) != "/") {
			$dir .= "/";
		}

		if (substr($filename, -4) != ".tdb") {
			$filename .= ".tdb";
		}

		if (file_exists($dir.$filename)) {
			$this->sendError(E_WARNING, "Database already exists", __LINE__);

            return false;
		}

		if (is_dir($dir)) {
			if (!is_writable($dir)) {
				$this->sendError(E_ERROR, "Fatal: $dir is not writable, try chmod 777 if 666 does not work.", __LINE__);

                return false;
			} else {
				$f = fopen($dir.$filename, "wb");
				fwrite($f, "");
				fclose($f);

                return true;
			}
		} else {
			$this->sendError(E_ERROR, "Failed creating database, $dir is not a valid path.", __LINE__);

            return false;
		}
	}

	/**
	 * Deletes the database as well as its tables
	 *
	 * @return bool
	 */
	public function removeDatabase()
    {
		if ($this->check(__LINE__) === false)
            return false;

        foreach ($this->Tables as $ta) {
			if (trim($ta) != "")
                $this->removeTable($ta);
		}

		unlink($this->workingDir.$this->Db);

        return true;
	}

	/**
	 * Deletes a table managed be the defined database.
	 *
	 * @param string $tableName
	 * @return bool
	 */
	public function removeTable($tableName)
    {
		if($this->check(__LINE__) === false)
            return false;

		if (substr($tableName, 0, (strlen($this->Db)-4)) != substr($this->Db, 0, -4))
            $tableName = substr($this->Db, 0, -4) . '_' . $tableName;

        if (!$this->isTable($tableName)) {
			$this->sendError(E_WARNING, "Table ($tableName) does not exist in the database.", __LINE__);

            return false;
		}

		foreach ($this->Tables as $key => $ta) {
			if ($ta == $tableName) {
				unset($this->Tables[$key]);
				$f = fopen($this->workingDir . $this->Db, 'wb');
				flock($f, 2);
				fwrite($f, implode("\n", $this->Tables));
				flock($f, 3);
				fclose($f);
			}
		}

		unlink($this->workingDir . $tableName . '.ta');
		unlink($this->workingDir . $tableName . '.memo');
		unlink($this->workingDir . $tableName . '.ref');

		return true;
	}

	/**
	 * Returns the number of records.
	 *
	 * @param string $fp
	 * @return int count on success, bool false on fail
	 */
	public function getNumberOfRecords($fp)
    {
		if ($this->check(__LINE__, $fp) === false)
            return false;

		/*$header = array();
		 $this->readHeader($fp, $header);

		 $eRecSize = filesize($this->fp[$fp].'.ta') - $header["recPos"];
		 $eRecCount = $eRecSize / $header["recLen"];

		 $delete = 0;
		 $next = $header['lastBlank'];
		 $f = fopen($this->fp[$fp].'.ta', 'rb');
		 while($next != -1) {
		 $delete++;
		 fseek($f, $this->bytesToSeek($fp, $header, $next));
		 $next = (int)trim(fread($f, 8), chr(24));
		 }
		 $eRecCount = $eRecCount - $delete;

		 return $eRecCount; */
		//Shorter version?
		return substr_count($this->get_ref_data($fp), chr(31));
	}

	/**
	 * Returns the names of tables.
	 *
	 * @return array tables on success, bool false on fail
	 */
	public function getTableList()
    {
		if ($this->check(__LINE__) === false)
            return false;

        return $this->Tables;
	}

	/**
	 * Retrieves the list of fields of a table and their parameters
	 *
	 * @param string $fp
	 * @return array fields on success, bool false on fail
	 */
	public function getFieldList($fp)
    {
		if ($this->check(__LINE__, $fp) === false)
            return false;

		$header = array();
		$this->readHeader($fp, $header);
		$returnArr = array();
		$cHeader = count($header) - 8;

        for ($i = 1; $i <= $cHeader; $i++) {
			$returnArr[] = $header[$i];
		}

		return $returnArr;
	}

	/**
	 * Retrieves the headers.
	 *
	 * @param string $fp
	 * @param array $header
	 * @return bool
	 */
	public function readHeader($fp, &$header)
    {
		if ($this->check(__LINE__, $fp) === false)
            return false;

		if (isset($this->_header[$fp])) {
			$header = $this->_header[$fp];

            return true;
		}

		$header = array();
		$f = fopen($this->fp[$fp] . '.ta', 'rb');

		if (!$f)
            return false;

		$header["headLen"] = "";

        while (!feof($f)) {
			$next = fgetc($f);

            if (ord($next) == 28)
                break;
			else
                $header["headLen"] .= $next;
		}

		if (is_numeric($header['headLen']))
            $header['headLen'] = (int)$header['headLen'];

		$header["raw"] = fread($f, $header["headLen"]);
		$header["fieldsraw"] = explode(chr(29), $header["raw"]);

        for ($i = 0; $i < count($header["fieldsraw"]); $i++) {
			$tmp = explode(chr(31), $header["fieldsraw"][$i]);
			$header[$tmp[0]]["fName"] = $tmp[3];
			$header[$tmp[0]]["fType"] = $tmp[1];
			$header[$tmp[0]]["fLength"] = (int)$tmp[2];
			unset($tmp);
		}

		unset($header["raw"], $header["fieldsraw"]);
		$header["recLen"] = "";

        while (!feof($f)) {
			$next = fgetc($f);

            if (ord($next) == 28)
                break;
			else
                $header["recLen"] .= $next;
		}

		if (is_numeric($header['recLen']))
            $header['recLen'] = (int)$header['recLen'];

		$header["curId"] = "";
		$header["idPos"] = ftell($f);

        while (!feof($f)) {
			$next = fgetc($f);

            if (ord($next) == 28)
                break;
			else
                $header["curId"] .= $next;
		}

		$header["curId"] = (int)trim($header["curId"]);
		$header["lastBlank"] = "";
		$header["blankPos"] = ftell($f);

        while (!feof($f)) {
			$next = fgetc($f);

            if (ord($next) == 28)
                break;
			else
                $header["lastBlank"] .= $next;
		}

		$header["lastBlank"] = (int)trim($header["lastBlank"]);
		$header["blockLength"] = "";

        while (!feof($f)) {
			$next = fgetc($f);

            if (ord($next) == 28)
                break;
			else
                $header["blockLength"] .= $next;
		}

		if (is_numeric($header['blockLength']))
            $header['blockLength'] = (int)$header['blockLength'];

		$header["recPos"] = ftell($f);
		fclose($f);
		$this->_header[$fp] = $header;

		return true;
	}

	/**
	 * Sets a filepointer to a table.  Necessary for most functions.
	 *
	 * @param string $fp
	 * @param string $table
	 * @return bool
	 */
	public function setFp($fp, $table)
    {
		if (substr($table, 0, (strlen($this->Db)-4)) != substr($this->Db, 0, -4))
            $table = substr($this->Db, 0, -4) . '_' . $table;

        if ($this->isTable($table)) {
			$this->fp[$fp] = $this->workingDir . $table;
			unset($this->_header[$fp]);
			unset($this->_query[$fp]);
			unset($this->_fileId[$fp]);
			unset($this->_ref[$fp]);
			unset($this->_firstBlankMemoBlockRef[$fp]);
		} else {
			$this->sendError(E_WARNING, "$table is not a valid table in $this->Db", __LINE__);

            return false;
		}

		return true;
	}

	/**
	 * Clears cached information on tables aswell as filepoints ($fp)
	 *
	 */
	public function cleanUp()
    {
		$this->fp = array();
		$this->_header = array();
		$this->_query = array();
		$this->_fileId = array();
		$this->_ref = array();
		$this->_firstBlankMemoBlockRef = array();
	}

	/**
	 * Determines if the table exists in the defined database
	 *
	 * @param string $table
	 * @return bool
	 */
	public function isTable($table)
    {
		if ($this->check(__LINE__) === false)
            return false;

        if (substr($table, 0, (strlen($this->Db) -4)) != substr($this->Db, 0, -4))
            $table = substr($this->Db, 0, -4) . '_' . $table;


        foreach ($this->Tables as $ta) {
			if ($ta == $table)
                return true;
		}

		return false;
	}

	/**
	 * Creates a table.
	 *
	 * @param string $table
	 * @param array $fields
	 * @param int $block_length[optional]
	 * @return bool
	 */
	public function createTable($table, $fields, $block_length = "100")
    {
		if ($this->check(__LINE__) === false)
            return false;

        $block_length = $block_length + 8; //To store the next fp of every block + end of text chr

        if (substr($table, 0, (strlen($this->Db) -4) != substr($this->Db, 0, -4)))
            $table = substr($this->Db, 0, -4) . '_' . $table;

		if (!is_array($fields)) {
			$this->sendError(E_USER_ERROR, "\$fields must be an array", __LINE__);

            return false;
		} else {
			// check if table already exists
			if (file_exists($this->workingDir.$table) || in_array($table, $this->Tables)) {
				$this->sendError(E_WARNING, "Table ($this->workingDir$table) already exists", __LINE__);

                return false;
			}

			// start building the header
			$h_fields = array();
			$h_recLen = 0;

			for ($i = 0; $i < count($fields); $i++) {
				if ($fields[$i][1] != "string" && $fields[$i][1] != "number" && $fields[$i][1] != "memo" && $fields[$i][1] != "id") {
					$this->sendError(E_USER_ERROR, "Field type must be either string, number, memo, or id.", __LINE__);

                    return false;
				}

				if ($fields[$i][1] == "id" || $fields[$i][1] == 'memo')
                    $fields[$i][2] = "7";
				//if($fields[$i][1] == "memo") $fields[$i][2] = "10"; //memo functions only use 7 chars

				$h_fields[] = ($i + 1) . chr(31) . $fields[$i][1] . chr(31) . $fields[$i][2] . chr(31) . $fields[$i][0];
				$h_recLen += $fields[$i][2];
			}

			$h_fields = implode(chr(29), $h_fields);
			$h_cid =       "0      ";
			$h_lastBlank = "-1     ";
			$header = strlen($h_fields) . chr(28) . $h_fields . $h_recLen . chr(28) . $h_cid . chr(28) . $h_lastBlank . chr(28) . $block_length . chr(28);

			// write the table header
			$f = fopen($this->workingDir . $table . '.ta', 'wb');
			fwrite($f, $header);
			fclose($f);
			$f = fopen($this->workingDir . $table . '.memo', 'wb');
			fwrite($f, '-1'.str_repeat(' ', 5).str_repeat(' ', ($block_length - 7))); //This is the memo header aswell as block #0.
			fclose($f);
			$f = fopen($this->workingDir . $table . '.ref', 'wb');
			fwrite($f, "");
			fclose($f);

			// write the table in the database
			$this->Tables[] = $table;
			$f = fopen($this->workingDir . $this->Db, 'wb');
			fwrite($f, implode("\n", $this->Tables));
			fclose($f);

			return true;
		}
	}

	/**
	 * Adds another field to a table
	 *
	 * @param string $fp
	 * @param array $field
	 * @return bool
	 */
	public function addField($fp, $field)
    {
		if ($this->check(__LINE__, $fp) === false)
            return false;

		$header = array();
		$this->readHeader($fp, $header);
		$cHeader = count($header) - 8;

		for ($i = 1; $i <= $cHeader; $i++) {
			if ($header[$i]["fName"] == $field[0]) {
				$this->sendError(E_USER_WARNING, "Field already exists, aborting...", __LINE__);

                return false;
			}
		}

		$cHeader += 1; // for the new field

		//name, type, size
		if ($field[1] != "string" && $field[1] != "number" && $field[1] != "memo" && $field[1] != "id") {
			$this->sendError(E_USER_ERROR, "Field type must be either string, number, memo, or id.", __LINE__);

            return false;
		}

		if ($field[1] == "id" || $field[1] == "memo")
            $field[2] = "7";
		//if($field[1] == "memo") $field[2] = "10";

		$header[$cHeader]["fName"] = $field[0];
		$header[$cHeader]["fType"] = $field[1];
		$header[$cHeader]["fLength"] = $field[2];

		// start building the header
		$h_fields = array();
		$h_recLen = 0;

		for ($i = 1; $i <= $cHeader; $i++) {
			$h_fields[] = $i . chr(31) . $header[$i]["fType"] . chr(31) . $header[$i]["fLength"] . chr(31) . $header[$i]["fName"];
			$h_recLen += $header[$i]["fLength"];
		}

		$h_fields = implode(chr(29), $h_fields);
		$h_cid = $header["curId"].str_repeat(" ", 7 - strlen($header["curId"]));
		$h_lastBlank = $header["lastBlank"].str_repeat(" ", 7 - strlen($header["lastBlank"]));

		$h_header = strlen($h_fields) . chr(28) . $h_fields . $h_recLen . chr(28) . $h_cid . chr(28) . $h_lastBlank . chr(28) . $header["blockLength"] . chr(28);

		// rebuild the table with the new field
		$eRecSize = filesize($this->fp[$fp] . '.ta') - $header["recPos"];
		$eRecCount = $eRecSize / $header["recLen"];

		$f = fopen($this->fp[$fp] . '.ta', 'rb');

		// open up temp file for writing the buffers
		$newFnam = $this->workingDir . "tmpF_" . md5(uniqid(rand()));
		$newF = fopen($newFnam, "wb");

		// write the header to the new table file
		fwrite($newF, $h_header);

		$value = "";
		if ($header[$cHeader]["fType"] == "id") {
			$ref_data = $this->get_ref_data($fp);
			$ref_arr = explode(chr(31), $ref_data);
			$rfArr = array();

			foreach ($ref_arr as $ref_tmp) {
				$ref_tmp = explode(':', $ref_tmp);
				$rfArr[$ref_tmp[1]-1] = $ref_tmp[0];
			}
		}

		$cid = 1;
		while (!feof($f)) {
			if ($cid > $eRecCount)
                break;

            $value = "";
			fseek($f, $this->bytesToSeek($fp, $header, $cid));

			if ($header[$cHeader]["fType"] == "id" && isset($rfArr[$cid]))
                $value = $rfArr[$cid];

			// write the record with the new field
			fwrite($newF, fread($f, $header["recLen"]).$value.str_repeat(" ", $header[$cHeader]["fLength"] - strlen($value)));
			$cid++;
		}

		fclose($newF);
		fclose($f);
		unlink($this->fp[$fp].'.ta');
		rename($newFnam, $this->fp[$fp] . '.ta');

		if (isset($this->_header[$fp]))
            unset($this->_header[$fp]);

        return true;
	}

	/**
	 * Edits a field's parameters
	 *
	 * @param string $fp
	 * @param name $oldfield
	 * @param array $field
	 * @return bool
	 */
	public function editField($fp, $oldfield, $field = array())
    {
		if ($this->check(__LINE__, $fp) === false)
            return false;

		$header = array();
		$this->readHeader($fp, $header);
		$cHeader = count($header) - 8;
		$foundField = false;

        for ($i = 1; $i <= $cHeader; $i++) {
			if ($header[$i]["fName"] == $oldfield) {
				$foundField = true;
				$fieldId = $i;
				break;
			}
		}

		if (!$foundField) {
			$this->sendError(E_USER_WARNING, "Cannot remove '$oldfield' field, it does not exist.", __LINE__);

            return false;
		}

		//name, type, size
		if ($field[1] != "string" && $field[1] != "number" && $field[1] != "memo" && $field[1] != "id") {
			$this->sendError(E_USER_ERROR, "New field type must be either string, number, memo, or id.", __LINE__);

            return false;
		}

		if ($field[1] == "id" || $field[1] == 'memo')
            $field[2] = "7";
		//if($field[1] == "memo") $field[2] = "10"; //memo functions only use 7 chars

		$oldlength = $header[$fieldId]["fLength"];
		$oldType = $header[$fieldId]["fType"];
		$header[$fieldId]["fName"] = $field[0];
		$header[$fieldId]["fType"] = $field[1];
		$header[$fieldId]["fLength"] = $field[2];

		// start building the header
		$h_fields = array();
		$h_recLen = 0;

		for ($i = 1; $i <= $cHeader; $i++) {
			$h_fields[] = $i.chr(31).$header[$i]["fType"].chr(31).$header[$i]["fLength"].chr(31).$header[$i]["fName"];
			$h_recLen += $header[$i]["fLength"];
		}

		$h_fields = implode(chr(29), $h_fields);
		$h_cid = $header["curId"] . str_repeat(" ", 7 - strlen($header["curId"]));
		$h_lastBlank = $header["lastBlank"] . str_repeat(" ", 7 - strlen($header["lastBlank"]));

		$h_header = strlen($h_fields) . chr(28) . $h_fields . $h_recLen . chr(28) . $h_cid . chr(28) . $h_lastBlank . chr(28) . $header["blockLength"] . chr(28);

		// rebuild the table with the new field
		$eRecSize = filesize($this->fp[$fp] . '.ta') - $header["recPos"];
		$eRecCount = $eRecSize / $header["recLen"];

		$f = fopen($this->fp[$fp] . '.ta', 'rb');

		// open up temp file for writing the buffers
		$newFnam = $this->workingDir . "tmpF_" . md5(uniqid(rand()));
		$newF = fopen($newFnam, "wb");

		// write the header to the new table file
		fwrite($newF, $h_header);

		$cid = 1;

		while (!feof($f)) {
			if ($cid > $eRecCount)
                break;

			fseek($f, $this->bytesToSeek($fp, $header, $cid));

            if (fread($f, 1) == chr(24)) {
				fwrite($newF, chr(24) . fread($f, 7) . str_repeat(' ', $h_recLen - 8));
				$cid++;
				continue;
			} else
                fseek($f, -1, SEEK_CUR);

			for ($i = 1; $i <= $cHeader; $i++) {
				if ($i != $fieldId) {
					fwrite($newF, fread($f, $header[$i]["fLength"]));
				} else {
					$value = rtrim(fread($f, $oldlength));

					if ($oldType == "memo" && $header[$i]["fType"] != "memo") {
						$memo = $value;
						$value = $this->readMemo($fp, $memo, $header);
						$this->deleteMemo($fp, $memo, $header);
						unset($memo);
					}

                    $field = $value;

					if ($header[$i]["fType"] == "memo") {
						$this->writeMemo($fp, $field, $header);
					} elseif ($header[$i]["fType"] == "string") {
						$field = substr($field, 0, $header[$i]["fLength"]);
					} elseif ($header[$i]["fType"] == "number") {
						$field = preg_replace("/[^0-9.-]/i", "", $field);
						$field = substr($field, 0, $header[$i]["fLength"]);
					} elseif ($header[$i]["fType"] == "id") {
						$field = $header["curId"];
					}

					$field = $field.str_repeat(" ", $header[$i]["fLength"] - strlen($field));
					fwrite($newF, $field);
				}
			}

			$cid++;
		}

		fclose($newF);
		fclose($f);
		unlink($this->fp[$fp] . '.ta');

		rename($newFnam, $this->fp[$fp] . '.ta');
		$this->_header[$fp] = $header;

        return true;
	}

	/**
	 * Removes a column from the table
	 *
	 * @param string $fp
	 * @param string $field
	 * @return bool
	 */
	public function removeField($fp, $field)
    {
		if ($this->check(__LINE__, $fp) === false)
            return false;

		$header = array();
		$this->readHeader($fp, $header);
		$cHeader = count($header) - 8;
		$foundField = false;

        for ($i = 1; $i <= $cHeader; $i++) {
			if ($header[$i]["fName"] == $field) {
				$foundField = true;
				break;
			}
		}

		if (!$foundField) {
			$this->sendError(E_USER_WARNING, "Cannot remove '$field' field, it does not exist.", __LINE__);

            return false;
		}

		// start building the header
		$h_fields = array();
		$h_recLen = 0;

		for ($i = 1, $j = 1; $i <= $cHeader; $i++) {
			if ($header[$i]["fName"] != $field) {
				$h_fields[] = $j++ . chr(31) . $header[$i]["fType"] . chr(31) . $header[$i]["fLength"] . chr(31) . $header[$i]["fName"];
				$h_recLen += $header[$i]["fLength"];
			}
		}

		$h_fields = implode(chr(29), $h_fields);
		$h_cid = $header["curId"] . str_repeat(" ", 7 - strlen($header["curId"]));
		$h_lastBlank = $header["lastBlank"] . str_repeat(" ", 7 - strlen($header["lastBlank"]));

		$h_header = strlen($h_fields) . chr(28) . $h_fields . $h_recLen . chr(28) . $h_cid . chr(28) . $h_lastBlank . chr(28) . $header["blockLength"] . chr(28);

		// rebuild the table with the field removed
		$eRecSize = filesize($this->fp[$fp] . '.ta') - $header["recPos"];
		$eRecCount = $eRecSize / $header["recLen"];

		$f = fopen($this->fp[$fp] . '.ta', 'rb');

		// open up temp file for writing the buffers
		$newFnam = $this->workingDir . "tmpF_" . md5(uniqid(rand()));
		$newF = fopen($newFnam, "wb");

		// write the header to the new table file
		fwrite($newF, $h_header);
		$cid = 1;

        while (!feof($f)) {
			if ($cid > $eRecCount)
                break;

			fseek($f, $this->bytesToSeek($fp, $header, $cid));

            if (fread($f, 1) == chr(24)) {
				fwrite($newF, chr(24) . fread($f, 7) . str_repeat(' ', $h_recLen - 8));
				$cid++;
				continue;
			} else
                fseek($f, -1, SEEK_CUR);

			for ($i = 1; $i <= $cHeader; $i++) {
				if ($header[$i]["fName"] != $field) {
					fwrite($newF, fread($f, $header[$i]["fLength"]));
				} elseif ($header[$i]["fType"] != "memo")
                    fseek($f, $header[$i]["fLength"], SEEK_CUR);
				else
                    $this->deleteMemo($fp, trim(fread($f, $header[$i]["fLength"])), $header);
			}

			$cid++;
		}

		fclose($newF);
		fclose($f);
		unlink($this->fp[$fp] . '.ta');
		rename($newFnam, $this->fp[$fp] . '.ta');

		if (isset($this->_header[$fp]))
            unset($this->_header[$fp]);

        return true;
	}

	/**
	 * Edits a record.
	 *
	 * @param string $fp
	 * @param int $id
	 * @param array $recArr
	 * @return bool
	 */
	public function edit($fp, $id, $recArr)
    {
		if ($this->check(__LINE__, $fp) === false)
            return false;

		$header = array();

		if (($fileId = $this->fileIdById($fp, $id)) === false) {
			$this->sendError(E_WARNING, "Unable to execute edit().  Unable to retrieve fileID", __LINE__);
			return false;
		}

		$this->readHeader($fp, $header);
		$f = fopen($this->fp[$fp] . '.ta', 'r+b');
		//fseek($f, $this->bytesToSeek($fp, $header, $fileId));
		$offset = $this->bytesToSeek($fp, $header, $fileId);

		//edit the record
		$cHeader = count($header) - 8;
		for($i = 1; $i <= $cHeader; $i++) {
			fseek($f, $offset);
			$field = "";

			if (isset($recArr[$header[$i]["fName"]])) {
				$field = $recArr[$header[$i]["fName"]];

				if ($header[$i]["fType"] == "memo") {
					$this->deleteMemo($fp, trim(fread($f, $header[$i]["fLength"])), $header);
					fseek($f, $offset);
					$field = $this->writeMemo($fp, $field, $header);
				} elseif ($header[$i]["fType"] == "string") {
					//$field = preg_replace("/[^a-z0-9 ,.:?/#]/i", "", $field);
					$field = substr($field, 0, $header[$i]["fLength"]);
				} elseif ($header[$i]["fType"] == "number") {
					$field = preg_replace("/[^0-9.-]/i", "", $field);
					$field = substr($field, 0, $header[$i]["fLength"]);
				} elseif ($header[$i]["fType"] == "id") {
					$theId = trim(fread($f, $header[$i]["fLength"]));
					fseek($f, $offset);
					$field = $theId;
				}

				$field = $field . str_repeat(" ", $header[$i]["fLength"] - strlen($field));
				fwrite($f, $field);
			}

			$offset += $header[$i]["fLength"];
		}

		fclose($f);
		if (isset($this->_query[$fp]))
            unset($this->_query[$fp]);

        return true;
	}

	/**
	 * Deletes a record.
	 *
	 * @param string $fp
	 * @param int $id
	 * @param bool $runRebuild[Optional]
	 * @return bool
	 */
	public function delete($fp, $id)
    {
		if ($this->check(__LINE__, $fp) === false)
            return false;

		if (($fileId = $this->fileIdById($fp, $id)) === false) {
			$this->sendError(E_WARNING, "Unable to execute delete().  Unable to retrieve fileID", __LINE__);

            return false;
		}

		$header = array();
		$this->readHeader($fp, $header);
		$f = fopen($this->fp[$fp] . '.ta', 'r+b');
		fseek($f, $this->bytesToSeek($fp, $header, $fileId));

		//Gather memo fps
		$offset = 0;

		for ($i = 1, $hmax = (count($header) - 8); $i <= $hmax; $i++) {
			if ($header[$i]["fType"] == "memo") {
				fseek($f, $offset, SEEK_CUR);
				$this->deleteMemo($fp, trim(fread($f, $header[$i]["fLength"])), $header);
				$offset = 0;
			} else
                $offset += $header[$i]["fLength"];
		}

		//erase the record
		fseek($f, $this->bytesToSeek($fp, $header, $fileId));
		fwrite($f, chr(24) . $header['lastBlank'] . str_repeat(" ", ($header["recLen"] - (strlen($header['lastBlank']) + 1))));
		//fwrite($f, chr(24).$header['lastBlank']);
		$this->_header[$fp]['lastBlank'] = $fileId;
		fseek($f, $header['blankPos']);
		$fileId = substr($fileId, 0, 7);
		fwrite($f, $fileId.str_repeat(' ', 7 - strlen($fileId)));
		fclose($f);

		$ref_data = chr(31).$this->get_ref_data($fp);
		$ref_data = substr(str_replace(chr(31) . $id . ':' . $fileId . chr(31), chr(31), $ref_data), 1);
		$f = fopen($this->fp[$fp] . '.ref', 'wb');
		$this->_ref[$fp] = $ref_data;
		fwrite($f, $ref_data);
		fclose($f);

		if (isset($this->_query[$fp]))
            unset($this->_query[$fp]);

        return true;
	}

	/**
	 * Obsolete function, passes arguments to tdb::sort()
	 *
	 * @param string $fp
	 * @param string $fieldName
	 * @param string $direction[Optional]
	 * @return bool
	 */
	public function sortAndBuild($fp, $fieldName, $direction = "ASC")
    {
		$this->sendError(E_USER_NOTICE, 'tdb::sortAndBuild() function obsolete.  Update scripts accordingly', __LINE__);

        return $this->sort($fp, $fieldName, $direction);
	}

	//new sys not avail.
	/**
	* Sorts records in a specific order based on a field
	*
	* @param string $fp
	* @param string $fieldName
	* @param string $direction[Optional]
	* @return bool
	*/
	public function sort($fp, $fieldName, $direction = "ASC")
    {
		if ($this->check(__LINE__, $fp) === false)
            return false;

		$header = array();
		$this->readHeader($fp, $header);
		$sortFieldIndex = 0;
		$sortType = ""; //numeric or string

		//first make sure this fieldname exists
		$exists = false;

        for ($i = 1; $i <= count($header) - 8; $i++) {
			if ($header[$i]["fName"] == $fieldName) {
				$sortFieldIndex = $i;
				$exists = true;
				break;
			}
		}

		unset($i);

		if ($exists) {
			if ($header[$sortFieldIndex]["fType"] == "number" ||  $header[$sortFieldIndex]["fType"] == "id") {
				$sortType = SORT_NUMERIC;
			} elseif ($header[$sortFieldIndex]["fType"] == "string") {
				$sortType = SORT_STRING;
			} else {
				$this->sendError(E_USER_WARNING, "You can only sort the following field types: number, id, and string", __LINE__);

                return false;
			}
		} else {
			$this->sendError(E_USER_WARNING, "The field '$fieldName' does not exist", __LINE__);

            return false;
		}

		//build the sorting array
		$arrById = array();

		$row = $this->listRec($fp, 1);
		for ($i = 0; $i < count($row); $i++) {
			$arrById[$row[$i][$fieldName]] = $row[$i]['id'];
		}

		//sort
		if ($direction == "ASC")
            ksort($arrById, $sortType);
		elseif($direction == "DESC")
            krsort($arrById, $sortType);

		reset($arrById);

		// grab all the references
		$ref_data = $this->get_ref_data($fp);
		$refArrOld = explode(chr(31), substr($ref_data, 0, -1));
		$refArr = array();

        foreach ($refArrOld as $refArrTmp) {
			$refTmp = explode(':', $refArrTmp);
			$refArr[$refTmp[0]] = $refArrTmp;
		}

		//Rebuild the references
		$new_ref = '';
		/*for($i=0;$i<count($row);$i++) {
		 $arrInfo = each($arrById);
		 $id = $arrInfo["key"] + 1;
		 $new_ref .= $refArr[$id].":".($i + 1).chr(31);
		 }*/

		foreach ($arrById as $arrInfo) {
			$new_ref .= $refArr[$arrInfo] . chr(31);
		}

		$this->_ref[$fp] = $new_ref;
		$f = fopen($this->fp[$fp] . '.ref', 'wb');
		fwrite($f, $new_ref);
		fclose($f);

		return true;
	}

	/**
	 * Obsolete function
	 *
	 */
	public function reBuild()
    {
		$this->sendError(E_USER_NOTICE, 'tdb::reBuild() function obsolete.  Update scripts accordingly', __LINE__);
	}

	/**
	 * Add a record to table $fp, using values from $recArr
	 *
	 * @param string $fp
	 * @param array input
	 * @return bool false on fail, int ID on success
	 */
	public function add($fp, $recArr)
    {
		if($this->check(__LINE__, $fp) === false)
            return false;

		$header = array();
		$this->readHeader($fp, $header);
		$header["curId"]++;

		//update the cache curId as well
		$this->_header[$fp]["curId"]++;

		if ($header["curId"] > 5000000) {
			$this->sendError(E_WARNING, "Maximum records reached (5,000,000) aborting...", __LINE__);

            return false;
		}

		$record = "";

        for ($i = 1, $cHeader = (count($header) - 8); $i <= $cHeader; $i++) {
			if (isset($recArr[$header[$i]["fName"]]))
                $field = $recArr[$header[$i]["fName"]];
			else
                $field = "";

			if ($header[$i]["fType"] == "memo") {
				$field = $this->writeMemo($fp, $field, $header);
			} elseif ($header[$i]["fType"] == "string") {
				//$field = preg_replace("/[^a-z0-9 ,.:?/#]/i", "", $field);
				//$field = substr($field, 0, $header[$i]["fLength"]);
			} elseif ($header[$i]["fType"] == "number") {
				$field = preg_replace("/[^0-9.-]/i", "", $field);
				//$field = substr($field, 0, $header[$i]["fLength"]);
			} elseif ($header[$i]["fType"] == "id") {
				$field = $header["curId"];
			}

			$field = substr($field, 0, $header[$i]["fLength"]);
			$field = $field . str_repeat(" ", $header[$i]["fLength"] - strlen($field));
			$record[] = $field;
		}

		$record = implode("", $record);

		if (strlen($record) != $header["recLen"]) {
			$this->sendError(E_ERROR, "There was an error adding the record.", __LINE__);

            return false;
		}

		if ($header['lastBlank'] != -1) {
			$fileId = $header['lastBlank'];
		} elseif (isset($this->_fileId[$fp])) {
			$this->_fileId[$fp]++;
			$fileId = $this->_fileId[$fp];
		} else {
			$fileId = (filesize($this->fp[$fp] . '.ta') - $header["recPos"]) / $header["recLen"] + 1;
			$this->_fileId[$fp] = $fileId;
		}

		$f = fopen($this->fp[$fp] . '.ref', 'ab');
		flock($f, 2);
		fwrite($f, $header["curId"] . ":" . $fileId . chr(31));
		flock($f, 3);

		if (isset($this->_ref[$fp]))
            $this->_ref[$fp] .= $header["curId"] . ":" . $fileId . chr(31);

		fclose($f);
		$f = fopen($this->fp[$fp] . '.ta', 'r+b');
		flock($f, 2);
		fseek($f, $header["idPos"]);
		fwrite($f, $header["curId"]);

		if ($header['lastBlank'] == -1)
            fseek($f, 0, SEEK_END);
		else {
			fseek($f, $this->bytesToSeek($fp, $header, $fileId));
			$nextBlank = ltrim(fread($f, 8), chr(24));
			fseek($f, $header['blankPos']);
			$nextBlank = substr($nextBlank, 0, 7);
			fwrite($f, $nextBlank);
			$this->_header[$fp]['lastBlank'] = (int)trim($nextBlank);
			fseek($f, $this->bytesToSeek($fp, $header, $fileId));
		}

		fwrite($f, $record);
		flock($f, 3);
		fclose($f);

		if (!in_array($this->fp[$fp], $this->editedTable))
            $this->editedTable[] = $this->fp[$fp];

        return $header["curId"];
	}

	/**
	 * Finds the physical address of a record based on its ID
	 *
	 * @param string $fp
	 * @param int $id
	 * @return int fileID on success, bool false on fail
	 */
	function fileIdById($fp, $id) {
		if(FALSE === $this->check(__LINE__, $fp)) return false;

		$ref_data = $this->get_ref_data($fp);
		$ref_data = chr(31).$ref_data;

		if(FALSE !== ($pos1 = strpos($ref_data, chr(31).$id.':'))) {
			$pos1 = $pos1 + strlen(chr(31).$id.':');
			$length = strpos($ref_data, chr(31), $pos1) - $pos1;
			$fileId = substr($ref_data, $pos1, $length);
			if((int)$fileId <= 0) {
				$this->sendError(E_ERROR, "fileIdById() found a nonpositive integer fileId(\"$fileId\") based on ID(\"$id\") in ".$this->fp[$fp].".  Dumping reference file:".$ref_data.". \$pos1:$pos1. \$length:$length.", __LINE__);
				return false;
			}
			return  (int)$fileId;
			//return substr($ref_data, $pos1, $length);
		}
		return false;
	}

	/**
	 * Retrieves the ref information of a table based on the $fp
	 *
	 * @param string $fp
	 * @return string
	 */
	function get_ref_data($fp) {
		if(FALSE === $this->check(__LINE__, $fp)) return false;
		if(!isset($this->_ref[$fp])) $this->_ref[$fp] = file_get_contents($this->fp[$fp].'.ref');
		return $this->_ref[$fp];
	}

	/**
	 * retrieves a file based on its ID
	 *
	 * @param string $fp
	 * @param int $id
	 * @return array record on success, bool false on fail
	 */
	function get($fp, $id, $fields=array('*')) {
		if(FALSE === ($fileId = $this->fileIdById($fp, $id))) {
			//$this->sendError(E_WARNING, "Unable to execute get().  Unable to retrieve fileID", __LINE__);
			return false;
		}
		$reqFields = array();
		if($fields[0] == "*") {
			$reqFields = array("*");
		} else {
			foreach($fields as $incField) {
				$reqFields[$incField] = true;
			}
		}

		$header = array();
		$this->readHeader($fp, $header);

		$f = fopen($this->fp[$fp].'.ta', 'rb');
		fseek($f, $this->bytesToSeek($fp, $header, $fileId));
		$buffer = fread($f, $header["recLen"]);
		fclose($f);
		if(ord($buffer{0}) != 28) {
			//return $this->parseRecord($fp, $buffer, $header); //new method
			return array($this->parseRecord($fp, $buffer, $header, $reqFields)); //old method
		}
		$this->sendError(E_USER_NOTICE, 'Unable to parse record with recordId of '.$id.' at fileId:'.$fileId.' in get() at '.$this->fp[$fp], __LINE__);
		return false;
	}

	/**
	 * Retrieves records in sequencial order
	 *
	 * @param string $fp
	 * @param int $start
	 * @param int $howmany[Optional]
	 * @param array $fields[Optional]
	 * @return array records on success, bool false on fail
	 */
	function listRec($fp, $start, $howmany=-1, $fields=array("*")) {
		if(FALSE === $this->check(__LINE__, $fp)) return false;
		$return = array();
		$pos2 = 0;
		$pos1 = 0;

		$ref_data = $this->get_ref_data($fp);
		for($i=1;$i<$start;$i++) { //skipping 1st rec, b/c default: $pos1 = 0;
			$pos1 = strpos($ref_data, chr(31), $pos1) + 1;

		}
		if($pos1 > strlen($ref_data)) {
			$this->sendError(E_ERROR, 'Searching for records past the end of file in listRec('.$fp.')', __LINE__);
			return false;
		}

		$reqFields = array();
		if($fields[0] == "*") {
			$reqFields = array("*");
		} else {
			foreach($fields as $incField) {
				$reqFields[$incField] = true;
			}
		}

		$header = array();
		$this->readHeader($fp, $header);
		$f = fopen($this->fp[$fp].'.ta', 'rb');
		while(FALSE !== ($pos2 = strpos($ref_data, chr(31), $pos1))) {
			if($howmany == 0) break;
			$fileId = substr(strstr(substr($ref_data, $pos1, ($pos2 - $pos1)), ':'), 1);
			$pos1 = $pos2 + 1;
			if($fileId === FALSE) continue;

			fseek($f, $this->bytesToSeek($fp, $header, $fileId));
			$buffer = fread($f, $header["recLen"]);
			if(ord($buffer{0}) != 28) {
				$return[] = $this->parseRecord($fp, $buffer, $header, $reqFields);
				$howmany--;
			}
			else $this->sendError(E_PARSE, 'Unable to parse record at fileId:'.$fileId.' in listRec() at '.$this->fp[$fp], __LINE__);
		}
		unset($buffer);

		fclose($f);

		if(empty($return)) return false;
		return $return;
	}

	/**
	 * Internal function used to parse a querystring for the query() function
	 *
	 * @param string $query
	 * @return bool false on fail, array search terms on success
	 */
	function parseQueryString($query) {
		if(trim($query) == "") return FALSE;

		//first thing is first, we need to find out if we are using =,?,<,>,!
		$pos1 = strpos($query, "=");
		$pos2 = strpos($query, "?");
		$pos3 = strpos($query, ">");
		$pos4 = strpos($query, "<");
		$pos5 = strpos($query, "!");

		$pointer = 0;
		$result = array();
		$lenquery = strlen($query);
		$i = 0;
		while($pos1 !== FALSE || $pos2 !== FALSE || $pos3 !== FALSE || $pos4 !== FALSE || $pos5 !== FALSE) {
			//find out which one came first
			$pos = FALSE;

			if($pos1 !== FALSE) {
				$pos = $pos1;
				$type = "=";
			}

			if($pos2 !== FALSE && $pos2 < $pos || $pos2 !== FALSE && $pos === FALSE) {
				$pos = $pos2;
				$type = "?";
			}

			if($pos3 !== FALSE && $pos3 < $pos || $pos3 !== FALSE && $pos === FALSE) {
				$pos = $pos3;
				$type = ">";
			}

			if($pos4 !== FALSE && $pos4 < $pos || $pos4 !== FALSE && $pos === FALSE) {
				$pos = $pos4;
				$type = "<";
			}
			if($pos5 !== FALSE && $pos5 < $pos || $pos5 !== FALSE && $pos === FALSE) {
				$pos = $pos5;
				$type = "!";
			}

			$field = substr($query, $pointer, ($pos - $pointer));
			$pointer = $pos + 1;

			//get the search text
			if(substr($query, $pointer, 1) != "'") $this->sendError(E_USER_ERROR, "Missing quote (') in query syntax", __LINE__);
			$pointer += 1;

			$text_pos = strpos($query, "'", $pointer);
			if($text_pos === FALSE) $this->sendError(E_USER_ERROR, "Invalid query syntax, missing ending quote (') in search term", __LINE__);

			$text = substr($query, $pointer, ($text_pos - $pointer));
			$pointer = $text_pos + 1;

			if($pointer >= $lenquery) $b = "&&";
			else {
				$b = substr($query, $pointer, 2);
				$pointer += 2;
			}

			if($b != "&&" && $b != "||") $this->sendError(E_USER_ERROR, "Invalid query syntax, missing '&&' or '||' between search terms", __LINE__);

			$result[$i][] = array("field" => $field, "type" => $type, "value" => $text);

			if($b == "||") {
				//create new list
				$i += 1;
			}

			//get our next term...
			$pos1 = strpos($query, "=", $pointer);
			$pos2 = strpos($query, "?", $pointer);
			$pos3 = strpos($query, ">", $pointer);
			$pos4 = strpos($query, "<", $pointer);
			$pos5 = strpos($query, "!", $pointer);
		}

		if($result !== FALSE) return $result;
		else return FALSE;
	}

	/**
	 * Queries a table based on the query string's parameters.
	 *
	 * @param string $fp
	 * @param string $query
	 * @param int $start[optional]
	 * @param in $howmany[optional]
	 * @return bool false on fail, array records on success
	 */
	function query($fp, $query, $start=1, $howmany=-1, $fields=array("*")) {
		if(FALSE === $this->check(__LINE__, $fp)) return false;

		$tmpfields = implode(",", $fields);
		if(!empty($this->_query[$fp])) {
			foreach($this->_query[$fp] as $cached_query) {
				if(
				$cached_query["query_string"] == $query &&
				$cached_query["start"] == $start &&
				$cached_query["howmany"] == $howmany &&
				$cached_query["fields"] == $tmpfields)
				return $cached_query["result"];
			}
		}
		$original_start = $start;
		$original_howmany = $howmany;
		$original_query = $query;
		$original_fields = $tmpfields;
		$string = $query;
		unset($query);

		$query_array = $this->parseQueryString($string);
		if(empty($query_array)) $this->sendError(E_USER_ERROR, "Invalid query syntax or empty query string", __LINE__);

		$header = array();
		$this->readHeader($fp, $header);

		$reqFields = array();
		if($fields[0] == "*") {
			//$getAllFields = true;
			$reqFields = array("*");
		} else {
			//$getAllFields = false;
			foreach($fields as $incField) {
				$reqFields[$incField] = true;
			}
		}


		$fieldOffsets[$header[1]["fName"]]["offset"] = 0;
		$fieldOffsets[$header[1]["fName"]]["length"] = $header[1]["fLength"];
		$fieldOffsets[$header[1]["fName"]]["type"] = $header[1]["fType"];
		$total = 0;
		//if($getAllFields) $reqFields[$header[1]["fName"]] = true;
		for($i=2;$i<count($header)-7;$i++) {
			$fieldOffsets[$header[$i]["fName"]]["offset"] = $total + $header[$i-1]["fLength"];
			$fieldOffsets[$header[$i]["fName"]]["length"] = $header[$i]["fLength"];
			$fieldOffsets[$header[$i]["fName"]]["type"] = $header[$i]["fType"];
			$total += $header[$i-1]["fLength"];
			//if($getAllFields) $reqFields[$header[1]["fName"]] = true;
		}
		$return = array();

		$start_c = 1;
		$ref_pos1 = 0;
		$ref_data = $this->get_ref_data($fp);
		$ref_pos2 = 0;
		$f = fopen($this->fp[$fp].'.ta', 'rb');
		while(FALSE !== ($ref_pos2 = strpos($ref_data, chr(31), $ref_pos1))) {
			if(FALSE === ($fileId = strstr(substr($ref_data, $ref_pos1, ($ref_pos2 - $ref_pos1)), ':'))) {
				$ref_pos1 = $ref_pos2 + 1;
				continue;
			}
			$fileId = (int) substr($fileId, 1);
			$ref_pos1 = $ref_pos2 + 1;
			if($howmany == 0) break;

			//add OR functionality BEGIN
			$foundMatch = false;
			foreach($query_array as $query) {

				$pass = true;

				for($i=0;$i<count($query);$i++){
					if(!$pass) break;
					if($foundMatch) break;

					$field = $query[$i]["field"];
					$value = $query[$i]["value"];

					if(!isset($fieldOffsets[$field])) $this->sendError(E_USER_ERROR, "Cannot run query, the field '$field' does not exist in this table", __LINE__);

					fseek($f, $this->bytesToSeek($fp, $header, $fileId) + $fieldOffsets[$field]["offset"]);
					$fieldValue = rtrim(fread($f, $fieldOffsets[$field]["length"]));
					$fieldType = $fieldOffsets[$field]["type"];

					if($fieldType == "memo") {
						$fieldValue = $this->readMemo($fp, $fieldValue, $header);
					}

					if($query[$i]["type"] == "=") {
						if(trim(strtolower($fieldValue)) != strtolower($value)) $pass = false;
					} elseif($query[$i]["type"] == "?") {
						if(strpos(strtolower($fieldValue), strtolower($value)) <= -1) $pass = false;
					} elseif($query[$i]["type"] == "<") {
						if((double) $fieldValue >= (double) $value) $pass = false;
					} elseif($query[$i]["type"] == ">") {
						if((double) $fieldValue <= (double) $value) $pass = false;
					} elseif($query[$i]["type"] == "!") {
						if(trim(strtolower($fieldValue)) == strtolower($value)) $pass = false;
					} else {
						$this->sendError(E_USER_ERROR, "Invalid query syntax, missing operator (=,?,>,<,!)", __LINE__);
						$pass = false;
					}
				}

				if($pass && !$foundMatch) {
					if($start_c < $start) {
						//echo $start_c.'<='.$start;
						$start_c++;
						continue;
					}
					//we have a match, return it
					fseek($f, $this->bytesToSeek($fp, $header, $fileId));
					$buffer = fread($f, $header["recLen"]);

					if(ord($buffer{0}) != 28) {
						$return[] = $this->parseRecord($fp, $buffer, $header, $reqFields);
						$howmany--;
						$foundMatch = true;
					}
					else $this->sendError(E_PARSE, 'Unable to parse record at fileId:'.$fileId.' in listRec() at '.$this->fp[$fp], __LINE__);
					//if($howmany_c >= $howmany && $howmany != -1) break;
				}

				//END add OR functionalilty
			}
		}
		unset($buffer);

		fclose($f);

		//cache the result
		$this->_query[$fp][] = array("result" => $return,
        "query_string" => $original_query,
        "start" => $original_start,
        "howmany" => $original_howmany,
        "fields" => $original_fields);

		if(empty($return)) return false;
		return $return;
	}

	/**
	 * Queries a table without a query string using only one field.
	 *
	 * @param string $fp
	 * @param string $field
	 * @param any $value
	 * @param int $start[optional]
	 * @param in $howmany[optional]
	 * @return bool false on fail, array records on success
	 */
	function basicQuery($fp, $field, $value, $start = 1, $howmany = -1, $fields=array('*')) {
		return $this->query($fp, "$field='$value'", $start, $howmany,$fields);
	}

	/**
	 * Internal function to parse raw records into arrays
	 *
	 * @param string $fp
	 * @param string $rawRecord
	 * @param array $header
	 * @param array $reqFields
	 * @return array
	 */
	function parseRecord($fp, $rawRecord, $header, $reqFields=array("*")) {
		if(ord($rawRecord{0}) == 28) {
			$this->sendError(E_PARSE, 'Unable to parse record in parseRecord() at '.$this->fp[$fp], __LINE__);
			return array();
		}
		$cHeader = count($header) - 8;
		$pos = 0;

		for($i=1;$i<=$cHeader;$i++) {
			if(isset($reqFields[$header[$i]["fName"]]) || $reqFields[0] == "*") {
				$value = rtrim(substr($rawRecord, $pos, $header[$i]["fLength"]));

				if($header[$i]["fType"] == "memo") $value = $this->readMemo($fp, $value, $header);
				$fRec[$header[$i]["fName"]] = $value;
			}
			$pos = $pos + $header[$i]["fLength"];
		}

		return $fRec;
	}

	/**
	 * Internal function to seek to a paricular record based on the fileID
	 *
	 * @param string $fp
	 * @param array $header
	 * @param int $recordId
	 * @return bool false on fail, int seek on success
	 */
	function bytesToSeek($fp, $header, $recordId) {
		$recordId--;
		$seek = $header["recPos"] + ($recordId * $header["recLen"]);
		if($seek < $header['recPos']) {$this->sendError(E_ERROR, "The record($recordId) you are trying to access before the RECORD_START_POSTION of the file(seeking ".$seek." at ".$this->fp[$fp].").", __LINE__); return false; }
		if($seek < filesize($this->fp[$fp].'.ta')) return $seek;
		else $this->sendError(E_ERROR, "The record($recordId) you are trying to access is past the end of the file(seeking ".$seek." at ".$this->fp[$fp].").", __LINE__);
		return false;
	}

	/**
	 * Obsolete function
	 *
	 */
	function rewriteMemo() {
		$this->sendError(E_USER_NOTICE, 'rewriteMemo() function obsolete.  Update scripts accordingly', __LINE__);
	}

	/**
	 * Retrieves information from the memo file based on the given index.
	 *
	 * @param string $fp
	 * @param int $index
	 * @param array $header
	 * @return string
	 */
	function readMemo($fp, $index, $header) {
		$readIndexes = array();

		$return = '';
		$next = $index;
		$f = fopen($this->fp[$fp].'.memo', 'rb');
		while(!empty($next) && $next > 0) {
			if(!ctype_digit($next)) die('<b>Fatal Error</b>(line '.__LINE__.'):The Script encountered a non-numeric value for readMemo(): readMemo("'.$this->fp[$fp].'", "'.$index.'", $header) literally at "'.$next.'"position ('.$next.' of '.(filesize($this->fp[$fp].'.memo') / $header["blockLength"]).' block) in the memo file.<br />');

			// Store read index to make sure we don't loop forever if indexes are messed up
			$readIndexes[$next] = true;

			fseek($f, ($next * $header["blockLength"]));
			$next = trim(fread($f, 7));

			// Make sure the next index hasn't already been read
			if(isset($readIndexes[$next])) die('<b>Fatal Error</b>(line '.__LINE__.'): There is an error in '.$this->fp[$fp].'.memo, this needs to be corrected. The error starts on index <b>'.$index.'</b>');

			if((ftell($f) - 7) == $next * $header["blockLength"]) die('<b>Fatal Error</b>(line '.__LINE__.'): Script entered an endless loop in readMemo("'.$this->fp[$fp].'", "'.$index.'", $header) at "'.$next.'" position ('.$next.' of '.(filesize($this->fp[$fp].'.memo') / $header["blockLength"]).' block) in the memo file.<br />');
			$return .= substr(rtrim(fread($f, $header["blockLength"] - 7)), 0, -1);
		}
		fclose($f);
		//str_replace added to remove <x> from all queries. Should not be used for non-UPB context.
		return str_replace('&lt;x&gt;','',$return);
	}

	/**
	 * Deletes information associated with  the index from the memo file.
	 *
	 * @param string $fp
	 * @param int $index
	 * @param array $header
	 * @return void
	 */
	function deleteMemo($fp, $index, $header) {
		if($index == '0') die('<b>Fatal Error</b>(line '.__LINE__.'): Tried to delete 0th memo record in '.$this->fp[$fp].'. Literally: '.$index);
		if(!(ctype_digit($index) && !empty($index))) return true;
		$readIndexes = array();

		$next = $index;
		$f = fopen($this->fp[$fp].'.memo', 'r+b');
		if(empty($this->_firstBlankMemoBlockRef[$fp])) $first_blank_memo_block_ref = trim(fread($f, 7));
		else $first_blank_memo_block_ref = $this->_firstBlankMemoBlockRef[$fp];
		while(ctype_digit($next) && !empty($next)) {
			// Store read index to make sure we don't loop forever if indexes are messed up
			$readIndexes[$next] = true;

			fseek($f, $next * $header["blockLength"]);
			$next = trim(fread($f, 7));

			// Make sure the next index hasn't already been read
			if(isset($readIndexes[$next])) die('<b>Fatal Error</b>(line '.__LINE__.'): There is an error in '.$this->fp[$fp].'.memo, this needs to be corrected. The error starts on index <b>'.$index.'</b>');

			if((ftell($f) - 7) == $next * $header["blockLength"]) die('<b>Fatal Error</b>(line '.__LINE__.'): Script entered an endless loop in deleteMemo("'.$this->fp[$fp].'", "'.$index.'", $header) at ('.$next.' of '.(filesize($this->fp[$fp].'.memo') / $header["blockLength"]).' block) in the memo file.<br />');
		}
		fseek($f, -7, SEEK_CUR);
		fwrite($f, $first_blank_memo_block_ref.str_repeat(' ', 7 - strlen($first_blank_memo_block_ref)));
		fseek($f, 0);
		fwrite($f, $index.str_repeat(' ', 7 - strlen($index)));
		$this->_firstBlankMemoBlockRef[$fp] = $index;
		return true;
	}

	/**
	 * Writes the data into the memo file
	 *
	 * @param string $fp
	 * @param string $data
	 * @param array $header
	 * @return int
	 */
	function writeMemo($fp, $oriData, $header) {
		$data = trim($oriData,"\t\n\r\0\x0B"); //strip all but whitespace from both ends of data

		if(strlen($data) == 0) return;

		$f = fopen($this->fp[$fp].'.memo', 'r+b');
		if(empty($this->_firstBlankMemoBlockRef[$fp])) {
			$next = trim(fread($f, 7));
			$this->_firstBlankMemoBlockRef[$fp] = $next;
		} else $next = $this->_firstBlankMemoBlockRef[$fp];

		$readIndexes = array();

		while(ctype_digit($next) && !empty($next) && !(strlen($data) == 0)) {
			if(!isset($return)) $return = $next;

			// Store read index to make sure we don't loop forever if indexes are messed up
			$readIndexes[$next] = true;

			fseek($f, $next * $header["blockLength"]);
			$next = trim(fread($f, 7));

			// Make sure the next index hasn't already been read
			if(isset($readIndexes[$next])) die('<b>Fatal Error</b>(line '.__LINE__.'): There is an error in '.$this->fp[$fp].'.memo, this needs to be corrected. The error starts on index <b>'.$index.'</b>');

			if(ftell($f) - 7 == $next * $header["blockLength"]) die('<b>Fatal Error</b>(line '.__LINE__.'): Script entered an endless loop in writeMemo("'.$this->fp[$fp].'", "'.$oriData.'", $header) at "'.$next.'" position in the memo file.<br />');
			if(strlen($data) > ($header["blockLength"] - 8)) { //if it won't fit
				fwrite($f, substr($data, 0, $header["blockLength"] - 8).chr(3));
				$data = substr($data, $header["blockLength"] - 8);
			} else {
				fseek($f, -7, SEEK_CUR);
				fwrite($f, '-1'.str_repeat(' ', 5).$data.chr(3).str_repeat(' ', ($header["blockLength"] - (strlen($data) + 8))));
				$data = '';
			}
		}
		if(!(strlen($data) == 0) && ftell($f) >= $header["blockLength"]) {
			fseek($f, -($header["blockLength"]), SEEK_CUR);
			$last_write_offset = ftell($f);
			fseek($f, 0, SEEK_END);
			$EOF_index = ftell($f) / $header["blockLength"];
			fseek($f, $last_write_offset);
			fwrite($f, $EOF_index.str_repeat(' ', 7 - strlen($EOF_index)));
		}

		fseek($f, 0);
		fwrite($f, $next.str_repeat(' ', 7 - strlen($next)));
		$this->_firstBlankMemoBlockRef[$fp] = $next;

		fseek($f, 0, SEEK_END);
		while(!(strlen($data) == 0)) {
			$next = (ftell($f) / $header["blockLength"]) + 1;

			if(!isset($return)) $return = $next - 1;
			if(!is_integer($next) || $next < 0) die('<b>Fatal Error</b>(line '.__LINE__.'):The Script encountered a non-numeric value for writeMemo(): writeMemo("'.$this->fp[$fp].'", "'.$oriData.'", $header) literally at "'.$next.'" position ('.($next / $header["blockLength"]).' of '.(filesize($this->fp[$fp].'.memo') / $header["blockLength"]).' block) in the memo file.<br />');

			if(strlen($data) > ($header["blockLength"] - 8)) { //if it won't fit
				fwrite($f, $next.str_repeat(' ', 7 - strlen($next)).substr($data, 0, $header["blockLength"] - 8).chr(3));
				$data = substr($data, $header["blockLength"] - 8);
			} else {
				fwrite($f, '-1'.str_repeat(' ', 5).$data.chr(3).str_repeat(' ', ($header["blockLength"] - (strlen($data) + 8))));
				$data = '';
			}
		}
		fclose($f);

		return $return;
	}

	/**
	 * Checks to validate a working database, directory, and [optional]table
	 *
	 * @param int $line
	 * @param string $fp[optional]
	 * @return bool
	 */
	function check($line, $fp = null) {
		clearstatcache();
		if(!isset($this->Db) || $this->Db == '') {
			$this->sendError(E_USER_ERROR, 'Fatal: the database is undefined', $line);
			return false;
		}
		if(!isset($this->workingDir) || $this->workingDir == '') {
			$this->sendError(E_USER_ERROR, 'Fatal: the working directory is undefined', $line);
			return false;
		}
		if(!file_exists($this->workingDir.$this->Db)) {
			$this->sendError(E_USER_ERROR, 'The database('.$this->workingDir.$this->Db.') does not exist', $line);
		}
		if($fp != null) {
			if($fp == '') {
				$this->sendError(E_USER_ERROR, 'The $fp is not set', $line);
				return false;
			}
			if(!isset($this->fp[$fp]) || $this->fp[$fp] == '') {
				$this->sendError(E_USER_ERROR, '"'.$fp.'" was not set as a valid table through setFp()', $line);
				return false;
			}
			if(!file_exists($this->fp[$fp].'.ta')) {
				$this->sendError(E_USER_NOTICE, 'The table('.$this->fp[$fp].') does not exist', $line);
				return false;
			}
		}
		return true;
	}

	/**
	 * triggers an error on behalf of the tdb.class.php
	 *
	 * @param string $errMsg
	 * @param int $line[Optional]
	 */
	function sendError($errno, $errMsg, $line = '') {
		if(self::TDB_ERROR_INCLUDE_ORIGIN == TRUE) {
			$error_trace = debug_backtrace();
			$error_origin = '  Executed on '.$error_trace[count($error_trace) -1]['file'].' at line '.$error_trace[count($error_trace) -1]['line'].'.';
			$errMsg .= $error_origin;
		}
		if(self::TDB_PRINT_ERRORS === TRUE) print('<b>Text Database Error</b>: '.$errMsg. ((($line != null || $line != '') && (self::TDB_ERROR_INCLUDE_ORIGIN === false)) ? ' near line '.$line : '').'<br />');
		elseif($this->error_handler !== FALSE) call_user_func_array($this->error_handler, array($errno, $errMsg, 'tdb.class.php', $line));
		else trigger_error('<b>Text Database Error</b>: '.$errMsg. (($line != null || $line != '') ? ' near line '.$line : ''));
	}

	/**
	 * Defines an error handler
	 *
	 * @param object $object
	 * @param string $function
	 */
	function define_error_handler(&$object, $function) {
		$this->error_handler = array(&$object, $function);
	}

	/**
	 * Returns the version of tdb class in use
	 *
	 * @return string
	 */
	function version() {
		return "4.4.4";
	}

	function deXSS($text) {
		//echo "$text::".substr_count('&lt;x&gt;',$text)."<br>";

		return str_replace('&lt;x&gt;','',$text);
	}
}
?>