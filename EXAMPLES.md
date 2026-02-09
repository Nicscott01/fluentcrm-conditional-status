# Usage Examples

This document provides real-world examples of how to use FluentCRM Conditional Status for FluentForms.

## Status Mapping Example (No Hidden-Field Logic Required)

Use this pattern when hidden fields cannot be conditionally controlled in your form builder setup.

1. Create multiple FluentCRM feeds for the same form.
2. Add feed-level conditional logic so each feed runs only for the intended branch (for example consent checked vs unchecked).
3. In each feed, set **Fallback / Forced Status**:
   - Feed A: `pending`
   - Feed B: `transactional`
4. (Optional) Still map **Subscriber Status (Mapped Value)** from a form field when available. The mapped status has priority; fallback/forced status is used if mapping is empty/invalid.

> Note: FluentForms feed smartcodes do not support inline fallback syntax in feed runtime parsing.

## Example 1: GDPR Marketing Consent Checkbox

**Scenario**: You have a contact form with a marketing consent checkbox. Only users who check the box should receive marketing emails, and they must confirm via double opt-in. Others should only receive transactional emails.

### Form Setup

Create a checkbox field in FluentForms:
- **Field Name**: `marketing_consent`
- **Field Label**: "I agree to receive marketing emails"
- **Field Type**: Checkbox

### Feed Configuration

In the FluentCRM feed settings:
- ✅ **Enable Conditional Status**: Checked
- **Field to Check**: `marketing_consent`
- **Status if TRUE/Checked**: `Pending` (triggers double opt-in)
- **Status if FALSE/Unchecked**: `Transactional`

### Result

- User checks the box → Status: **Pending** → Double opt-in email sent → After confirmation: **Subscribed**
- User doesn't check the box → Status: **Transactional** → Can receive transactional emails only

---

## Example 2: Newsletter Subscription Dropdown

**Scenario**: You want to give users explicit control over whether they subscribe to your newsletter via a dropdown selection.

### Form Setup

Create a select dropdown in FluentForms:
- **Field Name**: `newsletter_preference`
- **Field Label**: "Newsletter Subscription"
- **Options**:
  - "Yes, subscribe me to the newsletter"
  - "No thanks"

### Feed Configuration

In the FluentCRM feed settings:
- ✅ **Enable Conditional Status**: Checked
- **Field to Check**: `newsletter_preference`
- **Status if TRUE/Checked**: `Subscribed` (has a value = they selected an option)
- **Status if FALSE/Unchecked**: `Transactional` (no selection)

### Advanced Conditional Logic

For more control, you might want to check the actual value. Use FluentForms' conditional logic with multiple feeds:

**Feed 1** (for "Yes" responses):
- Conditional Logic: Show if `newsletter_preference` equals "Yes, subscribe me to the newsletter"
- Status: `Pending` (double opt-in)

**Feed 2** (for "No" responses):
- Conditional Logic: Show if `newsletter_preference` equals "No thanks"
- Status: `Transactional`

---

## Example 3: Customer Type Segmentation

**Scenario**: You have different customer types and want to handle them differently. Existing customers should not receive promotional emails, but new prospects should.

### Form Setup

Create a radio button field:
- **Field Name**: `customer_type`
- **Field Label**: "Are you a new or existing customer?"
- **Options**:
  - "New customer"
  - "Existing customer"

### Feed Configuration

In the FluentCRM feed settings:
- ✅ **Enable Conditional Status**: Checked
- **Field to Check**: `customer_type`
- **Status if TRUE/Checked**: `Transactional` (has value = either option selected)
- **Status if FALSE/Unchecked**: `Subscribed`

**Better Approach**: Use multiple feeds with FluentForms conditional logic:

**Feed 1** (New Customers):
- Conditional Logic: Show if `customer_type` equals "New customer"
- Status: `Pending` (send welcome email with opt-in)

**Feed 2** (Existing Customers):
- Conditional Logic: Show if `customer_type` equals "Existing customer"
- Status: `Transactional` (order updates only)

---

## Example 4: Event Registration with Email Preferences

**Scenario**: Event registration form where attendees can opt into the mailing list, but everyone needs to get event updates.

### Form Setup

1. Basic event registration fields (name, email, etc.)
2. Checkbox field:
   - **Field Name**: `join_mailing_list`
   - **Field Label**: "Add me to your mailing list for future events and news"

### Feed Configuration

In the FluentCRM feed settings:
- ✅ **Enable Conditional Status**: Checked
- **Field to Check**: `join_mailing_list`
- **Status if TRUE/Checked**: `Subscribed` (full subscription)
- **Status if FALSE/Unchecked**: `Transactional` (event emails only)

### Additional Setup

Add tags in the feed:
- Tag everyone with "Event Attendee 2025"
- This allows you to send event-specific emails to everyone regardless of their subscription status

---

## Example 5: Multi-Step Form with Progressive Consent

**Scenario**: A multi-step form where users provide basic info first, then opt into marketing on a later step.

