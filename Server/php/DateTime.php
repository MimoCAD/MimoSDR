<?php
namespace TTG;
use \DateTimeInterface as DateTimeInterface;
use \DateTimeZone as DateTimeZone;

class DateTime extends \DateTime
{
    /**
     * Return just the date component.
     */
    const DATE = 'Y-m-d';
    /**
     * Return just the time component.
     */
    const TIME = 'H:i:s';
    /**
     * Return Date in WEB format
     */
    const WEB = 'Y-m-d\TH:i:s';
    /**
     * Return Date in SQL format
     */
    const SQL = self::ISO8601;
    /**
     * Return an SQLite formatted Time String.
     */
    const SQLite = 'Y-m-d\TH:i';
    /**
     * Return an SQLite formatted TIMESTAMP.
     */
    const TIMESTAMP = 'Y-m-d H:i:s';
    /**
     * SQLTimeStamp = 'Y-m-d\TH:i:s'
     */
    const SQLTimeStamp = 'Y-m-d\TH:i:s';
    /**
     * Return an SQLite formatted string suiteable for BETWEEN calls.
     */
    const BETWEEN = 'Y-m-d H:i:s.u';
    /**
     * Return Date in FireComCad format
     * Only works on PHP 7.0 or better.
     */
    const FCC = 'Y-m-d\TH:i:s.vP';
    /**
     * Return Date in Google format
     * Only works on PHP 7.0 or better.
     */
    const Google = 'Y-m-d\TH:i:s.v\Z';
    /**
     * Return Date in emsCharts format.
     */
    const emsCharts = self::SQL . '\Z';
    /**
     * Return Date in W3C Date Format.
     */
    const W3C_DATE = 'Y-m-d';
    /**
     * Warning -- Your timezone must be transfered to UTC time before using this.
     */
    const ZULU = 'Y-m-d\TH:i:s\Z';

    /**
     * Return Date in ISO8601 format
     *
     * @return String
     */
    public function __toString()
    {
        return $this->format(self::SQLite);
    }

    /**
     * Return difference between $this and $delta
     *
     * @param Datetime|String $delta
     * @return DateInterval
     */
    public function delta($delta = 'NOW', DateTimeZone $timezone = NULL)
    {
        if (!($delta instanceOf DateTime))
        {
            $delta = new DateTime($delta, $timezone);
        }
        return parent::diff($delta);
    }

    /**
     * Return the delta in Years.
     *
     * @param Datetime|String $delta
     * @return Integer
     */
    public function getYears($delta = 'NOW'): int
    {
        return $this->delta($delta)->format('%y');
    }

    /**
    * Is DateTime In Span
    *
    * Find out if $time is within the $start & $end DateTimes.
    *
    * @param DateTime $start
    * @param DateTime $end
    * @param DateTime $time
    * @return bool TRUE if the time is within the specified time frame.
    */
    public function isDateTimeInSpan(DateTimeInterface $start, DateTimeInterface $end, DateTimeInterface $time = NULL): bool
    {
        if ($time === NULL)
        {
            $time = $this;
        }

        if ($end < $start)
        {
            $end = $end->modify('+1 Day');
        }

        return ($time >= $start AND $time <= $end) ? TRUE : FALSE;
    }

    /**
    * Is In Span
    * 
    * Finds out if a time is in a given startTime and endTime.
    *
    * @param string $date ISO 8601 Date (2015-06-02).
    * @param string $start In Military Time (1900).
    * @param string $end In Military Time (0700).
    * @param DateTime $time Optional. Time to check against.
    * @return bool TRUE if the time is within the specified time frame.
    */
    public function isInSpan(string $date, string $start, string $end, DateTimeInterface $time = NULL): bool
    {
        $startTime = new DateTime("$date $start");

        $endTime = new DateTime("$date $end");

        if ($endTime < $startTime)
        {
            $endTime = $endTime->modify("+1 Day");
        }

        if ($time === NULL)
        {
            $time = $this;
        }

        return self::isDateTimeInSpan($startTime, $endTime, $time);
    }

    /**
    * String to Span
    * 
    * Takes a date, start time and end time and turns it into a
    * tuple of DateTime objects for the start and end of the crew.
    * 
    * @param string $date ISO 8601 Date (2015-06-02).
    * @param String $start In Military Time (1900)
    * @param String $end In Military Time (0700)
    * @return Array tuple, ordered pair of DateTime fields.
    */
    public static function strToSpan(string $date, string $start, string $end): array
    {
        # Start
        $startTime = new DateTime("$date $start");

        # End
        $endTime = new DateTime("$date $end");

        # Make sure start is after end. 
        if ($startTime > $endTime)
        {
            $endTime = $endTime->modify("+1 Day");
        }

        return [$startTime, $endTime];
    }

