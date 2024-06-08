<?php

/** @noinspection PhpAccessingStaticMembersOnTraitInspection */
/** @noinspection PhpUnused */

namespace ppSan\enum;

use ValueError;

/**
 * Provides from, tryFrom methods for natural, not backed enums
 * @version 1.0.0
 */
trait FromTryFrom {

    /**
     * @param string $value
     * @return static
     * @throws ValueError
     */
    public static function from(string $value):self {
        /** @noinspection PhpUndefinedMethodInspection */
        foreach(self::cases() as $case) {
            if($case->name === $value) {
                return $case;
            }
        }
        throw new ValueError("Fatal error: Uncaught ValueError: \"$value\" is not a valid case for enum " . self::class);
    }

    public static function tryFrom(string $value):self|null {
        /** @noinspection PhpUndefinedMethodInspection */
        foreach(self::cases() as $case) {
            if($case->name === $value) {
                return $case;
            }
        }
        return null;
    }
}
