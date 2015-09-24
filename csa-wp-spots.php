<?php

function CsaWpPluginSpotForm ($spotID, $display){
	wp_enqueue_script( 'CsaWpPluginScripts' );
	wp_enqueue_script('CsaWpPluginSpotsScripts');
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

	global $days,$wpdb;
	$spotInfo;
	if ($spotID != null) 
		$spotInfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".csaSpots." WHERE id=%d", $spotID));
?>
	
	<br />
	<div id="csa-wp-plugin-showNewSpot_formHeader"><span style="cursor:pointer" id="csa-wp-plugin-showNewSpot_formHeader_text" 
		<?php 
			if ($spotID == null)
				echo 'onclick="CsaWpPluginToggleForm(\'showNewSpot\',\'Add New Spot\', \' form\')"';
			else echo 'onclick="CsaWpPluginToggleForm(\'showNewSpot\',\'Edit Spot\', \' form\')"';
		?>
	>
		<font size="4">
		<?php 
			if ($spotID == null)
				echo 'Add New Spot (show form)';
			else echo 'Edit Spot (show form)';

		?>
		</font></span></div>
	<div id="csa-wp-plugin-showNewSpot_div" 
		<?php 
			if ($spotID == null && $display == false) echo "style='display:none'";
		?>>
		<form method="POST" id="csa-wp-plugin-showNewSpot_form">
			<table class="form-table">
				<tr valign="top"><td>
					<input type="text" 
						<?php if ($spotID != null && $spotInfo[0]->spotName != "" && $spotInfo[0]->spotName != null) echo "value='".$spotInfo[0]->spotName."'"; ?>
						placeholder="Spot Name *"
						required 
						name="csa-wp-plugin-spots_spotName_input" 
						onchange="CsaWpPluginRequestSpotNameValidity(this.value, <?php echo ($spotID!=null?$spotID:"null");?> , 0, null)">
					<span id="csa-wp-plugin-showNewSpot_name_span_id" style="display:none"></span></td></tr>
				<tr valign="top"><td>
					<input 
						type="text" 
						<?php if ($spotID != null && $spotInfo[0]->streetName != "" && $spotInfo[0]->streetName != null) echo "value='".$spotInfo[0]->streetName."'"; ?>
						placeholder="Street Name *"
						required 
						name="csa-wp-plugin-spots_streetName_input">
					<input 
						type="text" 
						<?php if ($spotID != null && $spotInfo[0]->streetNumber != "" && $spotInfo[0]->streetNumber != null) echo "value='".$spotInfo[0]->streetNumber."'"; ?>
						placeholder="Street Number *"
						required 
						name="csa-wp-plugin-spots_streetNumber_input"></td></tr>
				<tr valign="top"><td>
					<input 
						type="text" 
						<?php if ($spotID != null && $spotInfo[0]->city != "" && $spotInfo[0]->city != null) echo "value='".$spotInfo[0]->city."'"; ?>
						placeholder="City *"
						required 
						name="csa-wp-plugin-spots_city_input">
					<input 
						type="text" 
						<?php if ($spotID != null && $spotInfo[0]->region != "" && $spotInfo[0]->region != null) echo "value='".$spotInfo[0]->region."'"; ?>
						placeholder="Region *"
						required 
						name="csa-wp-plugin-spots_region_input"></td></tr>
				<tr valign="top"><td>
					<textarea name="csa-wp-plugin-spots_description_input" rows="3" cols="30" placeholder="Description";
						><?php if ($spotID != null) echo $spotInfo[0]->description; ?></textarea></td></tr>
				
				<tr valign="top"><td>
					<select name="csa-wp-plugin-spots_isDeliverySpot_input" id="csa-wp-plugin-spots_isDeliverySpot_input_id"  onchange='CsaWpPluginShowNewSpotIsDeliverySelection(this,<?php echo ($spotID?$spotID:"null")?>)'
					<?php if ($spotID == null) echo "style='color:#999'"?>>
						<?php 
						if ($spotID != null) {
							echo '
								<option value="" style="color:#999" disabled="disabled">Is this a delivery spot? *</option>
								<option value="yes" style="color:black". '. ($spotInfo[0]->isDeliverySpot == 1?"selected='selected'> it is a delivery spot":">yes") .' </option>
								<option value="no" style="color:black"'. ($spotInfo[0]->isDeliverySpot == 0?"selected='selected'> it is not a delivery spot":">no") .' </option>
								';
						}
						else {
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
					if ($spotID != null && $spotInfo[0]->isDeliverySpot == 1) "style='display:block'";
					else echo "style='display:none'"; 
				?>>
				<table class="form-table">		
				
				<tr valign="top"><td>
					<select 
						name="csa-wp-plugin-delivery_spot_owner_input" 
						id="csa-wp-plugin-delivery_spot_owner_input_id"
						<?php if ($spotID == null) echo "style='color:#999'";?>						
						onfocus='getElementById("csa-wp-plugin-delivery_spot_owner_span_id").style.display = "none";'
						onchange='
							if (this.options[this.selectedIndex].text.split(" ")[0]!= "The") 
								this.options[this.selectedIndex].text = "The owner of this delivery spot is: "+ this.options[this.selectedIndex].text;
							(this.style.color=this.options[this.selectedIndex].style.color);'
					>
						<option value="" 
							<?php if ($spotID == null) echo "selected='selected'";?> 
							disabled="disabled" id="csa-wp-plugin-delivery_spot_owner_disabled_id">Owner of this delivery spot... *
						</option>
						<?php 
						$csaUsers = get_users();
						
						$spotOwner;
						if ($spotID != null)
							$spotOwner = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM ".csaSpotsToUsers." WHERE spot_id=%d AND type='delivery' ", $spotID), 0, 0);
						
						foreach ($csaUsers as $csaUser) {
							$text = $csaUser->user_login;
							$firstName = get_user_meta( $csaUser->ID, 'first_name', true );
							$lastName = get_user_meta( $csaUser->ID, 'last_name', true );
							
							if ($firstName != null) {
								$text = $text." (".$firstName; 
								if ($firstName != null) $text = $text." ".$lastName;
								$text = $text.")";
							}
							else if ($lastName != null)
								$text = $text." (".$lastName.")";

							if ($spotID == null)
								echo "<option value='".$csaUser->ID."' style='color:black'>".$text."</option>";
							else if ($csaUser->ID == $spotOwner) 								
								echo "<option value='".$csaUser->ID."' selected='selected' style='color:black'> The owner of this delivery spot is: ".$text."</option>";
							else
								echo "<option value='".$csaUser->ID."' style='color:black'>".$text."</option>";
						}
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
						<?php if ($spotID==null) echo "style='color:#999'"?>
					>
					<option value="" 
						<?php if ($spotID == null) echo "selected='selected'"; ?>
						disabled="disabled" id="csa-wp-plugin-spots_order_deadline_day_disabled_id">Order deadline day ... *</option>
					<?php 
					for ($i=0; $i<7; $i++) {
						if ($spotID!=null && $spotInfo[0]->default_order_deadline_day == $i)
							echo "<option value='$i' selected='selected'> Order deadline is on $days[$i] </option>";
						else echo "<option value='$i'>".$days[$i]."</option>";
					}
					?>
					</select> 
					<input 
						<?php 
							if ($spotID != null && $spotInfo[0]->default_order_deadline_time != "" && $spotInfo[0]->default_order_deadline_time != null) echo "value='up to ".CsaWpPluginRemoveSeconds($spotInfo[0]->default_order_deadline_time)."'";
							if ($spotID == null) echo "style='display:none'";
						?>
						placeholder="up to... *"
						id="csa-wp-plugin-spots_order_deadline_time_input_id"
						class="textbox-n" type="text" size="10" name="csa-wp-plugin-spots_order_deadline_time_input"
						onfocus='
							if (this.value != "") this.value=this.value.split(" ")[2];
							else getElementById("csa-wp-plugin-showNewSpotForm_orderDeadline_span").style.display = "none";
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
							if (this.options[this.selectedIndex].text.split(" ")[0] != "Delivery")
								this.options[this.selectedIndex].text = "Delivery day is " + this.options[this.selectedIndex].text;
							getElementById("csa-wp-plugin-spots_delivery_start_time_input_id").style.display = "inline"'
						<?php if ($spotID == null) echo "style='color:#999'";?>>
					<option value="" disabled="disabled" 
						<?php if ($spotID == null) echo "selected='selected'"; ?>
						id="csa-wp-plugin-spots_delivery_day_disabled_id">Delivery day ... *</option>
					<?php 
					for ($i=0; $i<7; $i++) {
						if ($spotID!=null && $spotInfo[0]->default_delivery_day == $i)
							echo "<option value='$i' selected='selected'> Delivery day is $days[$i] </option>";
						else echo "<option value='$i'>".$days[$i]."</option>";
					}
					?>
					</select> 
					<input id="csa-wp-plugin-spots_delivery_start_time_input_id"
						<?php 
							if ($spotID != null && $spotInfo[0]->default_delivery_strart_time != "" && $spotInfo[0]->default_delivery_strart_time != null) echo "value='from ".CsaWpPluginRemoveSeconds($spotInfo[0]->default_delivery_strart_time)."'";
							if ($spotID == null) echo "style='display:none'";
						?>
						placeholder="from... *"
						class="textbox-n" type="text" size="10" name="csa-wp-plugin-spots_delivery_start_time_input"

						onfocus='
							if (this.value != "") this.value=this.value.split(" ")[1];
							else getElementById("csa-wp-plugin-showNewSpotForm_invalidDeliveryTime_span").style.display = "none";
							this.type="time";'
						onblur='
							this.type="text";
							if (this.value == "") {
								getElementById("csa-wp-plugin-spots_delivery_end_time_input_id").style.display = "none";
								getElementById("csa-wp-plugin-spots_delivery_end_time_input_id").value = "";
							}
							else {
								this.style.color="black";
								this.value = "from " + this.value;
								getElementById("csa-wp-plugin-spots_delivery_end_time_input_id").style.display = "inline";
								CsaWpPluginValidateDeliveryTimePeriod();
							}'>
					<input id="csa-wp-plugin-spots_delivery_end_time_input_id"
						<?php 
							if ($spotID != null && $spotInfo[0]->default_delivery_end_time != "" && $spotInfo[0]->default_delivery_end_time != null) echo "value='to ".CsaWpPluginRemoveSeconds($spotInfo[0]->default_delivery_end_time)."'";
							if ($spotID == null) echo "style='display:none'";
						?>
						placeholder="to... *"					
						class="textbox-n" type="text" size="10" name="csa-wp-plugin-spots_delivery_end_time_input"
						
						onfocus='
							if (this.value != "") this.value=this.value.split(" ")[1];
							getElementById("csa-wp-plugin-showNewSpotForm_invalidDeliveryTime_span").style.display = "none";
							this.type="time";'
						onblur='
							this.type="text";
							if (this.value != "") {
								this.style.color="black";
								this.value = "to " + this.value;
								CsaWpPluginValidateDeliveryTimePeriod();
							}
						'>
					<span id="csa-wp-plugin-showNewSpotForm_invalidDeliveryTime_span"> </span>
				</td></tr>
			
				<tr valign="top"><td>
				<select 
					name="csa-wp-plugin-spots_close_input" 
					id="csa-wp-plugin-spots_close_input_id"
					<?php if ($spotID == null) echo "style='color:#999'";?>
					onfocus='getElementById("csa-wp-plugin-showNewSpotForm_ordersClose_span_id").innerHTML = "";'
					onchange='
						this.style.color="black";
						if (this.options[this.selectedIndex].text.split(" ")[0] != "Orders")
							this.options[this.selectedIndex].text = "Orders close "+this.options[this.selectedIndex].text;
						getElementById("csa-wp-plugin-spots_close_"+ this.options[this.selectedIndex].value).style.display= "inline";
						getElementById("csa-wp-plugin-spots_close_"+ this.options[((this.selectedIndex-1)+1)%2+1].value).style.display="none"'>
					<option value="" 
						<?php if ($spotID == null) echo "selected='selected'";?> 
						disabled="disabled" id="csa-wp-plugin-spots_close_disabled_id">Orders close... *</option>
					<option value="automatic" 
						<?php if ($spotID != null && $spotInfo[0]->close_order =="automatic") echo "selected='selected'";?> 
						title="Orders' submission will be closed automatically when order submission deadline is reached">
						<?php if ($spotID != null && $spotInfo[0]->close_order =="automatic") echo "Orders close "; ?>
						automatically</option>
					<option value="manual" 
						<?php if ($spotID != null && $spotInfo[0]->close_order =="manual") echo "selected='selected'";?> 
						title="Orders' submission will be closed manually by the user that is responsible for the delivery">
						<?php if ($spotID != null && $spotInfo[0]->close_order =="manual") echo "Orders close "; ?>
						manually</option>
				</select>
				<span 
					<?php 
						if ($spotID == null || $spotInfo[0]->close_order !="automatic") echo "style='display:none;color:gray'";
						else echo "style='color:gray'";
					?> 
					id="csa-wp-plugin-spots_close_automatic" 
					class="csa-wp-plugin-tip_spots"
					title="Orders' submission will be closed automatically when order submission deadline is reached"
				>&nbsp;i.e. ... (point here)</span>
				<span 
					<?php 
						if ($spotID == null || $spotInfo[0]->close_order !="manual") echo "style='display:none;color:gray'";
						else echo "style='color:gray'";
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
							if ($spotID == null || $spotInfo[0]->parking =="" ) $styleAttributes = $styleAttributes."#999'";
							else if ($spotInfo[0]->parking == "easy") $styleAttributes = $styleAttributes."blue'";
							else if ($spotInfo[0]->parking =="possible") $styleAttributes = $styleAttributes."green'";
							else if ($spotInfo[0]->parking =="hard") $styleAttributes = $styleAttributes."orange'";
							else if ($spotInfo[0]->parking =="impossible") $styleAttributes = $styleAttributes."red'";
							echo $styleAttributes;
						?>						
						onfocus='getElementById("csa-wp-plugin-showNewSpotForm_parkingSpace_span_id").style.display = "none";'
						onchange='
							this.style.color=this.options[this.selectedIndex].style.color;
							if (this.options[this.selectedIndex].text.split(" ")[0] != "Finding")
								this.options[this.selectedIndex].text = "Finding parking space is " + this.options[this.selectedIndex].text;'>
						<option value="" 
						<?php if ($spotID == null || $spotInfo[0]->parking =="") echo "selected='selected'";?> 
							disabled="disabled" id="csa-wp-plugin-spots_parking_disabled_id">Finding parking space is ...</option>
						<option value="easy" 
							<?php if ($spotID != null && $spotInfo[0]->parking !="" && $spotInfo[0]->parking =="easy") echo "selected='selected'";?> 
							style="color:blue">
							<?php if ($spotID != null && $spotInfo[0]->parking !="" && $spotInfo[0]->parking =="easy") echo "Finding parking space is "; ?>
							easy :)</option>
						<option value="possible" 
							<?php if ($spotID != null && $spotInfo[0]->parking !="" && $spotInfo[0]->parking =="possible") echo "selected='selected'";?> 
							style="color:green">
							<?php if ($spotID != null && $spotInfo[0]->parking !="" && $spotInfo[0]->parking =="possible") echo "Finding parking space is "; ?>
							possible :)</option>
						<option value="hard" 
							<?php if ($spotID != null && $spotInfo[0]->parking !="" && $spotInfo[0]->parking =="hard") echo "selected='selected'";?> 
							style="color:orange">
							<?php if ($spotID != null && $spotInfo[0]->parking !="" && $spotInfo[0]->parking =="hard") echo "Finding parking space is "; ?>
							hard :(</option>
						<option value="impossible" 
							<?php if ($spotID != null && $spotInfo[0]->parking !="" && $spotInfo[0]->parking =="impossible") echo "selected='selected'";?> 
							style="color:red">
							<?php if ($spotID != null && $spotInfo[0]->parking !="" && $spotInfo[0]->parking =="impossible") echo "Finding parking space is "; ?>
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
							if ($spotID == null || $spotInfo[0]->has_refrigerator =="" ) $styleAttributes = $styleAttributes."#999'";
							else if ($spotInfo[0]->has_refrigerator == "1") $styleAttributes = $styleAttributes."green'";
							else if ($spotInfo[0]->has_refrigerator == "0") $styleAttributes = $styleAttributes."red'";
							echo $styleAttributes;
						?>						
						onfocus='getElementById("csa-wp-plugin-showNewSpotForm_hasRefrigerator_span_id").style.display = "none";'
						onchange='
							(this.style.color=this.options[this.selectedIndex].style.color);
							if (this.options[this.selectedIndex].value == "yes")
								this.options[this.selectedIndex].text = "It has refrigerator to store products! :)";
							else this.options[this.selectedIndex].text = "It does not have refrigerator to store products! :("'>
						<option value="" 
							<?php if ($spotID == null || $spotInfo[0]->has_refrigerator =="") echo "selected='selected'";?> 
							disabled="disabled" id="csa-wp-plugin-spots_refrigerator_disabled_id">Does it have refrigerator to store products...?</option>
						<option value="yes" style="color:green"
							<?php if ($spotID != null && $spotInfo[0]->has_refrigerator !="" && $spotInfo[0]->has_refrigerator =="1") echo "selected='selected'";?> 
							>
							<?php if ($spotID != null && $spotInfo[0]->has_refrigerator !="" && $spotInfo[0]->has_refrigerator =="1") echo "It has refrigerator to store products! :) "; 
							else echo "yes";?> </option>
						<option value="no" style="color:red"
							<?php if ($spotID != null && $spotInfo[0]->has_refrigerator !="" && $spotInfo[0]->has_refrigerator =="0") echo "selected='selected'";?> 
							>
							<?php if ($spotID != null && $spotInfo[0]->has_refrigerator !="" && $spotInfo[0]->has_refrigerator =="0") echo "It does not have refrigerator to store products! :( "; 
							else echo "no";?></option>
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
					if ($spotID == null)
						echo "
							value='Add Spot'
							onclick='CsaWpPluginNewSpotFieldsValidation(this, null, \"". admin_url("/admin.php?page=csa_spots_management") ."\")'
						";
					else 
						echo "
							value='Update Spot'
							onclick='CsaWpPluginNewSpotFieldsValidation(this, $spotID, \"". admin_url("/admin.php?page=csa_spots_management") ."\")'
						";
				?>
				class="button button-primary"
				title="Please fill in required fields, i.e. those marked with (*)"
			/>
			<input 
				type="button"
				class="button button-secondary"
				<?php 
				if ($spotID == null) 
					echo "
					value='Reset Info'
					onclick='CsaWpPluginResetSpotForm();'";
				else echo "
					value='Cancel'
					onclick='window.location.replace(\"". admin_url('/admin.php?page=csa_spots_management')."\")'
					'";
				?>
			/>
		</form>
		<br/><br/>
	</div>
	
<?php
}

