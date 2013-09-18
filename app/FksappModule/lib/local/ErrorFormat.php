<?php

namespace localError;


class NiceError 
    {
        public function __construct() {
        }



        private 
    }
/**
 * return "nice" error message
*/
function niceErr() {
    $trace  = debug_backtrace();
    $caller = array_shift($trace);

    $err_string = "";
    return ;
}
