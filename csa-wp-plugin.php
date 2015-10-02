<?php
/*
Plugin Name: CSA WP Plugin
Plugin URI:  http://open-csa.tolabaki.gr
Description: Provides functionality required to run a CSA (Community Supported Agriculture) Team
Version:     1.0
Author:      Papagianakis Haris; Eleftherios Kosmas
Author URI:  ??
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

add_action('wp_head','CsaWpPluginAjaxurl');

function CsaWpPluginAjaxurl() {
?>
<script type="text/javascript">
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>
<?php
}

//$script_dir_path = plugin_dir_path(__FILE__)."csa-wp-javascripts.js";
//echo "<script src=". $script_dir_path. "></script>";
include 'csa-wp-general-functions.php';
//include ('C:\Apache24\htdocs\csa\wp-load.php');




global $wpdb;


define("csaSpots", $wpdb->prefix."csa_wp_plugin_spots");
define("csaSpotsToUsers", $wpdb->prefix."csa_wp_plugin_spots_to_users");
define("csaProductCategories", $wpdb->prefix."csa_wp_plugin_product_categories");
define("csaProducts", $wpdb->prefix."csa_wp_plugin_products");
define("csaDeliveries", $wpdb->prefix."csa_wp_plugin_deliveries");
define("csaProductOrders", $wpdb->prefix."csa_wp_plugin_product_orders");
define("csaUserOrders", $wpdb->prefix."csa_wp_plugin_user_orders");

define("enterKeyCode", "13");
define("tabKeyCode", "9");

define("csa_wp_plugin_date_format", "Y-m-d");
define("csa_wp_plugin_date_format_readable", "d-M-Y");

define("csaOptionsGroup", "csa-wp-plugin-options-group");


$days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");

include 'csa-wp-DB_tables_creation.php';

function CsaWpPluginAvtivation() {
	RegisterCSASettings();
	CsaWpPluginDBTablesCreation();
	CsaWpPluginPagesCreation();
}
register_activation_hook( __FILE__, 'CsaWpPluginAvtivation' );

function RegisterCSASettings() {
	register_setting(csaOptionsGroup, 'csa-wp-plugin-db_version');
	register_setting(csaOptionsGroup, 'csa-wp-plugin-administration-page');
	register_setting(csaOptionsGroup, 'csa-wp-plugin-orders-page');
}

function CsaWpPluginPagesCreation() {
	// Create post object
	$my_post = array(
	  'post_title'    	=> 'Administration',
	  'post_content' 	=> 'click on the administration sumbemus to select between "Manage Products" and "Manage Orders"',
	  'post_status'   	=> 'publish',
	  'guid'			=> 'csa-wp-plugin-administration',
	  'post_type'		=> 'page',
	  'comment_status'  => 'closed'
	);

	// Insert the post into the database
	$administrationPageID = wp_insert_post( $my_post );
	update_option( 'csa-wp-plugin-administration-page', $administrationPageID );

	// Create post object
	$my_post = array(
	  'post_title'    	=> 'Manage Products',
	  'post_content' 	=> '[csa-wp-plugin-manageProducts]',
	  'post_status'   	=> 'publish',
	  'guid'			=> 'csa-wp-plugin-administration',
	  'post_type'		=> 'page',
	  'post_parent'		=>  $administrationPageID,
	  'comment_status'  => 'closed'
	);

	// Insert the post into the database
	$manageProductsPageID = wp_insert_post( $my_post );
	update_option( 'csa-wp-plugin-manage-products-page', $manageProductsPageID );
	
	// Create post object
	$my_post = array(
	  'post_title'    	=> 'Manage User Orders',
	  'post_content' 	=> '[csa-wp-plugin-manageUserOrders]',
	  'post_status'   	=> 'publish',
	  'guid'			=> 'csa-wp-plugin-administration',
	  'post_type'		=> 'page',
	  'post_parent'		=>  $administrationPageID,
	  'comment_status'  => 'closed'
	);

	// Insert the post into the database
	$manageOrdersPageID = wp_insert_post( $my_post );
	update_option( 'csa-wp-plugin-manage-orders-page', $manageOrdersPageID );

	// Create post object
	$my_post = array(
	  'post_title'    	=> 'My Orders',
	  'post_content' 	=> '[csa-wp-plugin-manageMyOrders]',
	  'post_status'   	=> 'publish',
	  'guid'			=> 'csa-wp-plugin-my-orders',
	  'post_type'		=> 'page',
	  'comment_status'  => 'closed'
	);

	// Insert the post into the database
	$ordersPageID = wp_insert_post( $my_post );
	update_option( 'csa-wp-plugin-orders-page', $ordersPageID );	
}

function CsaWpPluginEnqueueCsaScripts() {
	wp_register_script('CsaWpPluginScripts', plugins_url('/csa-wp-javascripts.js', __FILE__));
	wp_register_script('CsaWpPluginSpotsScripts', plugins_url('/csa-wp-spots.js', __FILE__));
	wp_register_script('CsaWpPluginProductCategoriesScripts', plugins_url('/csa-wp-product_categories.js', __FILE__));
	wp_register_script('CsaWpPluginProductsScripts', plugins_url('/csa-wp-products.js', __FILE__));
	wp_register_script('CsaWpPluginDeliveriesScripts', plugins_url('/csa-wp-deliveries.js', __FILE__));	
	wp_register_script('CsaWpPluginOrdersScripts', plugins_url('/csa-wp-orders.js', __FILE__));	
	wp_register_script('jquery.datatables', plugins_url('/deps/jquery.datatables.js', __FILE__) );
	wp_register_script('jquery.jeditable', plugins_url('/deps/jquery.jeditable.js', __FILE__));
	wp_register_script('jquery.blockui', plugins_url('/deps/jquery.blockui.js', __FILE__));
	wp_register_script('jquery.cluetip', plugins_url('/deps/jquery.cluetip.js', __FILE__));
	wp_register_style('jquery.cluetip.style', plugins_url('/deps/jquery.cluetip.css', __FILE__));
	
	wp_register_style('CsaWpPluginStyle', plugins_url('/csa-wp-plugin-style.css', __FILE__));
	wp_enqueue_style('CsaWpPluginStyle');
}

add_action('admin_enqueue_scripts','CsaWpPluginEnqueueCsaScripts');
add_action('wp_enqueue_scripts','CsaWpPluginEnqueueCsaScripts');


/*	********************************
	CREATION OF ADMINISTRATION PANEL
	********************************	
*/

