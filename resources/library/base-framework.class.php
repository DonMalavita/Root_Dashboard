<?php

class Base_Framework {

	/**
	 * Functie: hash
	 * @param $data
	 * @return bool|string
	 */
	public static function hash($data) {
		$ivsize         = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
		Self::$_iv      = mcrypt_create_iv($ivsize, MCRYPT_DEV_RANDOM);

		$hash   = mcrypt_encrypt(MCRYPT_BLOWFISH,Config::get("encrypt/key256"),$data,MCRYPT_MODE_CBC,Self::$_iv);  //encrypt using triple DES
		return ((isset($hash))) ? $hash : false;
	}

	/**
	 * Functie: decrypt
	 * @param $data
	 * @return bool|string
	 */
	public static function decrypt($data) {
		$decrypted_data = mcrypt_decrypt(MCRYPT_BLOWFISH,Self::$_algo_key,$data,MCRYPT_MODE_CBC,Self::$_iv);
		return ((isset($decrypted_data))) ? $decrypted_data : false;
	}



function calc_hash($hasj) {
	
	$hash 			= sha1($hasj);
	$double_hash	= sha1($hash);
		
return $double_hash;
}

function FileUpload($file,$max_size) {

		
	
}

}

?>