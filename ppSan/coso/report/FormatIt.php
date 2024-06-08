<?php

/** @noinspection PhpMissingParamTypeInspection */
/** @noinspection PhpUnused */

namespace coso\report;

use DateTimeInterface;
use Stringable;

// formatIt mixed->string
// roundIt sera formatIt rounded?
class FormatIt {
    protected string $locale;
    protected int $decimals;
    protected string $decimalSeparator;
    protected string $thousandsSeparator;
    protected bool $zeroBlank;
    protected string $dateDefaultFormat;
    protected string $timeDefaultFormat;
    protected string $stringDefaultFormatter;
    protected string $trueString;
    protected string $falseString;
    protected bool $is_bcMath_available;
    protected bool $is_intl_available;

/// numbers

    /**
     * @param string|Stringable|int|float|null $num
     * @param string|int|null $decimals on null uses instance default defined at constructor
     * @param string|null $decimalSeprator on null uses instance default defined at constructor
     * @param string|null $thousandsSeparator on null uses instance default defined at constructor
     * @return string
     */
    public function num($num, $decimals = null, $decimalSeprator = null, $thousandsSeparator = null):string {
        if($num === null || $num === '') {
            return '';
        }
        if($this->is_bcMath_available && (is_string($num) || $num instanceof Stringable)) {
            return $this->bcformat($num, $decimals, $decimalSeprator, $thousandsSeparator);
        }
        if(!is_numeric($num)) {
            return $num;
        }
        return number_format(
            (float)$num,
            $decimals ?? $this->decimals,
            $decimalSeprator ?? $this->decimalSeparator,
            $thousandsSeparator ?? $this->thousandsSeparator
        );
    }

    /**
     * Redondea y pone comas a un numero en string, number_format para strings
     *
     * @param string|Stringable|int|float|null $num
     * @param int $decimals
     * @return string el $num con comas y redondeado al $decimals decimales
     */
    public function bcformat($num, $decimals = null, $decimalSeprator = null, $thousandsSeparator = null):string {
        if($num === null || $num === '') {
            return '';
        }
        if(!is_numeric($num)) {
            return $num;
        }
        if(!$this->is_bcMath_available) {
            return $this->num($num, $decimals, $decimalSeprator, $thousandsSeparator);
        }

        $num = bcadd("0", (string)$num, $decimals ?? $this->decimals);
        $int = strstr($num, '.', true);
        if($int === false) {
            $int = $num;
            $frac = '';
        } else {
            $frac = substr(strstr($num, '.'), 1);
        }
        return preg_replace(
            '/(\d)(?=(\d{3})+(?!\d))/mS',
            '$1' . ($thousandsSeparator ?? $this->thousandsSeparator),
            $int) .
            ($decimalSeprator ?? $this->decimalSeparator) . $frac;
    }

    // @todo currency, exchange_rate





////    Dates

    /**
     * Regresa la diferencia en frase bonita de las 2 fechas
     *
     * @param string|Stringable|int|float $dateTime1 mysqlDate o mysqlDateTime o timestamp
     * @param string|Stringable|int|float $dateTime2 mysqlDate o mysqlDateTime o timestamp
     * @param int $maxParts
     * @return string
     */
    function fechaDiff($dateTime1, $dateTime2, $maxParts = 2) {
        $to = [
            'año' => 60*60*24*365,
            'mes' => 60*60*24*30,
            'día' => 60*60*24,
            'hr' => 60*60,
            'min' => 60,
            'seg' => 1,
        ];
        $plural = [
            'año' => 'años',
            'mes' => 'meses',
            'día' => 'dias',
            'hr' => 'hrs',
            'min' => 'min',
            'seg' => 'seg',
        ];
        $last = 'seg';
        $format = [];
        if($dateTime1 instanceof Stringable) {
            $dateTime1 = (string)$dateTime1;
        }
        if(!is_numeric($dateTime1)) {
            $dateTime1 = strtotime($dateTime1);
        }
        if($dateTime2 instanceof Stringable) {
            $dateTime2 = (string)$dateTime2;
        }
        if(!is_numeric($dateTime2)) {
            $dateTime2 = strtotime($dateTime2);
        }
        $diff = abs( (float)$dateTime1 - (float)$dateTime2);
        $breakNow = 0;
        foreach($to as $label => $u) {
            if($breakNow >= $maxParts || ($breakNow > 0 && $label === $last)) {
                break;
            }
            $period = floor($diff/$u);
            if( $period >= 1 ) {
                $diff -= $period * $u;
                $format[] =  $period . ' ' . ($period === 1.00 ? $label : $plural[$label]);
                $breakNow++;
            } elseif($breakNow === 1) {
                $breakNow++;
            }
        }
        return implode(", ", $format);
    }

