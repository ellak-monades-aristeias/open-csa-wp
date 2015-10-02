<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*	********************************
	CREATION OF ADMINISTRATION PANEL
	********************************	
*/

if ( is_admin() ){ // admin actions
	add_action( 'admin_menu', 'CsaWpPluginMenu' );
//	add_action( 'admin_init', 'RegisterCSASettings' );
}

function CsaWpPluginMenu() {
	$parent_slug = 'csa_management';
	add_menu_page( 'CSA Management', 'CSA', 'manage_options', $parent_slug );
	add_submenu_page( $parent_slug, 'Manage CSA Products', 'Products', 'manage_options', $parent_slug, 'CsaWpPluginProductsMenuBackEnd');
	add_submenu_page( $parent_slug, 'Manage CSA Spots', 'Spots', 'manage_options', 'csa_spots_management', 'CsaWpPluginSpotsMenu');
	add_submenu_page( $parent_slug, 'Manage CSA Deliveries', 'Deliveries', 'manage_options', 'csa_deliveries_management', 'CsaWpPluginDeliveriesMenu');
	add_submenu_page( $parent_slug, 'Manage CSA Orders', 'Orders', 'manage_options', 'csa_orders_management', 'CsaWpPluginOrdersMenuBackEnd');		
	add_submenu_page( $parent_slug, 'Manage CSA Users', 'Users', 'manage_options', 'csa_users_management', 'CsaWpPluginUsersMenu');

}

function CsaWpPluginProductsMenuBackEnd() {CsaWpPluginProductsMenu(true);}
function CsaWpPluginProductsMenuFrontEnd() {CsaWpPluginProductsMenu(false);}
add_shortcode('csa-wp-plugin-manageProducts', 'CsaWpPluginProductsMenuFrontEnd');

function CsaWpPluginProductsMenu($backEndBool) {
	if (CsaWpPluginUserCanManage("Manage Products") === true) {
		echo '<div class="wrap">';
		if ($backEndBool === true)
			echo '<h2>CSA Management Panel</h2>';
		else echo "<h3>Manage Products </h3>";
		
		$redirectionURL = CsaWpPluginGetRedirectionURL($backEndBool);
		
		if (isset($_GET["id"])) 
			CsaWpPluginShowNewProductForm($_GET["id"], true, $redirectionURL);
		else {
			global $wpdb;
			if (count($wpdb->get_results("SELECT id FROM " .csaProductCategories)) == 0)
				CsaWpPluginShowNewProductCategoryForm(true);
			else {
				CsaWpPluginShowNewProductCategoryForm(false);
				CsaWpPluginShowProductCategories(false);
				
				if (count($wpdb->get_results("SELECT id FROM " .csaProducts)) == 0)
					CsaWpPluginShowNewProductForm(null, true, $redirectionURL);
				else { 
					CsaWpPluginShowNewProductForm(null, false, $redirectionURL);
					CsaWpPluginShowProducts(true, $redirectionURL);
				}
			}
		}
		echo '</div>';
	}
}

function CsaWpPluginSpotsMenu() {
	if (CsaWpPluginUserCanManage("Manage Spots") === true) {

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
}

function CsaWpPluginDeliveriesMenu() {
	if (CsaWpPluginUserCanManage("Manage Deliveries") === true) {

		echo '<div class="wrap">';
		echo '<h2>CSA Management Panel</h2>';

		global $wpdb;
		
		if (CsaWpPluginDeliverySpotsExist(false) === true) {
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
				CsaWpPluginNewDeliveryForm(null, null, array(), null, false);
				CsaWpPluginShowDeliveries(true);
			}
			else {
				CsaWpPluginNewDeliveryForm(null, null, array(), null, true); 
			}
		}
	}
}

function CsaWpPluginOrdersMenuBackEnd() {CsaWpPluginOrdersMenu(true, false);}
function CsaWpPluginOrdersMenuFrontEnd() {CsaWpPluginOrdersMenu(false, false);}
add_shortcode('csa-wp-plugin-manageUserOrders', 'CsaWpPluginOrdersMenuFrontEnd');

function CsaWpPluginOrdersManageMyOrders() {CsaWpPluginOrdersMenu(false, true);}
add_shortcode('csa-wp-plugin-manageMyOrders', 'CsaWpPluginOrdersManageMyOrders');

