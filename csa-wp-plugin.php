<?php
/*
Plugin Name: Open-CSA WP Plugin
Plugin URI:  
Description: Provides functionality required to run a CSA (Community Supported Agriculture) Team
Version:     1.0
Author:      Eleftherios Kosmas; Haris Papagianakis
Author URI:  
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: csa-wp-plugin
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*	***************************
	JS REGISTRATION - INCLUSION
	***************************
*/

add_action('wp_head','csa_wp_plugin_ajaxurl');

function csa_wp_plugin_ajaxurl() {
?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<?php
}

include 'csa-wp-general-functions.php';


/*	*********
	CONSTANTS
	*********
*/

define("CSA_WP_PLUGIN_DOMAIN", "csa-wp-plugin");

global $wpdb;
define("CSA_WP_PLUGIN_TABLE_SPOTS", $wpdb->prefix."csa_wp_plugin_spots");
define("CSA_WP_PLUGIN_TABLE_SPOTS_TO_USERS", $wpdb->prefix."csa_wp_plugin_spots_to_users");
define("CSA_WP_PLUGIN_TABLE_PRODUCT_CATEGORIES", $wpdb->prefix."csa_wp_plugin_product_categories");
define("CSA_WP_PLUGIN_TABLE_PRODUCTS", $wpdb->prefix."csa_wp_plugin_products");
define("CSA_WP_PLUGIN_TABLE_DELIVERIES", $wpdb->prefix."csa_wp_plugin_deliveries");
define("CSA_WP_PLUGIN_TABLE_PRODUCT_ORDERS", $wpdb->prefix."csa_wp_plugin_product_orders");
define("CSA_WP_PLUGIN_TABLE_USER_ORDERS", $wpdb->prefix."csa_wp_plugin_user_orders");

define("CSA_WP_PLUGIN_DATE_FORMAT", "Y-m-d");
define("CSA_WP_PLUGIN_DATE_FORMAT_READABLE", "d-M-Y");

define("CSA_WP_PLUGIN_OPTIONS_GROUP", "csa-wp-plugin-options-group");

/*	*******
	GLOBALS
	*******
*/

$days_of_week = array(
					__("Monday", CSA_WP_PLUGIN_DOMAIN), 
					__("Tuesday", CSA_WP_PLUGIN_DOMAIN), 
					__("Wednesday", CSA_WP_PLUGIN_DOMAIN), 
					__("Thursday", CSA_WP_PLUGIN_DOMAIN), 
					__("Friday", CSA_WP_PLUGIN_DOMAIN), 
					__("Saturday", CSA_WP_PLUGIN_DOMAIN), 
					__("Sunday", CSA_WP_PLUGIN_DOMAIN)
				);

				
/*	***************
	ACTIVATION CODE
	***************
*/
				
include 'csa-wp-DB-tables.php';

function csa_wp_plugin_activation() {
	csa_wp_plugin_register_settings();
	csa_wp_plugin_db_tables_creation();
	csa_wp_plugin_pages_creation();
}
register_activation_hook( __FILE__, 'csa_wp_plugin_activation' );

function csa_wp_plugin_register_settings() {
	register_setting(CSA_WP_PLUGIN_OPTIONS_GROUP, 'csa-wp-plugin-db-version');
	register_setting(CSA_WP_PLUGIN_OPTIONS_GROUP, 'csa-wp-plugin-administration-page');
	register_setting(CSA_WP_PLUGIN_OPTIONS_GROUP, 'csa-wp-plugin-orders-page');
}

