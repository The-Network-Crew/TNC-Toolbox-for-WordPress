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
 * Plugin Settings Handler
 *
 * Manages plugin settings, database storage, and settings UI.
 *
 * @package    TNCTOOLBOX
 * @author     The Network Crew Pty Ltd
 * @since      2.0.0
 */
class TNC_Settings {
    /**
     * The plugin name
     * @var string
     */
    private $plugin_name;

    /**
     * Constructor.
     *
     * @since 2.0.0
     */
    public function __construct() {
        $this->plugin_name = TNCTOOLBOX_NAME;
        add_action('init', array($this, 'init_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_styles'));
        add_action('wp_ajax_tnc_test_slack_webhook', array($this, 'ajax_test_slack_webhook'));
    }

    // Enqueue admin styles for settings page
    public function enqueue_admin_styles($hook) {
        if ($hook !== 'settings_page_tnc-toolbox') {
            return;
        }

        wp_enqueue_style(
            'tnc-toolbox-admin',
            plugins_url('/assets/styles-config.css', dirname(__FILE__)),
            array(),
            TNCTOOLBOX_VERSION
        );
    }

    /**
     * Initialise settings after WordPress is loaded
     */
    public function init_settings() {
        if (!current_user_can('manage_options')) {
            return;
        }
        add_action('admin_menu', array($this, 'register_admin_menu'));
    }

    /**
     * Return the plugin name
     * @return string Plugin name
     */
    public function get_plugin_name() {
        return apply_filters('TNCTOOLBOX/settings/get_plugin_name', $this->plugin_name);
    }

    /**
     * Register the WP Admin settings menu entry
     */
    public function register_admin_menu() {
        add_options_page(
            'TNC Toolbox',
            'TNC Toolbox',
            'manage_options',
            'tnc-toolbox',
            array($this, 'handle_settings_page')
        );
    }

    /**
     * Route settings page-loads; save/render
     */
    public function handle_settings_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Verify nonce before processing settings
        $is_settings_save = isset($_POST['submit_tnc_toolbox_settings']) &&
            wp_verify_nonce($_POST['tnc_toolbox_settings_nonce'], 'tnc_toolbox_settings');

        // Process settings submission first so notices show on page load
        if ($is_settings_save) {
            $this->save_settings();
        }

