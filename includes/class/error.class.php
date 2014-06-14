<?php
// Ultimate PHP Board's Custom Error Hander
class errorhandler {
	var $error = array();
	var $errortype = array (
	E_ERROR          => "Fatal error",
	E_WARNING        => "Warning",
	E_PARSE          => "Parsing Error",
	E_NOTICE          => "Notice",
	E_CORE_ERROR      => "Core Error",
	E_CORE_WARNING    => "Core Warning",
	E_COMPILE_ERROR  => "Compile Error",
	E_COMPILE_WARNING => "Compile Warning",
	E_USER_ERROR      => "Fatal error",
	E_USER_WARNING    => "Warning",
	E_USER_NOTICE    => "Notice",
	);

	function add_error($errno, $errstr, $errfile='', $errline='') {
		/*
		 [0]$errno = integer;
		 [1]$errstr = string;
		 [2]$errfile = string;
		 [3]$errline = integer;
		 [4]$errcontext = array;
		 */
		if($errfile != '') {
			$errfile = substr($errfile, strpos($errfile, dirname($_SERVER['PHP_SELF'])));
			if(defined('DB_DIR')) $errfile = str_replace(DB_DIR, '<DATA_DIRECTORY>', $errfile);
		}
		$this->error[] = array($errno, $errstr, $errfile, $errline);
		if($errno == E_ERROR || $errno == E_USER_ERROR || $errno == E_CORE_ERROR || $errno == E_COMPILE_ERROR) {
			$this->print_errors();
			exit;
		}
	}

	function print_errors($in_table=false) {
		reset($this->error);
		if($this->error_exists()) {
			echo 'The following errors occured,';
			if($in_table) echo '<table class="error"><td><td class="error">';
			else echo '<br>';
			echo '<div class="error">';
			foreach($this->error as $error) {
				echo "<b>".$this->errortype[$error[0]]."</b>: ";
				echo $error[1];
				if($error[3] != '') echo " on line <b>".$error[3]."</b>";
				if($error[2] != '') echo " in file <b>".$error[2]."</b>";
				echo '.<br>';

				if($error[0] == E_ERROR || $error[0] == E_USER_ERROR) {
					if($in_table) die('</div></td></tr></table>');
					else die('</div>');
				}
			}
			echo '</div>';
			if($in_table) echo '</td></tr></table>';
		}
	}

	function error_exists() {
		if(!empty($this->error[0])) return true;
		return false;
	}
}
?>