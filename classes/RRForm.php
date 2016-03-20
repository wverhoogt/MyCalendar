<?php namespace KurtJensen\MyCalendar\Classes;

use Form;
use Lang;
use Validator;

class RRForm
{
    public $messages;
    public $langPath;

    public function __construct($langPath = '')
    {
        $this->langPath = $langPath ? $langPath : 'kurtjensen.mycalendar::lang.rrule.';
    }

    public function t($string)
    {
        return Lang::get($this->langPath . $string);
    }

    public function getTimezones()
    {
        $zones = ['+00:00', '+01:00', '+02:00', '+03:00', '+04:00', '+05:00', '+05:30', '+05:45', '+06:00',
            '+07:00', '+08:00', '+09:00', '+09:30', '+10:00', '+11:00', '+12:00', '-02:00',
            '-03:00', '-04:00', '-04:30', '-05:00', '-06:00', '-07:00', '-08:00', '-09:00', '-10:00',
            '-11:00', '-12:00'];
        foreach ($zones as $zone) {
            $timezones[$zone] = $this->t('timezones.' . $zone);
        }
        return $timezones;
    }

    public function getFreqOptions()
    {
        $freqs = ['NONE', 'HOURLY', 'DAILY', 'WEEKDAYS', 'WEEKENDS', 'WEEKLY', 'MONTHLY', 'YEARLY', 'SERIES'];
        foreach ($freqs as $freq) {
            $freqOpts[$freq] = $this->t('freq.' . $freq);
        }
        return $freqOpts;
    }

    public function getByDayOptions()
    {
        $bydays = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA', 'SU,MO,TU,WE,TH,FR,SA', 'MO,TU,WE,TH,FR', 'SU,SA'];
        foreach ($bydays as $day) {
            $byDays[$day] = $this->t('ByDay.' . $day);
        }
        return $byDays;
    }

    public function getDayPosOptions()
    {
        $dayposs = ['1', '2', '3', '4', '-1', '-2'];
        foreach ($dayposs as $pos) {
            $day_pos[$pos] = $this->t('day-pos.' . $pos);
        }
        return $day_pos;
    }

    public function getMonthOptions()
    {
        foreach (range(1, 12) as $monthNum) {
            $months[$monthNum] = $this->t('month.' . $monthNum);
        }
        return $months;
    }

    public function getIntervalOptions()
    {
        $range = range(1, 360);
        return array_combine($range, $range);
    }

    public function getOccuranceOptions()
    {
        $range = range(1, 100);
        return array_combine($range, $range);
    }

    public function getWByDay($f)
    {
        $f = is_array($f) ? $f : [];
        $fields = '';
        $bydays = ['SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA'];
        foreach ($bydays as $day) {
            $fields .= '
            <label class="btn btn-default">' .
            Form::checkbox('WBYDAY[' . $day . ']', $day, in_array($day, $f), ['class' => 'r_all r_weekly']) . $this->t($day) . '</label>';

        };
        return $fields;
    }

