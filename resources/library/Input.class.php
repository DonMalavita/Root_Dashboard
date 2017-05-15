<?php
/**
 * Input wrapper.
 */
class Input {
    
    /**
     * Kijkt of $_POST of $_GET een waarde heeft.
     * 
     * @param type $type
     * @return type
     */
    public function exists($type = 'post') {
        switch ($type) {
            case 'post':
                return (!empty($_POST)) ? true : false;
            break;
            case 'get':
                return (!empty($_GET)) ? true : false;
            break;
        }
    }
    
    /**
     * Retrieved een waarde uit $_POST of $_GET.
     * 
     * @param type $item
     * @return string
     */
    public static function get($item) {
        if(isset($_POST[$item])) {
            return $_POST[$item];
        } elseif(isset($_GET[$item])) {
            return $_GET[$item];
        }
        return '';
    }
    
}