<?php namespace KurtJensen\MyCalendar\Classes;

use Carbon\Carbon;
/**
 * The Event Occurrence class.
 *
 * @package kurtjensen.mycalendar
 * @author Kurt Jensen
 */
class Rrules
{
    const SECONDLY = 7;
    const MINUTELY = 6;
    const HOURLY = 5;
    const DAILY = 4;
    const WEEKLY = 3;
    const MONTHLY = 2;
    const YEARLY = 1;
    // frequency names
    public static $frequencies = array(
        'SECONDLY' => self::SECONDLY,
        'MINUTELY' => self::MINUTELY,
        'HOURLY' => self::HOURLY,
        'DAILY' => self::DAILY,
        'WEEKLY' => self::WEEKLY,
        'MONTHLY' => self::MONTHLY,
        'YEARLY' => self::YEARLY,
    );
    // weekdays numbered from 1 (ISO-8601 or date('N'))
    public static $week_days = array(
        'MO' => 1,
        'TU' => 2,
        'WE' => 3,
        'TH' => 4,
        'FR' => 5,
        'SA' => 6,
        'SU' => 7,
    );
    // original rule
    protected $rule = array(
        'DTSTART' => null,
        'FREQ' => null,
        'UNTIL' => null,
        'COUNT' => null,
        'INTERVAL' => 1,
        'BYSECOND' => null,
        'BYMINUTE' => null,
        'BYHOUR' => null,
        'BYDAY' => null,
        'BYMONTHDAY' => null,
        'BYYEARDAY' => null,
        'BYWEEKNO' => null,
        'BYMONTH' => null,
        'BYSETPOS' => null,
        'WKST' => 'MO',
    );
    // parsed and validated values
    protected $dtstart = null;
    protected $freq = null;
    protected $until = null;
    protected $count = null;
    protected $interval = null;
    protected $bysecond = null;
    protected $byminute = null;
    protected $byhour = null;
    protected $byweekday = null;
    protected $byweekday_nth = null;
    protected $bymonthday = null;
    protected $bymonthday_negative = null;
    protected $byyearday = null;
    protected $byweekno = null;
    protected $bymonth = null;
    protected $bysetpos = null;
    protected $wkst = null;
    protected $timeset = null;
    // cache variables
    protected $total = null;
    protected $cache = array();
    /**
     * @var object The event object being split into its occurrences.
     */
    public $parts = [];
    public $parameters = [];
    public $dtstart = [];