    public function showForm($f)
    {
        /*
        return '
        <!-- Timezone -->
        <div class="row">
        <div class="col-md-6 form-group">
        ' . Form::label('timezone', $this->t('timezone')) . '
        ' . Form::select('timezone', $this->getTimezones(), array_get($f, 'timezone'), ['class' => 'form-control custom-select']) . '
        </div>
        </div>
         */

        return '
    <div class="row form-inline ">
<!-- FREQ -->
        <div class="col-md-3 form-group">
            ' . Form::label('FREQ', $this->t('repeat')) . '
            ' . Form::select('FREQ', $this->getFreqOptions(), array_get($f, 'FREQ'), ['class' => 'form-control custom-select']) . '
        </div>

        <div class="col-md-6' . (array_get($f, 'FREQ') == 'NONE' ? ' hidden' : '') . ' r_all r_hourly r_daily r_weekly r_monthly r_yearly">
            ' . Form::label('INTERVAL', $this->t('INTERVAL')) . '
            ' . Form::select('INTERVAL', $this->getIntervalOptions(), array_get($f, 'INTERVAL'), ['class' => 'form-control custom-select r_all r_hourly r_daily r_weekly r_monthly r_yearly']) . '
            <span class="inline-form-text' . (array_get($f, 'FREQ') == 'HOURLY' ? '' : ' hidden') . ' r_all r_hourly"><strong>' . $this->t('freq_units.HOURLY') . '</strong></span>
            <span class="inline-form-text' . (array_get($f, 'FREQ') == 'DAILY' ? '' : ' hidden') . ' r_all r_daily"><strong>' . $this->t('freq_units.DAILY') . '</strong></span>
            <span class="inline-form-text' . (array_get($f, 'FREQ') == 'WEEKLY' ? '' : ' hidden') . ' r_all r_weekly"><strong>' . $this->t('freq_units.WEEKLY') . '</strong></span>
            <span class="inline-form-text' . (array_get($f, 'FREQ') == 'MONTHLY' ? '' : ' hidden') . '  r_all r_monthly"><strong>' . $this->t('freq_units.MONTHLY') . '</strong></span>
            <span class="inline-form-text' . (array_get($f, 'FREQ') == 'YEARLY' ? '' : ' hidden') . ' r_all r_yearly"><strong>' . $this->t('freq_units.YEARLY') . '</strong></span>
        </div>
    </div>

<!-- WBYDAY -->
    <div class="row hidden r_all r_weekly"><br>
        <div class="col-md-8 form-group">
            <label for="bymonthday">' . $this->t('WBYDAY') . '</label>
            <fieldset class="btn-group" data-toggle="buttons">' .
        $this->getWByDay(array_get($f, 'WBYDAY'))
        . '
            </fieldset>
        </div>
    </div>

<!-- INTERVALS -->
    <div class="row hidden r_all r_series"><br>
        <div class="col-md-12 form-group">
            ' . Form::label('INTERVALS', $this->t('INTERVALS')) . '
            ' . Form::text('INTERVALS', array_get($f, 'INTERVALS')) . '
            <p>' . $this->t('INTERVALS_example') . '</p>
            </fieldset>
        </div>
    </div>


<!-- month_on -->
    <div class="row' . (array_get($f, 'FREQ') == 'MONTHLY' ? '' : ' hidden') . ' r_all r_monthly  form-inline "><br>
        <div class="col-md-8 form-group">
        <fieldset class="btn-group" data-toggle="buttons">
            ' . Form::label('month_on', '&nbsp;') . '
            ' . Form::select('month_on', ['on_day' => $this->t('on.on_day'), 'on_the' => $this->t('on.on_the')], array_get($f, 'month_on'), ['class' => 'form-control custom-select r_all r_monthly']) . '

<!-- MBYSETPOS -->
            ' . Form::label('MBYSETPOS', '&nbsp;') . '
            ' . Form::select('MBYSETPOS', $this->getDayPosOptions(), array_get($f, 'MBYSETPOS'), ['class' => 'form-control custom-select mo_all mo_on_the' . ((array_get($f, 'month_on') == 'on_the') ? '' : ' hidden')]) . '


<!-- MBYDAY -->
           ' . Form::label('MBYDAY', '&nbsp;') . '
           ' . Form::select('MBYDAY', $this->getByDayOptions(), array_get($f, 'MBYDAY'), ['class' => 'form-control custom-select mo_all mo_on_the' . ((array_get($f, 'month_on') == 'on_the') ? '' : ' hidden')]) . '
        </fieldset>
        </div>
    </div>


<!-- Year On -->
    <div class="row' . (array_get($f, 'INTERVAL') == 'YEARLY' ? '' : ' hidden') . ' r_all r_yearly  form-inline"><br>
        <div class="col-md-8 form-group">
        <fieldset class="btn-group" data-toggle="buttons">
            ' . Form::label('year_on', '&nbsp;') . '
            ' . Form::select('year_on', ['on_day' => $this->t('on.on_day'), 'on_the' => $this->t('on.on_the')], array_get($f, 'year_on'), ['class' => 'form-control custom-select r_all r_yearly']) . '


<!-- YBYSETPOS -->
            ' . Form::label('YBYSETPOS', '&nbsp;') . '
            ' . Form::select('YBYSETPOS', $this->getDayPosOptions(), array_get($f, 'YBYSETPOS'), ['class' => 'form-control custom-select yr_all yr_on_the' . ((array_get($f, 'year_on') == 'on_the') ? '' : ' hidden')]) . '


<!-- YBYDAY -->
            ' . Form::label('YBYDAY', '&nbsp;') . '
            ' . Form::select('YBYDAY', $this->getByDayOptions(), array_get($f, 'YBYDAY'), ['class' => 'form-control custom-select yr_all yr_on_the' . ((array_get($f, 'year_on') == 'on_the') ? '' : ' hidden')]) . '

<!-- YBYMONTH -->
            ' . Form::label('YBYMONTH', $this->t('YBYMONTH')) . '
            ' . Form::select('YBYMONTH', $this->getMonthOptions(), array_get($f, 'YBYMONTH'), ['class' => 'form-control custom-select yr_all yr_on_the' . ((array_get($f, 'year_on') == 'on_the') ? '' : ' hidden')]) . '
        </fieldset>
        </div>
    </div>
    <br>

<!-- Ends -->
    <div class="row' . (array_get($f, 'FREQ', 'NONE') == 'NONE' ? ' hidden' : '') . ' r_all r_hourly r_daily r_weekdays r_weekends r_weekly r_monthly r_yearly r_series form-inline">
        <div class="col-md-2 form-group">
            ' . Form::label('Ends', 'Ends') . '
            ' . Form::select('Ends', ['NEVER' => $this->t('Never'), 'AFTER' => $this->t('After'), 'DATE' => $this->t('On_date')], array_get($f, 'Ends'), ['class' => 'form-control custom-select  r_all r_hourly r_daily r_weekdays r_weekends r_weekly r_monthly r_yearly r_series']) . '
        </div>


        <div class="col-md-3' . (array_get($f, 'Ends') == 'AFTER' ? '' : ' hidden') . ' e_all e_after form-group ">
            ' . Form::label('COUNT', '&nbsp;') . '
            ' . Form::select('COUNT', $this->getOccuranceOptions(), array_get($f, 'COUNT'), ['class' => 'form-control custom-select']) . '
            <span class="inline-form-text"><strong>occurrence(s)</strong></span>
        </div>

        <div class="col-md-4' . (array_get($f, 'Ends') == 'DATE' ? '' : ' hidden') . ' e_all e_date form-group">
            <div
                id="DatePicker-form-ENDON"
                class="field-datepicker"
                data-control="datepicker"
                data-min-date="' . date('Y') . '-01-01 00:00:00"
                data-max-date="' . (date('Y') + 5) . '-12-31 00:00:00">
                <div class="right-align input-group date">
                    <input
                        type="text"
                        id="DatePicker-form-input-ENDON"
                        name="ENDON"
                        value="' . array_get($f, 'ENDON') . '"
                        class="form-control align-right"
                        autocomplete="off"
                         />
                    <label for="DatePicker-form-input-ENDON" class="input-group-addon">
                        ' . $this->t('ENDON') . ' <i class="icon icon-calendar"></i>
                    </label>
                </div>
            </div>
        </div>
    </div>
';
    }

