<?php

add_action( 'show_user_profile', 'CsaWpPluginShowUserProperties' );
add_action( 'edit_user_profile', 'CsaWpPluginShowUserProperties' );
//add_action( 'register_form','CsaWpPluginShowDefaultUserProperties');
add_action('user_new_form', 'CsaWpPluginAdminShowDefaultUserProperties');


function CsaWpPluginShowUserProperties( $user ) {
	if ( current_user_can( 'administrator' ) || 
		(($csaData = get_user_meta( $user->ID, 'csa-wp-plugin_user', true )) && $csaData['role'] == "administrator" )
	) CsaWpPluginEditUserProperties($user, false);
	else CsaWpPluginNonEditableUserProperties($user);
}

function CsaWpPluginNonEditableUserProperties( $user ) {
	
	$csaData = get_user_meta( $user->ID, 'csa-wp-plugin_user', true );
	
	if (!$csaData) {
		$type = "Your account's type in CSA has not yet been defined";
		$role = "Your account's role in CSA has not yet been defined";
	} else {
		$type = "Consumer";
		if ($csaData['type'] == "producer") $type = "Producer";
		else if ($csaData['type'] == "both") $type = "Producer and Consumer";
		$role = "Simple User";
		if ($csaData['role'] == "responsible") $role = "You can become responsible for some delivery";
		else if ($csaData['role'] == "adminisrator") $role = "Administrator";
	}
	
	echo "
		<h3>CSA Properties</h3>

		<table class='form-table'>
		<tr>
			<th>Type</th>
			<td>".$type."</td>
		</tr>
		<tr>
			<th>Role</th>
			<td>".$role."</td>
		</tr>
		</table>
	";
}

function CsaWpPluginAdminShowDefaultUserProperties ( $user) {
	CsaWpPluginEditUserProperties($user, true);
}

function CsaWpPluginEditUserProperties ($user, $new_user) {

	wp_enqueue_script('CsaWpPluginScripts');
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');


?>	
	<script type="text/javascript">
	// clueTip code for showing product details in tooltip 
	var $j = jQuery.noConflict();
	$j(document).ready(function() {
	  $j('.tip').cluetip({
		splitTitle: '|',							 
		showTitle: false,
		hoverClass: 'highlight',
		open: 'slideDown', 
		openSpeed: 'fast'
	  });
	});
	</script>
<?php

	$csaData = array();

	if (!$new_user)	
		$csaData = get_user_meta( $user->ID, 'csa-wp-plugin_user', true );
    ?>
	<h3>CSA Properties</h3>

	<table class="form-table">
		<tr>
			<th>Type</th>
			<td> 
				<input 
					id="csa-wp-plugin-consumer_radio"
					type="radio" 
					name="csa-wp-plugin_user_type" 
					value="consumer"  
					onclick="CsaWpPluginProducerOrderInfoVia(document.getElementById('csa-wp-plugin-consumer_radio'),document.getElementById('csa-wp-plugin-producer_contact_preference'))"
					<?php 
						if(!$new_user && isset($csaData['type'])) checked($csaData['type'], "consumer"); 
						else if($new_user || !isset($csaData['type'])) echo 'checked = "checked"';?>
				> <label for="csa-wp-plugin-consumer_radio">Consumer</label>
				<br>
				<input 
					id="csa-wp-plugin-producer_radio"
					type="radio" 
					name="csa-wp-plugin_user_type" 
					value="producer"  
					onclick="CsaWpPluginProducerOrderInfoVia(document.getElementById('csa-wp-plugin-producer_radio'),document.getElementById('csa-wp-plugin-producer_contact_preference'))"
					<?php if (!$new_user && isset($csaData['type'])) checked($csaData['type'], "producer");?>
				> <label for="csa-wp-plugin-producer_radio">Producer</label>
				<br>
				<input 
					id="csa-wp-plugin-both_radio"
					type="radio" 
					name="csa-wp-plugin_user_type"
					value="both"  
					onclick="CsaWpPluginProducerOrderInfoVia(document.getElementById('csa-wp-plugin-both_radio'),document.getElementById('csa-wp-plugin-producer_contact_preference'))"
					<?php if (!$new_user && isset($csaData['type'])) checked($csaData['type'], "both");?>
				> <label for="csa-wp-plugin-both_radio">Both</label>
				<div id='csa-wp-plugin-producer_contact_preference' 
				<?php 
					if (!$new_user && 
						isset($csaData['type']) &&
						($csaData['type'] == 'producer' || $csaData['type'] =='both'))
						echo "style='display:block'";
					else echo "style='display:none'";
				?>>
					and producer prefers to be informed about orders via
					<select name="csa-wp-plugin_user_info_via">
						<option 
							value=""
							disabled='disabled'
							<?php if($new_user || !isset($csaData['info-via'])) echo "selected='selected'";?>
						>please select...</option>
						<option 
							value='website'
							<?php if(!$new_user && isset($csaData['info-via'])) selected($csaData['info-via'], "website");  ?>
						>website</option>
						<option 
							value='e-mail'
							<?php if(!$new_user && isset($csaData['info-via'])) selected($csaData['info-via'], "e-mail");  ?>
						>e-mail</option>
						<option 
							value='sms'
							<?php if(!$new_user && isset($csaData['info-via'])) selected($csaData['info-via'], "sms");  ?>
						>sms</option>
						<option 
							value='phone'
							<?php if(!$new_user && isset($csaData['info-via'])) selected($csaData['info-via'], "phone");  ?>
						>phone call</option>
					</select>
				</div>
			</td>
		</tr>
		<tr>
			<th>Role</th>	
			<td> 
				<input 
					id="csa-wp-plugin-none_radio"
					type="radio" 
					name="csa-wp-plugin_user_role" 
					value="none"  
					<?php 
						if(!$new_user && isset($csaData['role'])) checked($csaData['role'], "none"); 
						else if($new_user || !isset($csaData['role'])) echo 'checked = "checked"';?>
				> <label for="csa-wp-plugin-none_radio">None</label>
				<br>
				<input 
					id="csa-wp-plugin-responsible_radio"
					type="radio" 
					name="csa-wp-plugin_user_role" 
					value="responsible"
					class="tip"
					title="Can become responsible of some delivery"
					<?php if (!$new_user && isset($csaData['role'])) checked($csaData['role'], "responsible");?>
				> <span class="tip" title="Can become responsible for some delivery"><label for="csa-wp-plugin-responsible_radio">Responsible</label></span>
				<br>
				<input
					id="csa-wp-plugin-administrator_radio"
					type="radio" 
					name="csa-wp-plugin_user_role" 
					value="administrator"  
					<?php if (!$new_user && isset($csaData['role'])) checked($csaData['role'], "administrator");?>
				> <label for="csa-wp-plugin-administrator_radio">Administrator</label>
			</td>
		</tr>
	</table>
<?php
}
	
