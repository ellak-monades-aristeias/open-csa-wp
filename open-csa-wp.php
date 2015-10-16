<?php
/*
Plugin Name: Open-CSA-WP
Plugin URI:  
Description: Provides functionality required to run a CSA (Community Supported Agriculture) Team
Version:     1.0
Author:      Eleftherios Kosmas; Haris Papagianakis
Author URI:  
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: open-csa-wp
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*	***************************
	JS REGISTRATION - INCLUSION
	***************************
*/

add_action('wp_head','open_csa_wp_ajaxurl');

function open_csa_wp_ajaxurl() {
?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<?php
}

include 'open-csa-wp-general-functions.php';


/*	*********
	CONSTANTS
	*********
*/

define("OPEN_CSA_WP_DOMAIN", "open-csa-wp");

global $wpdb;
define("OPEN_CSA_WP_TABLE_SPOTS", $wpdb->prefix."open_csa_wp_spots");
define("OPEN_CSA_WP_TABLE_SPOTS_TO_USERS", $wpdb->prefix."open_csa_wp_spots_to_users");
define("OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES", $wpdb->prefix."open_csa_wp_product_categories");
define("OPEN_CSA_WP_TABLE_PRODUCTS", $wpdb->prefix."open_csa_wp_products");
define("OPEN_CSA_WP_TABLE_DELIVERIES", $wpdb->prefix."open_csa_wp_deliveries");
define("OPEN_CSA_WP_TABLE_PRODUCT_ORDERS", $wpdb->prefix."open_csa_wp_product_orders");
define("OPEN_CSA_WP_TABLE_USER_ORDERS", $wpdb->prefix."open_csa_wp_user_orders");

define("OPEN_CSA_WP_DATE_FORMAT", "Y-m-d");
define("OPEN_CSA_WP_DATE_FORMAT_READABLE", "d-M-Y");

define("OPEN_CSA_WP_OPTIONS_GROUP", "open-csa-wp-options-group");

/*	*******
	GLOBALS
	*******
*/

$days_of_week = array(
					__("Monday", OPEN_CSA_WP_DOMAIN), 
					__("Tuesday", OPEN_CSA_WP_DOMAIN), 
					__("Wednesday", OPEN_CSA_WP_DOMAIN), 
					__("Thursday", OPEN_CSA_WP_DOMAIN), 
					__("Friday", OPEN_CSA_WP_DOMAIN), 
					__("Saturday", OPEN_CSA_WP_DOMAIN), 
					__("Sunday", OPEN_CSA_WP_DOMAIN)
				);

				
/*	***************
	ACTIVATION CODE
	***************
*/
				
include 'open-csa-wp-db-tables.php';

function open_csa_wp_activation() {
	open_csa_wp_register_settings();
	open_csa_wp_db_tables_creation();
	open_csa_wp_pages_creation();
}
register_activation_hook( __FILE__, 'open_csa_wp_activation' );

function open_csa_wp_register_settings() {
	register_setting(OPEN_CSA_WP_OPTIONS_GROUP, 'open-csa-wp-db-version');
	register_setting(OPEN_CSA_WP_OPTIONS_GROUP, 'open-csa-wp-administration-page');
	register_setting(OPEN_CSA_WP_OPTIONS_GROUP, 'open-csa-wp-orders-page');
}

function open_csa_wp_pages_creation() {
	// Create post object
	$my_post = array(
	  'post_title'    	=> __('Administration', OPEN_CSA_WP_DOMAIN),
	  'post_content' 	=> 'Click on the administration sumbemus to select between "Manage Products" and "Manage Orders"',
	  'post_status'   	=> 'publish',
	  'guid'			=> 'open-csa-wp-administration',
	  'post_type'		=> 'page',
	  'comment_status'  => 'closed'
	);

	// Insert the post into the database
	$administration_page_id = wp_insert_post( $my_post );
	update_option( 'open-csa-wp-administration-page', $administration_page_id );

	// Create post object
	$my_post = array(
	  'post_title'    	=> __('Manage Products', OPEN_CSA_WP_DOMAIN),
	  'post_content' 	=> '[open_csa_wp_manage_products]',
	  'post_status'   	=> 'publish',
	  'guid'			=> 'open-csa-wp-administration',
	  'post_type'		=> 'page',
	  'post_parent'		=>  $administration_page_id,
	  'comment_status'  => 'closed'
	);

	// Insert the post into the database
	$manage_products_page_id = wp_insert_post( $my_post );
	update_option( 'open-csa-wp-manage-products-page', $manage_products_page_id );
	
	// Create post object
	$my_post = array(
	  'post_title'    	=> __('Manage User Orders', OPEN_CSA_WP_DOMAIN),
	  'post_content' 	=> '[open_csa_wp_manage_user_orders]',
	  'post_status'   	=> 'publish',
	  'guid'			=> 'open-csa-wp-administration',
	  'post_type'		=> 'page',
	  'post_parent'		=>  $administration_page_id,
	  'comment_status'  => 'closed'
	);

	// Insert the post into the database
	$manage_orders_page_id = wp_insert_post( $my_post );
	update_option( 'open-csa-wp-manage-orders-page', $manage_orders_page_id );

	// Create post object
	$my_post = array(
	  'post_title'    	=> __('My Orders', OPEN_CSA_WP_DOMAIN),
	  'post_content' 	=> '[open_csa_wp_manage_my_orders]',
	  'post_status'   	=> 'publish',
	  'guid'			=> 'open-csa-wp-my-orders',
	  'post_type'		=> 'page',
	  'comment_status'  => 'closed'
	);

	// Insert the post into the database
	$orders_page_id = wp_insert_post( $my_post );
	update_option( 'open-csa-wp-orders-page', $orders_page_id );	
}

