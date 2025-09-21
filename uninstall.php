<?php
/**
 * Plugin uninstall handler
 *
 * @package NS_Hamburger_Menu
 */

// Prevent direct access
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Remove plugin options
delete_option('ns_hamburger_options');

// Remove from multisite if applicable
if (is_multisite()) {
    $sites = get_sites(array('number' => 0));
    foreach ($sites as $site) {
        switch_to_blog($site->blog_id);
        delete_option('ns_hamburger_options');
        restore_current_blog();
    }
}

// Clear plugin-specific transient cache keys
$transient_keys = array(
	'nshm_classic_menus',
	'nshm_nav_posts_admin',
	'nshm_nav_blocks',
);

foreach ( $transient_keys as $key ) {
	delete_transient( $key );
}

// Clear from multisite if applicable
if ( is_multisite() ) {
	$sites = get_sites( array( 'number' => 0 ) );
	foreach ( $sites as $site ) {
		switch_to_blog( $site->blog_id );
		foreach ( $transient_keys as $key ) {
			delete_transient( $key );
		}
		restore_current_blog();
	}
}