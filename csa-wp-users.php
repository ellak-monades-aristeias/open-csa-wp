<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


/*	*******************************
	SHOW/EDIT USER'S CSA PROPERTIES
	*******************************	
*/

add_action( 'show_user_profile', 'csa_wp_plugin_show_user_properties' );
add_action( 'edit_user_profile', 'csa_wp_plugin_show_user_properties' );
//add_action( 'register_form','CsaWpPluginShowDefaultUserProperties');
add_action('user_new_form', 'csa_wp_plugin_admin_show_default_user_properties');


function csa_wp_plugin_show_user_properties( $user ) {
	if ( current_user_can( 'administrator' ) || 
		(($csa_data = get_user_meta( $user->ID, 'csa-wp-plugin_user', true )) && $csa_data['role'] == "administrator" )
	) {
		csa_wp_plugin_edit_user_properties($user, false);
	} else {
		csa_wp_plugin_non_editable_user_properties($user);
	}
}

function csa_wp_plugin_non_editable_user_properties( $user ) {
	
	$csa_data = get_user_meta( $user->ID, 'csa-wp-plugin_user', true );
	
	if (!$csa_data) {
		$type = __("Your account's type in CSA has not yet been defined",CSA_WP_PLUGIN_DOMAIN);
		$role = __("Your account's role in CSA has not yet been defined",CSA_WP_PLUGIN_DOMAIN);
	} else {
		$type = "Consumer";
		if ($csa_data['type'] == "producer") {
			$type = __("Producer", CSA_WP_PLUGIN_DOMAIN);
		} else if ($csa_data['type'] == "both") {
			$type = __("Producer and Consumer",CSA_WP_PLUGIN_DOMAIN);
		}
		
		$role = __("Simple User",CSA_WP_PLUGIN_DOMAIN);
		if ($csa_data['role'] == "responsible") {
			$role = __("You can become responsible for some delivery",CSA_WP_PLUGIN_DOMAIN);
		} else if ($csa_data['role'] == "adminisrator") {
			$role = "Administrator";
		}
	}
	
	echo "
		<h3>".__('CSA Properties',CSA_WP_PLUGIN_DOMAIN)."</h3>

		<table class='form-table'>
		<tr>
			<th>".__('Type',CSA_WP_PLUGIN_DOMAIN)."</th>
			<td>".$type."</td>
		</tr>
		<tr>
			<th>".__('Role',CSA_WP_PLUGIN_DOMAIN)."</th>
			<td>".$role."</td>
		</tr>
		</table>
	";
}

function csa_wp_plugin_admin_show_default_user_properties ( $user) {
	csa_wp_plugin_edit_user_properties($user, true);
}