function open_csa_wp_enqueue_csa_scripts() {
	wp_register_script('open-csa-wp-general-scripts', plugins_url('/open-csa-wp-general-scripts.js', __FILE__));
	wp_register_script('open-csa-wp-users-scripts', plugins_url('/open-csa-wp-users.js', __FILE__));
	wp_register_script('open-csa-wp-spots-scripts', plugins_url('/open-csa-wp-spots.js', __FILE__));
	wp_register_script('open-csa-wp-product-categories-scripts', plugins_url('/open-csa-wp-product-categories.js', __FILE__));
	wp_register_script('open-csa-wp-products-scripts', plugins_url('/open-csa-wp-products.js', __FILE__));
	wp_register_script('open-csa-wp-deliveries-scripts', plugins_url('/open-csa-wp-deliveries.js', __FILE__));	
	wp_register_script('open-csa-wp-orders-scripts', plugins_url('/open-csa-wp-orders.js', __FILE__));	
	wp_register_script('jquery.datatables', plugins_url('/deps/jquery.datatables.js', __FILE__) );
	wp_register_script('jquery.jeditable', plugins_url('/deps/jquery.jeditable.js', __FILE__));
	wp_register_script('jquery.blockui', plugins_url('/deps/jquery.blockui.js', __FILE__));
	wp_register_script('jquery.cluetip', plugins_url('/deps/jquery.cluetip.js', __FILE__));
	wp_register_style('jquery.cluetip.style', plugins_url('/deps/jquery.cluetip.css', __FILE__));


	$general_translation_array = array(
		'you_forgot_this_one' => __( "you forgot this one", OPEN_CSA_WP_DOMAIN ),
		'invalid_delivery_period_undefined' => __( "invalid delivery period! start of period in not defined", OPEN_CSA_WP_DOMAIN ),
		'invalid_delivery_period_value' => __( "invalid delivery period! please fill in for end time some value", OPEN_CSA_WP_DOMAIN )
	);
	wp_localize_script( 'open-csa-wp-general-scripts', 'general_translation', $general_translation_array );
	
	
	$spots_translation_array = array(
		'tooltip_click_to_change' => __( "click to change...", OPEN_CSA_WP_DOMAIN ),
		'placeholder_click_to_fill' => __( "click to fill ...", OPEN_CSA_WP_DOMAIN ),
		'invalid_spot_name_hint' => __( "invalid! spot name already exists. please choose a unique one...", OPEN_CSA_WP_DOMAIN ),
		'invalid_spot_name' => __( "invalid! name already exists.", OPEN_CSA_WP_DOMAIN ),
		'spot_cannnot_be_deleted' => __( "You can not delete this spot, since at least one delivery has been initiated for it.", OPEN_CSA_WP_DOMAIN ),
		'can_update_info' => __( "you can update the following info...", OPEN_CSA_WP_DOMAIN ),
		'please_fill_info' => __( "please fill in the following info...", OPEN_CSA_WP_DOMAIN ),
		'delivery_spot_details_maintained' => __( "the details of the former delivery spot will be maintained for later reference...", OPEN_CSA_WP_DOMAIN )
	);
	wp_localize_script( 'open-csa-wp-spots-scripts', 'spots_translation', $spots_translation_array );

	$products_categories_translation_array = array(
		'tooltip' => __( "click to change...", OPEN_CSA_WP_DOMAIN ),
		'placeholder' => __( "click to fill ...", OPEN_CSA_WP_DOMAIN ),
		'product_category_cannnot_be_deleted' => __( "You can not delete this produt category, since there exists at least one product of this category.", OPEN_CSA_WP_DOMAIN )
	);
	wp_localize_script( 'open-csa-wp-product-categories-scripts', 'product_categories_translation', $products_categories_translation_array );

	$products_translation_array = array(
		'tooltip' => __( "click to change...", OPEN_CSA_WP_DOMAIN ),
		'placeholder' => __( "click to fill ...", OPEN_CSA_WP_DOMAIN ),	
		'yes' => __( "yes", OPEN_CSA_WP_DOMAIN ),
		'no' => __( "no", OPEN_CSA_WP_DOMAIN ),	
		'product_cannnot_be_deleted' => __( "You can not delete this product, since at least one order exists for it.", OPEN_CSA_WP_DOMAIN ),
		'mark_available' => __( "mark it as available", OPEN_CSA_WP_DOMAIN ),
		'mark_unavailable' => __( "mark it as unavailable", OPEN_CSA_WP_DOMAIN )
	);
	wp_localize_script( 'open-csa-wp-products-scripts', 'products_translation', $products_translation_array );

	$deliveries_translation_array = array(
		'yes' => __( "yes", OPEN_CSA_WP_DOMAIN ),
		'no' => __( "no", OPEN_CSA_WP_DOMAIN ),		
		'grant_ability_to_order' => __( "grant ability to order", OPEN_CSA_WP_DOMAIN ),		
		'remove_ability_to_order' => __( "remove ability to order", OPEN_CSA_WP_DOMAIN )	
	);
	wp_localize_script( 'open-csa-wp-deliveries-scripts', 'deliveries_translation', $deliveries_translation_array );

	$orders_translation_array = array(
		'tooltip' => __( "click to change...", OPEN_CSA_WP_DOMAIN ),
		'placeholder' => __( "click to fill ...", OPEN_CSA_WP_DOMAIN ),	
		'product_quantity_cannnot_be_updated' => __( "You can not update the quantity of your product orders, since the order deadline has been reached for this delivery. For any change, please contact either an administrator or the responsible for this delivery.", OPEN_CSA_WP_DOMAIN ),
		'total' => __( "Total", OPEN_CSA_WP_DOMAIN ),		
		'empty_order' => __( "Your order is still empty...", OPEN_CSA_WP_DOMAIN ),		
		'cannnot_add_or_update_order' => __( "You can not add new or upate your order for this delivery, since its order deadline has been reached. For any additional information, please contact either an administrator or the responsible for this delivery.", OPEN_CSA_WP_DOMAIN ),		
		'cannnot_delete_product' => __( "You can not delete your product order, since the order deadline has been reached for this delivery. For any change, please contact either an administrator or the responsible for this delivery.", OPEN_CSA_WP_DOMAIN ),		
		'cannnot_cancel_order' => __( "You can not cancel your product order, since the order deadline has been reached for this delivery. For any change, please contact either an administrator or the responsible for this delivery.", OPEN_CSA_WP_DOMAIN ),		
		'cannnot_become_responsible' => __( "You can not become responsible, since another user is already. For any change, please contact either an administrator or the responsible for this delivery.", OPEN_CSA_WP_DOMAIN ),		
		'' => __( "", OPEN_CSA_WP_DOMAIN ),		
	);
	wp_localize_script( 'open-csa-wp-orders-scripts', 'orders_translation', $orders_translation_array );

	
	wp_register_style('open-csa-wp-style', plugins_url('/open-csa-wp-style.css', __FILE__));
	wp_enqueue_style('open-csa-wp-style');
}

