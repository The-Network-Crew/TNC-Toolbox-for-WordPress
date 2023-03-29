# 🐆 TNC Toolbox for WP (inc. NGINX Cache actions)

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

1. Download the latest version of the TNC WP Toolbox
2. Extract the repository on your computer/server
3. ZIP the tnc-toolbox/ directory **on its own**
4. WP-Admin > Plugins > Add New > Upload ZIP

Server-side: Use the bash script (read it firstly) to deploy the plugin server-wide!

As always, restrict it suitably - add a WP-present check, etc - and run at your own risk.

### Configuring the API Key/s site- or server-wide:

- For single-site usage, configure Settings > TNC Toolbox fields
- ROOT: prep-users-and-install-tnc-wp.sh to prep and install for all sites
- ROOT: Make sure you amend the "wp" URL as described in the script, to grab ZIP

https://docs.cpanel.net/knowledge-base/security/how-to-use-cpanel-api-tokens/#create-an-api-token

### Feature Requests, Improvements, Bug Reports, etc:

Please raise an Issue on the GitHub Repository! Include all info, screenshots, etc, to help our crew.

https://github.com/LEOPARD-host/WordPress-Toolbox-by-TNC/issues

**The Network Crew Pty Ltd (TNC)**
