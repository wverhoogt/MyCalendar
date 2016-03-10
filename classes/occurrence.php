<?php namespace KurtJensen\MyCalendar\Classes;

use Lang;
use KurtJensen\MyCalendar\Models\Event as EventModel;
use KurtJensen\MyCalendar\Models\Occurrence as OccurrenceModel; 

/**
 * The Event Occurrence class.
 *
 * @package kurtjensen.mycalendar
 * @author Kurt Jensen
 */
class Occurrence
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

    /**
     * Comment.
     * @param 
     */
    public function __construct(EventModel $event = null)
    {
        $this->event = $event;
        /*
        'name',
        'is_published',
        'user_id',
        'date',
        'time',
        'text',
        'link',
        'length',
        'pattern',
        'categorys',
        */
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
            
            foreach ($allProperties as $property) { // TZID=America/New_York
                if (strpos($property, '=') === false) {
                    throw new \InvalidArgumentException('Failed to parse RFC string, invlaid property parameters: ' . $property);
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




    protected function wkst($property)
    {

            // WKST
        $rule = strtoupper($property);
        if (!array_key_exists($property, self::$week_days)) {
            throw new \InvalidArgumentException(
                'The WKST rule part must be one of the following: '
                . implode(', ', array_keys(self::$week_days))
            );
        }
        $this->wkst = self::$week_days[$property];
    }




    protected function freq($property)
    {        
        // FREQ
        if (is_integer($property)) {
            if ($property > self::SECONDLY || $property < self::YEARLY) {
                throw new \InvalidArgumentException(
                    'The FREQ rule part must be one of the following: '
                    . implode(', ', array_keys(self::$frequencies))
                );
            }
            $this->freq = $property;
        } else {
            // string
            $property = strtoupper($property);
            if (!array_key_exists($property, self::$frequencies)) {
                throw new \InvalidArgumentException(
                    'The FREQ rule part must be one of the following: '
                    . implode(', ', array_keys(self::$frequencies))
                );
            }
            $this->freq = self::$frequencies[$property];
        }
    }




    protected function interval($property)
    {     
        // INTERVAL
        $property = (int) $property;
        if ($property < 1) {
            throw new \InvalidArgumentException(
                'The INTERVAL rule part must be a positive integer (> 0)'
            );
        }
        $this->interval = (int) $property;
    
    }




    protected function dstart($property = null)
    {     
        // DTSTART
        if (not_empty($property)) {
            try {
                $this->dtstart = self::parseDate($property);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(
                    'Failed to parse DTSTART ; it must be a valid date, timestamp or \DateTime object'
                );
            }
        } else {
            $this->dtstart = new \DateTime();
        }
    
    }




    protected function until($property)
    {     
        // UNTIL (optional)
        if (not_empty($property)) {
            try {
                $this->until = self::parseDate($property);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(
                    'Failed to parse UNTIL ; it must be a valid date, timestamp or \DateTime object'
                );
            }
        }
    
    }




    protected function count($property)
    {     
        // COUNT (optional)
        if (not_empty($property)) {
            $property = (int) $property;
            if ($property < 1) {
                throw new \InvalidArgumentException('COUNT must be a positive integer (> 0)');
            }
            $this->count = $property;
        }
        if ($this->until && $this->count) {
            throw new \InvalidArgumentException('The UNTIL or COUNT rule parts MUST NOT occur in the same rule');
        }
        // infer necessary BYXXX rules from DTSTART, if not provided
        if (!(not_empty($this->parts['BYWEEKNO']) || not_empty($this->parts['BYYEARDAY']) || not_empty($this->parts['BYMONTHDAY']) || not_empty($this->parts['BYDAY']))) {
            switch ($this->freq) {
                case self::YEARLY:
                    if (!not_empty($this->parts['BYMONTH'])) {
                        $this->parts['BYMONTH'] = [(int) $this->dtstart->format('m')];
                    }
                    $this->parts['BYMONTHDAY'] = [(int) $this->dtstart->format('j')];
                    break;
                case self::MONTHLY:
                    $this->parts['BYMONTHDAY'] = [(int) $this->dtstart->format('j')];
                    break;
                case self::WEEKLY:
                    $this->parts['BYDAY'] = [array_search($this->dtstart->format('N'), self::$week_days)];
                    break;
            }
        }
    
    }




    protected function byday($property)
    {     
        // BYDAY (translated to byweekday for convenience)
        if (not_empty($property)) {
            if (!is_array($property)) {
                $property = explode(',', $property);
            }
            $this->byweekday = array();
            $this->byweekday_nth = array();
            foreach ($property as $value) {
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




    protected function bymonthday($property)
    {     
        // The BYMONTHDAY rule part specifies a COMMA-separated list of days
        // of the month.  Valid values are 1 to 31 or -31 to -1.  For
        // example, -10 represents the tenth to the last day of the month.
        // The BYMONTHDAY rule part MUST NOT be specified when the FREQ rule
        // part is set to WEEKLY.
        if (not_empty($property)) {
            if ($this->freq === self::WEEKLY) {
                throw new \InvalidArgumentException('The BYMONTHDAY rule part MUST NOT be specified when the FREQ rule part is set to WEEKLY.');
            }
            if (!is_array($property)) {
                $property = explode(',', $property);
            }
            $this->bymonthday = array();
            $this->bymonthday_negative = array();
            foreach ($property as $value) {
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




    protected function byyearday($property)
    {    

        if (not_empty($property)) {
            if ($this->freq === self::DAILY || $this->freq === self::WEEKLY || $this->freq === self::MONTHLY) {
                throw new \InvalidArgumentException('The BYYEARDAY rule part MUST NOT be specified when the FREQ rule part is set to DAILY, WEEKLY, or MONTHLY.');
            }
            if (!is_array($property)) {
                $property = explode(',', $property);
            }
            $this->bysetpos = array();
            foreach ($property as $value) {
                $value = (int) $value;
                if (!$value || $value < -366 || $value > 366) {
                    throw new \InvalidArgumentException('Invalid BYSETPOS value: ' . $value . ' (valid values are 1 to 366 or -366 to -1)');
                }
                $this->byyearday[] = $value;
            }
        }
    
    }




    protected function byweekno($property)
    {     
        // BYWEEKNO
        if (not_empty($property)) {
            if ($this->freq !== self::YEARLY) {
                throw new \InvalidArgumentException('The BYWEEKNO rule part MUST NOT be used when the FREQ rule part is set to anything other than YEARLY.');
            }
            if (!is_array($property)) {
                $property = explode(',', $property);
            }
            $this->byweekno = array();
            foreach ($property as $value) {
                $value = (int) $value;
                if (!$value || $value < -53 || $value > 53) {
                    throw new \InvalidArgumentException('Invalid BYWEEKNO value: ' . $value . ' (valid values are 1 to 53 or -53 to -1)');
                }
                $this->byweekno[] = $value;
            }
        }
    
    }




    protected function bymonth($property)
    {     
        // The BYMONTH rule part specifies a COMMA-separated list of months
        // of the year.  Valid values are 1 to 12.
        if (not_empty($property)) {
            if (!is_array($property)) {
                $property = explode(',', $property);
            }
            $this->bymonth = array();
            foreach ($property as $value) {
                $value = (int) $value;
                if ($value < 1 || $value > 12) {
                    throw new \InvalidArgumentException('Invalid BYMONTH value: ' . $value);
                }
                $this->bymonth[] = $value;
            }
        }

    
    }




    protected function bysetpos($property)
    {     
        if (not_empty($property)) {
            if (!(not_empty($this->parts['BYWEEKNO']) || not_empty($this->parts['BYYEARDAY'])
                || not_empty($this->parts['BYMONTHDAY']) || not_empty($this->parts['BYDAY'])
                || not_empty($this->parts['BYMONTH']) || not_empty($this->parts['BYHOUR'])
                || not_empty($this->parts['BYMINUTE']) || not_empty($this->parts['BYSECOND']))) {
                throw new \InvalidArgumentException('The BYSETPOS rule part MUST only be used in conjunction with another BYxxx rule part.');
            }
            if (!is_array($property)) {
                $property = explode(',', $property);
            }
            $this->bysetpos = array();
            foreach ($property as $value) {
                $value = (int) $value;
                if (!$value || $value < -366 || $value > 366) {
                    throw new \InvalidArgumentException('Invalid BYSETPOS value: ' . $value . ' (valid values are 1 to 366 or -366 to -1)');
                }
                $this->bysetpos[] = $value;
            }
        }
    }




    protected function byhour($property)
    {  


        if (not_empty($property)) {
            if (!is_array($property)) {
                $property = explode(',', $property);
            }
            $this->byhour = array();
            foreach ($property as $value) {
                $value = (int) $value;
                if ($value < 0 || $value > 23) {
                    throw new \InvalidArgumentException('Invalid BYHOUR value: ' . $value);
                }
                $this->byhour[] = $value;
            }
            sort($this->byhour);
        } elseif ($this->freq < self::HOURLY) {
            $this->byhour = array((int) $this->dtstart->format('G'));
        }
    }




    protected function byminute($property)
    {  


        if (not_empty($property)) {
            if (!is_array($property)) {
                $property$property = explode(',', $property);
            }
            $this->byminute = array();
            foreach ($property as $value) {
                $value = (int) $value;
                if ($value < 0 || $value > 59) {
                    throw new \InvalidArgumentException('Invalid BYMINUTE value: ' . $value);
                }
                $this->byminute[] = $value;
            }
            sort($this->byminute);
        } elseif ($this->freq < self::MINUTELY) {
            $this->byminute = array((int) $this->dtstart->format('i'));
        }
    }




    protected function bysecond($property)
    {  
        if (not_empty($property)) {
            if (!is_array($property)) {
                $property = explode(',', $property);
            }
            $this->bysecond = array();
            foreach ($property as $value) {
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
            $this->bysecond = array((int) $this->dtstart->format('s'));
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


    protected function crap()
    {
        EventModel::new();
        OccurrenceModel::new();
    }
    /**
     * Comment
     * @return string
     */
    public static function calcRecurrenceDates()
    {
        $patern = explode('|',$this->event->pattern)
//http://tools.ietf.org/html/rfc5545#section-3.8.5
        DAILY;COUNT=10 // Daily for 10 occurrences:
        DAILY;UNTIL=19971224T000000Z //Daily until December 24, 1997:
        DAILY;INTERVAL=2 // Every other day - forever:

        FREQ=DAILY;INTERVAL=10;COUNT=5 // Every 10 days, 5 occurrences:

       FREQ=YEARLY;UNTIL=20000131T140000Z;  //Every day in January, for 3 years:
        BYMONTH=1;BYDAY=SU,MO,TU,WE,TH,FR,SA
       or
       FREQ=DAILY;UNTIL=20000131T140000Z;BYMONTH=1

       ==> (1998 9:00 AM EST)January 1-31
           (1999 9:00 AM EST)January 1-31
           (2000 9:00 AM EST)January 1-31

      //Weekly for 10 occurrences:

       RRULE:FREQ=WEEKLY;COUNT=10

       ==> (1997 9:00 AM EDT) September 2,9,16,23,30;October 7,14,21
           (1997 9:00 AM EST) October 28;November 4


RRULE:FREQ=MONTHLY;BYMONTH=1,3
RRULE:FREQ=MONTHLY;BYMONTH=2
RRULE:FREQ=DAILY;UNTIL=20121011
RRULE:FREQ=DAILY;UNTIL=20121011T121314
RRULE:FREQ=DAILY;UNTIL=20121011T121314Z


  { "freq": "DAILY" },
  { "freq": "DAILY", "byday": ["MO", "TU", "WE", "TH", "FR"] },
  { "freq": "WEEKLY" },
  { "freq": "WEEKLY", "interval": 2 },
  { "freq": "MONTHLY" },
  { "freq": "YEARLY" },

  { "freq": "WEEKLY", "byday": "%" },

  { "freq": "MONTHLY", "bymonthday": "%" },
  { "freq": "MONTHLY", "byday": "%" },

  { "freq": "YEARLY", "bymonthday": "%", "bymonth": "%" },
  { "freq": "YEARLY", "byday": "%", "bymonth": "%" }

        return 'pages';
    }
};
