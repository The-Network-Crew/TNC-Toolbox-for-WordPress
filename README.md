# TNC WP Toolbox (inc. NGINX Cache Purge)

We asked cPanel to build this and apparently WordPress Toolkit's team might do it.

https://features.cpanel.net/topic/22571-nginx-user-cache-simple-plugin-to-purge-from-within-wordpress-wp

### Docs for Function & Location of key feature:

- https://api.docs.cpanel.net/openapi/cpanel/operation/clear_cache/
- https://docs.cpanel.net/knowledge-base/security/how-to-use-cpanel-api-tokens/
- https://developer.wordpress.org/reference/hooks/admin_bar_menu/ (in the plugin already)

### Example call to OpenAPI to purge cache:

- `curl -H'Authorization: cpanel username:APITOKEN' 'https://example.com:2083/execute/NginxCaching/clear_cache'`
- Need to programatically create a cPanel API Token for each user, store to ~/.tnc/cp-api-token and use?
- That way, plugin can find automatically, rather than also needing plugin configuration?

### Developer Docs & Boilerplate Plugin tool:

- https://developer.wordpress.org/plugins/
- https://pluginplate.com/plugin-boilerplate/
