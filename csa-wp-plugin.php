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
define("csaOrders", $wpdb->prefix."csa_wp_plugin_orders");

define("enterKeyCode", "13");
define("tabKeyCode", "9");

define("csa_wp_plugin_date_format", "Y-m-d");
define("csa_wp_plugin_date_format_readable", "d-M-Y");


$days = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");

include 'csa-wp-DB_tables_creation.php';

function CsaWpPluginAvtivation() {
	CsaWpPluginDBTablesCreation();
}
register_activation_hook( __FILE__, 'CsaWpPluginAvtivation' );

function CsaWpPluginEnqueueCsaScripts() {
	wp_register_script('CsaWpPluginScripts', plugins_url('/csa-wp-javascripts.js', __FILE__));
	wp_register_script('CsaWpPluginSpotsScripts', plugins_url('/csa-wp-spots.js', __FILE__));
	wp_register_script('CsaWpPluginProductCategoriesScripts', plugins_url('/csa-wp-product_categories.js', __FILE__));
	wp_register_script('CsaWpPluginProductsScripts', plugins_url('/csa-wp-products.js', __FILE__));
	wp_register_script('CsaWpPluginDeliveriesScripts', plugins_url('/csa-wp-deliveries.js', __FILE__));	
	wp_register_script('jquery.datatables', plugins_url('/deps/jquery.datatables.js', __FILE__) );
	wp_register_script('jquery.jeditable', plugins_url('/deps/jquery.jeditable.js', __FILE__));
	wp_register_script('jquery.blockui', plugins_url('/deps/jquery.blockui.js', __FILE__));
	wp_register_script('jquery.cluetip', plugins_url('/deps/jquery.cluetip.js', __FILE__));
	wp_register_style('jquery.cluetip.style', plugins_url('/deps/jquery.cluetip.css', __FILE__));
}
add_action('admin_enqueue_scripts','CsaWpPluginEnqueueCsaScripts');
add_action('wp_enqueue_scripts','CsaWpPluginEnqueueCsaScripts');


/*	********************************
	CREATION OF ADMINISTRATION PANEL
	********************************	
*/

define("csaOptionsGroup", "csa-options-group");
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
register_deactivation_hook(__FILE__, 'CsaWpPluginUnistall');

?>