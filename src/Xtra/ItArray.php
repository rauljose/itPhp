<?php
/** @noinspection PhpUnused */

namespace It\Xtra;

use Stringable;

class ItArray {

    /**
     * Returns array elements that are only in one of the arrays
     *
     * @param array $base
     * @param array $enEmpateGana
     * @return array
     */
    public static function diff(array $base, array $enEmpateGana): array {
        $intersection = array_intersect($base, $enEmpateGana);
        return [
          ...array_diff($base, $intersection),
          ...array_diff($enEmpateGana, $intersection)
        ];
    }

    /**
     *
     *
     *
     * @param array $base
     * @param array $enEmpateGana
     * @return array
     *
     * @Example
     *  diffAssoc(['ambos='=>'igual', 'ambos!'=>'A', 'A'=>'A'], ['ambos='=>'igual', 'ambos!'=>'B', 'B'=>'B'])
     *    [ 'ambos!' => 'B', 'A' => 'A', 'B' => 'B']
     */
    public static function diffAssoc(array $base, array $enEmpateGana): array {
        $intersection = array_intersect_assoc($base, $enEmpateGana);
        return [
          ...array_diff_assoc($base, $intersection),
          ...array_diff_assoc($enEmpateGana, $intersection)
        ];
    }

    /**
     * @param array $base
     * @param array $enEmpateGana
     * @return array
     */
    public static function diffCaseInsensitive(array $base, array $enEmpateGana): array {
        $intersection = array_uintersect($base, $enEmpateGana, [self::class, 'compareCaseInsensitive']);
        return [
          ...array_udiff($base, $intersection, [self::class, 'compareCaseInsensitive']),
          ...array_udiff($enEmpateGana, $intersection, [self::class, 'compareCaseInsensitive'])
        ];
    }

    /**
     *
     *
     *
     * @param array $base
     * @param array $enEmpateGana
     * @return array
     *
     * @Example
     *  diffAssoc(['ambos='=>'igual', 'ambos!'=>'A', 'A'=>'A'], ['ambos='=>'igual', 'ambos!'=>'B', 'B'=>'B'])
     *    [ 'ambos!' => 'B', 'A' => 'A', 'B' => 'B']
     */
    public static function diffAssocCaseInsensitive(array $base, array $enEmpateGana):array {
        $intersection = array_uintersect_assoc($base, $enEmpateGana, [self::class, 'compareCaseInsensitive']);
        return[
            ...array_udiff_assoc($base, $intersection, [self::class, 'compareCaseInsensitive']),
            ...array_udiff_assoc($enEmpateGana, $intersection, [self::class, 'compareCaseInsensitive'])
        ];
    }



    public static function getKeyCase($key, array $array) {
        if (array_key_exists($key, $array))
            return $key;

        $testKeyUpper = strtoupper($key);
        if (array_key_exists($testKeyUpper, $array))
            return $testKeyUpper;

        $testKeyLower = strtolower($key);
        if (array_key_exists($testKeyLower, $array))
            return $testKeyLower;

        foreach ($array as $k => $value)
            if (strcasecmp($testKeyLower, strtolower($k)) === 0)
                return $k;

        return null;
    }

    public static function getValueKeyInsensitive($key, array $array) {
        $keyCase = self::getKeyCase($key, $array);
        return $keyCase === null ? null : $array[$keyCase];
    }

    protected static function compareCaseInsensitive($a,$b):int {
        if($a instanceof Stringable || $a === null) $a = (string)$a;
        if($b instanceof Stringable || $b === null) $b = (string)$b;
        if(is_string($a) || is_string($b)) {
            if(is_numeric($a) && is_numeric($b)) {
                return bccomp($a, $b);
            }
            return strcasecmp((string)$a, (string)$b);
        }
        return $a <=> $b;
    }

}