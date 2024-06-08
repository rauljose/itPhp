<?php
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpUnused */

//@TODO tests

namespace jbr\utils;

/**
 * Abs Absoulte value
 *
 * @param string|int|float|bool|array|null $num
 * @param string|int|float $decimals
 * @return string|array
 */
function bcabs($num, $decimals):string|array {
    if(is_array($num)) {
        foreach($num as &$n)
            $n = bcabs($n, $decimals);
        return $num;
    }
    $num = bc2string($num);
    return bccomp("0", $num, (int)$decimals) >= 0 ?
        bcmul("-1", $num, (int)$decimals) :
        $num;
}

/**
 * Format a number
 *
 * @param string|int|float|bool|array|null $num
 * @param string|int|float $decimals
 * @param string $decimal_separator default '.'
 * @param string $thousands_separator default ','
 * @return string|array el $num con comas y redondeado al $decimals decimales
 */
function bcformat($num, $decimals = 2, $decimal_separator = '.', $thousands_separator = ','):string|array {
    if(is_array($num)) {
        foreach($num as &$n)
            $n = bcformat($n, $decimals);
        return $num;
    }
    if($num === null || $num === '')
        return '';
    if(!is_numeric($num))
        return $num;
    $num = bcadd("0", bc2string($num), (int)$decimals);
    $int = strstr($num, '.', true);
    if($int === false) {
        $int = $num;
        $frac = '';
    } else {
        $frac = strstr($num, '.');
        $frac[0] = $decimal_separator;
    }
    return preg_replace('/(\d)(?=(\d{3})+(?!\d))/mS', '$1'.$thousands_separator, $int) . $frac;
}

/**
 * @param string|int|float|bool|array|null $num
 * @return string|array
 */
function bcfloor($num):string|array {
    if(is_array($num)) {
        foreach($num as &$n)
            $n = bcfloor($n);
        return $num;
    }
    $num = bc2string($num);
    $integer = strstr($num, '.', true); //@TODO bcfloor negatives
    if($integer === false) {
        return $num;
    }
    $decs = strstr($num, '.');
    if(bccomp(substr($decs, 1), "0", 0) === 0)
        return $integer;
    return bccomp($integer, "0", 0) >=0 ? $integer : bcsub($integer, "1", 0);
}

/**
 * @param string|int|float|bool|array|null $num
 * @return string|array
 */
function bcceil($num):string|array {
    if(is_array($num)) {
        foreach($num as &$n)
            $n = bcceil($n);
        return $num;
    }
    $num = bc2string($num);
    $integer = strstr($num, '.', true); //@TODO bcceil negatives
    if($integer === false) {
        return $num;
    }
    $decs = strstr($num, '.');
    if(bccomp(substr($decs, 1), "0", 0) === 0) {
        return $integer;
    }
    return bccomp($integer, "0", 0) >= 0 ? bcadd($integer, "1", 0) : $integer;
}

/**
 * Get maximum value from array
 *
 * @param array $arr numbers to obtain maximum from
 * @param int|string $decimals number of decimals to use
 * @return string|array maximum value of $arr
 */
function bcmaxArray($arr, $decimals = 2):string|array {
    if(empty($arr))
        return '0';
    $max = bc2string(reset($arr)); //@TODO $max is an array
    foreach($arr as $n) {
        if(is_array($n)) {
            foreach($n as &$v)
                $v = bcmaxArray($v, $decimals);
            return $n;
        } elseif(bccomp( bc2string($n) , $max, $decimals) === 1)
            $max = bc2string($n);
    }
    return bcadd("0", $max, $decimals);
}

/**
 * Get minimum value from array
 *
 * @param array $arr numbers to obtain minimum from
 * @param int|string $decimals number of decimals to use
 * @return string|array minimum value of $arr
 */
function bcminArray($arr, $decimals = 2):string|array {
    if(empty($arr))
        return 0;
    $min = bc2string(reset($arr)); //@TODO $min is an array
    foreach($arr as $n)
        if(is_array($n)) {
            foreach($n as &$v)
                $v = bcminArray($v, $decimals);
            return $n;
        } elseif(bccomp( $min , bc2string($n), $decimals) === 1)
            $min = bc2string($n);
    return bcadd("0", $min, $decimals);
}

