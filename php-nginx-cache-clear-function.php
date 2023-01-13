<?php

function clear_nginx_cache() {
    // Get the cPanel API token
    $api_token = 'YOUR_API_TOKEN_HERE';
    // Get the server hostname
    $server_hostname = 'YOUR_SERVER_HOSTNAME_HERE';
    // Get the cPanel username
    $cpanel_username = 'YOUR_CPANEL_USERNAME_HERE';
    // Build the headers for the request
    $headers = array(
        'Authorization' => 'cpanel '. $cpanel_username . ':' . $api_token,
    );
    // Build the body for the request
    $body = array(
        'parameter' => 'value',
    );
    // Build the URL for the request
    $url = 'https://' . $server_hostname . ':2083/execute/Module/function';
    // Make the request
    $response = wp_remote_post( $url, array(
        'headers' => $headers,
        'body' => $body,
    ) );
    // Check for a successful response
    if ( ! is_wp_error( $response ) && $response['response']['code'] === 200 ) {
        // Display a message to the user
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>Cache cleared successfully!</p>';
        echo '</div>';
    } else {
        // Display an error message to the user
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p>An error occurred while trying to clear the cache. Please try again later.</p>';
        echo '</div>';
    }
}

?>
