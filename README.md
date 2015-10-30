# MyCalendar plugin

This plugin adds a simple calendar with events database feature to [OctoberCMS](http://octobercms.com).

* Easily add a calendar to your site and populate with your own event dates
* Store dates in a simple table through backend form

MyCalendar was built soley to be extended to do what you need.  You can use it as is or add fields with your own plugins.


Go to the MyCalendar page in the backend and add your events.

## Display Month calendar on page
- Drag "Month Component" to the page layout.

```
	{% component 'Month' %}
```	


## Display Month calendar on page and make it wider
- Drag "Month Component" to the page layout and edit as below.

```
    <style>
      table.mycal { width: 900px;}
	</style>

	{% component 'Month' %}
```	


## Display Month calendar on page and insert events from DB
- Double click "Events Component" to add it to page. 
- Drag "Month Component" to the page layout and edit as below.

```
	{% component 'Month' events = MyEvents %}
```

The "Events Component" injects the MyEvents array into the page.


## Display Event list calendar on page and insert events from DB for current and next month
- Double click "Events Component" to add it to page. 
- Drag "List Component" to the page layout and edit as below.

```
	<div style="width:100px">
	{% component 'EvList' events = MyEvents %}
	{% set m = date()|date("m") +1 %}
	{% component 'EvList' month = m events = MyEvents %}
	</div>
```

The "List Component" only shows up when there are events for the month indicated.
The "Events Component" injects the MyEvents array into the page.

## You have multiple optional properties for each component
- __Month__ (month) - The month you want to show. ( defaults to now )
- __Year__ (year) -The year you want to show. ( defaults to now )
- __Events__ (events) - Array of the events you want to show. 
- __Calendar Color__ (color) - What color do you want calendar to be? ( defaults to red )
- __Day Properties__ (dayprops) - Array of the properties you want to put on the day indicator.
- __Load Style Sheet__ (loadstyle) - Do you want to load the default stylesheet?

These properties can be set by clicking on component and changing them there or in the page layout as below:
    
	{% component 'EvList' month = 2 events = MyEvents %}


## Like this plugin?
If you like this plugin or if you use some of my plugins, you can help me by submiting a review in the market.

Please do not hesitate to find me in the IRC channel or contact me for assistance.
Sincerely 
Kurt Jensen
