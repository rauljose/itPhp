<?php
/** @noinspection PhpUnused */

namespace ppSan\enum;

use BackedEnum;
use Error;

/**
 * Allows calling an instance to get its value. enum::case() or $status = enum::Name; echo $status();
 * in not backed enums it returns it's name.
 * Overrides __invoke and __callStatic
 *
 */
trait InvokeForValue {

    /**
     * Allows calling an instance to get its value. $status = enum::Name; echo $status();
     * in not backed enums it returns it's name
     *
     * @return int|string
     */
    public function __invoke():string|int {
        /** @noinspection PhpUndefinedFieldInspection */
        return $this instanceof BackedEnum ? $this->value : $this->name;
    }

    /**
     * Allows enum::case() to get its value.
     * in not backed enums it returns it's name
     *
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws Error Fatal error: Uncaught Error: Call to undefined method
     */
    public static function __callStatic(string $name, array $arguments): mixed {
        /** @noinspection PhpAccessingStaticMembersOnTraitInspection */
        /** @noinspection PhpUndefinedMethodInspection */
        $cases = static::cases();
        foreach ($cases as $case) {
            if($case->name === $name || strcasecmp($case->name, $name) === 0) {
                return $case instanceof BackedEnum ? $case->value : $case->name;
            }
        }
        throw new Error("Fatal error: Uncaught Error: Call to undefined method " . self::class."::$name.()");
    }
}
