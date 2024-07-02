<?php

/**
 * Class mf_core_notifications
 * This class will be used to shown errors/notifications/warnings in dashboard
 * if your website is not eligible if some reasons to use this plugin
 */
class mf_core_notifications
{
	
	/**
	 * Show warning if your PHP version is too old
	 */
	public static function php_version_warrning_message() {
		printf('<div class="update-nag"><p>%s</p></div>', __('<strong>' .MF_PLUGIN_NAME.'</strong>: your version of PHP is too old to run this plugin. You need to have at least PHP v5.4'));
	}
	// END
	
	/**
	 * Show warning if your WP version is too old
	 */
	public static function wp_version_warrning_message() {
		printf('<div class="update-nag"><p>%s</p></div>', __('<strong>' .MF_PLUGIN_NAME.'</strong>: you have to be running at least 3.4 version of Wordpress to use this plugin.'));
	}
	// END
	
	/**
	 * Show warning if wp fusion is not installed and activated
	 */
	public static function wp_fusion_warrning_message() {
		printf('<div class="update-nag"><p>%s</p></div>', __('<strong>' .MF_PLUGIN_NAME.'</strong>: please make sure that you have WP-Fusion installed and activated.'));
	}
	// END
	
	/**
	 * Show warning if dap table not exists
	 */
	public static function dap_table_warrning_message() {
		printf('<div class="update-nag"><p>%s</p></div>', __('<strong>' .MF_PLUGIN_NAME.'</strong>: it seems that you don\'t have DAP tables in your database. Did you delete them? If not, please contact us (support@memberfix.com) and we will be happy to help you.'));
	}
	// END
	

}