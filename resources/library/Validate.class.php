<?php

/**
 * Class Validate
 *
 * Omschrijving:
 *
 */
class Validate extends Sanitizer {
    
    private $_passed = false,
            $_errors = array(),
            $_db = null,
            $_error_class;

    /**
     * Validate constructor.
     */
    public function __construct()
    {
              $this->_db = DB::getInstance();
              $this->_error_class = new Error();
    }

    /**
     * @param $source
     * @param array $items
     */
    public function addRule($source, $items = array())
    {

              foreach($items as $item => $rules) {
                  foreach($rules as $rule => $rule_value) {

                      $value = $this->escape($source[$item]);

                      if($rule === 'required' && empty($value)) {
                          $this->addError("{$item} moet ingevuld worden.");
                      } 
                      elseif(!empty($value)) {
                          switch ($rule) {
                              case "min":
                                   if(strlen($value) < $rule_value) {
                                     $this->addError("<font color=red><strong>Fout:</strong></font> <u>{$item} moet minimaal {$rule_value} characters zijn.</u>");
                                     }
                              break;
                              case "max":
                                   if(strlen($value) > $rule_value) {
                                     $this->addError("<font color=red><strong>Fout:</strong></font> <u>{$item} moet maximaal {$rule_value} characters bevatten.</u>");
                                   }
                              break;
                              case "matches":
                                   if($value != $source[$rule_value]) {
                                     $this->addError("<font color=red><strong>Fout:</strong></font> <u>{$item} moet gelijk zijn aan {$rule_value}.</u>");
                                   }
                              break;
                              case "unique":
                                   $check = $this->_db->get($this->escape($rule_value), array($this->escape($item), '=', $this->escape($value)));
                                      if($check->count()) {
                                         $this->addError("<font color=red><strong>Fout:</strong></font> <u>{$item} {$value} bestaat al.</u>");
                                      }
                              break;


                              default:
                              break;
                          }
                      }

                  }
              }

              if(empty($this->_errors)) {
                  $this->_passed = true;
              }
          }

    /**
     *
     * Validates a single var according to $type.
     * Allows for static calling to allow simple validation.
     * @param $var
     * @param $type
     * @return bool
     */
    public static function filter($var, $type)
    {
        if(array_key_exists($type, self::$regexes))
        {
            $returnval =  filter_var($var, FILTER_VALIDATE_REGEXP, array("options"=> array("regexp"=>'!'.self::$regexes[$type].'!i'))) !== false;
            return($returnval);
        }
        $filter = false;
        switch($type)
        {
            case 'email':
                $var = substr($var, 0, 254);
                $filter = FILTER_VALIDATE_EMAIL;
                break;
            case 'int':
                $filter = FILTER_VALIDATE_INT;
                break;
            case 'boolean':
                $filter = FILTER_VALIDATE_BOOLEAN;
                break;
            case 'ip':
                $filter = FILTER_VALIDATE_IP;
                break;
            case 'url':
                $filter = FILTER_VALIDATE_URL;
                break;
        }
        return ($filter === false) ? false : filter_var($var, $filter);
    }

    /**
     * Functie: is_email
     * @param $email
     * @param bool $dns_check
     * @return bool
     */
    function is_email($email, $dns_check = true)
    {
        $array = explode('@', $email);
        if (count($array) < 2) {
            return false;
        }
        $domain = end($array);
        array_pop($array);
        if (function_exists('idn_to_ascii')) {
            //php filter no workie with unicode characters
            $domain = idn_to_ascii($domain);
        }
        $ipcheck = preg_replace(array('/^\[ipv6\:/i', '/^\[/', '/\]$/'), '', $domain);
        if (filter_var($ipcheck, FILTER_VALIDATE_IP)) {
            // it's an IP address
            if (!filter_var($ipcheck, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return FALSE;
            }
        } else {
            // it's a domain name
            //   php bug - FILTER_VALIDATE_EMAIL doesn't like naked TLD
            if (!filter_var('user@a.' . $domain, FILTER_VALIDATE_EMAIL)) {
                return FALSE;
            }
            if ($dns_check) {
                if (!dns_get_record($domain)) {
                    return FALSE;
                }
            }
        }
        return;
    }

    /**
     * Functie: errors
     * @return array
     */
    public function errors()
    {
        return $this->_errors;
    }

    /**
     * Functie: passed
     * @return bool
     */
    public function passed()
    {
        return $this->_passed;
    }

    /**
     * Functie: addError
     * @param $error
     */
    private function addError($error)
    {
        $this->_errors[] = $error;
    }
}