/**
 * Sum or add all elements from the array
 *
 * @param array $arr numbers to sum
 * @param int|string $decimals number of decimals to use
 * @return string|array result of adding up all elements of $arr
 */
function bcaddArray($arr, $decimals = 2):string|array {
    if(empty($arr))
        return "0";
    $decimals = (int)$decimals;
    $result = "0";
    foreach($arr as $n)
        if(is_array($n)) {
            foreach($n as &$v)
                $v = bcaddArray($v, $decimals);
            return $n;
        } else
            $result = bcadd($result, bc2string($n), $decimals);
    return $result;
}

/**
 * Multiply all elements from the array
 *
 * @param array $arr numbers to multiply
 * @param int|string $decimals number of decimals to use
 * @return string|array result of multiplying all elements of $arr
 */
function bcmulArray($arr, $decimals = 2):string|array {
    if(empty($arr))
        return "0";
    $decimals = (int)$decimals;
    $result = "1";
    foreach($arr as $n)
        if(is_array($n)) {
            foreach($n as &$v)
                $v = bcmulArray($v, $decimals);
            return $n;
        } else
            $result = bcmul($result, bc2string($n), $decimals);
    return $result;
}

/**
 * Substract all elements from the array
 *
 * @param array $arr numbers to substract
 * @param int|string $decimals number of decimals to use
 * @return string|array result of substracting up all elements of $arr
 */
function bcsubArray($arr, $decimals = 2):string|array {
    if(empty($arr))
        return "0";
    $decimals = (int)$decimals;
    $result = "0";
    foreach($arr as $n)
        if(is_array($n)) {
            foreach($n as &$v)
                $v = bcsubArray($v, $decimals);
            return $n;
        } else
            $result = bcsub($result, bc2string($n), $decimals);
    return $result;
}

/**
 * Removes exponential notation, empty and null to zero
 *
 * @param string|int|float|bool|array|null $number
 * @return string|array
 */
function bc2string($number):string|array {
    if(is_array($number)) {
        foreach($number as &$n)
            $n = bc2string($n);
        return $number;
    }
    if(empty($number))
        return "0";
    if(!is_numeric($number) || stripos("$number", 'E') === false)
        return "$number";
    return rtrim(rtrim(bcmul(sprintf("%.20f", $number ), '1', 20), '0'), '.');
}

function bcround($number, $decimals = 2):string {
    if($number === null)
        return "0";
    if(bccomp('0', $number) <= 0)
        return bcadd($number, '0.'. str_repeat('0', $decimals).'5', $decimals);
    return bcsub($number, '0.'. str_repeat('0', $decimals).'5', $decimals);
}

// FALTA
class IncorporateBC {

    private static function deduceRoundToScale($roundTo):string {
        $pos = strrpos($roundTo, '.');
        if($pos === false) {
            return "0";
        }
        return strlen($roundTo) - $pos - 1;
    }

    public static function bcRoundDownTo($n, $roundTo):string {
        $scale = self::deduceRoundToScale($roundTo);

        if(bccomp($roundTo, "1", $scale) >= 0) {
            return bcmul(bcfloor(bcdiv($n, $roundTo, $scale + 1)), $roundTo, $scale);
        }
        $parts = bcdiv("1", $roundTo, $scale);
        return bcdiv(bcfloor(bcmul($n, $parts, $scale + 1)), $parts, $scale);
    }

    public static function bcRoundUpTo($n, $roundTo):string {
        $scale = self::deduceRoundToScale($roundTo);
        if(bccomp($roundTo, "1", $scale) >= 0) {
            return bcmul(bcceil(bcdiv($n, $roundTo, $scale + 1)), $roundTo, $scale);
        }
        $parts = bcdiv("1", $roundTo, $scale + 1);
        return bcdiv(ciel(bcmul($n, $parts, $scale + 1)), $parts, $scale);
    }

    public static function bcRoundTo($n, $roundTo, $mode = PHP_ROUND_HALF_UP):string {
        $scale = self::deduceRoundToScale($roundTo);
        if(bccomp($roundTo, "1", $scale) >= 0) {
            return bcmul(bcround(bcdiv($n, $roundTo, $scale + 1), 0, $mode), $roundTo, $scale);
        }
        $parts = bcdiv("1", $roundTo, $scale + 1);
        return bcdiv(bcround(bcmul($n, $parts, $scale + 1), 0, $mode), $parts, $scale);
    }
}