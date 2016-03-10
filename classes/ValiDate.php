<?php namespace KurtJensen\MyCalendar\Classes;

use Carbon\Carbon;
use Validator;


     $formValues = post();



        $freq = strtoupper(array_get($formValues,'FREQ'));
        switch ($freq) {
            case 'None':
                break;
            case 'WEEKDAYS':
                break;
            case 'WEEKENDS':
                break;
            case 'HOURLY': $v['INTERVAL'] = 'required|integer|min:1';
                break;
            case 'DAILY': $v['INTERVAL'] =  'required|integer|min:1';
                break;
            case 'WEEKLY':  $v['INTERVAL'] =  'required|integer|min:1';
                            $v['BYDAY'] = 'required|array|in:"MO","TU","WE","TH","FR","SA","SU"';
                break;
            case 'MONTHLY': $v['INTERVAL'] =  'required|integer|min:1';
                if (!array_get($formValues,'month_on') == 'on_day') {
                    $v['BYSETPOS'] = 'required|max:5|min:-5';
                    $v['BYDAY'] = 'required|array|in:"MO","TU","WE","TH","FR","SA","SU"';
                }
                break;
            case 'YEARLY':  $v['INTERVAL'] =  'required|integer|min:1';
                if (!array_get($formValues,'year_on') == 'on_day') {

                    $formValues['BYMONTH'] = array_get($formValues,'YBYMONTH','');
                    $formValues['BYDAY'] = explode(',',array_get($formValues,'YBYDAY',''));
                    unset($formValues['YBYMONTH'],$formValues['YBYDAY']);
                    
                    $v['YBYMONTH'] = 'required|max:12|min:1';
                    $v['BYSETPOS'] = 'required|max:5|min:-5';

                    $v['BYDAY'] = 'required|array|in:"MO","TU","WE","TH","FR","SA","SU"';
                }
                break;
        }
        $ends = strtoupper(array_get($formValues,'Ends',''));
        if ($ends == 'AFTER') {
            $v['COUNT'] = 'required|max:100|min:1';
        } elseif ($ends == 'DATE') {
            $v['ENDON'] = 'required|date_format:"Y-m-d"';
        }


$validations = [
    'name' => 'required',
'is_published' => 'required',
'date' => 'required',
'time' => 'required',
'text' => 'required',
'link' => 'required',
'length' => 'required',
'pattern' => 'required',
'categorys' => 'required',
]+$v;


        $validator = Validator::make($formValues,
            $validations;
        );

        if ($validator->fails()) {
            $messages = $validator->messages();

            return $this->flash(implode('<br />', $messages->all()), 'error');
        }

        $formValues['from'] = $this->sender()->name . ' ' . $this->sender()->surname . '<' . $this->sender()->email . '>';



}