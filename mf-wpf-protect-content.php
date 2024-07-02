<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/**
 * Plugin Name: Protect content from DAP to WP Fusion
 * Plugin URI:  https://memberfix.rocks/
 * Description: Protect your content from DAP with WP Fusion tags in just three clicks.
 * Version:     1.0
 * Author:      MemberFix
 * Author URI:  https://memberfix.rocks/
 * Text Domain: mf-wpfprotect
 */

include_once 'config/constants.php';
include_once 'classes/mf_wpf_protect_content.php';
include_once 'classes/mf_core_notifications.php';

$mf_wpf_protect_content = new mf_wpf_protect_content();
global $wp_version;

// If PHP version is too old show an error
if ( version_compare( phpversion(), '5.4', '<' ) ) {
	add_action('admin_notices', ['mf_core_notifications', 'php_version_warrning_message']);
	return false;
}

// If Wordpress version is too old show an error
if ( version_compare( $wp_version, '3.4.0', '<' ) ) {
	add_action('admin_notices', ['mf_core_notifications', 'wp_version_warrning_message']);
	return false;
}

// Check if is wp fusion installed and activated
if (
	! in_array('wp-fusion-lite/wp-fusion-lite.php', apply_filters('active_plugins', get_option('active_plugins'))) &&
	! in_array('wp-fusion/wp-fusion.php', apply_filters('active_plugins', get_option('active_plugins'))) ) {
	
	add_action('admin_notices', ['mf_core_notifications', 'wp_fusion_warrning_message']);
	return false;
}

// Check if DAP table exists in database
if ( $mf_wpf_protect_content->check_if_dap_table_exists() === false ) {
	add_action('admin_notices', ['mf_core_notifications', 'dap_table_warrning_message']);
	return false;
}


// include the main class
$mf_wpf_protect_content->init();

























