<?php
//Security Image functions
function get_rnd_iv($iv_len){
	$iv = '';
	while ($iv_len-- > 0) {
		$iv .= chr(mt_rand() & 0xff);
	}
	return $iv;
}


function md5_encrypt($plain_text, $password, $iv_len = 16){
	$plain_text .= "\x13";
	$n = strlen($plain_text);
	if ($n % 16) $plain_text .= str_repeat("\0", 16 - ($n % 16));
	$i = 0;
	$enc_text = get_rnd_iv($iv_len);
	$iv = substr($password ^ $enc_text, 0, 512);
	while ($i < $n) {
		$block = substr($plain_text, $i, 16) ^ pack('H*', md5($iv));
		$enc_text .= $block;
		$iv = substr($block . $iv, 0, 512) ^ $password;
		$i += 16;
	}
	return base64_encode($enc_text);
}


function md5_decrypt($enc_text, $password, $iv_len = 16){
	$enc_text = base64_decode($enc_text);
	$n = strlen($enc_text);
	$i = $iv_len;
	$plain_text = '';
	$iv = substr($password ^ substr($enc_text, 0, $iv_len), 0, 512);
	while ($i < $n) {
		$block = substr($enc_text, $i, 16);
		$plain_text .= $block ^ pack('H*', md5($iv));
		$iv = substr($block . $iv, 0, 512) ^ $password;
		$i += 16;
	}
	return preg_replace('/\\x13\\x00*$/', '', $plain_text);
}

//new pass hasher and check
define('SALT_LENGTH', 9);
define('HASH_LENGTH', 49);
function generateHash($plainText, $salt = null) {
	if ($salt === null) {
		$salt = substr(sha1(uniqid(rand(), true)), 0, SALT_LENGTH);
	} else {
		$salt = substr($salt, 0, SALT_LENGTH);
	}
	return $salt . sha1($salt . $plainText);
}

//old password system
if(defined('DB_DIR')) {
	if(file_exists(DB_DIR."/config2.php")) {
		require_once(DB_DIR . '/config2.php');
		function t_encrypt($text, $key) {
			$crypt = "";
			for($i=0;$i<strlen($text);$i++)     {
				$i_key = ord(substr($key, $i, 1));
				$i_text = ord(substr($text, $i, 1));
				$n_key = ord(substr($key, $i+1, 1));
				$i_crypt = $i_text + $i_key;
				$i_crypt = $i_crypt - $n_key;
				$crypt .= chr($i_crypt);
			}
			return $crypt;
		}

		function t_decrypt($text, $key) {
			$crypt = "";
			for($i=0;$i<strlen($text);$i++) {
				$i_key = ord(substr($key, $i, 1));
				$i_text = ord(substr($text, $i, 1));
				$n_key = ord(substr($key, $i+1, 1));
				$i_crypt = $i_text + $n_key;
				$i_crypt = $i_crypt - $i_key;
				$crypt .= chr($i_crypt);
			}
			return $crypt;
		}
	}
}
?>