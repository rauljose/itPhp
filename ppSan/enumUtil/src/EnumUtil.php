<?php
/** @noinspection PhpUnused */
// helpers
// https://github.com/archtechx/enums
// https://github.com/datomatic/enum-helper
// https://github.com/framjet/php-enum-bitmask

// sets
// https://github.com/alexanderpas/php-http-enum/blob/master/src/ReasonPhrase.php
// https://github.com/PrinsFrank/standards enums for ISO
    // Countries ISO3166_1_Alpha_2,3...
    // ISO 4217. countries
    // ISO639_1_Alpha_2 Languages
    // HttpStatusCode.php de ReasonPhrase.php status summary 1xx=, 2xx=,...
// data

namespace ppSan\enum;

trait EnumUtil {
    use ExtraFunctions;
    use InvokeForValue; // Allows: $kind->enumCase()|$kind::enumCase() to get value or name in non-backed enums  Overrides __invoke, __callStatic
    use KeyValueKvAttribute; // Retrieves 1 or many: #[Kv(Key, Value)] case enumCase;
    use KeyValueCallByKey; // Allows: $kind->enumCase()->Key Override __call
}
