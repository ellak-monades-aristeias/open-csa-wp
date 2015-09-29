<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*	********************************
	CREATION OF ADMINISTRATION PANEL
	********************************	
*/

if ( is_admin() ){ // admin actions
	add_action( 'admin_menu', 'CsaWpPluginMenu' );
	add_action( 'admin_init', 'RegisterCSASettings' );
}


function CsaWpPluginMenu() {
	$parent_slug = 'csa_management';
	add_menu_page( 'CSA Management', 'CSA', 'manage_options', $parent_slug );
	add_submenu_page( $parent_slug, 'Manage CSA Products', 'Products', 'manage_options', $parent_slug, 'CsaWpPluginProductsMenu');
	add_submenu_page( $parent_slug, 'Manage CSA Spots', 'Spots', 'manage_options', 'csa_spots_management', 'CsaWpPluginSpotsMenu');
	add_submenu_page( $parent_slug, 'Manage CSA Deliveries', 'Deliveries', 'manage_options', 'csa_deliveries_management', 'CsaWpPluginDeliveriesMenu');
	add_submenu_page( $parent_slug, 'Manage CSA Orders', 'Orders', 'manage_options', 'csa_orders_management', 'CsaWpPluginOrdersMenu');		
	add_submenu_page( $parent_slug, 'Manage CSA Users', 'Users', 'manage_options', 'csa_users_management', 'CsaWpPluginUsersMenu');

}

function CsaWpPluginProductsMenu() {
	if ( !current_user_can( 'administrator' ) &&
		(!($csaData = get_user_meta( $user->ID, 'csa-wp-plugin_user', true )) || $csaData['role'] != "administrator" )
	)	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

	echo '<div class="wrap">';
	echo '<h2>CSA Management Panel</h2>';
	
	
	if (isset($_GET["id"])) 
		CsaWpPluginShowNewProductForm($_GET["id"], true);
	else {
		global $wpdb;
		if (count($wpdb->get_results("SELECT id FROM " .csaProductCategories)) == 0) {
			
			CsaWpPluginShowNewProductCategoryForm(true);
		}
		else {
			CsaWpPluginShowNewProductCategoryForm(false);
			CsaWpPluginShowProductCategories(false);
			
			if (count($wpdb->get_results("SELECT id FROM " .csaProducts)) == 0)
				CsaWpPluginShowNewProductForm(null, true);
			else { 
				CsaWpPluginShowNewProductForm(null, false);
				CsaWpPluginShowProducts(true);
			}
		}
	}
	echo '</div>';
}

function CsaWpPluginSpotsMenu() {
	if ( !current_user_can( 'administrator' ) &&
		(!($csaData = get_user_meta( $user->ID, 'csa-wp-plugin_user', true )) || $csaData['role'] != "administrator" )
	)	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

	echo '<div class="wrap">';
	echo '<h2>CSA Management Panel</h2>';

	global $wpdb;
	if (isset($_GET["id"])) 
		CsaWpPluginSpotForm($_GET["id"], true);
	else if (count($wpdb->get_results("SELECT id FROM " .csaSpots)) == 0)
		CsaWpPluginSpotForm(null, true);
	else {
		CsaWpPluginSpotForm(null, false);
		CsaWpPluginShowSpots();	
	}
	echo '</div>';
}

