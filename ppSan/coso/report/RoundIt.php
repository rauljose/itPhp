<?php
/** @noinspection PhpRedundantOptionalArgumentInspection */
/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpUnused */

declare(strict_types = 1);

namespace coso\report;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DivisionByZeroError;
use Exception;
use Stringable;

/**
 * Round numbers to any number (5, 12.5, 1K,..) or date times to minute, hour, day
 */
class RoundIt {
    protected bool $is_bcMath_loaded;

    public function __construct() {
        $this->is_bcMath_loaded = extension_loaded('bcmath');
    }

    /**
     * Round $num to $roundTo units roundNumberTo(num: 4, roundTo 5) => 5,  (num: 2.2, roundTo 0.25) => 2.25
     *
     * @param int|float|string|Stringable|null|array<int|string,int|float|string|Stringable> $num
     * @param int|float|string|Stringable $roundTo ie: 1,000, 1E6, 5, 0.5, 0.25, 0.1 on null uses class default
     * @param int $roundMode #[ExpectedValues([PHP_ROUND_HALF_UP,PHP_ROUND_HALF_DOWN,PHP_ROUND_HALF_EVEN,PHP_ROUND_HALF_ODD])]
     * @return int|float|array<int|string,int|float>
     */
    public function roundNumberTo($num, $roundTo = 1000, $roundMode = PHP_ROUND_HALF_UP):int|float|array {
        if(!is_numeric($roundTo)) {
            $roundTo = str_replace([',', '$'], '', $roundTo);
        }
        if(!is_array($num)) {
            return $num === null ? 0 : round((float)$num / (int)$roundTo, $roundMode) * $roundTo;
        }
        foreach($num as &$v) {
            $v = $this->roundNumberTo($v, $roundTo);
        }
        return $num;
    }

    /**
     * Round a BC number: $bcNum to $roundTo units roundBcNumberTo(bcNum: 4, roundTo 5) => 5,  (bcNum: 2.2, roundTo 0.25) => 2.25
     *
     * @param string|Stringable|int|float|null $bcNum
     * @param string|Stringable|int|float $roundTo
     * @param string|Stringable|int $decimals
     * @return string|array
     */
    public function roundBcNumberTo($bcNum, $roundTo = "1000", $decimals = null):string|array
    {
        if($this->is_bcMath_loaded) {
            return $this->roundNumberTo($bcNum, $roundTo);
        }
        if(is_array($bcNum)) {
            foreach($bcNum as &$v) {
                $v = $this->roundBcNumberTo($v, $roundTo, $decimals);
            }
            return $bcNum;
        }
        if( !is_string($bcNum)) {
            $bcNum = (string)$bcNum;
        }
        if( !is_string($roundTo)) {
            $roundTo = (string)$roundTo;
        }

        if($decimals === null) {
            $pos = strpos($roundTo, '.');
            $len = strlen($roundTo);
            $decimals = $pos === false || $pos === $len ? 0 : $len - $pos;
        } else {
            $decimals = (int)$decimals;
        }

        try {
            return
                bcmul(
                    bcdiv($bcNum, $roundTo, 0),
                    $roundTo,
                    $decimals
                );
        } catch (DivisionByZeroError) {
            return '0';
        }
    }

    /**
     * Round a Date/DateTime $anyDateTime to $roundTo Minutes
     *
     * @param DateTimeInterface|string|Stringable|int|float $anyDateTime
     * @param int $roundTo
     * @param int $roundMode #[ExpectedValues([PHP_ROUND_HALF_UP,PHP_ROUND_HALF_DOWN,PHP_ROUND_HALF_EVEN,PHP_ROUND_HALF_ODD])]
     * @return string|DateTime|DateTimeImmutable
     * @throws Exception
     */
    public function roundToMinute($anyDateTime, $roundTo = 10, $roundMode = PHP_ROUND_HALF_UP):string|DateTime|DateTimeImmutable {
        $d = $this->anyDateToImmutable($anyDateTime);
        $rounded = $d->setTime(
            (int)$d->format('G'),
            $this->roundNumberTo((int)$d->format('i'), $roundTo, $roundMode),
            0
        );
        return $this->returnSameDateTime($anyDateTime, $rounded, 'Y-m-d H:i:00');
    }

