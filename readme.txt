=== FluentCRM Conditional Status for FluentForms ===
Contributors: nicscott01
Tags: fluentcrm, fluentforms, email marketing, consent, transactional
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds authoritative subscriber status mapping to FluentCRM feeds in FluentForms.

== Description ==

FluentCRM Conditional Status modifies FluentCRM feed behavior in FluentForms so status assignment is explicit and reliable.

It provides:

- `Subscriber Status (Mapped Value)` in FluentCRM feed **Other Fields** mapping
- `Fallback / Forced Status` per feed
- Removal of conflicting native status toggles in the feed UI
- A custom feed runtime that makes mapped/forced status authoritative

= Status resolution order =

1. Mapped status from Other Fields (`status`)
2. Fallback / Forced Status (`fcs_force_status`)
3. Legacy/default behavior (for backward compatibility)

If resulting status is `pending`, double opt-in email is sent.

== Installation ==

1. Upload `fluentcrm-conditional-status` to `/wp-content/plugins/`
2. Activate the plugin through the Plugins screen
3. Open a Fluent Form and edit its FluentCRM feed

== Frequently Asked Questions ==

= Do I need FluentForms and FluentCRM? =

Yes. This plugin extends the FluentCRM integration feed inside FluentForms.

= How do I trigger double opt-in? =

Set the resolved status to `pending` using mapped status or fallback/forced status.

= Does this remove native FluentCRM feed options? =

It removes conflicting status toggles (`double_opt_in`, `force_subscribe`) from the feed UI so status handling stays unambiguous.

= Can I use multiple feeds? =

Yes. Use feed-level conditional logic and assign status per feed.

== Changelog ==

= 1.0.0 =
* Initial release
* Added status mapping and fallback/forced status controls
* Replaced default FluentCRM feed runtime callback with status-authoritative runtime

== Upgrade Notice ==

= 1.0.0 =
Initial release.
