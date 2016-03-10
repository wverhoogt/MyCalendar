<?php namespace KurtJensen\MyCalendar\Classes;

use Carbon\Carbon;
use \DateInterval;

class Rcur
{
    protected $occurrences = [];
    protected $paterns = [];
    protected $dtstart = null;
    protected $dtpointer = null;

    public function __construct($pattern, $dtstart)
    {
        $this->dtstart = new Carbon($dtstart);
        $this->paterns = explode('|', $pattern);
        $this->dtpointer = $this->dtstart->copy();

        foreach ($this->paterns as $patern) {
            $this->occurrences[] = $this->process($patern);
            $this->dtpointer = null;
        }
    }

    public function process($patern)
    {
        $interval = new DateInterval($patern);
        return $this->dtpointer->add($interval);
    }

    public function getOccurrences()
    {
        return $this->occurrences;
    }
}
