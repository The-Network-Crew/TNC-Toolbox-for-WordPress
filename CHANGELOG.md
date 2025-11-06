# Changelog
All notable changes to TNC Toolbox for WordPress will be documented in this file.

## [2.0.3] - 2025-11-06

### 🚀 Feature
- Auto-purge (ACF): When you save ACF config, purge! (#24)

## [2.0.2] - 2025-11-06

### 🐛 Bug Fix
- Settings: Save no longer errors (relocated func.) (#32)

## [2.0.1] - 2025-11-06

### 🐛 Bug Fix
- Auto-purge: Now calls directly (re: nonce failure) (#31)

## [2.0.0] - 2025-11-06

### 🚀 Feature
- Feature: Add disk/inode usage info (API connected OK) (#5)
- Architecture: Complete codebase reorganisation for maintainability

### 💅 Polish
- Improvement: Better API response handling and error messages (#28)
- Architecture: Move cPanel API functionality to vendor module
- Architecture: Automatic config migration from old versions
- License: Properly apply GPLv3 to all code files in repo
- Legacy: Requires WP 6.0 & PHP 8.0 to set easy baselines

### 🐛 Bug Fix
- Security: Move config storage from files to WordPress database (#6)
- Security: Secure deletion of old config files after migration
- Auto-update: Revert flag from v1.4.2 which was global (#29)

## [1.4.2] - 2025-11-04

### 🐛 Bug Fix
- Config Checker: Add function to iterate over
- On-update Check: When core updates, also check

## [1.4.1] - 2025-08-16

### 💅 Polish
- Update WordPress supported version to 6.8.x (#26)
- Add screenshots to the WP.org plugin listing (#27)
- Slight improvements to the WP.org Plugin Readme (#27)

## [1.4.0] - 2025-02-21

### 💅 Polish
- Set max length of newly-relayed API error (re: #22)
- Auto-purge when WP Core is successfully upgraded (#23)

## [1.3.9] - 2024-12-31

### 💅 Polish
- Pass-through error from cP API back to WP GUI (#22)
- Improve uncaught error wording, and clarify on user type

## [1.3.8] - 2024-12-18

### 💅 Polish
- Update compatibility with WP to support v6.7 (#20)
- Slight improvements to GUI Notice wording (#21)

## [1.3.7] - 2024-05-09

### 💅 Polish
- Slight improvements to GUI/Menu wording

### 🐛 Bug Fix
- Declare class firstly, resolve warnings (#18)

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
