<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*	********************************
	CREATION OF ADMINISTRATION PANEL
	********************************	
*/

if ( is_admin() ) { // admin actions
	add_action( 'admin_menu', 'open_csa_wp_menu' );
}

function open_csa_wp_menu() {
	$parent_slug = 'csa_management';
	add_menu_page( __('CSA Management', OPEN_CSA_WP_DOMAIN), __('CSA',OPEN_CSA_WP_DOMAIN), 'manage_options', $parent_slug );
	add_submenu_page( $parent_slug, __('Manage CSA Products', OPEN_CSA_WP_DOMAIN), __('Products',OPEN_CSA_WP_DOMAIN), 'manage_options', $parent_slug, 'open_csa_wp_products_menu_back_end');
	add_submenu_page( $parent_slug, __('Manage CSA Spots',OPEN_CSA_WP_DOMAIN), __('Spots',OPEN_CSA_WP_DOMAIN), 'manage_options', 'csa_spots_management', 'open_csa_wp_spots_menu');
	add_submenu_page( $parent_slug, __('Manage CSA Deliveries',OPEN_CSA_WP_DOMAIN), __('Deliveries',OPEN_CSA_WP_DOMAIN), 'manage_options', 'csa_deliveries_management', 'open_csa_wp_deliveries_menu');
	add_submenu_page( $parent_slug, __('Manage CSA Orders',OPEN_CSA_WP_DOMAIN), __('Orders',OPEN_CSA_WP_DOMAIN), 'manage_options', 'csa_orders_management', 'open_csa_wp_orders_menu_back_end');		
	add_submenu_page( $parent_slug, __('Manage CSA Users',OPEN_CSA_WP_DOMAIN), __('Users',OPEN_CSA_WP_DOMAIN), 'manage_options', 'csa_users_management', 'open_csa_wp_users_menu');

}

/*	*************
	PRODUCTS MENU
	*************	
*/


function open_csa_wp_products_menu_back_end() {open_csa_wp_products_menu(true);}
function open_csa_wp_products_menu_front_end() {open_csa_wp_products_menu(false);}
add_shortcode('open_csa_wp_manage_products', 'open_csa_wp_products_menu_front_end');

function open_csa_wp_products_menu($back_end_bool) {
	if (open_csa_wp_user_can("Manage Products") === true) {
		echo '<div class="wrap">';
		if ($back_end_bool === true) {
			echo '<h2>'.__('CSA Management Panel',OPEN_CSA_WP_DOMAIN).'</h2>';
		} else {
			echo "<h3>".__('Manage Products',OPEN_CSA_WP_DOMAIN)." </h3>";
		}
		
		$redirection_url = open_csa_wp_get_redirection_url($back_end_bool, "csa_management");
		
		if (isset($_GET["id"])) {
			open_csa_wp_show_new_product_form($_GET["id"], true, $redirection_url);
		} else {
			global $wpdb;
			if ($wpdb->get_var("SELECT COUNT(id) FROM " .OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES) == 0) {
				open_csa_wp_show_new_product_category_form(true);
			} else {
				open_csa_wp_show_new_product_category_form(false);
				open_csa_wp_show_product_categories(false);
				
				if ($wpdb->get_var("SELECT COUNT(id) FROM " .OPEN_CSA_WP_TABLE_PRODUCTS) == 0) {
					open_csa_wp_show_new_product_form(null, true, $redirection_url);
				} else { 
					open_csa_wp_show_new_product_form(null, false, $redirection_url);
					open_csa_wp_show_products(true, $redirection_url);
				}
			}
		}
		echo '</div>';
	}
}

/*	**********
	SPOTS MENU
	**********	
*/

function open_csa_wp_spots_menu() {
	if (open_csa_wp_user_can("Manage Spots") === true) {

		echo '<div class="wrap">';
		echo '<h2>'.__('CSA Management Panel',OPEN_CSA_WP_DOMAIN).'</h2>';

		global $wpdb;
		if (isset($_GET["id"])) {
			open_csa_wp_spot_form($_GET["id"], true);
		} else if ($wpdb->get_var("SELECT COUNT(id) FROM " .OPEN_CSA_WP_TABLE_SPOTS) == 0) {
			open_csa_wp_spot_form(null, true);
		} else {
			open_csa_wp_spot_form(null, false);
			open_csa_wp_show_spots();	
		}
		echo '</div>';
	}
}

