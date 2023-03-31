# 🐆 TNC Toolbox for WP (inc. NGINX Cache actions)

https://wordpress.org/plugins/tnc-toolbox

### What the WordPress Plugin does:

- Allows you to purge the EA-NGINX User Cache from within WordPress
- Allows you to disable or enable the NGINX User Cache from inside WP
- For any action, redirects you back to the page you requested it from
- Will expand to include other features as requested by community & clients

### System Requirements for the plugin:

- cPanel API Key, cPanel Username & Server Hostname needed in Settings
- Hosting Environment must be cPanel+WHM, running NGINX + Apache stack!
- Bash script needs: WP-in-functional-CageFS; shell->on for each cP user

Note: EA-NGINX (Reverse Proxy) by cPanel is supported, not "old school" implementations.

### Installing the Plugin (within WordPress CMS):

1. Head to WP-Admin > Plugins, search for "TNC Toolbox"
2. Click "Install" for the resulting plugin (this one)
3. Once installed, click "Activate" to make it live
4. Done! Head to Settings > TNC Toolbox for config
5. Configure your API Key, Username & Hostname

Server-side: Use the bash script (read it firstly) to deploy the plugin server-wide!

As always, restrict it suitably - add a WP-present check, etc - and run at your own risk.

### Using the script to prepare your sites server-wide:

- Please note, the script requires amendments before it will work reliably again (code changes)
- Target is to complete this within April 2023; its pre-reqs will remain the same (CageFS & so on)

### Feature Requests, Improvements, Bug Reports, etc:

Please raise an Issue on the GitHub Repository! Include all info, screenshots, etc, to help our crew.

https://github.com/LEOPARD-host/WordPress-Toolbox-by-TNC/issues

**The Network Crew Pty Ltd (TNC)**
