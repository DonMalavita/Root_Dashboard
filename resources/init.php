<?php
#Constants
define('PROJECT_DIR', realpath(__DIR__ . DIRECTORY_SEPARATOR . '..'));
define('CSS_DIR', dirname(__FILE__) . '/public_html/css');

// Require_once hieronder:
require PROJECT_DIR . '\resources\core\app_conf\config.php';
require PROJECT_DIR . '\resources\vendor\autoload.php';
require PROJECT_DIR . '\resources\library\Autoloader.php';

//$str = str_replace(array('\\','/'), DIRECTORY_SEPARATOR, PROJECT_DIR . '/resources/library/');
//echo $str . '<br />';

$al = new Autoloader;

if (empty($al->getInstance())) {
	echo "Autoloader::getInstance() returned false<br />";
} else {
	echo "Autoloader draait.<br /><br />";
}

$smarty = new Smarty();

define('DEBUG', Config::get("admin/debug"));

$blaat 	= microtime(true); // Voor laadtijd.

// Errors
//set_error_handler("log_error");
//error_reporting(E_NOTICE);
//

/**
* Hier instantieert hij classes/functies, die nodig zijn om cookies en sessies te maken.
*/

//if(Cookie::exists(Config::get('remember/cookie_name')) && Session::exists(Config::get('session/session_name'))) {
//    $hash = Cookie::get(Config::get('remember/cookie_name')); // BELANGRIJK: Hier extract hij de waarde van de cookie.
//    $hashCheck = DB::getInstance()->get('user_sessions', array('hash', '=', $hash));
//
//   if($hashCheck->count()) {
//        $user = new User($hashCheck->first()->user_id);
//        $user->login();
//    }
//}