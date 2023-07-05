# 🐆 TNC Toolbox for WP (inc. NGINX Cache actions)

https://wordpress.org/plugins/tnc-toolbox

## ❓ WHAT THE TNC TOOLBOX DOES ❓

- Allows you to purge the EA-NGINX User Cache from within WordPress
- Allows you to disable or enable the NGINX User Cache from inside WP
- For any action, redirects you back to the page you requested it from

Will expand to include other features as requested by community & clients.

## 🖥️ PLUGIN SYSTEM REQUIREMENTS 🖥️

- cPanel API Key, cPanel Username & Server Hostname needed in Settings
- Hosting Environment must be cPanel+WHM, running NGINX + Apache stack!

Note: EA-NGINX (Reverse Proxy) by cPanel is supported, not "old school" implementations.

## ⚙️ INSTALLING THE TNC TOOLBOX ⚙️

1. Head to WP-Admin > Plugins, search for "TNC Toolbox"
2. Click "Install" for the resulting plugin (this one)
3. Once installed, click "Activate" to make it live
4. Done! Head to Settings > TNC Toolbox for config
5. Configure your API Key, Username & Hostname

## 🛠️ DEPLOYING IT SERVER-WIDE 🛠️

- Prepare your server: ensure shell/CageFS/wp/fixperms/jq are installed and properly functional.
- As root, execute the script and it should take care of the deployment process for your WP users.

## 🆕 UPGRADING TO V1.3.1 > 🆕

Note: This will change the config file location, and you will need to re-enter accordingly.

/wp-content/plugins/tnc-toolbox/config/* becomes /wp-content/tnc-toolbox-config/*

## ✨ CONTRIBUTING ✨

Please raise an Issue on the GitHub Repository! Include all info, screenshots, etc, to help our crew.

https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress/issues

**The Network Crew Pty Ltd (TNC)**