    /**
     * Format $anyDate with $format with Date($format,...)/$anyDate->format($format)
     *
     * @param string|Stringable|int|DateTimeInterface $anyDate
     * @param string|null $format
     * @return string
     */
    public function date($anyDate, $format):string {
        if($anyDate === null) {
            return '';
        }
        if($format === null) {
            $format = $this->dateDefaultFormat;
        }
        if($anyDate instanceof DateTimeInterface) {
            return $anyDate->format($format);
        }
        return Date($format, is_numeric($anyDate) ? $anyDate : strtotime((string)$anyDate));
    }

    public function toYmd($anyDate):string {return $this->date($anyDate, 'Y-m-d');}

    public function toYear($anyDate):string {return $this->date($anyDate, 'Y');}

    public function toYearQuarter($anyDate):string {return $this->date($anyDate, 'Y-??');}

    public function toYearMonth($anyDate):string {return $this->date($anyDate, 'Y-m');}

    public function toQuarter($anyDate):string {return $this->date($anyDate, '??');}

    public function toMonth($anyDate):string {return $this->date($anyDate, 'm');}

    public function toYearMonthShort($anyDate):string {return $this->date($anyDate, 'Y-M');}

    public function toMonthShort($anyDate):string {return $this->date($anyDate, 'M');}

////@todo Times & round times, time since

////@todo Strings ucFirst, ucWords, toLower, toUpper, snake, camel, left, right?

//// Helpers 
    protected function is_ymd(string $ymd):bool {
        if(strlen($ymd) !== 10) {
            return false;
        }
        $dateParts = explode('-', $ymd);
        if(count($dateParts) !== 3) {
            return false;
        }
        return checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0]);
    }

    /**
     * Converts a date format pattern from [php date() function format][] to [ICU format][].
     *
     * Pattern constructs that are not supported by the ICU format will be removed.
     *
     * [php date() function format]: https://www.php.net/manual/en/function.date.php
     * [ICU format]: https://unicode-org.github.io/icu/userguide/format_parse/datetime/#datetime-format-syntax
     *
     * Since 2.0.13 it handles escaped characters correctly.
     * https://github.com/yiisoft/yii2/framework/helpers/BaseFormatConverter.php
     * @param string $pattern date format pattern in php date()-function format.
     * @return string The converted date format pattern.
     */
    public static function convertDatePhpToIcu($pattern):string
    {
        // https://www.php.net/manual/en/function.date.php
        $result = strtr($pattern, [
            "'" => "''''",  // single `'` should be encoded as `''`, which internally should be encoded as `''''`
            // Day
            '\d' => "'d'",
            'd' => 'dd',    // Day of the month, 2 digits with leading zeros 	01 to 31
            '\D' => "'D'",
            'D' => 'eee',   // A textual representation of a day, three letters 	Mon through Sun
            '\j' => "'j'",
            'j' => 'd',     // Day of the month without leading zeros 	1 to 31
            '\l' => "'l'",
            'l' => 'eeee',  // A full textual representation of the day of the week 	Sunday through Saturday
            '\N' => "'N'",
            'N' => 'e',     // ISO-8601 numeric representation of the day of the week, 1 (for Monday) through 7 (for Sunday)
            '\S' => "'S'",
            'S' => '',      // English ordinal suffix for the day of the month, 2 characters 	st, nd, rd or th. Works well with j
            '\w' => "'w'",
            'w' => '',      // Numeric representation of the day of the week 	0 (for Sunday) through 6 (for Saturday)
            '\z' => "'z'",
            'z' => 'D',     // The day of the year (starting from 0) 	0 through 365
            // Week
            '\W' => "'W'",
            'W' => 'w',     // ISO-8601 week number of year, weeks starting on Monday (added in PHP 4.1.0) 	Example: 42 (the 42nd week in the year)
            // Month
            '\F' => "'F'",
            'F' => 'MMMM',  // A full textual representation of a month, January through December
            '\m' => "'m'",
            'm' => 'MM',    // Numeric representation of a month, with leading zeros 	01 through 12
            '\M' => "'M'",
            'M' => 'MMM',   // A short textual representation of a month, three letters 	Jan through Dec
            '\n' => "'n'",
            'n' => 'M',     // Numeric representation of a month, without leading zeros 	1 through 12, not supported by ICU but we fallback to "with leading zero"
            '\t' => "'t'",
            't' => '',      // Number of days in the given month 	28 through 31
            // Year
            '\L' => "'L'",
            'L' => '',      // Whether it's a leap year, 1 if it is a leap year, 0 otherwise.
            '\o' => "'o'",
            'o' => 'Y',     // ISO-8601 year number. This has the same value as Y, except that if the ISO week number (W) belongs to the previous or next year, that year is used instead.
            '\Y' => "'Y'",
            'Y' => 'yyyy',  // A full numeric representation of a year, 4 digits 	Examples: 1999 or 2003
            '\y' => "'y'",
            'y' => 'yy',    // A two digit representation of a year 	Examples: 99 or 03
            // Time
            '\a' => "'a'",
            'a' => 'a',     // Lowercase Ante meridiem and Post meridiem, am or pm
            '\A' => "'A'",
            'A' => 'a',     // Uppercase Ante meridiem and Post meridiem, AM or PM, not supported by ICU but we fallback to lowercase
            '\B' => "'B'",
            'B' => '',      // Swatch Internet time 	000 through 999
            '\g' => "'g'",
            'g' => 'h',     // 12-hour format of an hour without leading zeros 	1 through 12
            '\G' => "'G'",
            'G' => 'H',     // 24-hour format of an hour without leading zeros 0 to 23h
            '\h' => "'h'",
            'h' => 'hh',    // 12-hour format of an hour with leading zeros, 01 to 12 h
            '\H' => "'H'",
            'H' => 'HH',    // 24-hour format of an hour with leading zeros, 00 to 23 h
            '\i' => "'i'",
            'i' => 'mm',    // Minutes with leading zeros 	00 to 59
            '\s' => "'s'",
            's' => 'ss',    // Seconds, with leading zeros 	00 through 59
            '\u' => "'u'",
            'u' => '',      // Microseconds. Example: 654321
            // Timezone
            '\e' => "'e'",
            'e' => 'VV',    // Timezone identifier. Examples: UTC, GMT, Atlantic/Azores
            '\I' => "'I'",
            'I' => '',      // Whether or not the date is in daylight saving time, 1 if Daylight Saving Time, 0 otherwise.
            '\O' => "'O'",
            'O' => 'xx',    // Difference to Greenwich time (GMT) in hours, Example: +0200
            '\P' => "'P'",
            'P' => 'xxx',   // Difference to Greenwich time (GMT) with colon between hours and minutes, Example: +02:00
            '\T' => "'T'",
            'T' => 'zzz',   // Timezone abbreviation, Examples: EST, MDT ...
            '\Z' => "'Z'",
            'Z' => '',      // Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive. -43200 through 50400
            // Full Date/Time
            '\c' => "'c'",
            'c' => "yyyy-MM-dd'T'HH:mm:ssxxx", // ISO 8601 date, e.g. 2004-02-12T15:19:21+00:00
            '\r' => "'r'",
            'r' => 'eee, dd MMM yyyy HH:mm:ss xx', // RFC 2822 formatted date, Example: Thu, 21 Dec 2000 16:01:07 +0200
            '\U' => "'U'",
            'U' => '',      // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
            '\\\\' => '\\',
        ]);

        // remove `''` - they're result of consecutive escaped chars (`\A\B` will be `'A''B'`, but should be `'AB'`)
        // real `'` are encoded as `''''`
        return strtr($result, [
            "''''" => "''",
            "''" => '',
        ]);
    }



}
