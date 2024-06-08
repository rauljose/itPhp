<?php /** @noinspection PhpUnused */

namespace coso\Other;

use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use JetBrains\PhpStorm\ArrayShape;
use Stringable;


class WorkDays {
    const ARRAY_SHAPE_WORK_DAYS =
        ['Sun'=>'bool','Sat'=>'bool','Mon'=>'bool','Tue'=>'bool', 'Wed'=>'bool', 'Thu'=>'bool', 'Fri'=>'bool'];

    /**
     * @var array $skipWeekDays = #[ArrayShape(self::ARRAY_SHAPE_WORK_DAYS)]
     */
    protected array $skipWeekDays;

    /**
     * @var array<int|string, string|Stringable|int|float|DateTimeInterface> $skipHolidays ['anyDate',..]
     */
    protected array $skipHolidays;

    protected DateInterval $oneDay;

    /**
     * @param array $skipWeekDays = #[ArrayShape(['Sun'=>'bool','Sat'=>'bool','Mon'=>'bool','Tue'=>'bool', 'Wed'=>'bool', 'Thu'=>'bool', 'Fri'=>'bool']]
     * @param array<int|string, string|Stringable|int|float|DateTimeInterface|null > $skipHolidays ['anyDate',..]
     */
    public function __construct(
        #[ArrayShape(self::ARRAY_SHAPE_WORK_DAYS)]
        array $skipWeekDays = ['Sun'=>true,'Sat'=>true,'Mon'=>false,'Tue'=>false, 'Wed'=>false, 'Thu'=>false, 'Fri'=>false],
        array $skipHolidays = [])
    {
        $this->skipWeekDays = $skipWeekDays;
        $this->skipHolidays = [];
        foreach($skipHolidays as $key => $d)
            if(!empty($d))
                $this->skipHolidays[$this->toYmd($d)] = $key;
        $this->oneDay = new DateInterval('P1D');
    }

    /**
     * @param DateTimeInterface $date
     * @return bool
     */
    public function is_workDay(DateTimeInterface $date):bool {
        if(array_key_exists($date->format('Y-m-d'),  $this->skipHolidays))
            return false;
        return !$this->skipWeekDays[$date->format('D')];
    }

    /**
     * @param DateTimeImmutable $date
     * @return DateTimeImmutable
     */
    public function workDayOrNext(DateTimeImmutable $date):DateTimeImmutable {
        if(array_key_exists($date->format('Y-m-d'),  $this->skipHolidays))
            return $this->workDayOrNext($date->add($this->oneDay));
        if($this->skipWeekDays[$date->format('D')])
            return $this->workDayOrNext($date->add($this->oneDay));
        return $date;
    }

    /**
     * @param DateTimeImmutable $date
     * @return DateTimeImmutable
     */
    public function workDayOrPrev(DateTimeImmutable $date):DateTimeImmutable {
        if(array_key_exists($date->format('Y-m-d'),  $this->skipHolidays))
            return $this->workDayOrPrev($date->sub($this->oneDay));
        if($this->skipWeekDays[$date->format('D')])
            return $this->workDayOrPrev($date->sub($this->oneDay));
        return $date;
    }

    /**
     * @param DateTimeImmutable $date
     * @param int|float|string|Stringable|null $days
     * @return DateTimeImmutable|mixed
     */
    public function addWorkDays(DateTimeImmutable $date, int|float|string|Stringable|null $days) {
        if($days < 0)
            return $this->subWorkDays($date, -$days);
        if(is_string($days) || $days instanceof Stringable)
            $days = (int)"$days";
        for($i = 0; $i < $days; ++$i)
            $date = $this->workDayOrNext($date->add($this->oneDay));
        return $date;
    }

    public function subWorkDays(DateTimeImmutable $date, int|float|string|Stringable|null $days) {
        if($days < 0)
            return $this->addWorkDays($date, -$days);
        if(is_string($days) || $days instanceof Stringable)
            $days = (int)"$days";
        for($i = 0; $i < $days; ++$i)
            $date = $this->workDayOrPrev($date->sub($this->oneDay));
        return $date;
    }

    /**
     * @param string|Stringable|int|float|DateTimeInterface $anyDate
     * @return string
     */
    protected function toYmd($anyDate):string {
        if($anyDate instanceof DateTimeInterface)
            return $anyDate->format('Y-m-d');
        if($anyDate instanceof Stringable)
            $anyDate = (string)$anyDate;
        if(is_string($anyDate))
            $anyDate = trim($anyDate);
        if(is_numeric($anyDate))
            return Date('Y-m-d', (int)$anyDate);
        if(strlen($anyDate) > 6 && str_contains($anyDate, ' ') || str_contains($anyDate, 'T')) {
            $ymd = substr($anyDate, 0, 10);
            if($this->is_ymd($ymd))
                return $ymd;
        }
        if($this->is_ymd($anyDate))
            return $anyDate;
        return Date('Y-m-d', strtotime($anyDate));
    }

    protected function is_ymd($date):bool {
        if($date instanceof Stringable)
            $date = (string)$date;
        if(!is_string($date))
            return false;
        $parts = explode('-', $date);
        return count($parts) === 3 && checkdate($parts[1], $parts[2], $parts[0]);
    }

}