function CsaWpPluginOrdersMenu($backEndBool, $personalOrder) {

	if (!is_user_logged_in())
		echo "<h6 style='color:brown'> sorry... you need to log in first...</h6>";
	else if ($personalOrder === true || CsaWpPluginUserCanManage("Manage User Orders") === true) {

		echo '<div class="wrap">';

		if ($backEndBool === true) 
			echo "<h2>CSA Management Panel</h2>";
		else if ($personalOrder === false)
			echo "<h3>Manage User Orders</h3>";
		else echo "<h3>Manage Your Orders</h3>";

		$redirectionURL = CsaWpPluginGetRedirectionURL($backEndBool);
		
		global $wpdb;
		
		if (CsaWpPluginDeliverySpotsExist($personalOrder) === true && CsaWpPluginDeliveryProductsExist($personalOrder) === true) {
			$userID = null;
			$spotID = null;
			$deliveryID = null;
			
			if (isset($_POST["csa-wp-plugin-showTotalOrdersOfDelivery_delivery_input"])) {
				$deliveryID = $_POST["csa-wp-plugin-showTotalOrdersOfDelivery_delivery_input"];
				if (isset($_POST["csa-wp-plugin-showTotalOrdersOfDelivery_producer_input"]))
					CsaWpPluginShowTotalOrdersOfDelivery($deliveryID, $_POST["csa-wp-plugin-showTotalOrdersOfDelivery_producer_input"], $redirectionURL);
				else CsaWpPluginShowTotalOrdersOfDelivery($deliveryID, null, $redirectionURL);
			}
			else if (isset($_POST["csa-wp-plugin-showEditableUserOrderForm_user_input"])) {
				$userID = $_POST["csa-wp-plugin-showEditableUserOrderForm_user_input"];
				$deliveryID = $_POST["csa-wp-plugin-showEditableUserOrderForm_delivery_input"];
				$spotID = $wpdb->get_var($wpdb->prepare("SELECT spot_id FROM ". csaDeliveries ." WHERE id=%d", $deliveryID));
				CsaWpPluginShowOrderForm($userID, $spotID, $deliveryID, true, $redirectionURL, $personalOrder);
			}	
			else {
				if ($personalOrder === true)
					$userID = get_current_user_id();
				else if (isset($_POST["csa-wp-plugin-showNewOrderForm_user_input"]))
					$userID = $_POST["csa-wp-plugin-showNewOrderForm_user_input"];
				
				if (isset($_POST["csa-wp-plugin-showSelectSpotForm_spot_input"]))
					$spotID = $_POST["csa-wp-plugin-showSelectSpotForm_spot_input"];
					
				if (isset($_POST["csa-wp-plugin-showSelectSpotForm_delivery_input"]))
					$deliveryID = $_POST["csa-wp-plugin-showSelectSpotForm_delivery_input"];
				
				if ($userID!=null && ($personalOrder === false || $spotID != null))
					CsaWpPluginShowOrderForm($userID, $spotID, $deliveryID, true, $redirectionURL, $personalOrder);
				else { 
					$showNewOrderForm = CsaWpPluginActiveDeliveriesExist() === true ? true:false;
					$showOrders = $wpdb->get_var("SELECT COUNT(*) FROM " .csaUserOrders) > 0 ? true:false;
					
					if ($showOrders === false && $showNewOrderForm === true)
						CsaWpPluginShowOrderForm($userID, $spotID, $deliveryID, true, $redirectionURL, $personalOrder);

					else if ($showOrders === true) {
						CsaWpPluginShowOrderForm($userID, $spotID, $deliveryID, false, $redirectionURL, $personalOrder);
						
						if($personalOrder === false) {
							CsaWpPluginShowAllUserOrders(null, false);
							CsaWpPluginShowDeliveryOrdersList ($userID, false, true);
						}
						else {
							CsaWpPluginShowAllUserOrders($userID, true);
							echo "<h3>Total Orders of Deliveries You Are Responsible</h3>";
							CsaWpPluginShowDeliveryOrdersList ($userID, false, true);
							
							$userData = get_user_meta($userID, 'csa-wp-plugin_user', true ); 
							if ($userData != null && ($userData['type'] == "producer" || $userData['type'] == "both")) {
								echo "<h3>Your Total Orders per Delivery</h3>";	
								CsaWpPluginShowDeliveryOrdersList ($userID, true, true);
							}
						}
					}
				}
			}
		}
		
		echo '</div>';
	}
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

function CsaWpPluginUserCanManage($pageName) {
	if (!is_user_logged_in()) {
		echo "<h6 style='color:brown'> sorry... you need to log in first...</h6><br/>";
		return false;
	}
	if (!current_user_can( 'administrator' ) &&
		(!($csaData = get_user_meta(get_current_user_id(), 'csa-wp-plugin_user', true )) || $csaData['role'] != "administrator" )
	) {	
		echo "<h6 style='color:brown'> sorry... you do not have sufficient privileges to access \"$pageName\" page. In case something goes wrong, please contant one of your team's administrators...</h6><br/>";
		return false;
	}
	else return true;
}

?>