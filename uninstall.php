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

// Clear any cached data
wp_cache_flush();