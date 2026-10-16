# Hi-hat ACF Metabox Ordering

[![License: GPL v2](https://img.shields.io/badge/License-GPLv2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress Plugin: Tested up to 6.7](https://img.shields.io/badge/WordPress%20Plugin-Tested%20up%20to%206.7-blue.svg)](https://wordpress.org/plugins/)

A WordPress plugin that applies a global ACF metabox ordering set by the site administrator to all users.

Works on both **standard WordPress** installations and **WordPress Multisite** networks.

## Description

By default, WordPress stores metabox ordering per-user. This means if an admin rearranges ACF field group meta boxes on a page, other users won't see that order—each user has their own independent arrangement.

This plugin solves that problem by capturing the admin's preferred metabox order and applying it globally to **all users** on the site, including Editors, Contributors, Authors, and any custom roles. This ensures consistent metabox layouts across all editors, regardless of individual user preferences.

**Multisite Compatible** ✅ - Each site in a multisite network maintains its own metabox ordering configuration.

## How It Works

1. **Selective Activation**: Configure which post types should use global metabox ordering in Tools → Metabox Ordering
2. **Automatic Capture**: When an administrator rearranges ACF metaboxes on an enabled post type, the plugin automatically captures and stores that order
3. **Global Enforcement**: All subsequent user sessions see metaboxes in the order set by the admin, overriding per-user preferences

## Installation

### From WordPress.org Plugin Repository

1. Go to **Plugins → Add New** in your WordPress admin
2. Search for "Hi-hat ACF Metabox Ordering"
3. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the latest release from [GitHub Releases](https://github.com/miketurner/hi-hat-acf-metabox-ordering/releases)
2. Extract the ZIP file and upload the `hi-hat-acf-metabox-ordering` folder to `/wp-content/plugins/`
3. Activate the plugin through the **Plugins** menu in WordPress
4. Go to **Tools → Metabox Ordering** and select which post types should use global ordering
5. As an administrator, arrange ACF metaboxes as desired—the order is automatically captured

### From Source (Development)

```bash
git clone https://github.com/miketurner/hi-hat-acf-metabox-ordering.git
cd hi-hat-acf-metabox-ordering
composer install  # if using vendor dependencies
npm install       # if using build tools
```

## Configuration

### Enabling Post Types

After activation, navigate to **Tools → Metabox Ordering** to:
- See all available post types (Pages, Posts, Custom Post Types, etc.)
- Check the boxes for post types that should enforce global metabox ordering
- Save your settings

Only administrators can access this settings page.

### Setting the Global Order

1. Go to the edit screen for any enabled post type
2. Rearrange the ACF metaboxes by dragging them
3. The order is automatically captured and will apply to all users

## Requirements

- WordPress 5.0+
- Advanced Custom Fields (ACF) Pro or Free version
- PHP 7.2+

## Frequently Asked Questions

### Role Behavior

**Who can SET the global metabox order?**
- Only **Administrators** (users with `manage_options` capability) can set the global order
- Simply rearrange metaboxes on an enabled post type as an admin, and the plugin automatically captures the order

**Who SEES the global metabox order?**
- **All users** see the admin-set global order on enabled post types:
  - ✅ Administrators
  - ✅ Editors
  - ✅ Contributors
  - ✅ Authors
  - ✅ Custom roles with post-editing capabilities

### Q: Can I choose which post types use global ordering?
A: Yes! Go to **Tools → Metabox Ordering** and select which post types should enforce the global order. Unselected post types will continue to use per-user metabox ordering.

### Q: Can individual users customize their own metabox order?
A: No. For post types with global ordering enabled, all users see the admin-set order. Individual user preferences are overridden.

### Q: How do I change the global order?
A: Simply log in as an admin, rearrange the metaboxes on an enabled post type, and the new order will be captured and applied to all users.

### Q: Does this work with multisite?
A: Yes! The plugin works perfectly with both **standard WordPress** installations and **WordPress Multisite** networks. Each site maintains its own post type settings and metabox orders. Changes on one site don't affect others.

### Q: Can I reset the plugin to default behavior?
A: Deactivate the plugin in **Tools → Metabox Ordering** (uncheck all post types), or delete it entirely to restore per-user metabox ordering.

## Technical Details

### Storage
- **Settings** - Stored per-site in `wp_options` under key `hihat_enabled_post_types`
- **Metabox Orders** - Stored per-site in `wp_options` under key `hihat_acf_metabox_order`

### Multisite
In multisite networks, each site maintains separate settings and metabox orders. Changes on one site don't affect others.

### Hooks
- `update_user_metadata` (priority 10) — Captures metabox order changes when admin rearranges
- `get_user_metadata` (priority 10) — Retrieves global order for all users instead of per-user preference

## Support

For issues or feature requests, please:

- **Report Issues**: [GitHub Issues](https://github.com/miketurner/hi-hat-acf-metabox-ordering/issues)
- **Contact Support**: [Hi-hat Consulting](https://hi-hatconsulting.com)
- **Submit PRs**: Contributions are welcome! Please create a pull request with your changes

## License

This plugin is licensed under the GPL v2 or later. See [LICENSE](./LICENSE) for details.

## Contributing

We welcome contributions! Please follow these guidelines:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Setup

```bash
# Install dependencies
composer install
npm install

# Run tests
npm run test

# Build distribution
./build.sh
```

## Releases

### Creating a Release

To create a new release for distribution:

#### 1. Update Version Numbers

Update the version in the plugin files:

```bash
# Edit the main plugin file
nano hihat-acf-metabox-ordering.php
# Update: Version: X.Y.Z

# Edit the readme
nano readme.txt
# Update: Stable tag: X.Y.Z
```

#### 2. Commit and Tag

```bash
# Commit the version update
git add .
git commit -m "Bump version to X.Y.Z"
git push origin main

# Create a Git tag
git tag -a vX.Y.Z -m "Version X.Y.Z - Brief description of release"

# Push the tag to GitHub
git push origin vX.Y.Z
```

#### 3. Build Distribution

```bash
# Build the production-ready zip file
./build.sh

# This creates: dist/hi-hat-acf-metabox-ordering.zip
```

#### 4. Create GitHub Release

**Option A: Using GitHub CLI** (if installed)

```bash
gh release create vX.Y.Z \
  --title "Version X.Y.Z - Release Title" \
  --notes "Release notes and feature list here" \
  dist/hi-hat-acf-metabox-ordering.zip
```

**Option B: Via GitHub Web Interface**

1. Visit: `https://github.com/miketurner/hi-hat-acf-metabox-ordering/releases`
2. Click **"Create a new release"**
3. Select tag: `vX.Y.Z`
4. Add release title and description
5. Upload the zip file from `dist/` folder
6. Click **"Publish release"**

#### Example Release Command

```bash
# Complete release workflow
git tag -a v1.0.1 -m "Version 1.0.1 - Bug fixes"
git push origin v1.0.1
./build.sh
gh release create v1.0.1 \
  --title "Version 1.0.1 - Bug Fixes" \
  --notes "Improved metabox ordering stability and performance" \
  dist/hi-hat-acf-metabox-ordering.zip
```

### Semantic Versioning

Releases follow semantic versioning (MAJOR.MINOR.PATCH):

- `v1.0.0` — Initial public release
- `v1.0.1` — Patch release (bug fixes only)
- `v1.1.0` — Minor release (new features, backward compatible)
- `v2.0.0` — Major release (breaking changes)



**Author:** Mike Turner - [Hi-hat Consulting](https://hi-hatconsulting.com)

---

**Made with ❤️ for WordPress developers**

## Changelog

### 1.0.0
- Initial release
- Automatic capture of admin metabox ordering
- Per-post-type configuration via settings page
- Global enforcement across all users
- WordPress multisite support

