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
 * Slack Alerts Handler
 *
 * Sends alerts to Slack when certain WordPress events occur.
 *
 * @package    TNCTOOLBOX
 * @author     The Network Crew Pty Ltd
 * @since      2.1.2
 */
class TNC_Slack_Alerts {
    /**
     * Option name for storing the Slack webhook URL
     */
    const OPTION_WEBHOOK_URL = 'tnc_toolbox_slack_webhook';

    /**
     * Constructor - register hooks
     */
    public function __construct() {
        // Hook into wp_mail_failed to send Slack alerts
        add_action('wp_mail_failed', array($this, 'handle_mail_failed'));
    }

    /**
     * Get the stored Slack webhook URL
     *
     * @return string The webhook URL or empty string if not set
     */
    public static function get_webhook_url() {
        return get_option(self::OPTION_WEBHOOK_URL, '');
    }

    /**
     * Store the Slack webhook URL
     *
     * @param string $url The webhook URL to store
     * @return bool True if option was updated, false otherwise
     */
    public static function store_webhook_url($url) {
        return update_option(self::OPTION_WEBHOOK_URL, sanitize_url($url));
    }

    /**
     * Handle wp_mail_failed hook
     *
     * @param WP_Error $wp_error The WP_Error object containing mail failure details
     */
    public function handle_mail_failed($wp_error) {
        $webhook_url = self::get_webhook_url();

        // Only proceed if webhook URL is configured
        if (empty($webhook_url)) {
            return;
        }

        // Extract error details
        $error_message = $wp_error->get_error_message();
        $error_data = $wp_error->get_error_data();

        // Build recipient info
        $to = '';
        if (isset($error_data['to']) && is_array($error_data['to'])) {
            $to = implode(', ', $error_data['to']);
        } elseif (isset($error_data['to'])) {
            $to = $error_data['to'];
        }

        // Get subject if available
        $subject = isset($error_data['subject']) ? $error_data['subject'] : 'Unknown';

        // Build Slack message
        $site_name = get_bloginfo('name');
        $site_url = get_bloginfo('url');

        $message = $this->build_slack_message($site_name, $site_url, $error_message, $to, $subject);

        // Send to Slack
        $this->send_slack_message($webhook_url, $message);
    }

    /**
     * Build the Slack message payload
     *
     * @param string $site_name The WordPress site name
     * @param string $site_url The WordPress site URL
     * @param string $error_message The error message from wp_mail
     * @param string $to The recipient(s) of the failed email
     * @param string $subject The subject of the failed email
     * @return array The Slack message payload
     */
    private function build_slack_message($site_name, $site_url, $error_message, $to, $subject) {
        return array(
            'blocks' => array(
                array(
                    'type' => 'header',
                    'text' => array(
                        'type' => 'plain_text',
                        'text' => '⚠️ WordPress Mail Failed',
                        'emoji' => true
                    )
                ),
                array(
                    'type' => 'section',
                    'fields' => array(
                        array(
                            'type' => 'mrkdwn',
                            'text' => "*Site:*\n<{$site_url}|{$site_name}>"
                        ),
                        array(
                            'type' => 'mrkdwn',
                            'text' => "*Time:*\n" . current_time('Y-m-d H:i:s')
                        )
                    )
                ),
                array(
                    'type' => 'section',
                    'fields' => array(
                        array(
                            'type' => 'mrkdwn',
                            'text' => "*To:*\n" . ($to ?: 'Unknown')
                        ),
                        array(
                            'type' => 'mrkdwn',
                            'text' => "*Subject:*\n" . $subject
                        )
                    )
                ),
                array(
                    'type' => 'section',
                    'text' => array(
                        'type' => 'mrkdwn',
                        'text' => "*Error:*\n```{$error_message}```"
                    )
                ),
                array(
                    'type' => 'context',
                    'elements' => array(
                        array(
                            'type' => 'mrkdwn',
                            'text' => 'Sent by TNC Toolbox v' . TNCTOOLBOX_VERSION
                        )
                    )
                )
            )
        );
    }

    /**
     * Send a message to Slack via webhook
     *
     * @param string $webhook_url The Slack webhook URL
     * @param array $message The message payload
     * @return bool True if message was sent successfully
     */
    private function send_slack_message($webhook_url, $message) {
        $response = wp_remote_post($webhook_url, array(
            'body' => wp_json_encode($message),
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            // Log error but don't create a loop by triggering another alert
            error_log('TNC Toolbox: Failed to send Slack alert - ' . $response->get_error_message());
            return false;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        return ($response_code >= 200 && $response_code < 300);
    }

    /**
     * Test the Slack webhook configuration
     *
     * @return array Result with 'success' and 'message' keys
     */
    public static function test_webhook() {
        $webhook_url = self::get_webhook_url();

        if (empty($webhook_url)) {
            return array(
                'success' => false,
                'message' => 'No Slack webhook URL configured.'
            );
        }

        $test_message = array(
            'blocks' => array(
                array(
                    'type' => 'section',
                    'text' => array(
                        'type' => 'mrkdwn',
                        'text' => '✅ *TNC Toolbox Test*: Slack webhook is working correctly for *' . get_bloginfo('name') . '*'
                    )
                )
            )
        );

        $response = wp_remote_post($webhook_url, array(
            'body' => wp_json_encode($test_message),
            'headers' => array(
                'Content-Type' => 'application/json'
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => 'Slack test failed: ' . $response->get_error_message()
            );
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code >= 200 && $response_code < 300) {
            return array(
                'success' => true,
                'message' => 'Slack webhook test successful!'
            );
        }

        return array(
            'success' => false,
            'message' => 'Slack test failed with HTTP ' . $response_code
        );
    }
}

// Initialise Slack Alerts
new TNC_Slack_Alerts();