### Form Setup

**Step 1**: Basic information (name, email, etc.)

**Step 2**: Marketing preferences
- Checkbox: `email_marketing_consent` - "Send me marketing emails"
- Checkbox: `sms_marketing_consent` - "Send me SMS updates"

### Feed Configuration

**Feed 1** (Email Marketing):
- ✅ **Enable Conditional Status**: Checked
- **Field to Check**: `email_marketing_consent`
- **Status if TRUE**: `Pending`
- **Status if FALSE**: `Transactional`
- **Lists**: "Email Marketing List"

**Feed 2** (SMS Marketing):
- ✅ **Enable Conditional Status**: Checked
- **Field to Check**: `sms_marketing_consent`
- **Status if TRUE**: `Subscribed`
- **Status if FALSE**: `Transactional`
- **Lists**: "SMS List"
- **Tags**: "SMS Opt-in"

---

## Example 6: Age Verification with Parental Consent

**Scenario**: Form with age verification where minors need parental consent for marketing emails.

### Form Setup

1. Radio button: `age_group`
   - "Under 18"
   - "18 or older"
2. Checkbox: `parental_consent` (shown conditionally if under 18)
   - "I have parental consent to receive emails"

### Feed Configuration

Use FluentForms conditional logic with multiple feeds:

**Feed 1** (Adults):
- Conditional Logic: Show if `age_group` equals "18 or older"
- Status: `Pending` (standard double opt-in)

**Feed 2** (Minors with Consent):
- Conditional Logic: Show if `age_group` equals "Under 18" AND `parental_consent` is checked
- Status: `Pending`
- Tags: "Minor with Consent"

**Feed 3** (Minors without Consent):
- Conditional Logic: Show if `age_group` equals "Under 18" AND `parental_consent` is not checked
- Status: `Transactional`
- Tags: "Minor - No Marketing"

---

## Example 7: B2B Lead Qualification

**Scenario**: B2B contact form where you want to qualify leads and only add high-quality leads to your marketing automation.

### Form Setup

1. Text field: `company_name`
2. Select: `company_size`
   - "1-10 employees"
   - "11-50 employees"
   - "51-200 employees"
   - "200+ employees"
3. Checkbox: `interested_in_demo`

### Feed Configuration

**Feed 1** (Qualified Leads - Large Companies):
- Conditional Logic: Show if `company_size` equals "51-200 employees" OR "200+ employees"
- ✅ **Enable Conditional Status**: Checked
- **Field to Check**: `interested_in_demo`
- **Status if TRUE**: `Subscribed` (interested in demo)
- **Status if FALSE**: `Pending` (not sure yet)
- **Tags**: "Enterprise Lead"

**Feed 2** (Small Companies):
- Conditional Logic: Show if `company_size` equals "1-10 employees"
- Status: `Transactional`
- **Tags**: "Small Business"

---

## Developer Example: Custom Status Logic

For advanced use cases, you can hook into the plugin:

```php
/**
 * Custom logic for subscriber status
 */
add_filter( 'fluentform/fluentcrm_integration_subscriber_data', function( $subscriber_data, $feed, $entry ) {
    // Get custom field value
    $account_value = isset( $entry->response['account_value'] ) ? $entry->response['account_value'] : 0;
    
    // High-value accounts get immediate subscription
    if ( $account_value > 10000 ) {
        $subscriber_data['status'] = 'subscribed';
        $subscriber_data['tags'][] = 'VIP';
    }
    
    return $subscriber_data;
}, 20, 3 );
```

---

## Tips for Success

1. **Test First**: Always test your forms with different field values before going live
2. **Clear Labels**: Make your form field labels crystal clear about what users are consenting to
3. **Check Double Opt-in**: If using "Pending" status, ensure FluentCRM double opt-in is properly configured
4. **Use Tags**: Combine conditional status with tags for better segmentation
5. **Multiple Feeds**: Don't be afraid to use multiple feeds with conditional logic for complex scenarios
6. **GDPR Compliance**: Document your consent process and keep records of when users opted in
7. **Transactional Status**: Use this for users who need to receive essential emails but haven't consented to marketing

---

## Common Patterns

### Pattern: Explicit Opt-in (GDPR Compliant)
- Default: No subscription
- Checkbox required: "Yes, I want marketing emails"
- Checked → Pending (double opt-in)
- Unchecked → Transactional (or don't add to CRM at all)

### Pattern: Opt-out (Legacy/Pre-checked)
- Default: Subscribed
- Checkbox: "I don't want marketing emails"
- Checked → Transactional
- Unchecked → Subscribed

### Pattern: Preference Center
- Multiple checkboxes for different types of emails
- Multiple feeds with different lists
- Each checkbox controls a different list subscription

---

Need more examples? Open an issue on [GitHub](https://github.com/yourusername/fluentcrm-conditional-status/issues) or check the [documentation](https://github.com/yourusername/fluentcrm-conditional-status).
