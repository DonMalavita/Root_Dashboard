<?php
/**
 * AutoLoader.class.php
 * --------------------
 * Registreert een simpele class loader.
 * Gebruik:
 * $loader = new AutoLoader;
 * // Hiermee activeer je hem.
 *
 * En gebruik:
 *
 */
class Autoloader {

    private static $regex_s = array("/","\\");

    /**
     * @ORM\Column(type="string")
     */
    private $_is_running = null,
            $_class_dir,
            $_fp;

    /**
     * Autoloader constructor.
     */
    public function __construct() {
        @set_include_path(array(
            'C:\\wamp\\apache2\\htdocs\\Root_Dashboard\\resources\\library\\',
            'C:\\wamp\\apache2\\htdocs\\Root_Dashboard\\resources\\library\\frameworks\\'));
        spl_autoload_extensions('.class.php');
        spl_autoload_register(array($this, 'loader'));
        $this->_is_running = true;
    }

    /**
     * @param $className
     */
    public function loader($className) {
        
        $pre_approved = array(
            'C:\\wamp\\apache2\\htdocs\\Root_Dashboard\\resources\\library\\',
            'C:\\wamp\\apache2\\htdocs\\Root_Dashboard\\resources\\library\\frameworks\\');
        
        $c = count($pre_approved);
        $s = 0;
        
        while (isset($pre_approved))
        {
            if(is_readable(FileHandler::ds($pre_approved[$s])))
            {
                
            }
        }
        
        //$this->_class_dir = $GLOBALS['config']['includes']['lib'] . DIRECTORY_SEPARATOR;
        $this->_fp = $className . ".class.php";
        echo 'Probeert om ', $this->_fp, ' te benaderen via ', __METHOD__, "()<br /";
        ($this->getInstance() !== false) && (is_readable($this->_fp)) ? include $this->_fp : die("Autoloader faalt te starten");

    }


    /**
     * @return string
     */
    public function getInstance() {
        return ($this->_is_running === true) ? true : false;
    }
}

