<?php

/*
 * Config.class.php
 */

/**
 * Description of Config.class.php
 * 
 * Parses configuratie.
 *
 * @author marc
 */

class Config {
    
    /**
     * Hij gebruikt hier de array $GLOBALS['config']
     * 
     * Path = (Catagorie...)/(Onderwerp..)/(etc.)  <-- Alleen in deze context.
     * 
     * Voor een voorbeeld bekijk het configuratie bestand conf.php
     * 
     * @param text $path (format: foo/bar)
     * @return config (on true)
     * @return bool (on false)
     */
    public static function get($path = null) {
        if($path) {
            $path = strtolower($path);
            $config = $GLOBALS['config'];
            $path   = explode('/', $path);
            
            foreach ($path as $bit) {
                if(isset($config[$bit])){
                    $config = $config[$bit];
                }
            }
            
            return $config;
        }
        return false;
    }
 
/**
 * Zoekt alle files uit een directory.
 * 
 * Deze is ongetest!!
 * 
 * @param text $dir
 * @return type
 */    
 function find_all_files($dir) {
    $root = scandir($dir);
    foreach($root as $value) {
        if($value === '.' || $value === '..') {continue;}
        if(is_file("$dir/$value")) {$result[]="$dir/$value";continue;}
        foreach(find_all_files("$dir/$value") as $value){
            $result[]=$value;
        }
    }
    return $result;
} 
    
    
}