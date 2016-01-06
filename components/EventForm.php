<?php namespace KurtJensen\MyCalendar\Components;

use Auth;
use Cms\Classes\ComponentBase;
use KurtJensen\MyCalendar\Models\Category as MyCalCategory;
use KurtJensen\MyCalendar\Models\Event as MyCalEvent;
use Lang;

class EventForm extends ComponentBase
{
    public $myevents;
    public $myevent;
    public $categorylist;
    public $user;
    public $allowpublish;
    public $ckeditor;
    public $is_copy;

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
        $this->addJs('/modules/backend/formwidgets/datepicker/assets/js/build-min.js');
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/css/datepicker.css');
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/vendor/pikaday/css/pikaday.css');
        $this->addCss('/modules/backend/formwidgets/datepicker/assets/vendor/clockpicker/css/jquery-clockpicker.css');

        if ($this->ckeditor) {
            $this->addJs('//cdn.ckeditor.com/4.5.4/standard/ckeditor.js');
        }

        $this->myevents = $this->page['myevents'] = $this->loadEvents();
    }

    public function trans($string)
    {
        return Lang::get($string);
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

}
