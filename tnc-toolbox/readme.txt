=== TNC Toolbox: Web Performance ===
Author URI: https://tnc.works
Plugin URI: https://merlot.digital
Donate link: 
Contributors: 
Tags: NGINX, Cache Purge, Web Performance, Automatic Purge, Freeware
Requires at least: 
Tested up to: 6.7
Requires PHP: 
Stable tag: 1.4.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Adds functionality (cache purge, etc) to WP - designed for NGINX-powered Servers on cPanel+WHM. Made to help you fly online!


== Description ==

TNC Toolbox aims to enhance your WordPress experience with NGINX-on-cPanel (ea-nginx). 

Built for our Managed Server clients, we've open-sourced it so others can enjoy it too!

With a heavy focus on the Apache + NGINX as Reverse Caching Proxy web stack, the plugin aims to help with Website Management, Performance and Security. 

**At the moment, TNC Toolbox:**

- Allows you to enable, disable and purge the NGINX User Cache
- Purges the NGINX Cache magically on post/page publish/update!
- Also purges the Cache when the WP Core is successfully updated
- Lets you know if the plugin is activated but not yet configured
- Only allows Administrators to enable/disable caching; edit config

**Eager for even more capabilities?**

We plan to add further features as clients & the community request it. 

_Please let us know your ideas on [GitHub](https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/) - we'd love to hear from you!_

**FOSS by [The Network Crew Pty Ltd](https://tnc.works) for [Merlot Digital](https://merlot.digital) and the world.**


== Frequently Asked Questions ==

= Does the plugin allow me to purge the NGINX User Cache? =

Yes, it does! All users can do this easily via the button in the admin top bar.

= Does it allow me to disable or enable NGINX User Caching? =

Yes! Admins can disable or enable the cache from the top admin bar.

This is only visible if you are logged in as an Administrator.

= Can I request functionality to be added into the module? =

Yes! Simply raise an Issue/PR on the [GitHub repository](https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/issues) and we'll take a look.

= Why am I getting a cURL Error 3 on my WP-Admin dashboard? =

Most likely due to newline /n characters in your config files. Use the [script](https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/blob/main/script-remove-conf-newlines.sh) in the GitHub Repo to remove these.

= Is there a way for us to deploy the plugin server-wide? =

Yes! If you check the GitHub repository, you can use the bash scripts there (make sure you satisfy their pre-requisites) to deploy/update the plugin server-wide.

Please note there are pre-requisites to running the scripts, so understand & prepare before usage as always.


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

**Caching ideals:**
- Don't forget, ea-NGINX (reverse proxy caching) is meant to be 2nd-level
- ie. Make sure your WP site also has on-site caching, like WP Super Cache
- You can go further with caching, and should: like browser-caching assets!


== Changelog ==

= 1.4.0: Feb 21, 2025 =
* Truncate max-length of relayed API error to GUI (#22)
* Auto-purge when WP Core is successfully upgraded (#23)

= 1.3.9: Dec 31, 2024 =
* Pass-through error from cP API back to WP GUI (#22)
* Improve uncaught error wording, and clarify on user type

= 1.3.8: Dec 18, 2024 =
* Update compatibility to WP 6.7.x sub-major (#20)
* Slight refinement to wording of not-configured prompt (#21)

= 1.3.7: May 9, 2024 =
* Declare class firstly, resolve warnings (#18)
* Slight improvements to GUI/Menu wording

= 1.3.6: May 2, 2024 =
* Pluggable: Remove dependency, ie. support multi-site (#17)
* Re-factor: Merge API calls into single function (#16)
* GPLv3: Consistent, no longer partial v2 & v3
