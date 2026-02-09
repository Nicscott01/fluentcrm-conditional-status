# Quick Reference Guide

## 📦 Plugin Overview

**Name**: FluentCRM Conditional Status for FluentForms  
**Version**: 1.0.0  
**License**: GPL v2+  
**Requires**: WordPress 5.8+, PHP 7.4+, FluentForms, FluentCRM

## 🎯 What It Does

Allows you to set different FluentCRM subscriber statuses based on form field values. Perfect for:
- GDPR consent checkboxes
- Newsletter opt-in preferences  
- Customer type segmentation
- Event registration preferences

## ⚡ Quick Setup

1. **Install Plugin**
   ```bash
   # Plugin is located at:
   web/app/plugins/fluentcrm-conditional-status/
   ```

2. **Activate** (WordPress Admin → Plugins)

3. **Configure a Form**
   - Go to FluentForms → Your Form → Settings & Integrations → FluentCRM
   - Enable "Conditional Status"
   - Select field to check
   - Choose statuses for TRUE/FALSE

## 📊 Code Statistics

- **PHP Code**: 633 lines
- **Documentation**: 1,152 lines
- **Total Files**: 19
- **Classes**: 3 main classes
- **Hooks**: Multiple actions and filters

## 🗂️ File Structure

```
fluentcrm-conditional-status/
├── 📄 Main Plugin
│   └── fluentcrm-conditional-status.php (132 lines)
├── 📁 Core Classes
│   ├── includes/class-feed-settings.php (258 lines)
│   └── includes/class-submission-handler.php (243 lines)
├── 📚 Documentation
│   ├── README.md (GitHub)
│   ├── readme.txt (WordPress.org)
│   ├── EXAMPLES.md (7 use cases)
│   ├── INSTALL.md (Installation guide)
│   ├── CONTRIBUTING.md (Developer guide)
│   ├── CHANGELOG.md (Version history)
│   └── PROJECT-SUMMARY.md (This project)
├── 🔧 Configuration
│   ├── composer.json (PHP deps)
│   ├── phpcs.xml (Code standards)
│   ├── .gitignore
│   └── .distignore
├── 🚀 Deployment
│   ├── build.sh (Build script)
│   └── .github/workflows/ (CI/CD)
└── 🎨 Assets
    └── assets/ (Icon & banner guidelines)
```

## 🎛️ Available Statuses

| Status | Description | Use Case |
|--------|-------------|----------|
| **Subscribed** | Fully subscribed, no confirmation | Existing customers |
| **Pending** | Triggers double opt-in | New subscribers (GDPR) |
| **Transactional** | Transactional emails only | No marketing consent |
| **Unsubscribed** | Opted out | Rare use case |
| **Bounced** | Email bounced | Rare use case |
| **Complained** | Marked as spam | Rare use case |

## 🔌 Key Hooks

### Actions

```php
// Track when status is applied
add_action('fluentcrm_conditional_status_applied', function($data) {
    // $data contains: field_name, field_value, is_truthy, status, entry_id, form_id
}, 10, 1);
```

### Filters

```php
// Modify subscriber data before processing
add_filter('fluentform/fluentcrm_integration_subscriber_data', function($data, $feed, $entry) {
    // Your custom logic
    return $data;
}, 10, 3);

// Modify after contact is added
add_filter('fluentcrm_contact_added_by_fluentform', function($subscriber, $feed, $entry) {
    // Your custom logic
    return $subscriber;
}, 10, 3);
```

## ✅ Field Evaluation Logic

### TRUE (Truthy)
- ✅ Checkbox: Checked
- ✅ Text field: Has content
- ✅ Select/Radio: Has value selected
- ✅ Array: Has at least one item

### FALSE (Falsy)
- ❌ Checkbox: Unchecked
- ❌ Text field: Empty
- ❌ Select/Radio: No selection
- ❌ Value is: `false`, `no`, `0`, `off`, `unchecked`, or empty

## 🎨 Supported Field Types

- ✅ `input_checkbox`
- ✅ `input_radio`
- ✅ `select`
- ✅ `input_text`
- ✅ `input_email`
- ✅ `input_number`
- ✅ `textarea`
- ✅ `gdpr-agreement`
- ✅ `terms_and_condition`

## 🛠️ Development Commands

```bash
# Navigate to plugin
cd web/app/plugins/fluentcrm-conditional-status

# Install dev dependencies
composer install

# Check code standards
composer run phpcs

# Fix code standards
composer run phpcbf

# Build distribution ZIP
./build.sh

# View plugin info
wp plugin list --name=fluentcrm-conditional-status
```

## 🚀 Deployment Checklist

### Before Publishing

- [ ] Update author name in all files
- [ ] Update GitHub username in URLs
- [ ] Update website URLs
- [ ] Update email addresses
- [ ] Create plugin icons (128x128, 256x256)
- [ ] Create banners (772x250, 1544x500)
- [ ] Take screenshots (5 recommended)
- [ ] Test all field types
- [ ] Test double opt-in
- [ ] Run PHPCS
- [ ] Test on clean WP install

### GitHub Publication

```bash
git init
git add .
git commit -m "Initial commit v1.0.0"
git branch -M main
git remote add origin https://github.com/USERNAME/fluentcrm-conditional-status.git
git push -u origin main
git tag -a 1.0.0 -m "Version 1.0.0"
git push --tags
```

### WordPress.org Publication

1. Create WordPress.org account
2. Submit plugin for review
3. Wait for approval (1-14 days)
4. Use GitHub Action or manual SVN
5. Upload assets to assets/ folder

## 🎯 Common Use Cases

### 1. GDPR Consent
```
Field: marketing_consent (checkbox)
TRUE: Pending (double opt-in)
FALSE: Transactional
```

### 2. Newsletter Opt-in
```
Field: subscribe_newsletter (checkbox)
TRUE: Subscribed
FALSE: Transactional
```

### 3. Customer Type
```
Field: customer_type (select)
TRUE: Transactional (existing)
FALSE: Subscribed (new)
```

## 📞 Support & Resources

- **Documentation**: All included in plugin
- **Examples**: See EXAMPLES.md (7 detailed scenarios)
- **Code Standards**: WordPress Coding Standards
- **License**: GPL v2 or later
- **GitHub**: Create repo at github.com
- **WordPress.org**: Submit at wordpress.org/plugins/developers/add/

## 💡 Pro Tips

1. **Test First**: Always test forms before production
2. **Use Multiple Feeds**: Complex logic = multiple feeds with conditions
3. **Combine with Tags**: Status + Tags = powerful segmentation
4. **Double Opt-in**: Use "Pending" for GDPR compliance
5. **Transactional**: Perfect for non-marketing consents
6. **Documentation**: EXAMPLES.md has 7+ real-world scenarios

## 🐛 Troubleshooting

**Plugin not appearing?**
- Clear cache
- Check both FluentForms and FluentCRM are active

**Status not applying?**
- Check conditional status is enabled
- Verify field name is correct
- Check field value (use debug hook)

**Double opt-in not triggering?**
- Ensure status is set to "Pending"
- Check FluentCRM double opt-in settings
- Verify contact is added to a list

## 📈 Version History

- **1.0.0** (2025-02-09) - Initial release

---

**Ready to use!** See PROJECT-SUMMARY.md for next steps.