function csa_wp_plugin_edit_user_properties ($user, $new_user) {

	wp_enqueue_script('csa-wp-plugin-general-scripts');
	wp_enqueue_script('csa-wp-plugin-users-scripts');
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

	$csa_data = array();

	if (!$new_user)	{
		$csa_data = get_user_meta( $user->ID, 'csa-wp-plugin_user', true );
	}
    ?>
	<h3><?php _e('CSA Properties',CSA_WP_PLUGIN_DOMAIN); ?></h3>

	<table class="form-table">
		<tr>
			<th><?php _e('Type',CSA_WP_PLUGIN_DOMAIN); ?></th>
			<td> 
				<input 
					id="csa-wp-plugin-consumer_radio"
					type="radio" 
					name="csa-wp-plugin_user_type" 
					value="consumer"  
					onclick="csa_wp_plugin_producer_orders_info_via(document.getElementById('csa-wp-plugin-consumer_radio'),document.getElementById('csa-wp-plugin-producer_contact_preference'))"
					<?php 
						if(!$new_user && isset($csa_data['type'])) {
							checked($csa_data['type'], "consumer"); 
						} else if ($new_user || !isset($csa_data['type'])) {
							echo 'checked = "checked"';
						}
					?>
				> <label for="csa-wp-plugin-consumer_radio"><?php _e('Consumer',CSA_WP_PLUGIN_DOMAIN);?></label>
				<br>
				<input 
					id="csa-wp-plugin-producer_radio"
					type="radio" 
					name="csa-wp-plugin_user_type" 
					value="producer"  
					onclick="csa_wp_plugin_producer_orders_info_via(document.getElementById('csa-wp-plugin-producer_radio'),document.getElementById('csa-wp-plugin-producer_contact_preference'))"
					<?php 
						if (!$new_user && isset($csa_data['type'])) {
							checked($csa_data['type'], "producer");
						}
					?>
				> <label for="csa-wp-plugin-producer_radio"><?php _e('Producer',CSA_WP_PLUGIN_DOMAIN);?></label>
				<br>
				<input 
					id="csa-wp-plugin-both_radio"
					type="radio" 
					name="csa-wp-plugin_user_type"
					value="both"  
					onclick="csa_wp_plugin_producer_orders_info_via(document.getElementById('csa-wp-plugin-both_radio'),document.getElementById('csa-wp-plugin-producer_contact_preference'))"
					<?php 
						if (!$new_user && isset($csa_data['type'])) {
							checked($csa_data['type'], "both");
						}
					?>
				> <label for="csa-wp-plugin-both_radio"><?php _e('Both',CSA_WP_PLUGIN_DOMAIN);?></label>
				<div id='csa-wp-plugin-producer_contact_preference' 
				<?php 
					if (!$new_user && 
						isset($csa_data['type']) &&
						($csa_data['type'] == 'producer' || $csa_data['type'] =='both')) {
						echo "style='display:block'";
					} else {
						echo "style='display:none'";
					}
				?>>
					<?php _e('and producer prefers to be informed about orders via',CSA_WP_PLUGIN_DOMAIN); ?>
					<select name="csa-wp-plugin_user_info_via">
						<option 
							value=""
							disabled='disabled'
							<?php 
								if($new_user || !isset($csa_data['info-via'])) {
									echo "selected='selected'";
								}
							?>
						><?php _e('please select ...',CSA_WP_PLUGIN_DOMAIN);?></option>
						<option 
							value='website'
							<?php 
								if(!$new_user && isset($csa_data['info-via'])) { 
									selected($csa_data['info-via'], "website");  
								}
							?>
						><?php _e('website',CSA_WP_PLUGIN_DOMAIN);?></option>
						<option 
							value='e-mail'
							<?php 
								if(!$new_user && isset($csa_data['info-via'])) {	
									selected($csa_data['info-via'], "e-mail");  
								}
							?>
						><?php _e('e-mail',CSA_WP_PLUGIN_DOMAIN);?></option>
						<option 
							value='sms'
							<?php 
								if(!$new_user && isset($csa_data['info-via'])) {
									selected($csa_data['info-via'], "sms");  
								}
							?>
						><?php _e('sms',CSA_WP_PLUGIN_DOMAIN);?></option>
						<option 
							value='phone'
							<?php 
								if(!$new_user && isset($csa_data['info-via'])) { 
									selected($csa_data['info-via'], "phone");  
								}
							?>
						><?php _e('phone call',CSA_WP_PLUGIN_DOMAIN);?></option>
					</select>
				</div>
			</td>
		</tr>
		<tr>
			<th><?php _e('Role',CSA_WP_PLUGIN_DOMAIN);?></th>	
			<td> 
				<input 
					id="csa-wp-plugin-none_radio"
					type="radio" 
					name="csa-wp-plugin_user_role" 
					value="none"  
					<?php 
						if (!$new_user && isset($csa_data['role'])) {
							checked($csa_data['role'], "none"); 
						} else if($new_user || !isset($csa_data['role'])) {
							echo 'checked = "checked"';
						}
					?>
				> <label for="csa-wp-plugin-none_radio"><?php _e('None',CSA_WP_PLUGIN_DOMAIN);?></label>
				<br>
				<input 
					id="csa-wp-plugin-responsible_radio"
					type="radio" 
					name="csa-wp-plugin_user_role" 
					value="responsible"
					<?php 
						if (!$new_user && isset($csa_data['role'])) {
							checked($csa_data['role'], "responsible");
						}
					?>
				> <span class="csa-wp-plugin-tip_users" title="Can become responsible for some delivery"><label for="csa-wp-plugin-responsible_radio"><?php _e('Responsible',CSA_WP_PLUGIN_DOMAIN);?></label></span>
				<br>
				<input
					id="csa-wp-plugin-administrator_radio"
					type="radio" 
					name="csa-wp-plugin_user_role" 
					value="administrator"  
					<?php 
						if (!$new_user && isset($csa_data['role'])) {
							checked($csa_data['role'], "administrator");
						}
					?>
				> <label for="csa-wp-plugin-administrator_radio"><?php _e('Administrator',CSA_WP_PLUGIN_DOMAIN);?></label>
			</td>
		</tr>
	</table>
<?php
}