        // Always render the page - either after save or on fresh load
        $this->render_settings_page();
    }

    /**
     * Save updated settings to database and test the connection
     */
    private function save_settings() {
        // Web stack selection
        $web_stack = sanitize_text_field($_POST['tnc_web_stack'] ?? 'nginx');
        TNC_Detection::set_web_stack($web_stack);

        // Sanitise inputs (always save these to preserve credentials)
        $api_key = sanitize_text_field($_POST['tnc_toolbox_api_key'] ?? '');
        $username = sanitize_text_field($_POST['tnc_toolbox_username'] ?? '');
        $hostname = sanitize_text_field($_POST['tnc_toolbox_server_hostname'] ?? '');
        $slack_webhook = sanitize_url($_POST['tnc_toolbox_slack_webhook'] ?? '');

        // Handle selective purge setting - use the POST value directly to avoid race conditions
        $selective_purge_enabled = isset($_POST['tnc_selective_purge']);
        TNC_Cache_Purge::set_enabled($selective_purge_enabled);

        // Save Slack webhook URL
        TNC_Slack_Alerts::store_webhook_url($slack_webhook);

        // Always save cPanel config (preserves credentials when switching stacks)
        TNC_cPanel_UAPI::store_config($username, $api_key, $hostname);

        // Handle based on web stack type
        if (TNC_Detection::is_litespeed_stack()) {
            // LiteSpeed stack - no cPanel connection needed
            TNC_Core::set_notice(
                'Settings saved. Using LiteSpeed stack - please install the <a href="https://wordpress.org/plugins/litespeed-cache/" target="_blank">LiteSpeed Cache plugin</a> for optimal performance.',
                'success'
            );
        } else {
            // Test connection if we have all required fields
            if (!empty($hostname) && !empty($username) && !empty($api_key)) {
                try {
                    $test_result = TNC_cPanel_UAPI::test_connection();
                    $message = $test_result['message'];
                    if ($selective_purge_enabled && $test_result['success']) {
                        $message .= ' Selective cache purging enabled.';
                    }
                    TNC_Core::set_notice(
                        $message,
                        $test_result['success'] ? 'success' : 'error'
                    );
                } catch (Exception $e) {
                    TNC_Core::set_notice(
                        'Connection test failed: ' . $e->getMessage(),
                        'error'
                    );
                }
            } else {
                TNC_Core::set_notice(
                    'Please fill in all cPanel API fields to enable cache management.',
                    'error'
                );
            }
        }
    }

    /**
     * Render the plugin's settings page
     */
    public function render_settings_page() {
        $stored_config = TNC_cPanel_UAPI::get_config();
        $web_stack = TNC_Detection::get_web_stack();
        $detected_server = TNC_Detection::detect_web_server();
        $is_litespeed = TNC_Detection::is_litespeed_stack();

        // Check for auto-detection mismatch and notify
        $auto_switch = TNC_Detection::auto_detect_and_switch_stack();
        if ($auto_switch) {
            $web_stack = $auto_switch['switched_to']; // Update for display
            $is_litespeed = true;
        }
        ?>
        <div class="wrap">
            <div class="tnc-toolbox-header">
                <h1><?php echo esc_html(get_admin_page_title()) . " v" . TNCTOOLBOX_VERSION; ?> (by <a href="https://tnc.works" target="_blank">TNC</a> & <a href="https://merlot.digital" target="_blank">Co.</a>)</h1>
                <p><strong>Configure your web stack settings. Supports ea-NGINX (cPanel) and LiteSpeed (OpenLiteSpeed/Enterprise).</strong><br>
                <?php if ($detected_server): ?>
                    Detected server: <code><?php echo esc_html(ucfirst($detected_server)); ?></code>
                <?php endif; ?>
                </p>
            </div>

            <?php if ($auto_switch): ?>
            <div class="notice notice-info is-dismissible">
                <p><strong>Web stack auto-switched!</strong> Detected LiteSpeed server but was configured for NGINX. Automatically switched to LiteSpeed stack. Your cPanel credentials are preserved if you need to switch back.</p>
            </div>
            <?php endif; ?>

            <div class="tnc-toolbox-form">
                <form method="post" action="">
                    <?php wp_nonce_field('tnc_toolbox_settings', 'tnc_toolbox_settings_nonce'); ?>

                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">
                                <label for="tnc_web_stack">Web Stack</label>
                                <p class="description">
                                    Select your hosting environment.
                                </p>
                            </th>
                            <td>
                                <select id="tnc_web_stack" name="tnc_web_stack" class="regular-text">
                                    <option value="nginx" <?php selected($web_stack, 'nginx'); ?>>
                                        ea-NGINX (cPanel/WHM)
                                    </option>
                                    <option value="litespeed" <?php selected($web_stack, 'litespeed'); ?>>
                                        LiteSpeed (OpenLS/Enterprise)
                                    </option>
                                </select>
                                <?php if ($detected_server && $detected_server !== $web_stack): ?>
                                    <p class="description" style="color: #d63638;">
                                        ⚠ Detected <strong><?php echo esc_html(ucfirst($detected_server)); ?></strong> but configured for <strong><?php echo esc_html(ucfirst($web_stack)); ?></strong>
                                    </p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <!-- LiteSpeed Stack Notice -->
                    <div id="tnc-litespeed-notice" class="tnc-toolbox-status" style="<?php echo $is_litespeed ? '' : 'display: none;'; ?> background: #e7f3ff; border-left-color: #0073aa;">
                        <h3 style="margin-top: 0;">🚀 LiteSpeed Stack</h3>
                        <p>For LiteSpeed servers (OpenLiteSpeed or LiteSpeed Enterprise), we recommend using the official <strong>LiteSpeed Cache</strong> plugin for optimal performance.</p>
                        <p><a href="https://wordpress.org/plugins/litespeed-cache/" target="_blank" class="button button-primary">Install LiteSpeed Cache Plugin</a></p>
                        <p><small>The LSCache plugin provides built-in cache management tailored for LiteSpeed servers. See <a href="https://docs.openlitespeed.org/config/lscache/" target="_blank">LiteSpeed Cache documentation</a> for configuration details.</small></p>
                    </div>

                    <!-- NGINX Stack Settings (cPanel UAPI) -->
                    <div id="tnc-nginx-settings" style="<?php echo $is_litespeed ? 'display: none;' : ''; ?>">
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row">
                                    <label for="tnc_toolbox_api_key">cPanel API Token</label>
                                    <p class="description">
                                        Key only, not the name. <br><a href="https://docs.cpanel.net/cpanel/security/manage-api-tokens-in-cpanel/" target="_blank">View documentation</a>.
                                    </p>
                                </th>
                                <td>
                                    <input type="text" id="tnc_toolbox_api_key" name="tnc_toolbox_api_key"
                                        value="<?php echo esc_attr($stored_config['api_key']); ?>"
                                        placeholder="Enter your cPanel API token"
                                        class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="tnc_toolbox_username">cPanel Username</label>
                                    <p class="description">Plain-text username. <br>Not the API user.</p>
                                </th>
                                <td>
                                    <input type="text" id="tnc_toolbox_username" name="tnc_toolbox_username"
                                        value="<?php echo esc_attr($stored_config['username']); ?>"
                                        placeholder="Enter your cPanel username"
                                        class="regular-text" />
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="tnc_toolbox_server_hostname">Server Hostname</label>
                                    <p class="description">FQDN of Server, like:<br><code>server.example.com</code></p>
                                </th>
                                <td>
                                    <input type="text" id="tnc_toolbox_server_hostname" name="tnc_toolbox_server_hostname"
                                        value="<?php echo esc_attr($stored_config['hostname']); ?>"
                                        placeholder="Enter your server hostname"
                                        class="regular-text" />
                                </td>
                            </tr>
                            <?php $status = TNC_Cache_Purge::get_status( true ); // Force recheck on settings page ?>
                            <tr>
                                <th scope="row">
                                    <label for="tnc_selective_purge">Selective Purging?</label>
                                    <p class="description">Requires Module & Config:<br><code>ea-nginx-cache-purge</code></p>
                                </th>
                                <td>
                                    <label>
                                        <input type="checkbox" id="tnc_selective_purge" name="tnc_selective_purge"
                                               <?php checked($status['enabled']); ?> />
                                        Only purge affected URLs when content changes.<br>If disabled, the entire user cache is purged instead.
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">
                                <label for="tnc_toolbox_slack_webhook">Slack Webhook URL</label>
                                <p class="description">For mail failure alerts.<br><a href="https://api.slack.com/messaging/webhooks" target="_blank">Create Webhook</a>.</p>
                            </th>
                            <td>
                                <input type="url" id="tnc_toolbox_slack_webhook" name="tnc_toolbox_slack_webhook"
                                    value="<?php echo esc_attr(TNC_Slack_Alerts::get_webhook_url()); ?>"
                                    placeholder="https://hooks.slack.com/services/..."
                                    class="regular-text" />
                                <button type="button" id="tnc_test_slack_webhook" class="button button-secondary">Test Webhook</button>
                                <span id="tnc_slack_test_result" style="margin-left: 10px;"></span>
                                <p class="description">Receive alerts when WordPress fails to send emails.</p>
                            </td>
                        </tr>
                    </table>

                    <p class="submit">
                        <input type="submit" name="submit_tnc_toolbox_settings" class="button button-primary"
                               value="<?php echo esc_attr__('Save Settings'); ?>" />
                    </p>

                </form>

                <?php if (!$is_litespeed && !empty($stored_config['hostname']) && !empty($stored_config['username']) && !empty($stored_config['api_key'])): ?>
                    <?php
                    $quota = TNC_cPanel_UAPI::make_api_request('Quota/get_quota_info');
                    if ($quota['success'] && isset($quota['data']['megabytes_used'])):
                    ?>
                        <div class="tnc-toolbox-status success">
                            <h3>cPanel API Connected</h3>
                            <br>
                            <strong>Inodes</strong>: <code><?php echo number_format($quota['data']['inodes_used']); ?></code> of <code><?php echo number_format($quota['data']['inode_limit']); ?></code><br>
                            <strong>Disk</strong>: <code><?php echo number_format($quota['data']['megabytes_used']); ?>MB</code> of <code><?php echo number_format($quota['data']['megabyte_limit']); ?>MB</code></p>
                            <br><small>Note: A limit of 0 indicates there is no set limit.</small>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Toggle visibility based on web stack selection
            function toggleStackSettings() {
                var stack = $('#tnc_web_stack').val();
                if (stack === 'litespeed') {
                    $('#tnc-litespeed-notice').show();
                    $('#tnc-nginx-settings').hide();
                } else {
                    $('#tnc-litespeed-notice').hide();
                    $('#tnc-nginx-settings').show();
                }
            }

            $('#tnc_web_stack').on('change', toggleStackSettings);
            toggleStackSettings(); // Initial state

            $('#tnc_test_slack_webhook').on('click', function() {
                var $button = $(this);
                var $result = $('#tnc_slack_test_result');
                var webhookUrl = $('#tnc_toolbox_slack_webhook').val();
                
                if (!webhookUrl) {
                    $result.html('<span style="color: #dc3232;">Please enter a webhook URL first.</span>');
                    return;
                }
                
                $button.prop('disabled', true).text('Testing...');
                $result.html('');
                
                $.post(ajaxurl, {
                    action: 'tnc_test_slack_webhook',
                    webhook_url: webhookUrl,
                    nonce: '<?php echo wp_create_nonce('tnc_test_slack_webhook'); ?>'
                }, function(response) {
                    if (response.success) {
                        $result.html('<span style="color: #46b450;">✓ ' + response.data.message + '</span>');
                    } else {
                        $result.html('<span style="color: #dc3232;">✗ ' + response.data.message + '</span>');
                    }
                }).fail(function() {
                    $result.html('<span style="color: #dc3232;">✗ Request failed</span>');
                }).always(function() {
                    $button.prop('disabled', false).text('Test Webhook');
                });
            });
        });
        </script>
        <?php
    }

    /**
     * AJAX handler for testing Slack webhook
     */
    public function ajax_test_slack_webhook() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'tnc_test_slack_webhook')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
        }

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
        }

        // Get and validate webhook URL
        $webhook_url = sanitize_url($_POST['webhook_url'] ?? '');
        if (empty($webhook_url)) {
            wp_send_json_error(array('message' => 'No webhook URL provided'));
        }

        // Temporarily store the URL for testing
        TNC_Slack_Alerts::store_webhook_url($webhook_url);

        // Run the test
        $result = TNC_Slack_Alerts::test_webhook();

        if ($result['success']) {
            wp_send_json_success(array('message' => $result['message']));
        } else {
            wp_send_json_error(array('message' => $result['message']));
        }
    }
}
