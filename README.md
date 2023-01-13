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

### ChatGPT's input on how to approach build:

To code a button in the WordPress Admin top menu bar that fires an NGINX Cache clear via the cPanel API, you would need to use the `admin_bar_menu` hook to add the button to the menu, and then use the cPanel API to clear the cache. 

Here's a general overview of the steps you would need to take:

1. Use the `admin_bar_menu` hook to add a button to the WordPress Admin top menu bar. You can use the `add_node()` method to add the button and the `add_menu()` method to add a sub-menu to the button.
2. When the button is clicked, use the cPanel API to clear the cache. You will need to authenticate with the cPanel API in order to clear the cache. You can use cPanel API token to authenticate.
3. To use the cPanel API, you will need to make an HTTP request to the API endpoint for the `clear_cache` operation. You can use the `wp_remote_post()` function to make the request.
4. In the HTTP request, you will need to include your cPanel API token in the headers, so that the API knows that you are authenticated.
5. Once the cache is cleared, you can use the `admin_notices` action to display a message to the user that the cache has been cleared.
