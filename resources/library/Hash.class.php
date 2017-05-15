<?php

/*
 * Description of Hash
 * 
 * Dit is de class die alle hashes voor ons verwerkt. 
 * 
 * @author marc
 */

class Hash {
    /**
     * Deze functie maakt onze geheim woord.
     * 
     * @param type $string
     * @param type $salt
     * @return type
     */
    public static function make($string, $salt = '') {
        return hash('sha256', $string . $salt);
    }
    
    /**
     * Dit is de salt functie.
     * 
     * Input is een lengte.
     * 
     * @param type $len
     * @return type
     */
    public static function salt($len) {
        return mcrypt_create_iv($len);
    }
  /**
   * Deze maakt onze unieke waarde die dan gebruikt kan worden voor je hash.
   * 
   * @return type
   */
    public static function unique() {
        return self::make(uniqid());
    }
    
    /**
     * Dit is een cipher die gebruik maakt van de BlowFish encryptie.
     * 
     * @param type $data
     * @return type
     * 
     * @todo Afmaken en testen!
     */
    public static function hash($data) {        
        $ivsize = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($ivsize, MCRYPT_DEV_RANDOM);        
        
        $hash = mcrypt_encrypt(MCRYPT_BLOWFISH,Config::get("encrypt/key256"),$data,MCRYPT_MODE_CBC,$iv);  //encrypt using triple DES
        return ((isset($hash))) ? $hash : false;
    }
    
    public static function decrypt($data,$iv = 0) {
        $decrypted_data = mcrypt_decrypt(MCRYPT_BLOWFISH,Config::get("encrypt/key256"),MCRYPT_MODE_CBC,$iv);
        return ((isset($decrypted_data))) ? $decrypted_data : false;
    }
}
