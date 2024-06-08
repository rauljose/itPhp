<?php 
/** @noinspection PhpUnused */
/** @noinspection PhpMissingParamTypeInspection */

declare(strict_types = 1);
namespace coso\Other;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Stringable;


/**
 * DatePeriod wrapper: $fromDate for a period defined from $initialDate,
 * auto invert duration/interval if needed, when $toDate is in the past, or recurrences < 0
 */
class DatePeriodIt {

    /**
     * @var array $skipWeekDays
     * #[ArrayShape(['Sun'=>'bool','Sat'=>'bool','Mon'=>'bool','Tue'=>'bool', 'Wed'=>'bool', 'Thu'=>'bool', 'Fri'=>'bool'])]
     */
    protected array $skipWeekDays;
    /**
     * @var array $skipHolidays ['anyDate',..]
     */
    protected array $skipHolidays;

    /**
     * @param array $skipHolidays
     */
    public function __construct(
       array $skipWeekDays = ['Sun'=>true,'Sat'=>true,'Mon'=>false,'Tue'=>false, 'Wed'=>false, 'Thu'=>false, 'Fri'=>false],
        array $skipHolidays = []) {
        $this->skipWeekDays = $skipWeekDays;
        $this->skipHolidays = $skipHolidays;
    }


    /**
     *
     *
     * @param DateTimeInterface|string|Stringable|int|float $initialDate
     * @param string|Stringable $duration interval
     * @param DateTimeInterface|string|Stringable|int|float $fromDate
     * @param DateTimeInterface|string|Stringable|int|float $toDate
     * @return DatePeriod
     * @throws Exception
     */
    public function datePeriodFromTo($initialDate, string|Stringable $duration,  $fromDate, $toDate):DatePeriod {
        $initial = $this->toImmutable($initialDate);
        $from = $this->toImmutable($fromDate);
        $dateInterval = new DateInterval((string)$duration);
        $startDate = $this->initialDate($initial, $dateInterval, $from);
        $dateInterval->invert =  $initial <= $from ? 0 : 1;
        return new DatePeriod($startDate, $dateInterval, $this->toImmutable($toDate));
    }

    /**
     *
     *
     * @param DateTimeInterface|string|Stringable|int|float $initialDate
     * @param string|Stringable $duration interval
     * @param DateTimeInterface|string|Stringable|int|float $fromDate
     * @param int|string|Stringable $n recurrences
     * @return DatePeriod
     * @throws Exception
     */
    public function datePeriodFromNRecurrences($initialDate, $duration,  $fromDate, $n):DatePeriod {
        $initial = $this->toImmutable($initialDate);
        $from = $this->toImmutable($fromDate);
        $dateInterval = new DateInterval((string)$duration);
        $startDate = $this->initialDate($initial, $dateInterval, $from);
        if($n instanceof Stringable) {
            $n = (int)"$n";
        }
        $n = (int)$n;
        if($n === 0) {
            $n = 1;
        }
        $dateInterval->invert = $n < 0 ? 1 : 0;
        return new DatePeriod($startDate, $dateInterval, $n);
    }

    /**
     * $fromDate adjusted to the interval from $initialDate
     *
     * @param DateTimeInterface|string|Stringable|int|float $initialDate
     * @param string|Stringable $dateInterval interval
     * @param DateTimeInterface|string|Stringable|int|float $fromDate
     * @return DateTimeImmutable
     * @throws Exception
     */
    public function firstDate($initialDate, string|Stringable $dateInterval, $fromDate):DateTimeImmutable {
        return $this->initialDate(
            $this->toImmutable($initialDate),
            new DateInterval((string)$dateInterval),
            $this->toImmutable($fromDate)
        );
    }

    /**
     * $fromDate adjusted to the interval from $initialDate
     *
     * @param DateTimeImmutable $initialDate
     * @param DateInterval $dateInterval interval
     * @param DateTimeImmutable $fromDate
     * @return DateTimeImmutable
     * @throws Exception
     */
    protected function initialDate($initialDate, $dateInterval, $fromDate):DateTimeImmutable {
        if($initialDate > $fromDate) {
            $dateInterval->invert = 1;
            $startDate = $initialDate;
            foreach((new DatePeriod($initialDate, $dateInterval , $fromDate)) as $date) {
                if($date < $fromDate) {
                    return $startDate;
                }
                $startDate = $date;
            }
        }
        $dateInterval->invert = 0;
        $startDate = $initialDate;
        foreach((new DatePeriod($initialDate, $dateInterval , $fromDate)) as $date) {
            if($date > $fromDate) {
                return $startDate;
            }
            $startDate = $date;
        }
        return $startDate;
    }

    /**
     * Converts any date format to a DateTimeImmutable
     *
     * @param DateTimeInterface|string|Stringable|int|float $anyDateTime
     * @return DateTimeImmutable
     * @throws Exception
     */
    protected function toImmutable($anyDateTime): DateTimeImmutable {
        if($anyDateTime instanceof DateTimeImmutable) {
            return $anyDateTime;
        }
        if($anyDateTime instanceof DateTime) {
            return DateTimeImmutable::createFromMutable($anyDateTime);
        }
        if($anyDateTime instanceof Stringable) {
            $anyDateTime = (string)$anyDateTime;
        }
        return new DateTimeImmutable($anyDateTime);
    }

}
