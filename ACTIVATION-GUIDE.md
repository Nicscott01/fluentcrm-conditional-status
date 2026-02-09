# ✅ Activation & Testing Guide

Follow these steps to activate and test your new plugin.

## Step 1: Check Prerequisites ✓

Before activating, ensure you have:

- [ ] WordPress 5.8 or higher
- [ ] PHP 7.4 or higher  
- [ ] **FluentForms** installed and active
- [ ] **FluentCRM** installed and active

### Check in Terminal

```bash
# Check if FluentForms is installed
wp plugin list | grep fluentform

# Check if FluentCRM is installed  
wp plugin list | grep fluent-crm

# If not installed, install them:
wp plugin install fluentform --activate
wp plugin install fluent-crm --activate
```

## Step 2: Activate the Plugin 🚀

### Option A: WordPress Admin (Recommended)

1. Log in to your WordPress admin
2. Go to **Plugins**
3. Find "FluentCRM Conditional Status for FluentForms"
4. Click **Activate**
5. Look for success message

### Option B: WP-CLI

```bash
cd /Users/nscott/web_repos/friendsofyorkcommunitycenter

# List the plugin
wp plugin list --status=inactive | grep fluentcrm-conditional

# Activate it
wp plugin activate fluentcrm-conditional-status

# Verify activation
wp plugin list | grep fluentcrm-conditional-status
```

### Option C: Manual Symlink (If Not Auto-Detected)

If WordPress doesn't see the plugin:

```bash
# Ensure proper permissions
chmod -R 755 /Users/nscott/web_repos/friendsofyorkcommunitycenter/web/app/plugins/fluentcrm-conditional-status

# Fix ownership (if needed)
# sudo chown -R www-data:www-data web/app/plugins/fluentcrm-conditional-status
```

## Step 3: Verify Dependencies ✅

After activation, check for any notices:

1. **Expected**: Plugin activates successfully
2. **If you see a warning**: It means FluentForms or FluentCRM is missing
3. **Action**: Install the missing plugin(s)

The plugin will show a clear admin notice if dependencies are missing.

## Step 4: Create a Test Form 📝

Let's create a simple test form:

### Using FluentForms Admin:

1. Go to **FluentForms → Add New Form**
2. Choose "Blank Form" or "Contact Form"
3. Add these fields:
   - **Name** (text field)
   - **Email** (email field)
   - **Marketing Consent** (checkbox)
     - Field Name: `marketing_consent`
     - Label: "Yes, I want to receive marketing emails"

4. Save the form

### Using WP-CLI (Advanced):

```bash
# This would require a more complex command with JSON data
# Stick with the admin interface for first test
```

## Step 5: Configure FluentCRM Feed 🎯

1. In your form, click **Settings & Integrations**
2. Click **FluentCRM**
3. Click **Add New Integration** (or edit existing)
4. Fill in basic settings:
   - **Integration Name**: "Test Conditional Status"
   - **List**: Select a list (or create one in FluentCRM first)
   - **Primary Email Field**: Select your email field
   - **First Name**: Map to your name field

5. **Scroll down** - You should now see:
   ```
   ┌─────────────────────────────────────┐
   │ ☐ Enable Conditional Status        │
   │   Set subscriber status based on    │
   │   form field values                 │
   └─────────────────────────────────────┘
   ```

6. **Check the box** to enable

7. New fields appear:
   - **Field to Check**: Select `marketing_consent`
   - **Status if TRUE/Checked**: Select `Pending`
   - **Status if FALSE/Unchecked**: Select `Transactional`

8. Click **Save Integration**

## Step 6: Test Scenario 1 - Checkbox Checked ✅

1. Go to your form on the frontend
2. Fill out:
   - Name: "Test User 1"
   - Email: "test1@example.com"
   - ✅ **Check** the marketing consent box
3. Submit

### Expected Result:

1. Go to **FluentCRM → Contacts**
2. Find "test1@example.com"
3. Status should be: **Pending**
4. Check email (if configured) for double opt-in message

## Step 7: Test Scenario 2 - Checkbox Unchecked ❌

1. Go to your form again
2. Fill out:
   - Name: "Test User 2"
   - Email: "test2@example.com"
   - ❌ **Do NOT check** the marketing consent box
3. Submit

### Expected Result:

1. Go to **FluentCRM → Contacts**
2. Find "test2@example.com"
3. Status should be: **Transactional**
4. No double opt-in email sent

## Step 8: Verify & Debug 🔍

### Check Contact Status

```bash
# View FluentCRM contacts via database
wp db query "SELECT id, email, status FROM wp_fc_subscribers ORDER BY id DESC LIMIT 5"
```

