<?php

class Csrf {
	
	function generate() {
		if (empty($_SESSION['token'])) {
			$_SESSION['token'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
		}
	}
	
	function validate($key) {
		if (!empty($key)) {
			if ($_SESSION['token'] == $key) {
				return TRUE;
			} else {
				 return FALSE;
			}
		}
	}
	
	function destroy() {
		unset($_SESSION['token']);
	}
} 

?>