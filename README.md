# FluentCRM Conditional Status for FluentForms

FluentForms + FluentCRM add-on that makes subscriber status assignment explicit and predictable.

## What It Does

This plugin changes FluentCRM feed behavior in FluentForms by:

- Adding `Subscriber Status (Mapped Value)` to **Other Fields** mapping
- Adding a per-feed `Fallback / Forced Status` select
- Removing conflicting native status toggles from the feed UI (`double_opt_in`, `force_subscribe`)
- Replacing FluentCRM's default feed runtime callback so mapped/forced status is authoritative

## Why

FluentCRM's default FluentForms feed runtime sets status mainly via native toggles (`subscribed` / `pending`). That prevents reliable field-based status mapping (for example, `transactional`).

This plugin keeps the existing feed model but takes control of status resolution.

## Status Resolution Order

For each feed run:

1. Mapped status from **Other Fields** (`status`)
2. `Fallback / Forced Status` (`fcs_force_status`)
3. Legacy/default behavior (for backward compatibility)

If final status is `pending`, double opt-in email is sent.

## Requirements

- WordPress 5.8+
- PHP 7.4+
- Fluent Forms
- FluentCRM

## Installation

1. Copy this plugin folder to `wp-content/plugins/fluentcrm-conditional-status`
2. Activate in WordPress admin
3. Edit a Fluent Form > **Settings & Integrations > FluentCRM**

## Usage

1. In FluentCRM feed, under **Other Fields**, map:
   - Contact Property: `Subscriber Status (Mapped Value)`
   - Form field/smartcode that resolves to a status slug (`subscribed`, `pending`, `transactional`, etc.)
2. Optionally set `Fallback / Forced Status` per feed.
3. Use feed-level conditional logic to run different feeds for different consent paths.

## Release Process (GitHub Tags)

This repo currently uses GitHub tags for releases.

Example:

```bash
git tag v1.0.1
git push origin v1.0.1
```

Create release ZIP from repo root:

```bash
git archive --format=zip --output fluentcrm-conditional-status-1.0.1.zip HEAD
```

Before tagging, update:

- `fluentcrm-conditional-status.php` plugin version
- `readme.txt` stable tag/changelog
- `CHANGELOG.md`

## WordPress.org Later

The plugin already includes the key file used by WordPress.org (`readme.txt`).
When ready, publish to the plugin SVN repo and tag there.
