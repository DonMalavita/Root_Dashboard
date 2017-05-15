<?php
use Doctrine\ORM\Mapping as ORM;

/**
 * Base.class.php
 *
 * @Author Marc
 */
class Base {
    
    private static $_timezone,
                    $_algo_key,
                    $_iv;

    

    /**
     * Functie: setTZ
     * @param null $timezone
     * @return bool
     */
    public static function setTZ($timezone = NULL) {
        if(empty($timezone)) {
            Self::$_timezone = Config::get("app/timezone");
            date_default_timezone_set(Self::$_timezone);
        }
        return false;
    }
    
    
}