/*	***************
	DELIVERIES MENU
	***************	
*/

function open_csa_wp_deliveries_menu() {
	if (open_csa_wp_user_can("Manage Deliveries") === true) {

		echo '<div class="wrap">';
		echo '<h2>'.__('CSA Management Panel',OPEN_CSA_WP_DOMAIN).'</h2>';

		global $wpdb;
		
		if (open_csa_wp_delivery_spots_exist(false) === true) {
			if (isset($_GET["id"])){
				$spot_id = $_GET["id"];
				open_csa_wp_new_delivery_form($spot_id, null, array(), null, true);
			}
			else if (isset($_GET["delivery_id"])){
				$delivery_id = $_GET["delivery_id"];
				$delivery_info = $wpdb->get_results($wpdb->prepare("SELECT spot_id,order_deadline_date FROM ".OPEN_CSA_WP_TABLE_DELIVERIES." WHERE id=%d", $delivery_id))[0];
				$spot_id = $delivery_info -> spot_id;
				$order_deadline_date = $delivery_info -> order_deadline_date;
				open_csa_wp_new_delivery_form($spot_id, $order_deadline_date, array(), $delivery_id, true);
			}
			else if (isset($_POST["open-csa-wp-newDelivery_spotID_choice"])) {
				
				$spot_id = $_POST["open-csa-wp-newDelivery_spotID_choice"];
				
				$custom_values = array();
				if (isset($_POST["open-csa-wp-newDelivery_orderDeadlineDate_choice"])) {
					$custom_values = open_csa_wp_return_custom_values_for_new_delivery($spot_id);
				}
				
				$delivery_id = null;
				if (isset($_POST["open-csa-wp-newDelivery_deliveryID_choice"])) {
					$delivery_id = $_POST["open-csa-wp-newDelivery_deliveryID_choice"];
				}

				$order_deadline_date = null;
				if (isset($_POST["open-csa-wp-newDelivery_orderDeadlineDate_choice"])) {
					$order_deadline_date = explode(";",$_POST["open-csa-wp-newDelivery_orderDeadlineDate_choice"])[0];
				}

				open_csa_wp_new_delivery_form($spot_id, $order_deadline_date, $custom_values, $delivery_id, true);
			}
			else if ($wpdb->get_var("SELECT COUNT(id) FROM " .OPEN_CSA_WP_TABLE_DELIVERIES) > 0) {
				open_csa_wp_new_delivery_form(null, null, array(), null, false);
				open_csa_wp_show_deliveries(true);
			}
			else {
				open_csa_wp_new_delivery_form(null, null, array(), null, true); 
			}
		}
	}
}

/*	***********
	ORDERS MENU
	***********	
*/

function open_csa_wp_orders_menu_back_end() {open_csa_wp_orders_menu(true, false);}
function open_csa_wp_orders_menu_front_end() {open_csa_wp_orders_menu(false, false);}
add_shortcode('open_csa_wp_manage_user_orders', 'open_csa_wp_orders_menu_front_end');

function open_csa_wp_orders_manage_my_orders() {open_csa_wp_orders_menu(false, true);}
add_shortcode('open_csa_wp_manage_my_orders', 'open_csa_wp_orders_manage_my_orders');

