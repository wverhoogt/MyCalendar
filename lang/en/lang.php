<?php

return [
    'plugin' => [//Plugin File
        'name' => 'MyCalendar',
        'description' => 'Super simple calendar for displaying events.',
        'user_btn' => 'Users',
        'tab' => 'MyCalendar',
        'access_events' => 'events',
        'access_categories' => 'categories',
    ],
    'events' => [// Events Controller
        'menu_label' => 'Events',

        'all_users' => 'All Users',
        'new_user' => 'New User',
        'list_title' => 'Manage Users',
        'activating' => 'Activating...',
        'activate_warning_title' => 'User not activated!',
        'activate_warning_desc' => 'This user has not been activated and will be unable to sign in.',
        'activate_confirm' => 'Do you really want to activate this user?',
        'active_manually' => 'Activate this user manually',
        'delete_confirm' => 'Do you really want to delete this user?',
        'activated_success' => 'User has been activated successfully!',
        'return_to_list' => 'Return to users list',
        'delete_selected_empty' => 'There are no selected users to delete.',
        'delete_selected_confirm' => 'Delete the selected users?',
        'delete_selected_success' => 'Successfully deleted the selected users.',
    ],
    'categories' => [// Categories Controller
        'menu_label' => 'Categories',

        'all_users' => 'All Users',
        'new_user' => 'New User',
        'list_title' => 'Manage Users',
        'activating' => 'Activating...',
        'activate_warning_title' => 'User not activated!',
        'activate_warning_desc' => 'This user has not been activated and will be unable to sign in.',
        'activate_confirm' => 'Do you really want to activate this user?',
        'active_manually' => 'Activate this user manually',
        'delete_confirm' => 'Do you really want to delete this user?',
        'activated_success' => 'User has been activated successfully!',
        'return_to_list' => 'Return to users list',
        'delete_selected_empty' => 'There are no selected users to delete.',
        'delete_selected_confirm' => 'Delete the selected users?',
        'delete_selected_success' => 'Successfully deleted the selected users.',
    ],
    'settings' => [
        'description' => 'Configure calendar category protection.',

        'menu_label' => 'User settings',
        'menu_description' => 'Manage user based settings.',

        'public_perm_label' => 'Public Category',
        'public_perm_comment' => 'A permission for categories that will NOT be blocked from public viewing.',

        'deny_perm_label' => 'Denied Category',
        'deny_perm_comment' => 'A permission for categories that WILL be blocked from any viewing.',

        'default_perm_label' => 'Default Category',
        'default_perm_comment' => 'A permission that will be set on new categories by default ( unless set by user ).',
    ],
    'month' => [// Month Component
        'name' => 'Month Component',
        'description' => 'Shows a month calendar with events',

        'month_title' => 'Month',
        'month_description' => 'The month you want to show.',

        'year_title' => 'Year',
        'year_description' => 'The year you want to show.',

        'events_title' => 'Events',
        'events_description' => 'Array of the events you want to show.',

        'color_title' => 'Calendar Color',
        'color_description' => 'What color do you want calendar to be?',

        'dayprops_title' => 'Day Properties',
        'dayprops_description' => 'Array of the properties you want to put on the day indicator.',

        'loadstyle_title' => 'Load Style Sheet',
        'loadstyle_description' => 'Load the default CSS file.',

        'opt_no' => 'No',
        'opt_yes' => 'Yes',

        'color_red' => 'red',
        'color_green' => 'green',
        'color_blue' => 'blue',
        'color_yellow' => 'yellow',

        'day_sun' => 'Sun',
        'day_mon' => 'Mon',
        'day_tue' => 'Tue',
        'day_wed' => 'Wed',
        'day_thu' => 'Thu',
        'day_fri' => 'Fri',
        'day_sat' => 'Sat',

        'previous' => 'Previous',
        'next' => 'Next',
    ],
    'week' => [// Week Component
        'name' => 'Events Component',
        'description' => 'Get Events from DB and insert them into page',
    ],
    'evlist' => [// EvList Component
        'label' => 'Event',
        'id' => 'ID',
        'name' => 'Title',
        'is_published' => 'Published',
        'user_id' => 'Creator',
        'fname' => 'Creator First',
        'lname' => 'Creator Last',
        'date' => 'Date',
        'time' => 'Time',
        'text' => 'Details',
        'link' => 'Link',
        'categorys' => 'Categories',
    ],
    'events_comp' => [// Events Component
        'name' => 'Events Component',
        'description' => 'Get Events from DB and insert them into page',

        'linkpage_title' => 'Link to Page',
        'linkpage_desc' => 'Name of the event page file for the "More Details" links. This property is used by the event component partial.',
        'linkpage_group' => 'Links',
        'linkpage_opt_none' => 'None - Use Modal Pop-up',

        'title_max_title' => 'Maximum Popup Title Length',
        'title_max_description' => 'Maximum length of "title" property that shows the details of an event on hover.',

        'permissions_title' => 'Use Permission',
        'permissions_description' => 'Use permissions to restrict what categories of events are shown based on roles.',
        'permissions_opt_no' => 'No',
        'permissions_opt_yes' => 'Yes',
    ],
    'event' => [// Event Component and Model
        'label' => 'Event',
        'id' => 'ID',
        'name' => 'Title',
        'is_published' => 'Published',
        'user_id' => 'Creator',
        'fname' => 'Creator First',
        'lname' => 'Creator Last',
        'date' => 'Date',
        'time' => 'Time',
        'text' => 'Details',
        'link' => 'Link',
        'categorys' => 'Categories',
        'category' => 'Category',
        'error_not_found' => 'Event not found!',
        'error_allow_no' => 'Event not allowed!',
        'error_prohibit' => 'Event Prohibited!',

        'phold_name' => 'Name your Event',
        'phold_fname' => 'Creator First',
        'phold_lname' => 'Creator Last',
        'phold_date' => 'Pick a Date',
        'phold_time' => 'Pick a Time',
        'phold_text' => 'Enter as much details as you want about your event. (HTML OK)',
        'phold_link' => 'Add URL Link to your event.',
        'phold_categorys' => 'Categories',
        'empty_categorys' => 'There are no categories, you should create one first!',
    ],
    'event_form' => [// EventForm Component
        'name' => 'EventForm Component',
        'description' => 'Front end form to allow users to ad their own events',

        'allow_pub_title' => 'Allow Publish',
        'allow_pub_description' => 'Allow users to publish their event. (No means an admin must do it.)',

        'ckeditor_title' => 'Use CKEditor',
        'ckeditor_description' => 'Load CKEditor from cdn.ckeditor.com and show rich editor field for event description.',

        'opt_no' => 'No',
        'opt_yes' => 'Yes',

        'btn_add' => 'Add Event',
        'btn_edit' => 'Edit',
        'btn_delete' => 'Delete',
        'btn_save' => 'Save',
        'btn_cancel' => 'Cancel',
        'saving' => 'Saving Event...',
        'delete_conf' => 'Do you really want to delete this event?',
    ],
];
