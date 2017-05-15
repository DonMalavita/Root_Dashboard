<?php

/**
 * URL.class.php
 * ---------------------------------------------
 *
 * Bevat alle methods betreft het handelen
 * met URLs.
 *
 * // Redirect naar error pages.
 * header();
 * @param  string (uitsluitend)
 * @output header();
 *
 * // Redirect op basis van redirects.php ( <-- Config )
 * redirect();
 * @param  string (uitsluitend)
 * @output header("Location: ". @param);
 *
 * // Checked of de URL bereikbaar is icm cURL
 * isDomain();
 * @param string met URL
 * @output boolean
 *
 * // Checked of input een URL is op basis van regexp
 * isURL();
 * @param string met "URL"
 * @output boolean
 *
 * // Genereert een hash
 * hash();
 * @param string met id / waardevolle informatie
 * @output hash gemaakt van bovenstaande informatie
 *
 * decrypt(); // Decrypt een hash
 * @param string met hash
 * @output string met originele informatie
 *
 * @author Marc
 */

class URL {

    public function header($loc = null,$file = null) {
            if(isset($loc)) {
                if(is_numeric($loc)){
                    switch ($loc) {
                        case 'img':
                            if(isset($file)) {
                                header('Content-type: image/png');
                                (file_exists($file)) ? imgpng($file) : "Kan de opgegeven image niet vinden.";
                            }
                        break;
                        case 400:
                            $msg = '';
                            header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request");
                            include Config::get("includes/error-templ") . '/errors/error.php';
                            exit();
                        break;
                        case 401:
                            $msg = 'U heeft niet de juiste machtigingen om door te gaan.';
                            header($_SERVER["SERVER_PROTOCOL"]." 401 Unauthorized");
                            include Config::get("includes/error-templ") . '/errors/error.php';
                            exit();
                        break;
                        case 403:
                            $msg = 'U heeft niet de juiste machtigingen om door te gaan.';
                            header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden");
                            include Config::get("includes/error-templ") . '/errors/error.php';
                            exit();
                        break;
                        case 404:
                            $msg = 'Oops... het lijkt erop dat u een dode link te pakken heeft, mijn excuses hiervoor.';
                            header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
                           ((file_exists(Config::get("includes/error-templ") . '/errors/404.php'))) ? include_once Config::get("includes/error-templ") . '/errors/404.php' : false;
                            exit();
                        break;
                        case 500:
                            $msg = 'Fout: 500<br />Whoops... daar ging iets gigantisch fout.';
                            header($_SERVER["SERVER_PROTOCOL"]." 500 Internal Server Error");
                            include Config::get("includes/error-templ") . '/errors/error.php';
                            exit();
                        break;
                    }
                }

                exit();


        }
    }

    public function redirect($path = null) {
         if(isset($path)) {
            $path = strtolower($path);
            $location = $GLOBALS['redirect'];
            $page = explode('/', $path);

            if(count($page) > 0) {
                foreach ($page as $val) {
                    if(isset($location[$val])) {
                        $location = $location[$val];
                    }
                }

                //$adres_check = $this->isURL($location);

                $adres = (filter_var($location, FILTER_VALIDATE_URL)) ? true : false;  // Checked of het opgegeven pad een URL is.

                echo $adres;
                //((isset($adres))) ? header("Location: " . $location) : header("Location: " . Config::get("site/base-url") . $location);

             }
         }
    }

    public function isDomain($domain) {
               if(!filter_var($domain, FILTER_VALIDATE_URL)) {
                       return false;
               }

               $curlInit = curl_init($domain);
               curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
               curl_setopt($curlInit,CURLOPT_HEADER,true);
               curl_setopt($curlInit,CURLOPT_NOBODY,true);
               curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

               $response = curl_exec($curlInit);

               curl_close($curlInit);

               return (isset($response)) ? true : false;
       }

    public function isURL($url) {
        return (preg_match("%^((https?://)|(www\.))([a-z0-9-].?)+(:[0-9]+)?(/.*)?$%i", $url)) ? $url : false;
    }

    public function hash($key) {
        $hash = mcrypt_ecb (MCRYPT_3DES, Config::get("members/hash-key"), $key, MCRYPT_ENCRYPT);  //encrypt using triple DES
        return ((isset($hash))) ? urlencode(base64_encode($hash)) : false;
    }

    public function decrypt($_id) {
        $decrypted_data = mcrypt_decrypt(MCRYPT_BLOWFISH,Config::get("members/hash-key"),base64_decode(urldecode($_id)), MCRYPT_MODE_CBC);
        return ((isset($decrypted_data))) ? urlencode(base64_encode($decrypted_data)) : false;
    }
}
