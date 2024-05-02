# Changelog
All notable changes to TNC Toolbox for WordPress will be documented in this file.

## [1.3.6] - 2024-05-02

### 💅 Polish
- Upgrade consistently to GPLv3 from a split mix of v2 and v3
- Merge all cPanel API Calls into single function, pass action (#16)

### 🐛 Bug Fix
- Multi-site should now function properly, due to #17 being fixed
- pluggable.php no longer included, as it is supplied by WP (#17)

## [1.3.5] - 2023-12-10

### 🚀 Feature
- Import pluggable.php for access to more WP API functionality (#15)

### 💅 Polish
- Restrict on/off and reconfiguration functionality to WP Admins only (#14)

### 🐛 Bug Fix
- Don't show a PHP warning when the config is empty, instead return empty (#13)

## [1.3.4] - 2023-06-14

### 🐛 Bug Fix
- Auto-purge now working properly

## [1.3.2] - 2023-06-13

### 🚀 Feature
- Purges the NGINX User Cache if a post/page is published or updated (#3)

### 💅 Polish
- Add a Settings link to WP's Installed Plugins page (#9)
- Add a warning if the plugin is activated but not configured (#4)
- Colour the button backgrounds (traffic light coming in v1.3.3) re: UX (#9)

### 🐛 Bug Fix
- Expand the size of the config fields to show API key (#9)
- Retain existing config values during a WP Plugin Update (#8)

## [1.2.0] - 2023-04-01

### 🚀 Feature
- Added Config GUI
- Published on WP.org!

## [1.1.1] - 2023-01-20

### 💅 Polish
- Clarify that ea-nginx (cPanel) is needed for usage.

### 🐛 Bug Fix
- Improves security by escaping data before echoing.

## [1.1.0] - 2023-01-19

### 🚀 Feature
- Adds buttons to enable and disable the NGINX User Cache.

### 💅 Polish
- Passes you back to the page you requested the action from, rather than to WP-Admin home.

## [1.0.0] - 2023-01-13

### 🚀 Feature
- Includes the ability to purge the NGINX User Cache via the admin top menu bar in one-click.
