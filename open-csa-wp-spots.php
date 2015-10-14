<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function open_csa_wp_spot_form ($spot_id, $display){
	wp_enqueue_script( 'open-csa-wp-general-scripts' );
	wp_enqueue_script('open-csa-wp-spots-scripts');
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

	global $days_of_week,$wpdb;
	$spot_info;
	if ($spot_id != null) {
		$spot_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".OPEN_CSA_WP_TABLE_SPOTS." WHERE id=%d", $spot_id));
	}
?>
	
	<br />
	<div id="open-csa-wp-showNewSpot_formHeader">
		<span 	
			id="open-csa-wp-showNewSpot_formHeader_text" 
			<?php 
				if ($spot_id == null) {
					echo 'style="cursor:pointer"';
					echo 'onclick="open_csa_wp_toggle_form(\'showNewSpot\',\''.__('Add New Spot',OPEN_CSA_WP_DOMAIN).'\', \' '. __('form',OPEN_CSA_WP_DOMAIN).'\')"';
				}
			?>>
			<font size='4'>
			<?php 
			if ($spot_id == null) {
				if ($display == false) {
					_e('Add New Spot (show form)',OPEN_CSA_WP_DOMAIN);
				} else { 
					_e('Add New Spot (hide form)',OPEN_CSA_WP_DOMAIN);
				}
			} else {
				echo __('Edit Spot #',OPEN_CSA_WP_DOMAIN).$spot_id;
			}
			?>
		</font></span></div>
	<div id="open-csa-wp-showNewSpot_div" 
		<?php 
			if ($display == false) {
				echo "style='display:none'";
			}
		?>>
		<form method="POST" id="open-csa-wp-showNewSpot_form">
			<table class="form-table">
				<tr valign="top"><td>
					<input type="text" 
						<?php 
							if ($spot_id != null && $spot_info[0]->spot_name != "" && $spot_info[0]->spot_name != null) {
								echo "value='".$spot_info[0]->spot_name."'"; 
							}
							echo "placeholder=\"".__('Spot Name *',OPEN_CSA_WP_DOMAIN)."\"";
						?>
						required 
						name="open-csa-wp-spots_spot_name_input" 
						onchange="open_csa_wp_request_spot_name_validity(this.value, <?php echo ($spot_id!=null?$spot_id:"null");?> , 0, null)">
					<span id="open-csa-wp-showNewSpot_name_span_id" style="display:none"></span></td></tr>
				<tr valign="top"><td>
					<input 
						type="text" 
						<?php 
							if ($spot_id != null && $spot_info[0]->street_name != "" && $spot_info[0]->street_name != null) {
								echo "value='".$spot_info[0]->street_name."'"; 
							}							
							echo "placeholder=\"".__('Street Name *',OPEN_CSA_WP_DOMAIN)."\"";
						?>
						required 
						name="open-csa-wp-spots_street_name_input">
					<input 
						type="text" 
						<?php 
							if ($spot_id != null && $spot_info[0]->street_number != "" && $spot_info[0]->street_number != null) {
								echo "value='".$spot_info[0]->street_number."'"; 
							}
							echo "placeholder=\"".__('Street Number *',OPEN_CSA_WP_DOMAIN)."\"";
						?>
						required 
						name="open-csa-wp-spots_street_number_input"></td></tr>
				<tr valign="top"><td>
					<input 
						type="text" 
						<?php 
							if ($spot_id != null && $spot_info[0]->city != "" && $spot_info[0]->city != null) {
								echo "value='".$spot_info[0]->city."'"; 
							}
							echo "placeholder=\"".__('City *',OPEN_CSA_WP_DOMAIN)."\"";
						?>
						required 
						name="open-csa-wp-spots_city_input">
					<input 
						type="text" 
						<?php 
							if ($spot_id != null && $spot_info[0]->region != "" && $spot_info[0]->region != null) {
								echo "value='".$spot_info[0]->region."'"; 
							}
							echo "placeholder=\"".__('Region *',OPEN_CSA_WP_DOMAIN)."\"";
						?>
						required 
						name="open-csa-wp-spots_region_input"></td></tr>
				<tr valign="top"><td>
					<textarea name="open-csa-wp-spots_description_input" rows="3" cols="30" 
						<?php _e('placeholder="Description"',OPEN_CSA_WP_DOMAIN); ?>
						><?php 
							if ($spot_id != null) {
								echo $spot_info[0]->description; 
							}
						?></textarea></td></tr>
				
				<tr valign="top"><td>
					<select name="open-csa-wp-spots_is_delivery_spot_input" id="open-csa-wp-spots_is_delivery_spot_input_id"  onchange='open_csa_wp_show_new_spot_is_delivery_selection(this,<?php echo ($spot_id?$spot_id:"null")?>)'
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
							<option value="yes" style="color:black"><?php _e('yes',OPEN_CSA_WP_DOMAIN);?></option>
							<option value="no" style="color:black"><?php _e('no',OPEN_CSA_WP_DOMAIN);?></option>
						<?php
						}
						?>
					</select> 
					<span id="open-csa-wp-showNewSpotForm_deliverySpot_span"></span>
				
				</td></tr>				
				
			</table> 
			<div id = "open-csa-wp-spots_deliverySpot_div" 
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
						name="open-csa-wp-delivery_spot_owner_input" 
						id="open-csa-wp-delivery_spot_owner_input_id"
						<?php if (!$is_delivery_spot) echo "style='color:#999'";?>						
						onfocus='getElementById("open-csa-wp-delivery_spot_owner_span_id").style.display = "none";'
						onchange='
							if (this.options[this.selectedIndex].text.split(" ")[0] != "<?php _e('The owner of this delivery spot is',OPEN_CSA_WP_DOMAIN);?>".split(" ")[0]) 
								this.options[this.selectedIndex].text = "<?php _e('The owner of this delivery spot is',OPEN_CSA_WP_DOMAIN);?>: "+ this.options[this.selectedIndex].text;
							(this.style.color=this.options[this.selectedIndex].style.color);'
					>
						<option value="" 
							<?php if (!$is_delivery_spot) echo "selected='selected'";?> 
							disabled="disabled" id="open-csa-wp-delivery_spot_owner_disabled_id"><?php _e('Owner of this delivery spot... *',OPEN_CSA_WP_DOMAIN);?>
						</option>
						<?php 
						$spot_owner_id = null;
						if ($is_delivery_spot) {
							$spot_owner_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM ".OPEN_CSA_WP_TABLE_SPOTS_TO_USERS." WHERE spot_id=%d AND type='delivery' ", $spot_id), 0, 0);
						}							
						open_csa_wp_select_users($spot_owner_id, __("The owner of this delivery spot is: ", OPEN_CSA_WP_DOMAIN));
						
						?>
					</select> 
					<span style="display:none;color:blue" id="open-csa-wp-delivery_spot_owner_span_id"></span>
				</td></tr>
			
				<tr valign="top"><td>
					<select 
						name="open-csa-wp-spots_order_deadline_day_input"
						id='open-csa-wp-spots_order_deadline_day_input_id'
						onfocus='document.getElementById("open-csa-wp-showNewSpotForm_orderDeadline_span").style.display = "none";'
						onchange='
							this.style.color="black";
							if (this.options[this.selectedIndex].text.split(" ")[0] != "<?php _e('Order deadline is on',OPEN_CSA_WP_DOMAIN);?>".split(" ")[0])
								this.options[this.selectedIndex].text = "<?php _e('Order deadline is on',OPEN_CSA_WP_DOMAIN);?>: " + this.options[this.selectedIndex].text;
							getElementById("open-csa-wp-spots_order_deadline_time_input_id").style.display = "inline"
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
						disabled="disabled" id="open-csa-wp-spots_order_deadline_day_disabled_id"><?php _e('Order deadline day... *',OPEN_CSA_WP_DOMAIN);?> </option>
					<?php 
					for ($i=0; $i<7; $i++) {
						if ($is_delivery_spot && $spot_info[0]->default_order_deadline_day == $i) {
							echo "<option value='$i' selected='selected' style='color:black'>". __('Order deadline is on ', OPEN_CSA_WP_DOMAIN)." $days_of_week[$i] </option>";
						} else {
							echo "<option value='$i' style='color:black'>".$days_of_week[$i]."</option>";
						}
					}
					?>
					</select> 	
					<input 
						<?php 
							if ($is_delivery_spot && $spot_info[0]->default_order_deadline_time != "" && $spot_info[0]->default_order_deadline_time != null) {
								echo "value='".__('up to',OPEN_CSA_WP_DOMAIN). " " .open_csa_wp_remove_seconds($spot_info[0]->default_order_deadline_time)."'";
							}
							if (!$is_delivery_spot) {
								echo "style='display:none'";
							}
							echo "placeholder=\"". __('up to',OPEN_CSA_WP_DOMAIN)."... *\"";
						?>
						id="open-csa-wp-spots_order_deadline_time_input_id"
						class="textbox-n" type="text" size="10" name="open-csa-wp-spots_order_deadline_time_input"
						onfocus='
							if (this.value != "") {
								this.value=this.value.split(" ")[2];
							} else {
								getElementById("open-csa-wp-showNewSpotForm_orderDeadline_span").style.display = "none";
							}
							this.type="time";'
						onblur='
							this.type="text";
							if (this.value != "") {
								this.style.color="black";
								this.value = "<?php _e('up to',OPEN_CSA_WP_DOMAIN);?> " + this.value;
							}'>
					<span id="open-csa-wp-showNewSpotForm_orderDeadline_span" style="display:none"></span>
				</td></tr>
								
				<tr valign="top"><td>
					<select 
						name="open-csa-wp-spots_delivery_day_input" 
						id="open-csa-wp-spots_delivery_day_input_id"
						onfocus='getElementById("open-csa-wp-showNewSpotForm_invalidDeliveryTime_span").innerHTML = "";'
						onchange='
							this.style.color="black";
							if (this.options[this.selectedIndex].text.split(" ")[0] != "<?php _e('Delivery day is',OPEN_CSA_WP_DOMAIN);?>".split(" ")[0]) {
								this.options[this.selectedIndex].text = "<?php _e('Delivery day is',OPEN_CSA_WP_DOMAIN);?> " + this.options[this.selectedIndex].text;
							}
							
							getElementById("open-csa-wp-showNewSpotForm_delivery_start_time_input_id").style.display = "inline"
							getElementById("open-csa-wp-showNewSpotForm_delivery_end_time_input_id").style.display = "inline";
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
						id="open-csa-wp-spots_delivery_day_disabled_id"><?php _e('Delivery day',OPEN_CSA_WP_DOMAIN);?>... *</option>
					<?php 
					for ($i=0; $i<7; $i++) {
						if ($is_delivery_spot && $spot_info[0]->default_delivery_day == $i) {
							echo "<option value='$i' selected='selected'>". __('Delivery day is',OPEN_CSA_WP_DOMAIN)." $days_of_week[$i] </option>";
						} else { 
							echo "<option value='$i'>".$days_of_week[$i]."</option>";
						}
					}
					?>
					</select> 
					<input id="open-csa-wp-showNewSpotForm_delivery_start_time_input_id"
						<?php 
							if ($is_delivery_spot && $spot_info[0]->default_delivery_start_time != "" && $spot_info[0]->default_delivery_start_time != null) { 
								echo "value='".__('from', OPEN_CSA_WP_DOMAIN)." ".open_csa_wp_remove_seconds($spot_info[0]->default_delivery_start_time)."'";
							}
							if (!$is_delivery_spot) {
								echo "style='display:none'";
							}
							echo "placeholder=\"". __('from',OPEN_CSA_WP_DOMAIN)."... *\"";
						?>
						class="textbox-n" type="text" size="10" name="open-csa-wp-spots_delivery_start_time_input"

						onfocus='
							if (this.value != "") {
								this.value=this.value.split(" ")[1];
							} else {
								getElementById("open-csa-wp-showNewSpotForm_invalidDeliveryTime_span").style.display = "none";
							}
							this.type="time";'
						onblur='
							this.type="text";
							if (this.value == "") {
								getElementById("open-csa-wp-showNewSpotForm_delivery_end_time_input_id").value = "";
							}
							else {
								this.style.color="black";
								this.value = "<?php _e('from',OPEN_CSA_WP_DOMAIN);?> " + this.value;
								open_csa_wp_validate_delivery_time_period("showNewSpotForm");
							}'>
					<input id="open-csa-wp-showNewSpotForm_delivery_end_time_input_id"
						<?php 
							if ($is_delivery_spot && $spot_info[0]->default_delivery_end_time != "" && $spot_info[0]->default_delivery_end_time != null) {
								echo "value='".__('to',OPEN_CSA_WP_DOMAIN)." ".open_csa_wp_remove_seconds($spot_info[0]->default_delivery_end_time)."'";
							}
							if (!$is_delivery_spot) {
								echo "style='display:none'";
							}
							echo "placeholder=\"". __('to',OPEN_CSA_WP_DOMAIN)."... *\"";
						?>
						class="textbox-n" type="text" size="10" name="open-csa-wp-spots_delivery_end_time_input"
						
						onfocus='
							if (this.value != "") { 
								this.value=this.value.split(" ")[1];
							}
							getElementById("open-csa-wp-showNewSpotForm_invalidDeliveryTime_span").style.display = "none";
							this.type="time";'
						onblur='
							this.type="text";
							if (this.value != "") {
								this.style.color="black";
								this.value = "<?php _e('to',OPEN_CSA_WP_DOMAIN);?> " + this.value;
								open_csa_wp_validate_delivery_time_period("showNewSpotForm");
							}
						'>
					<span id="open-csa-wp-showNewSpotForm_invalidDeliveryTime_span"> </span>
				</td></tr>
			
				<tr valign="top"><td>
				<select 
					name="open-csa-wp-spots_close_input" 
					id="open-csa-wp-spots_close_input_id"
					<?php 
						if (!$is_delivery_spot) {
							echo "style='color:#999'";
						}
					?>
					onfocus='getElementById("open-csa-wp-showNewSpotForm_ordersClose_span_id").innerHTML = "";'
					onchange='
						this.style.color="black";
						if (this.options[this.selectedIndex].text.split(" ")[0] != "<?php _e('Order close',OPEN_CSA_WP_DOMAIN);?>".split(" ")[0]) {
							this.options[this.selectedIndex].text = "<?php _e('Order close',OPEN_CSA_WP_DOMAIN);?> "+this.options[this.selectedIndex].text;
						}
						getElementById("open-csa-wp-spots_close_"+ this.options[this.selectedIndex].value).style.display= "inline";
						getElementById("open-csa-wp-spots_close_"+ this.options[((this.selectedIndex-1)+1)%2+1].value).style.display="none"'>
					<option value="" 
						<?php 
							if (!$is_delivery_spot) { 
								echo "selected='selected'";
							}
						?> 
						disabled="disabled" id="open-csa-wp-spots_close_disabled_id"><?php _e('Order close',OPEN_CSA_WP_DOMAIN);?>... *</option>
					<option value="automatic" 
						<?php 
							if ($is_delivery_spot && $spot_info[0]->close_order =="automatic") {
								echo "selected='selected'";
							}
						?> 
						title="<?php _e("Orders' submission will be closed automatically when order submission deadline is reached", OPEN_CSA_WP_DOMAIN); ?>">
						<?php 
							if ($is_delivery_spot && $spot_info[0]->close_order =="automatic") {
								echo __('Order close',OPEN_CSA_WP_DOMAIN)." "; 
							}
						?>
						automatically</option>
					<option value="manual" 
						<?php 
							if ($is_delivery_spot && $spot_info[0]->close_order =="manual") {
								echo "selected='selected'";
							}
						?> 
						title="<?php _e("Orders' submission will be closed manually by the user that is responsible for the delivery",OPEN_CSA_WP_DOMAIN); ?>">
						<?php 
							if ($is_delivery_spot && $spot_info[0]->close_order =="manual") {
								echo __('Order close',OPEN_CSA_WP_DOMAIN)." "; 
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
					id="open-csa-wp-spots_close_automatic" 
					class="open-csa-wp-tip_spots"
					title="<?php _e("Orders' submission will be closed automatically when order submission deadline is reached", OPEN_CSA_WP_DOMAIN); ?>"
				>&nbsp;i.e. ... (point here)</span>
				<span 
					<?php 
						if (!$is_delivery_spot || $spot_info[0]->close_order !="manual") { 
							echo "style='display:none;color:gray'";
						} else {
							echo "style='color:gray'";
						}
					?> 
					id="open-csa-wp-spots_close_manual"
					class="open-csa-wp-tip_spots"
					title="<?php _e("Orders' submission will be closed manually by the user that is responsible for the delivery",OPEN_CSA_WP_DOMAIN); ?>"
				>&nbsp;i.e. ... (point here)</span>
				<span id="open-csa-wp-showNewSpotForm_ordersClose_span_id"></span>
				</td></tr>

				<tr valign="top"><td>
					<select 
						name="open-csa-wp-spots_parking_input" 
						id="open-csa-wp-spots_parking_input_id"
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
						onfocus='getElementById("open-csa-wp-showNewSpotForm_parkingSpace_span_id").style.display = "none";'
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
							disabled="disabled" id="open-csa-wp-spots_parking_disabled_id"><?php _e("Finding parking space is", OPEN_CSA_WP_DOMAIN); ?>...</option>
						<option value="easy" 
							<?php 
								if ($is_delivery_spot && $spot_info[0]->parking !="" && $spot_info[0]->parking =="easy") {
									echo "selected='selected'";
								}
							?> 
							style="color:blue">
							<?php 
								if ($is_delivery_spot && $spot_info[0]->parking !="" && $spot_info[0]->parking =="easy") {
									echo __('Finding parking space is',OPEN_CSA_WP_DOMAIN) ." "; 
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
									echo __('Finding parking space is',OPEN_CSA_WP_DOMAIN) ." "; 
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
									echo __('Finding parking space is',OPEN_CSA_WP_DOMAIN) ." "; 
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
									echo __('Finding parking space is',OPEN_CSA_WP_DOMAIN) ." "; 
								}
							?>
							impossible :(</option>
					</select>
					<span style="display:none;color:blue" id="open-csa-wp-showNewSpotForm_parkingSpace_span_id"></span>
				</td></tr>
				<tr valign="top"><td>
					<select 
						name="open-csa-wp-spots_refrigerator_input" 
						id="open-csa-wp-spots_refrigerator_input_id"
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
						onfocus='getElementById("open-csa-wp-showNewSpotForm_hasRefrigerator_span_id").style.display = "none";'
						onchange='
							(this.style.color=this.options[this.selectedIndex].style.color);
							if (this.options[this.selectedIndex].value == "yes") {
								this.options[this.selectedIndex].text = "<?php _e('It has refrigerator to store products',OPEN_CSA_WP_DOMAIN);?>! :)";
							}
							else {
								this.options[this.selectedIndex].text = "<?php _e('It does not have refrigerator to store products',OPEN_CSA_WP_DOMAIN);?>! :("
							}'
					>
						<option value="" 
							<?php 
								if (!$is_delivery_spot || $spot_info[0]->has_refrigerator =="") {
									echo "selected='selected'";
								}
							?> 
							disabled="disabled" id="open-csa-wp-spots_refrigerator_disabled_id"><?php _e('Does it have refrigerator to store products',OPEN_CSA_WP_DOMAIN);?>...?</option>
						<option value="yes" style="color:green"
							<?php 
								if ($is_delivery_spot && $spot_info[0]->has_refrigerator !="" && $spot_info[0]->has_refrigerator =="1") {
									echo "selected='selected'";
								}
							?> 
							>
							<?php 
								if ($is_delivery_spot && $spot_info[0]->has_refrigerator !="" && $spot_info[0]->has_refrigerator =="1") {
									echo __('It has refrigerator to store products',OPEN_CSA_WP_DOMAIN). "! :) "; 
								} else {
									echo __("yes", OPEN_CSA_WP_DOMAIN);
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
									echo _e('It does not have refrigerator to store products',OPEN_CSA_WP_DOMAIN)."! :( "; 
								} else {
									echo __("no", OPEN_CSA_WP_DOMAIN);
								}
							?></option>
					</select> 
					<span style="display:none;color:blue" id="open-csa-wp-showNewSpotForm_hasRefrigerator_span_id"></span>
				</td></tr>
				
			</table> 
			</div>
			<input 
				type="submit" 
				name="open-csa-wp-showNewSpot_button" 
				id="open-csa-wp-showNewSpot_button_id"
				<?php 
					if ($spot_id == null) {
						echo "
							value='".__('Add Spot', OPEN_CSA_WP_DOMAIN)."'
							onclick='open_csa_wp_new_spot_fields_validation(this, null, \"". admin_url("/admin.php?page=csa_spots_management") ."\")'
						";
					} else {
						echo "
							value='".__('Update Spot', OPEN_CSA_WP_DOMAIN)."'
							onclick='open_csa_wp_new_spot_fields_validation(this, $spot_id, \"". admin_url("/admin.php?page=csa_spots_management") ."\")'
						";
					}
				?>
				class="button button-primary"
				title="<?php __('Please fill in required fields, i.e. those marked with (*)',OPEN_CSA_WP_DOMAIN); ?>"
			/>
			<input 
				type="button"
				class="button button-secondary"
				<?php 
				if ($spot_id == null) {
					echo "
					value='".__('Reset Info', OPEN_CSA_WP_DOMAIN)."'
					onclick='open_csa_wp_reset_spot_form();'";
				} else { 
					echo "
					value='".__('Cancel', OPEN_CSA_WP_DOMAIN)."'
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

add_action( 'wp_ajax_open-csa-wp-check_spot_name_validity', 'open_csa_wp_check_spot_name_validity' );

function open_csa_wp_check_spot_name_validity() {

	if( isset($_POST['spot_name']) && isset($_POST['spot_id']) && isset($_POST['num_entries_exist'])) {
		$newspot_name = open_csa_wp_clean_input($_POST['spot_name']);
		$spot_id = open_csa_wp_clean_input($_POST['spot_id']);
		if ($spot_id) {
			$spot_id = intval($spot_id);
		}
		$num_entries_exist = intval(open_csa_wp_clean_input($_POST['num_entries_exist']));
		
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare("SELECT id FROM ".OPEN_CSA_WP_TABLE_SPOTS." WHERE spot_name=%s", $newspot_name));
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

add_action( 'wp_ajax_open-csa-wp-spot_add_or_update_request', 'open_csa_wp_add_or_update_spot' );

function open_csa_wp_add_or_update_spot() {

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
		
		
		$spot_id = intval(open_csa_wp_clean_input($_POST['spot_id']));
		
		if ($spot_id != null) { //update spot (query)
			$spot_id = intval($spot_id);
			if ($wpdb->update(
					OPEN_CSA_WP_TABLE_SPOTS,
					$data_vals,
					array('id' => $spot_id), 
					$data_types
				) === FALSE) {
				echo 'error, sql request failed.';												
			}
			else { 		
				if ($data_vals['is_delivery_spot']) {
					if($wpdb->update(
						OPEN_CSA_WP_TABLE_SPOTS_TO_USERS,
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
				OPEN_CSA_WP_TABLE_SPOTS, 
				$data_vals, 
				$data_types
			) === FALSE) {
				echo 'error, sql request failed.';
			} else { 
				if ($data_vals['is_delivery_spot']) {
					if($wpdb->insert(
						OPEN_CSA_WP_TABLE_SPOTS_TO_USERS,
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

function open_csa_wp_show_spots() {
	wp_enqueue_script('open-csa-wp-general-scripts');
	wp_enqueue_script('open-csa-wp-spots-scripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); 
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

?>

	<br/>
	<div id="open-csa-wp-showSpotsList_header"><span style="cursor:pointer" id="open-csa-wp-showSpotsList_formHeader_text" onclick="open_csa_wp_toggle_form('showSpotsList','Spots List', '')"><font size='4'>Spots List (hide)</font></span></div>
	<div id="open-csa-wp-showSpotsList_div" style="display:block">		

		<span class='open-csa-wp-tip_spots' title='
			If you want to update a field (except the last one), click on it, write the new value, and then press ENTER.
			| If you want to edit some of the other spot details, click on the "pen" icon.
			| If you want to delete some spot, click on the "x" icon.
		'>
		<p style="color:green;font-style:italic; font-size:13px">
			by pointing here you can read additional information...</p></span>
			
		<table class='table-bordered' id="open-csa-wp-showSpotsList_table" style='border-spacing:1em'> 
		<thead class='tableHeader'>
			<tr>
				<th><?php _e('Spot Name', OPEN_CSA_WP_DOMAIN);?></th>
				<th><?php _e('Street Name', OPEN_CSA_WP_DOMAIN);?></th>
				<th><?php _e('Street Number', OPEN_CSA_WP_DOMAIN);?></th>
				<th><?php _e('City', OPEN_CSA_WP_DOMAIN);?></th>
				<th><?php _e('Region', OPEN_CSA_WP_DOMAIN);?></th>
				<th><?php _e('Description', OPEN_CSA_WP_DOMAIN);?></th>
				<th><?php _e('Delivery Spot?', OPEN_CSA_WP_DOMAIN);?></th>
				<th/>
				<th/>
			</tr>
		</thead> 
		<tbody> <?php
			global $wpdb;
			$plugins_dir = plugins_url();
			
			$spots = $wpdb->get_results("SELECT * FROM ". OPEN_CSA_WP_TABLE_SPOTS);
			foreach($spots as $row) 
			{
				$spot_id = $row->id;
				echo "
					<tr valign='top' 
						id='open-csa-wp-showSpotsSpotID_$spot_id' 
						class='open-csa-wp-showSpotsSpotID-spot'
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
							src='$plugins_dir/open-csa-wp/icons/edit.png' 
							style='cursor:pointer;padding-left:10px;' 
							onclick='open_csa_wp_edit_spot(this, \"". admin_url('/admin.php?page=csa_spots_management')."\")' 
							title='". __('click to edit this spot', OPEN_CSA_WP_DOMAIN) ."'/></td>
					<td style='text-align:center'> 
						<img 
							class='delete no-underline' 
							src='$plugins_dir/open-csa-wp/icons/delete.png' 
							style='cursor:pointer;padding-left:10px;' 
							onmouseover='open_csa_wp_hover_icon(this, \"delete\", \"$plugins_dir\")' 
							onmouseout='open_csa_wp_unhover_icon(this, \"delete\", \"$plugins_dir\")' 
							onclick='open_csa_wp_request_delete_spot(this)' title='". __('click to delete this spot', OPEN_CSA_WP_DOMAIN) ."'/></td></tr>";
			}
			?>
		</tbody> </table>
	</div>	
<?php
}

add_action( 'wp_ajax_open-csa-wp-update-spot', 'open_csa_wp_update_spot' );

function open_csa_wp_update_spot() {
	if(isset($_POST['value']) && isset($_POST['column']) && isset($_POST['spot_id'])) {
		//$old_value = open_csa_wp_clean_input($_POST['old_val']);
		$new_value = open_csa_wp_clean_input($_POST['value']);
		$column_num = intval(open_csa_wp_clean_input($_POST['column']))+1; //not valid for getting the right column, when html table structure differs from the relative db table
		if ($column_num == 7) $new_value = ($new_value == "yes"?1:0);

		$spot_id = intval(open_csa_wp_clean_input($_POST['spot_id']));
		if(!empty($column_num) && !empty($spot_id)) {
			// Updating the information 
			global $wpdb;
			//get column names and assign them to an array
			$columns = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".OPEN_CSA_WP_TABLE_SPOTS."' ORDER BY ORDINAL_POSITION", ARRAY_N);
			//update the database, using the relative column name
			$column_name = $columns[$column_num][0];

			if(	$wpdb->update(
				OPEN_CSA_WP_TABLE_SPOTS,
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

add_action( 'wp_ajax_open-csa-wp-delete_spot', 'open_csa_wp_delete_spot' );

function open_csa_wp_delete_spot() {
	if(isset($_POST['spot_id'])) {
		$spot_id = intval(open_csa_wp_clean_input($_POST['spot_id']));
		if(!empty($spot_id)) {
		
			global $wpdb;

			$spot_is_used_in_deliveries = $wpdb->get_var($wpdb->prepare("
													SELECT COUNT(spot_id)
													FROM ".OPEN_CSA_WP_TABLE_DELIVERIES." 
													WHERE spot_id=%d", $spot_id));

			$spot_is_used_in_spots_to_users = $wpdb->get_var($wpdb->prepare("
													SELECT COUNT(spot_id)
													FROM ".OPEN_CSA_WP_TABLE_SPOTS_TO_USERS." 
													WHERE spot_id=%d", $spot_id));
		
			if ($spot_is_used_in_deliveries > 0) {
				echo 'skipped, used in deliveries';
			} else {
				// deleting entries in spot to user array
				if(	$wpdb->delete(
					OPEN_CSA_WP_TABLE_SPOTS_TO_USERS,
					array('spot_id' => $spot_id ),
					array ('%d')
				) === FALSE) {
					echo 'error, sql request failed.';												
				} else {
					// deleting spot 
					if(	$wpdb->delete(
						OPEN_CSA_WP_TABLE_SPOTS,
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

function open_csa_wp_delivery_spots_exist ($personal_order){
	global $wpdb;
	if ($wpdb->get_var("SELECT COUNT(id) FROM " .OPEN_CSA_WP_TABLE_SPOTS. " WHERE is_delivery_spot = 1") == 0) {
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

function open_csa_wp_select_delivery_spots($selected_spot_id, $message) {
	global $wpdb;
	$deliverySpots = $wpdb->get_results("SELECT id,spot_name FROM ".OPEN_CSA_WP_TABLE_SPOTS." WHERE is_delivery_spot=1");
	
	foreach ($deliverySpots as $delivery_spot_id) {
		if ($delivery_spot_id->id == $selected_spot_id) 								
			echo "<option value='".$delivery_spot_id->id."' selected='selected' style='color:black'>". $message. $delivery_spot_id->spot_name ."</option>";
		else
			echo "<option value='".$delivery_spot_id->id."' style='color:black'>". $delivery_spot_id->spot_name ."</option>";
	}
}


?>