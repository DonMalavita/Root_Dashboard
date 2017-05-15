<?php
/**
 * Beschrijving Log.class.php
 *
 * Een class met alle methods om errors
 * te behandelen.
 *
 * @Author Marc
 */
class Log {

    const EMERGENCY = 'Noodgeval';
    const ALERT     = 'Waarschuwing';
    const CRITICAL  = 'Kritiek';
    const ERROR     = 'FOUT';
    const WARNING   = 'Waarschuwing';
    const NOTICE    = 'Meededeling';
    const INFO      = 'Info';
    const DEBUG     = 'Debug';

    /**
     * Log constructor.
     */
//    public function __construct() {
//
//    }

    /**
     * Functie: log2db
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param null $info
     * @return bool
     */
    public function log2db($errno, $errstr, $errfile, $errline, $info = null) {
        if(!empty($errno) && !empty($errstr) && !empty($errfile) && !empty($errline)) {
            $_db = DB::getInstance();
                if($_db->insert('errors', array(
                                'type' => $errno,
                                'msg' => $errstr,
                                'info' => $info,
                                'line' => $errline,
                                'file' => $errfile
                                ))
                ) {
                return true;
                }
        }
        return true;
    }

    /**
     * Functie: formatText
     * @param array $data
     */
    public function formatText($data = array())
    {

        list($usec, $sec) = explode(' ', microtime());
            $datetime = strftime("%m%d%Y %H:%M:%S", time());
            
    
            $msg = Sanitize::Cleanup($_SERVER["REMOTE_HOST"]) . "@" . "$datetime'" . sprintf("%06s", intval($usec * 1000000)) . ": \n";



    }
    
    public function writeFile() 
    {
        $path = Config::get("log/dir") . "app-log-notices-" . date("Y-m-d_H:i:s") . ".txt";
    }
    /**
     * Functie: Write
     * @param $title
     * @param $data
     * @return bool
     * @throws Exception
     * @internal param $msg
     */
    function Write($title, $data) {
        if (is_string($title) && is_string($data)) {
            list($usec, $sec) = explode(' ', microtime());
            $datetime = strftime("%m%d%Y %H:%M:%S", time());
            $fp = Config::get("includes/logs-dir") . "app-log-notices-" . date("Y-m-d_H:i:s") . ".txt";

            $msg = "$datetime'" . sprintf("%06s", intval($usec * 1000000)) . "@" . \Clean\Clean::clean($_SERVER["REMOTE_HOST"]) . ": \n" . $data["err_msg"];

            $fp = (@fopen($fp)) ? fputs($fp, "$msg\n") : trigger_error("Kan de log-file niet openen.\nDirectorie: " . $fp . "\nRemote Host: " . Clean::clean($_SERVER["REMOTE_HOST"]));
            fclose($fp); // close the file
            if(empty($fp)) {
                throw new Exception("[-] WriteLog faalt");
            }
        } else {
            return false;
        }
    }

    /**
     * Functie: errLog
     * @param $data
     * @return bool
     * @throws Exception
     */
    function errLog($data = array()) {
        if(is_array($data) && !empty($errno) && !empty($errstr) && !empty($errfile) && !empty($errline)) {
            $path = Config::get("log/dir") . "app-log-" . date("Y-m-d_H:i:s") . ".txt";
            $fp = fopen($path ,"a");
                if($fp){
                    $msg = "err_str:" . $errstr . '\n';
                    $msg .= "err_type:" . $errno . '\n';
                    $msg .= "err_date:" . date("H:i:s - Y/m/d") . '\n';
                    $msg .= "r_host:" . Validate::filter($_SERVER['REMOTE_HOST']) . '\n';
                    $msg .= "err_file:" . $errfile . '(lijn: )' . $errline . '\n';

                    $msg_clean = Sanitize::trim($msg);

                    fwrite($fp, "\t$msg_clean\r\n");
                    fclose($fp);
                } else {
                    throw new Exception("[+] Error - Rerouted from");
                }

            } else {
                return false;
            }
    }


    /**
     * Functie: log2file
     * @param $errno
     * @param $errstr
     * @param $errfile
     * @param $errline
     * @param null $info
     * @return bool
     * @throws Exception
     */
    public static function log2file($errno, $errstr, $errfile, $errline, $info = null) {
      if(!empty($errno) && !empty($errstr) && !empty($errfile) && !empty($errline)) {
        $path = Config::get("log/dir") . "app-log-" . date("Y-m-d_H:i:s") . ".txt";
            $fp = fopen($path ,"a");
              if($fp){
                  $msg  = "err_date:" . date("H:i:s - Y/m/d") . '\n';
                  $msg .= "err_:" . $errno . '\n';
                  $msg .= "err_str:" . $errstr . '\n';
                  $msg .= "r_host:" . Validate::filter($_SERVER['REMOTE_HOST']) . '\n';
                  $msg .= "err_file:" . $errfile . '(lijn: )' . $errline . '\n';

                  $msg_clean = Sanitizer::trim($msg);

                  fwrite($fp, "\t$msg_clean\r\n");
                  fclose($fp);
              } else {
                  throw new Exception("[+] Error - Rerouted from");
                  exit();
              }

      } else {
        return false;
      }
    }
    
    /**
     * Functie: mailAdmin
     * @param $subject
     * @param $msg
     * @param $receiver
     * @param array $extra_headers
     * @return bool
     */
    public function mailAdmin($subject, $msg, $receiver, $extra_headers = array()) {
        //Building HTML headers;
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        //End of HTML headers;
        $headers .= 'From: ' .$this->_name_watcher. '<' .$this->_email_watcher. '>' . "\r\n";
        if(!empty($extra_headers)) {
            $headers .= $extra_headers;
        }
        $mail = mail($receiver, $subject, $msg, $extra_headers);
        return (isset($mail)) ? true : false;
    }
}
