<?php


/**
 * -----------------------------------
 * Deze class bevat alle methods ter
 * beveiliging van sessies / cookies / Captcha's
 * 
 * @author Marc
 */
class Security {
    
    private static  $_new_agent,
                    $_cur_agent,
                    $_msg,
                    $_rand = NULL,
                    $_basedir,
                    $path_parts,
                    $_final_img = null;

	/**
     * Functie: checkDirectory
     * @param $dir
     */
    public function checkDirectory($dir)
    {
        // declare the basic directory for security reasons
        // Please do NOT attach a "/"-suffix !
        Self::$_basedir = Config::get("app/url");

        // compare the entered path with the basedir
        Self::$path_parts = pathinfo($dir);
        if (realpath(Self::$path_parts['dirname']) != Self::$_basedir) {
            trigger_error("Validatie van directorie " . Self::$path_parts . " foutgegaan.",E_USER_ERROR);
        }
    }

    /**
 * getUserAgent()
 * checkUserAgent()
 * -------------------------
 * Deze methods worden gebruikt om
 * sessies te beveiligen.
 * 
 * 
 * Call checkUserAgent()
 * Die checked of de user agent overeen komt
 * met diegene die is opgeslagen in $_SERVER
 * en onderneemt actie, atm word hij/zij die zal bij
 * true niets doen maar als de user agent 
 * NIET MATCHED word je uitgelogd en
 * geredirect naar de login pagina voor een 
 * checkup.
*/    
    public function checkUserAgent() {
        Self::$_new_agent = ($_SESSION['HTTP_USER_AGENT'] !== $_SERVER['HTTP_USER_AGENT']) ? false : $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);
        Self::$_cur_agent = md5($_SERVER['HTTP_USER_AGENT']);

        Self::$_msg = "Er is een fout opgetreden betreft uw login, graag opnieuw inloggen zodat we weten wie u bent.<br />Sorrie voor het ongemak.";
        return (isset($_SESSION['HTTP_USER_AGENT']) && Self::$_cur_agent !== Self::$_new_agent) ? User::logout() && Session::flash('msg_login',Self::$_msg) && URL::redirect("members/login") : true;
    }
    
    /**
     * getRandom() genereert een unieke waarde
     * 
     * @return type
     */
    public function getRandom() { //Getest!!
        Self::$_rand = rand(10000, 100000);
        $_SESSION['phrase'] = Self::$_rand;
        return ($_SESSION['phrase'] !== 0) ? Self::$_rand : false;
    }
    
    /**
     * pullimage(); Genereert een image met een unieke waarde gegenereerd door getRandom();
     * 
     * @return type Een image
     */    
    public static function pullImage() { //Getest!!
        $img = imagecreate(Config::get("captcha/img/width"), Config::get("captcha/img/height"));
        $bg_color = imagecolorallocate($img,Config::get("captcha/img/rgb/red"),Config::get("captcha/img/rgb/green"),Config::get("captcha/img/rgb/blue"));
        $txt_color = imagecolorallocate($img, 255, 255, 255); //Wit.
        imagesetthickness($img, 2);
        imageline($img, 1, 25, 100, 25, $txt_color);
        
        imagettftext($img,20,0,Config::get("captcha/img/x"),Config::get("captcha/img/y"),$txt_color,Config::get("captcha/img/font"),self::getRandom());

        header('Content-type: image/png');
        imagepng($img);//Return image.
        imagedestroy($img); //Vernietigen.
    }
    
    /**
     * kijkt of $code overeenkomt met de opgeslagen waarde.
     * 
     * De code staat opgeslagen in $_SESSION['phrase'] en binnen deze class
     * in self::_rand
     * 
     * @param number $code
     * @return boolean
     */
    public static function CheckCaptcha($code = false) {
        if(!is_numeric($code)) {
            return false;
        }
        
        echo '<strong>Saved var:  </strong>' . Self::$_rand.'<br>';
        echo '<strong>Input:  </strong>' . $code.'<br>';
        echo '<strong>Session:  </strong>' . $_SESSION['phrase'];
        
        //return ($code === $cur) ? true : false;
    }
}