# Upgrade guide

- [Upgrading to 1.0.8 from 1.0.7](#upgrade-1.0.8)
- [Upgrading to 1.0.10 from 1.0.9](#upgrade-1.0.8)

<a name="upgrade-1.0.8"></a>
## Upgrading To 1.0.8

The MyCalendar plugin has been changed to allow use of permissions for your events stored by the MyCaledar Events table.
If you choose to use these new permission features, you will need to enable this by adding permissions to all of your categories and then changing the Events component setting "Use Permission" to "yes".

In short, to retain the old functionaliy simply do nothing after upgrade.


<a name="upgrade-1.0.10"></a>
## Upgrading To 1.0.10

The Month Component now includes a "Next" and "Previous" link to allow users to scroll through months.
You will need to add parameters to your page URL to accept these parameters.
Example:

    /calendarpage/:month?/:year?

This will make the parameters optional and default to current month and year.