    /**
     * Converts form values into RRULE for creating reccurrence and saving rule.
     * @return null
     */
    public function unParseRrule($data)
    {

        $freq = strtoupper(array_get($data, 'FREQ'));
        $rrule = 'FREQ=' . $freq . ';';
        switch ($freq) {
            case 'NONE':
                return 'FREQ=DAILY;INTERVAL=1;COUNT=1;';
                break;
            case 'HOURLY':
                $rrule .= 'INTERVAL=' . array_get($data, 'INTERVAL') . ';';     //RDATE=FREQ=HOURLY;INTERVAL=1;UNTIL=2016-03-16;
                break;
            case 'DAILY':
                $rrule .= 'INTERVAL=' . array_get($data, 'INTERVAL') . ';';     // RDATE=FREQ=DAILY;INTERVAL=1;COUNT=1;
                break;
            case 'WEEKDAYS':
                $rrule = 'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO,TU,WE,TH,FR;WKST=SU;';
                break;
            case 'WEEKENDS':
                $rrule = 'FREQ=WEEKLY;INTERVAL=1;BYDAY=SA,SU;WKST=SU;';
                break;
            case 'WEEKLY':
                $rrule .= 'INTERVAL=' . array_get($data, 'INTERVAL') . ';';
                if (count(array_get($data, 'BYDAY')) > 0) {
                    $BYDAY = implode(',', array_get($data, 'WBYDAY'));
                    $rrule .= 'BYDAY=' . $BYDAY . ';';     //FREQ=WEEKLY;BYDAY=SU,WE,TH;INTERVAL=3;COUNT=6
                }
                if (array_get($data, 'WBYDAY') && array_get($data, 'INTERVAL') > 1) {
                    $rrule .= 'WKST=SU;';
                }
                break;
            case 'MONTHLY':
                $rrule .= 'INTERVAL=' . array_get($data, 'INTERVAL') . ';';

                if (array_get($data, 'month_on') == 'on_day') {
                    list($y, $m, $d) = explode('-', array_get($data, 'date'));
                    $rrule .= 'BYMONTHDAY=' . $d . ';';

                } else {
                    $rrule .= 'BYSETPOS=' . array_get($data, 'MBYSETPOS') . ';BYDAY=' . array_get($data, 'MBYDAY') . ';';
                }
                break;
            case 'YEARLY':
                $rrule .= 'INTERVAL=' . array_get($data, 'INTERVAL') . ';';

                if (array_get($data, 'year_on') == 'on_day') {
                    list($y, $m, $d) = explode('-', array_get($data, 'date'));
                    $rrule .= 'BYMONTH=' . $m . ';BYMONTHDAY=' . $d . ';';

                } else {
                    $rrule .= 'BYMONTH=' . array_get($data, 'YBYMONTH') . ';BYSETPOS=' . array_get($data, 'YBYSETPOS') . ';BYDAY=' . array_get($data, 'YBYDAY') . ';';
                }
                if (array_get($data, 'BYWEEKNO')) {
                    $rrule .= 'WKST=SU;';
                }

                break;
            case 'SERIES':
                $rrule .= 'INTERVALS=' . array_get($data, 'INTERVALS') . ';';
                break;
        }

        $ends = strtoupper(array_get($data, 'Ends'));
        if ($ends == 'AFTER') {
            $rrule .= 'COUNT=' . array_get($data, 'COUNT') . ';';
        } elseif ($ends == 'DATE') {
            $rrule .= 'UNTIL=' . array_get($data, 'ENDON') . ';'; // UNTIL=20000131T090000Z;

        } else {
            $rrule .= 'COUNT=10;';
        }
        return $rrule;
    }

