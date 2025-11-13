=== TNC Toolbox: Web Performance ===
Author URI: https://tnc.works
Plugin URI: https://merlot.digital
Donate link: 
Contributors: 
Tags: NGINX, Cache Purge, Web Performance, Automatic Purge, Freeware
Tested up to: 6.8
Stable tag: 2.0.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Made to help you fly online! Adds functionality (cache purge, etc) to WP - designed for NGINX-powered Servers on cPanel+WHM.


== Description ==

TNC Toolbox aims to enhance your WordPress experience with NGINX-on-cPanel (ea-nginx). 

**Built for our Managed Server clients, we've open-sourced it so others can enjoy it too!**

With a heavy focus on the Apache + NGINX as Reverse Caching Proxy web stack, the plugin aims to help with Website Management, Performance and Security. 

> ❤️ **FOSS by [The Network Crew Pty Ltd](https://tnc.works) (TNC) for [Merlot Digital](https://merlot.digital) & the world.** ❤️

== Functionality ==

**At the moment, TNC Toolbox:**

- Allows you to enable, disable and purge the NGINX User Cache
- Purges the NGINX Cache magically on post/page publish/update!
- Also purges the Cache when the WP Core is successfully updated
- Lets you know if the plugin is activated but not yet configured
- Only allows Admins to enable/disable caching & edit configs
- Shows you the status of cP UAPI via disk usage info
- Purge when any ACF config options are saved

**Eager for even more capabilities?**

We plan to add further features as clients & the community request it. 

_Please let us know your ideas on [GitHub](https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/) - we'd love to hear from you!_

== Updating from v1 to v2.x.x ==

**On every website running the plugin, check that:**

1. Website is reporting v2.x.x plugin version.
2. Plugin has been activated post-update. *
3. Config exists in the plugin settings.
4. API status checker reports OK.
5. /wp-content/tnc-toolbox-config/ folder is gone.

_(* Change to main plugin file name may result in deactivation)_

== Screenshots ==

1. Top Menu Bar options for NGINX Caching.
2. Configuration in the WP Admin GUI.

== Frequently Asked Questions ==

= Does the plugin allow me to purge the NGINX User Cache? =

**Yes, it does!** All Users can do this easily via the button in the Admin top menu bar.

= Does it allow me to disable or enable NGINX User Caching? =

**Yes!** Admins can disable or enable the cache from the Admin top menu bar.

This is only visible if you are logged in as a WP Administrator.

= Can I request functionality to be added into the module? =

**Yes!** Simply raise an Issue/PR on the [GitHub repository](https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/issues) and we'll take a look.

= Is there a way for us to deploy the plugin server-wide? =

**Yes!** You can use `WP-CLI` to install **tnc-toolbox** for all users!

== Caching Deployments ==

**Caching ideals:**
- Don't forget, ea-NGINX (reverse proxy caching) is meant to be 2nd-level
- ie. Make sure your WP site also has on-site caching, like WP Super Cache
- You can go further with caching, and should: like browser-caching assets!

**3-layer Cache:**
1. NGINX Caching Proxy (ahead of Apache)
2. WP Super Cache, WP Rocket, etc on-site
3. htaccess/etc rules for Browser Caching

**This way, you can ensure maximum efficiency!**

The key is to purge when stale, so properly configuring your WP Plugin Cache is critical to ensuring that you don't end up with cache misses due to stale data that could've/should've been purged by garbage collection, preloading, etc, rule-sets.

== Installation ==

**Pre-requisites:**
1. To use ea-NGINX features, your Hosting needs to be on cPanel
2. Acquire a cPanel API Token (cPanel > Manage API Tokens)
3. Configure the TNC Toolbox plugin inside WordPress

**How to install:**
1. Go to `Plugins` in WP-Admin
2. Click on the button `Add New`
3. Search for `TNC Toolbox` then click `Install Now`
4. Click on `Activate plugin` then `Settings`
5. Enter your API Token, User & Hostname
6. Save the config & use WP as-normal!

https://docs.cpanel.net/cpanel/security/manage-api-tokens-in-cpanel/

== Changelog ==

= 2.0.4: Nov 11, 2025 =
* Remove minimum requirements: Ensure v2 adoption growth
* Folder Renames: languages to locale; vendors to vendor

= 2.0.3: Nov 6, 2025 =
* Auto-purge (ACF): When you save ACF config, purge! (#24)

= 2.0.2: Nov 6, 2025 =
* Fix: Settings save no longer errors (relocated func.) (#32)

= 2.0.1: Nov 6, 2025 =
* Fix: Auto-purge now calls directly (re: nonce failure) (#31)

= 2.0.0: Nov 6, 2025 =
* NOTE: MAJOR REBUILD, PLEASE TEST BEFORE DEPLOYING
* Feature: Add direct quota info re: API connected OK (#5)
* Security: Move config from files to WordPress database (#6)
* Security: Secure deletion of old config after migration
* License: Properly apply GPLv3 to all code in the repo
* Improvement: Better API response handling and errors (#28)
* Architecture: Complete codebase re-build for maintainability
* Architecture: Move cPanel API functionality to vendor module
* Architecture: Automatic config migration from old versions
* Legacy: Requires WP 6.0 & PHP 8.0 to set easy baselines
* Auto-update: Revert flag from v1.4.2 - was global (#29)

= 1.4.2: Nov 4, 2025 =
* Config Checker: Add function to iterate over
* On-update Check: When core updates, also check

= 1.4.1: Aug 16, 2025 =
* Update WordPress supported version to 6.8.x (#26)
* Add screenshots to the WP.org plugin listing (#27)
* Slight improvements to the WP.org Plugin Readme (#27)

= 1.4.0: Feb 21, 2025 =
* Truncate max-length of relayed API error to GUI (#22)
* Auto-purge when WP Core is successfully upgraded (#23)
