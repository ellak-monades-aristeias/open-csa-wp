<?php

function CsaWpPluginNewDeliveryForm($spotID, $orderDeadlineDate, $customValues, $deliveryID, $display) { 
	
	wp_enqueue_script( 'CsaWpPluginScripts' );
	wp_enqueue_script( 'CsaWpPluginDeliveriesScripts' );
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

	global $days,$wpdb;
	
	$inCharge = null;
	$customBool = false;
	if ($spotID != null) {
		$spotInfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".csaSpots." WHERE id=%d", $spotID))[0];
	
		if ($deliveryID != null) {
			$deliveryInfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". csaDeliveries ." WHERE id=%d", $deliveryID))[0];

			$inCharge = $deliveryInfo->userInCharge;
			
			$orderDeadlineDay = (date("w", strtotime($orderDeadlineDate)) - 1) % 7;
			if ($orderDeadlineDay == -1) $orderDeadlineDay = 6;									// So that the 'if' below (for customBool) is executed correctly
			$orderDeadlineTime = CsaWpPluginRemoveSeconds($deliveryInfo->order_deadline_time);
			$deliveryDay = (date("w", strtotime($deliveryInfo->delivery_day)) - 1) % 7;
			if ($deliveryDay == -1) $deliveryDay = 6;											// So that the 'if' below (for customBool) is executed correctly
			$deliveryStartTime = CsaWpPluginRemoveSeconds($deliveryInfo->delivery_start_time);
			$deliveryEndTime = CsaWpPluginRemoveSeconds($deliveryInfo->delivery_end_time);
			
			if (
				$orderDeadlineDay 	!= $spotInfo->default_order_deadline_day ||
				$orderDeadlineTime 	!= CsaWpPluginRemoveSeconds($spotInfo->default_order_deadline_time) ||
				$deliveryDay 		!= $spotInfo->default_delivery_day ||
				$deliveryStartTime 	!= CsaWpPluginRemoveSeconds($spotInfo->default_delivery_start_time) ||
				$deliveryEndTime 	!= CsaWpPluginRemoveSeconds($spotInfo->default_delivery_end_time)
			) $customBool = true;
		}
		else {
			$orderDeadlineDay = $spotInfo->default_order_deadline_day;
			$orderDeadlineTime = CsaWpPluginRemoveSeconds($spotInfo->default_order_deadline_time);
			$deliveryDay = $spotInfo->default_delivery_day;
			$deliveryStartTime = CsaWpPluginRemoveSeconds($spotInfo->default_delivery_start_time);
			$deliveryEndTime = CsaWpPluginRemoveSeconds($spotInfo->default_delivery_end_time);
		}
		
		if (count($customValues) > 0) {
			$customBool = true;
			
			if (isset($customValues["order_deadline_day"]))
				$orderDeadlineDay = $customValues["order_deadline_day"];
			if (isset($customValues["order_deadline_time"]))
				$orderDeadlineTime = $customValues["order_deadline_time"];
			if (isset($customValues["delivery_day"]))
				$deliveryDay = $customValues["delivery_day"];
			if (isset($customValues["delivery_start_time"]))
				$deliveryStartTime = $customValues["delivery_start_time"];
			if (isset($customValues["delivery_end_time"]))
				$deliveryEndTime = $customValues["delivery_end_time"];			
		}
		else if ($deliveryID!= null && $orderDeadlineDate == null) 
				$orderDeadlineDate = $deliveryInfo->order_deadline_date;

	}
		
	if ($orderDeadlineDate != null) {
		$orderDeadlineDate = explode(";", $orderDeadlineDate)[0];
	}
?>

	<br/>

	<div id="csa-wp-plugin-newDelivery_formHeader">
		<span 
			id="csa-wp-plugin-newDelivery_formHeader_text" 
			<?php 
				if ($spotID == null) {
					echo 'style="cursor:pointer"';
					echo 'onclick="CsaWpPluginToggleForm(\'newDelivery\',\'Initiate New Delivery\', \' form\')"';
				}
			?>
		><font size='4'>
		<?php 
			if ($spotID == null) {
				if ($display == false) echo 'Initiate New Delivery (show form)';
				else echo 'Initiate New Delivery (hide form)';
			}
			else if ($deliveryID != null)
				echo 'Edit Delivery #'. $deliveryID;
			else echo 'Initiating new delivery for ';
		?>
		</font>
		</span>
	</div>
	<div id="csa-wp-plugin-newDelivery_div" 
		<?php if ($display == false) echo 'style="display:none"' ?>	
	>
		<form method="POST" id='csa-wp-plugin-initiateNewDelivery_form_id'>
			<table class="form-table">
				<tr valign="top">
					<td>
					<select 
						name="csa-wp-plugin-newDelivery_spotDetails_spotID_input" 
						id="csa-wp-plugin-newDelivery_spotDetails_spotID_input_id"
						<?php 
							if ($spotID == null) echo "style='color:#999'";
							echo "onchange='window.location.replace(\"". admin_url('/admin.php?page=csa_deliveries_management')."&id=\" + this.value)'";
						?>
					>
					<option 
						value="" 
						<?php if ($spotID == null)  echo "selected='selected' "; ?>
						disabled='disabled'
						id = "csa-wp-plugin-newDelivery_spotDetails_spotID_input_disabled_id"
					>Select Spot *</option>
 					<?php echo CsaWpPluginSelectOptionsFromDB(
									array("spotName"), 
									"id", 
									csaSpots, 
									($spotID != null)?$spotID:null,
									"Spot "
								); ?>
                  	</select>
					<span id="csa-wp-plugin-newDelivery_spotDetails_spotID_input_span_id">
					<?php 
						if ($spotID!=null) {
							if ($orderDeadlineDate!=null)
								echo "&nbsp;&nbsp;where";
							else if ($customBool === false)
								echo "
									<i style='color:gray' class='csa-wp-plugin-tip_deliveries' title='
										Below, you can customize the deadline and delivery dates (and times) for this delivery.
									'> 
										&nbsp; &nbsp; with default values... (point here) 
									</i>";
							else echo "<i style='color:gray'> &nbsp; &nbsp; with custom values... </i>";
						}
					?>
					</span>
				</tr>
				<tr
					<?php if ($spotID == null) echo 'style="display:none"'?>
				>
					<td>
					<select 
						<?php if ($orderDeadlineDate == null) echo 'style="color:#999"'?>
						id="csa-wp-plugin-newDelivery_delivery_deadline_date_input_id"
						name="csa-wp-plugin-newDelivery_delivery_deadline_date_input"
						onchange = '
							//this.style.color = "black";
							//document.getElementById("csa-wp-plugin-newDelivery_spotDetails_spotID_input_span_id").innerHTML = "&nbsp;&nbsp; where ";
							//document.getElementById("csa-wp-plugin-newDelivery_delivery_deadline_date_input_span_id").style.display = "inline";
							//if (this.options[this.selectedIndex].text.split(" ")[0] != "Order")
							//	this.options[this.selectedIndex].text = "Order deadline is on " + this.options[this.selectedIndex].text;
														
							document.getElementById("csa-wp-plugin-newDelivery_orderDeadlineDate_choice_id").value = this.options[this.selectedIndex].value;
							CsaWpPluginNewDeliveryFormatCustomValues(
								document.getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id")
							)
						'

					>
					<option disabled="disabled" 
						value=""
						id = "csa-wp-plugin-newDelivery_delivery_deadline_date_disabled_id";
					<?php if ($orderDeadlineDate == null) echo 'selected="selected"'?>> Choose a deadline date * </option>
					<?php 
						$deadline_day = $days[$orderDeadlineDay];
						for ($i=0; $i<5; $i++) {
							$nextDeadline_date = date(csa_wp_plugin_date_format, strtotime("Next ". $deadline_day . "+$i week"));
							$nextDeadline_date_readable = $deadline_day . ", ". date(csa_wp_plugin_date_format_readable, strtotime($nextDeadline_date)) . ", up to ". $orderDeadlineTime;
							if ($orderDeadlineDate == null || $orderDeadlineDate != $nextDeadline_date)
								echo "<option 
										style='color:black' 
										value='$nextDeadline_date;$orderDeadlineTime'
									>$nextDeadline_date_readable</option>";
							else if ($orderDeadlineDate == $nextDeadline_date)
								echo "<option 
										style='color:black' 
										selected = 'selected' 
										value='$nextDeadline_date;$orderDeadlineTime'
									>Order deadline is on $nextDeadline_date_readable</option>";
						}
					?>
					</select>
					<span id="csa-wp-plugin-newDelivery_delivery_deadline_date_input_span_id" 
						<?php if ($orderDeadlineDate == null) echo 'style="display:none"'; ?>
					> &nbsp;&nbsp; and
					</span></td>
				</tr>
				<tr valign="top" <?php if ($orderDeadlineDate==null) echo 'style="display:none"';?>><td><span> 
				<?php
					$deliveryDate = date(csa_wp_plugin_date_format_readable, strtotime("Next ". $days[$deliveryDay], strtotime($orderDeadlineDate)));
					$valueOfReadOnlyInput = "Delivery is on ". $days[$deliveryDay] .", ". $deliveryDate .", from $deliveryStartTime to $deliveryEndTime";
					$valueOfReadOnlyInput_len = strlen($valueOfReadOnlyInput);
					$sizeOfReadOnlyInput = (($valueOfReadOnlyInput_len + 1) ).'"px\"';
					echo " 	<input 
								name = 'csa-wp-plugin-newDelivery_DeliveryDaTeDetails_input'
								type = 'text'
								readonly = 'readonly'
								value='$valueOfReadOnlyInput'
								style='border:none; background-color:white;'
								size='$sizeOfReadOnlyInput'
							/>";
				?>
				</span></td></tr>
				
				<tr valign="top" <?php if ($orderDeadlineDate==null) echo 'style="display:none"';?>><td>
					<select 
						name="csa-wp-plugin-newDelivery_inCharge_input"
						id="csa-wp-plugin-newDelivery_inCharge_input_id"		
						onchange = '
							this.style.color="black"
							if (this.options[this.selectedIndex].text.split(" ")[0] != "Responsible for this delivery is")
								this.options[this.selectedIndex].text = "Responsible for this delivery is " + this.options[this.selectedIndex].text;
						'
						<?php if ($deliveryID == null) echo "style='color:#999'"?>
					>
						<option 
							value="" 
							<?php if ($deliveryID == null) echo "selected='selected'"?>
							id = "csa-wp-plugin-newDelivery_inCharge_input_disabled_id"
							disabled='disabled'
						>Do you know who is going to be in charge?</option>
						<?php echo CsaWpPluginSelectUsersOfType("consumer", $inCharge, "Responsible for this delivery is "); ?>
					</select>
				</td></tr>
				
				<tr valign="top"
					<?php if ($deliveryID == null) echo "style='display:none'"; ?>
				><td>
					<select 
						name="csa-wp-plugin-delivery_abilityToSubmitOrder_input" 
						id="csa-wp-plugin-delivery_abilityToSubmitOrder_input_id"
						<?php 
							if ($deliveryID != null && $deliveryInfo->areOrdersOpen == 1) echo "style='color:green'";
							else echo "style='color:brown'";
						?>
						onchange='
							if (this.options[this.selectedIndex].value == "yes") {
								this.style.color = "green";
								this.options[this.selectedIndex].text = "Currently, new orders can be submitted"
							}
							else {
								this.style.color = "brown";
								this.options[this.selectedIndex].text = "Currently, new orders can not be submitted"
							}
							'
					>
					<?php 
						if ($deliveryID != null) {
							echo '
								<option value="yes" style="color:green". '. ($deliveryInfo->areOrdersOpen == 1?"selected='selected'":"").'> Currently, new orders can be submitted </option>
								<option value="no" style="color:brown"'. ($deliveryInfo->areOrdersOpen == 0?"selected='selected'":"").'> Currently, new orders can not be submitted </option>
							';
						}
					?>					
					</select>
					</td>
				</tr>

				
				
				<tr <?php if ($spotID == null) echo 'style="display:none"'?>>
					<td>
					<input 
						type="submit" 
						class="button button-primary"
						id="csa-wp-plugin-initiateNewDelivery_button_id"
						
						<?php 
							if ($deliveryID == null)
								echo "
									value='Initiate Delivery'
									onclick='CsaWpPluginRequestInitiateNewOrUpdateDelivery(this, null, \"". admin_url("/admin.php?page=csa_deliveries_management") ."\");'
								";
							else
								echo "
									value='Update Delivery'
									onclick='CsaWpPluginRequestInitiateNewOrUpdateDelivery(this, $deliveryID, \"". admin_url("/admin.php?page=csa_deliveries_management") ."\");'
								";

						?>
					/>
					
					<input 
						type="button"
						class="button button-secondary"
						value="Cancel"
						<?php echo "onclick='window.location.replace(\"". admin_url('/admin.php?page=csa_deliveries_management')."\")'";
						?>
					/>
					</td>
				</tr>
			</table>
		</form>
		
		
		<form 
			id="csa-wp-plugin-initiateNewDelivery_spotDetails_form"
			method="post"
			<?php if ($spotID == null) echo 'style="display:none"'?>
			action="<?php echo admin_url('/admin.php?page=csa_deliveries_management');?>"
		>
			<br/>
			<div id="csa-wp-plugin-newDelivery_spotDetailsDetails_formHeader">
				<span 
					id="csa-wp-plugin-newDelivery_spotDetailsDetails_formHeader_text" 
					style="cursor:pointer"
					<?php
						if ($customBool === true) 
							echo "onclick=\"CsaWpPluginToggleForm('newDelivery_spotDetailsDetails','Custom values ', ' form', 3, '&nbsp;&nbsp;&nbsp;')\"";
						else echo "onclick=\"CsaWpPluginToggleForm('newDelivery_spotDetailsDetails','Customize default values ', ' form', 3, '&nbsp;&nbsp;&nbsp;')\"";
					?>
				><font size='3'>
				<?php
					$textHideShow = "hide";
					if ($orderDeadlineDate != null || $deliveryID != null) $textHideShow = "show";
				
					if ($customBool === true)
						echo "&nbsp;&nbsp;&nbsp;Custom values ($textHideShow form)";
					else echo "&nbsp;&nbsp;&nbsp;Customize default values ($textHideShow form)";
				?>
					
				</font>
				</span>
			</div>
			<div id = "csa-wp-plugin-newDelivery_spotDetailsDetails_div"
				<?php if($orderDeadlineDate != null || $deliveryID != null) echo "style='display:none'"?>
			>
				<table class="form-table">		
				<tr hidden="hidden">
					<td>
					<input 	name='csa-wp-plugin-newDelivery_spotID_choice' 
							id='csa-wp-plugin-newDelivery_spotID_choice_id'
							value="<?php if ($spotID!=null) echo $spotID?>">
					</td>
				<tr/>
				<tr hidden="hidden">
					<td>
					<input 	name='csa-wp-plugin-newDelivery_deliveryID_choice' 
							value="<?php if ($deliveryID!=null) echo $deliveryID?>">
					</td>
				<tr/>
				<tr hidden="hidden">
					<td>
					<input 	name='csa-wp-plugin-newDelivery_orderDeadlineDate_choice' 
							id='csa-wp-plugin-newDelivery_orderDeadlineDate_choice_id'
							value="">
					</td>
				<tr/>
				
				<?php
					if ($deliveryID != null)
						echo"
							<tr hidden = 'hidden'> 
								<td> <input name='csa-wp-plugin-newDelivery_deliveryID' value=\"$deliveryID\"/> </td>
							</tr>
						";
				?>
				<tr valign="top"><td>
					<select 
						name="csa-wp-plugin-newDelivery_order_deadline_day_input"
						id='csa-wp-plugin-newDelivery_order_deadline_day_input_id'
						onfocus=' getElementById("csa-wp-plugin-newDelivery_spotDetails_orderDeadline_span").style.display = "none";'
						onchange='
							if (this.options[this.selectedIndex].text.split(" ")[0] != "order")
								this.options[this.selectedIndex].text = "order deadline is on " + this.options[this.selectedIndex].text;
							getElementById("csa-wp-plugin-newDelivery_order_deadline_time_input_id").style.display = "inline"
							'
					>
					<option value="" selected='selected' disabled="disabled" id="csa-wp-plugin-newDelivery_order_deadline_day_disabled_id">order deadline day ... *</option>
					<?php 
					for ($i=0; $i<7; $i++) {
						if ($orderDeadlineDay == $i)
							echo "<option value='$i' selected='selected'> order deadline is on $days[$i] </option>";
						else echo "<option value='$i'>".$days[$i]."</option>";
					}
					?>
					</select> 
					<input 
						<?php 
							if ($spotID != null) echo "value='up to $orderDeadlineTime'";
						?>
						placeholder="up to... *"
						id="csa-wp-plugin-newDelivery_order_deadline_time_input_id"
						class="textbox-n" type="text" size="10" name="csa-wp-plugin-newDelivery_order_deadline_time_input"
						onfocus='
							getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id").disabled = true;
							<?php
								if ($customBool === true && $deliveryID == null) 
									echo 'getElementById("csa-wp-plugin-newDeliveryCustomValues_reset_button_id").disabled = true;';
							?>
							if (this.value != "") this.value=this.value.split(" ")[2];
							else getElementById("csa-wp-plugin-newDelivery_spotDetails_orderDeadline_span").style.display = "none";
							this.type="time";'
						onchange = '//CsaWpPluginToggleDeadlinePeriods(false);'
						onblur='
							getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id").disabled = false;
							<?php
								if ($customBool === true && $deliveryID == null) 
									echo 'getElementById("csa-wp-plugin-newDeliveryCustomValues_reset_button_id").disabled = false;';
							?>
							this.type="text";
							if (this.value != "") {
								this.style.color="black";
								this.value = "up to " + this.value;
							}'
						>
					<span id="csa-wp-plugin-newDelivery_spotDetails_orderDeadline_span" style="display:none"></span>
				</td></tr>
								
				<tr valign="top"><td>
					<select 
						name="csa-wp-plugin-newDelivery_delivery_day_input" 
						id="csa-wp-plugin-newDelivery_delivery_day_input_id"
						onfocus='getElementById("csa-wp-plugin-newDelivery_spotDetails_invalidDeliveryTime_span").innerHTML = "";'
						onchange='
							//CsaWpPluginToggleDeadlinePeriods(false);
							if (this.options[this.selectedIndex].text.split(" ")[0] != "Delivery")
								this.options[this.selectedIndex].text = "Delivery day is " + this.options[this.selectedIndex].text;
							getElementById("csa-wp-plugin-newDelivery_spotDetails_delivery_start_time_input_id").style.display = "inline"'
					>
					<option value="" disabled="disabled" 
						id="csa-wp-plugin-newDelivery_delivery_day_disabled_id">Delivery day ... *</option>
					<?php 
					for ($i=0; $i<7; $i++) {
						if ($deliveryDay == $i)
							echo "<option value='$i' selected='selected'> Delivery day is $days[$i] </option>";
						else echo "<option value='$i'>".$days[$i]."</option>";
					}
					?>
					</select> 
					<input id="csa-wp-plugin-newDelivery_spotDetails_delivery_start_time_input_id"
						<?php 
							if ($spotID != null) echo "value='from $deliveryStartTime'";
						?>
						placeholder="from... *"
						class="textbox-n" type="text" size="10" 
						name="csa-wp-plugin-newDelivery_delivery_start_time_input"

						onfocus='
							getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id").disabled = true;
							<?php
								if ($customBool === true && $deliveryID == null) 
									echo 'getElementById("csa-wp-plugin-newDeliveryCustomValues_reset_button_id").disabled = true;';
							?>
							if (this.value != "") this.value=this.value.split(" ")[1];
							else getElementById("csa-wp-plugin-newDelivery_spotDetails_invalidDeliveryTime_span").style.display = "none";
							this.type="time";'
						onblur='
							getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id").disabled = false;
							<?php
								if ($customBool === true && $deliveryID == null) 
									echo 'getElementById("csa-wp-plugin-newDeliveryCustomValues_reset_button_id").disabled = false;';
							?>
							this.type="text";
							if (this.value == "") {
								getElementById("csa-wp-plugin-newDelivery_spotDetails_delivery_end_time_input_id").style.display = "none";
								getElementById("csa-wp-plugin-newDelivery_spotDetails_delivery_end_time_input_id").value = "";
							}
							else {
								this.style.color="black";
								this.value = "from " + this.value;
								getElementById("csa-wp-plugin-newDelivery_spotDetails_delivery_end_time_input_id").style.display = "inline";
								CsaWpPluginValidateDeliveryTimePeriod("newDelivery_spotDetails");
							}'
						>
					<input id="csa-wp-plugin-newDelivery_spotDetails_delivery_end_time_input_id"
						<?php 
							if ($spotID != null) echo "value='to $deliveryEndTime'";
						?>
						placeholder="to... *"					
						class="textbox-n" type="text" size="10" 
						name="csa-wp-plugin-newDelivery_delivery_end_time_input"
						
						onfocus='
							getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id").disabled = true;
							<?php
								if ($customBool === true && $deliveryID == null) 
									echo 'getElementById("csa-wp-plugin-newDeliveryCustomValues_reset_button_id").disabled = true;';
							?>
							if (this.value != "") this.value=this.value.split(" ")[1];
							getElementById("csa-wp-plugin-newDelivery_spotDetails_invalidDeliveryTime_span").style.display = "none";
							this.type="time";'
						onblur='
							getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id").disabled = false;
							<?php
								if ($customBool === true && $deliveryID == null) 
									echo 'getElementById("csa-wp-plugin-newDeliveryCustomValues_reset_button_id").disabled = false;';
							?>
							this.type="text";
							if (this.value != "") {
								this.style.color="black";
								this.value = "to " + this.value;
								CsaWpPluginValidateDeliveryTimePeriod("newDelivery_spotDetails");
							}
						'
						>
					<span id="csa-wp-plugin-newDelivery_spotDetails_invalidDeliveryTime_span"> </span>
					</td> 
				</tr>					
				<tr valign="top"><td>
					<input 
						type="submit" 
						class="button button-secondary"
						id="csa-wp-plugin-newDeliveryCustomValues_button_id"
						value="Use Custom Values"
						onclick="CsaWpPluginNewDeliveryFormatCustomValues(this);"
					/>
					<?php
						if ($customBool === true && $deliveryID == null)
							echo "
								<input 
									type='submit'
									class='button button-secondary'
									id='csa-wp-plugin-newDeliveryCustomValues_reset_button_id'
									value='Reset to default values'
									onclick='
										window.location.replace(\"". admin_url('/admin.php?page=csa_deliveries_management')."&id=". $spotID ."\");
										event.preventDefault();
									'
								/>
							";					
					?>
				</td>
				
				</tr>
				</table>
			</div>
		</form>
	</div>
	
<?php

}

function CsaWpPluginReturnCustomValuesForNewDelivery ($spotID) {

	$customValues = array();
	
	global $wpdb;
	$spotInfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".csaSpots." WHERE id=%d", $spotID))[0];
	
	if ($_POST["csa-wp-plugin-newDelivery_order_deadline_day_input"] != $spotInfo->default_order_deadline_day )
		$customValues["order_deadline_day"] = $_POST["csa-wp-plugin-newDelivery_order_deadline_day_input"];

	if ($_POST["csa-wp-plugin-newDelivery_order_deadline_time_input"] != $spotInfo->default_order_deadline_time )
		$customValues["order_deadline_time"] = CsaWpPluginRemoveSeconds($_POST["csa-wp-plugin-newDelivery_order_deadline_time_input"]);

	if ($_POST["csa-wp-plugin-newDelivery_delivery_day_input"] != $spotInfo->default_delivery_day )
		$customValues["delivery_day"] = $_POST["csa-wp-plugin-newDelivery_delivery_day_input"];

	if ($_POST["csa-wp-plugin-newDelivery_delivery_start_time_input"] != $spotInfo->default_delivery_start_time )
		$customValues["delivery_start_time"] = CsaWpPluginRemoveSeconds($_POST["csa-wp-plugin-newDelivery_delivery_start_time_input"]);

	if ($_POST["csa-wp-plugin-newDelivery_delivery_end_time_input"] != $spotInfo->default_delivery_end_time )
		$customValues["delivery_end_time"] = CsaWpPluginRemoveSeconds($_POST["csa-wp-plugin-newDelivery_delivery_end_time_input"]);
		
	return $customValues;
}


add_action( 'wp_ajax_csa-wp-plugin-initiate_or_update_new_delivery_request', 'CsaWpPluginInitiateOrUpdateNewDelivery' );

function CsaWpPluginInitiateOrUpdateNewDelivery() {

	if( isset($_POST['data']) && isset($_POST['deliveryID'])) {

		$dataReceived = json_decode(stripslashes($_POST['data']),true);
		
		$parts = explode(";", $dataReceived[1]['value']);
		$orderDeadlineDate = $parts[0];
		$orderDeadlineTime = $parts[1];
		
		$parts = explode(", ", $dataReceived[2]['value']);
		
		$deliveryDate = date(csa_wp_plugin_date_format, strtotime($parts[1]));
		
		$parts = explode(" ", $parts[2]);
		$deliveryStartTime = $parts[1];
		$deliveryEndTime = $parts[3];

		$userInCharge = $dataReceived[3]['value'];
		
		$dataVals = array(
					'spot_id' 				=> intval(clean_input($dataReceived[0]['value'])),
					'order_deadline_date' 	=> $orderDeadlineDate,
					'order_deadline_time' 	=> $orderDeadlineTime,
					'delivery_day'			=> $deliveryDate,
					'delivery_start_time'	=> $deliveryStartTime,
					'delivery_end_time'	 	=> $deliveryEndTime,
					'areOrdersOpen' 		=> $dataReceived[4]['value'] == "yes"?1:0
				);

		$dataTypes = array ("%d", "%s", "%s", "%s", "%s", "%s");
				
		
		if ($userInCharge!=null) {
			$dataVals['userInCharge'] = $userInCharge;
			$dataTypes[6] = "%d";
		}

		
		global $wpdb;
	
		$deliveryID = intval(clean_input($_POST['deliveryID']));
	
		if ($deliveryID != null) {
			$deliveryID = intval($deliveryID);
			
			//update delivery (query)
			if(	$wpdb->update(
				csaDeliveries, 
				$dataVals, 
				array('id' => $deliveryID), 
				$dataTypes
			) === FALSE) echo 'error, sql request failed.';
			else echo 'Success, delivery is updated.';
		
		}
		else { 
			//insert delivery (query)
			if(	$wpdb->insert(
				csaDeliveries, 
				$dataVals, 
				$dataTypes
			) === FALSE) echo 'error, sql request failed.';
			else echo 'Success, delivery is initiated.';
		}
	}
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}


function CsaWpPluginShowDeliveries($display) {
	wp_enqueue_script('CsaWpPluginScripts');
	wp_enqueue_script('CsaWpPluginDeliveriesScripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); 
	
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

?>
		
	<br />
	<div id="csa-wp-plugin-showDeliveriesList_header">
		<span 
			style="cursor:pointer" 
			id="csa-wp-plugin-showDeliveriesList_formHeader_text" 
			onclick="CsaWpPluginToggleForm('showDeliveriesList','Deliveries List', '')">
			<font size='4'>
			<?php 
				if ($display == false) echo 'Deliveries List (show)';
				else echo 'Deliveries List (hide)'
			?>
			</font>
		</span>
	</div>
	<div id="csa-wp-plugin-showDeliveriesList_div" 
		<?php if ($display == false) echo 'style="display:none"' ?>	
	>
		<span class='csa-wp-plugin-tip_deliveries' title='
			To change the ability of new order sumbission, you can press the "envelope" icon.
			| If you want to edit delivery details, press the "pen" icon.
			| If you want to delete some delivery, press the "x" icon.
			'>
		<p style="color:green;font-style:italic; font-size:13px">
			By pointing here you can read additional information.</p></span>


		<table 
			class='table-bordered' 
			id="csa-wp-plugin-showDeliveriesList_table" 
			style='border-spacing:1em' 
		> 
		<thead class='tableHeader'>
			<tr>
				<th>Spot</th>
				<th>Order Deadline Date</th>
				<th>Order Deadline Time</th>
				<th>Delivery Date</th>
				<th>Delivery Start Time</th>
				<th>Delivery End Time</th>
				<th>User In Charge</th>
				<th>New Orders Can be Submitted?</th>
				<th/>
				<th/>
				<th/>
			</tr>
		</thead> 
		<tbody> <?php
			global $wpdb;
			$pluginsDir = plugins_url();

			$deliveries = $wpdb->get_results("SELECT * FROM ". csaDeliveries);
			foreach($deliveries as $delivery) 
			{
				$deliveryID = $delivery->id;
				$spotName = $wpdb->get_var($wpdb->prepare("SELECT spotName FROM ". csaSpots ." WHERE id=%d", $delivery->spot_id));
				 
				$userInCharge_login = "";
				if ($delivery->userInCharge != null )
					$userInCharge_login = get_user_by('id', $delivery->userInCharge)->user_login;
				
				$pastDelivery = false;
				$currentDateTime = current_time('mysql');
				if (strtotime($delivery->order_deadline_date." ". $delivery->order_deadline_time) < strtotime($currentDateTime))
					$pastDelivery = true;
				
				echo "
					<tr 
						valign='top' 
						id='csa-wp-plugin-showDeliveriesDeliveryID_$deliveryID'  
						class='csa-wp-plugin-showDeliveries-delivery'
						style='color:". (($pastDelivery === true)?"gray": ($delivery->areOrdersOpen == 1?"green":"brown")) ."'
					>
					<td style='text-align:center' class='editable'>$spotName </td>
					<td style='text-align:center'>".date(csa_wp_plugin_date_format_readable, strtotime($delivery->order_deadline_date))."</td>
					<td style='text-align:center' class='editable'>".CsaWpPluginRemoveSeconds($delivery->order_deadline_time)."</td>
					<td style='text-align:center'>".date(csa_wp_plugin_date_format_readable, strtotime($delivery->delivery_day))."</td>
					<td style='text-align:center'>".CsaWpPluginRemoveSeconds($delivery->delivery_start_time)."</td>
					<td style='text-align:center'>".CsaWpPluginRemoveSeconds($delivery->delivery_end_time)."</td>
					<td style='text-align:center' class='editable'>$userInCharge_login</td>
					<td style='text-align:center'
						class='editable_boolean'
						id = 'csa-wp-plugin-showDeliveriesOpenOrdersID_$deliveryID'
					>".(($delivery->areOrdersOpen == 1)?"yes":"no")."</td>
					<td style='text-align:center'><img 
							style='cursor:pointer' 
							src='".plugins_url()."/csa-wp-plugin/icons/".(($delivery->areOrdersOpen == 1)?"open":"close").".png' 
							height='24' width='24' 
							id = 'csa-wp-plugin-showDeliveriesOpenOrdersIconID_$deliveryID'
							title='".(($delivery->areOrdersOpen == 1)?"remove":"grant")." ability to order'
							onclick='CsaWpPluginRequestToggleDeliveryAbilityToOrder(this,\"$pluginsDir\")'></td>
					<td style='text-align:center'> 
						<img 
							width='24' height='24'  
							class='delete no-underline' 
							src='$pluginsDir/csa-wp-plugin/icons/edit.png' 
							style='cursor:pointer;padding-left:10px;' 
							onclick='CsaWpPluginEditDelivery(this, \"". admin_url('/admin.php?page=csa_deliveries_management')."\")' 
							title='click to edit this delivery'/></td>
					<td style='text-align:center'> <img 
						style='cursor:pointer' 
						src='".plugins_url()."/csa-wp-plugin/icons/delete.png' 
						height='24' width='24'
						onmouseover='CsaWpPluginHoverIcon(this, \"delete\", \"$pluginsDir\")' 
						onmouseout='CsaWpPluginUnHoverIcon(this, \"delete\", \"$pluginsDir\")' 						
						onclick='CsaWpPluginRequestDeleteDelivery(this)' 
						title='delete delivery'></td>
					</tr>

				";
						
			}
			?>
		</tbody> </table>
	</div>	
<?php
}

add_action( 'wp_ajax_csa-wp-plugin-update_delivery_abilityToOrder', 'CsaWpPluginUpdateDeliveryAbilityToOrder' );

 function CsaWpPluginUpdateDeliveryAbilityToOrder() {
	if(isset($_POST['deliveryID']) && isset($_POST['areOrdersOpen'])) {
		$deliveryID = intval($_POST['deliveryID']);
		$areOrdersOpen = $_POST['areOrdersOpen'];

		global $wpdb;		
		if(	$wpdb->update(
			csaDeliveries,
			array("areOrdersOpen" => $areOrdersOpen), 
			array('id' => $deliveryID)
		) === FALSE) 
			echo 'error, sql request failed';												
		else echo 'success, ability to order has been updated.';
	} else {
		echo 'error, invalid request made.';
	}
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_csa-wp-plugin-delete_delivery', 'CsaWpPluginDeleteDelivery' );

function CsaWpPluginDeleteDelivery() {
	if(isset($_POST['deliveryID'])) {
		$deliveryID = intval(clean_input($_POST['deliveryID']));
		if(!empty($deliveryID)) {
			// Updating the information 
			global $wpdb;

			if(	$wpdb->delete(
				csaDeliveries,
				array('id' => $deliveryID ),
				array ('%d')
			) === FALSE) 
				echo 'error, sql request failed.';												
			else echo 'success';
		} 
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

?>