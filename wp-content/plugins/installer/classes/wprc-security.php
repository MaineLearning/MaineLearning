<?php
/**
 * Security Class
 * 
 * Manages Encryption/Decryption with key
 * 
 * 
 */
class WPRC_Security
{

	/*
	*  based on http://stackoverflow.com/questions/1289061/best-way-to-use-php-to-encrypt-and-decrypt
	*
	*/
	public static function encrypt($key,$string)
	{
		/*$encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, md5(md5($key))));
		return $encrypted;*/
	}
	
	/*
	*  based on http://stackoverflow.com/questions/1289061/best-way-to-use-php-to-encrypt-and-decrypt
	*
	*/
	public static function decrypt($key,$encrypted)
	{
		/*$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($encrypted), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
		return $decrypted;*/
	}
}
?>