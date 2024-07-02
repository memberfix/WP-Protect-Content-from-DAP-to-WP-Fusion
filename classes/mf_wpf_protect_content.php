<?php

/**
 * Class mf_wpf_protect_content
 * The main class that we will use for protecting content from DAP to WP Fusion
 */
class mf_wpf_protect_content {
	
	public function init() {
		// Call admin scripts
		add_action( 'admin_enqueue_scripts', array($this, 'include_admin_scripts') );
		
		// Call admin menu
		add_action( 'admin_menu', array($this, 'admin_menu'), 99 );
		
		// Call AJAX action - get all pages_id
		add_action( 'wp_ajax_wpfGetAllPagesId', array($this, 'get_all_pages_id' ) );
		add_action( 'wp_ajax_nopriv_wpfGetAllPagesId', array($this, 'get_all_pages_id' ) );
		
		// Call AJAX action - set tags to wp fusion
		add_action( 'wp_ajax_wpfContentProtect', array($this, 'set_tags_ajax' ) );
		add_action( 'wp_ajax_nopriv_wpfContentProtect', array($this, 'set_tags_ajax' ) );
	}
	
	/**
	 * Admin menu
	 */
	public function admin_menu() {
		add_submenu_page( 'options-general.php', __('WPF Protect content'), __('WPF Protect content'), 'manage_options', 'mf-wpf-protect-content', array($this, 'wpf_protect_content') );
	}
	
	/**
	 * Method is callback on submenu page "WPF Protect content" that is showed in Settings options
	 * This method we not use anymore because now we created the method set_tags_ajax()
	 * that is called with ajax
	 */
	public function wpf_protect_content() {
		$plugin_url = plugin_dir_path(__DIR__); // get plugin URL
		$get_all_post_types = $this->get_all_post_types(); // get post types
		
		include_once $plugin_url . '/views/admin/main.php';
	}
	
	/**
	 * @return mixed
	 * This method will return all pages id
	 * We need this method because we have to send tags for each page
	 */
	public function get_all_pages_id() {
		
		$selected_post_types = $_POST['mf_post_types']; // get selected post types from ajax
		$selected_post_types_output = ''; // used to show which post types are selected
		
		$content_id = [];
		
		if ( is_array($selected_post_types) ) {
			$selected_post_types_output = implode(', ', $selected_post_types);
			
			$args = array(
				'post_type' => $selected_post_types,
				'post_status' => 'any',
				'posts_per_page' => -1
				
			);
			$all_content = new WP_Query( $args );
			
			// go through post type and get id
			foreach ( $all_content->posts as $post ) {
				$content_id[] = $post->ID;
			}
		}
		
		
		$content_id = implode(',', $content_id);
		
		echo json_encode(['content_id' => $content_id, 'selected_pt' => $selected_post_types_output]);
		
		die();
	}
	
	/**
	 * This method will be called clicking on the button "Add tags from DAP to pages - WP Fusion"
	 */
	public function set_tags_ajax() {
		$page_id = $_POST['page_id'];
		$page = get_post($page_id);
		
		$output = '';
		
		$get_permalink = get_permalink($page_id);
		$url = (!empty($_SERVER['HTTPS'])) ? "https://".$_SERVER['SERVER_NAME'] : "http://".$_SERVER['SERVER_NAME'];
		$permalinknew = str_replace(array('http://','https://'), '', $get_permalink);
		
		$permalink = (!empty($_SERVER['HTTPS'])) ? "https://".$permalinknew : "http://".$permalinknew;
		$permalink_slug_old = str_replace($url ,"",$permalink);
		$permalink_slug =rtrim($permalink_slug_old,"/");
		
		// Get all tags from the DAP - will return false if slug doesn't exit in DAP table
		$tags = $this->get_all_page_tags_from_dap($permalink_slug);
		
		// Add tags to the page if tags exists
		if ( $tags === false ) {
			$add_tags_status = false;
			$this->remove_wpf_tags_from_page($page->ID);
		} else {
			// Tag exists so we can add the tags to the page
			$add_tags_status = $this->set_wpf_tags_to_page($page->ID, $tags);
		}
		
		$success_message = '';
		$added_tags_message = '';
		
		// If tags added successfully
		// Show success or error message if the page is updated with tags or not
		if ( $tags !== false && $add_tags_status !== false ) {
			$success_message = 'Success';
			// Show tags that are added
			$added_tags_message = implode(', ', $tags);
		} else if ( $tags !== false && $add_tags_status === false ) {
			$success_message = 'Success';
			
			// Tags already exists or there is some error while adding tags, developer will need to check
			// if this makes a troubles for somebody, but usually that is because tags already exists
			$added_tags_message = 'Tags already exists';
		} else if ( $tags === false && $add_tags_status === false ) {
			// In case when $tags variable return false (the variable $add_tags_status will automatically be false)
			// that means that page doesn't exist in the DAP
			$success_message = 'This page doesn\'t exist in DAP';
			$added_tags_message = '';
		}
		
		// Prepare output
		$output .= 'Page ID: ' . $page->ID . '<br>';
		$output .= 'Page title: ' . $page->post_title . '<br>';
		$output .= 'Status: ' . $success_message . '<br>';
		$output .= 'Added tags: ' . $added_tags_message . '<br><br>';
		
		echo $output;
		
		die();
	}
	
