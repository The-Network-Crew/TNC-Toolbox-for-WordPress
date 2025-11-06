<?php

/*
    TNC Toolbox: Web Performance (for WordPress)
    
    Copyright (C) The Network Crew Pty Ltd (TNC)
    PO Box 3113 Uki 2484 NSW Australia https://tnc.works

    https://github.com/The-Network-Crew/TNC-Toolbox-for-WordPress

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

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
     * Plugin options names in WP database
     */
    const USERNAME_KEY = 'tnc_cpanel_username';
    const API_KEY_KEY = 'tnc_cpanel_api_key';
    const HOSTNAME_KEY = 'tnc_cpanel_hostname';

    /**
     * Get stored API configuration
     *
     * @return array|false API config, or false if not set
     */
    public static function get_config() {
        // Cleanly return if user can't edit post
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        $username = get_option(self::USERNAME_KEY);
        $api_key = get_option(self::API_KEY_KEY);
        $hostname = get_option(self::HOSTNAME_KEY);

        if (empty($username) && empty($api_key) && empty($hostname)) {
            return false;
        }

        return [
            'username' => $username,
            'api_key' => $api_key,
            'hostname' => $hostname
        ];
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
        if (!current_user_can('manage_options')) {
            return false;
        }

        $username = sanitize_text_field($username);
        $api_key = sanitize_text_field($api_key);
        $hostname = sanitize_text_field($hostname);

        $saved = true;
        $saved &= update_option(self::USERNAME_KEY, $username);
        $saved &= update_option(self::API_KEY_KEY, $api_key);
        $saved &= update_option(self::HOSTNAME_KEY, $hostname);

        return $saved;
    }

    /**
     * Make a request to the cPanel API
     *
     * @param string $endpoint API endpoint (e.g. 'NginxCaching/clear_cache')
     * @param array $body Request body parameters
     * @return array Response data array with 'success', 'message', and optional 'data'
     */
    public static function make_api_request($endpoint, $body = []) {
        try {
            $config = self::get_config();
            if (!$config) {
                self::log_error('API configuration not set');
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
            $args = [
                'headers' => $headers,
                'body' => $body,
                'timeout' => 30,
                'sslverify' => true
            ];

            self::log_error("Attempting API request to: " . $url);
            $response = wp_remote_post($url, $args);

            // Handle common error cases
            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
                self::log_error("WP_Error: " . $error_message);
                return [
                    'success' => false,
                    'message' => 'Connection Error: ' . $error_message,
                    'error' => $error_message
                ];
            }

            // Parse response
            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = wp_remote_retrieve_body($response);
            
            if (empty($response_body)) {
                self::log_error("Empty response body received");
                return [
                    'success' => false,
                    'message' => 'Empty response from server',
                    'error' => 'Empty response body'
                ];
            }

            $response_data = json_decode($response_body, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                self::log_error("JSON Parse Error: " . json_last_error_msg());
                self::log_error("Response Body: " . substr($response_body, 0, 1000));
                return [
                    'success' => false,
                    'message' => "API Error: " . substr($response_body, 0, 1000),
                    'error' => json_last_error_msg()
                ];
            }

            // Handle API error responses
            if ($response_code !== 200) {
                $error_msg = !empty($response_data['errors']) ? implode(', ', $response_data['errors']) : 'Unknown error occurred';
                self::log_error("API Error (" . $response_code . "): " . $error_msg);
                return [
                    'success' => false,
                    'message' => "API Error (Code " . $response_code . "): " . $error_msg,
                    'error' => $error_msg
                ];
            }

            return [
                'success' => true,
                'message' => !empty($response_data['messages']) ? implode(', ', $response_data['messages']) : 'Request successful',
                'data' => $response_data['data'] ?? null
            ];

        } catch (Exception $e) {
            self::log_error("Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Internal Error: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Log error message if WP_DEBUG is enabled
     */
    private static function log_error($message) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('TNC Toolbox UAPI: ' . $message);
        }
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
                    'Saved Config & Tested OK. Disk Usage: %s MB',
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

        // Set the transient message
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