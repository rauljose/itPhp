<?php
/** @noinspection PhpUnused */

namespace ppSan\enum;

trait EnumUtilNotBacked {
    use ExtraFunctions;
    use FromTryFrom; // adds from and tryFrom methods that retrieve enumCase's name
    use InvokeForValue; // Allows: $kind->enumCase()|$kind::enumCase() to get value or name in non-backed enums  Overrides __invoke, __callStatic
    use KeyValueKvAttribute; // Retrieves 1 or many: #[Kv(Key, Value)] case enumCase;
    use KeyValueCallByKey; // Allows: $kind->enumCase()->Key Override __call
}
