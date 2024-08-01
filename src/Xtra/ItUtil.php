<?php
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpRedundantOptionalArgumentInspection */

namespace It\Xtra;

class ItUtil {

    private function __construct() {}
    private function __clone() {}
    private function __wakeup() {}
    
    /**
     * A unique 16 char hex number, using xxh64 hash algorithm.
     * @param string $string
     * @return string a hopefully unique 16 char hex number
     */
    public static function hashHex($string):string {return hash('xxh64', $string, FALSE);}

}