<?php

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;

/**
 * Core Plugin Functionality
 * 
 * Handles core plugin features like cache management and toolbar integration.
 *
 * @package    TNCWPTBOX
 * @author     The Network Crew Pty Ltd
 * @since      2.0.0
 */
class TNC_Core {
    /**
     * Constructor to set up WordPress hooks
     */
    function __construct() {
        // Register basic hooks
        add_action('init', array($this, 'add_hooks'));
        
        // Register capability-dependent hooks after WP is fully loaded
        add_action('admin_init', array($this, 'add_capability_dependent_hooks'));
    }

    /**
     * Register WordPress hooks and filters
     */
    public function add_hooks() {
        // Plugin row links
        add_filter('plugin_action_links_' . TNCWPTBOX_PLUGIN_BASE, array($this, 'add_plugin_action_link'), 20);

        // Admin bar customization
        add_action('admin_enqueue_scripts', array($this, 'enqueue_custom_css'));
        add_action('admin_bar_menu', array($this, 'add_parent_menu_entry'), 99);
        add_action('admin_bar_menu', array($this, 'add_cache_purge_button'), 100);

        // Cache purge actions
        add_action('admin_post_nginx_cache_purge', array($this, 'nginx_cache_purge'));
        add_action('post_updated', array($this, 'purge_cache_on_update'), 10, 3);
        add_action('_core_updated_successfully', array($this, 'nginx_cache_purge'));

        // Notices
        add_action('admin_notices', array($this, 'display_admin_notices'));
    }

    /**
     * Register capability dependent hooks
     */
    public function add_capability_dependent_hooks() {
        if (current_user_can('update_core')) {
            add_action('admin_bar_menu', array($this, 'add_cache_off_button'), 100);
            add_action('admin_post_nginx_cache_off', array($this, 'nginx_cache_off'));
            add_action('admin_bar_menu', array($this, 'add_cache_on_button'), 100);
            add_action('admin_post_nginx_cache_on', array($this, 'nginx_cache_on'));
        }
    }

    /**
     * Add plugin action links
     */
    public function add_plugin_action_link($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            admin_url('options-general.php?page=tnc_toolbox'),
            __('Settings', 'tnc-toolbox')
        );

        $links['our_shop'] = sprintf(
            '<a href="%s" title="my.Merlot" style="font-weight:700;">%s</a>',
            'https://my.merlot.digital',
            __('my.Merlot', 'tnc-toolbox')
        );

        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Add custom CSS for admin bar
     */
    public function enqueue_custom_css() {
        wp_register_style('tnc_custom_css', false);
        wp_enqueue_style('tnc_custom_css');
        
        $custom_css = "
            .nginx-cache-btn.nginx-cache-off a { background-color: #d63638 !important; }
            .nginx-cache-btn.nginx-cache-on a { background-color: green !important; }
        ";
        wp_add_inline_style('tnc_custom_css', $custom_css);
    }

    /**
     * Add parent menu entry to admin bar
     */
    public function add_parent_menu_entry($wp_admin_bar) {
        $args = array(
            'id' => 'tnc_parent_menu_entry',
            'title' => sprintf(
                '<img src="%s" style="height: 20px; padding-top: 6px;">',
                plugins_url('assets/tnc-icon-light.png', TNCWPTBOX_PLUGIN_FILE)
            ),
            'href' => admin_url('options-general.php?page=tnc_toolbox'),
            'meta' => array('class' => 'tnc-parent-menu-entry')
        );
        $wp_admin_bar->add_node($args);
    }

    /**
     * Add cache control buttons to admin bar
     */
    public function add_cache_purge_button($wp_admin_bar) {
        $wp_admin_bar->add_node(array(
            'id' => 'nginx_cache_purge',
            'parent' => 'tnc_parent_menu_entry',
            'title' => 'NGINX Cache: Purge!',
            'href' => admin_url('admin-post.php?action=nginx_cache_purge'),
            'meta' => array('class' => 'nginx-cache-btn nginx-cache-purge')
        ));
    }

    public function add_cache_off_button($wp_admin_bar) {
        $wp_admin_bar->add_node(array(
            'id' => 'nginx_cache_off',
            'parent' => 'tnc_parent_menu_entry',
            'title' => 'NGINX Cache: OFF',
            'href' => admin_url('admin-post.php?action=nginx_cache_off'),
            'meta' => array('class' => 'nginx-cache-btn nginx-cache-off')
        ));
    }

    public function add_cache_on_button($wp_admin_bar) {
        $wp_admin_bar->add_node(array(
            'id' => 'nginx_cache_on',
            'parent' => 'tnc_parent_menu_entry',
            'title' => 'NGINX Cache: ON',
            'href' => admin_url('admin-post.php?action=nginx_cache_on'),
            'meta' => array('class' => 'nginx-cache-btn nginx-cache-on')
        ));
    }

    /**
     * Cache control actions
     */
    public function nginx_cache_purge() {
        $result = TNC_cPanel_UAPI::purge_cache();
        wp_safe_redirect(wp_get_referer());
        exit;
    }

    public function nginx_cache_off() {
        $result = TNC_cPanel_UAPI::disable_cache();
        wp_safe_redirect(wp_get_referer());
        exit;
    }

    public function nginx_cache_on() {
        $result = TNC_cPanel_UAPI::enable_cache();
        wp_safe_redirect(wp_get_referer());
        exit;
    }

    /**
     * Automatic cache purging
     */
    public function purge_cache_on_update($post_id, $post_after, $post_before) {
        if ('publish' === $post_after->post_status || 
            ($post_before->post_status === 'publish' && $post_after->post_status !== 'trash')) {
            $this->nginx_cache_purge();
        }
    }

    /**
     * Display admin notices
     */
    public function display_admin_notices() {
        foreach (['error', 'success'] as $type) {
            $transient_key = "tnc_wp_toolbox_cpanel_action_{$type}";
            if ($message = get_transient($transient_key)) {
                printf(
                    '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
                    esc_attr($type),
                    esc_html($message)
                );
                delete_transient($transient_key);
            }
        }
    }
}