<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


/*	*******************************
	SHOW/EDIT USER'S CSA PROPERTIES
	*******************************	
*/

add_action( 'show_user_profile', 'open_csa_wp_show_user_properties' );
add_action( 'edit_user_profile', 'open_csa_wp_show_user_properties' );
//add_action( 'register_form','CsaWpPluginShowDefaultUserProperties');
add_action('user_new_form', 'open_csa_wp_admin_show_default_user_properties');


function open_csa_wp_show_user_properties( $user ) {
	if ( current_user_can( 'administrator' ) || 
		(($csa_data = get_user_meta( $user->ID, 'open-csa-wp_user', true )) && $csa_data['role'] == "administrator" )
	) {
		open_csa_wp_edit_user_properties($user, false);
	} else {
		open_csa_wp_non_editable_user_properties($user);
	}
}

function open_csa_wp_non_editable_user_properties( $user ) {
	
	$csa_data = get_user_meta( $user->ID, 'open-csa-wp_user', true );
	
	if (!$csa_data) {
		$type = __("Your account's type in CSA has not yet been defined",OPEN_CSA_WP_DOMAIN);
		$role = __("Your account's role in CSA has not yet been defined",OPEN_CSA_WP_DOMAIN);
	} else {
		$type = "Consumer";
		if ($csa_data['type'] == "producer") {
			$type = __("Producer", OPEN_CSA_WP_DOMAIN);
		} else if ($csa_data['type'] == "both") {
			$type = __("Producer and Consumer",OPEN_CSA_WP_DOMAIN);
		}
		
		$role = __("Simple User",OPEN_CSA_WP_DOMAIN);
		if ($csa_data['role'] == "responsible") {
			$role = __("You can become responsible for some delivery",OPEN_CSA_WP_DOMAIN);
		} else if ($csa_data['role'] == "adminisrator") {
			$role = "Administrator";
		}
	}
	
	echo "
		<h3>".__('CSA Properties',OPEN_CSA_WP_DOMAIN)."</h3>

		<table class='form-table'>
		<tr>
			<th>".__('Type',OPEN_CSA_WP_DOMAIN)."</th>
			<td>".$type."</td>
		</tr>
		<tr>
			<th>".__('Role',OPEN_CSA_WP_DOMAIN)."</th>
			<td>".$role."</td>
		</tr>
		</table>
	";
}

function open_csa_wp_admin_show_default_user_properties ( $user) {
	open_csa_wp_edit_user_properties($user, true);
}