function CsaWpPluginSpotFormInputValue ($spotID, $value, $info, $placeholder) {
	if ($spotID != null && $value != "" && $value != null) echo "value='$info$value'";
	echo "placeholder='$placeholder'";
}

add_action( 'wp_ajax_csa-wp-plugin-check_spotName_validity', 'CsaWpPluginCkeckSpotNameValidity' );

function CsaWpPluginCkeckSpotNameValidity() {

	if( isset($_POST['spotName']) && isset($_POST['spotID']) && isset($_POST['numEntriesExist'])) {
		$newSpotName = clean_input($_POST['spotName']);
		$spotID = clean_input($_POST['spotID']);
		if ($spotID) $spotID = intval($spotID);
		$numEntriesExist = intval(clean_input($_POST['numEntriesExist']));
		
		global $wpdb;
		$results = $wpdb->get_results( $wpdb->prepare("SELECT id FROM ".csaSpots." WHERE spotName=%s", $newSpotName));
		if ($wpdb->num_rows > $numEntriesExist && $results[0]->id != $spotID)
			echo 'invalid spot name';
		else if  ($wpdb->num_rows > $numEntriesExist && $results[0]->id == $spotID)
			echo 'valid updating spot name';
		else echo 'valid spot name';
	} else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_csa-wp-plugin-spot_request', 'CsaWpPluginAddOrUpdateSpot' );

function CsaWpPluginAddOrUpdateSpot() {

	if( isset($_POST['data']) && isset($_POST['spotID'])) {

		$dataReceived = json_decode(stripslashes($_POST['data']),true);
		
		$dataVals = array(
					'spotName' 						=> $dataReceived[0]['value'],
					'streetName' 					=> $dataReceived[1]['value'],
					'streetNumber' 					=> $dataReceived[2]['value'],
					'city' 							=> $dataReceived[3]['value'],
					'region' 						=> $dataReceived[4]['value'],
					'description' 					=> $dataReceived[5]['value'],
					'isDeliverySpot'				=> $dataReceived[6]['value'] == "yes"?1:0
				);
		$dataTypes = array ("%s", "%s", "%s", "%s", "%s", "%s", "%s");
		
		$ownerID;
		if ($dataVals['isDeliverySpot']) {
			$ownerID = $dataReceived[7]['value'];
			$dataVals += array(
						'default_order_deadline_day' 	=> $dataReceived[8]['value'],
						'default_order_deadline_time' 	=> explode(' ', $dataReceived[9]['value'])[2],
						'default_delivery_day' 			=> $dataReceived[10]['value'],
						'default_delivery_strart_time' 	=> explode(' ', $dataReceived[11]['value'])[1],
						'default_delivery_end_time'		=> explode(' ', $dataReceived[12]['value'])[1],
						'close_order'					=> $dataReceived[13]['value'],
						'parking'						=> $dataReceived[14]['value'],
						'has_refrigerator'				=> $dataReceived[15]['value'] == "yes"?1:0
						
					);
			$dataTypes += array ("%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s");
		}
		
		global $wpdb;
		
		
		$spotID = intval(clean_input($_POST['spotID']));
		
		if ($spotID != null) { //update spot (query)
			$spotID = intval($spotID);
			if ($wpdb->update(
					csaSpots,
					$dataVals,
					array('id' => $spotID), 
					$dataTypes
				) === FALSE) echo 'error, sql request failed.';												
			else { 		
				if ($dataVals['isDeliverySpot']) {
					if($wpdb->update(
						csaSpotsToUsers,
						array(
							'user_id' 	=> $ownerID
						), 
						array(
							'spot_id'	=> $spotID,
							'type'		=> 'delivery'
						),
						array ("%d")
					) === FALSE)
						echo 'error, sql request failed.';												
					else echo 'Success, spot and spot to user relationship are updated.';
				}
				else echo 'Success, spot is updated.';
			}
		}

		
		else { 	//insert spot (query)
			if(	$wpdb->insert(
				csaSpots, 
				$dataVals, 
				$dataTypes
			) === FALSE) echo 'error, sql request failed.';
			else { 
				if ($dataVals['isDeliverySpot']) {
					if($wpdb->insert(
						csaSpotsToUsers,
						array(
							'spot_id' 	=> $wpdb->insert_id,
							'user_id' 	=> $ownerID,
							'type'		=> 'delivery'
						), 
						array ("%d", "%d", "%s")
					) === FALSE)
						echo 'error, sql request failed.';												
					else echo 'Success, spot and spot to user relationship are added.';
				}
				else echo 'Success, spot is added.';
			}
		}
	}
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

function CsaWpPluginShowSpots() {
	wp_enqueue_script('CsaWpPluginScripts');
	wp_enqueue_script('CsaWpPluginSpotsScripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); 
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

?>

	<br/>
	<div id="csa-wp-plugin-showSpotsList_header"><span style="cursor:pointer" id="csa-wp-plugin-showSpotsList_formHeader_text" onclick="CsaWpPluginToggleForm('showSpotsList','Spots List', '')"><font size='4'>Spots List (hide)</font></span></div>
	<div id="csa-wp-plugin-showSpotsList_div" style="display:block">		

		<span class='csa-wp-plugin-tip_spots' title='
			If you want to update a field, click on it, write the new value, and then press ENTER.
			| If you want to delete a spot, press the icon at the end of the corresponding row.'>
		<p style="color:green;font-style:italic; font-size:13px">
			By pointing here you can read additional information.</p></span>
			
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
			$pluginsDir = plugins_url();
			
			$spots = $wpdb->get_results("SELECT * FROM ". csaSpots);
			foreach($spots as $row) 
			{
				$spotID = $row->id;
				echo "
					<tr valign='top' id='csa-wp-plugin-showSpotsSpotID_$spotID'>
					<td class='editable'>$row->spotName </td>
					<td class='editable'>$row->streetName </td>
					<td class='editable'>$row->streetNumber</td>
					<td class='editable'>$row->city</td>
					<td class='editable'>$row->region</td>
					<td class='editable'>$row->description</td>
					<td class='editable_select'>".($row->isDeliverySpot==1?'yes':($row->isDeliverySpot==NULL?'unknown':'no'))."</td>
					<td style='text-align:center'> 
						<img 
							width='20' height='20'  
							class='delete no-underline' 
							src='$pluginsDir/csa-wp-plugin/icons/edit3.png' 
							style='cursor:pointer;padding-left:10px;' 
							onclick='CsaWpPluginEditSpot(this, \"". admin_url('/admin.php?page=csa_spots_management')."\")' 
							title='click to edit this spot'/></td>
					<td style='text-align:center'> 
						<img 
							class='delete no-underline' 
							src='$pluginsDir/csa-wp-plugin/icons/delete.png' 
							style='cursor:pointer;padding-left:10px;' 
							onmouseover='CsaWpPluginHoverIcon(this, \"delete\", \"$pluginsDir\")' 
							onmouseout='CsaWpPluginUnHoverIcon(this, \"delete\", \"$pluginsDir\")' 
							onclick='CsaWpPluginRequestDeleteSpot(this)' title='click to delete this spot'/></td></tr>";
			}
			?>
		</tbody> </table>
	</div>	
<?php
}

add_action( 'wp_ajax_csa-wp-plugin-update_spot', 'CsaWpPluginUpdatePost' );

function CsaWpPluginUpdatePost() {
	if(isset($_POST['value']) && isset($_POST['column']) && isset($_POST['spotID'])) {
		//$old_value = clean_input($_POST['old_val']);
		$new_value = clean_input($_POST['value']);
		$columnNum = intval(clean_input($_POST['column']))+1; //not valid for getting the right column, when html table structure differs from the relative db table
		if ($columnNum == 7) $new_value = ($new_value == "yes"?1:0);

		$spotID = intval(clean_input($_POST['spotID']));
		if(!empty($columnNum) && !empty($spotID)) {
			// Updating the information 
			global $wpdb;
			//get column names and assign them to an array
			$columns = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".csaSpots."' ORDER BY ORDINAL_POSITION", ARRAY_N);
			//update the database, using the relative column name
			$columnName = $columns[$columnNum][0];

			if(	$wpdb->update(
				csaSpots,
				array($columnName => $new_value), 
				array('id' => $spotID )
			) === FALSE) 
				echo '<span style="color:red">Κάτι δε δούλεψε σωστά. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή.</span>';												
			else echo 'success,'.$new_value;
		} 
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_csa-wp-plugin-delete_spot', 'CsaWpPluginDeleteSpot' );

function CsaWpPluginDeleteSpot() {
	if(isset($_POST['spotID'])) {
		$spotID = intval(clean_input($_POST['spotID']));
		if(!empty($spotID)) {
			// Updating the information 
			global $wpdb;

			if(	$wpdb->delete(
				csaSpots,
				array('id' => $spotID ),
				array ('%d')
			) === FALSE) 
				echo '<span style="color:red">Κάτι δε δούλεψε σωστά. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή.</span>';												
			else echo 'success';
		} 
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

?>