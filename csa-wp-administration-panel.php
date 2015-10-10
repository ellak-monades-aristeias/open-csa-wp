<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*	********************************
	CREATION OF ADMINISTRATION PANEL
	********************************	
*/

if ( is_admin() ) { // admin actions
	add_action( 'admin_menu', 'csa_wp_plugin_menu' );
}

function csa_wp_plugin_menu() {
	$parent_slug = 'csa_management';
	add_menu_page( 'CSA Management', 'CSA', 'manage_options', $parent_slug );
	add_submenu_page( $parent_slug, 'Manage CSA Products', 'Products', 'manage_options', $parent_slug, 'csa_wp_plugin_products_menu_back_end');
	add_submenu_page( $parent_slug, 'Manage CSA Spots', 'Spots', 'manage_options', 'csa_spots_management', 'csa_wp_plugin_spots_menu');
	add_submenu_page( $parent_slug, 'Manage CSA Deliveries', 'Deliveries', 'manage_options', 'csa_deliveries_management', 'csa_wp_plugin_deliveries_menu');
	add_submenu_page( $parent_slug, 'Manage CSA Orders', 'Orders', 'manage_options', 'csa_orders_management', 'csa_wp_plugin_orders_menu_back_end');		
	add_submenu_page( $parent_slug, 'Manage CSA Users', 'Users', 'manage_options', 'csa_users_management', 'csa_wp_plugin_users_menu');

}

function csa_wp_plugin_products_menu_back_end() {csa_wp_plugin_products_menu(true);}
function csa_wp_plugin_products_menu_front_end() {csa_wp_plugin_products_menu(false);}
add_shortcode('csa_wp_plugin_manage_products', 'csa_wp_plugin_products_menu_front_end');

function csa_wp_plugin_products_menu($back_end_bool) {
	if (csa_wp_plugin_user_can_manage("Manage Products") === true) {
		echo '<div class="wrap">';
		if ($back_end_bool === true) {
			echo '<h2>CSA Management Panel</h2>';
		} else {
			echo "<h3>Manage Products </h3>";
		}
		
		$redirection_url = csa_wp_plugin_get_redirection_url($back_end_bool, "csa_management");
		
		if (isset($_GET["id"])) {
			csa_wp_plugin_show_new_product_form($_GET["id"], true, $redirection_url);
		} else {
			global $wpdb;
			if ($wpdb->get_var("SELECT COUNT(id) FROM " .CSA_WP_PLUGIN_TABLE_PRODUCT_CATEGORIES) == 0) {
				csa_wp_plugin_show_new_product_category_form(true);
			} else {
				csa_wp_plugin_show_new_product_category_form(false);
				csa_wp_plugin_show_product_categories(false);
				
				if ($wpdb->get_var("SELECT COUNT(id) FROM " .CSA_WP_PLUGIN_TABLE_PRODUCTS) == 0) {
					csa_wp_plugin_show_new_product_form(null, true, $redirection_url);
				} else { 
					csa_wp_plugin_show_new_product_form(null, false, $redirection_url);
					csa_wp_plugin_show_products(true, $redirection_url);
				}
			}
		}
		echo '</div>';
	}
}

function csa_wp_plugin_spots_menu() {
	if (csa_wp_plugin_user_can_manage("Manage Spots") === true) {

		echo '<div class="wrap">';
		echo '<h2>CSA Management Panel</h2>';

		global $wpdb;
		if (isset($_GET["id"])) {
			csa_wp_plugin_spot_form($_GET["id"], true);
		} else if ($wpdb->get_var("SELECT COUNT(id) FROM " .CSA_WP_PLUGIN_TABLE_SPOTS) == 0) {
			csa_wp_plugin_spot_form(null, true);
		} else {
			csa_wp_plugin_spot_form(null, false);
			csa_wp_plugin_show_spots();	
		}
		echo '</div>';
	}
}

