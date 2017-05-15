<?php
/**
 * Error.class.php
 *
 * @author Marc
 */
class ErrorHandler {


static $errorType = array (
				E_ERROR                => 'Error',
				E_WARNING              => 'Warning',
				E_NOTICE               => 'Notice',
				E_USER_ERROR           => 'User Error',
				E_USER_WARNING         => 'User Warning',
				E_USER_NOTICE          => 'User Notice');
    
    /**
     * Voegt error data toe aan een HTML file voor verdere debugging.
     */
    // public static function addError() { //Check Log.class.php
    //
    // }

    /**
     * Voegt een notice door aan een HTML file voor verdere debugging.
     */
    // public static function addNotice() { //Check Log.class.php
    //
    // }


    /**
     * ErrorHandler constructor.
     */
    public function __construct() {
      @set_error_handler(array($this, 'catchError'));
        trigger_error('[+] Succes: ErrorHandler geregistreert en draaiende.', E_USER_NOTICE);

      @set_exception_handler(array($this, 'catchException'));
           throw new Exception('Exceptions worden nu onderschept, en gelogd.');
    }

    /**
     * Functie: error
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @return bool
     */
    function catchError($errno, $errstr, $errfile, $errline) {

//    $_email_watcher = 'watcher@example.org';
//    $_email_subject = 'Kritische fout: ';
//    $_name_watcher = 'Watcher 2.0';
    
    //Create objects;
    $log = new Log();
//    $db = new DB();

    switch ($errno) {
        case E_ERROR:
            Log::log2file($errno,$errstr,$errfile,$errline);
            echo "<div class=msg.error><strong>" . $errno . ":</strong><br /><br />$errstr<br /></div>\n";
            break;

        case E_WARNING:
            echo "<div class=msg.error><strong>" . $errno . ":</strong><br /><br />$errstr<br /></div>\n";
            break;

        case E_PARSE:
            echo "<div class=msg.error><strong>" . $errno . ":</strong><br /><br />$errstr<br /></div>\n";
            break;

        case E_NOTICE:
            echo "<div class=msg-error><strong>" . $errno . ":</strong><br />$errstr</div><br /></div>\n";
            break;
        case E_USER_ERROR:
            //Fixing a detailed message
            $info = "Level: " . $errno . "<br />";
            $info .= "Bestand: " . $errfile . "<br />";
			$info .= "";

            //$sub = $this->_email_subject . $_SERVER['SERVER_NAME'];//Define email subject

            $log->log_to_db($errno, $errstr, $errfile, $errline, $info);//Log to dbase

            //$log->mail($sub,$msg,$receiver,$extra_headers); //Inform the watcher/admin
            if (isset($debug)) { //Are we in debug mode?
                echo $info;
            } else {
                echo "Afsluiten...<br />\n";//Afsluiten en redirecten naar een 404
                URL::header(404);
            }
            exit(1);
            break;

        case E_USER_WARNING:
            echo "<div class=msg error><strong>" . $errorType[$errno] . ":</strong><br /><br />$errstr<br /></div>\n";
            break;

        case E_USER_NOTICE:
            echo "<div class=msg-error><strong>" . $errorType[$errno] . ":</strong><br />$errstr<br /></div>\n";
            break;
        default:
            echo "<div class=msg.error><em>Onbekende foutmelding:</em><br /><br /> [$errno] $errstr<br /></div>\n";
            break;
    }
        return true;
}

}
