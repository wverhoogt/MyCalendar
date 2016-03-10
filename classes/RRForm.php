<?php namespace KurtJensen\MyCalendar\Classes;

use Lang;

class RRForm
{
    public function __construct()
    {
    }

    public function t($string)
    {
        return Lang::get('kurtjensen.mycalendar::lang.rrule.' . $string);
    }

    public function getTimezones()
    {
       $zones ['+00:00','+01:00','+02:00','+03:00','+04:00','+05:00','+05:30','+05:45','+06:00','+06:00','+07:00','+08:00','+09:00','+09:30','+10:00','+1100','+12:00','+12:00','+12:00','-02:00','-03:00','-03:00','-04:00','-04:30','-05:00','-06:00','-07:00','-08:00','-09:00','-10:00','11:00','-12:00'];
       foreach ($zones as $zone){
            $timezones[$zone] = $this->t('timezones.'.$zone);
       }
       return $timezones;
    }

    public function getFreqOptions()
    {
        $freqs = ['NONE','HOURLY','DAILY','WEEKDAYS','WEEKENDS','WEEKLY','MONTHLY','YEARLY'];
       foreach ($freqs as $freq){
            $freqOpts[$freq] = $this->t('freq.'.$freq);
       }
       return $freqOpts;
    }

    public function getWeeklyByDayOptions()
    {
        $bydays = ['SU','MO','TU','WE','TH','FR','SA'];
       foreach ($bydays as $day){
            $byDays[$day] = $this->t($day);
       }
       return $byDays;
    }

    public function getByDayOptions()
    {
        $bydays = ['SU','MO','TU','WE','TH','FR','SA','SU,MO,TU,WE,TH,FR,SA','MO,TU,WE,TH,FR','SU,SA'];
       foreach ($bydays as $day){
            $byDays[$day] = $this->t($day);
       }
       return $byDays;
    }

    public function getDayPosOptions()
    {
        $dayposs = ['1','2','3','4','-1','-2'];
       foreach ($dayposs as $pos){
            $day_pos[$pos] = $this->t('day-pos.'.$pos);
       }
       return $day_pos;
    }

    public function getMonthOptions()
    {
        foreach (range(1,12) as $monthNum){
            $months[$monthNum] = $this->t('month.'.$monthNum);
       }
       return $months;
    }

    public function getOccuranceOptions()
    {
        $range = range(1,100);
        return array_combine($range,$range);
    }