    /**
    * Is Span In Span
    *
    * Is time span in another time span.
    * 
    * @param DateTime $crewStart In ISO 8601 Format
    * @param DateTime $crewEnd In ISO 8601 Format
    * @param DateTime $signStart In ISO 8601 Format
    * @param DateTime $signEnd In ISO 8601 Format
    * @return bool TRUE if $span is within the $crew.
    */
    public static function isSpanInSpan(DateTimeInterface $crewStart, DateTimeInterface $crewEnd, DateTimeInterface $signStart, DateTimeInterface $signEnd)
    {
        return ($signStart <= $crewStart AND $signEnd >= $crewEnd) ? TRUE : FALSE;
    }

    /**
    * Is Span Overlapping Span
    *
    * Is time overlapping another span.
    * 
    * @param DateTime $crewStart In ISO 8601 Format
    * @param DateTime $crewEnd In ISO 8601 Format
    * @param DateTime $signStart In ISO 8601 Format
    * @param DateTime $signEnd In ISO 8601 Format
    * @return bool TRUE if $span is within the $crew.
    */
    public static function isSpanOverlappingSpan(DateTimeInterface $crewStart, DateTimeInterface $crewEnd, DateTimeInterface $signStart, DateTimeInterface $signEnd)
    {
        return (
            # if ends after start
            $crewEnd > $signStart
            AND
            # if starts before end 
            $crewStart < $signEnd
        ) ? TRUE : FALSE;
    }


    /**
    * Sunrise
    *
    * Returns time of sunrise for a given day and location.
    *
    * @param DateTime $timestamp If not set assumes this instance, otherwise must be a DateTime object.
    * @param float $latitude Defaults to North, pass in a negative value for South. See also: date.default_latitude.
    * @param float $longitude Defaults to East, pass in a negative value for West. See also: date.default_longitude.
    * @param float $zenith Default: date.sunrise_zenith
    * @param float $gmt_offset Specified in hours.
    * @return Returns the sunrise time in a specified format on success or FALSE on failure.
    */
    public function sunrise(DateTimeInterface $timestamp = null, float $latitude = null, float $longitude = null, float $zenith = null, float $gmt_offset = null)
    {
        $timestamp = ($timestamp) ? $timestamp->format('U') : $this->format('U');
        $latitude = ($latitude) ?: ini_get("date.default_latitude");
        $longitude = ($longitude) ?: ini_get("date.default_longitude");
        $zenith = ($zenith) ?: ini_get("date.sunrise_zenith");
        $return = date_sunrise($timestamp, SUNFUNCS_RET_TIMESTAMP, $latitude, $longitude, $zenith, $gmt_offset);

        return ($return) ? (new DateTime())->setTimestamp($return) : false;
    }

    /**
    * Sunset
    *
    * Returns time of sunset for a given day and location.
    *
    * @param DateTime $timestamp If not set assumes this instance, otherwise must be a DateTime object.
    * @param float $latitude Defaults to North, pass in a negative value for South. See also: date.default_latitude.
    * @param float $longitude Defaults to East, pass in a negative value for West. See also: date.default_longitude.
    * @param float $zenith Default: date.sunrise_zenith
    * @param float $gmt_offset Specified in hours.
    * @return Returns the sunset time in a specified format on success or FALSE on failure.
    */
    public function sunset(DateTimeInterface $timestamp = null, float $latitude = null, float $longitude = null, float $zenith = null, float $gmt_offset = null)
    {
        $timestamp = ($timestamp) ? $timestamp->format('U') : $this->format('U');
        $latitude = ($latitude) ?: ini_get("date.default_latitude");
        $longitude = ($longitude) ?: ini_get("date.default_longitude");
        $zenith = ($zenith) ?: ini_get("date.sunrise_zenith");
        $return = date_sunset($timestamp, SUNFUNCS_RET_TIMESTAMP, $latitude, $longitude, $zenith, $gmt_offset);

        return ($return) ? (new DateTime())->setTimestamp($return) : false;
    }

    /**
    * Sun Info
    *
    * Returns an array with information about sunset / sunrise and twilight begin / end
    *
    * @param DateTime $timestamp If not set assumes this instance, otherwise must be a DateTime object.
    * @param float $latitude Defaults to North, pass in a negative value for South. See also: date.default_latitude.
    * @param float $longitude Defaults to East, pass in a negative value for West. See also: date.default_longitude.
    * @return Returns array on success or FALSE on failure.
    */
    public function sunInfo(DateTimeInterface $timestamp = null, float $latitude = null, float $longitude = null)
    {
        $timestamp = ($timestamp) ? $timestamp->format('U') : $this->format('U');
        $latitude = ($latitude) ?: ini_get("date.default_latitude");
        $longitude = ($longitude) ?: ini_get("date.default_longitude");

        return date_sun_info($timestamp, $latitude, $longitude);
    }

    public function resolveThisYear(DateTime &$timeStart = null, DateTime &$timeEnd = null)
    {
        if ($timeStart === null)
        {
            $timeStart = new DateTime(date('Y') . '-01-01T00:00:00');
        }

        if ($timeEnd === null)
        {
            $timeEnd = new DateTime(date('Y') . '-12-31T23:59:59');
        }
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

}
