<?php

/** @noinspection PhpAccessingStaticMembersOnTraitInspection */
/** @noinspection PhpUnused */

/**
 * Extra functions useful for both backed and not backed enums
 * @version 1.0.0
 */

namespace ppSan\enum;

use BackedEnum;
use JetBrains\PhpStorm\ExpectedValues;
use ReflectionEnum;
use ValueError;

//@TODO ¿radios/checkboxes?

trait ExtraFunctions {

    /**
     *
     * @param int $flags JSON_*
     * @param int $depth
     * @return false|string
     */
    public static function json_encode(int $flags = 0, int $depth = 512) {
        return json_encode(self::nameValue(), $flags, $depth);
    }

    /**
     * enum NAMED implements JsonSerializable { case PUFF; case SUFFI: }
     * json_encode(NAMED::PUFF) = "{\"PUFF\":\"PUFF\"}"
     * enum NAMED:string implements JsonSerializable { case puff = 'Puff The Kitten'; case suffi = 'Suffi the Cat'; }
     * json_encode(NAMED::CASE_1) = "{\"puff\":\"Puff The Kitten\"}"
     *
     * @return false|string json encoded case
     */
    public function jsonSerialize() {
        return json_encode([$this->name => self::isBacked() ? $this->value : $this->name]);
    }

    /**
     * @return array [ string name => string|int value]
     * */
    public static function nameValue(Sorter $sortBy = Sorter::VALUE):array {
        /** @noinspection PhpUndefinedMethodInspection */
        $keyValue = array_column(self::cases(), self::isBacked() ? 'value' : 'name', 'name');
        $sortBy->sort($keyValue);
        return $keyValue;
    }

    /**
     * @return array [ string|int value => string name ]
     * */
    public static function valueName(Sorter $sortBy = Sorter::VALUE):array {
        return array_flip(self::nameValue($sortBy));
    }

    /**
     * @return array [ string caseName => enumObject]
     * */
    public static function nameEnum(Sorter $sortBy = Sorter::VALUE): array {
        $cases = [];
        /** @noinspection PhpUndefinedMethodInspection */
        foreach(self::cases() as $case)
            $cases[$case->name] = $case;
        $sortBy->sort($cases);
        return $cases;
    }

    /**
     * @return array [ string|int value => enumObject]
     * */
    public static function valueEnum(Sorter $sortBy = Sorter::VALUE): array {
        $isBacked = self::isBacked();
        $cases = [];
        /** @noinspection PhpUndefinedMethodInspection */
        foreach(self::cases() as $case)
            $cases[$isBacked ? $case->value : $case->name] = $case;
        $sortBy->sort($cases);
        return $cases;
    }

    /**
     * @param string $name
     * @param bool $caseInsensitive
     * @return static
     * @throws ValueError
     */
    public static function fromName(string $name, bool $caseInsensitive = false):self {
        /** @noinspection PhpUndefinedMethodInspection */
        foreach(self::cases() as $case)
            if($case->name === $name)
                return $case;
            elseif($caseInsensitive && strcasecmp($case->name, $name) === 0)
                return $case;
        throw new ValueError(
            "Fatal error: Uncaught ValueError: \"$name\" is not a valid case for enum " . self::class);
    }

    public static function tryFromName(string $name, bool $caseInsensitive = false):self|null {
        /** @noinspection PhpUndefinedMethodInspection */
        foreach(self::cases() as $case)
            if($case->name === $name)
                return $case;
            elseif($caseInsensitive && strcasecmp($case->name, $name) === 0)
                return $case;
        return null;
    }

    /**
     * backedEnum.from that also works on not backed enums, optionally case insensitive
     * translates a string or int into the corresponding Enum case, if any. If there is no matching case defined,
     * it will throw a ValueError.
     *
     * @param string|int $value
     * @param bool $caseInsensitive
     * @return static
     * @throws ValueError
     */
    public static function fromValue(string|int $value, bool $caseInsensitive = false):self {
        /** @noinspection PhpUndefinedMethodInspection */
        foreach(self::cases() as $case) {
            $vs = self::isBacked() ? $case->value : $case->name;
            if ($vs == $value)
                return $case;
            elseif ($caseInsensitive && strcasecmp($vs, $value) === 0)
                return $case;
        }
        throw new ValueError(
            "Fatal error: Uncaught ValueError: \"$value\" is not a valid value in the enum " . self::class);
    }

    /**
     * backedEnum.tryFrom that also works on not backed enums, optionally case insensitive
     * Translates a string or int into the corresponding Enum case, if any. If there is no matching case defined,
     * it will return null.
     *
     * @param string|int $value
     * @param bool $caseInsensitive
     * @return static|null
     */
    public static function tryFromValue(string|int $value, bool $caseInsensitive = false):self|null {
        /** @noinspection PhpUndefinedMethodInspection */
        foreach(self::cases() as $case) {
            $vs = self::isBacked() ? $case->value : $case->name;
            if ($vs == $value) {
                return $case;
            }
            elseif ($caseInsensitive && strcasecmp($vs, $value) === 0) {
                return $case;
            }
        }
        return null;
    }

    public static function getName(self $case):string {
        /** @noinspection PhpUndefinedFieldInspection */
        return $case->name;
    }

    public static function getValue(self $case):string|int {
        /** @noinspection PhpUndefinedFieldInspection */
        return $case instanceof BackedEnum ? $case->value : $case->name;
    }

    /**
     * @return string "value1, value2 "
     */
    public static function getNamesListed(string $separator = ', ', Sorter $sortBy = Sorter::VALUE): string {
        return implode($separator, array_keys(self::nameValue($sortBy)));
    }

    /**
     * @return string "value1, value2 "
     */
    public static function valuesListed(string $separator = ', ', Sorter $sortBy = Sorter::VALUE): string {
        return implode($separator, self::nameValue($sortBy));
    }

    public static function getOptions(string|array|null $selected = '', Sorter $sortBy = Sorter::VALUE):string {
        $options = [];
        if(is_array($selected)) {
            $selectValues = array_combine($selected, $selected);
        }
        else {
            $selectValues = [$selected => true];
        }
        foreach(self::nameValue($sortBy) as $name => $value) {
            $selected = array_key_exists($name, $selectValues) ? ' SELECTED' : '';
            $options[] = "<option$selected value='" . htmlentities($name) . "'>" . htmlentities($value) .
                '</option>';
        }
        return implode('', $options);
    }

    public static function isBacked():bool {
        return (new ReflectionEnum(self::class))->isBacked();
    }

    public static function getBackingType():string {
        return (string)(new ReflectionEnum(self::class))->getBackingType();
    }

}
