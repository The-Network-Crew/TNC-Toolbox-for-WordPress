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
- Selective cache purging with `ea-nginx-cache-purge` module (optional)

## 🖥️ (PLUGIN) System Requirements

> [!IMPORTANT]  
> EA-NGINX (Reverse Proxy) by cPanel is supported, not "old school" implementations.
> 
> CloudLinux Apache2NGINX (MAx) depends on ea-NGINX so _shouldn't_ cause issues with TNC Toolbox.

- cPanel API Key, cPanel Username & Server Hostname needed in Settings
- Hosting Server must run cPanel+WHM, with the ea-NGINX & Apache stack!

### 🔨 (HOLE PUNCH) Selective Purging how-to

**Starting with v2.1.0, you can leverage Selective Cache Purging rather than the entire cache.**

To do this, you need to complete some steps - else it will not work. You must be root.

0. Install the GetPageSpeed repository (dnf/yum) onto the Server:<br>
`dnf -y install https://extras.getpagespeed.com/release-latest.rpm`
1. Install ea-nginx-cache-purge module from GetPageSpeed repo:<br>
`dnf -y install ea-nginx-cache-purge`
2. Add `/etc/nginx/conf.d/server-includes/cache-purge.conf`<br>
File Contents: `proxy_cache_purge PURGE from 127.0.0.1;`
3. Rebuild & Reload: `nginx -t && systemctl reload nginx`

We'd like to say a huge thank-you to Danila (GitHub: dvershinin) for this contribution via [#35](https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/pull/35).

## ⚙️ (INSTALL) How to Install the Plugin

1. Head to WP-Admin > Plugins, search for "TNC Toolbox"
2. Click "Install" for the resulting plugin (this one)
3. Once installed, click "Activate" to make it live
4. Done! Head to Settings > TNC Toolbox for config
5. Configure your API Key, Username & Hostname

## 🛠️ (DEPLOY) Loading it onto all WP sites

Use `WP-CLI` to install **tnc-toolbox** for all relevant accounts.

This repo includes scripts for server-wide deploying & updating.

Note: You will need to configure UAPI creds per-website afterwards.

## ♻️ (UPDATE) v1 to v2.x.x Overhaul

**On every website running the plugin, check that:**

1. Website is reporting v2.x.x plugin version.
2. Plugin has been activated post-update. *
3. Config exists in the plugin settings.
4. API status checker reports OK.
5. /wp-content/tnc-toolbox-config/ folder is gone.

_(* Change to main plugin file name may result in deactivation)_

## 🖥️ (LOGS) Verifying ea-NGINX Actions

**If you'd like to ensure actions are firing properly at a deeper level:**

1. WHM > Tweak Settings > Logging > Enable cPanel API Log > On
2. WHM > Terminal > `tail -f /usr/local/cpanel/logs/api_log`
3. WordPress > Update a Post/Page, or explicitly Purge
4. WHM > Terminal > You should see the action fire!
5. WHM > Terminal > Ctrl+C to close the tail

Note: To do this, you require `root` access to the Server.

## 🆘 (HELP) Getting help with the Plugin

Include all info, screenshots, etc, to help our crew.

> [!NOTE]  
> Please raise an [Issue](https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/issues) on the GitHub Repository! 

## ✨ (FOSS) Contributing to the Plugin

Please feel free to contribute by submitting a PR with your proposed changes!

**The Network Crew Pty Ltd (TNC)**