function CsaWpPluginDeliveriesMenu() {
	if ( !current_user_can( 'administrator' ) &&
		(!($csaData = get_user_meta( $user->ID, 'csa-wp-plugin_user', true )) || $csaData['role'] != "administrator" )
	)	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

	echo '<div class="wrap">';
	echo '<h2>CSA Management Panel</h2>';

	global $wpdb;
	
	if (count($wpdb->get_results("SELECT id FROM " .csaSpots. " WHERE isDeliverySpot = 1")) == 0)
		echo "
			<h3 style='color:orangeorange'>sorry... no spots found...!</h3> 
			<h4 style='color:gray'>You must create at least one delivery spot, before you initiate a delivery (for some spot).</h4>
			<h4 style='color:gray'>To do so, use the corresponding menu of CSA Managemement Panel or simply click 
			<a href='".
				admin_url('/admin.php?page=csa_spots_management')
			."'>here </a></h4>
		";
	else {
		if (isset($_GET["id"])){
			$spotID = $_GET["id"];
			CsaWpPluginNewDeliveryForm($spotID, null, array(), null, true);
		}
		else if (isset($_GET["deliveryID"])){
			$deliveryID = $_GET["deliveryID"];
			$spotID = $wpdb->get_var($wpdb->prepare("SELECT spot_id FROM ".csaDeliveries." WHERE id=%d", $deliveryID));
			CsaWpPluginNewDeliveryForm($spotID, null, array(), $deliveryID, true);
		}
		else if (isset($_POST["csa-wp-plugin-newDelivery_spotID_choice"])) {
			
			$spotID = $_POST["csa-wp-plugin-newDelivery_spotID_choice"];
			
			$customValues = array();
			if (isset($_POST["csa-wp-plugin-newDelivery_orderDeadlineDate_choice"]))
				$customValues = CsaWpPluginReturnCustomValuesForNewDelivery($spotID);
			
			$deliveryID = null;
			if (isset($_POST["csa-wp-plugin-newDelivery_deliveryID_choice"]))
				$deliveryID = $_POST["csa-wp-plugin-newDelivery_deliveryID_choice"];

			$orderDeadlineDate = null;
			if (isset($_POST["csa-wp-plugin-newDelivery_orderDeadlineDate_choice"]))
				$orderDeadlineDate = explode(";",$_POST["csa-wp-plugin-newDelivery_orderDeadlineDate_choice"])[0];

			CsaWpPluginNewDeliveryForm($spotID, $orderDeadlineDate, $customValues, $deliveryID, true);
		}
		else if (count($wpdb->get_results("SELECT id FROM " .csaDeliveries)) > 0) {
			CsaWpPluginNewDeliveryForm(null, null, array(), false, null);  // i.e. CsaWpPluginNewDeliveryForm(null, $deliveryID, $orderDeadlineDate, false, array());
			CsaWpPluginShowDeliveries(true);
		}
		else CsaWpPluginNewDeliveryForm(null, null, array(), true, null);  // i.e. CsaWpPluginNewDeliveryForm(null, $deliveryID, $orderDeadlineDate, true, array());
	}
}

function CsaWpPluginOrdersMenu() {
	if ( !current_user_can( 'administrator' ) &&
		(!($csaData = get_user_meta( $user->ID, 'csa-wp-plugin_user', true )) || $csaData['role'] != "administrator" )
	)	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );

	echo '<div class="wrap">';
	echo "<h2>CSA Management Panel</h2>";

	CsaWpPluginUserOrder();
	
	echo '</div>';
}

function CsaWpPluginUsersMenu() {
	if ( !current_user_can( 'administrator' ) &&
		(!($csaData = get_user_meta( $user->ID, 'csa-wp-plugin_user', true )) || $csaData['role'] != "administrator" )
	)	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	?>
	<script>
	window.location.replace("<?php echo admin_url("/users.php"); ?>");
	</script>
<?php
}


function RegisterCSASettings() {
	register_setting(csaOptionsGroup, 'csa_delivery_day');
	register_setting(csaOptionsGroup, 'csa_last_delivery_date');  
	register_setting(csaOptionsGroup, 'csa_consumer_fee_percentage');
	register_setting(csaOptionsGroup, 'csa_producer_fee_percentage');  
	register_setting(csaOptionsGroup, 'csa-wp-plugin-db_version');  
}

function UnRegisterCSASettings() {
	unregister_setting(csaOptionsGroup, 'csa_delivery_day' );
	delete_option('csa_delivery_day');
	unregister_setting(csaOptionsGroup, 'csa_last_delivery_date' );
	delete_option('csa_last_delivery_date');  
	unregister_setting(csaOptionsGroup, 'csa_consumer_fee_percentage');
	delete_option('csa_consumer_fee_percentage');  
	unregister_setting(csaOptionsGroup, 'csa_producer_fee_percentage');  
	delete_option('csa_producer_fee_percentage');  
	unregister_setting(csaOptionsGroup, 'csa-wp-plugin-db_version');  
	delete_option('csa-wp-plugin-db_version');  
}
?>