    /**
     * Converts RRULE into form values needed for setting the form to the
     * state it was in when event was created.
     * @param  string $rrule the reccurrence rule string
     * @return array $formVals Form values keyed by input name
     */
    public function parseRrule($rrule)
    {
        if (!$rrule) {
            return [];
        }

        $rparts = explode(';', trim($rrule, ';'));
        $FREQ =
        $INTERVAL =
        $BYDAY =
        $BYSETPOS =
        $BYMONTH =
        $UNTIL =
        $COUNT = null;

        foreach ($rparts as $part) {
            list($prop, $val) = explode('=', $part);
            $$prop = $val;
        }
        switch ($FREQ) {
            case 'None':
                return ['FREQ' => 'None'];
                break;
            case 'HOURLY':
                $formVals = ['FREQ' => 'HOURLY', 'INTERVAL' => $INTERVAL];
                break;
            case 'DAILY':
                $formVals = ['FREQ' => 'DAILY', 'INTERVAL' => $INTERVAL];
                break;
            case 'WEEKLY':
                $formVals = ['FREQ' => 'WEEKLY', 'INTERVAL' => $INTERVAL];
                if (isset($BYDAY)) {
                    $BYDAYs = explode(',', $BYDAY);
                    $formVals['WBYDAY'] = $BYDAYs;
                }
                break;
            case 'MONTHLY':
                $formVals = ['FREQ' => 'MONTHLY', 'INTERVAL' => $INTERVAL];
                if (isset($BYSETPOS)) {
                    $formVals['MBYDAY'] = $BYDAY;
                    $formVals['MBYSETPOS'] = $BYSETPOS;
                    $formVals['month_on'] = 'on_the';
                } else {
                    $formVals['month_on'] = 'on_day';
                }
                break;
            case 'YEARLY':
                $formVals = ['FREQ' => 'YEARLY', 'INTERVAL' => $INTERVAL];
                if (isset($BYSETPOS)) {
                    $formVals['YBYDAY'] = $BYDAY;
                    $formVals['YBYSETPOS'] = $BYSETPOS;
                    $formVals['YBYMONTH'] = $BYMONTH;
                    $formVals['year_on'] = 'on_the';
                } else {
                    $formVals['year_on'] = 'on_day';
                }
                break;
            case 'SERIES':
                $formVals = ['FREQ' => 'SERIES', 'INTERVALS' => $INTERVALS];
                break;
        }

        //die(print_r($formVals));
        if (isset($UNTIL)) {
            return array_merge(['Ends' => 'DATE', 'ENDON' => $UNTIL], $formVals);
        } else {
            return array_merge(['Ends' => 'AFTER', 'COUNT' => $COUNT], $formVals);
        }

    }

