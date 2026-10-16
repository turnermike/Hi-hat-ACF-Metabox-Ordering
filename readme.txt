=== Hi-hat ACF Metabox Ordering ===
Contributors: miketurner
Tags: acf, metabox, ordering, fields, admin
Requires at least: 5.0
Tested up to: 7.1
Requires PHP: 7.2
Stable tag: 1.1.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Set a global ACF metabox order for your site. Admin configures it once, all users see the same order on enabled post types.

== Description ==

By default, WordPress stores metabox ordering per-user. This means if an admin rearranges ACF field group metaboxes on a page, other users won't see that order—each user has their own independent arrangement.

This plugin solves that problem by capturing the admin's preferred metabox order and applying it globally to **all users** on the site, including Editors, Contributors, Authors, and any custom roles. This ensures consistent metabox layouts across all editors, regardless of individual user preferences.

**Multisite Compatible** ✅ - Each site in a multisite network maintains its own metabox ordering configuration.

== Features ==

* **Selective Activation** - Configure which post types should use global metabox ordering
* **Automatic Capture** - Admin's metabox rearrangements are automatically saved
* **Global Enforcement** - All users see the same metabox order on enabled post types
* **Multisite Support** - Each site maintains separate settings and orders
* **Easy Configuration** - Simple settings page at Tools → Metabox Ordering

== Installation ==

1. Upload the `hi-hat-acf-metabox-ordering` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Tools → Metabox Ordering** and select which post types should use global ordering
4. Log in as an administrator and rearrange ACF metaboxes as desired—the order will be captured automatically

== Configuration ==

After activation, navigate to **Tools → Metabox Ordering** to:
- See all available post types
- Check the boxes for post types that should enforce global metabox ordering
- Save your settings

Only administrators can access this settings page.

== Screenshots ==

1. Settings page — Select which post types should enforce global ACF metabox ordering for all users

== Frequently Asked Questions ==

= Who can SET the global metabox order? =

Only **Administrators** (users with `manage_options` capability) can set the global order. Simply rearrange metaboxes on an enabled post type as an admin, and the plugin automatically captures the order.

= Who SEES the global metabox order? =

**All users** see the admin-set global order on enabled post types:
* Administrators
* Editors
* Contributors
* Authors
* Custom roles with post-editing capabilities

= Can I choose which post types use global ordering? =

Yes! Go to **Tools → Metabox Ordering** and select which post types should enforce the global order. Unselected post types will continue to use per-user metabox ordering.

= Can individual users customize their own metabox order? =

No. For post types with global ordering enabled, all users see the admin-set order. Individual user preferences are overridden.

= How do I change the global order? =

Simply log in as an admin, rearrange the metaboxes on an enabled post type, and the new order will be captured and applied to all users.

= Does this work with multisite? =

Yes! The plugin works perfectly with both **standard WordPress** installations and **WordPress Multisite** networks. Each site maintains its own post type settings and metabox orders. Changes on one site don't affect others.

= Can I reset the plugin to default behavior? =

Deactivate the plugin in **Tools → Metabox Ordering** (uncheck all post types), or delete it entirely to restore per-user metabox ordering.

== Requirements ==

* WordPress 5.0+
* Advanced Custom Fields (ACF) Pro or Free version
* PHP 7.2+

== Changelog ==

= 1.0.0 =
* Initial release
* Automatic capture of admin metabox ordering
* Per-post-type configuration via settings page
* Global enforcement across all users
* WordPress multisite support

== Support ==

For issues or feature requests, please visit the plugin's support page.

== License ==

This plugin is licensed under the GPL v2 or later.
