<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function csa_wp_plugin_spot_form ($spot_id, $display){
	wp_enqueue_script( 'csa-wp-plugin-enqueue-csa-scripts' );
	wp_enqueue_script('csa-wp-plugin-spots-scripts');
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

	global $days_of_week,$wpdb;
	$spot_info;
	if ($spot_id != null) {
		$spot_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".CSA_WP_PLUGIN_TABLE_SPOTS." WHERE id=%d", $spot_id));
	}
?>
	
	<br />
	<div id="csa-wp-plugin-showNewSpot_formHeader">
		<span 	
			id="csa-wp-plugin-showNewSpot_formHeader_text" 
			<?php 
				if ($spot_id == null) {
					echo 'style="cursor:pointer"';
					echo 'onclick="csa_wp_plugin_toggle_form(\'showNewSpot\',\'Add New Spot\', \' form\')"';
				}
			?>>
			<font size='4'>
			<?php 
			if ($spot_id == null) {
				if ($display == false) {
					echo 'Add New Spot (show form)';
				} else { 
					echo 'Add New Spot (hide form)';
				}
			} else {
				echo 'Edit Spot #'.$spot_id;
			}
			?>
		</font></span></div>
	<div id="csa-wp-plugin-showNewSpot_div" 
		<?php 
			if ($display == false) {
				echo "style='display:none'";
			}
		?>>
		<form method="POST" id="csa-wp-plugin-showNewSpot_form">
			<table class="form-table">
				<tr valign="top"><td>
					<input type="text" 
						<?php 
							if ($spot_id != null && $spot_info[0]->spot_name != "" && $spot_info[0]->spot_name != null) {
								echo "value='".$spot_info[0]->spot_name."'"; 
							}
						?>
						placeholder="Spot Name *"
						required 
						name="csa-wp-plugin-spots_spot_name_input" 
						onchange="csa_wp_plugin_request_spot_name_validity(this.value, <?php echo ($spot_id!=null?$spot_id:"null");?> , 0, null)">
					<span id="csa-wp-plugin-showNewSpot_name_span_id" style="display:none"></span></td></tr>
				<tr valign="top"><td>
					<input 
						type="text" 
						<?php 
							if ($spot_id != null && $spot_info[0]->street_name != "" && $spot_info[0]->street_name != null) {
								echo "value='".$spot_info[0]->street_name."'"; 
							}							
						?>
						placeholder="Street Name *"
						required 
						name="csa-wp-plugin-spots_street_name_input">
					<input 
						type="text" 
						<?php 
							if ($spot_id != null && $spot_info[0]->street_number != "" && $spot_info[0]->street_number != null) {
								echo "value='".$spot_info[0]->street_number."'"; 
							}
						?>
						placeholder="Street Number *"
						required 
						name="csa-wp-plugin-spots_street_number_input"></td></tr>
				<tr valign="top"><td>
					<input 
						type="text" 
						<?php 
							if ($spot_id != null && $spot_info[0]->city != "" && $spot_info[0]->city != null) {
								echo "value='".$spot_info[0]->city."'"; 
							}
						?>
						placeholder="City *"
						required 
						name="csa-wp-plugin-spots_city_input">
					<input 
						type="text" 
						<?php 
							if ($spot_id != null && $spot_info[0]->region != "" && $spot_info[0]->region != null) {
								echo "value='".$spot_info[0]->region."'"; 
							}
						?>
						placeholder="Region *"
						required 
						name="csa-wp-plugin-spots_region_input"></td></tr>
				<tr valign="top"><td>
					<textarea name="csa-wp-plugin-spots_description_input" rows="3" cols="30" placeholder="Description";
						><?php 
							if ($spot_id != null) {
								echo $spot_info[0]->description; 
							}
						?></textarea></td></tr>
				
				<tr valign="top"><td>
					<select name="csa-wp-plugin-spots_is_delivery_spot_input" id="csa-wp-plugin-spots_is_delivery_spot_input_id"  onchange='csa_wp_plugin_show_new_spot_is_delivery_selection(this,<?php echo ($spot_id?$spot_id:"null")?>)'
					<?php 
						if ($spot_id == null) {
							echo "style='color:#999'";
						}
					?>>
						<?php 
						if ($spot_id != null) {
							echo '
								<option value="" style="color:#999" disabled="disabled">Is this a delivery spot? *</option>
								<option value="yes" style="color:black". '. ($spot_info[0]->is_delivery_spot == 1?"selected='selected'> it is a delivery spot":">yes") .' </option>
								<option value="no" style="color:black"'. ($spot_info[0]->is_delivery_spot == 0?"selected='selected'> it is not a delivery spot":">no") .' </option>
								';
						} else {
						?>
							<option value="" selected="selected" disabled="disabled">Is this a delivery spot? *</option>
							<option value="yes" style="color:black">yes</option>
							<option value="no" style="color:black">no</option>
						<?php
						}
						?>
					</select> 
					<span id="csa-wp-plugin-showNewSpotForm_deliverySpot_span"></span>
				
				</td></tr>				
				
			</table> 
			<div id = "csa-wp-plugin-spots_deliverySpot_div" 
				<?php 
					$is_delivery_spot = ($spot_id != null && $spot_info[0]->is_delivery_spot == 1)?1:0;
					if ($is_delivery_spot) {
						"style='display:block'";
					} else {
						echo "style='display:none'"; 
					}
				?>>
				<table class="form-table">		
				
				<tr valign="top"><td>
					<select 
						name="csa-wp-plugin-delivery_spot_owner_input" 
						id="csa-wp-plugin-delivery_spot_owner_input_id"
						<?php if (!$is_delivery_spot) echo "style='color:#999'";?>						
						onfocus='getElementById("csa-wp-plugin-delivery_spot_owner_span_id").style.display = "none";'
						onchange='
							if (this.options[this.selectedIndex].text.split(" ")[0]!= "The") 
								this.options[this.selectedIndex].text = "The owner of this delivery spot is: "+ this.options[this.selectedIndex].text;
							(this.style.color=this.options[this.selectedIndex].style.color);'
					>
						<option value="" 
							<?php if (!$is_delivery_spot) echo "selected='selected'";?> 
							disabled="disabled" id="csa-wp-plugin-delivery_spot_owner_disabled_id">Owner of this delivery spot... *
						</option>
						<?php 
						$spotOwner = null;
						if ($is_delivery_spot) {
							$spot_owner_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM ".CSA_WP_PLUGIN_TABLE_SPOTS_TO_USERS." WHERE spot_id=%d AND type='delivery' ", $spot_id), 0, 0);
						}							
						csa_wp_plugin_select_users($spot_owner_id, "The owner of this delivery spot is: ");
						
						?>
					</select> 
					<span style="display:none;color:blue" id="csa-wp-plugin-delivery_spot_owner_span_id"></span>
				</td></tr>
			
				<tr valign="top"><td>
					<select 
						name="csa-wp-plugin-spots_order_deadline_day_input"
						id='csa-wp-plugin-spots_order_deadline_day_input_id'
						onfocus='document.getElementById("csa-wp-plugin-showNewSpotForm_orderDeadline_span").style.display = "none";'
						onchange='
							this.style.color="black";
							if (this.options[this.selectedIndex].text.split(" ")[0] != "Order")
								this.options[this.selectedIndex].text = "Order deadline is on " + this.options[this.selectedIndex].text;
							getElementById("csa-wp-plugin-spots_order_deadline_time_input_id").style.display = "inline"
							'
						<?php	
							if (!$is_delivery_spot) {
								echo "style='color:#999'";
							}
						?>
					>
					<option value="" 
						<?php 
							if (!$is_delivery_spot) {
								echo "selected='selected'"; 
							}
						?>
						disabled="disabled" id="csa-wp-plugin-spots_order_deadline_day_disabled_id">Order deadline day ... *</option>
					<?php 
					for ($i=0; $i<7; $i++) {
						if ($is_delivery_spot && $spot_info[0]->default_order_deadline_day == $i) {
							echo "<option value='$i' selected='selected'> Order deadline is on $days_of_week[$i] </option>";
						} else {
							echo "<option value='$i'>".$days_of_week[$i]."</option>";
						}
					}
					?>
					</select> 
					<input 
						<?php 
							if ($is_delivery_spot && $spot_info[0]->default_order_deadline_time != "" && $spot_info[0]->default_order_deadline_time != null) {
								echo "value='up to ".csa_wp_plugin_remove_seconds($spot_info[0]->default_order_deadline_time)."'";
							}
							if (!$is_delivery_spot) {
								echo "style='display:none'";
							}
						?>
						placeholder="up to... *"
						id="csa-wp-plugin-spots_order_deadline_time_input_id"
						class="textbox-n" type="text" size="10" name="csa-wp-plugin-spots_order_deadline_time_input"
						onfocus='
							if (this.value != "") {
								this.value=this.value.split(" ")[2];
							} else {
								getElementById("csa-wp-plugin-showNewSpotForm_orderDeadline_span").style.display = "none";
							}
							this.type="time";'
						onblur='
							this.type="text";
							if (this.value != "") {
								this.style.color="black";
								this.value = "up to " + this.value;
							}'>
					<span id="csa-wp-plugin-showNewSpotForm_orderDeadline_span" style="display:none"></span>
				</td></tr>
								
				<tr valign="top"><td>
					<select 
						name="csa-wp-plugin-spots_delivery_day_input" 
						id="csa-wp-plugin-spots_delivery_day_input_id"
						onfocus='getElementById("csa-wp-plugin-showNewSpotForm_invalidDeliveryTime_span").innerHTML = "";'
						onchange='
							this.style.color="black";
							if (this.options[this.selectedIndex].text.split(" ")[0] != "Delivery") {
								this.options[this.selectedIndex].text = "Delivery day is " + this.options[this.selectedIndex].text;
							}
							
							getElementById("csa-wp-plugin-showNewSpotForm_delivery_start_time_input_id").style.display = "inline"
							getElementById("csa-wp-plugin-showNewSpotForm_delivery_end_time_input_id").style.display = "inline";
						'
						<?php 
							if (!$is_delivery_spot) {
								echo "style='color:#999'";
							}
						?>>
					<option value="" disabled="disabled" 
						<?php 
							if (!$is_delivery_spot) {
								echo "selected='selected'"; 
							}
						?>
						id="csa-wp-plugin-spots_delivery_day_disabled_id">Delivery day ... *</option>
					<?php 
					for ($i=0; $i<7; $i++) {
						if ($is_delivery_spot && $spot_info[0]->default_delivery_day == $i) {
							echo "<option value='$i' selected='selected'> Delivery day is $days_of_week[$i] </option>";
						} else { 
							echo "<option value='$i'>".$days_of_week[$i]."</option>";
						}
					}
					?>
					</select> 
					<input id="csa-wp-plugin-showNewSpotForm_delivery_start_time_input_id"
						<?php 
							if ($is_delivery_spot && $spot_info[0]->default_delivery_start_time != "" && $spot_info[0]->default_delivery_start_time != null) { 
								echo "value='from ".csa_wp_plugin_remove_seconds($spot_info[0]->default_delivery_start_time)."'";
							}
							if (!$is_delivery_spot) {
								echo "style='display:none'";
							}
						?>
						placeholder="from... *"
						class="textbox-n" type="text" size="10" name="csa-wp-plugin-spots_delivery_start_time_input"

						onfocus='
							if (this.value != "") {
								this.value=this.value.split(" ")[1];
							} else {
								getElementById("csa-wp-plugin-showNewSpotForm_invalidDeliveryTime_span").style.display = "none";
							}
							this.type="time";'
						onblur='
							this.type="text";
							if (this.value == "") {
								getElementById("csa-wp-plugin-showNewSpotForm_delivery_end_time_input_id").value = "";
							}
							else {
								this.style.color="black";
								this.value = "from " + this.value;
								csa_wp_plugin_validate_delivery_time_period("showNewSpotForm");
							}'>
					<input id="csa-wp-plugin-showNewSpotForm_delivery_end_time_input_id"
						<?php 
							if ($is_delivery_spot && $spot_info[0]->default_delivery_end_time != "" && $spot_info[0]->default_delivery_end_time != null) {
								echo "value='to ".csa_wp_plugin_remove_seconds($spot_info[0]->default_delivery_end_time)."'";
							}
							if (!$is_delivery_spot) {
								echo "style='display:none'";
							}
						?>
						placeholder="to... *"					
						class="textbox-n" type="text" size="10" name="csa-wp-plugin-spots_delivery_end_time_input"
						
						onfocus='
							if (this.value != "") { 
								this.value=this.value.split(" ")[1];
							}
							getElementById("csa-wp-plugin-showNewSpotForm_invalidDeliveryTime_span").style.display = "none";
							this.type="time";'
						onblur='
							this.type="text";
							if (this.value != "") {
								this.style.color="black";
								this.value = "to " + this.value;
								csa_wp_plugin_validate_delivery_time_period("showNewSpotForm");
							}
						'>
					<span id="csa-wp-plugin-showNewSpotForm_invalidDeliveryTime_span"> </span>
				</td></tr>
			
				<tr valign="top"><td>
				<select 
					name="csa-wp-plugin-spots_close_input" 
					id="csa-wp-plugin-spots_close_input_id"
					<?php 
						if (!$is_delivery_spot) {
							echo "style='color:#999'";
						}
					?>
					onfocus='getElementById("csa-wp-plugin-showNewSpotForm_ordersClose_span_id").innerHTML = "";'
					onchange='
						this.style.color="black";
						if (this.options[this.selectedIndex].text.split(" ")[0] != "Orders") {
							this.options[this.selectedIndex].text = "Orders close "+this.options[this.selectedIndex].text;
						}
						getElementById("csa-wp-plugin-spots_close_"+ this.options[this.selectedIndex].value).style.display= "inline";
						getElementById("csa-wp-plugin-spots_close_"+ this.options[((this.selectedIndex-1)+1)%2+1].value).style.display="none"'>
					<option value="" 
						<?php 
							if (!$is_delivery_spot) { 
								echo "selected='selected'";
							}
						?> 
						disabled="disabled" id="csa-wp-plugin-spots_close_disabled_id">Orders close... *</option>
					<option value="automatic" 
						<?php 
							if ($is_delivery_spot && $spot_info[0]->close_order =="automatic") {
								echo "selected='selected'";
							}
						?> 
						title="Orders' submission will be closed automatically when order submission deadline is reached">
						<?php 
							if ($is_delivery_spot && $spot_info[0]->close_order =="automatic") {
								echo "Orders close "; 
							}
						?>
						automatically</option>
					<option value="manual" 
						<?php 
							if ($is_delivery_spot && $spot_info[0]->close_order =="manual") {
								echo "selected='selected'";
							}
						?> 
						title="Orders' submission will be closed manually by the user that is responsible for the delivery">
						<?php 
							if ($is_delivery_spot && $spot_info[0]->close_order =="manual") {
								echo "Orders close "; 
							}
						?>
						manually</option>
				</select>
				<span 
					<?php 
						if (!$is_delivery_spot || $spot_info[0]->close_order !="automatic") { 
							echo "style='display:none;color:gray'";
						} else {
							echo "style='color:gray'";
						}
					?> 
					id="csa-wp-plugin-spots_close_automatic" 
					class="csa-wp-plugin-tip_spots"
					title="Orders' submission will be closed automatically when order submission deadline is reached"
				>&nbsp;i.e. ... (point here)</span>
				<span 
					<?php 
						if (!$is_delivery_spot || $spot_info[0]->close_order !="manual") { 
							echo "style='display:none;color:gray'";
						} else {
							echo "style='color:gray'";
						}
					?> 
					id="csa-wp-plugin-spots_close_manual"
					class="csa-wp-plugin-tip_spots"
					title="Orders' submission will be closed manually by the user that is responsible for the delivery"
				>&nbsp;i.e. ... (point here)</span>
				<span id="csa-wp-plugin-showNewSpotForm_ordersClose_span_id"></span>
				</td></tr>

				<tr valign="top"><td>
					<select 
						name="csa-wp-plugin-spots_parking_input" 
						id="csa-wp-plugin-spots_parking_input_id"
						<?php 
							$styleAttributes = "style='color:";
							if (!$is_delivery_spot || $spot_info[0]->parking =="" ) {
								$styleAttributes = $styleAttributes."#999'";
							} else if ($spot_info[0]->parking == "easy") {
								$styleAttributes = $styleAttributes."blue'";
							} else if ($spot_info[0]->parking =="possible") {
								$styleAttributes = $styleAttributes."green'";
							} else if ($spot_info[0]->parking =="hard") {
								$styleAttributes = $styleAttributes."orange'";
							} else if ($spot_info[0]->parking =="impossible") {
								$styleAttributes = $styleAttributes."brown'";
							}
							echo $styleAttributes;
						?>						
						onfocus='getElementById("csa-wp-plugin-showNewSpotForm_parkingSpace_span_id").style.display = "none";'
						onchange='
							this.style.color=this.options[this.selectedIndex].style.color;
							if (this.options[this.selectedIndex].text.split(" ")[0] != "Finding") {
								this.options[this.selectedIndex].text = "Finding parking space is " + this.options[this.selectedIndex].text;
							}'
					>
						<option value="" 
						<?php 
							if (!$is_delivery_spot || $spot_info[0]->parking =="") {
								echo "selected='selected'";
							}
						?> 
							disabled="disabled" id="csa-wp-plugin-spots_parking_disabled_id">Finding parking space is ...</option>
						<option value="easy" 
							<?php 
								if ($is_delivery_spot && $spot_info[0]->parking !="" && $spot_info[0]->parking =="easy") {
									echo "selected='selected'";
								}
							?> 
							style="color:blue">
							<?php 
								if ($is_delivery_spot && $spot_info[0]->parking !="" && $spot_info[0]->parking =="easy") {
									echo "Finding parking space is "; 
								}
							?>
							easy :)</option>
						<option value="possible" 
							<?php 
								if ($is_delivery_spot && $spot_info[0]->parking !="" && $spot_info[0]->parking =="possible") {
									echo "selected='selected'";
								}
							?> 
							style="color:green">
							<?php 
								if ($is_delivery_spot && $spot_info[0]->parking !="" && $spot_info[0]->parking =="possible") {
									echo "Finding parking space is "; 
								}
							?>
							possible :)</option>
						<option value="hard" 
							<?php 
								if ($is_delivery_spot && $spot_info[0]->parking !="" && $spot_info[0]->parking =="hard")  {
									echo "selected='selected'";
								}
							?> 
							style="color:orange">
							<?php 
								if ($is_delivery_spot && $spot_info[0]->parking !="" && $spot_info[0]->parking =="hard") {
									echo "Finding parking space is "; 
								}
							?>
							hard :(</option>
						<option value="impossible" 
							<?php 
								if ($is_delivery_spot && $spot_info[0]->parking !="" && $spot_info[0]->parking =="impossible") {
									echo "selected='selected'";
								}
							?> 
							style="color:brown">
							<?php 
								if ($is_delivery_spot && $spot_info[0]->parking !="" && $spot_info[0]->parking =="impossible") {
									echo "Finding parking space is "; 
								}
							?>
							impossible :(</option>
					</select>
					<span style="display:none;color:blue" id="csa-wp-plugin-showNewSpotForm_parkingSpace_span_id"></span>
				</td></tr>
				<tr valign="top"><td>
					<select 
						name="csa-wp-plugin-spots_refrigerator_input" 
						id="csa-wp-plugin-spots_refrigerator_input_id"
						<?php 
							$styleAttributes = "style='color:";
							if (!$is_delivery_spot || $spot_info[0]->has_refrigerator =="" ) {
								$styleAttributes = $styleAttributes."#999'";
							} else if ($spot_info[0]->has_refrigerator == "1") {
								$styleAttributes = $styleAttributes."green'";
							} else if ($spot_info[0]->has_refrigerator == "0") {
								$styleAttributes = $styleAttributes."brown'";
							}
							echo $styleAttributes;
						?>						
						onfocus='getElementById("csa-wp-plugin-showNewSpotForm_hasRefrigerator_span_id").style.display = "none";'
						onchange='
							(this.style.color=this.options[this.selectedIndex].style.color);
							if (this.options[this.selectedIndex].value == "yes") {
								this.options[this.selectedIndex].text = "It has refrigerator to store products! :)";
							}
							else {
								this.options[this.selectedIndex].text = "It does not have refrigerator to store products! :("
							}'
					>
						<option value="" 
							<?php 
								if (!$is_delivery_spot || $spot_info[0]->has_refrigerator =="") {
									echo "selected='selected'";
								}
							?> 
							disabled="disabled" id="csa-wp-plugin-spots_refrigerator_disabled_id">Does it have refrigerator to store products...?</option>
						<option value="yes" style="color:green"
							<?php 
								if ($is_delivery_spot && $spot_info[0]->has_refrigerator !="" && $spot_info[0]->has_refrigerator =="1") {
									echo "selected='selected'";
								}
							?> 
							>
							<?php 
								if ($is_delivery_spot && $spot_info[0]->has_refrigerator !="" && $spot_info[0]->has_refrigerator =="1") {
									echo "It has refrigerator to store products! :) "; 
								} else {
									echo "yes";
								}
							?> </option>
						<option value="no" style="color:brown"
							<?php 
								if ($is_delivery_spot && $spot_info[0]->has_refrigerator !="" && $spot_info[0]->has_refrigerator =="0") {
									echo "selected='selected'";
								}
							?> 
							>
							<?php 
								if ($is_delivery_spot && $spot_info[0]->has_refrigerator !="" && $spot_info[0]->has_refrigerator =="0") {
									echo "It does not have refrigerator to store products! :( "; 
								} else {
									echo "no";
								}
							?></option>
					</select> 
					<span style="display:none;color:blue" id="csa-wp-plugin-showNewSpotForm_hasRefrigerator_span_id"></span>
				</td></tr>
				
			</table> 
			</div>
			<input 
				type="submit" 
				name="csa-wp-plugin-showNewSpot_button" 
				id="csa-wp-plugin-showNewSpot_button_id"
				<?php 
					if ($spot_id == null) {
						echo "
							value='Add Spot'
							onclick='csa_wp_plugin_new_spot_fields_validation(this, null, \"". admin_url("/admin.php?page=csa_spots_management") ."\")'
						";
					} else {
						echo "
							value='Update Spot'
							onclick='csa_wp_plugin_new_spot_fields_validation(this, $spot_id, \"". admin_url("/admin.php?page=csa_spots_management") ."\")'
						";
					}
				?>
				class="button button-primary"
				title="Please fill in required fields, i.e. those marked with (*)"
			/>
			<input 
				type="button"
				class="button button-secondary"
				<?php 
				if ($spot_id == null) {
					echo "
					value='Reset Info'
					onclick='csa_wp_plugin_reset_spot_form();'";
				} else { 
					echo "
					value='Cancel'
					onclick='window.location.replace(\"". admin_url('/admin.php?page=csa_spots_management')."\")'
					'";
				}
				?>
			/>
		</form>
		<br/><br/>
	</div>
	
<?php
}

add_action( 'wp_ajax_csa-wp-plugin-check_spot_name_validity', 'csa_wp_plugin_check_spot_name_validity' );

function csa_wp_plugin_check_spot_name_validity() {

	if( isset($_POST['spot_name']) && isset($_POST['spot_id']) && isset($_POST['num_entries_exist'])) {
		$newspot_name = csa_wp_plugin_clean_input($_POST['spot_name']);
		$spot_id = csa_wp_plugin_clean_input($_POST['spot_id']);
		if ($spot_id) {
			$spot_id = intval($spot_id);
		}
		$num_entries_exist = intval(csa_wp_plugin_clean_input($_POST['num_entries_exist']));
		
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare("SELECT id FROM ".CSA_WP_PLUGIN_TABLE_SPOTS." WHERE spot_name=%s", $newspot_name));
		if ($wpdb->num_rows > $num_entries_exist && $results[0]->id != $spot_id) {
			echo 'invalid spot name';
		} else if  ($wpdb->num_rows > $num_entries_exist && $results[0]->id == $spot_id) {
			echo 'valid updating spot name';
		} else {
			echo 'valid spot name';
		}
	} else {
		echo 'error,Bad request.';
	}
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_csa-wp-plugin-spot_add_or_update_request', 'csa_wp_plugin_add_or_update_spot' );

function csa_wp_plugin_add_or_update_spot() {

	if( isset($_POST['data']) && isset($_POST['spot_id'])) {

		$data_received = json_decode(stripslashes($_POST['data']),true);
		
		$data_vals = array(
					'spot_name' 					=> $data_received[0]['value'],
					'street_name' 					=> $data_received[1]['value'],
					'street_number' 				=> $data_received[2]['value'],
					'city' 							=> $data_received[3]['value'],
					'region' 						=> $data_received[4]['value'],
					'description' 					=> $data_received[5]['value'],
					'is_delivery_spot'				=> $data_received[6]['value'] == "yes"?1:0
				);
		$data_types = array ("%s", "%s", "%s", "%s", "%s", "%s", "%s");
		
		$ownerID;
		if ($data_vals['is_delivery_spot']) {
			$ownerID = $data_received[7]['value'];
			$data_vals += array(
						'default_order_deadline_day' 	=> $data_received[8]['value'],
						'default_order_deadline_time' 	=> explode(' ', $data_received[9]['value'])[2],
						'default_delivery_day' 			=> $data_received[10]['value'],
						'default_delivery_start_time' 	=> explode(' ', $data_received[11]['value'])[1],
						'default_delivery_end_time'		=> explode(' ', $data_received[12]['value'])[1],
						'close_order'					=> $data_received[13]['value'],
						'parking'						=> $data_received[14]['value'],
						'has_refrigerator'				=> $data_received[15]['value'] == "yes"?1:0
						
					);
			$data_types += array ("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s");
		}
		
		global $wpdb;
		
		
		$spot_id = intval(csa_wp_plugin_clean_input($_POST['spot_id']));
		
		if ($spot_id != null) { //update spot (query)
			$spot_id = intval($spot_id);
			if ($wpdb->update(
					CSA_WP_PLUGIN_TABLE_SPOTS,
					$data_vals,
					array('id' => $spot_id), 
					$data_types
				) === FALSE) {
				echo 'error, sql request failed.';												
			}
			else { 		
				if ($data_vals['is_delivery_spot']) {
					if($wpdb->update(
						CSA_WP_PLUGIN_TABLE_SPOTS_TO_USERS,
						array(
							'user_id' 	=> $ownerID
						), 
						array(
							'spot_id'	=> $spot_id,
							'type'		=> 'delivery'
						),
						array ("%d")
					) === FALSE) {
						echo 'error, sql request failed.';												
					} else {
						echo 'Success, spot and spot to user relationship are updated.';
					}
				} else {
					echo 'Success, spot is updated.';
				}
			}
		}

		
		else { 	//insert spot (query)
			if(	$wpdb->insert(
				CSA_WP_PLUGIN_TABLE_SPOTS, 
				$data_vals, 
				$data_types
			) === FALSE) {
				echo 'error, sql request failed.';
			} else { 
				if ($data_vals['is_delivery_spot']) {
					if($wpdb->insert(
						CSA_WP_PLUGIN_TABLE_SPOTS_TO_USERS,
						array(
							'spot_id' 	=> $wpdb->insert_id,
							'user_id' 	=> $ownerID,
							'type'		=> 'delivery'
						), 
						array ("%d", "%d", "%s")
					) === FALSE) {
						echo 'error, sql request failed.';												
					} else {
						echo 'Success, spot and spot to user relationship are added.';
					}
				} else {
					echo 'Success, spot is added.';
				}
			}
		}
	}
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

function csa_wp_plugin_show_spots() {
	wp_enqueue_script('csa-wp-plugin-enqueue-csa-scripts');
	wp_enqueue_script('csa-wp-plugin-spots-scripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); 
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

?>

	<br/>
	<div id="csa-wp-plugin-showSpotsList_header"><span style="cursor:pointer" id="csa-wp-plugin-showSpotsList_formHeader_text" onclick="csa_wp_plugin_toggle_form('showSpotsList','Spots List', '')"><font size='4'>Spots List (hide)</font></span></div>
	<div id="csa-wp-plugin-showSpotsList_div" style="display:block">		

		<span class='csa-wp-plugin-tip_spots' title='
			If you want to update a field (except the last one), click on it, write the new value, and then press ENTER.
			| If you want to edit some of the other spot details, click on the "pen" icon.
			| If you want to delete some spot, click on the "x" icon.
		'>
		<p style="color:green;font-style:italic; font-size:13px">
			by pointing here you can read additional information...</p></span>
			
		<table class='table-bordered' id="csa-wp-plugin-showSpotsList_table" style='border-spacing:1em'> 
		<thead class='tableHeader'>
			<tr>
				<th>Spot Name</th>
				<th>Street Name</th>
				<th>Street Number</th>
				<th>City</th>
				<th>Region</th>
				<th>Description</th>
				<th>Delivery Spot?</th>
				<th/>
				<th/>
			</tr>
		</thead> 
		<tbody> <?php
			global $wpdb;
			$plugins_dir = plugins_url();
			
			$spots = $wpdb->get_results("SELECT * FROM ". CSA_WP_PLUGIN_TABLE_SPOTS);
			foreach($spots as $row) 
			{
				$spot_id = $row->id;
				echo "
					<tr valign='top' 
						id='csa-wp-plugin-showSpotsSpotID_$spot_id' 
						class='csa-wp-plugin-showSpotsSpotID-spot'
						style='text-align:center'
					>
					<td class='editable'>$row->spot_name </td>
					<td class='editable'>$row->street_name </td>
					<td class='editable'>$row->street_number</td>
					<td class='editable'>$row->city</td>
					<td class='editable'>$row->region</td>
					<td class='editable'>$row->description</td>
					<td>".($row->is_delivery_spot==1?'yes':($row->is_delivery_spot==NULL?'unknown':'no'))."</td>
					<td style='text-align:center'> 
						<img 
							width='24' height='24'  
							class='delete no-underline' 
							src='$plugins_dir/csa-wp-plugin/icons/edit.png' 
							style='cursor:pointer;padding-left:10px;' 
							onclick='csa_wp_plugin_edit_spot(this, \"". admin_url('/admin.php?page=csa_spots_management')."\")' 
							title='click to edit this spot'/></td>
					<td style='text-align:center'> 
						<img 
							class='delete no-underline' 
							src='$plugins_dir/csa-wp-plugin/icons/delete.png' 
							style='cursor:pointer;padding-left:10px;' 
							onmouseover='csa_wp_plugin_hover_icon(this, \"delete\", \"$plugins_dir\")' 
							onmouseout='csa_wp_plugin_unhover_icon(this, \"delete\", \"$plugins_dir\")' 
							onclick='csa_wp_plugin_request_delete_spot(this)' title='click to delete this spot'/></td></tr>";
			}
			?>
		</tbody> </table>
	</div>	
<?php
}

add_action( 'wp_ajax_csa-wp-plugin-update-spot', 'csa_wp_plugin_update_spot' );

function csa_wp_plugin_update_spot() {
	if(isset($_POST['value']) && isset($_POST['column']) && isset($_POST['spot_id'])) {
		//$old_value = csa_wp_plugin_clean_input($_POST['old_val']);
		$new_value = csa_wp_plugin_clean_input($_POST['value']);
		$column_num = intval(csa_wp_plugin_clean_input($_POST['column']))+1; //not valid for getting the right column, when html table structure differs from the relative db table
		if ($column_num == 7) $new_value = ($new_value == "yes"?1:0);

		$spot_id = intval(csa_wp_plugin_clean_input($_POST['spot_id']));
		if(!empty($column_num) && !empty($spot_id)) {
			// Updating the information 
			global $wpdb;
			//get column names and assign them to an array
			$columns = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".CSA_WP_PLUGIN_TABLE_SPOTS."' ORDER BY ORDINAL_POSITION", ARRAY_N);
			//update the database, using the relative column name
			$column_name = $columns[$column_num][0];

			if(	$wpdb->update(
				CSA_WP_PLUGIN_TABLE_SPOTS,
				array($column_name => $new_value), 
				array('id' => $spot_id )
			) === FALSE) 
				echo 'error, sql request failed.';												
			else echo 'success,'.$new_value;
		} 
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_csa-wp-plugin-delete_spot', 'csa_wp_plugin_delete_spot' );

function csa_wp_plugin_delete_spot() {
	if(isset($_POST['spot_id'])) {
		$spot_id = intval(csa_wp_plugin_clean_input($_POST['spot_id']));
		if(!empty($spot_id)) {
		
			global $wpdb;

			$spot_is_used_in_deliveries = $wpdb->get_var($wpdb->prepare("
													SELECT COUNT(spot_id)
													FROM ".CSA_WP_PLUGIN_TABLE_DELIVERIES." 
													WHERE spot_id=%d", $spot_id));

			$spot_is_used_in_spots_to_users = $wpdb->get_var($wpdb->prepare("
													SELECT COUNT(spot_id)
													FROM ".CSA_WP_PLUGIN_TABLE_SPOTS_TO_USERS." 
													WHERE spot_id=%d", $spot_id));
		
			if ($spot_is_used_in_deliveries > 0) {
				echo 'skipped, used in deliveries';
			} else {
				// deleting entries in spot to user array
				if(	$wpdb->delete(
					CSA_WP_PLUGIN_TABLE_SPOTS_TO_USERS,
					array('spot_id' => $spot_id ),
					array ('%d')
				) === FALSE) {
					echo 'error, sql request failed.';												
				} else {
					// deleting spot 
					if(	$wpdb->delete(
						CSA_WP_PLUGIN_TABLE_SPOTS,
						array('id' => $spot_id ),
						array ('%d')
					) === FALSE) {
						echo 'error, sql request failed.';												
					} else {
						echo 'success';
					}				
					echo 'success';
				}
			}
		} else {
			echo 'error,Empty values.';
		}
	} else {
		echo 'error,Bad request.';
	}
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

function csa_wp_plugin_delivery_spots_exist ($personal_order){
	global $wpdb;
	if ($wpdb->get_var("SELECT COUNT(id) FROM " .CSA_WP_PLUGIN_TABLE_SPOTS. " WHERE is_delivery_spot = 1") == 0) {
		echo "<h3 style='color:brown'>sorry... no spots found...!</h3>";
		if ($personal_order === false)
			echo "<h4 style='color:gray'>You must create at least one delivery spot, before you initiate a delivery (for some spot).</h4>
				<h4 style='color:gray'>To do so, use the corresponding menu of CSA Managemement Panel or simply click 
				<a href='".
					admin_url('/admin.php?page=csa_spots_management')
				."'>here </a></h4>
			";
		else echo "<h4 style='color:gray'>In case something goes wrong, please contant one of your team's administrators...</h4>";
			
		return false;
	}
	else return true;	
}

function csa_wp_plugin_select_delivery_spots($selected_spot_id, $message) {
	global $wpdb;
	$deliverySpots = $wpdb->get_results("SELECT id,spot_name FROM ".CSA_WP_PLUGIN_TABLE_SPOTS." WHERE is_delivery_spot=1");
	
	foreach ($deliverySpots as $delivery_spot_id) {
		if ($delivery_spot_id->id == $selected_spot_id) 								
			echo "<option value='".$delivery_spot_id->id."' selected='selected' style='color:black'>". $message. $delivery_spot_id->spot_name ."</option>";
		else
			echo "<option value='".$delivery_spot_id->id."' style='color:black'>". $delivery_spot_id->spot_name ."</option>";
	}
}


?>