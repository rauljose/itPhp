<?php

trait enumer {
    public static function toArray():array {
        $array = [];
        foreach(self::cases() as $v)
            $array[$v->name] = $v->value;
        return $array;
    }

    public static function normalizeValue(string|\Stringable|int|array $value):string|int|array {
        static $array;
        if(is_array($value)) {
            foreach($value as &$v)
                $v = self::normalizeValue($v);
            return $value;
        }
        if($value instanceof \Stringable)
            $value = "$value";
        if(self::tryFrom($value) !== null)
            return $value;
        if(!isset($array))
            $array = self::toArray();
        if(array_key_exists($value, $array))
            return $array[$value];
        if(in_array($value, $array, true))
            return $value;
        foreach($array as $k => $v) {
            if($k == $value)
                return $v;
            if($v == $value)
                return $v;
            if(strcasecmp($v, $value) === 0)
                return $v;
            if(strcasecmp($k, $value) === 0)
                return $v;
        }
        return $value;
    }

    public static function invalidValues(array $validate):array {
        $invalid = [];
        $valid = self::toArray();
        foreach($validate as $v)
            if(!array_key_exists($v, $valid) && !in_array($v, $valid))
                $invalid[] = $v;
        return $invalid;
    }
}
enum Suit: string {
    use enumer;

    case Hearts = 'H';
    case Diamonds = 'D';
    case Clubs = 'C';
    case Spades = 'S';

}
