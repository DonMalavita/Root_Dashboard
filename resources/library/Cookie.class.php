<?php
/**
 * Cookie.class.php
 */

/* * 
 * Beschrijving Cookie
 * 
 * Parses alle cookies voor hun informatie.
 * 
 * @package
 * @author
 * 
 */
class Cookie {

    /**
     * Maakt een default key om te gebruiken
     * met de rest van deze class.
     *
     * @return bool
     */
    function setKey(){
        try {
            $key = Crypto::CreateNewRandomKey();
            // WARNING: Do NOT encode $key with bin2hex() or base64_encode(),
            // they may leak the key to the attacker through side channels.
        } catch (CryptoTestFailedException $ex) {
            die('Kan geen veilige sleutel genereren');
        } catch (CannotPerformOperationException $ex) {
            die('Kan geen veilige sleutel genereren');
        }
        return $key;
    }
    
    /**
     * Sla een waarde ENCRYPTED op in een Cookie.
     *
     * @param string $name - cookie name
     * @param mixed $cookieData - cookie data
     * @param string $key - crypto key
     *
     * @return bool
     */

    function setSafe($name, $cookieData, $key){
        try {
            $ciphertext = Crypto::Encrypt(json_encode($cookieData), $key);
        } catch (CryptoTestFailedException $ex) {
            die('Kan niet veilig encrypten.');
        } catch (CannotPerformOperationException $ex) {
            die('Kan niet veilig decrypten');
        }

        return setcookie($name, $ciphertext);
    }

    /**
     * Decrypt a cookie, expand to array
     *
     * @param string $name - cookie name
     * @param string $key - crypto key
     *
     * @return bool
     */
    function getSafe($name, $key){
        if (!isset($_COOKIE[$name])) {
            return array();
        }
        $ciphertext = $_COOKIE[$name];

        try {
            $decrypted = Crypto::Decrypt($ciphertext, $key);
        } catch (InvalidCiphertextException $ex) { // VERY IMPORTANT
            // Either:
            //   1. The ciphertext was modified by the attacker,
            //   2. The key is wrong, or
            //   3. $ciphertext is not a valid ciphertext or was corrupted.
            // Assume the worst.
            die('Crypto::Decrypt');
        } catch (CryptoTestFailedException $ex) {
            die('Cannot safely perform encryption');
        } catch (CannotPerformOperationException $ex) {
            die('Cannot safely perform decryption');
        }
        if (empty($decrypted)) {
            array();
        }

        return json_decode($decrypted, true);
    }

    /**
     * Kijkt of het cookie bestaat.
     * 
     * @param text $name
     * @return bool
     */
    public static function exists($name) {
        return (isset($_COOKIE[$name])) ? true : false;
    }
    
    /**
     * Probeert informatie uit het cookie te parsen.
     * 
     * @param text $name
     * @return bool
     */
//    public static function get($name) {
//        return (isset(\filter_input(INPUT_COOKIE, $_COOKIE[$name]))) ? filter_input(INPUT_COOKIE, $_COOKIE[$name]) : false;
//    }
    
    /**
     * Voegt informatie toe aan een bestaand of nieuw cookie.
     * 
     * @param text $name
     * @param text $content
     * @param date $expire
     * @return bool
     */
    public static function put($name, $content, $expire) {
        return (setcookie($name, $content, time() + $expire, "/")) ? true : false;
    }
    
    /**
     * Delete een cookie.
     * 
     * @param text $name
     */
    public static function destroy($name) {
        self::put($name, "", time() - 1);
    }
}