<?php
declare(strict_types=1);

namespace ppSan\enum;

/**
 * Sort arrays by keys or values.
 * @version 1.0.0
 */
enum Sorter:String {
    case UNSORTED = 'UNSORTED';
    case KEY = 'KEY';
    case VALUE = 'VALUE';

    /**
     * @param array $array
     * @param int $sortFlags
     * @return bool
     */
    public function sort(array &$array, int $sortFlags = SORT_NATURAL | SORT_FLAG_CASE):bool {
        return match ($this) {
            self::UNSORTED => true,
            self::KEY => ksort($array, $sortFlags),
            default => asort($array, $sortFlags),
        };
    }
}
