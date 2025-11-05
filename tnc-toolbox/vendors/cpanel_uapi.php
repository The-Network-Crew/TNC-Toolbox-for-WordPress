<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * cPanel API Request Handler
 *
 * Handles all communication with cPanel's UAPI endpoints.
 * 
 * @package TNCTOOLBOX
 * @subpackage Vendors
 * @author The Network Crew Pty Ltd
 * @since 2.0.0
 */

class TNC_cPanel_UAPI {
    /**
     * Plugin options name in WP database
     */
    const OPTIONS_KEY = 'tnc_cpanel_uapi';

    /**
     * Get stored API configuration
     *
     * @return array|false API config, or false if not set
     */
    public static function get_config() {
        $config = get_option(self::OPTIONS_KEY);
        if (empty($config) || !is_array($config)) {
            return false;
        }
        return $config;
    }

    /**
     * Store API configuration in WordPress options
     *
     * @param string $username cPanel username
     * @param string $api_key  cPanel UAPI key
     * @param string $hostname Server hostname
     * @return bool True on success, false on failure
     */
    public static function store_config($username, $api_key, $hostname) {
        $config = [
            'username' => sanitize_text_field($username),
            'api_key' => sanitize_text_field($api_key),
            'hostname' => sanitize_text_field($hostname)
        ];

        return update_option(self::OPTIONS_KEY, $config, false);
    }

    /**
     * Make a request to the cPanel API
     *
     * @param string $endpoint API endpoint (e.g. 'NginxCaching/clear_cache')
     * @param array $body Request body parameters
     * @return array Response data array with 'success', 'message', and optional 'data'
     */
    public static function make_api_request($endpoint, $body = []) {
        $config = self::get_config();
        if (!$config) {
            return [
                'success' => false,
                'message' => 'API configuration is not set. Please configure the plugin settings.'
            ];
        }

        // Prepare request
        $headers = [
            'Authorization' => 'cpanel ' . $config['username'] . ':' . $config['api_key']
        ];

        // Make the request
        $url = 'https://' . $config['hostname'] . ':2083/execute/' . $endpoint;
        $response = wp_remote_post($url, [
            'headers' => $headers,
            'body' => $body,
            'timeout' => 30,
            'sslverify' => true
        ]);

        // Handle common error cases
        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => 'Connection Error: ' . $response->get_error_message()
            ];
        }

        // Parse response
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $response_data = json_decode($response_body, true);

        // Handle API error responses
        if ($response_code !== 200) {
            $error_msg = !empty($response_data['errors']) ? implode(', ', $response_data['errors']) : 'Unknown error occurred';
            return [
                'success' => false,
                'message' => "API Error (Code {$response_code}): {$error_msg}"
            ];
        }

        // Success case with data
        return [
            'success' => true,
            'message' => !empty($response_data['messages']) ? implode(', ', $response_data['messages']) : 'Request successful',
            'data' => $response_data['data'] ?? null
        ];
    }

    /**
     * Test API connection by retrieving quota info
     *
     * @return array Response with success status, message, and quota info if successful
     */
    public static function test_connection() {
        $response = self::make_api_request('Quota/get_quota_info');
        
        if (!$response['success']) {
            return $response;
        }

        if (isset($response['data']['megabytes_used'])) {
            return [
                'success' => true,
                'message' => sprintf(
                    'API Connected. Disk Usage: %s MB',
                    number_format($response['data']['megabytes_used'])
                ),
                'data' => $response['data']
            ];
        }

        return [
            'success' => false,
            'message' => 'API appears to have connected, but no data retrieved?'
        ];
    }

    /**
     * Helper to set admin notice transient
     */
    public static function set_notice($message, $type = 'error') {
        $transient_key = $type === 'error' ? 
            'tnctoolbox_uapi_action_error' : 
            'tnctoolbox_uapi_action_success';
        
        set_transient($transient_key, $message, 60);
    }

    /**
     * (REQUEST WRAPPER) Purge NGINX cache
     */
    public static function purge_cache() {
        $response = self::make_api_request('NginxCaching/clear_cache');
        self::set_notice(
            $response['success'] ? 
            'TNC Toolbox: NGINX Cache has been Purged!' : 
            'TNC Toolbox: ' . $response['message'],
            $response['success'] ? 'success' : 'error'
        );
        return $response['success'];
    }

    /**
     * (REQUEST WRAPPER) Disable NGINX cache
     */
    public static function disable_cache() {
        $response = self::make_api_request('NginxCaching/disable_cache');
        self::set_notice(
            $response['success'] ? 
            'TNC Toolbox: NGINX Cache has been Disabled.' : 
            'TNC Toolbox: ' . $response['message'],
            $response['success'] ? 'success' : 'error'
        );
        return $response['success'];
    }

    /**
     * (REQUEST WRAPPER) Enable NGINX cache
     */
    public static function enable_cache() {
        $response = self::make_api_request('NginxCaching/enable_cache');
        self::set_notice(
            $response['success'] ? 
            'TNC Toolbox: NGINX Cache has been Enabled.' : 
            'TNC Toolbox: ' . $response['message'],
            $response['success'] ? 'success' : 'error'
        );
        return $response['success'];
    }
}