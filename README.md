# 🐆 TNC WP Toolbox (inc. NGINX Cache Purge)

### What the WordPress Plugin does:

- Allows you to purge the EA-NGINX User Cache from within WordPress
- Will expand to include other features as requested by community & clients

### System Requirements for the plugin:

- ~/.tnc/cp-api-key file must contain a cPanel API Token (on its own)
- Hosting Environment must be cPanel+WHM, running NGINX+Apache stack

Note: EA-NGINX (Reverse Proxy) by cPanel is supported, not "old school" implementations.

### Installing the Plugin (within WordPress CMS):

1. Download the latest version of the TNC WP Toolbox
2. Extract the repository on your computer/server
3. ZIP the tnc-wp-toolbox/ directory on its own
4. WP-Admin > Plugins > Add New > Upload ZIP

### Configuring the API Key/s site- or server-wide:

- For single-site usage, create ~/.tnc/ and ~/.tnc/cp-api-key
- Create a cPanel API Token and place it (nothing else) in that file
- ROOT: Use prepare-users-for-tnc-wp.sh to prepare all users on the server

https://docs.cpanel.net/knowledge-base/security/how-to-use-cpanel-api-tokens/#create-an-api-token

### Feature Requests, Improvements, Bug Reports, etc:

Please raise an Issue on the GitHub Repository! Include all info, screenshots, etc, to help our crew.

https://github.com/LEOPARD-host/TNC-WP-Toolbox/issues

**The Network Crew Pty Ltd (TNC)**
