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
    }

    public function onRun()
    {
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/css/datepicker.css');
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/vendor/pikaday/css/pikaday.css');
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/vendor/clockpicker/css/jquery-clockpicker.css');
        $this->addCss('/plugins/kurtjensen/mycalendar/assets/css/cal-form.css');
        $this->addJs('/modules/backend/formwidgets/datepicker/assets/js/build-min.js');
        if ($this->ckeditor) {
            $this->addJs('//cdn.ckeditor.com/4.5.4/standard/ckeditor.js');
        }

        $this->page['myevents'] = $this->loadEvents();
        //$this->onEventForm();
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

        $this->myevents = MyCalEvent::where('user_id', '=', $this->user->id)->
        orderBy('date')->
        get();
        return $this->myevents;
    }

    protected function getMyEvent()
    {
        if (!$this->user->id) {
            return null;
        }

        $eventId = post('id');

        if (!$eventId) {
            $this->myevent = new MyCalEvent();
            $this->myevent->user_id = $this->user->id;
        } else {
            $this->myevent = MyCalEvent::where('user_id', '=', $this->user->id)->find($eventId);
        }

        return $this->myevent;
    }

    protected function onEventForm()
    {
        $this->addJs('/plugins/kurtjensen/mycalendar/assets/js/scheduler.js');
        if (!$this->getMyEvent()) {
            return null;
        }

        $this->is_copy = $this->page['is_copy'] = post('copy');

        $cat = isset($this->myevent->categorys->first()->id) ? $this->myevent->categorys->first()->id : 0;
        $this->categorylist = $this->page['categorylist'] = MyCalCategory::selector(
            $cat,
            array('class' => 'form-control custom-select',
                'id' => 'Form-field-myevent-category_id')
        );

        $this->myevent = $this->page['myevent'] = $this->myevent;

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
            return ['#ajaxResponse' => $this->renderPartial('@ajaxResponse', $this->ajaxResponse)];
        }
        $this->myevent->save();

        $this->onRun();
    }

    protected function onDelete()
    {
        $eventId = post('id');

        if (!($eventId && $this->user)) {
            return null;
        }

        $this->myevent = MyCalEvent::where('user_id', $this->user->id)
            ->find($eventId);

        $this->myevent->delete();

        $this->onRun();
    }

    public function onProcess()
    {
        $dates = $this->processPost();
        if (!$dates) {
            return ['#ajaxResponse' => $this->renderPartial('@ajaxResponse', $this->ajaxResponse)];
        }
        $this->myevent->save();
        $this->onRun();
    }

    public function onPreviewRrule()
    {
        $dates = $this->processPost();
        if (!$dates) {
            return ['#EventDetail' => $this->renderPartial('@ajaxResponse', $this->ajaxResponse)];
        }

        $occurrences = [];
        foreach ($dates as $occurrence) {
            $occurrences[] = $occurrence->getStart()->format('M d Y H:i') . ' - ' . $occurrence->getEnd()->format('H:i');
        }

        return ['#EventDetail' => $this->renderPartial('@details', ['ev' => $this->myevent, 'occs' => $occurrences, 'context' => 'success']), '#ajaxResponse' => ''];

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

        $this->getMyEvent();

        $this->myevent->name = post('name');
        $this->myevent->text = post('text');
        $this->myevent->date = post('date');
        $this->myevent->time = post('time');
        $this->myevent->length = post('length');
        $this->myevent->pattern = $pattern;
        $this->myevent->categorys = [post('category_id')];
        if ($this->allowpublish) {
            $this->myevent->is_published = post('is_published');
        }

        $start_at = $this->myevent->carbon_time;

        list($lengthHour, $lengthMinute) = explode(':', $this->myevent->length);

        $end_at = $start_at->copy();
        $end_at->addMinutes($lengthMinute)->addHours($lengthHour);

        $rules = new \Recurr\Rule($pattern, $start_at, $end_at);
        $transformer = new \Recurr\Transformer\ArrayTransformer;
        $dates = $transformer->transform($rules);

        return $dates;

    }
}
