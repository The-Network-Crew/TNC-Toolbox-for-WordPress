# TNC WP Toolbox (inc. NGINX Cache Purge)

### What the WordPress Plugin does for clients:

- Allows them to purge the NGINX Cache from within WordPress
- Will expand to include other features as requested by them

### System Requirements for the TNC WP Toolbox:

- /home/USERNAME/.tnc/cp-api-key file must contain a cPanel API Token
- Hosting Environment must be cPanel+WHM, running NGINX+Apache stack

### Docs for Function & Location of key feature:

- https://developer.wordpress.org/reference/hooks/admin_bar_menu/ (where)
- https://api.docs.cpanel.net/openapi/cpanel/operation/clear_cache/ (what)
- https://developer.wordpress.org/reference/functions/wp_remote_post/ (how)
- https://developer.wordpress.org/reference/classes/wp_admin_bar/add_node/ (button)
- https://docs.cpanel.net/knowledge-base/security/how-to-use-cpanel-api-tokens/ (auth)

### Official Feature Request - to avoid API auth:

We asked cPanel to build this and apparently WordPress Toolkit's team might do it.

https://features.cpanel.net/topic/22571-nginx-user-cache-simple-plugin-to-purge-from-within-wordpress-wp