/*	****************************
	UPDATE USER'S CSA PROPERTIES
	****************************	
*/
	
add_action( 'personal_options_update', 'csa_wp_plugin_save_user_properties' );
add_action( 'edit_user_profile_update', 'csa_wp_plugin_save_user_properties' );
add_action( 'user_register', 'csa_wp_plugin_save_user_properties');


function csa_wp_plugin_save_user_properties( $user_id ) {
	$csa_data = array();

	if(!empty( $_POST['csa-wp-plugin_user_type'] ))	{
		$csa_data['type'] = sanitize_text_field( $_POST['csa-wp-plugin_user_type'] );
	}

	if(!empty( $_POST['csa-wp-plugin_user_info_via'] ))	{
		$csa_data['info-via'] = sanitize_text_field( $_POST['csa-wp-plugin_user_info_via'] );
	}
		
	if(!empty( $_POST['csa-wp-plugin_user_role'] ))	{
		$csa_data['role'] = sanitize_text_field( $_POST['csa-wp-plugin_user_role'] );
	}
	
	if(!empty($csa_data)) {
		update_user_meta( $user_id, 'csa-wp-plugin_user', $csa_data);
	}
}


add_filter( 'manage_users_columns', 'csa_wp_plugin_display_user_properties_columns' );
add_filter( 'manage_users_custom_column', 'csa_wp_plugin_populate_user_properties_columns', 10, 3 );

function csa_wp_plugin_display_user_properties_columns ( $columns )  {
    $columns['csa-wp-plugin-user_type'] = 'CSA Type';
	$columns['csa-wp-plugin-user_role'] = 'CSA Role';

    return $columns;
}

function csa_wp_plugin_populate_user_properties_columns ( $value, $column_name, $user_id ) {
    if ( $column_name != "csa-wp-plugin-user_type" && $column_name != "csa-wp-plugin-user_role" ) {
        return value;
	}

	$csa_data = get_user_meta( $user_id, 'csa-wp-plugin_user', true );
	
    if ( $column_name == "csa-wp-plugin-user_type" && $csa_data && $csa_data['type']) {
        return $csa_data['type'];
	}

    if ( $column_name == "csa-wp-plugin-user_role" && $csa_data && $csa_data['role']) {
        return $csa_data['role'];
	}
		
	return "";
}


/*	****************************
	DELETE USER'S CSA PROPERTIES
	****************************	
*/


add_action( 'delete_user', 'csa_wp_plugin_delete_user' );

function csa_wp_plugin_delete_user ($user_id) {
	delete_user_meta ($user_id, 'csa-wp-plugin_user');
}

function csa_wp_plugin_delete_users_meta() {
	$csa_users = get_users(array('fields' => 'ID'));
	
    foreach($csa_users as $csaUser) csa_wp_plugin_delete_user($csaUser);
}

function csa_wp_plugin_select_users($selected_user_id, $message) {
	$users = get_users();
	csa_wp_plugin_build_users_selection($users, $selected_user_id, $message);
}

function csa_wp_plugin_select_users_of_type($user_type, $selected_user_id, $message) {
	$csa_producers = csa_wp_plugin_get_user_excluding($user_type=="consumer"?"producer":"consumer") ;
	csa_wp_plugin_build_users_selection($csa_producers, $selected_user_id, $message);
}

