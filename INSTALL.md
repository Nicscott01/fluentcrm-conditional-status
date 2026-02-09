# Installation Instructions

## Quick Start

### Option 1: Install from WordPress.org (Recommended)

1. Log in to your WordPress admin panel
2. Navigate to **Plugins > Add New**
3. Search for "FluentCRM Conditional Status"
4. Click **Install Now**
5. Click **Activate**

### Option 2: Manual Upload

1. Download the latest release ZIP file from [GitHub Releases](https://github.com/yourusername/fluentcrm-conditional-status/releases) or [WordPress.org](https://wordpress.org/plugins/fluentcrm-conditional-status/)
2. Log in to your WordPress admin panel
3. Navigate to **Plugins > Add New > Upload Plugin**
4. Choose the ZIP file and click **Install Now**
5. Click **Activate**

### Option 3: Install via WP-CLI

```bash
wp plugin install fluentcrm-conditional-status --activate
```

### Option 4: Manual Installation (FTP)

1. Download and extract the plugin ZIP file
2. Upload the `fluentcrm-conditional-status` folder to `/wp-content/plugins/`
3. Activate the plugin through the WordPress admin panel

### Option 5: Composer (for Bedrock/Modern WP Setups)

Add to your `composer.json`:

```json
{
  "require": {
    "wpackagist-plugin/fluentcrm-conditional-status": "^1.0"
  }
}
```

Then run:

```bash
composer install
```

## Post-Installation Setup

### Prerequisites Check

After activation, the plugin will check for required dependencies:
- ✅ FluentForms (free or pro)
- ✅ FluentCRM (free or pro)

If either plugin is missing, you'll see an admin notice with installation links.

### Configure Your First Feed

1. Go to a FluentForm in your WordPress admin
2. Click **Settings & Integrations**
3. Click on **FluentCRM** integration
4. Add a new feed or edit an existing one
5. Scroll down to find the new **Enable Conditional Status** section
6. Configure your conditional logic:
   - ✅ Check "Enable conditional subscriber status mapping"
   - 📋 Select the form field to check (e.g., opt-in checkbox)
   - ✅ Choose "Status if TRUE/Checked" (e.g., Pending for double opt-in)
   - ❌ Choose "Status if FALSE/Unchecked" (e.g., Transactional)
7. Save the feed

### Test Your Configuration

1. Submit a test form with the checkbox **checked**
   - Verify the contact appears in FluentCRM with the correct status
   - If using "Pending", check that the double opt-in email was sent
2. Submit another test with the checkbox **unchecked**
   - Verify the contact has the alternative status

## Troubleshooting

### Plugin doesn't appear after activation

**Solution**: Clear your WordPress object cache and browser cache.

### No conditional status options in feed settings

**Possible causes**:
1. FluentForms or FluentCRM is not active
2. You're using a very old version of FluentForms
3. Browser cache issue

**Solution**: 
- Ensure both FluentForms and FluentCRM are installed and activated
- Update both plugins to the latest versions
- Clear browser cache or try a different browser

### Status not being applied to subscribers

**Debug steps**:
1. Check that conditional status is enabled in the feed
2. Verify you selected the correct form field
3. Submit a test entry and check the field value
4. Check FluentCRM contact status directly in the database

**Enable debug logging**:
```php
add_action('fluentcrm_conditional_status_applied', function($data) {
    error_log('Conditional Status Applied: ' . print_r($data, true));
}, 10, 1);
```

### Double opt-in not triggering

**Solution**: 
- Ensure the status is set to "Pending"
- Check FluentCRM double opt-in settings
- Verify the contact is added to a list that has double opt-in enabled

## Uninstallation

### Standard Uninstall

1. Deactivate the plugin from **Plugins** page
2. Click **Delete** to remove all plugin files

### Complete Removal

The plugin does not create any custom database tables or options. Simply deleting the plugin will remove all traces.

## Need Help?

- 📖 [Full Documentation](https://github.com/yourusername/fluentcrm-conditional-status)
- 💬 [Support Forum](https://wordpress.org/support/plugin/fluentcrm-conditional-status/)
- 🐛 [Report Issues](https://github.com/yourusername/fluentcrm-conditional-status/issues)
- ✉️ Email: your-email@example.com
