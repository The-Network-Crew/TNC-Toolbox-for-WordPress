=== TNC Toolbox ===
Author URI: https://thenetworkcrew.com.au
Plugin URI: https://leopard.host
Donate link: 
Contributors: 
Tags: 
Requires at least: 
Tested up to: 6.2
Requires PHP: 
Stable tag: 1.3.3
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds features (cache purge, etc) for your NGINX-powered Hosting on cPanel (ea-nginx).


== Description ==

This plugin enhances your WordPress experience with NGINX-on-cPanel (ea-nginx). 

Built for our Managed Server clients, we've open-sourced it so others can enjoy it too!

We plan to add further features as clients & the community request it. Check the FAQ.


== Frequently Asked Questions ==

= Does the plugin allow me to purge the NGINX User Cache? =

Yes, it does! This can be done easily via the button in the admin top bar.

= Does it allow me to disable or enable NGINX User Caching? =

Yes! You can disable or enable the cache from the top admin bar.

= Can I request functionality to be added into the module? =

Yes! Simply raise an Issue/PR on the [GitHub repository](https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/issues) and we'll take a look.

= Why am I getting a cURL Error 3 on my WP-Admin dashboard? =

Most likely due to newline /n characters in your config files. Use the [script](https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/blob/main/remove-newlines-from-configs.sh) in the GitHub Repo to remove these.


== Installation ==

1. Go to `Plugins` in the Admin menu
2. Click on the button `Add New`
3. Search for `TNC Toolbox` and click 'Install Now'
4. Click on `Activate plugin`


== Changelog ==

= 1.3.3: June 13, 2023 =
* Top bar links: Traffic lights

= 1.3.2: June 13, 2023 =
* Config Folder: Create before save

= 1.3.1: June 13, 2023 =
* Config Files: Relocate (to preserve)

= 1.3.0: June 13, 2023 =
* Auto-purge: On post/page save/update
* Settings link: Add to Installed Plugins

= 1.2.1: June 13, 2023 =
* Colours: Off/On buttons now Red/Green
* Warning: If activated, but not configured
* Config Fields: Expand field sizing to be 45
* Credits: https://www.psyborg.com.au

= 1.2.0: April 1, 2023 =
* Published: Now listed on WP.org!
* Configs: Moved from assets/ to config/

= 1.1.2: March 30, 2023 =
* GUI: Add config page in WP-Admin
* API/User/Host: Change to fields

= 1.1.1: January 20, 2023 =
* Security: Improve escaping, etc
* Description: Re-word for WP.org

= 1.1.0: January 19, 2023 =
* NGINX Cache: Disable/Enable added

= 1.0.0: January 12, 2023 =
* It's a module: Birth of TNC Toolbox
* NGINX Cache: Purge the Cache in WP
