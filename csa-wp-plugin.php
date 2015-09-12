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

add_action('wp_head','csa_wp_plugin_ajaxurl');

function csa_wp_plugin_ajaxurl() {
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
define("csaOrders", $wpdb->prefix."csa_orders");
define("csaProducts", $wpdb->prefix."csa_products");
include 'csa-wp-DB_tables_creation.php';

function CsaWpPluginAvtivation() {
	CsaWpPluginDBTablesCreation();
	CsaWpPluginDBAddElements();
}
register_activation_hook( __FILE__, 'CsaWpPluginAvtivation' );

function CsaWpPluginEnqueueCsaScripts() {
	wp_register_script('CsaWpPluginScripts', plugins_url('/csa-wp-javascripts.js', __FILE__));
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
include 'csa-wp-administration_panel.php';
include 'csa-wp-products.php';
include 'csa-wp-orders.php';

function CsaWpPluginUnistall() {
	UnRegisterCSASettings();
	CsaWpPluginDBTablesDrop();
}
register_deactivation_hook(__FILE__, 'CsaWpPluginUnistall');

?>