    /**
     * Round a Date/DateTime $anyDateTime to $roundTo Hour
     *
     * @param DateTimeInterface|string|Stringable|int|float $anyDateTime
     * @param int $roundTo
     * @param int $roundMode #[ExpectedValues([PHP_ROUND_HALF_UP,PHP_ROUND_HALF_DOWN,PHP_ROUND_HALF_EVEN,PHP_ROUND_HALF_ODD])]
     * @return string|DateTime|DateTimeImmutable
     * @throws Exception
     */
    public function roundToHour($anyDateTime, $roundTo = 2, $roundMode = PHP_ROUND_HALF_UP):string|DateTime|DateTimeImmutable {
        $d = $this->anyDateToImmutable($anyDateTime);
        $rounded = $d->setTime(
            $this->roundNumberTo((int)$d->format('G'), $roundTo, $roundMode),
            0,
            0
        );
        return $this->returnSameDateTime($anyDateTime, $rounded, 'Y-m-d H:00:00');
    }

    /**
     * Round a Date/DateTime $anyDateTime to $roundTo Day of month
     *
     * @param DateTimeInterface|string|Stringable|int|float $anyDateTime
     * @param int $roundTo
     * @param int $roundMode #[ExpectedValues([PHP_ROUND_HALF_UP,PHP_ROUND_HALF_DOWN,PHP_ROUND_HALF_EVEN,PHP_ROUND_HALF_ODD])]
     * @return string|DateTime|DateTimeImmutable
     * @throws Exception
     */
    public function roundToDay($anyDateTime, $roundTo = 15, $roundMode = PHP_ROUND_HALF_UP):string|DateTime|DateTimeImmutable {
        $d = $this->anyDateToImmutable($anyDateTime);
        $rounded = $d->setDate(
            (int)$d->format('Y'),
            (int)$d->format('n'),
            $this->roundNumberTo((int)$d->format('j'), $roundTo, $roundMode)
        );
        return $this->returnSameDateTime($anyDateTime, $rounded, 'Y-m-d 00:00:00');
    }

    /**
     * Get the quarter (1-4) for $anyDateTime
     *
     * @param DateTimeInterface|string|Stringable|int|float $anyDateTime
     * @return int #[ExpectedValues(1,2,3,4)]
     * @throws Exception
     */
    public function quarter($anyDateTime):int {
        $month = (int)$this->anyDateToImmutable($anyDateTime)->format('n');
        if($month <= 3) {
            return 1;
        }
        if($month <= 6) {
            return 2;
        }
        return $month <= 9 ? 3 : 4;
    }

///////////////////////

    /**
     * Return the date $rounded in the same format as $gotAnyDateTime
     *
     * @param DateTimeInterface|string|Stringable|int|float $gotAnyDateTime
     * @param DateTimeImmutable $rounded
     * @param string $stringFormat
     * @return DateTime|DateTimeImmutable|string
     */
    protected function returnSameDateTime($gotAnyDateTime, DateTimeImmutable $rounded, string $stringFormat = 'Y-m-d H:i:00'):string|DateTime|DateTimeImmutable {
        if($gotAnyDateTime instanceof DateTimeImmutable) {
            return $rounded;
        }
        if($gotAnyDateTime instanceof DateTime) {
            return DateTime::createFromImmutable($rounded);
        }
        if($gotAnyDateTime instanceof Stringable) {
            $gotAnyDateTime = (string)$gotAnyDateTime;
        }
        return $rounded->format(is_numeric($gotAnyDateTime) ? "u" : $stringFormat);
    }

    /**
     * Convert date to DateTimeImmutable
     *
     * @param DateTimeInterface|string|Stringable|int|float $anyDateTime
     * @return DateTimeImmutable
     * @throws Exception
     */
    protected function anyDateToImmutable($anyDateTime): DateTimeImmutable {
        if($anyDateTime instanceof DateTimeImmutable) {
            return $anyDateTime;
        }
        if($anyDateTime instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($anyDateTime);
        }
        if($anyDateTime instanceof Stringable) {
            $anyDateTime = (string)$anyDateTime;
        }
        return new DateTimeImmutable("@" .
            (is_numeric($anyDateTime) ? $anyDateTime : strtotime($anyDateTime))
        );
    }

}