add_action('admin_enqueue_scripts','open_csa_wp_enqueue_csa_scripts');
add_action('wp_enqueue_scripts','open_csa_wp_enqueue_csa_scripts');


function open_csa_wp_load_plugin_textdomain() {
    load_plugin_textdomain( 'open-csa-wp', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'open_csa_wp_load_plugin_textdomain' );


/*	********************************
	CREATION OF ADMINISTRATION PANEL
	********************************	
*/

include 'open-csa-wp-users.php';
include 'open-csa-wp-spots.php';
include 'open-csa-wp-products.php';
include 'open-csa-wp-product-categories.php';
include 'open-csa-wp-deliveries.php';
include 'open-csa-wp-orders.php';
include 'open-csa-wp-administration-panel.php';


register_deactivation_hook(__FILE__, 'open_csa_wp_deactivation');

function open_csa_wp_deactivation() {
	open_csa_wp_delete_pages();
}

function open_csa_wp_delete_pages() {	
	wp_delete_post(get_option('open-csa-wp-manage-products-page'));
	delete_option('open-csa-wp-manage-products-page');  
	
	wp_delete_post(get_option('open-csa-wp-manage-orders-page'));
	delete_option('open-csa-wp-manage-orders-page');  

	wp_delete_post(get_option('open-csa-wp-administration-page'));
	delete_option('open-csa-wp-administration-page');  	
	
	wp_delete_post(get_option('open-csa-wp-orders-page'));
	delete_option('open-csa-wp-orders-page');  
}

register_uninstall_hook(__FILE__, 'open_csa_wp_uninstall');

function open_csa_wp_uninstall() {
	open_csa_wp_unregister_settings();
	open_csa_wp_db_tables_drop();
	open_csa_wp_delete_users_meta();
}

function open_csa_wp_unregister_settings() {
	unregister_setting(OPEN_CSA_WP_OPTIONS_GROUP, 'open-csa-wp-db-version');  
	delete_option('open-csa-wp-db-version');  
}

?>