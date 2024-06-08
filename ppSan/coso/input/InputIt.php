<?php
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpUnused */

namespace coso\input;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Stringable;
use Throwable;

/** User Input */
// validate enum ymd, h:m:s, ymd h:m:s
class InputIt {

    /** read user input */
    function inputRaw():string|false {
        return file_get_contents('php://input');
    }

    function inputJsonToPost():array {
        try {
            $postData = trim(file_get_contents('php://input') ?? '' );
            if(empty($postData)) {
                return $_POST ?? [];
            }
            return $_POST = array_merge(
                $_POST ?? [],
                json_decode($postData, true,
                    flags: JSON_BIGINT_AS_STRING | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
                ),
            );
        } catch(Throwable) {
            return $_POST ?? [];
        }
    }

    function request($key, $default):string|array {
        if(is_array($key)) {
            $ret = [];
            foreach($key as $k) {
                $ret[$k] = $_REQUEST[$k] ?? $default;
            }
            return $ret;
        }
        return $_REQUEST[$key] ?? $default;
    }

    function post($key, $default):string|array {
        if(is_array($key)) {
            $ret = [];
            foreach($key as $k) {
                $ret[$k] = $_POST[$k] ?? $default;
            }
            return $ret;
        }
        return $_POST[$key] ?? $default;
    }

    function get($key, $default):string|array {
        if(is_array($key)) {
            $ret = [];
            foreach($key as $k) {
                $ret[$k] = $_GET[$k] ?? $default;
            }
            return $ret;
        }
        return $_GET[$key] ?? $default;
    }

    // Clean input

    /**
     * Trim a string, or array of strings, to a one liner with single spaces, converting null to empty string
     * Purpose: Remove problems with leading, trailing and multiple spaces in searches, formatting,...
     *
     * @param string|Stringable|int|float|bool|null|array<mixed,string|Stringable|int|float|bool|null|array> $str
     * @return string|array<mixed,string|array>
     */
    function trimSuper(string|Stringable|int|float|bool|array|null $str):string|array {
        if($str === null) {
            return '';
        }
        if($str instanceof Stringable) {
            $str = (string)$str;
        }
        if(is_array($str)) {
            foreach($str as &$d) {
                $d = $this->trimSuper($d);
            }
            return $str;
        }
        $trimmed = preg_replace('/[\pZ\pC]/muS', ' ',trim("$str") );
        if($trimmed !== null) {
            $trimmed = preg_replace('/ {2,}/muS', ' ', $trimmed);
            if($trimmed !== null) {
                return trim($trimmed);
            }
        }
        // fallback when there is a preg_replace error
        $str = "$str";
        do {
            $prev = $str;
            $str = str_replace('  ', ' ', $str);
        } while($prev !== $str);
        return trim($str);
    }

    /**
     * @param string|Stringable|int|float|bool|null|array $number
     * @param $default
     * @param $nullToDefault
     * @return string|null|array
     */
    function cleanNumber($number, $default = '0', $nullToDefault = true):string|null|array {
        if($number instanceof Stringable) {
            $number = (string)$number;
        }
        if(is_array($number)) {
            foreach($number as &$n) {
                $n = $this->cleanNumber($n, $default);
            }
            return $number;
        }
        if($number === NULL) {
            return $nullToDefault ? $default : NULL;
        }
        if($number === '') {
            return $default;
        }
        $result = preg_replace('/[\pZ\pC\p{Sc},\'`´~+%#]/S', '', "$number");
        if($result !== null) {
            return $result;
        }
        // preg error fallback
        $n = str_replace(['+', ',', "'", '`', '´', ' ',
            '$', '﹩', '＄', '💲',
            '£', '￡',
            '€', '¢', '¥',
            '%', '#' ],
            '',
            $this->trimSuper($number)
        );
        return $n === '' ? $default : $n;
    }

    /**
     * @param  string|null|array<mixed,string|null|array> $ymd
     * @param string|null|array<mixed,string|null|array> $default
     * @return string|null|array<mixed,string|null|array>
     */
    function cleanYMD($ymd, $default = NULL):string|null|array {
        if($ymd instanceof DateTimeInterface) {
            $ymd = $ymd->format("Y-m-d");
        }
        elseif($ymd instanceof Stringable) {
            $ymd = (string)$ymd;
        }
        elseif(is_array($ymd)) {
            foreach($ymd as &$d) {
                $d = $this->cleanYMD($d, $default);
            }
            return $ymd;
        }
        if(empty($ymd) || $ymd === $default || str_starts_with($ymd, '0000-00-00')) {
            return $default;
        }
        return $this->trimSuper($ymd);
    }

