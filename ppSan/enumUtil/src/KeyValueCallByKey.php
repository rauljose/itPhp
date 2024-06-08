<?php
/** @noinspection PhpAccessingStaticMembersOnTraitInspection */
/** @noinspection PhpUnused */

namespace ppSan\enum;

trait KeyValueCallByKey {
    use KeyValueKvAttribute;

    public function __call(string $key, array $arguments):mixed {
        static $kv;
        if(empty($kv))
            /** @noinspection PhpUndefinedFieldInspection */
            static::getKeyValuesFor($this->name);
        if(isset($kv[$key]))
            return $kv[$key];
        return null;
    }

}