    public function showForm($f)
    {
return '
<!-- Timezone -->
    <div class="row">
        <div class="col-md-6 form-group">
            '.Form::label('timezone', $this->t('timezone')).'
            '.Form::select('timezone', $this->getTimezones(), $f->timezone,['class'=>'form-control custom-select'.$class]).'
        </div>
    </div>

    <div class="row form-inline ">
<!-- FREQ -->
        <div class="col-md-3 form-group">
            '.Form::label('FREQ', $this->t('repeat')).'
            '.Form::select('FREQ', $this->getFreqOptions(), $f->FREQ,['class'=>'form-control custom-select'.$class, 'onchange'=>'repeatChange()']).'
        </div>

        <div class="col-md-6 hidden r_all r_hourly r_daily r_weekly r_monthly r_yearly">
            '.Form::label('INTERVAL', $this->t('INTERVAL')).'
            '.Form::select('INTERVAL', range(1,365), $f->INTERVAL,['class'=>'r_all r_hourly r_daily r_weekly r_monthly r_yearly']).'
            <span class="inline-form-text'.($f->INTERVAL == 'HOURLY'?' hidden':'').' r_all r_hourly"><strong>hours(s)</strong></span>
            <span class="inline-form-text'.($f->INTERVAL == 'DAILY'?' hidden':'').' r_all r_daily"><strong>days(s)</strong></span>
            <span class="inline-form-text'.($f->INTERVAL == 'WEEKLY'?' hidden':'').' r_all r_weekly"><strong>week(s)</strong></span>
            <span class="inline-form-text'.($f->INTERVAL == 'MONTHLY'?' hidden':'').'  r_all r_monthly"><strong>month(s)</strong></span>
            <span class="inline-form-text'.($f->INTERVAL == 'YEARLY'?' hidden':'').' r_all r_yearly"><strong>year(s)</strong></span>
        </div>
    </div>

<!-- WBYDAY -->
    <div class="row hidden r_all r_weekly"><br>
        <div class="col-md-8 form-group">
            <label for="bymonthday">&nbsp;</label>
            <fieldset class="btn-group" data-toggle="buttons">'.
            function(){ $f = ''; 
                foreach ($this->getByDayOptions() as $day){
                    $f .='
                <label class="btn btn-default">'.
                Form::checkbox('WBYDAY['.$day.']', $day, in_array($day, $f->BYDAY),['class'=>'r_all r_weekly']).$this->t($day)).'</label>';

                };
                return $f;
            }
            .'
            </fieldset>
        </div>
    </div>


<!-- Month On -->
    <div class="row'.($f->INTERVAL == 'MONTHLY'?'':' hidden').' r_all r_monthly  form-inline "><br>
        <div class="col-md-8 form-group">
        <fieldset class="btn-group" data-toggle="buttons">
            '.Form::label('month_on', '').'
            '.Form::select('month_on', ['on_day'=>$this->t('on.on_day'),'on_the'=>$this->t('on.on_the')], $f->month_on,['class'=>'form-control custom-select r_all r_monthly']).'

<!-- MBYSETPOS -->
            '.Form::label('MBYSETPOS', '').'
            '.Form::select('MBYSETPOS', $this->getDayPosOptions(), $f->MBYSETPOS,['class'=>'form-control custom-select mo_all mo_on_the'.(($f->month_on == 'on_the')?'':' hidden')]).'


<!-- MBYDAY -->
           '.Form::label('MBYDAY', '').'
           '.Form::select('MBYDAY', $this->getByDayOptions(), $f->MBYDAY,['class'=>'form-control custom-select mo_all mo_on_the'.(($f->month_on == 'on_the')?'':' hidden')]).'
        </fieldset>
        </div>
    </div>


<!-- Year On -->
    <div class="row'.($f->INTERVAL == 'YEARLY'?'':' hidden').' r_all r_yearly  form-inline"><br>
        <div class="col-md-8 form-group">
        <fieldset class="btn-group" data-toggle="buttons">
            '.Form::label('year_on', '').'
            '.Form::select('year_on', ['on_day'=>$this->t('on.on_day'),'on_the'=>$this->t('on.on_the')], $f->year_on,['class'=>'form-control custom-select r_all r_yearly', 'onchange'=>'yearOnChange()']).'


<!-- YBYSETPOS -->
            '.Form::label('YBYSETPOS', '').'
            '.Form::select('YBYSETPOS', $this->getDayPosOptions(), $f->YBYSETPOS,['class'=>'form-control custom-select yr_all yr_on_the'.(($f->year_on == 'on_the')?'':' hidden')]).'


<!-- YBYDAY -->
            '.Form::label('YBYDAY', '').'
            '.Form::select('YBYDAY', $this->getByDayOptions(), $f->YBYDAY,['class'=>'form-control custom-select yr_all yr_on_the'.(($f->year_on == 'on_the')?'':' hidden')]).'
        </fieldset>
        </div>
    </div>

<!-- YBYMONTH -->
            '.Form::label('YBYMONTH', '').'
            '.Form::select('YBYMONTH', $this->getMonthOptions(), $f->YBYMONTH,['class'=>'form-control custom-select yr_all yr_on_the'.(($f->year_on == 'on_the')?'':' hidden')]).'
        </fieldset>
        </div>
    </div>
    <br>

<!-- Ends -->
    <div class="row'.($f->FREQ == 'NONE'?' hidden':'').' r_all r_hourly r_daily r_weekdays r_weekends r_weekly r_monthly r_yearly form-inline ">
        <div class="col-md-3 form-group">
            '.Form::label('Ends', 'Ends').'
            '.Form::select('Ends', ['NEVER'=>'Never','AFTER'=>'After','DATE' =>'On date'], $f->Ends,['class'=>'form-control custom-select  r_all r_hourly r_daily r_weekdays r_weekends r_weekly r_monthly r_yearly',  'onchange'=>'endsChange()']).'
        </div>


        <div class="col-md-2'.($f->FREQ == 'AFTER'?'':' hidden').' e_all e_after form-group ">
            '.Form::label('COUNT', '').'
            '.Form::select('COUNT', $this->getOccuranceOptions(), $f->COUNT,['class'=>'form-control custom-select e_all e_after'.(($f->year_on == 'on_the')?'':' hidden')]).'
            <span class="inline-form-text"><strong>occurrence(s)</strong></span>
        </div>

        <div class="col-md-4'.($f->FREQ == 'DATE'?'':' hidden').' e_all e_date form-group">
            <div
                id="DatePicker-form-ENDON"
                class="field-datepicker"
                data-control="datepicker"
                data-min-date="'. date('Y') .'-01-01 00:00:00"
                data-max-date="'. date('Y') +5.'-12-31 00:00:00">
                <div class="right-align input-group date">
                    <input
                        type="text"
                        id="DatePicker-form-input-ENDON"
                        name="ENDON"
                        value="'.$f->ENDON.'"
                        class="form-control align-right"
                        autocomplete="off"
                         />
                    <label for="DatePicker-form-input-ENDON" class="input-group-addon">
                        '.$this->t('ENDON').'<i class="icon icon-calendar"></i>
                    </label>
                </div>
            </div>
        </div>
    </div>
';
}
