<?php

add_action( 'admin_bar_menu', 'admin_bar_item', 500 );
function admin_bar_item ( WP_Admin_Bar $admin_bar ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$admin_bar->add_menu( array(
		'id'    => 'menu-id',
		'parent' => null,
		'group'  => null,
		'title' => 'Menu Title', //you can use img tag with image link. it will show the image icon Instead of the title.
		'href'  => admin_url('admin.php?page=custom-page'),
		'meta' => [
			'title' => __( 'Menu Title', 'textdomain' ), //This title will show on hover
		]
	) );
}

?>
