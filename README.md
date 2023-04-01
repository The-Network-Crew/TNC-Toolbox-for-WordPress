# 🐆 TNC Toolbox for WP (inc. NGINX Cache actions)

https://wordpress.org/plugins/tnc-toolbox

### What the WordPress Plugin does:

- Allows you to purge the EA-NGINX User Cache from within WordPress
- Allows you to disable or enable the NGINX User Cache from inside WP
- For any action, redirects you back to the page you requested it from

Will expand to include other features as requested by community & clients.

### System Requirements for the Plugin:

- cPanel API Key, cPanel Username & Server Hostname needed in Settings
- Hosting Environment must be cPanel+WHM, running NGINX + Apache stack!

Note: EA-NGINX (Reverse Proxy) by cPanel is supported, not "old school" implementations.

### Installing the Plugin (within WordPress CMS):

1. Head to WP-Admin > Plugins, search for "TNC Toolbox"
2. Click "Install" for the resulting plugin (this one)
3. Once installed, click "Activate" to make it live
4. Done! Head to Settings > TNC Toolbox for config
5. Configure your API Key, Username & Hostname

### Using the script to deploy the plugin server-wide:

- Prepare your server: ensure shell/CageFS/wp/fixperms/jq are installed and properly functional.
- As root, execute the script and it should take care of the deployment process for your WP users.

### Feature Requests, Improvements, Bug Reports, etc:

Please raise an Issue on the GitHub Repository! Include all info, screenshots, etc, to help our crew.

https://github.com/LEOPARD-host/WordPress-Toolbox-by-TNC/issues

**The Network Crew Pty Ltd (TNC)**
