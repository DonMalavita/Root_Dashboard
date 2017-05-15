<?php
/**
 * Created by:
 * User: mdewi
 * Date: 10-5-2017
 * Time: 00:16
 */

namespace DebugSetup;


/**
 * Class Debug
 * @package DebugSetup
 *
 * Omschrijving:
 *
 */
class Debug {

    private $stack_trace;

    /**
     * Debug constructor.
     */
//    function __construct() {
//
//    }
//
//    function __destruct() {
//
//    }

    /**
     * Functie: getLastTrace
     *
     * Vraag de laatst uitgevoerde stacktrace op.
     *
     * @return mixed
     */
    public function getLastTrace() {
        return isset($this->stack_trace) ? $this->stack_trace : $this->Trace();
    }

    /**
     * Functie: Trace
     * @return string
     */
    public function Trace() {

            $e = new Exception();

          $trace =  explode("\n", $e->getTraceAsString());
          $trace =  array_reverse($trace);
                    array_shift($trace); // remove {main}
                    array_pop($trace); // remove call to this method
          $length = count($trace);
          $result = array();

        for ($i = 0; $i < $length; $i++) {
            $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
        }

        return (empty(Self::$stack_trace)) ? Self::$stack_trace = "\t" . implode("\n\t", $result) : Self::$stack_trace; // Bewaart de stacktrace in $this->stack_trace;
    }

}