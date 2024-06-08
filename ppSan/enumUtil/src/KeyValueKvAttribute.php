<?php
/** @noinspection PhpUnused */
/** @noinspection PhpAccessingStaticMembersOnTraitInspection */

namespace ppSan\enum;

use ReflectionClassConstant;

/**
 * KV Attribute a lazy in-code readonly key value store
 *
 * #[KV('keyName',value)] #[KV('keyName2','value2')]
 * case ENUM_NAME;
 *
 *
 */
trait KeyValueKvAttribute {

    public static function getKeyValue(string $enumName, string $key, mixed $default = '') {
        return self::readKv()[$enumName][$key] ?? $default;
    }

    public static function getKeyValuesFor(string $enumName, mixed $default = '') {
        return self::readKv()[$enumName] ?? $default;
    }

    /**
     * @return mixed [enumCase=>['key'=>value, 'key2' => [value1, value2]] | $default
     */
    public static function getKeyValuesAll():mixed {
        static $kv;
        if(empty($kv))
            $kv = self::readKv();
        return $kv;
    }

    protected static function readKv(string|null $name = null, string|null $key = null, $default = ''): mixed {
        $return = [];
        /** @noinspection PhpUndefinedMethodInspection */
        foreach (self::cases() as $c) {
            if ($name !== null && strcasecmp($c->name, $name))
                continue;
            $reflection = new ReflectionClassConstant($c, $c->name);
            foreach ($reflection->getAttributes('kv') as $r) {
                $k = $r->getArguments()[0] ?? null;
                if(is_string($k) || is_int($k) && ($key === null || $k === $key)) {
                    if (count($r->getArguments()) <= 1)
                        $valor = $default;
                    else {
                        $valor = array_slice($r->getArguments(), 1);
                        if(count($valor) <= 1)
                            $valor = $valor[0] ?? $default;
                    }
                    if (isset($return[$c->name][$k])) {
                        if (is_array($return[$c->name][$k]) && count($return[$c->name][$k]) > 1)
                            $return[$c->name][$k][] = $valor;
                        else
                            $return[$c->name][$k] = [$return[$c->name][$k], $valor];
                    } else {
                        $return[$c->name][$k] = $valor;
                    }
                }
            }
            if ($name !== null)
                break;
        }
        return $name === null ? $return : $return[$name];
    }

}
