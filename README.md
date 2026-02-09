# FluentCRM Conditional Status for FluentForms

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/fluentcrm-conditional-status.svg)](https://wordpress.org/plugins/fluentcrm-conditional-status/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/r/fluentcrm-conditional-status.svg)](https://wordpress.org/plugins/fluentcrm-conditional-status/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/fluentcrm-conditional-status.svg)](https://wordpress.org/plugins/fluentcrm-conditional-status/)
[![License](https://img.shields.io/badge/license-GPL--2.0%2B-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

Conditionally set FluentCRM subscriber status based on FluentForms field values. Perfect for implementing GDPR-compliant opt-in forms with proper consent tracking.

## 🎯 The Problem This Solves

By default, FluentForms' FluentCRM integration only allows you to set subscribers as "Subscribed" or enable double opt-in for everyone. There's no native way to:

- Set contacts to **Transactional** status (for non-marketing emails)
- Conditionally trigger double opt-in based on form fields
- Map checkbox/radio/select values to different subscriber statuses
- Handle GDPR consent checkboxes properly

## ✨ Features

- **Conditional Status Mapping**: Set different subscriber statuses based on form field values
- **Support for All Field Types**: Works with checkboxes, radio buttons, select dropdowns, text fields, and more
- **GDPR Compliant**: Perfect for consent checkboxes - unchecked = Transactional, checked = Pending (double opt-in)
- **Native Integration**: Feels like a built-in feature of FluentForms
- **Multiple Status Options**: Subscribed, Pending, Transactional, Unsubscribed, Bounced, Complained
- **Easy Configuration**: Intuitive UI integrated directly into FluentCRM feed settings
- **WordPress Coding Standards**: Clean, well-documented code following best practices

## 📋 Requirements

- WordPress 5.8 or higher
- PHP 7.4 or higher
- [FluentForms](https://wordpress.org/plugins/fluentform/) (free or pro)
- [FluentCRM](https://wordpress.org/plugins/fluent-crm/) (free or pro)

## 🚀 Installation

### From WordPress.org (Recommended)

1. Go to **Plugins > Add New** in your WordPress admin
2. Search for "FluentCRM Conditional Status"
3. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the plugin ZIP file
2. Go to **Plugins > Add New > Upload Plugin**
3. Choose the ZIP file and click **Install Now**
4. Activate the plugin

### From GitHub

```bash
cd wp-content/plugins
git clone https://github.com/yourusername/fluentcrm-conditional-status.git
```

Then activate the plugin from the WordPress admin.

## 📖 Usage

### Basic Setup

1. Create or edit a FluentForm
2. Go to **Settings & Integrations > FluentCRM**
3. Add or edit a FluentCRM feed
4. Look for the **Enable Conditional Status** checkbox
5. Configure your conditional logic:
   - **Field to Check**: Select the form field (e.g., an opt-in checkbox)
   - **Status if TRUE/Checked**: Choose the status when field has a value (e.g., "Pending" for double opt-in)
   - **Status if FALSE/Unchecked**: Choose the status when field is empty (e.g., "Transactional")
6. Save the feed

### Example Use Cases

#### 1. GDPR Consent Checkbox

**Scenario**: You have a marketing consent checkbox. Checked = they want marketing emails (trigger double opt-in), Unchecked = transactional emails only.

**Configuration**:
- Field to Check: `marketing_consent` (checkbox)
- Status if TRUE: **Pending** (triggers double opt-in)
- Status if FALSE: **Transactional**

#### 2. Newsletter Preference

**Scenario**: Radio buttons for newsletter preference: "Yes, subscribe me" or "No thanks".

**Configuration**:
- Field to Check: `newsletter_preference` (radio)
- Status if TRUE: **Subscribed**
- Status if FALSE: **Transactional**

#### 3. Customer Type

**Scenario**: Dropdown for customer type. If they select "Existing Customer", don't send marketing emails.

**Configuration**:
- Field to Check: `customer_type` (select)
- Status if TRUE: **Transactional** (has a value selected)
- Status if FALSE: **Subscribed** (no selection)

## 🎨 How It Works

### Field Evaluation Logic

The plugin evaluates form fields as "truthy" or "falsy":

**Truthy** (Status if TRUE):
- Checkboxes: Checked
- Radio/Select: Has a value selected
- Text fields: Contains any text
- Arrays: Has at least one value

**Falsy** (Status if FALSE):
- Checkboxes: Unchecked
- Radio/Select: No value selected
- Text fields: Empty
- Values: `false`, `no`, `0`, `off`, `unchecked`, or empty string

### Available Subscriber Statuses

- **Subscribed**: Fully subscribed, no confirmation needed
- **Pending**: Triggers FluentCRM double opt-in confirmation emails
- **Transactional**: Can receive transactional emails only (no marketing)
- **Unsubscribed**: Contact is unsubscribed
- **Bounced**: Email address has bounced
- **Complained**: Contact marked emails as spam

## 🔧 Developer Hooks

### Actions

**Track when status is applied**:
```php
add_action( 'fluentcrm_conditional_status_applied', function( $data ) {
    error_log( 'Status applied: ' . print_r( $data, true ) );
}, 10, 1 );
```

### Filters

**Modify subscriber data**:
```php
add_filter( 'fluentform/fluentcrm_integration_subscriber_data', function( $subscriber_data, $feed, $entry ) {
    // Your custom logic
    return $subscriber_data;
}, 20, 3 );
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 Coding Standards

This plugin follows:
- [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- PHPCS with WordPress ruleset

Run code checks:
```bash
composer install
./vendor/bin/phpcs
```

## 🐛 Bug Reports

If you find a bug, please [open an issue](https://github.com/yourusername/fluentcrm-conditional-status/issues) with:
- Your WordPress version
- Your PHP version
- FluentForms version
- FluentCRM version
- Steps to reproduce the issue

## 📄 License

This plugin is licensed under the GPLv2 or later.

## 👨‍💻 Author

**Your Name**
- Website: [yourwebsite.com](https://yourwebsite.com)
- GitHub: [@yourusername](https://github.com/yourusername)

## 🙏 Credits

Special thanks to:
- [WPManageNinja](https://wpmanageninja.com/) for FluentForms and FluentCRM
- The WordPress community

## 💬 Support

- **Documentation**: [Plugin website](https://yourwebsite.com/fluentcrm-conditional-status)
- **Support Forum**: [WordPress.org Support](https://wordpress.org/support/plugin/fluentcrm-conditional-status/)
- **Email**: your-email@example.com

---

⭐ If you find this plugin helpful, please consider giving it a star on GitHub or a review on WordPress.org!