function open_csa_wp_orders_menu($back_end_bool, $personal_order) {

	if (!is_user_logged_in()) {
		echo "<h6 style='color:brown'>". __('sorry... you need to log in first...',OPEN_CSA_WP_DOMAIN)."</h6>";
	} else if ($personal_order === true || open_csa_wp_user_can("Manage User Orders") === true) {

		echo '<div class="wrap">';

		if ($back_end_bool === true) {
			echo "<h2>".__('CSA Management Panel',OPEN_CSA_WP_DOMAIN)."</h2>";
		} else if ($personal_order === false) {
			echo "<h3>".__('Manage User Orders',OPEN_CSA_WP_DOMAIN)."</h3>";
		} else {
			echo "<h3>".__('Manage Your Orders',OPEN_CSA_WP_DOMAIN)."</h3>";
		}

		$redirection_url = open_csa_wp_get_redirection_url($back_end_bool, "csa_orders_management");
		
		global $wpdb;
		
		if (open_csa_wp_delivery_spots_exist($personal_order) === true && open_csa_wp_delivery_products_exist($personal_order) === true) {
			$user_id = null;
			$spot_id = null;
			$delivery_id = null;
			
			if (isset($_POST["open-csa-wp-showTotalOrdersOfDelivery_delivery_input"])) {
				$delivery_id = $_POST["open-csa-wp-showTotalOrdersOfDelivery_delivery_input"];
				if (isset($_POST["open-csa-wp-showTotalOrdersOfDelivery_producer_input"]))
					open_csa_wp_show_total_orders_of_delivery($delivery_id, $_POST["open-csa-wp-showTotalOrdersOfDelivery_producer_input"], $redirection_url);
				else open_csa_wp_show_total_orders_of_delivery($delivery_id, null, $redirection_url);
			} else if (isset($_POST["open-csa-wp-showEditableUserOrderForm_user_input"])) {
				$user_id = $_POST["open-csa-wp-showEditableUserOrderForm_user_input"];
				$delivery_id = $_POST["open-csa-wp-showEditableUserOrderForm_delivery_input"];
				$spot_id = $wpdb->get_var($wpdb->prepare("SELECT spot_id FROM ". OPEN_CSA_WP_TABLE_DELIVERIES ." WHERE id=%d", $delivery_id));
				open_csa_wp_show_order_form($user_id, $spot_id, $delivery_id, true, $redirection_url, $personal_order);
			} else {
				if ($personal_order === true) {
					$user_id = get_current_user_id();
				} else if (isset($_POST["open-csa-wp-showNewOrderForm_user_input"])) {
					$user_id = $_POST["open-csa-wp-showNewOrderForm_user_input"];
				}
				
				if (isset($_POST["open-csa-wp-showSelectSpotForm_spot_input"])) {
					$spot_id = $_POST["open-csa-wp-showSelectSpotForm_spot_input"];
				}
					
				if (isset($_POST["open-csa-wp-showSelectSpotForm_delivery_input"])) {
					$delivery_id = $_POST["open-csa-wp-showSelectSpotForm_delivery_input"];
				}
				
				if ($user_id!=null && ($personal_order === false || $spot_id != null)) {
					open_csa_wp_show_order_form($user_id, $spot_id, $delivery_id, true, $redirection_url, $personal_order);
				} else { 
					$show_new_order_form = open_csa_wp_active_deliveries_exist() === true ? true:false;
					$show_orders = $wpdb->get_var("SELECT COUNT(*) FROM " .OPEN_CSA_WP_TABLE_USER_ORDERS) > 0 ? true:false;
					
					if ($show_orders === false && $show_new_order_form === true) {
						open_csa_wp_show_order_form($user_id, $spot_id, $delivery_id, true, $redirection_url, $personal_order);
					} else if ($show_orders === true) {
						open_csa_wp_show_order_form($user_id, $spot_id, $delivery_id, false, $redirection_url, $personal_order);
						
						if($personal_order === false) {
							open_csa_wp_show_all_user_orders(null, false);
							open_csa_wp_show_delivery_orders_list ($user_id, false, true);
						} else {
							open_csa_wp_show_all_user_orders($user_id, true);
							
							$user_data = get_user_meta($user_id, 'open-csa-wp_user', true ); 
							
							if ($user_data != null) {
								if($user_data['role'] == "responsible" || $user_data['role'] == "administrator") { 
									echo "<h3>".__('Total Orders of Deliveries You Are Responsible',OPEN_CSA_WP_DOMAIN)."</h3>";
									open_csa_wp_show_delivery_orders_list ($user_id, false, true);	
								}
								if ($user_data['type'] == "producer" || $user_data['type'] == "both") {
									echo "<h3>".__('Your Total Orders per Delivery',OPEN_CSA_WP_DOMAIN)."</h3>";	
									open_csa_wp_show_delivery_orders_list ($user_id, true, true);
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

/*	**********
	USERS MENU
	**********	
*/

function open_csa_wp_users_menu() {
	if ( !current_user_can( 'administrator' ) &&
		(!($csa_data = get_user_meta( $user->ID, 'open-csa-wp_user', true )) || $csa_data['role'] != "administrator" )
	) {	
		wp_die( __( 'You do not have sufficient permissions to access this page.', OPEN_CSA_WP_DOMAIN ) );
	}
	?>
	<script>
	window.location.replace("<?php echo admin_url("/users.php"); ?>");
	</script>
<?php
}
?>