function csa_wp_plugin_pages_creation() {
	// Create post object
	$my_post = array(
	  'post_title'    	=> __('Administration', CSA_WP_PLUGIN_DOMAIN),
	  'post_content' 	=> 'Click on the administration sumbemus to select between "Manage Products" and "Manage Orders"',
	  'post_status'   	=> 'publish',
	  'guid'			=> 'csa-wp-plugin-administration',
	  'post_type'		=> 'page',
	  'comment_status'  => 'closed'
	);

	// Insert the post into the database
	$administration_page_id = wp_insert_post( $my_post );
	update_option( 'csa-wp-plugin-administration-page', $administration_page_id );

	// Create post object
	$my_post = array(
	  'post_title'    	=> __('Manage Products', CSA_WP_PLUGIN_DOMAIN),
	  'post_content' 	=> '[csa_wp_plugin_manage_products]',
	  'post_status'   	=> 'publish',
	  'guid'			=> 'csa-wp-plugin-administration',
	  'post_type'		=> 'page',
	  'post_parent'		=>  $administration_page_id,
	  'comment_status'  => 'closed'
	);

	// Insert the post into the database
	$manage_products_page_id = wp_insert_post( $my_post );
	update_option( 'csa-wp-plugin-manage-products-page', $manage_products_page_id );
	
	// Create post object
	$my_post = array(
	  'post_title'    	=> __('Manage User Orders', CSA_WP_PLUGIN_DOMAIN),
	  'post_content' 	=> '[csa_wp_plugin_manage_user_orders]',
	  'post_status'   	=> 'publish',
	  'guid'			=> 'csa-wp-plugin-administration',
	  'post_type'		=> 'page',
	  'post_parent'		=>  $administration_page_id,
	  'comment_status'  => 'closed'
	);

	// Insert the post into the database
	$manage_orders_page_id = wp_insert_post( $my_post );
	update_option( 'csa-wp-plugin-manage-orders-page', $manage_orders_page_id );

	// Create post object
	$my_post = array(
	  'post_title'    	=> __('My Orders', CSA_WP_PLUGIN_DOMAIN),
	  'post_content' 	=> '[csa_wp_plugin_manage_my_orders]',
	  'post_status'   	=> 'publish',
	  'guid'			=> 'csa-wp-plugin-my-orders',
	  'post_type'		=> 'page',
	  'comment_status'  => 'closed'
	);

	// Insert the post into the database
	$orders_page_id = wp_insert_post( $my_post );
	update_option( 'csa-wp-plugin-orders-page', $orders_page_id );	
}