Expected output:
```
+----+---------------------+---------------+
| id | email               | status        |
+----+---------------------+---------------+
|  2 | test2@example.com   | transactional |
|  1 | test1@example.com   | pending       |
+----+---------------------+---------------+
```

### Enable Debug Logging

Add this to your `wp-config.php` temporarily:

```php
// Enable debug mode
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Add this to your theme's `functions.php` or a custom plugin:

```php
// Log conditional status applications
add_action('fluentcrm_conditional_status_applied', function($data) {
    error_log('=== FluentCRM Conditional Status Applied ===');
    error_log('Field Name: ' . $data['field_name']);
    error_log('Field Value: ' . $data['field_value']);
    error_log('Is Truthy: ' . ($data['is_truthy'] ? 'Yes' : 'No'));
    error_log('Status: ' . $data['status']);
    error_log('Entry ID: ' . $data['entry_id']);
    error_log('Form ID: ' . $data['form_id']);
}, 10, 1);
```

Then check your debug log:

```bash
tail -f /Users/nscott/web_repos/friendsofyorkcommunitycenter/web/app/debug.log
```

## Step 9: Test Different Field Types 🧪

### Test with Radio Buttons

1. Add a radio field to your form:
   - Field Name: `newsletter_preference`
   - Options: "Yes, subscribe me" / "No thanks"

2. Create another FluentCRM feed:
   - Field to Check: `newsletter_preference`
   - Status if TRUE: `Subscribed` (any selection)
   - Status if FALSE: `Transactional` (no selection)

### Test with Select Dropdown

1. Add a select field:
   - Field Name: `customer_type`
   - Options: "New Customer" / "Existing Customer"

2. Create another feed with appropriate status mapping

### Test with Text Field

1. Add a text field:
   - Field Name: `company_name`

2. Create a feed:
   - Field to Check: `company_name`
   - Status if TRUE: `Subscribed` (has company name)
   - Status if FALSE: `Transactional` (no company name)

## Step 10: Production Checklist 🎯

Before using in production:

- [ ] Tested with checkbox checked
- [ ] Tested with checkbox unchecked  
- [ ] Verified status in FluentCRM
- [ ] Tested double opt-in email (for Pending status)
- [ ] Tested with different field types
- [ ] Removed test contacts from FluentCRM
- [ ] Disabled debug logging
- [ ] Documented your configuration
- [ ] Created backup

## Troubleshooting 🔧

### Issue: Plugin doesn't activate

**Solution**: Check that both FluentForms and FluentCRM are active

```bash
wp plugin activate fluentform fluent-crm
```

### Issue: No conditional status option in feed

**Solutions**:
1. Hard refresh browser (Cmd+Shift+R)
2. Clear WordPress cache
3. Check browser console for JS errors
4. Verify plugin is actually activated

### Issue: Status not being applied

**Debug steps**:
1. Enable debug logging (see Step 8)
2. Submit test form
3. Check debug log for status application
4. Verify field name matches exactly
5. Check field value format

### Issue: Double opt-in not working

**Check**:
1. Status is set to "Pending"
2. FluentCRM double opt-in is enabled
3. Email settings are configured
4. Contact is added to a list
5. Check spam folder

## Next Steps 🚀

Once testing is complete:

1. **Document Your Setup**: Note which forms use which configurations
2. **Train Your Team**: Share EXAMPLES.md with your team
3. **Monitor Results**: Track opt-in rates and contact statuses
4. **Optimize**: Adjust status mappings based on results
5. **Consider Publishing**: Share on GitHub or WordPress.org

## Support 💬

If you encounter issues:

1. Check debug log
2. Review EXAMPLES.md for similar use cases
3. Check FluentForms submission data
4. Verify FluentCRM settings
5. Open GitHub issue with debug details

---

## ✅ Success Indicators

You know it's working when:

- ✅ Plugin activates without errors
- ✅ Conditional status option appears in feed settings
- ✅ Test submission #1 (checked) → Status: Pending
- ✅ Test submission #2 (unchecked) → Status: Transactional  
- ✅ Double opt-in email sent for Pending contacts
- ✅ Debug log shows status applications

**Congratulations!** Your plugin is working correctly! 🎉

---

**Quick Commands Reference:**

```bash
# Activate plugin
wp plugin activate fluentcrm-conditional-status

# Check status
wp plugin list | grep fluentcrm-conditional

# View recent contacts
wp db query "SELECT id, email, status FROM wp_fc_subscribers ORDER BY id DESC LIMIT 10"

# Deactivate (if needed)
wp plugin deactivate fluentcrm-conditional-status

# View plugin info
wp plugin get fluentcrm-conditional-status
```