include 'csa-wp-users.php';
include 'csa-wp-spots.php';
include 'csa-wp-products.php';
include 'csa-wp-product_categories.php';
include 'csa-wp-deliveries.php';
include 'csa-wp-orders.php';
include 'csa-wp-administration_panel.php';

function CsaWpPluginUnistall() {
	UnRegisterCSASettings();
	CsaWpPluginDBTablesDrop();
	CsaWpPluginDeleteUsers();
}

function UnRegisterCSASettings() {
	unregister_setting(csaOptionsGroup, 'csa-wp-plugin-db_version');  
	delete_option('csa-wp-plugin-db_version');  
		
	wp_delete_post(get_option('csa-wp-plugin-manage-products-page'));
	delete_option('csa-wp-plugin-manage-products-page');  
	
	wp_delete_post(get_option('csa-wp-plugin-manage-orders-page'));
	delete_option('csa-wp-plugin-manage-orders-page');  

	wp_delete_post(get_option('csa-wp-plugin-administration-page'));
	delete_option('csa-wp-plugin-administration-page');  	
	
	wp_delete_post(get_option('csa-wp-plugin-orders-page'));
	delete_option('csa-wp-plugin-orders-page');  
}
register_deactivation_hook(__FILE__, 'CsaWpPluginUnistall');

?>