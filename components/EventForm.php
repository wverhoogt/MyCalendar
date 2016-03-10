<?php namespace KurtJensen\MyCalendar\Components;

use Auth;
use Cms\Classes\ComponentBase;
use KurtJensen\MyCalendar\Classes\RRForm;
use KurtJensen\MyCalendar\Models\Category as MyCalCategory;
use KurtJensen\MyCalendar\Models\Event as MyCalEvent;
use Lang;
use Validator;
use \Recurr\Rule;

class EventForm extends ComponentBase
{
    public $myevents;
    public $myevent;
    public $categorylist;
    public $user;
    public $allowpublish;
    public $ckeditor;
    public $is_copy;
    public $rdate;
    public $formValues = [];
    public $ajaxResponse = [
        'context' => 'default',
        'title' => '',
        'content' => '',
        'footer' => '',
    ];

    public function componentDetails()
    {
        return [
            'name' => 'kurtjensen.mycalendar::lang.event_form.name',
            'description' => 'kurtjensen.mycalendar::lang.event_form.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'allowpublish' => [
                'title' => 'kurtjensen.mycalendar::lang.event_form.allow_pub_title',
                'description' => 'kurtjensen.mycalendar::lang.event_form.allow_pub_description',
                'type' => 'dropdown',
                'default' => '1',
                'options' => [
                    0 => 'kurtjensen.mycalendar::lang.event_form.opt_no',
                    1 => 'kurtjensen.mycalendar::lang.event_form.opt_yes',
                ],
            ],
            'ckeditor' => [
                'title' => 'kurtjensen.mycalendar::lang.event_form.ckeditor_title',
                'description' => 'kurtjensen.mycalendar::lang.event_form.ckeditor_description',
                'type' => 'dropdown',
                'default' => '1',
                'options' => [
                    0 => 'kurtjensen.mycalendar::lang.event_form.opt_no',
                    1 => 'kurtjensen.mycalendar::lang.event_form.opt_yes',
                ],
            ],
        ];
    }

    public function init()
    {
        $this->user = Auth::getUser();

        if (!$this->user) {
            return null;
        }
        $this->allowpublish = $this->property('allowpublish');
        $this->ckeditor = $this->property('ckeditor');
    }

    public function onRun()
    {
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/css/datepicker.css');
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/css/datepicker.css');
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/vendor/pikaday/css/pikaday.css');
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/vendor/clockpicker/css/jquery-clockpicker.css');
        $this->addCss('/plugins/kurtjensen/mycalendar/assets/css/cal-form.css');
        $this->addJs('/modules/backend/formwidgets/datepicker/assets/js/build-min.js');

        if ($this->ckeditor) {
            $this->addJs('//cdn.ckeditor.com/4.5.4/standard/ckeditor.js');
        }
        $this->addJs('/plugins/kurtjensen/mycalendar/assets/js/scheduler.js');
        $this->myevents = $this->page['myevents'] = $this->loadEvents();
        $this->formValues = $this->page['formValues'] = $this->parseRrule('');
        $RRForm = new RRForm();
        $this->page['formValues'] = $RRForm->showForm([]);
    }

    public function trans($string)
    {
        return Lang::get('kurtjensen.mycalendar::lang.' . $string);
    }

    protected function loadEvents()
    {
        if (!$this->user->id) {
            return null;
        }

        $myevents = MyCalEvent::where('user_id', '=', $this->user->id)->
        orderBy('year')->
        orderBy('month')->
        orderBy('day')->
        orderBy('time')->
        get();
        return $myevents;
    }

    protected function getMyEvent()
    {
        if (!$this->user->id) {
            return null;
        }

        $eventId = post('id');

        if (!$eventId) {
            $myevent = new MyCalEvent();
            $myevent->user_id = $this->user->id;
        } else {
            $myevent = MyCalEvent::where('user_id', '=', $this->user->id)->find($eventId);
        }

        return $myevent;
    }

    protected function onEventForm()
    {

        $this->addJs('/plugins/kurtjensen/mycalendar/assets/js/scheduler.js');
        if (!$myevent = $this->getMyEvent()) {
            return null;
        }
        $this->is_copy = $this->page['is_copy'] = post('copy');

        $cat = isset($myevent->categorys->first()->id) ? $myevent->categorys->first()->id : 0;
        $this->categorylist = $this->page['categorylist'] = MyCalCategory::selector(
            $cat,
            array('class' => 'form-control custom-select',
                'id' => 'Form-field-myevent-category_id')
        );

        $this->myevent = $this->page['myevent'] = $myevent;

        //$this->page['datefield'] = Form::date('name');
    }

    /**
     * Update the myeventone
     */
    public function onUpdateEvent()
    {
        if (!$myevent = $this->getMyEvent()) {
            return null;
        }

        $myevent->name = post('name');
        $myevent->text = post('text');
        $myevent->date = post('date');
        $myevent->time = post('time');
        $myevent->categorys = [post('category_id')];
        if ($this->allowpublish) {
            $myevent->is_published = post('is_published');
        }
        $myevent->save();

        $this->onRun();
    }

    protected function onDelete()
    {
        $eventId = post('id');

        if (!($eventId && $this->user)) {
            return null;
        }

        $myevent = MyCalEvent::where('user_id', $this->user->id)
            ->find($eventId);

        $myevent->delete();

        $this->onRun();
    }

    public function onProcess()
    {
        if (
            $this->valiDate()) {
            $this->processPost();
            return ['#answer' => $this->rdate];
        }
        return ['#answer' => 'Error'];
    }

    public function onPreviewRrule()
    {
        if (!$this->valiDate()) {
            return ['#ajaxResponse' => $this->renderPartial('@ajaxResponse', $this->ajaxResponse)];
        }

        $dates = $this->processPost();

        $myevent = ['Event Data<hr>',
            'NAME' => $this->myevent->name,
            'IS_PUBLISHED = ' . $this->myevent->is_published,
            'USER_ID = ' . post('user_id'),
            'DATE = ' . $this->myevent->date,
            'TIME = ' . $this->myevent->time,
            'TEXT = ' . $this->myevent->text,
            'LINK = ' . $this->myevent->link,
            'LENGTH = ' . $this->myevent->length,
            'PATTERN = ' . $this->myevent->pattern,
            'CATEGORYS = ' . $this->myevent->categorys,
        ];

        $occurrences = [];
        foreach ($dates as $occurrence) {
            $occurrences[] = $occurrence->getStart()->format('Y-m-d H:i:s') . ' --- ' . $occurrence->getEnd()->format('Y-m-d H:i:s');
        }

        $this->ajaxResponse = array_merge($this->ajaxResponse, [
            'context' => 'default',
            'title' => 'Event Preview:',
            'content' => implode('<br>', $myevent) . '<ul><li>' . implode('</li><li>', $occurrences) . '</li></ul>',
            'footer' => '',
        ]);

        return ['#ajaxResponse' => $this->renderPartial('@ajaxResponse', $this->ajaxResponse)];

    }

    public function processPost()
    {

        $pattern = $this->unParseRrule();

        $myevent = $this->getMyEvent();

        $myevent->name = post('name');
        $myevent->text = post('text');
        $myevent->date = post('date');
        $myevent->time = post('time');
        $myevent->length = post('length');
        $myevent->pattern = $pattern;
        $myevent->categorys = [post('category_id')];
        if ($this->allowpublish) {
            $myevent->is_published = post('is_published');
        }
        // Copy into class variable
        $this->myevent = $myevent;

        $start_at = $myevent->carbon_time; //new Carbon(post('date') . ' ' . post('time'));

        list($lengthHour, $lengthMinute) = explode(':', $myevent->length);

        $end_at = $start_at->copy();
        $end_at->addMinutes($lengthMinute)->addHours($lengthHour);

        $rules = new \Recurr\Rule($pattern, $start_at, $end_at);
        $transformer = new \Recurr\Transformer\ArrayTransformer;
        $dates = $transformer->transform($rules);

        return $dates;

    }

    /**
     * Converts form values into RRULE for creating reccurrence and saving rule.
     * @return null
     */
    public function unParseRrule()
    {

        $freq = strtoupper(post('FREQ'));
        $rrule = 'FREQ=' . $freq . ';';
        switch ($freq) {
            case 'None':
                return 'FREQ=DAILY;INTERVAL=1;COUNT=1;';
                break;
            case 'HOURLY':
                $rrule .= 'INTERVAL=' . post('INTERVAL') . ';';     //RDATE=FREQ=HOURLY;INTERVAL=1;UNTIL=2016-03-16;
                break;
            case 'DAILY':
                $rrule .= 'INTERVAL=' . post('INTERVAL') . ';';     // RDATE=FREQ=DAILY;INTERVAL=1;COUNT=1;
                break;
            case 'WEEKDAYS':
                $rrule = 'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO,TU,WE,TH,FR;WKST=SU;';
                break;
            case 'WEEKENDS':
                $rrule = 'FREQ=WEEKLY;INTERVAL=1;BYDAY=SA,SU;WKST=SU;';
                break;
            case 'WEEKLY':
                $rrule .= 'INTERVAL=' . post('INTERVAL') . ';';
                if (count(post('BYDAY')) > 0) {
                    $BYDAY = implode(',', post('WBYDAY'));
                    $rrule .= 'BYDAY=' . $BYDAY . ';';     //FREQ=WEEKLY;BYDAY=SU,WE,TH;INTERVAL=3;COUNT=6
                }
                if (post('WBYDAY') && post('INTERVAL') > 1) {
                    $rrule .= 'WKST=SU;';
                }
                break;
            case 'MONTHLY':
                $rrule .= 'INTERVAL=' . post('INTERVAL') . ';';

                if (post('month_on') == 'on_day') {
                    list($y, $m, $d) = explode('-', post('date'));
                    $rrule .= 'BYMONTHDAY=' . $d . ';';

                } else {
                    $rrule .= 'BYSETPOS=' . post('MBYSETPOS') . ';BYDAY=' . post('MBYDAY') . ';';
                }
                break;
            case 'YEARLY':
                $rrule .= 'INTERVAL=' . post('INTERVAL') . ';';

                if (post('year_on') == 'on_day') {
                    list($y, $m, $d) = explode('-', post('date'));
                    $rrule .= 'BYMONTH=' . $m . ';BYMONTHDAY=' . $d . ';';

                } else {
                    $rrule .= 'BYMONTH=' . post('YBYMONTH') . ';BYSETPOS=' . post('YBYSETPOS') . ';BYDAY=' . post('YBYDAY') . ';';
                }
                if (post('BYWEEKNO')) {
                    $rrule .= 'WKST=SU;';
                }

                break;
        }

        $ends = strtoupper(post('Ends'));
        if ($ends == 'AFTER') {
            $rrule .= 'COUNT=' . post('COUNT') . ';';
        } elseif ($ends == 'DATE') {
            $rrule .= 'UNTIL=' . post('ENDON') . ';'; // UNTIL=20000131T090000Z;

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
        $rrule = 'FREQ=MONTHLY;INTERVAL=1;BYSETPOS=2;BYDAY=MO,TU,WE,TH,FR;COUNT=3;';
        $rparts = explode(';', trim($rrule, ';'));

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
        }

        //die(print_r($formVals));
        if (isset($UNTIL)) {
            return array_merge(['Ends' => 'DATE', 'ENDON' => $UNTIL], $formVals);
        } else {
            return array_merge(['Ends' => 'AFTER', 'COUNT' => $COUNT], $formVals);
        }

    }

    public function valiDate()
    {

        $formValues = post();
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
                $v['INTERVAL'] = ['required', 'integer', 'min:1'];
                $v['BYDAY'] = 'required|array';     // 'required|array|each:in:"MO","TU","WE","TH","FR","SA","SU"';
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
                if (!array_get($formValues, 'year_on') == 'on_day') {
                    $formValues['BYMONTH'] = array_get($formValues, 'YBYMONTH', '');
                    $formValues['BYDAY'] = explode(',', array_get($formValues, 'YBYDAY', ''));
                    unset($formValues['YBYMONTH'], $formValues['YBYDAY']);

                    $v['YBYMONTH'] = 'required|max:12|min:1';
                    $v['BYSETPOS'] = 'required|max:5|min:-5';

                    $v['BYDAY'] = 'required|array';
                }
                break;
        }
        if (isset($v['INTERVAL'])) {
            $ends = strtoupper(array_get($formValues, 'Ends', ''));
            if ($ends == 'AFTER') {
                $v['COUNT'] = 'required|max:100|min:1';
            } elseif ($ends == 'DATE') {
                $v['ENDON'] = 'required|date_format:"Y-m-d"';
            }
        }
        $validations = array_merge([
            'name' => 'required',
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

        $validator = Validator::make($formValues,
            $validations
        );

        if ($validator->fails()) {

            $messages = $validator->messages();

            // Sets a warning message

            $this->ajaxResponse = array_merge($this->ajaxResponse, [
                'context' => 'danger',
                'title' => 'Form Validation Error:',
                'content' => '<ul><li>' . implode('</li><li>', $messages->all()) . '</li></ul>',
                'footer' => '',
            ]);

            return false;
        }
        return true;

    }
}
