# 🧰 TNC Toolbox: Web Performance 🚀

https://wordpress.org/plugins/tnc-toolbox

> [!TIP]
> **Will expand to include other features as requested by community & clients!**

- Allows you to purge the EA-NGINX User Cache from within WP
- Automatically purges the cache when a post or page is updated
- Does the same when the WP Core has been successfully upgraded :)
- Allows you to disable or enable the NGINX User Cache from inside WP
- Only presents the options to enable/disable/configure for WP Admins
- For any action, redirects you back to the page you requested it from
- Shows you whether or not the API is working via diagnostic quota info

## 🖥️ (PLUGIN) System Requirements

> [!IMPORTANT]  
> EA-NGINX (Reverse Proxy) by cPanel is supported, not "old school" implementations.
> 
> CloudLinux Apache2NGINX (A2N) depends on ea-NGINX so _shouldn't_ cause issues with TNC Toolbox.

- cPanel API Key, cPanel Username & Server Hostname needed in Settings
- Hosting Environment must be cPanel+WHM, running NGINX + Apache stack!

## ♻️ (UPDATE) v1 to v2.x.x Overhaul

**On every website running the plugin, check that:**

1. Website is reporting v2.x.x plugin version.
2. Plugin has been activated post-update. *
3. Config exists in the plugin settings.
4. API status checker reports OK.
5. /wp-content/tnc-toolbox-config/ folder is gone.

_(* Change to main plugin file name results in deactivation)_

## ⚙️ (INSTALL) How to Install the Plugin

1. Head to WP-Admin > Plugins, search for "TNC Toolbox"
2. Click "Install" for the resulting plugin (this one)
3. Once installed, click "Activate" to make it live
4. Done! Head to Settings > TNC Toolbox for config
5. Configure your API Key, Username & Hostname

## 🛠️ (DEPLOY) Loading it onto all WP sites

Use `WP-CLI` to install **tnc-toolbox** for all relevant accounts.

## 🆘 (HELP) Getting help with the Plugin

Include all info, screenshots, etc, to help our crew.

> [!NOTE]  
> Please raise an [Issue](https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/issues) on the GitHub Repository! 

## ✨ (FOSS) Contributing to the Plugin

Please feel free to contribute by submitting a PR with your proposed changes!

**The Network Crew Pty Ltd (TNC)**
