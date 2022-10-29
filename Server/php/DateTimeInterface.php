<?php
namespace TTG;
use \DateTimeInterface as DateTimeInterface;

class DateTimeImmutable extends \DateTimeImmutable
{
    /**
     * Int Day in Month
     */
    public $day;
    /**
     * Int Month of Year
     */
    public $month;
    /**
     * Int Year in Calendar
     */
    public $year;
    /**
     * Int Days in Month
     */
    public $daysInMonth;

    public function __construct(string $datetime = "now", \DateTimeZone $timezone = NULL)
    {
        parent::__construct($datetime, $timezone);

        $this->day = (int) $this->format('j');
        $this->month = (int) $this->format('n');
        $this->year = (int) $this->format('Y');
        $this->daysInMonth = (int) $this->format('t');
    }

    public function resolveThisMonth(DateTime &$timeStart = null, DateTime &$timeEnd = null)
    {
        if ($timeStart === null)
        {
            $timeStart = new DateTime('first day of this month 00:00:00');
        }

        if ($timeEnd === null)
        {
            $timeEnd = new DateTime('first day of next month 00:00:00');
            $timeEnd->modify('-1 Second');
        }
    }

    public function __toString(): string
    {
        return $this->format('Y-m-d H:i');
    }
}