add_action( 'personal_options_update', 'CsaWpPluginSaveUserProperties' );
add_action( 'edit_user_profile_update', 'CsaWpPluginSaveUserProperties' );
add_action( 'user_register', 'CsaWpPluginSaveUserProperties');


function CsaWpPluginSaveUserProperties( $user_id ) {
	$csaData = array();

	if(!empty( $_POST['csa-wp-plugin_user_type'] ))	
		$csaData['type'] = sanitize_text_field( $_POST['csa-wp-plugin_user_type'] );

	if(!empty( $_POST['csa-wp-plugin_user_info_via'] ))	
		$csaData['info-via'] = sanitize_text_field( $_POST['csa-wp-plugin_user_info_via'] );
		
	if(!empty( $_POST['csa-wp-plugin_user_role'] ))	
		$csaData['role'] = sanitize_text_field( $_POST['csa-wp-plugin_user_role'] );
	
	if(!empty($csaData)) 
		update_user_meta( $user_id, 'csa-wp-plugin_user', $csaData);
}


add_filter( 'manage_users_columns', 'CsaWpPluginDisplayUserPropertiesColumns' );
add_filter( 'manage_users_custom_column', 'CsaWpPluginPopulateUserPropertiesColumns', 10, 3 );

function CsaWpPluginDisplayUserPropertiesColumns ( $columns )  {
    $columns['csa-wp-plugin-user_type'] = 'CSA Type';
	$columns['csa-wp-plugin-user_role'] = 'CSA Role';

    return $columns;
}

function CsaWpPluginPopulateUserPropertiesColumns ( $value, $column_name, $user_id ) {
    if ( $column_name != "csa-wp-plugin-user_type" && $column_name != "csa-wp-plugin-user_role" )
        return value;

	$csaData = get_user_meta( $user_id, 'csa-wp-plugin_user', true );
	
    if ( $column_name == "csa-wp-plugin-user_type" && $csaData && $csaData['type'])
        return $csaData['type'];

    if ( $column_name == "csa-wp-plugin-user_role" && $csaData && $csaData['role'])
        return $csaData['role'];
		
	return "";
}

add_action( 'delete_user', 'CsaWpPluginDeleteUser' );

function CsaWpPluginDeleteUser ($user_id) {
	delete_user_meta ($user_id, 'csa-wp-plugin_user');
}

function CsaWpPluginDeleteUsers() {
	$csaUsers = get_users(array('fields' => 'ID'));
	
    foreach($csaUsers as $csaUser) CsaWpPluginDeleteUser($csaUser);
}

// -----------------------------------
// ------------ NEW STUFF ------------
// -----------------------------------

function CsaWpPluginSelectUsers($selectedUserID, $message) {
	$users = get_users();
	CsaWpPluginBuildUsersSelection($users, $selectedUserID, $message);
}

function CsaWpPluginSelectUsersOfType($userType, $selectedUserID, $message) {
	$csaProducers = CsaWpPluginGetUserExcluding($userType=="consumer"?"producer":"consumer") ;
	CsaWpPluginBuildUsersSelection($csaProducers, $selectedUserID, $message);
}

function CsaWpPluginGetUserExcluding($userType) {
	$csaUsers = get_users();
	
	foreach ($csaUsers as $i => $user) {
		$csaUserData = get_user_meta( $user->ID, 'csa-wp-plugin_user', true );
		if ($csaUserData == "" || $csaUserData['type'] == $userType)
			unset($csaUsers[$i]);
	}
	
	return $csaUsers;
}

function CsaWpPluginBuildUsersSelection($usersToSelect, $selectedUserID, $message) {
						
	foreach ($usersToSelect as $user) {
		$text = "";
		$firstName = get_user_meta( $user->ID, 'first_name', true );
		$lastName = get_user_meta( $user->ID, 'last_name', true );
		
		if ($firstName != null) {
			$text = $firstName; 
			if ($lastName != null) $text = $text." ".$lastName;
			$text.=" ";
		}
		else if ($lastName != null)
			$text .= $lastName . " ";
			
		$text .= "(".$user->user_login.")";
		
		if ($user->ID == $selectedUserID) 								
			echo "<option value='".$user->ID."' selected='selected' style='color:black'>". $message.$text."</option>";
		else
			echo "<option value='".$user->ID."' style='color:black'>".$text."</option>";

	}
}

?>