function csa_wp_plugin_enqueue_csa_scripts() {
	wp_register_script('csa-wp-plugin-general-scripts', plugins_url('/csa-wp-general-scripts.js', __FILE__));
	wp_register_script('csa-wp-plugin-users-scripts', plugins_url('/csa-wp-users.js', __FILE__));
	wp_register_script('csa-wp-plugin-spots-scripts', plugins_url('/csa-wp-spots.js', __FILE__));
	wp_register_script('csa-wp-plugin-product-categories-scripts', plugins_url('/csa-wp-product-categories.js', __FILE__));
	wp_register_script('csa-wp-plugin-products-scripts', plugins_url('/csa-wp-products.js', __FILE__));
	wp_register_script('csa-wp-plugin-deliveries-scripts', plugins_url('/csa-wp-deliveries.js', __FILE__));	
	wp_register_script('csa-wp-plugin-orders-scripts', plugins_url('/csa-wp-orders.js', __FILE__));	
	wp_register_script('jquery.datatables', plugins_url('/deps/jquery.datatables.js', __FILE__) );
	wp_register_script('jquery.jeditable', plugins_url('/deps/jquery.jeditable.js', __FILE__));
	wp_register_script('jquery.blockui', plugins_url('/deps/jquery.blockui.js', __FILE__));
	wp_register_script('jquery.cluetip', plugins_url('/deps/jquery.cluetip.js', __FILE__));
	wp_register_style('jquery.cluetip.style', plugins_url('/deps/jquery.cluetip.css', __FILE__));


	$general_translation_array = array(
		'you_forgot_this_one' => __( "you forgot this one", CSA_WP_PLUGIN_DOMAIN ),
		'invalid_delivery_period_undefined' => __( "invalid delivery period! start of period in not defined", CSA_WP_PLUGIN_DOMAIN ),
		'invalid_delivery_period_value' => __( "invalid delivery period! please fill in for end time some value", CSA_WP_PLUGIN_DOMAIN )
	);
	wp_localize_script( 'csa-wp-plugin-general-scripts', 'general_translation', $general_translation_array );
	
	
	$spots_translation_array = array(
		'tooltip_click_to_change' => __( "click to change...", CSA_WP_PLUGIN_DOMAIN ),
		'placeholder_click_to_fill' => __( "click to fill ...", CSA_WP_PLUGIN_DOMAIN ),
		'invalid_spot_name_hint' => __( "invalid! spot name already exists. please choose a unique one...", CSA_WP_PLUGIN_DOMAIN ),
		'invalid_spot_name' => __( "invalid! name already exists.", CSA_WP_PLUGIN_DOMAIN ),
		'spot_cannnot_be_deleted' => __( "You can not delete this spot, since at least one delivery has been initiated for it.", CSA_WP_PLUGIN_DOMAIN ),
		'can_update_info' => __( "you can update the following info...", CSA_WP_PLUGIN_DOMAIN ),
		'please_fill_info' => __( "please fill in the following info...", CSA_WP_PLUGIN_DOMAIN ),
		'delivery_spot_details_maintained' => __( "the details of the former delivery spot will be maintained for later reference...", CSA_WP_PLUGIN_DOMAIN )
	);
	wp_localize_script( 'csa-wp-plugin-spots-scripts', 'spots_translation', $spots_translation_array );

	$products_categories_translation_array = array(
		'tooltip' => __( "click to change...", CSA_WP_PLUGIN_DOMAIN ),
		'placeholder' => __( "click to fill ...", CSA_WP_PLUGIN_DOMAIN ),
		'product_category_cannnot_be_deleted' => __( "You can not delete this produt category, since there exists at least one product of this category.", CSA_WP_PLUGIN_DOMAIN )
	);
	wp_localize_script( 'csa-wp-plugin-product-categories-scripts', 'product_categories_translation', $products_categories_translation_array );

	$products_translation_array = array(
		'tooltip' => __( "click to change...", CSA_WP_PLUGIN_DOMAIN ),
		'placeholder' => __( "click to fill ...", CSA_WP_PLUGIN_DOMAIN ),	
		'yes' => __( "yes", CSA_WP_PLUGIN_DOMAIN ),
		'no' => __( "no", CSA_WP_PLUGIN_DOMAIN ),	
		'product_cannnot_be_deleted' => __( "You can not delete this product, since at least one order exists for it.", CSA_WP_PLUGIN_DOMAIN ),
		'mark_available' => __( "mark it as available", CSA_WP_PLUGIN_DOMAIN ),
		'mark_unavailable' => __( "mark it as unavailable", CSA_WP_PLUGIN_DOMAIN )
	);
	wp_localize_script( 'csa-wp-plugin-products-scripts', 'products_translation', $products_translation_array );

	$deliveries_translation_array = array(
		'yes' => __( "yes", CSA_WP_PLUGIN_DOMAIN ),
		'no' => __( "no", CSA_WP_PLUGIN_DOMAIN ),		
		'grant_ability_to_order' => __( "grant ability to order", CSA_WP_PLUGIN_DOMAIN ),		
		'remove_ability_to_order' => __( "remove ability to order", CSA_WP_PLUGIN_DOMAIN )	
	);
	wp_localize_script( 'csa-wp-plugin-deliveries-scripts', 'deliveries_translation', $deliveries_translation_array );

	$orders_translation_array = array(
		'tooltip' => __( "click to change...", CSA_WP_PLUGIN_DOMAIN ),
		'placeholder' => __( "click to fill ...", CSA_WP_PLUGIN_DOMAIN ),	
		'product_quantity_cannnot_be_updated' => __( "You can not update the quantity of your product orders, since the order deadline has been reached for this delivery. For any change, please contact either an administrator or the responsible for this delivery.", CSA_WP_PLUGIN_DOMAIN ),
		'total' => __( "Total", CSA_WP_PLUGIN_DOMAIN ),		
		'empty_order' => __( "Your order is still empty...", CSA_WP_PLUGIN_DOMAIN ),		
		'cannnot_add_or_update_order' => __( "You can not add new or upate your order for this delivery, since its order deadline has been reached. For any additional information, please contact either an administrator or the responsible for this delivery.", CSA_WP_PLUGIN_DOMAIN ),		
		'cannnot_delete_product' => __( "You can not delete your product order, since the order deadline has been reached for this delivery. For any change, please contact either an administrator or the responsible for this delivery.", CSA_WP_PLUGIN_DOMAIN ),		
		'cannnot_cancel_order' => __( "You can not cancel your product order, since the order deadline has been reached for this delivery. For any change, please contact either an administrator or the responsible for this delivery.", CSA_WP_PLUGIN_DOMAIN ),		
		'cannnot_become_responsible' => __( "You can not become responsible, since another user is already. For any change, please contact either an administrator or the responsible for this delivery.", CSA_WP_PLUGIN_DOMAIN ),		
		'' => __( "", CSA_WP_PLUGIN_DOMAIN ),		
	);
	wp_localize_script( 'csa-wp-plugin-orders-scripts', 'orders_translation', $orders_translation_array );

	
	
	
	
	wp_register_style('csa-wp-plugin-style', plugins_url('/csa-wp-plugin-style.css', __FILE__));
	wp_enqueue_style('csa-wp-plugin-style');
}

