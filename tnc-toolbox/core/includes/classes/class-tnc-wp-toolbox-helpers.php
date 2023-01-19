<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Tnc_Wp_Toolbox_Helpers
 *
 * This class contains repetitive functions that
 * are used globally within the plugin.
 *
 * @package		TNCWPTBOX
 * @subpackage	Classes/Tnc_Wp_Toolbox_Helpers
 * @author		The Network Crew Pty Ltd
 * @since		1.0.0
 */
class Tnc_Wp_Toolbox_Helpers{

	/**
	 * ######################
	 * ###
	 * #### CALLABLE FUNCTIONS
	 * ###
	 * ######################
	 */

	/**
	 * HELPER COMMENT START
	 *
	 * Within this class, you can define common functions that you are 
	 * going to use throughout the whole plugin. 
	 * 
	 * Down below you will find a demo function called output_text()
	 * To access this function from any other class, you can call it as followed:
	 * 
	 * TNCWPTBOX()->helpers->output_text( 'my text' );
	 * 
	 */
	 
	/**
	 * Output some text
	 *
	 * @param	string	$text	The text to output
	 * @since	1.0.0
	 *
	 * @return	void
	 */
	 public function output_text( $text = '' ){
		 echo esc_html( $text) ;
	 }

	 /**
	  * HELPER COMMENT END
	  */

}
