<?php

namespace coso\Other;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use Stringable;

/**
 *
 * Dado $initalDate, $period:
 *  a) datesInRange($d1, $d2) => [d1,d2,d3,d4]
 *  b) datesFrom($d1, $num_dates>0) [d1,d2,d3,d4]
 *
 * Helpers:
 *      ¿period maker?
 *
 */
class ScheduleIt {
    protected DateTimeImmutable $initialDate;
    protected string $duration;
    protected DateInterval $dateInterval;

    /**
     * @param DateTimeInterface|string|Stringable|int|float $initialDate
     * @param string|Stringable $duration
     * @throws Exception when an invalid $duration cannot be parsed as an interval.
     */
    public function __construct($initialDate, $duration) {
        $this->initialDate = $this->anyDateToImmutable($initialDate);
        $this->duration = (string)$duration;
        $this->dateInterval = new DateInterval($duration);
    }

    public function datesInRange($startDate, $endDate):array {
        return [];
    }

    /**
     * Get DateInterval, usually for use with ->format
     *
     * @return DateInterval
     */
    public function getDateInterval(): DateInterval{return $this->dateInterval;}



///////////////

    /**
     * @param DateTimeImmutable $firstDate
     * @return DateTimeImmutable
     * @throws Exception
     */
    protected function getFirstDate(DateTimeImmutable $firstDate):DateTimeImmutable {
        $datePeriod = new DatePeriod($this->initialDate, $this->dateInterval, $firstDate);
        $from = $datePeriod->getEndDate();
        return $from === null ? $firstDate : $this->anyDateToImmutable($from);
    }

    /**
     * Converts any date format to a DateTimeImmutable
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