    public function valiDate($formValues)
    {
        $v = [];
        $freq = strtoupper(array_get($formValues, 'FREQ'));
        switch ($freq) {
            case 'None':
                break;
            case 'WEEKDAYS':
                break;
            case 'WEEKENDS':
                break;
            case 'HOURLY':$v['INTERVAL'] = 'required|integer|min:1';
                break;
            case 'DAILY':$v['INTERVAL'] = 'required|integer|min:1';
                break;
            case 'WEEKLY':
                $formValues['BYDAY'] = array_get($formValues, 'WBYDAY', '');
                $v['INTERVAL'] = 'required|integer|min:1';
                $v['WBYDAY'] = 'required|array';
                break;
            case 'MONTHLY':
                $v['INTERVAL'] = 'required|integer|min:1';
                if (!array_get($formValues, 'month_on') == 'on_day') {
                    $v['BYSETPOS'] = 'required|max:5|min:-5';
                    $v['BYDAY'] = 'required|array';
                }
                break;
            case 'YEARLY':
                $v['INTERVAL'] = 'required|integer|min:1';
                if (array_get($formValues, 'year_on', '') == 'on_the') {
                    $formValues['BYMONTH'] = array_get($formValues, 'YBYMONTH', '');
                    $formValues['BYDAY'] = explode(',', array_get($formValues, 'YBYDAY', ''));
                    $formValues['BYSETPOS'] = explode(',', array_get($formValues, 'YBYSETPOS', ''));

                    $v['YBYMONTH'] = 'required';
                    $v['YBYDAY'] = 'required';
                    $v['YBYSETPOS'] = 'required';
                }
                break;
            case 'SERIES':
                $v['INTERVALS'] = 'required|min:3';
                break;
        }
        if (isset($v['INTERVAL']) || isset($v['INTERVALS'])) {
            $ends = strtoupper(array_get($formValues, 'Ends', ''));
            if ($ends == 'AFTER') {
                $v['COUNT'] = 'required|max:100|min:1';
            } elseif ($ends == 'DATE') {
                $v['ENDON'] = 'required|date_format:"Y-m-d"';
            }
        }

        $validations = array_merge([
            'name' => 'required|min:3',
            'is_published' => 'boolean',
            'date' => 'required',
            'time' => 'required',
            'text' => 'required',
            // 'link' => 'required',
            'length' => 'required',
            // 'pattern' => 'required',
            // 'categorys' => 'required',
        ], $v);

        $this->formValues = $formValues;

        $validator = Validator::make($formValues, $validations, Lang::get('kurtjensen.mycalendar::validation'));

        if ($validator->fails()) {
            $this->messages = $validator->messages();
            return false;
        }
        return true;

    }
}