function csa_wp_plugin_deliveries_menu() {
	if (csa_wp_plugin_user_can_manage("Manage Deliveries") === true) {

		echo '<div class="wrap">';
		echo '<h2>CSA Management Panel</h2>';

		global $wpdb;
		
		if (csa_wp_plugin_delivery_spots_exist(false) === true) {
			if (isset($_GET["id"])){
				$spot_id = $_GET["id"];
				csa_wp_plugin_new_delivery_form($spot_id, null, array(), null, true);
			}
			else if (isset($_GET["delivery_id"])){
				$delivery_id = $_GET["delivery_id"];
				$delivery_info = $wpdb->get_results($wpdb->prepare("SELECT spot_id,order_deadline_date FROM ".CSA_WP_PLUGIN_TABLE_DELIVERIES." WHERE id=%d", $delivery_id))[0];
				$spot_id = $delivery_info -> spot_id;
				$order_deadline_date = $delivery_info -> order_deadline_date;
				csa_wp_plugin_new_delivery_form($spot_id, $order_deadline_date, array(), $delivery_id, true);
			}
			else if (isset($_POST["csa-wp-plugin-newDelivery_spotID_choice"])) {
				
				$spot_id = $_POST["csa-wp-plugin-newDelivery_spotID_choice"];
				
				$custom_values = array();
				if (isset($_POST["csa-wp-plugin-newDelivery_orderDeadlineDate_choice"])) {
					$custom_values = csa_wp_plugin_return_custom_values_for_new_delivery($spot_id);
				}
				
				$delivery_id = null;
				if (isset($_POST["csa-wp-plugin-newDelivery_deliveryID_choice"])) {
					$delivery_id = $_POST["csa-wp-plugin-newDelivery_deliveryID_choice"];
				}

				$order_deadline_date = null;
				if (isset($_POST["csa-wp-plugin-newDelivery_orderDeadlineDate_choice"])) {
					$order_deadline_date = explode(";",$_POST["csa-wp-plugin-newDelivery_orderDeadlineDate_choice"])[0];
				}

				csa_wp_plugin_new_delivery_form($spot_id, $order_deadline_date, $custom_values, $delivery_id, true);
			}
			else if ($wpdb->get_var("SELECT COUNT(id) FROM " .CSA_WP_PLUGIN_TABLE_DELIVERIES) > 0) {
				csa_wp_plugin_new_delivery_form(null, null, array(), null, false);
				csa_wp_plugin_show_deliveries(true);
			}
			else {
				csa_wp_plugin_new_delivery_form(null, null, array(), null, true); 
			}
		}
	}
}

function csa_wp_plugin_orders_menu_back_end() {csa_wp_plugin_orders_menu(true, false);}
function csa_wp_plugin_orders_menu_front_end() {csa_wp_plugin_orders_menu(false, false);}
add_shortcode('csa_wp_plugin_manage_user_orders', 'csa_wp_plugin_orders_menu_front_end');

function csa_wp_plugin_orders_manage_my_orders() {csa_wp_plugin_orders_menu(false, true);}
add_shortcode('csa_wp_plugin_manage_my_orders', 'csa_wp_plugin_orders_manage_my_orders');

