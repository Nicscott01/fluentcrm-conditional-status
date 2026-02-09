=== FluentCRM Conditional Status for FluentForms ===
Contributors: yournamehere
Donate link: https://yourwebsite.com/donate
Tags: fluentcrm, fluentforms, subscriber status, double opt-in, gdpr, email marketing, conditional logic
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Conditionally set FluentCRM subscriber status based on FluentForms field values. Perfect for GDPR-compliant opt-in forms with consent tracking.

== Description ==

**FluentCRM Conditional Status** extends FluentForms' FluentCRM integration, allowing you to set different subscriber statuses based on form field values. This is essential for GDPR compliance, consent management, and creating sophisticated email marketing workflows.

= The Problem =

By default, FluentForms' FluentCRM integration only allows you to:
* Set all subscribers as "Subscribed"
* Enable double opt-in for everyone

There's NO native way to:
* Set contacts to **Transactional** status for non-marketing emails
* Conditionally trigger double opt-in based on checkbox values
* Map form field values to different subscriber statuses
* Properly handle GDPR consent checkboxes

= The Solution =

This plugin adds a powerful conditional status mapping feature directly into your FluentCRM feed settings. Now you can:

* **Map field values to statuses**: If checkbox is checked → Pending (double opt-in), if unchecked → Transactional
* **Support all field types**: Checkboxes, radio buttons, select dropdowns, text fields, and more
* **GDPR compliant**: Perfect for consent checkboxes
* **Multiple status options**: Subscribed, Pending, Transactional, Unsubscribed, Bounced, Complained

= Use Cases =

**GDPR Consent Management**
Have a marketing consent checkbox. Checked = they want marketing emails (trigger double opt-in), Unchecked = transactional emails only.

**Newsletter Preferences**
Radio buttons for newsletter preference. Map selections to appropriate subscriber statuses.

**Customer Type Segmentation**
Use dropdown selections to automatically set the right subscriber status based on customer type.

= Features =

* ✅ Conditional status mapping based on form fields
* ✅ Support for checkboxes, radio buttons, selects, text fields
* ✅ Native UI integration with FluentForms
* ✅ All 6 FluentCRM subscriber statuses supported
* ✅ WordPress coding standards compliant
* ✅ Translation ready
* ✅ Lightweight and performant
* ✅ No additional dependencies

= Requirements =

* FluentForms (free or pro version)
* FluentCRM (free or pro version)

== Installation ==

= Automatic Installation =

1. Go to Plugins > Add New in your WordPress admin
2. Search for "FluentCRM Conditional Status"
3. Click Install Now and then Activate

= Manual Installation =

1. Download the plugin ZIP file
2. Go to Plugins > Add New > Upload Plugin
3. Choose the ZIP file and click Install Now
4. Activate the plugin

= Setup =

1. Create or edit a FluentForm
2. Go to Settings & Integrations > FluentCRM
3. Add or edit a FluentCRM feed
4. Enable "Enable Conditional Status"
5. Select the field to check (e.g., opt-in checkbox)
6. Choose status for TRUE/checked (e.g., "Pending")
7. Choose status for FALSE/unchecked (e.g., "Transactional")
8. Save the feed

== Frequently Asked Questions ==

= Do I need both FluentForms and FluentCRM? =

Yes, this plugin extends the integration between FluentForms and FluentCRM, so both plugins must be installed and active.

= Does this work with the free versions? =

Yes! This plugin works with both free and pro versions of FluentForms and FluentCRM.

= What field types are supported? =

Checkboxes, radio buttons, select dropdowns, text fields, email fields, number fields, textareas, GDPR agreement fields, and terms & conditions fields.

= How does the plugin determine if a field is "true" or "false"? =

* **TRUE/Checked**: Checkbox is checked, field has a value, text field is not empty
* **FALSE/Unchecked**: Checkbox is unchecked, no value selected, text field is empty, or value is "false", "no", "0", "off"

= Will this trigger double opt-in emails? =

Yes! If you set the status to "Pending", FluentCRM will automatically send the double opt-in confirmation email.

= What is "Transactional" status? =

Transactional status allows contacts to receive transactional emails (order confirmations, password resets, etc.) but not marketing emails. Perfect for GDPR compliance when users don't consent to marketing.

= Can I use this with multiple feeds? =

Yes! You can configure different conditional status logic for each FluentCRM feed on your form.

= Is this GDPR compliant? =

The plugin provides the tools for GDPR compliance, but you're responsible for proper implementation. Use it to respect user consent by mapping consent checkboxes to appropriate statuses.

= Does this affect existing subscribers? =

The conditional status is applied when the form is submitted. It does not retroactively change existing subscribers.

= Can I customize the logic? =

Yes! The plugin provides WordPress hooks and filters for developers to customize behavior. See the GitHub repository for documentation.

== Screenshots ==

1. Enable conditional status in FluentCRM feed settings
2. Select which form field to check
3. Choose statuses for TRUE and FALSE conditions
4. Status mapping logic explained in the UI
5. Example GDPR consent checkbox configuration

== Changelog ==

= 1.0.0 - 2025-02-09 =
* Initial release
* Conditional status mapping based on form fields
* Support for all FluentCRM subscriber statuses
* Native UI integration with FluentForms
* Support for checkboxes, radios, selects, text fields
* WordPress coding standards compliant
* Translation ready

== Upgrade Notice ==

= 1.0.0 =
Initial release of FluentCRM Conditional Status for FluentForms.

== Developer Hooks ==

= Actions =

`fluentcrm_conditional_status_applied` - Fires when a conditional status is applied

= Filters =

`fluentform/fluentcrm_integration_subscriber_data` - Modify subscriber data before sending to FluentCRM

== Support ==

For support, please visit:
* [WordPress.org Support Forum](https://wordpress.org/support/plugin/fluentcrm-conditional-status/)
* [GitHub Repository](https://github.com/yourusername/fluentcrm-conditional-status)

== Credits ==

Special thanks to WPManageNinja for creating FluentForms and FluentCRM.
