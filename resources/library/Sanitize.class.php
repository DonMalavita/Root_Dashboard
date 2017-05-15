<?php
namespace Sanitize;
/**
 * Sanitize
 * ------------------------------
 * Een schoonmaker voor ALLÉ input!
 *
 * @author Marc
 */
class Sanitize {

    public static $regexes = Array(
        'date' => "^[0-9]{1,2}[-/][0-9]{1,2}[-/][0-9]{4}\$",
        'amount' => "^[-]?[0-9]+\$",
        'number' => "^[-]?[0-9,]+\$",
        'alfanum' => "^[0-9a-zA-Z ,.-_\\s\?\!]+\$",
        'not_empty' => "[a-z0-9A-Z]+",
        'words' => "^[A-Za-z]+[A-Za-z \\s]*\$",
        'phone' => "^[0-9]{10,11}\$",
        'zipcode' => "^[1-9][0-9]{3}[a-zA-Z]{2}\$",
        'plate' => "^([0-9a-zA-Z]{2}[-]){2}[0-9a-zA-Z]{2}\$",
        'price' => "^[0-9.,]*(([.,][-])|([.,][0-9]{2}))?\$",
        '2digitopt' => "^\d+(\,\d{2})?\$",
        '2digitforce' => "^\d+\,\d\d\$",
        'anything' => "^[\d\D]{1,}\$"
    );

    /**
     * @ORM\Column(type="string")
     */
    private $_escaped = array();
    static $_cleaned;

    /**
     *
     * Sanatize a single var according to $type.
     * Allows for static calling to allow simple sanatization
     * @param $var
     * @param $type
     * @return
     */
    public static function Cleanup($var, $type)
    {
        $flags = NULL;
        switch($type)
        {
            case 'url':
                $filter = FILTER_SANITIZE_URL;
                break;
            case 'int':
                $filter = FILTER_SANITIZE_NUMBER_INT;
                break;
            case 'float':
                $filter = FILTER_SANITIZE_NUMBER_FLOAT;
                $flags = FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND;
                break;
            case 'email':
                $var = substr($var, 0, 254);
                $filter = FILTER_SANITIZE_EMAIL;
                break;
            case 'string':
                $filter = FILTER_SANITIZE_STRING;
                $flags = FILTER_FLAG_NO_ENCODE_QUOTES;
                break;
            default:
                $flags = FILTER_FLAG_NO_ENCODE_QUOTES;
                break;

        }
        $output = filter_var($var, $filter, $flags);
        return($output);
    }

    /**
     * Gewoon een simpel en snelle trim() & strip_tag()
     *
     * @param $string
     * @return bool|string
     */
    public static function trim($string) {
        if (is_array($string)) {
            array_walk($string, "trim");
            return $string;
        }
        elseif (is_string($string)) {
            trim($string);
            return $string;
        }
        return false;
    }

    /**
     *
     *
     * @param $string
     * @return array
     */
    public static function escape($string) {
        if(is_array($string)) {
            foreach ($string as $dirty) {
                Self::$_escaped = Self::escape($dirty);
            }
        }
        elseif(is_string($string)) {
            Self::$_escaped = mysqli_real_escape_string($string);
        }
        return (Self::$_escaped !== false) ? true : false;

    }
}