function open_csa_wp_edit_user_properties ($user, $new_user) {

	wp_enqueue_script('open-csa-wp-general-scripts');
	wp_enqueue_script('open-csa-wp-users-scripts');
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

	$csa_data = array();

	if (!$new_user)	{
		$csa_data = get_user_meta( $user->ID, 'open-csa-wp_user', true );
	}
    ?>
	<h3><?php _e('CSA Properties',OPEN_CSA_WP_DOMAIN); ?></h3>

	<table class="form-table">
		<tr>
			<th><?php _e('Type',OPEN_CSA_WP_DOMAIN); ?></th>
			<td> 
				<input 
					id="open-csa-wp-consumer_radio"
					type="radio" 
					name="open-csa-wp_user_type" 
					value="consumer"  
					onclick="open_csa_wp_producer_orders_info_via(document.getElementById('open-csa-wp-consumer_radio'),document.getElementById('open-csa-wp-producer_contact_preference'))"
					<?php 
						if(!$new_user && isset($csa_data['type'])) {
							checked($csa_data['type'], "consumer"); 
						} else if ($new_user || !isset($csa_data['type'])) {
							echo 'checked = "checked"';
						}
					?>
				> <label for="open-csa-wp-consumer_radio"><?php _e('Consumer',OPEN_CSA_WP_DOMAIN);?></label>
				<br>
				<input 
					id="open-csa-wp-producer_radio"
					type="radio" 
					name="open-csa-wp_user_type" 
					value="producer"  
					onclick="open_csa_wp_producer_orders_info_via(document.getElementById('open-csa-wp-producer_radio'),document.getElementById('open-csa-wp-producer_contact_preference'))"
					<?php 
						if (!$new_user && isset($csa_data['type'])) {
							checked($csa_data['type'], "producer");
						}
					?>
				> <label for="open-csa-wp-producer_radio"><?php _e('Producer',OPEN_CSA_WP_DOMAIN);?></label>
				<br>
				<input 
					id="open-csa-wp-both_radio"
					type="radio" 
					name="open-csa-wp_user_type"
					value="both"  
					onclick="open_csa_wp_producer_orders_info_via(document.getElementById('open-csa-wp-both_radio'),document.getElementById('open-csa-wp-producer_contact_preference'))"
					<?php 
						if (!$new_user && isset($csa_data['type'])) {
							checked($csa_data['type'], "both");
						}
					?>
				> <label for="open-csa-wp-both_radio"><?php _e('Both',OPEN_CSA_WP_DOMAIN);?></label>
				<div id='open-csa-wp-producer_contact_preference' 
				<?php 
					if (!$new_user && 
						isset($csa_data['type']) &&
						($csa_data['type'] == 'producer' || $csa_data['type'] =='both')) {
						echo "style='display:block'";
					} else {
						echo "style='display:none'";
					}
				?>>
					<?php _e('and producer prefers to be informed about orders via',OPEN_CSA_WP_DOMAIN); ?>
					<select name="open-csa-wp_user_info_via">
						<option 
							value=""
							disabled='disabled'
							<?php 
								if($new_user || !isset($csa_data['info-via'])) {
									echo "selected='selected'";
								}
							?>
						><?php _e('please select ...',OPEN_CSA_WP_DOMAIN);?></option>
						<option 
							value='website'
							<?php 
								if(!$new_user && isset($csa_data['info-via'])) { 
									selected($csa_data['info-via'], "website");  
								}
							?>
						><?php _e('website',OPEN_CSA_WP_DOMAIN);?></option>
						<option 
							value='e-mail'
							<?php 
								if(!$new_user && isset($csa_data['info-via'])) {	
									selected($csa_data['info-via'], "e-mail");  
								}
							?>
						><?php _e('e-mail',OPEN_CSA_WP_DOMAIN);?></option>
						<option 
							value='sms'
							<?php 
								if(!$new_user && isset($csa_data['info-via'])) {
									selected($csa_data['info-via'], "sms");  
								}
							?>
						><?php _e('sms',OPEN_CSA_WP_DOMAIN);?></option>
						<option 
							value='phone'
							<?php 
								if(!$new_user && isset($csa_data['info-via'])) { 
									selected($csa_data['info-via'], "phone");  
								}
							?>
						><?php _e('phone call',OPEN_CSA_WP_DOMAIN);?></option>
					</select>
				</div>
			</td>
		</tr>
		<tr>
			<th><?php _e('Role',OPEN_CSA_WP_DOMAIN);?></th>	
			<td> 
				<input 
					id="open-csa-wp-none_radio"
					type="radio" 
					name="open-csa-wp_user_role" 
					value="none"  
					<?php 
						if (!$new_user && isset($csa_data['role'])) {
							checked($csa_data['role'], "none"); 
						} else if($new_user || !isset($csa_data['role'])) {
							echo 'checked = "checked"';
						}
					?>
				> <label for="open-csa-wp-none_radio"><?php _e('None',OPEN_CSA_WP_DOMAIN);?></label>
				<br>
				<input 
					id="open-csa-wp-responsible_radio"
					type="radio" 
					name="open-csa-wp_user_role" 
					value="responsible"
					<?php 
						if (!$new_user && isset($csa_data['role'])) {
							checked($csa_data['role'], "responsible");
						}
					?>
				> <span class="open-csa-wp-tip_users" title="Can become responsible for some delivery"><label for="open-csa-wp-responsible_radio"><?php _e('Responsible',OPEN_CSA_WP_DOMAIN);?></label></span>
				<br>
				<input
					id="open-csa-wp-administrator_radio"
					type="radio" 
					name="open-csa-wp_user_role" 
					value="administrator"  
					<?php 
						if (!$new_user && isset($csa_data['role'])) {
							checked($csa_data['role'], "administrator");
						}
					?>
				> <label for="open-csa-wp-administrator_radio"><?php _e('Administrator',OPEN_CSA_WP_DOMAIN);?></label>
			</td>
		</tr>
	</table>
<?php
}

/*	****************************
	UPDATE USER'S CSA PROPERTIES
	****************************	
*/
	
add_action( 'personal_options_update', 'open_csa_wp_save_user_properties' );
add_action( 'edit_user_profile_update', 'open_csa_wp_save_user_properties' );
add_action( 'user_register', 'open_csa_wp_save_user_properties');


function open_csa_wp_save_user_properties( $user_id ) {
	$csa_data = array();

	if(!empty( $_POST['open-csa-wp_user_type'] ))	{
		$csa_data['type'] = sanitize_text_field( $_POST['open-csa-wp_user_type'] );
	}

	if(!empty( $_POST['open-csa-wp_user_info_via'] ))	{
		$csa_data['info-via'] = sanitize_text_field( $_POST['open-csa-wp_user_info_via'] );
	}
		
	if(!empty( $_POST['open-csa-wp_user_role'] ))	{
		$csa_data['role'] = sanitize_text_field( $_POST['open-csa-wp_user_role'] );
	}
	
	if(!empty($csa_data)) {
		update_user_meta( $user_id, 'open-csa-wp_user', $csa_data);
	}
}


add_filter( 'manage_users_columns', 'open_csa_wp_display_user_properties_columns' );
add_filter( 'manage_users_custom_column', 'open_csa_wp_populate_user_properties_columns', 10, 3 );

