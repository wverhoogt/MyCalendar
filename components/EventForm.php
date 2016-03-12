<?php namespace KurtJensen\MyCalendar\Components;

use Auth;
use Cms\Classes\ComponentBase;
use KurtJensen\MyCalendar\Classes\RRForm;
use KurtJensen\MyCalendar\Models\Category as MyCalCategory;
use KurtJensen\MyCalendar\Models\Event as MyCalEvent;
use Lang;
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
    private $rdate;
    public $RRForm;
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
        if ($this->ckeditor) {
            $this->addJs('//cdn.ckeditor.com/4.5.4/standard/ckeditor.js');
        }
        $this->addJs('/plugins/kurtjensen/mycalendar/assets/js/scheduler.js');
    }

    public function onRun()
    {
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/css/datepicker.css');
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/css/datepicker.css');
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/vendor/pikaday/css/pikaday.css');
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/vendor/clockpicker/css/jquery-clockpicker.css');
        $this->addCss('/plugins/kurtjensen/mycalendar/assets/css/cal-form.css');
        $this->addJs('/modules/backend/formwidgets/datepicker/assets/js/build-min.js');
/*
if ($this->ckeditor) {
$this->addJs('//cdn.ckeditor.com/4.5.4/standard/ckeditor.js');
}
$this->addJs('/plugins/kurtjensen/mycalendar/assets/js/scheduler.js');
 */$this->myevents = $this->page['myevents'] = $this->loadEvents();
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

        $this->RRForm = new RRForm();
        $this->formValues = $this->page['formVals'] = array_merge($this->RRForm->parseRrule($this->myevent->pattern), $this->myevent->toArray());
        $this->page['rcurForm'] = $this->RRForm->showForm($this->formValues);
    }

    /**
     * Update the myeventone
     */
    public function onUpdateEvent()
    {

        $dates = $this->processPost();
        if (!$dates) {
            die('fuck');
            return ['#ajaxResponse' => $this->renderPartial('@ajaxResponse', $this->ajaxResponse)];
        }
        $this->myevent->save();

        $this->onRun();
        return;

        if (!$myevent = $this->getMyEvent()) {
            return null;
        }
        $this->RRForm = new RRForm();
        if ($this->RRForm->unParseRrule(post()));

        $myevent->name = post('name');
        $myevent->text = post('text');
        $myevent->date = post('date');
        $myevent->time = post('time');
        $myevent->pattern = unParseRrule(post());
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
        $dates = $this->processPost();
        if (!$dates) {
            return ['#ajaxResponse' => $this->renderPartial('@ajaxResponse', $this->ajaxResponse)];
        }
        $this->myevent->save();

    }

    public function onPreviewRrule()
    {
        $dates = $this->processPost();
        if (!$dates) {
            return ['#ajaxResponse' => $this->renderPartial('@ajaxResponse', $this->ajaxResponse)];
        }

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

        return ['#ajaxResponse' => $this->renderPartial('@details', ['ev' => $this->myevent])];

    }

    public function processPost()
    {

        $this->RRForm = new RRForm();
        if (!$this->RRForm->valiDate(post())) {
            // Sets a warning message

            $this->ajaxResponse = array_merge($this->ajaxResponse, [
                'context' => 'danger',
                'title' => 'Form Validation Error:',
                'content' => '<ul><li>' . implode('</li><li>', $this->RRForm->messages->all()) . '</li></ul>',
                'footer' => '',
            ]);
            return false;
        }

        $pattern = $this->RRForm->unParseRrule(post());

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
}
