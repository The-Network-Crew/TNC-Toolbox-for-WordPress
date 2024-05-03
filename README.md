# TNC Toolbox: Web Performance

**WP.org:** https://wordpress.org/plugins/tnc-toolbox

- Allows you to purge the EA-NGINX User Cache from within WordPress
- Automatically purges the cache when a post or page is updated in WP
- Allows you to disable or enable the NGINX User Cache from inside WP
- Only presents the options to enable/disable/configure for WP Admins
- For any action, redirects you back to the page you requested it from

**Will expand to include other features as requested by community & clients!**

## 🖥️ (PLUGIN) System Requirements

- cPanel API Key, cPanel Username & Server Hostname needed in Settings
- Hosting Environment must be cPanel+WHM, running NGINX + Apache stack!

> Note: EA-NGINX (Reverse Proxy) by cPanel is supported, not "old school" implementations.
> 
> CloudLinux Apache2NGINX (A2N) depends on ea-NGINX so shouldn't cause issues with TNC Toolbox.

## ⚙️ (INSTALL) How to Install the Plugin

1. Head to WP-Admin > Plugins, search for "TNC Toolbox"
2. Click "Install" for the resulting plugin (this one)
3. Once installed, click "Activate" to make it live
4. Done! Head to Settings > TNC Toolbox for config
5. Configure your API Key, Username & Hostname

## 🛠️ (DEPLOY) Loading it onto all WP sites

- **Prepare your server: ensure shell/CageFS/wp/fixperms/jq are installed and properly functional.**
- As root, execute the script and it should take care of the deployment process for your WP users.

**Scripts supplied:**

1. `script-deploy-tnc-toolbox.sh` :: Deploy to all WP sites found across a server
2. `script-patch-all-tnc-toolbox.sh` :: Update all copies found with latest version
3. `script-remove-conf-newlines.sh` :: Fix up config files to ensure no blank post-PHP

## 🆘 (HELP) Getting help with the Plugin

Please raise an [Issue](https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/issues) on the GitHub Repository! 

Include all info, screenshots, etc, to help our crew.

## ✨ (FOSS) Contributing to the Plugin

Please feel free to contribute by submitting a PR with your proposed changes!

**The Network Crew Pty Ltd (TNC)**