    /** Helpers */
    /**
     *
     * @param string|Stringable|null|array<int|string,string|Stringable|array> $value   minValue ... maxValue o minValue … maxValue donde 4 … es min=4 y … 5 max=5
     * @return array<string,string|array> ['min'=>value, 'max'=>value] o []
     */
    function rangeToMinMax($value):array {
        if($value instanceof Stringable) {
            $value = (string)$value;
        }
        if($value === '' || $value === NULL) {
            return [];
        }
        if(is_array($value)) {
            foreach($value as &$v) {
                if( !empty($v)) {
                    $v = $this->rangeToMinMax($v);
                }
            }
            return $value;
        }
        if(str_contains('...', "$value")) {
            $between = explode('...', "$value");
        }
        if(str_contains('…', "$value")) { // … \u2026 &#8230; &#x2026; &hellip;
            $between = explode('…', "$value");
        }
        if(empty($between)) {
            return [];
        }
        $min = $between[0];
        $max = $between[1] ?? '';
        if($min !== '' && $max !== '') {
            return $min <= $max ? ['min' => $min, 'max' => $max] : ['min' => $max, 'max' => $min];
        }
        if($min !== '') {
            return ['min' => $min];
        }
        return $max === '' ? [] : ['max' => $max];
    }

    /**
     * Ensure $start <= $end, else swap it. Returns true when swapped, false not swapped
     *
     * @param int|float|string|bool|null|DateTime|DateTimeImmutable $start
     * @param int|float|string|bool|null|DateTime|DateTimeImmutable $end
     * @return bool true swaped so $start < $end
     */
    public function ordered(&$start, &$end):bool {
        if($start <= $end) {
            return false;
        }
        $swap = $start;
        $start = $end;
        $end = $swap;
        return true;
    }

}

function is_container_number(string|Stringable $container_number):bool {
    $container_number = strtoupper( trim((string)$container_number) );
    if(!preg_match('/[A-Z]{4}[0-9]{7}/i', $container_number))
        return false;
    $code = [
        0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7, 8 => 8, 9 => 9,
        'A' => 10, 'B' => 12, 'C' => 13, 'D' => 14, 'E' => 15, 'F' => 16, 'G' => 17, 'H' => 18, 'I' => 19, 'J' => 20,
        'K' => 21, 'L' => 23, 'M' => 24, 'N' => 25, 'O' => 26, 'P' => 27, 'Q' => 28, 'R' => 29, 'S' => 30, 'T' => 31,
        'U' => 32, 'V' => 34, 'W' => 35, 'X' => 36, 'Y' => 37, 'Z' => 38
    ];
    $sum = 0; $m = 1; $len = strlen($container_number) - 1;
    for($i=0; $i < $len; ++$i) {
        $sum += $code[$container_number[$i]] * $m;
        $m = $m << 1;
    }
    $checkDigit = $sum - floor($sum/11) * 11;

    return $checkDigit == 10 ? $container_number[$len] == '0' : $checkDigit == $container_number[$len];
    // http://www.gvct.co.uk/2011/09/how-is-the-check-digit-of-a-container-calculated/
    /*
    $ok = 'Ok'; $mal = "<span style='color:red'>X</span>";
    $cn = 'ymlu8744830'; echo "<li>$cn = " . (is_container_number($cn) ? $ok : $mal);
    $cn = 'YMLU8744830'; echo "<li>$cn = " . (is_container_number($cn) ? $ok : $mal);
    $cn = 'TCNU1945814'; echo "<li>$cn = " . (is_container_number($cn) ? $ok : $mal);
    $cn =  'SEGU5392270'; echo "<li>$cn = " . (is_container_number($cn) ? $ok : $mal);
    $cn = 'TGBU8864437'; echo "<li>$cn = " . (is_container_number($cn) ? $ok : $mal);

    $cn = 'GVTU300038'; echo "<li>$cn = " . (is_container_number($cn) ? $ok : $mal);
    $cn = 'GVTU3000380'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);

    $cn = "SEGU5329271"; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);

    $cn = 'SEGU5329210'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);

    $cn = 'VTU3000389'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = 'V U3000389'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = '1VTU3000389'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = 'G1TU3000389'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = 'GV1U3000389'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = 'GVT13000389'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = 'GVTUa000389'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = 'GVTU3a00389'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = 'GVTU30a0389'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = 'GVTU300a389'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = 'GVTU3000a89'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = 'GVTU30003a9'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = 'GVTU3000380'; echo "<li>$cn erroneo da erroneo: " . (is_container_number($cn) ? $mal : $ok);
    $cn = 'VTU3000389'; echo "<li>$cn = " . (is_container_number($cn) ? $ok : $mal);
    $cn = 'GVTU300038'; echo "<li>$cn = " . (is_container_number($cn) ? $ok : $mal);


     */
}
