<?php
/**
 * SQL Patterns: Slotted Counter
 *
 * @see https://planetscale.com/blog/the-slotted-counter-pattern thanks!
 */

/**
 * Counters, slotted counters
 *
 * https://planetscale.com/blog/the-slotted-counter-pattern
 * record_id: bigint, varchar, guuid, binary guuid
CREATE TABLE `slotted_counters` (
`id` int NOT NULL AUTO_INCREMENT,
`record_type` int NOT NULL COMMENT 'table for records',
`record_id` bigint NOT NULL COMMENT 'id of record to count',
`slot` int NOT NULL DEFAULT '0',
`count` int DEFAULT NOT NULL DEFAULT '0',
PRIMARY KEY (`id`),
UNIQUE KEY `records_and_slots` (`record_type`,`record_id`,`slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
 *
 *
-- increment count record_type-record_id
INSERT INTO slotted_counters(record_type, record_id, slot, count)
VALUES (123, 456, RAND() * 100, 1)
ON DUPLICATE KEY UPDATE count = count + 1;
 *
-- Read counter
SELECT SUM(count) as count
FROM slotted_counters
WHERE record_type = 123 AND record_id = 456;
 */

namespace coso\sql;

class Counter {
   static function incrementStatic($a) {$a++; return $a;}
   function inc($a) {$a++; return $a;}
}