function csa_wp_plugin_orders_menu($back_end_bool, $personal_order) {

	if (!is_user_logged_in()) {
		echo "<h6 style='color:brown'> sorry... you need to log in first...</h6>";
	} else if ($personal_order === true || csa_wp_plugin_user_can_manage("Manage User Orders") === true) {

		echo '<div class="wrap">';

		if ($back_end_bool === true) {
			echo "<h2>CSA Management Panel</h2>";
		} else if ($personal_order === false) {
			echo "<h3>Manage User Orders</h3>";
		} else {
			echo "<h3>Manage Your Orders</h3>";
		}

		$redirection_url = csa_wp_plugin_get_redirection_url($back_end_bool, "csa_orders_management");
		
		global $wpdb;
		
		if (csa_wp_plugin_delivery_spots_exist($personal_order) === true && csa_wp_plugin_delivery_products_exist($personal_order) === true) {
			$user_id = null;
			$spot_id = null;
			$delivery_id = null;
			
			if (isset($_POST["csa-wp-plugin-showTotalOrdersOfDelivery_delivery_input"])) {
				$delivery_id = $_POST["csa-wp-plugin-showTotalOrdersOfDelivery_delivery_input"];
				if (isset($_POST["csa-wp-plugin-showTotalOrdersOfDelivery_producer_input"]))
					csa_wp_plugin_show_total_orders_of_delivery($delivery_id, $_POST["csa-wp-plugin-showTotalOrdersOfDelivery_producer_input"], $redirection_url);
				else csa_wp_plugin_show_total_orders_of_delivery($delivery_id, null, $redirection_url);
			} else if (isset($_POST["csa-wp-plugin-showEditableUserOrderForm_user_input"])) {
				$user_id = $_POST["csa-wp-plugin-showEditableUserOrderForm_user_input"];
				$delivery_id = $_POST["csa-wp-plugin-showEditableUserOrderForm_delivery_input"];
				$spot_id = $wpdb->get_var($wpdb->prepare("SELECT spot_id FROM ". CSA_WP_PLUGIN_TABLE_DELIVERIES ." WHERE id=%d", $delivery_id));
				csa_wp_plugin_show_order_form($user_id, $spot_id, $delivery_id, true, $redirection_url, $personal_order);
			} else {
				if ($personal_order === true) {
					$user_id = get_current_user_id();
				} else if (isset($_POST["csa-wp-plugin-showNewOrderForm_user_input"])) {
					$user_id = $_POST["csa-wp-plugin-showNewOrderForm_user_input"];
				}
				
				if (isset($_POST["csa-wp-plugin-showSelectSpotForm_spot_input"])) {
					$spot_id = $_POST["csa-wp-plugin-showSelectSpotForm_spot_input"];
				}
					
				if (isset($_POST["csa-wp-plugin-showSelectSpotForm_delivery_input"])) {
					$delivery_id = $_POST["csa-wp-plugin-showSelectSpotForm_delivery_input"];
				}
				
				if ($user_id!=null && ($personal_order === false || $spot_id != null)) {
					csa_wp_plugin_show_order_form($user_id, $spot_id, $delivery_id, true, $redirection_url, $personal_order);
				} else { 
					$show_new_order_form = csa_wp_plugin_active_deliveries_exist() === true ? true:false;
					$show_orders = $wpdb->get_var("SELECT COUNT(*) FROM " .CSA_WP_PLUGIN_TABLE_USER_ORDERS) > 0 ? true:false;
					
					if ($show_orders === false && $show_new_order_form === true) {
						csa_wp_plugin_show_order_form($user_id, $spot_id, $delivery_id, true, $redirection_url, $personal_order);
					} else if ($show_orders === true) {
						csa_wp_plugin_show_order_form($user_id, $spot_id, $delivery_id, false, $redirection_url, $personal_order);
						
						if($personal_order === false) {
							csa_wp_plugin_show_all_user_orders(null, false);
							csa_wp_plugin_show_delivery_orders_list ($user_id, false, true);
						} else {
							csa_wp_plugin_show_all_user_orders($user_id, true);
							
							$user_data = get_user_meta($user_id, 'csa-wp-plugin_user', true ); 
							
							if ($user_data != null) {
								if($user_data['role'] == "responsible" || $user_data['role'] == "administrator") { 
									echo "<h3>Total Orders of Deliveries You Are Responsible</h3>";
									csa_wp_plugin_show_delivery_orders_list ($user_id, false, true);	
								}
								if ($user_data['type'] == "producer" || $user_data['type'] == "both") {
									echo "<h3>Your Total Orders per Delivery</h3>";	
									csa_wp_plugin_show_delivery_orders_list ($user_id, true, true);
								}
							}
						}
					}
				}
			}
		}
		
		echo '</div>';
	}
}

function csa_wp_plugin_users_menu() {
	if ( !current_user_can( 'administrator' ) &&
		(!($csa_data = get_user_meta( $user->ID, 'csa-wp-plugin_user', true )) || $csa_data['role'] != "administrator" )
	) {	
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	?>
	<script>
	window.location.replace("<?php echo admin_url("/users.php"); ?>");
	</script>
<?php
}

function csa_wp_plugin_user_can_manage($page_name) {
	if (!is_user_logged_in()) {
		echo "<h6 style='color:brown'> sorry... you need to log in first...</h6><br/>";
		return false;
	}
	if (!current_user_can( 'administrator' ) &&
		(!($csa_data = get_user_meta(get_current_user_id(), 'csa-wp-plugin_user', true )) || $csa_data['role'] != "administrator" )
	) {	
		echo "<h6 style='color:brown'> sorry... you do not have sufficient privileges to access \"$page_name\" page. In case something goes wrong, please contant one of your team's administrators...</h6><br/>";
		return false;
	}
	else return true;
}

?>