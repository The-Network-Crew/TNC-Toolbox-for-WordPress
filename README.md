# 🐆 TNC Toolbox for WP (inc. NGINX Cache actions)

**WP.org:** https://wordpress.org/plugins/tnc-toolbox

- Allows you to purge the EA-NGINX User Cache from within WordPress
- Allows you to disable or enable the NGINX User Cache from inside WP
- For any action, redirects you back to the page you requested it from

Will expand to include other features as requested by community & clients!

## 🖥️ (PLUGIN) System Requirements 🖥️

- cPanel API Key, cPanel Username & Server Hostname needed in Settings
- Hosting Environment must be cPanel+WHM, running NGINX + Apache stack!

> Note: EA-NGINX (Reverse Proxy) by cPanel is supported, not "old school" implementations.

## ⚙️ (INSTALL) How to Install the Plugin ⚙️

1. Head to WP-Admin > Plugins, search for "TNC Toolbox"
2. Click "Install" for the resulting plugin (this one)
3. Once installed, click "Activate" to make it live
4. Done! Head to Settings > TNC Toolbox for config
5. Configure your API Key, Username & Hostname

## 🛠️ (DEPLOY) Loading it onto all WP sites 🛠️

- Prepare your server: ensure shell/CageFS/wp/fixperms/jq are installed and properly functional.
- As root, execute the script and it should take care of the deployment process for your WP users.

## 🆕 (OLD) Updating to v1.3.1 onwards > 🆕

Note: This will change the config file location, and you will need to re-enter accordingly.

/wp-content/plugins/tnc-toolbox/config/* becomes /wp-content/tnc-toolbox-config/*

## 🆘 (HELP) Getting help with the Plugin 🆘

Please raise an [Issue](https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/issues) on the GitHub Repository! 

Include all info, screenshots, etc, to help our crew.

## ✨ (FOSS) Contributing to the Plugin ✨

Please feel free to contribute by submitting a PR with your proposed changes!

**The Network Crew Pty Ltd (TNC)**
