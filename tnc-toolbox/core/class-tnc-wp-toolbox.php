<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * HELPER COMMENT START
 * 
 * This is the main class that is responsible for registering
 * the core functions, including the files and setting up all features. 
 * 
 * To add a new class, here's what you need to do: 
 * 1. Add your new class within the following folder: core/includes/classes
 * 2. Create a new variable you want to assign the class to (as e.g. public $helpers)
 * 3. Assign the class within the instance() function ( as e.g. self::$instance->helpers = new Tnc_Wp_Toolbox_Helpers();)
 * 4. Register the class you added to core/includes/classes within the includes() function
 * 
 * HELPER COMMENT END
 */

if ( ! class_exists( 'Tnc_Wp_Toolbox' ) ) :

	/**
	 * Main Tnc_Wp_Toolbox Class.
	 *
	 * @package		TNCWPTBOX
	 * @subpackage	Classes/Tnc_Wp_Toolbox
	 * @since		1.0.0
	 * @author		The Network Crew Pty Ltd
	 */
	final class Tnc_Wp_Toolbox {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Tnc_Wp_Toolbox
		 */
		private static $instance;

		/**
		 * TNCWPTBOX object declarations.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Tnc_Wp_Toolbox_*
		 */
		public $helpers;
		public $settings;
		public $run;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'tnc-toolbox' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'tnc-toolbox' ), '1.0.0' );
		}
		
		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function includes() {
			require_once TNCWPTBOX_PLUGIN_DIR . 'core/includes/classes/class-tnc-wp-toolbox-helpers.php';
			require_once TNCWPTBOX_PLUGIN_DIR . 'core/includes/classes/class-tnc-wp-toolbox-settings.php';
			require_once TNCWPTBOX_PLUGIN_DIR . 'core/includes/classes/class-tnc-wp-toolbox-run.php';
		}

		/**
		 * Main Tnc_Wp_Toolbox Instance.
		 *
		 * Insures that only one instance of Tnc_Wp_Toolbox exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Tnc_Wp_Toolbox	The one true Tnc_Wp_Toolbox
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Tnc_Wp_Toolbox ) ) {
				self::$instance				= new Tnc_Wp_Toolbox;
				self::$instance->includes();
				self::$instance->helpers		= new Tnc_Wp_Toolbox_Helpers();
				self::$instance->settings		= new Tnc_Wp_Toolbox_Settings();
				self::$instance->run 			= new Tnc_Wp_Toolbox_Run();
				self::$instance->base_hooks();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action('TNCWPTBOX/plugin_loaded');
			}

			return self::$instance;
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action('plugins_loaded', array(self::$instance, 'load_textdomain'));
			add_action('plugins_loaded', array(self::$instance->run, 'add_capability_dependent_hooks'));
			add_action('plugins_loaded', array(self::$instance->settings, 'add_capability_dependent_settings'));
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain('tnc-toolbox', FALSE, dirname(plugin_basename(TNCWPTBOX_PLUGIN_FILE)) . '/languages/' );
		}

	}

endif; // End if class_exists check.