function csa_wp_plugin_get_user_excluding($user_type) {
	$csa_users = get_users();
	
	foreach ($csa_users as $i => $user) {
		$csa_user_data = get_user_meta( $user->ID, 'csa-wp-plugin_user', true );
		if ($csa_user_data == "" || $csa_user_data['type'] == $user_type) {
			unset($csa_users[$i]);
		}
	}
	
	return $csa_users;
}

function csa_wp_plugin_build_users_selection($users_to_select, $selected_user_id, $message) {
						
	foreach ($users_to_select as $user) {
		$text = csa_wp_plugin_user_readable($user);
		
		if ($user->ID == $selected_user_id) {
			echo "<option value='".$user->ID."' selected='selected' style='color:black'>". $message.$text."</option>";
		} else {
			echo "<option value='".$user->ID."' style='color:black'>".$text."</option>";
		}

	}
}

function csa_wp_plugin_user_readable ($user) {
	$text = "";
	$first_name = get_user_meta( $user->ID, 'first_name', true );
	$last_name = get_user_meta( $user->ID, 'last_name', true );
	
	if ($first_name != null) {
		$text = $first_name; 
		if ($last_name != null) {
			$text = $text." ".$last_name;
		}
		$text.=" ";
	} else if ($last_name != null) {
		$text .= $last_name . " ";
	}
		
	$text .= "(".$user->user_login.")";
	
	return $text;
}

function csa_wp_plugin_user_readable_without_login ($user) {
	$text = "";
	$first_name = get_user_meta( $user->ID, 'first_name', true );
	$last_name = get_user_meta( $user->ID, 'last_name', true );
	
	if ($first_name != null) {
		$text = $first_name; 
		if ($last_name != null) {
			$text = $text." ".$last_name;
		}
		$text.=" ";
	} else if ($last_name != null) {
		$text .= $last_name . " ";
	}

	if ($text == "") {
		$text = $user->user_login;
	}
	
	return $text;
}

function csa_wp_plugin_producers_map_array () {
	$producers_map = array ();
	
	$csa_users = get_users();
	foreach ($csa_users as $user) {
		$csa_user_data = get_user_meta( $user->ID, 'csa-wp-plugin_user', true );
		if ($csa_user_data != "" && ($csa_user_data['type'] == "producer" || $csa_user_data['type'] == "both")) {
			$producers_map[$user->ID] = csa_wp_plugin_user_readable_without_login($user);
		}
	}
	
	return $producers_map;
}

function csa_wp_plugin_is_user_csa_administrator($user_id) {
	$user_data = get_user_meta($user_id, 'csa-wp-plugin_user', true ); 
	
	if ($user_data == null || $user_data['role'] != "administrator") {
		return false;
	} else {
		return true;
	}
}

function csa_wp_plugin_is_user_responsible_for_delivery($user_id, $delivery_id) {

	$user_data = get_user_meta($user_id, 'csa-wp-plugin_user', true ); 
	
	if ($user_data == null || $user_data['role'] == "none") {
		return false;
	} else {
		global $wpdb;
		$user_in_charge = $wpdb->get_var($wpdb->prepare("SELECT user_in_charge FROM ".CSA_WP_PLUGIN_TABLE_DELIVERIES." WHERE id=%d", $delivery_id));
		
		if ($user_in_charge != $user_id) {
			return false;
		} else {
			return true;
		}	
	}
}

function csa_wp_plugin_user_can($page_name) {
	if (!is_user_logged_in()) {
		echo "<h6 style='color:brown'>". __('sorry... you need to log in first...',CSA_WP_PLUGIN_DOMAIN)."</h6><br/>";
		return false;
	}
	if (!current_user_can( 'administrator' ) &&
		(!($csa_data = get_user_meta(get_current_user_id(), 'csa-wp-plugin_user', true )) || $csa_data['role'] != "administrator" )
	) {	
		echo "<h6 style='color:brown'>"; 
		printf (
				__('sorry... you do not have sufficient privileges to access \"%s\" page. In case something goes wrong, please contant one of your team\'s administrators...',CSA_WP_PLUGIN_DOMAIN), 
				$page_name
			);		
		echo "</h6><br/>";
		return false;
	}
	else return true;
}
?>