# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-02-09

### Added
- Initial release
- Conditional status mapping based on form field values
- Support for all FluentCRM subscriber statuses (Subscribed, Pending, Transactional, Unsubscribed, Bounced, Complained)
- Native UI integration with FluentForms feed settings
- Support for multiple field types (checkboxes, radio buttons, select dropdowns, text fields, etc.)
- Comprehensive field value evaluation logic (truthy/falsy detection)
- WordPress coding standards compliance
- Translation ready (i18n support)
- Developer hooks and filters for extensibility
- Detailed documentation (README.md and readme.txt)
- Dependency checking with admin notices

### Features
- `enable_conditional_status` toggle in feed settings
- `conditional_status_field` selector for choosing form field
- `status_if_true` option for when field has value/is checked
- `status_if_false` option for when field is empty/unchecked
- Automatic double opt-in triggering for "Pending" status
- Debug action hook for tracking status applications

### Developer
- `fluentcrm_conditional_status_applied` action hook
- `fluentform/fluentcrm_integration_subscriber_data` filter integration
- `fluentcrm_contact_added_by_fluentform` filter integration
- Clean OOP architecture with singleton pattern
- Comprehensive inline documentation

[1.0.0]: https://github.com/yourusername/fluentcrm-conditional-status/releases/tag/1.0.0
