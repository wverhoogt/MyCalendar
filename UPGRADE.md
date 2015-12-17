# Upgrade guide

- [Upgrading to 1.0.8 from 1.0.7](#upgrade-1.0.8)
- [Upgrading to 1.0.10 from 1.0.9](#upgrade-1.0.10)
- [Upgrading to 1.0.18](#upgrade-1.0.18)



<a name="upgrade-1.0.18"></a>
## Upgrading To 1.0.18

The database has been changed to the more common and easier to use date field instead of separate year,month, and day fields.
There is very good reason for using one date type field and I should have thought that through better prior to original design. If you are ever thinking of breaking dates across multiple DB fields in one of your own projects, DON'T DO IT. Dates are a nightmare already without adding a non-standard method of storage to the problem.

Your data should transfer to this new date field automagicaly on upgrade and you should not need to change any data.

Please review instructions as some things have changed for displaying "Event Lists".  This should be much easier now.

I have discovered that some items in Backend still do not use translation and those will areas be addressed in upcoming updates.  Thank you for your patients.

<a name="upgrade-1.0.10"></a>
## Upgrading To 1.0.10

The Month Component now includes a "Next" and "Previous" link to allow users to scroll through months.
You will need to add parameters to your page URL to accept these parameters.
Example:

    /calendarpage/:month?/:year?

This will make the parameters optional and default to current month and year.

<a name="upgrade-1.0.8"></a>
## Upgrading To 1.0.8

The MyCalendar plugin has been changed to allow use of permissions for your events stored by the MyCaledar Events table.
If you choose to use these new permission features, you will need to enable this by adding permissions to all of your categories and then changing the Events component setting "Use Permission" to "yes".

In short, to retain the old functionaliy simply do nothing after upgrade.