function open_csa_wp_display_user_properties_columns ( $columns )  {
    $columns['open-csa-wp-user_type'] = 'CSA Type';
	$columns['open-csa-wp-user_role'] = 'CSA Role';

    return $columns;
}

function open_csa_wp_populate_user_properties_columns ( $value, $column_name, $user_id ) {
    if ( $column_name != "open-csa-wp-user_type" && $column_name != "open-csa-wp-user_role" ) {
        return value;
	}

	$csa_data = get_user_meta( $user_id, 'open-csa-wp_user', true );
	
    if ( $column_name == "open-csa-wp-user_type" && $csa_data && $csa_data['type']) {
        return $csa_data['type'];
	}

    if ( $column_name == "open-csa-wp-user_role" && $csa_data && $csa_data['role']) {
        return $csa_data['role'];
	}
		
	return "";
}


/*	****************************
	DELETE USER'S CSA PROPERTIES
	****************************	
*/


add_action( 'delete_user', 'open_csa_wp_delete_user' );

function open_csa_wp_delete_user ($user_id) {
	delete_user_meta ($user_id, 'open-csa-wp_user');
}

function open_csa_wp_delete_users_meta() {
	$csa_users = get_users(array('fields' => 'ID'));
	
    foreach($csa_users as $csaUser) open_csa_wp_delete_user($csaUser);
}

function open_csa_wp_select_users($selected_user_id, $message) {
	$users = get_users();
	open_csa_wp_build_users_selection($users, $selected_user_id, $message);
}

function open_csa_wp_select_users_of_type($user_type, $selected_user_id, $message) {
	$csa_producers = open_csa_wp_get_user_excluding($user_type=="consumer"?"producer":"consumer") ;
	open_csa_wp_build_users_selection($csa_producers, $selected_user_id, $message);
}

function open_csa_wp_get_user_excluding($user_type) {
	$csa_users = get_users();
	
	foreach ($csa_users as $i => $user) {
		$csa_user_data = get_user_meta( $user->ID, 'open-csa-wp_user', true );
		if ($csa_user_data == "" || $csa_user_data['type'] == $user_type) {
			unset($csa_users[$i]);
		}
	}
	
	return $csa_users;
}

function open_csa_wp_build_users_selection($users_to_select, $selected_user_id, $message) {
						
	foreach ($users_to_select as $user) {
		$text = open_csa_wp_user_readable($user);
		
		if ($user->ID == $selected_user_id) {
			echo "<option value='".$user->ID."' selected='selected' style='color:black'>". $message.$text."</option>";
		} else {
			echo "<option value='".$user->ID."' style='color:black'>".$text."</option>";
		}

	}
}

function open_csa_wp_user_readable ($user) {
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

function open_csa_wp_user_readable_without_login ($user) {
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

function open_csa_wp_producers_map_array () {
	$producers_map = array ();
	
	$csa_users = get_users();
	foreach ($csa_users as $user) {
		$csa_user_data = get_user_meta( $user->ID, 'open-csa-wp_user', true );
		if ($csa_user_data != "" && ($csa_user_data['type'] == "producer" || $csa_user_data['type'] == "both")) {
			$producers_map[$user->ID] = open_csa_wp_user_readable_without_login($user);
		}
	}
	
	return $producers_map;
}

function open_csa_wp_is_user_csa_administrator($user_id) {
	$user_data = get_user_meta($user_id, 'open-csa-wp_user', true ); 
	
	if ($user_data == null || $user_data['role'] != "administrator") {
		return false;
	} else {
		return true;
	}
}

function open_csa_wp_is_user_responsible_for_delivery($user_id, $delivery_id) {

	$user_data = get_user_meta($user_id, 'open-csa-wp_user', true ); 
	
	if ($user_data == null || $user_data['role'] == "none") {
		return false;
	} else {
		global $wpdb;
		$user_in_charge = $wpdb->get_var($wpdb->prepare("SELECT user_in_charge FROM ".OPEN_CSA_WP_TABLE_DELIVERIES." WHERE id=%d", $delivery_id));
		
		if ($user_in_charge != $user_id) {
			return false;
		} else {
			return true;
		}	
	}
}

function open_csa_wp_user_can($page_name) {
	if (!is_user_logged_in()) {
		echo "<h6 style='color:brown'>". __('sorry... you need to log in first...',OPEN_CSA_WP_DOMAIN)."</h6><br/>";
		return false;
	}
	if (!current_user_can( 'administrator' ) &&
		(!($csa_data = get_user_meta(get_current_user_id(), 'open-csa-wp_user', true )) || $csa_data['role'] != "administrator" )
	) {	
		echo "<h6 style='color:brown'>"; 
		printf (
				__('sorry... you do not have sufficient privileges to access \"%s\" page. In case something goes wrong, please contant one of your team\'s administrators...',OPEN_CSA_WP_DOMAIN), 
				$page_name
			);		
		echo "</h6><br/>";
		return false;
	}
	else return true;
}
?>