    /**
     * Comment.
     * @param 
     */
    public function __construct($parameters, $dtstart)
    {
        $this->dtstart = new Carbon($dtstart);
        if (is_string($parameters)) {
            $parameters = self::parseRfcString($parameters);
        } elseif (is_array($parameters)) {
            $parameters = array_change_key_case($parameters, CASE_UPPER);
        } else {
            throw new \InvalidArgumentException(sprintf(
                'The argument for the RRULE class must be a string or an array (%s provided)',
                gettype($parameters)
            ));
        }
        // validate extra parameters
        $unsupported = array_diff_key($parameters, $this->rule);
        if (!empty($unsupported)) {
            throw new \InvalidArgumentException(
                'Unsupported parameter(s): '
                . implode(',', array_keys($unsupported))
            );
        }
        $this->parameters = $parameters;
    }
    /**
     * Comment.
     * @param 
     */
    public function process()
    {
        if (!array_key_exists($this->parameters['WKST']){ $this->wkst($this->parameters('WKST')};
        if (!array_key_exists($this->parameters['FREQ']){ $this->freq($this->parameters('FREQ')};
        if (!array_key_exists($this->parameters['INTERVAL']){ $this->interval($this->parameters('INTERVAL')};
        if (!array_key_exists($this->parameters['DTSTART']){ $this->dtstart($this->parameters('DTSTART')};
        if (!array_key_exists($this->parameters['UNTIL']){ $this->until($this->parameters('UNTIL')};
        if (!array_key_exists($this->parameters['COUNT']){ $this->count($this->parameters('COUNT')};
        if (!array_key_exists($this->parameters['BYDAY']){ $this->byday($this->parameters('BYDAY')};
        if (!array_key_exists($this->parameters['BYMONTHDAY']){ $this->bymonthday($this->parameters('BYMONTHDAY')};
        if (!array_key_exists($this->parameters['BYYEARDAY']){ $this->byyearday($this->parameters('BYYEARDAY')};
        if (!array_key_exists($this->parameters['BYWEEKNO']){ $this->byweekno($this->parameters('BYWEEKNO')};
        if (!array_key_exists($this->parameters['BYMONTH']){ $this->bymonth($this->parameters('BYMONTH')};
        if (!array_key_exists($this->parameters['BYSETPOS']){ $this->bysetpos($this->parameters('BYSETPOS')};
        if (!array_key_exists($this->parameters['BYHOUR']){ $this->byhour($this->parameters('BYHOUR')};
        if (!array_key_exists($this->parameters['BYMINUTE']){ $this->byminute($this->parameters('BYMINUTE')};
        if (!array_key_exists($this->parameters['BYSECOND']){ $this->bysecond($this->parameters('BYSECOND')};
        
    }

    public function __toString()
    {
        return $this->rfcString();
    }
    /**
     * Format a rule according to RFC 5545
     * @return string
     */
    public function rfcString()
    {
        $str = '';
        
        $parts = array();
        foreach ($this->rule as $key => $value) {
            if ($key === 'INTERVAL' && $value == 1) {
                continue;
            }
            if ($key === 'WKST' && $value === 'MO') {
                continue;
            }
            if ($key === 'UNTIL' && $value) {
                // for a reason that I do not understand, UNTIL seems to always
                // be in UTC (even when DTSTART includes TZID)
                $tmp = clone $this->until;
                $tmp->setTimezone(new \DateTimeZone('UTC'));
                $parts[] = 'UNTIL=' . $tmp->format('Ymd\THis\Z');
                continue;
            }
            if ($value) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $parts[] = strtoupper(str_replace(' ', '', "$key=$value"));
            }
        }
        $str .= implode(';', $parts);
        return $str;
    }

     /**
     * Take a RFC 5545 string and returns an array (to be given to the constructor)
     * @return array
     */
    public static function parseRfcString($string)
    {
        /*
FREQ=DAILY;UNTIL=19971224T000000Z;WKST=SU;BYDAY=MO,WE,FR;BYMONTH=1' 
*/
            $string = trim($string);
            
            $allProperties = explode(';', $string);
            
            foreach ($allProperties as $parameter) { // TZID=America/New_York
                if (strpos($parameter, '=') === false) {
                    throw new \InvalidArgumentException('Failed to parse RFC string, invlaid property parameters: ' . $parameter);
                }

                list($key, $value) = explode('=', $pair);
                if ($key === 'UNTIL') {
                    $value = new \DateTime($value);
                }
                $parts[$key] = $value;
            }
        }
        return $this->parts = $parts;
    }




    protected function wkst($parameter)
    {

            // WKST
        $rule = strtoupper($parameter);
        if (!array_key_exists($parameter, self::$week_days)) {
            throw new \InvalidArgumentException(
                'The WKST rule part must be one of the following: '
                . implode(', ', array_keys(self::$week_days))
            );
        }
        $this->wkst = self::$week_days[$parameter];
    }




    protected function freq($parameter)
    {        
        // FREQ
        if (is_integer($parameter)) {
            if ($parameter > self::SECONDLY || $parameter < self::YEARLY) {
                throw new \InvalidArgumentException(
                    'The FREQ rule part must be one of the following: '
                    . implode(', ', array_keys(self::$frequencies))
                );
            }
            $this->freq = $parameter;
        } else {
            // string
            $parameter = strtoupper($parameter);
            if (!array_key_exists($parameter, self::$frequencies)) {
                throw new \InvalidArgumentException(
                    'The FREQ rule part must be one of the following: '
                    . implode(', ', array_keys(self::$frequencies))
                );
            }
            $this->freq = self::$frequencies[$parameter];
        }
    }




    protected function interval($parameter)
    {     
        // INTERVAL
        $parameter = (int) $parameter;
        if ($parameter < 1) {
            throw new \InvalidArgumentException(
                'The INTERVAL rule part must be a positive integer (> 0)'
            );
        }
        $this->interval = (int) $parameter;
    
    }




    protected function dstart($parameter = null)
    {     
        // DTSTART
        if (not_empty($parameter)) {
            try {
                $this->dtstart = self::parseDate($parameter);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(
                    'Failed to parse DTSTART ; it must be a valid date, timestamp or \DateTime object'
                );
            }
        } else {
            $this->dtstart = new \DateTime();
        }
    
    }




    protected function until($parameter)
    {     
        // UNTIL (optional)
        if (not_empty($parameter)) {
            try {
                $this->until = self::parseDate($parameter);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(
                    'Failed to parse UNTIL ; it must be a valid date, timestamp or \DateTime object'
                );
            }
        }
    
    }




    protected function count($parameter)
    {     
        // COUNT (optional)
        if (not_empty($parameter)) {
            $parameter = (int) $parameter;
            if ($parameter < 1) {
                throw new \InvalidArgumentException('COUNT must be a positive integer (> 0)');
            }
            $this->count = $parameter;
        }
        if ($this->until && $this->count) {
            throw new \InvalidArgumentException('The UNTIL or COUNT rule parts MUST NOT occur in the same rule');
        }
        // infer necessary BYXXX rules from DTSTART, if not provided
        if (!(not_empty($this->parts['BYWEEKNO']) || not_empty($this->parts['BYYEARDAY']) || not_empty($this->parts['BYMONTHDAY']) || not_empty($this->parts['BYDAY']))) {
            switch ($this->freq) {
                case self::YEARLY:
                    if (!not_empty($this->parts['BYMONTH'])) {
                        $this->parts['BYMONTH'] = [(int) $this->dtstart->month];
                    }
                    $this->parts['BYMONTHDAY'] = [(int) $this->dtstart->day];
                    break;
                case self::MONTHLY:
                    $this->parts['BYMONTHDAY'] = [(int) $this->dtstart->day];
                    break;
                case self::WEEKLY:
                    $this->parts['BYDAY'] = [array_search($this->dtstart->dayOfWeek, self::$week_days)];
                    break;
            }
        }
    
    }




    protected function byday($parameter)
    {     
        // BYDAY (translated to byweekday for convenience)
        if (not_empty($parameter)) {
            if (!is_array($parameter)) {
                $parameter = explode(',', $parameter);
            }
            $this->byweekday = array();
            $this->byweekday_nth = array();
            foreach ($parameter as $value) {
                $value = trim(strtoupper($value));
                $valid = preg_match('/^([+-]?[0-9]+)?([A-Z]{2})$/', $value, $matches);
                if (!$valid || (not_empty($matches[1]) && ($matches[1] == 0 || $matches[1] > 53 || $matches[1] < -53)) || !array_key_exists($matches[2], self::$week_days)) {
                    throw new \InvalidArgumentException('Invalid BYDAY value: ' . $value);
                }
                if ($matches[1]) {
                    $this->byweekday_nth[] = array(self::$week_days[$matches[2]], (int) $matches[1]);
                } else {
                    $this->byweekday[] = self::$week_days[$matches[2]];
                }
            }
            if (!empty($this->byweekday_nth)) {
                if (!($this->freq === self::MONTHLY || $this->freq === self::YEARLY)) {
                    throw new \InvalidArgumentException('The BYDAY rule part MUST NOT be specified with a numeric value when the FREQ rule part is not set to MONTHLY or YEARLY.');
                }
                if ($this->freq === self::YEARLY && not_empty($this->parts['BYWEEKNO'])) {
                    throw new \InvalidArgumentException('The BYDAY rule part MUST NOT be specified with a numeric value with the FREQ rule part set to YEARLY when the BYWEEKNO rule part is specified.');
                }
            }
        }
    
    }




    protected function bymonthday($parameter)
    {     
        // The BYMONTHDAY rule part specifies a COMMA-separated list of days
        // of the month.  Valid values are 1 to 31 or -31 to -1.  For
        // example, -10 represents the tenth to the last day of the month.
        // The BYMONTHDAY rule part MUST NOT be specified when the FREQ rule
        // part is set to WEEKLY.
        if (not_empty($parameter)) {
            if ($this->freq === self::WEEKLY) {
                throw new \InvalidArgumentException('The BYMONTHDAY rule part MUST NOT be specified when the FREQ rule part is set to WEEKLY.');
            }
            if (!is_array($parameter)) {
                $parameter = explode(',', $parameter);
            }
            $this->bymonthday = array();
            $this->bymonthday_negative = array();
            foreach ($parameter as $value) {
                $value = (int) $value;
                if (!$value || $value < -31 || $value > 31) {
                    throw new \InvalidArgumentException('Invalid BYMONTHDAY value: ' . $value . ' (valid values are 1 to 31 or -31 to -1)');
                }
                if ($value < 0) {
                    $this->bymonthday_negative[] = $value;
                } else {
                    $this->bymonthday[] = $value;
                }
            }
        }
    
    }




    protected function byyearday($parameter)
    {    

        if (not_empty($parameter)) {
            if ($this->freq === self::DAILY || $this->freq === self::WEEKLY || $this->freq === self::MONTHLY) {
                throw new \InvalidArgumentException('The BYYEARDAY rule part MUST NOT be specified when the FREQ rule part is set to DAILY, WEEKLY, or MONTHLY.');
            }
            if (!is_array($parameter)) {
                $parameter = explode(',', $parameter);
            }
            $this->bysetpos = array();
            foreach ($parameter as $value) {
                $value = (int) $value;
                if (!$value || $value < -366 || $value > 366) {
                    throw new \InvalidArgumentException('Invalid BYSETPOS value: ' . $value . ' (valid values are 1 to 366 or -366 to -1)');
                }
                $this->byyearday[] = $value;
            }
        }
    
    }




    protected function byweekno($parameter)
    {     
        // BYWEEKNO
        if (not_empty($parameter)) {
            if ($this->freq !== self::YEARLY) {
                throw new \InvalidArgumentException('The BYWEEKNO rule part MUST NOT be used when the FREQ rule part is set to anything other than YEARLY.');
            }
            if (!is_array($parameter)) {
                $parameter = explode(',', $parameter);
            }
            $this->byweekno = array();
            foreach ($parameter as $value) {
                $value = (int) $value;
                if (!$value || $value < -53 || $value > 53) {
                    throw new \InvalidArgumentException('Invalid BYWEEKNO value: ' . $value . ' (valid values are 1 to 53 or -53 to -1)');
                }
                $this->byweekno[] = $value;
            }
        }
    
    }




    protected function bymonth($parameter)
    {     
        // The BYMONTH rule part specifies a COMMA-separated list of months
        // of the year.  Valid values are 1 to 12.
        if (not_empty($parameter)) {
            if (!is_array($parameter)) {
                $parameter = explode(',', $parameter);
            }
            $this->bymonth = array();
            foreach ($parameter as $value) {
                $value = (int) $value;
                if ($value < 1 || $value > 12) {
                    throw new \InvalidArgumentException('Invalid BYMONTH value: ' . $value);
                }
                $this->bymonth[] = $value;
            }
        }

    
    }




    protected function bysetpos($parameter)
    {     
        if (not_empty($parameter)) {
            if (!(not_empty($this->parts['BYWEEKNO']) || not_empty($this->parts['BYYEARDAY'])
                || not_empty($this->parts['BYMONTHDAY']) || not_empty($this->parts['BYDAY'])
                || not_empty($this->parts['BYMONTH']) || not_empty($this->parts['BYHOUR'])
                || not_empty($this->parts['BYMINUTE']) || not_empty($this->parts['BYSECOND']))) {
                throw new \InvalidArgumentException('The BYSETPOS rule part MUST only be used in conjunction with another BYxxx rule part.');
            }
            if (!is_array($parameter)) {
                $parameter = explode(',', $parameter);
            }
            $this->bysetpos = array();
            foreach ($parameter as $value) {
                $value = (int) $value;
                if (!$value || $value < -366 || $value > 366) {
                    throw new \InvalidArgumentException('Invalid BYSETPOS value: ' . $value . ' (valid values are 1 to 366 or -366 to -1)');
                }
                $this->bysetpos[] = $value;
            }
        }
    }




    protected function byhour($parameter)
    {  


        if (not_empty($parameter)) {
            if (!is_array($parameter)) {
                $parameter = explode(',', $parameter);
            }
            $this->byhour = array();
            foreach ($parameter as $value) {
                $value = (int) $value;
                if ($value < 0 || $value > 23) {
                    throw new \InvalidArgumentException('Invalid BYHOUR value: ' . $value);
                }
                $this->byhour[] = $value;
            }
            sort($this->byhour);
        } elseif ($this->freq < self::HOURLY) {
            $this->byhour = array((int) $this->dtstart->hour);
        }
    }




    protected function byminute($parameter)
    {  


        if (not_empty($parameter)) {
            if (!is_array($parameter)) {
                $parameter$parameter = explode(',', $parameter);
            }
            $this->byminute = array();
            foreach ($parameter as $value) {
                $value = (int) $value;
                if ($value < 0 || $value > 59) {
                    throw new \InvalidArgumentException('Invalid BYMINUTE value: ' . $value);
                }
                $this->byminute[] = $value;
            }
            sort($this->byminute);
        } elseif ($this->freq < self::MINUTELY) {
            $this->byminute = array((int) $this->dtstart->minute);
        }
    }




    protected function bysecond($parameter)
    {  
        if (not_empty($parameter)) {
            if (!is_array($parameter)) {
                $parameter = explode(',', $parameter);
            }
            $this->bysecond = array();
            foreach ($parameter as $value) {
                $value = (int) $value;
                // yes, "60" is a valid value, in (very rare) cases on leap seconds
                //  December 31, 2005 23:59:60 UTC is a valid date...
                // so is 2012-06-30T23:59:60UTC
                if ($value < 0 || $value > 60) {
                    throw new \InvalidArgumentException('Invalid BYSECOND value: ' . $value);
                }
                $this->bysecond[] = $value;
            }
            sort($this->bysecond);
        } elseif ($this->freq < self::SECONDLY) {
            $this->bysecond = array((int) $this->dtstart->second);
        }
        if ($this->freq < self::HOURLY) {
            // for frequencies DAILY, WEEKLY, MONTHLY AND YEARLY, we can build
            // an array of every time of the day at which there should be an
            // occurrence - default, if no BYHOUR/BYMINUTE/BYSECOND are provided
            // is only one time, and it's the DTSTART time. This is a cached version
            // if you will, since it'll never change at these frequencies
            $this->timeset = array();
            foreach ($this->byhour as $hour) {
                foreach ($this->byminute as $minute) {
                    foreach ($this->bysecond as $second) {
                        $this->timeset[] = array($hour, $minute, $second);
                    }
                }
            }
        }
    }
    /**
     * This is the main method, where all of the magic happens.
     *
     * This method is a generator that works for PHP 5.3/5.4 (using static variables)
     *
     * The main idea is : a brute force made fast by not relying on date() functions
     *
     * There is one big loop that examines every interval of the given frequency
     * (so every day, every week, every month or every year), constructs an
     * array of all the yeardays of the interval (for daily frequencies, the array
     * only has one element, for weekly 7, and so on), and then filters out any
     * day that do no match BYXXX elements.
     *
     * The algorithm does not try to be "smart" in calculating the increment of
     * the loop. That is, for a rule like "every day in January for 10 years"
     * the algorithm will loop through every day of the year, each year, generating
     * some 3650 iterations (+ some to account for the leap years).
     * This is a bit counter-intuitive, as it is obvious that the loop could skip
     * all the days in February till December since they are never going to match.
     *
     * Fortunately, this approach is still super fast because it doesn't rely
     * on date() or DateTime functions, and instead does all the date operations
     * manually, either arithmetically or using arrays as converters.
     *
     * Another quirk of this approach is that because the granularity is by day,
     * higher frequencies (hourly, minutely and secondly) have to have
     * their own special loops within the main loop, making the all thing quite
     * convoluted.
     * Moreover, at such frequencies, the brute-force approach starts to really
     * suck. For example, a rule like
     * "Every minute, every Jan 1st between 10:00 and 10:59, for 10 years"
     * requires a tremendous amount of useless iterations to jump from Jan 1st 10:59
     * at year 1 to Jan 1st 10.00 at year 2.
     *
     * In order to make a "smart jump", we would have to have a way to determine
     * the gap between the next occurence arithmetically. I think that would require
     * to analyze each "BYXXX" rule part that "Limit" the set (see the RFC page 43)
     * at the given frequency. For example, a YEARLY frequency doesn't need "smart
     * jump" at all; MONTHLY and WEEKLY frequencies only need to check BYMONTH;
     * DAILY frequency needs to check BYMONTH, BYMONTHDAY and BYDAY, and so on.
     * The check probably has to be done in reverse order, e.g. for DAILY frequencies
     * attempt to jump to the next weekday (BYDAY) or next monthday (BYMONTHDAY)
     * (I don't know yet which one first), and then if that results in a change of
     * month, attempt to jump to the next BYMONTH, and so on.
     * @return \DateTime|null
     */
    protected function iterate($reset = false)
    {
        // these are the static variables, i.e. the variables that persists
        // at every call of the method (to emulate a generator)
        static $year = null, $month = null, $day = null;
        static $hour = null, $minute = null, $second = null;
        static $dayset = null, $masks = null, $timeset = null;
        static $dtstart = null, $total = 0, $use_cache = true;
        if ($reset) {
            $year = $month = $day = null;
            $hour = $minute = $second = null;
            $dayset = $masks = $timeset = null;
            $dtstart = null;
            $total = 0;
            $use_cache = true;
            reset($this->cache);
        }
        // go through the cache first
        if ($use_cache) {
            while (($occurrence = current($this->cache)) !== false) {
                // echo "Cache hit\n";
                $dtstart = $occurrence;
                next($this->cache);
                $total += 1;
                return $occurrence;
            }
            reset($this->cache);
            // now set use_cache to false to skip the all thing on next iteration
            // and start filling the cache instead
            $use_cache = false;
            // if the cache as been used up completely and we now there is nothing else
            if ($total === $this->total) {
                // echo "Cache used up, nothing else to compute\n";
                return null;
            }
            // echo "Cache used up with occurrences remaining\n";
            if ($dtstart) {
                // so we skip the last occurrence of the cache
                if ($this->freq === self::SECONDLY) {
                    $dtstart->modify('+' . $this->interval . 'second');
                } else {
                    $dtstart->modify('+1second');
                }
            }
        }
        // stop once $total has reached COUNT
        if ($this->count && $total >= $this->count) {
            $this->total = $total;
            return null;
        }
        if ($dtstart === null) {
            $dtstart = clone $this->dtstart->copy();
        }
        if ($year === null) {
            if ($this->freq === self::WEEKLY) {
                // we align the start date to the WKST, so we can then
                // simply loop by adding +7 days. The Python lib does some
                // calculation magic at the end of the loop (when incrementing)
                // to realign on first pass.
                $tmp = clone $dtstart;
                $tmp->modify('-' . pymod($this->dtstart->day - $this->wkst, 7) . 'days');
                list($year, $month, $day, $hour, $minute, $second) = explode(' ', $tmp->format('Y n j G i s'));
                unset($tmp);
            } else {
                list($year, $month, $day, $hour, $minute, $second) = explode(' ', $dtstart->format('Y n j G i s'));
            }
            // remove leading zeros
            $minute = (int) $minute;
            $second = (int) $second;
        }
        // we initialize the timeset
        if ($timeset == null) {
            if ($this->freq < self::HOURLY) {
                // daily, weekly, monthly or yearly
                // we don't need to calculate a new timeset
                $timeset = $this->timeset;
            } else {
                // initialize empty if it's not going to occurs on the first iteration
                if (
                    ($this->freq >= self::HOURLY && $this->byhour && !in_array($hour, $this->byhour))
                    || ($this->freq >= self::MINUTELY && $this->byminute && !in_array($minute, $this->byminute))
                    || ($this->freq >= self::SECONDLY && $this->bysecond && !in_array($second, $this->bysecond))
                ) {
                    $timeset = array();
                } else {
                    $timeset = $this->getTimeSet($hour, $minute, $second);
                }
            }
        }
        // while (true) {
        $max_cycles = self::$REPEAT_CYCLES[$this->freq <= self::DAILY ? $this->freq : self::DAILY];
        for ($i = 0; $i < $max_cycles; $i++) {
            // 1. get an array of all days in the next interval (day, month, week, etc.)
            // we filter out from this array all days that do not match the BYXXX conditions
            // to speed things up, we use days of the year (day numbers) instead of date
            if ($dayset === null) {
                // rebuild the various masks and converters
                // these arrays will allow fast date operations
                // without relying on date() methods
                if (empty($masks) || $masks['year'] != $year || $masks['month'] != $month) {
                    $masks = array('year' => '', 'month' => '');
                    // only if year has changed
                    if ($masks['year'] != $year) {
                        $masks['leap_year'] = is_leap_year($year);
                        $masks['year_len'] = 365 + (int) $masks['leap_year'];
                        $masks['next_year_len'] = 365 + is_leap_year($year + 1);
                        $masks['weekday_of_1st_yearday'] = date_create($year . "-01-01 00:00:00")->format('N');
                        $masks['yearday_to_weekday'] = array_slice(self::$WEEKDAY_MASK, $masks['weekday_of_1st_yearday'] - 1);
                        if ($masks['leap_year']) {
                            $masks['yearday_to_month'] = self::$MONTH_MASK_366;
                            $masks['yearday_to_monthday'] = self::$MONTHDAY_MASK_366;
                            $masks['yearday_to_monthday_negative'] = self::$NEGATIVE_MONTHDAY_MASK_366;
                            $masks['last_day_of_month'] = self::$LAST_DAY_OF_MONTH_366;
                        } else {
                            $masks['yearday_to_month'] = self::$MONTH_MASK;
                            $masks['yearday_to_monthday'] = self::$MONTHDAY_MASK;
                            $masks['yearday_to_monthday_negative'] = self::$NEGATIVE_MONTHDAY_MASK;
                            $masks['last_day_of_month'] = self::$LAST_DAY_OF_MONTH;
                        }
                        if ($this->byweekno) {
                            $this->buildWeeknoMask($year, $month, $day, $masks);
                        }
                    }
                    // everytime month or year changes
                    if ($this->byweekday_nth) {
                        $this->buildNthWeekdayMask($year, $month, $day, $masks);
                    }
                    $masks['year'] = $year;
                    $masks['month'] = $month;
                }
                // calculate the current set
                $dayset = $this->getDaySet($year, $month, $day, $masks);
                $filtered_set = array();
                foreach ($dayset as $yearday) {
                    if ($this->bymonth && !in_array($masks['yearday_to_month'][$yearday], $this->bymonth)) {
                        continue;
                    }
                    if ($this->byweekno && !isset($masks['yearday_is_in_weekno'][$yearday])) {
                        continue;
                    }
                    if ($this->byyearday) {
                        if ($yearday < $masks['year_len']) {
                            if (!in_array($yearday + 1, $this->byyearday) && !in_array(-$masks['year_len'] + $yearday, $this->byyearday)) {
                                continue;
                            }
                        } else {
                            // if ( ($yearday >= $masks['year_len']
                            if (!in_array($yearday + 1 - $masks['year_len'], $this->byyearday) && !in_array(-$masks['next_year_len'] + $yearday - $mask['year_len'], $this->byyearday)) {
                                continue;
                            }
                        }
                    }
                    if (($this->bymonthday || $this->bymonthday_negative)
                        && !in_array($masks['yearday_to_monthday'][$yearday], $this->bymonthday)
                        && !in_array($masks['yearday_to_monthday_negative'][$yearday], $this->bymonthday_negative)) {
                        continue;
                    }
                    if ($this->byweekday && !in_array($masks['yearday_to_weekday'][$yearday], $this->byweekday)) {
                        continue;
                    }
                    if ($this->byweekday_nth && !isset($masks['yearday_is_nth_weekday'][$yearday])) {
                        continue;
                    }
                    $filtered_set[] = $yearday;
                }
                $dayset = $filtered_set;
                // if BYSETPOS is set, we need to expand the timeset to filter by pos
                // so we make a special loop to return while generating
                if ($this->bysetpos && $timeset) {
                    $filtered_set = array();
                    foreach ($this->bysetpos as $pos) {
                        $n = count($timeset);
                        if ($pos < 0) {
                            $pos = $n * count($dayset) + $pos;
                        } else {
                            $pos = $pos - 1;
                        }
                        $div = (int) ($pos / $n); // daypos
                        $mod = $pos % $n; // timepos
                        if (isset($dayset[$div]) && isset($timeset[$mod])) {
                            $yearday = $dayset[$div];
                            $time = $timeset[$mod];
                            // used as array key to ensure uniqueness
                            $tmp = $year . ':' . $yearday . ':' . $time[0] . ':' . $time[1] . ':' . $time[2];
                            if (!isset($filtered_set[$tmp])) {
                                $occurrence = \DateTime::createFromFormat(
                                    'Y z',
                                    "$year $yearday"
                                );
                                $occurrence->setTime($time[0], $time[1], $time[2]);
                                $filtered_set[$tmp] = $occurrence;
                            }
                        }
                    }
                    sort($filtered_set);
                    $dayset = $filtered_set;
                }
            }
            // 2. loop, generate a valid date, and return the result (fake "yield")
            // at the same time, we check the end condition and return null if
            // we need to stop
            if ($this->bysetpos && $timeset) {
                while (($occurrence = current($dayset)) !== false) {
                    // consider end conditions
                    if ($this->until && $occurrence > $this->until) {
                        $this->total = $total; // save total for count() cache
                        return null;
                    }
                    next($dayset);
                    if ($occurrence >= $dtstart) {
                        // ignore occurrences before DTSTART
                        $total += 1;
                        $this->cache[] = $occurrence;
                        return $occurrence; // yield
                    }
                }
            } else {
                // normal loop, without BYSETPOS
                while (($yearday = current($dayset)) !== false) {
                    $occurrence = \DateTime::createFromFormat('Y z', "$year $yearday");
                    while (($time = current($timeset)) !== false) {
                        $occurrence->setTime($time[0], $time[1], $time[2]);
                        // consider end conditions
                        if ($this->until && $occurrence > $this->until) {
                            $this->total = $total; // save total for count() cache
                            return null;
                        }
                        next($timeset);
                        if ($occurrence >= $dtstart) {
                            // ignore occurrences before DTSTART
                            $total += 1;
                            $this->cache[] = $occurrence;
                            return $occurrence; // yield
                        }
                    }
                    reset($timeset);
                    next($dayset);
                }
            }
            // 3. we reset the loop to the next interval
            $days_increment = 0;
            switch ($this->freq) {
                case self::YEARLY:
                    // we do not care about $month or $day not existing,
                    // they are not used in yearly frequency
                    $year = $year + $this->interval;
                    break;
                case self::MONTHLY:
                    // we do not care about the day of the month not existing
                    // it is not used in monthly frequency
                    $month = $month + $this->interval;
                    if ($month > 12) {
                        $div = (int) ($month / 12);
                        $mod = $month % 12;
                        $month = $mod;
                        $year = $year + $div;
                        if ($month == 0) {
                            $month = 12;
                            $year = $year - 1;
                        }
                    }
                    break;
                case self::WEEKLY:
                    $days_increment = $this->interval * 7;
                    break;
                case self::DAILY:
                    $days_increment = $this->interval;
                    break;
                // For the time frequencies, things are a little bit different.
                // We could just add "$this->interval" hours, minutes or seconds
                // to the current time, and go through the main loop again,
                // but since the frequencies are so high and needs to much iteration
                // it's actually a bit faster to have custom loops and only
                // call the DateTime method at the very end.
                case self::HOURLY:
                    if (empty($dayset)) {
                        // an empty set means that this day has been filtered out
                        // by one of the BYXXX rule. So there is no need to
                        // examine it any further, we know nothing is going to
                        // occur anyway.
                        // so we jump to one iteration right before next day
                        $hour += ((int) ((23 - $hour) / $this->interval)) * $this->interval;
                    }
                    $found = false;
                    for ($j = 0; $j < self::$REPEAT_CYCLES[self::HOURLY]; $j++) {
                        $hour += $this->interval;
                        $div = (int) ($hour / 24);
                        $mod = $hour % 24;
                        if ($div) {
                            $hour = $mod;
                            $days_increment += $div;
                        }
                        if (!$this->byhour || in_array($hour, $this->byhour)) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $this->total = $total;     // save total for count cache
                        return null;     // stop the iterator
                    }
                    $timeset = $this->getTimeSet($hour, $minute, $second);
                    break;
                case self::MINUTELY:
                    if (empty($dayset)) {
                        $minute += ((int) ((1439 - ($hour * 60 + $minute)) / $this->interval)) * $this->interval;
                    }
                    $found = false;
                    for ($j = 0; $j < self::$REPEAT_CYCLES[self::MINUTELY]; $j++) {
                        $minute += $this->interval;
                        $div = (int) ($minute / 60);
                        $mod = $minute % 60;
                        if ($div) {
                            $minute = $mod;
                            $hour += $div;
                            $div = (int) ($hour / 24);
                            $mod = $hour % 24;
                            if ($div) {
                                $hour = $mod;
                                $days_increment += $div;
                            }
                        }
                        if ((!$this->byhour || in_array($hour, $this->byhour)) &&
                            (!$this->byminute || in_array($minute, $this->byminute))) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $this->total = $total;     // save total for count cache
                        return null;     // stop the iterator
                    }
                    $timeset = $this->getTimeSet($hour, $minute, $second);
                    break;
                case self::SECONDLY:
                    if (empty($dayset)) {
                        $second += ((int) ((86399 - ($hour * 3600 + $minute * 60 + $second)) / $this->interval)) * $this->interval;
                    }
                    $found = false;
                    for ($j = 0; $j < self::$REPEAT_CYCLES[self::SECONDLY]; $j++) {
                        $second += $this->interval;
                        $div = (int) ($second / 60);
                        $mod = $second % 60;
                        if ($div) {
                            $second = $mod;
                            $minute += $div;
                            $div = (int) ($minute / 60);
                            $mod = $minute % 60;
                            if ($div) {
                                $minute = $mod;
                                $hour += $div;
                                $div = (int) ($hour / 24);
                                $mod = $hour % 24;
                                if ($div) {
                                    $hour = $mod;
                                    $days_increment += $div;
                                }
                            }
                        }
                        if ((!$this->byhour || in_array($hour, $this->byhour))
                            && (!$this->byminute || in_array($minute, $this->byminute))
                            && (!$this->bysecond || in_array($second, $this->bysecond))) {
                            $found = true;
                            break;
                        }
                    }
                    if (!$found) {
                        $this->total = $total;     // save total for count cache
                        return null;     // stop the iterator
                    }
                    $timeset = $this->getTimeSet($hour, $minute, $second);
                    break;
            }
            // here we take a little shortcut from the Python version, by using DateTime
            if ($days_increment) {
                list($year, $month, $day) = explode('-', date_create("$year-$month-$day")->modify("+ $days_increment days")->format('Y-n-j'));
            }
            $dayset = null; // reset the loop
        }
        $this->total = $total; // save total for count cache
        return null; // stop the iterator
    }
}