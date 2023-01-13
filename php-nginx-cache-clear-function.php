<?php

function clear_nginx_cache() {
    // Get the cPanel username
    $cpanel_username = get_current_user();
    // Get the API token
    $api_token = file_get_contents("/home/".$cpanel_username."/.tnc/cp-api-key");
    // Get the server hostname
    $server_hostname = gethostname();
    // Build the headers for the request
    $headers = array(
        'Authorization' => 'cpanel '. $cpanel_username . ':' . $api_token,
    );
    // Build the body for the request
    $body = array(
        'parameter' => 'value',
    );
    // Build the URL for the request
    $url = 'https://' . $server_hostname . ':2083/execute/NginxCaching/clear_cache';
    // Make the request
    $response = wp_remote_post( $url, array(
        'headers' => $headers,
        'body' => $body,
    ) );
    // Check for a successful response
    if ( ! is_wp_error( $response ) && $response['response']['code'] === 200 ) {
        // Display a message to the user
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p>NGINX Cache cleared successfully!</p>';
        echo '</div>';
    } else {
        // Display an error message to the user
        echo '<div class="notice notice-error is-dismissible">';
        echo '<p>An error occurred while trying to clear the NGINX Cache. Please contact us if this persists in 60 minutes.</p>';
        echo '</div>';
    }
}


?>