add_action('admin_enqueue_scripts','csa_wp_plugin_enqueue_csa_scripts');
add_action('wp_enqueue_scripts','csa_wp_plugin_enqueue_csa_scripts');


function csa_wp_plugin_load_plugin_textdomain() {
    load_plugin_textdomain( 'csa-wp-plugin', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'csa_wp_plugin_load_plugin_textdomain' );


/*	********************************
	CREATION OF ADMINISTRATION PANEL
	********************************	
*/

include 'csa-wp-users.php';
include 'csa-wp-spots.php';
include 'csa-wp-products.php';
include 'csa-wp-product-categories.php';
include 'csa-wp-deliveries.php';
include 'csa-wp-orders.php';
include 'csa-wp-administration-panel.php';


register_deactivation_hook(__FILE__, 'csa_wp_plugin_deactivation');

function csa_wp_plugin_deactivation() {
	csa_wp_plugin_delete_pages();
}

function csa_wp_plugin_delete_pages() {	
	wp_delete_post(get_option('csa-wp-plugin-manage-products-page'));
	delete_option('csa-wp-plugin-manage-products-page');  
	
	wp_delete_post(get_option('csa-wp-plugin-manage-orders-page'));
	delete_option('csa-wp-plugin-manage-orders-page');  

	wp_delete_post(get_option('csa-wp-plugin-administration-page'));
	delete_option('csa-wp-plugin-administration-page');  	
	
	wp_delete_post(get_option('csa-wp-plugin-orders-page'));
	delete_option('csa-wp-plugin-orders-page');  
}

register_uninstall_hook(__FILE__, 'csa_wp_plugin_uninstall');

function csa_wp_plugin_uninstall() {
	csa_wp_plugin_unregister_settings();
	csa_wp_plugin_dt_tables_drop();
	csa_wp_plugin_delete_users_meta();
}

function csa_wp_plugin_unregister_settings() {
	unregister_setting(CSA_WP_PLUGIN_OPTIONS_GROUP, 'csa-wp-plugin-db-version');  
	delete_option('csa-wp-plugin-db-version');  
}

?>