	/**
	 * Include all script that will be loaded in wp-admin
	 */
	public function include_admin_scripts() {
		wp_register_style('mf-wpf-protect', plugin_dir_url(__DIR__).'assets/admin/css/mf-wpfprotect.css');
		wp_enqueue_style('mf-wpf-protect');
		
		wp_register_script('mf-wpf-protect-script', plugin_dir_url(__DIR__) . 'assets/admin/js/mf-wpfprotect.js', array( 'jquery' ) );
		
		$admin_script = array( 'ajax_url' => admin_url( 'admin-ajax.php' ) );
		wp_localize_script( 'mf-wpf-protect-script', 'wpp', $admin_script );
		wp_enqueue_script( 'mf-wpf-protect-script' );
	}
	
	/**
	 * @param $product_id
	 * This methos will return DAP product name by product ID
	 *
	 * @return bool|mixed
	 */
	public function get_dap_product_name_by_id( $product_id ) {
		global $wpdb;
		$result = $wpdb->get_col( $wpdb->prepare( "SELECT name FROM dap_products p WHERE p.id= %d", $product_id ) );
		
		return ( is_array( $result ) && count( $result ) == 1 ) ? $result[0] : $result;
	}
	
	/**
	 * @param $page_id
	 * @param array $tags
	 * This method will remove and clear all tags and checked checkbox from WP Fusion settings in page
	 * @return bool
	 */
	public function remove_wpf_tags_from_page( $page_id, $tags=[] ) {
		$wp_fusion_data = [
			'lock_content' => '0',
			'redirect'     => 0,
			'redirect_url' => '',
			'apply_delay'  => 0,
			'allow_tags'   => $tags
		];
		if (update_post_meta( $page_id, 'wpf-settings', $wp_fusion_data )) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * @param $page_id
	 * @param $tags
	 * This method will put all WP Fusion tags to the page
	 * @return boolean
	 */
	public function set_wpf_tags_to_page( $page_id, $tags ) {
		$wp_fusion_data = [
			'lock_content' => '1',
			'redirect'     => 0,
			'redirect_url' => '',
			'apply_delay'  => 0,
			'allow_tags'   => $tags
		];
		if (update_post_meta( $page_id, 'wpf-settings', $wp_fusion_data )) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * @param $slug
	 * Method will return all tags from dap table or
	 * it will return false if the page is not found in DAP table
	 * @return array|boolean
	 */
	public function get_all_page_tags_from_dap($slug) {
		global $wpdb;
		$sql = "SELECT * FROM dap_file_resources fr, dap_products_resources_jn prj WHERE fr.url = '".$slug."' AND fr.id = prj.resource_id";
		$get_tags_from_page = $wpdb->get_results( $sql );
		
		$tags = [];
		if ( ! empty($get_tags_from_page) ) {
			foreach ( $get_tags_from_page as $row ) {
				$tags[] = $this->get_dap_product_name_by_id($row->product_id);
			}
			return $tags;
		} else {
			return false;
		}
	}
	
	
	/**
	 * @param array $new_args
	 * This method will return all available post types excluding attachments
	 * @return array
	 */
	public function get_all_post_types($new_args=[]) {
		$args = [
			'public' => true,
		];
		
		if ( !empty($args) ) {
			$args = array_merge($args, $new_args);
		}
		
		$post_types = get_post_types($args, 'objects');
		
		// unset the attachments post type
		if ( isset( $post_types['attachment'] )) unset($post_types['attachment']);
		
		$all_post_types = [];
		foreach ( $post_types as $pt ) {
			$all_post_types[] = ['slug' => $pt->name, 'name' => $pt->label];
		}
		
		return $all_post_types;
	}
	
	/**
	 * This function will check if DAP tables exists
	 * For this plugin we are use three table:
	 * 'dap_products', 'dap_file_resources', 'dap_products_resources_jn'
	 * @return bool
	 */
	public function check_if_dap_table_exists() {
		global $wpdb;
		$dap_tables = ['dap_products', 'dap_file_resources', 'dap_products_resources_jn'];
		
		$all_tables = $wpdb->get_results("SHOW TABLES", 'ARRAY_N');
		$status = false;
		
		foreach ($all_tables as $table) {
			if ( in_array($table[0], $dap_tables) ) {
				$status = true;
				break;
			}
		}
		
		return $status;
	}
	
}