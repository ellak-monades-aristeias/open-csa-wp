<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function csa_wp_plugin_new_delivery_form($spot_id, $order_deadline_date, $custom_values, $delivery_id, $display) { 
	
	wp_enqueue_script( 'csa-wp-plugin-general-scripts' );
	wp_enqueue_script( 'csa-wp-plugin-deliveries-scripts' );
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

	global $days_of_week,$wpdb;
	
	$in_charge = null;
	$custom_bool = false;
	if ($spot_id != null) {
		$spot_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".CSA_WP_PLUGIN_TABLE_SPOTS." WHERE id=%d", $spot_id))[0];
	
		if ($delivery_id != null) {
			$delivery_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". CSA_WP_PLUGIN_TABLE_DELIVERIES ." WHERE id=%d", $delivery_id))[0];

			$in_charge = $delivery_info->user_in_charge;
			
			$order_deadline_day = (date("w", strtotime($order_deadline_date)) - 1) % 7;
			if ($order_deadline_day == -1) {
				$order_deadline_day = 6;									// So that the 'if' below (for custom_bool) is executed correctly
			}
			$order_deadline_time = csa_wp_plugin_remove_seconds($delivery_info->order_deadline_time);
			$delivery_day = (date("w", strtotime($delivery_info->delivery_date)) - 1) % 7;
			if ($delivery_day == -1) {
				$delivery_day = 6;											// So that the 'if' below (for custom_bool) is executed correctly
			}
			$delivery_start_time = csa_wp_plugin_remove_seconds($delivery_info->delivery_start_time);
			$delivery_end_time = csa_wp_plugin_remove_seconds($delivery_info->delivery_end_time);
			
			if (
				$order_deadline_day 	!= $spot_info->default_order_deadline_day ||
				$order_deadline_time 	!= csa_wp_plugin_remove_seconds($spot_info->default_order_deadline_time) ||
				$delivery_day 			!= $spot_info->default_delivery_day ||
				$delivery_start_time 	!= csa_wp_plugin_remove_seconds($spot_info->default_delivery_start_time) ||
				$delivery_end_time 		!= csa_wp_plugin_remove_seconds($spot_info->default_delivery_end_time)
			) {
				$custom_bool = true;
			}			
		}
		else {
			$order_deadline_day = $spot_info->default_order_deadline_day;
			$order_deadline_time = csa_wp_plugin_remove_seconds($spot_info->default_order_deadline_time);
			$delivery_day = $spot_info->default_delivery_day;
			$delivery_start_time = csa_wp_plugin_remove_seconds($spot_info->default_delivery_start_time);
			$delivery_end_time = csa_wp_plugin_remove_seconds($spot_info->default_delivery_end_time);
		}
		
		if (count($custom_values) > 0) {
			$custom_bool = true;
			
			if (isset($custom_values["order_deadline_day"])) {
				$order_deadline_day = $custom_values["order_deadline_day"];
			}
			if (isset($custom_values["order_deadline_time"])) {
				$order_deadline_time = $custom_values["order_deadline_time"];
			}
			if (isset($custom_values["delivery_day"])) {
				$delivery_day = $custom_values["delivery_day"];
			}
			if (isset($custom_values["delivery_start_time"])) {
				$delivery_start_time = $custom_values["delivery_start_time"];
			}
			if (isset($custom_values["delivery_end_time"])) {
				$delivery_end_time = $custom_values["delivery_end_time"];		
			}
		}
		else if ($delivery_id!= null && $order_deadline_date == null) {
			$order_deadline_date = $delivery_info->order_deadline_date;
		}

	}
		
	if ($order_deadline_date != null) {
		$order_deadline_date = explode(";", $order_deadline_date)[0];
		
		if ($spot_id != null && $delivery_id == null ) {
			$delivery_date = date(CSA_WP_PLUGIN_DATE_FORMAT, strtotime("Next ". $days_of_week[$delivery_day], strtotime($order_deadline_date)));
			$deliveries_info = $wpdb->get_results($wpdb->prepare("
										SELECT * FROM ". CSA_WP_PLUGIN_TABLE_DELIVERIES ." 
										WHERE
											order_deadline_date = %s AND
											delivery_date=%s
									", $order_deadline_date, $delivery_date));
									
			if ($deliveries_info != null){
				$delivery_info = $deliveries_info[0];
				$delivery_id = $delivery_info->id;
				$in_charge = $delivery_info->user_in_charge;
			}
		}
	}
	
?>

	<br/>

	<div id="csa-wp-plugin-newDelivery_formHeader">
		<span 
			id="csa-wp-plugin-newDelivery_formHeader_text" 
			<?php 
				if ($spot_id == null) {
					echo 'style="cursor:pointer"';
					echo 'onclick="csa_wp_plugin_toggle_form(\'newDelivery\',\''. __('Initiate New Delivery',CSA_WP_PLUGIN_DOMAIN) .'\', \''. __(' 	form',CSA_WP_PLUGIN_DOMAIN).'\')"';
				}
			?>
		><font size='4'>
		<?php 
			if ($spot_id == null) {
				if ($display == false) {
					echo __('Initiate New Delivery', CSA_WP_PLUGIN_DOMAIN) .' ('. __('show form',CSA_WP_PLUGIN_DOMAIN) .')';
				} else {
					echo __('Initiate New Delivery', CSA_WP_PLUGIN_DOMAIN) .' ('. __('hide form',CSA_WP_PLUGIN_DOMAIN) .')';
				}
			}
			else if ($delivery_id != null) {
				echo __('Edit Delivery', CSA_WP_PLUGIN_DOMAIN) . ' #'. $delivery_id;	
			}
			else {
				echo __('Initiating new delivery for ', CSA_WP_PLUGIN_DOMAIN) ;
			}
		?>
		</font>
		</span>
	</div>
	<div id="csa-wp-plugin-newDelivery_div" 
		<?php 
			if ($display == false) {
				echo 'style="display:none"';
			}?>	
	>
		<form method="POST" id='csa-wp-plugin-initiateNewDelivery_form_id'>
			<table class="form-table">
				<tr valign="top">
					<td>
					<select 
						name="csa-wp-plugin-newDelivery_spotDetails_spotID_input" 
						id="csa-wp-plugin-newDelivery_spotDetails_spotID_input_id"
						<?php 
							if ($spot_id == null) {
								echo "style='color:#999'";
							}
							echo "onchange='window.location.replace(\"". admin_url('/admin.php?page=csa_deliveries_management')."&id=\" + this.value)'";
						?>
					>
					<option 
						value="" 
						<?php 
							if ($spot_id == null) {  
								echo "selected='selected' "; 
							}
						?>
						disabled='disabled'
						id = "csa-wp-plugin-newDelivery_spotDetails_spotID_input_disabled_id"
					><?php _e('Select Spot', CSA_WP_PLUGIN_DOMAIN)?> *</option>
 					<?php 
						echo csa_wp_plugin_select_delivery_spots(($spot_id != null)?$spot_id:null, "Spot "); 
					?>
                  	</select>
					<span id="csa-wp-plugin-newDelivery_spotDetails_spotID_input_span_id">
					<?php 
						if ($spot_id!=null) {
							if ($order_deadline_date!=null) {
								echo "&nbsp;&nbsp;where";
							} else if ($custom_bool === false) {
								echo "
									<i style='color:gray' class='csa-wp-plugin-tip_deliveries' title='
										". __('Below, you can customize the deadline and delivery dates (and times) for this delivery.', CSA_WP_PLUGIN_DOMAIN) ."
									'> 
										&nbsp; &nbsp; ". __('with default values... (point here)', CSA_WP_PLUGIN_DOMAIN) ." 
									</i>";
							} else {
								echo "<i style='color:gray'> &nbsp; &nbsp; ". __('with custom values...',CSA_WP_PLUGIN_DOMAIN) ." </i>";
							}
						}
					?>
					</span>
				</tr>
				<tr
					<?php 
						if ($spot_id == null) {
							echo 'style="display:none"';
						}
					?>
				>
					<td>
					<select 
						<?php 
							if ($order_deadline_date == null) {
								echo 'style="color:#999"';
							}
						?>
						id="csa-wp-plugin-newDelivery_delivery_deadline_date_input_id"
						name="csa-wp-plugin-newDelivery_delivery_deadline_date_input"
						onchange = '														
							document.getElementById("csa-wp-plugin-newDelivery_orderDeadlineDate_choice_id").value = this.options[this.selectedIndex].value;
							csa_wp_plugin_new_delivery_format_custom_values(
								document.getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id")
							)
						'

					>
					<option disabled="disabled" 
						value=""
						id = "csa-wp-plugin-newDelivery_delivery_deadline_date_disabled_id";
					<?php 
						if ($order_deadline_date == null) { 
							echo 'selected="selected"';
						}
					?>
					> <?php _e('Choose a deadline date', CSA_WP_PLUGIN_DOMAIN)?> * </option>
					<?php 
						$deadline_day = $days_of_week[$order_deadline_day];
						for ($i=0; $i<5; $i++) {
							$next_deadline_date = date(CSA_WP_PLUGIN_DATE_FORMAT, strtotime("Next ". $deadline_day . "+$i week"));
							$next_deadline_date_readable = $deadline_day . ", ". date(CSA_WP_PLUGIN_DATE_FORMAT_READABLE, strtotime($next_deadline_date)) . ", ". __('up to', CSA_WP_PLUGIN_DOMAIN) ." ". $order_deadline_time;
							if ($order_deadline_date == null || $order_deadline_date != $next_deadline_date) {
								echo "<option 
										style='color:black' 
										value='$next_deadline_date;$order_deadline_time'
									>$next_deadline_date_readable</option>";
							} else if ($order_deadline_date == $next_deadline_date) {
								echo "<option 
										style='color:black' 
										selected = 'selected' 
										value='$next_deadline_date;$order_deadline_time'
									>".__('Order deadline is on',CSA_WP_PLUGIN_DOMAIN)." $next_deadline_date_readable</option>";
							}
						}
					?>
					</select>
					<span id="csa-wp-plugin-newDelivery_delivery_deadline_date_input_span_id" 
						<?php 
							if ($order_deadline_date == null) {
								echo 'style="display:none"'; 
							}
						?>
					> &nbsp;&nbsp; <?php _e('and', CSA_WP_PLUGIN_DOMAIN); ?>
					</span></td>
				</tr>
				<tr valign="top" 
				<?php 
					if ($order_deadline_date==null) {
						echo 'style="display:none"';
					}
				?>><td><span> 
				<?php
					$delivery_date = date(CSA_WP_PLUGIN_DATE_FORMAT_READABLE, strtotime("Next ". $days_of_week[$delivery_day], strtotime($order_deadline_date)));
					$value_of_read_only_input = "Delivery is on ". $days_of_week[$delivery_day] .", ". $delivery_date .", from $delivery_start_time to $delivery_end_time";
					$value_of_read_only_input_len = strlen($value_of_read_only_input);
					$size_of_read_only_input = (($value_of_read_only_input_len + 1) ).'"px\"';
					echo " 	<input 
								name = 'csa-wp-plugin-newDelivery_DeliveryDaTeDetails_input'
								type = 'text'
								readonly = 'readonly'
								value='$value_of_read_only_input'
								style='border:none; background-color:white;'
								size='$size_of_read_only_input'
							/>";
				?>
				</span></td></tr>
				
				<tr valign="top" 
				<?php 
					if ($order_deadline_date==null) {
						echo 'style="display:none"';
					}
				?>><td>
					<select 
						name="csa-wp-plugin-newDelivery_inCharge_input"
						id="csa-wp-plugin-newDelivery_inCharge_input_id"		
						onchange = '
							this.style.color="black"
							if (this.options[this.selectedIndex].text.split(" ")[0] != "<?php _e('Responsible for this delivery is', CSA_WP_PLUGIN_DOMAIN); ?>".split(" ")[0]) {
								this.options[this.selectedIndex].text = <?php _e('Responsible for this delivery is', CSA_WP_PLUGIN_DOMAIN); ?> + " " + this.options[this.selectedIndex].text;
							}
						'
						<?php 
							if ($delivery_id == null) 
								{echo "style='color:#999'";
							}
						?>
					>
						<option 
							value="" 
							<?php 
								if ($delivery_id == null) {
									echo "selected='selected'";
								}
							?>
							id = "csa-wp-plugin-newDelivery_inCharge_input_disabled_id"
							disabled='disabled'
						><?php _e('Do you know who is going to be in charge?', CSA_WP_PLUGIN_DOMAIN)?> </option>
						<?php echo csa_wp_plugin_select_users_of_type("consumer", $in_charge, __('Responsible for this delivery is', CSA_WP_PLUGIN_DOMAIN)." "); ?>
					</select>
				</td></tr>
				
				<tr valign="top"
					<?php 
						if ($delivery_id == null) {
							echo "style='display:none'"; 
						}
					?>
				><td>
					<select 
						name="csa-wp-plugin-delivery_abilityToSubmitOrder_input" 
						id="csa-wp-plugin-delivery_abilityToSubmitOrder_input_id"
						<?php 
							if ($delivery_id != null && $delivery_info->are_orders_open == 1) {
								echo "style='color:green'";
							} else {
								echo "style='color:brown'";
							}
						?>
						onchange='
							if (this.options[this.selectedIndex].value == "yes") {
								this.style.color = "green";
								this.options[this.selectedIndex].text = "<?php _e('Currently, new orders can be submitted', CSA_WP_PLUGIN_DOMAIN); ?>"
							}
							else {
								this.style.color = "brown";
								this.options[this.selectedIndex].text = "<?php _e('Currently, new orders can not be submitted', CSA_WP_PLUGIN_DOMAIN); ?>"
							}
							'
					>
					<?php 
						if ($delivery_id != null) {
							echo '
								<option value="yes" style="color:green". '. ($delivery_info->are_orders_open == 1?"selected='selected'":"").'> '.__('Currently, new orders can be submitted', CSA_WP_PLUGIN_DOMAIN).' </option>
								<option value="no" style="color:brown"'. ($delivery_info->are_orders_open == 0?"selected='selected'":"").'> '.__('Currently, new orders can not be submitted', CSA_WP_PLUGIN_DOMAIN).' </option>
							';
						}
					?>					
					</select>
					</td>
				</tr>

				
				
				<tr <?php 
					if ($spot_id == null) {
						echo 'style="display:none"';
					}
					?>>
					<td>
					<input 
						type="submit" 
						class="button button-primary"
						id="csa-wp-plugin-initiateNewDelivery_button_id"
						
						<?php 
							if ($delivery_id == null) {
								echo "
									value='".__('Initiate Delivery', CSA_WP_PLUGIN_DOMAIN)."'
									onclick='csa_wp_plugin_request_initiate_new_or_update_delivery(this, null, \"". admin_url("/admin.php?page=csa_deliveries_management") ."\");'
								";
							} else {
								echo "
									value='".__('Update Delivery', CSA_WP_PLUGIN_DOMAIN)."'
									onclick='csa_wp_plugin_request_initiate_new_or_update_delivery(this, $delivery_id, \"". admin_url("/admin.php?page=csa_deliveries_management") ."\");'
								";
							}
						?>
					/>
					
					<input 
						type="button"
						class="button button-secondary"
						value="<?php _e('Cancel', CSA_WP_PLUGIN_DOMAIN)?>"
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
			<?php 
				if ($spot_id == null) {
					echo 'style="display:none"';
				}
			?>
			action="<?php echo admin_url('/admin.php?page=csa_deliveries_management');?>"
		>
			<br/>
			<div id="csa-wp-plugin-newDelivery_spotDetailsDetails_formHeader">
				<span 
					id="csa-wp-plugin-newDelivery_spotDetailsDetails_formHeader_text" 
					style="cursor:pointer"
					<?php
						if ($custom_bool === true) {
							echo "onclick=\"csa_wp_plugin_toggle_form('newDelivery_spotDetailsDetails','".__('Custom values', CSA_WP_PLUGIN_DOMAIN)." ', ' ".__('form', CSA_WP_PLUGIN_DOMAIN)."', 3, '&nbsp;&nbsp;&nbsp;')\"";
						} else {
							echo "onclick=\"csa_wp_plugin_toggle_form('newDelivery_spotDetailsDetails','".__('Customize default values', CSA_WP_PLUGIN_DOMAIN)." ', ' ".__('form', CSA_WP_PLUGIN_DOMAIN)."', 3, '&nbsp;&nbsp;&nbsp;')\"";
						}
					?>
				><font size='3'>
				<?php
					$text_hide_show = "hide";
					if ($order_deadline_date != null || $delivery_id != null) {
						$text_hide_show = "show";
					}
				
					if ($custom_bool === true) {
						echo "&nbsp;&nbsp;&nbsp;".__('Custom values', CSA_WP_PLUGIN_DOMAIN)." ($text_hide_show ".__('form', CSA_WP_PLUGIN_DOMAIN).")";
					} else {
						echo "&nbsp;&nbsp;&nbsp;".__('Customize default values', CSA_WP_PLUGIN_DOMAIN)." ($text_hide_show ".__('form', CSA_WP_PLUGIN_DOMAIN).")";
					}
				?>
					
				</font>
				</span>
			</div>
			<div id = "csa-wp-plugin-newDelivery_spotDetailsDetails_div"
				<?php 
					if ($order_deadline_date != null || $delivery_id != null) {
						echo "style='display:none'";
					}
				?>
			>
				<table class="form-table">		
				<tr hidden="hidden">
					<td>
					<input 	name='csa-wp-plugin-newDelivery_spotID_choice' 
							id='csa-wp-plugin-newDelivery_spotID_choice_id'
							value="<?php 
										if ($spot_id!=null) { 
											echo $spot_id;
										}
									?>">
					</td>
				<tr/>
				<tr hidden="hidden">
					<td>
					<input 	name='csa-wp-plugin-newDelivery_deliveryID_choice' 
							value="<?php 
										if ($delivery_id!=null) {
											echo $delivery_id;
										}
									?>">
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
					if ($delivery_id != null) {
						echo"
							<tr hidden = 'hidden'> 
								<td> <input name='csa-wp-plugin-newDelivery_deliveryID' value=\"$delivery_id\"/> </td>
							</tr>
						";
					}
				?>
				<tr valign="top"><td>
					<select 
						name="csa-wp-plugin-newDelivery_order_deadline_day_input"
						id='csa-wp-plugin-newDelivery_order_deadline_day_input_id'
						onfocus=' getElementById("csa-wp-plugin-newDelivery_spotDetails_orderDeadline_span").style.display = "none";'
						onchange='
							if (this.options[this.selectedIndex].text.split(" ")[0] != "order") {
								this.options[this.selectedIndex].text = "order deadline is on " + this.options[this.selectedIndex].text;
							}
							getElementById("csa-wp-plugin-newDelivery_order_deadline_time_input_id").style.display = "inline"
							'
					>
					<option value="" selected='selected' disabled="disabled" id="csa-wp-plugin-newDelivery_order_deadline_day_disabled_id">order deadline day ... *</option>
					<?php 
					for ($i=0; $i<7; $i++) {
						if ($order_deadline_day == $i) {
							echo "<option value='$i' selected='selected'> ".__('order deadline is on', CSA_WP_PLUGIN_DOMAIN)." $days_of_week[$i] </option>";
						} else {
							echo "<option value='$i'>".$days_of_week[$i]."</option>";
						}
					}
					?>
					</select> 
					<input 
						<?php 
							if ($spot_id != null) {
								echo "value='".__('up to', CSA_WP_PLUGIN_DOMAIN)." $order_deadline_time'";
							}
						?>
						placeholder="up to... *"
						id="csa-wp-plugin-newDelivery_order_deadline_time_input_id"
						class="textbox-n" type="text" size="10" name="csa-wp-plugin-newDelivery_order_deadline_time_input"
						onfocus='
							getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id").disabled = true;
							<?php
								if ($custom_bool === true && $delivery_id == null) {
									echo 'getElementById("csa-wp-plugin-newDeliveryCustomValues_reset_button_id").disabled = true;';
								}
							?>
							if (this.value != "") this.value=this.value.split(" ")[2];
							else getElementById("csa-wp-plugin-newDelivery_spotDetails_orderDeadline_span").style.display = "none";
							this.type="time";'
						onblur='
							getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id").disabled = false;
							<?php
								if ($custom_bool === true && $delivery_id == null) {
									echo 'getElementById("csa-wp-plugin-newDeliveryCustomValues_reset_button_id").disabled = false;';
								}
							?>
							this.type="text";
							if (this.value != "") {
								this.style.color="black";
								this.value = "<?php _e('up to', CSA_WP_PLUGIN_DOMAIN); ?> " + this.value;
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
							if (this.options[this.selectedIndex].text.split(" ")[0] != "Delivery") {
								this.options[this.selectedIndex].text = "Delivery day is " + this.options[this.selectedIndex].text;
							}
							getElementById("csa-wp-plugin-newDelivery_spotDetails_delivery_start_time_input_id").style.display = "inline"'
					>
					<option value="" disabled="disabled" 
						id="csa-wp-plugin-newDelivery_delivery_day_disabled_id"><?php _e('Delivery Day', CSA_WP_PLUGIN_DOMAIN); ?> ... *</option>
					<?php 
					for ($i=0; $i<7; $i++) {
						if ($delivery_day == $i) {
							echo "<option value='$i' selected='selected'> ".__('Delivery day is', CSA_WP_PLUGIN_DOMAIN)." $days_of_week[$i] </option>";
						} else {
							echo "<option value='$i'>".$days_of_week[$i]."</option>";
						}
					}
					?>
					</select> 
					<input id="csa-wp-plugin-newDelivery_spotDetails_delivery_start_time_input_id"
						<?php 
							if ($spot_id != null) {
								echo "value='from $delivery_start_time'";
							}
						?>
						placeholder="<?php _e('from', CSA_WP_PLUGIN_DOMAIN); ?>... *"
						class="textbox-n" type="text" size="10" 
						name="csa-wp-plugin-newDelivery_delivery_start_time_input"

						onfocus='
							getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id").disabled = true;
							<?php
								if ($custom_bool === true && $delivery_id == null) {
									echo 'getElementById("csa-wp-plugin-newDeliveryCustomValues_reset_button_id").disabled = true;';
								}
							?>
							if (this.value != "") {
								this.value=this.value.split(" ")[1];
							} else {
								getElementById("csa-wp-plugin-newDelivery_spotDetails_invalidDeliveryTime_span").style.display = "none";
							}
							this.type="time";'
						onblur='
							getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id").disabled = false;
							<?php
								if ($custom_bool === true && $delivery_id == null) {
									echo 'getElementById("csa-wp-plugin-newDeliveryCustomValues_reset_button_id").disabled = false;';
								}
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
								csa_wp_plugin_validate_delivery_time_period("newDelivery_spotDetails");
							}'
						>
					<input id="csa-wp-plugin-newDelivery_spotDetails_delivery_end_time_input_id"
						<?php 
							if ($spot_id != null) {
								echo "value='".__('to', CSA_WP_PLUGIN_DOMAIN)." $delivery_end_time'";
							}
						?>
						placeholder="<?php _e('to', CSA_WP_PLUGIN_DOMAIN); ?>... *"					
						class="textbox-n" type="text" size="10" 
						name="csa-wp-plugin-newDelivery_delivery_end_time_input"
						
						onfocus='
							getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id").disabled = true;
							<?php
								if ($custom_bool === true && $delivery_id == null) {
									echo 'getElementById("csa-wp-plugin-newDeliveryCustomValues_reset_button_id").disabled = true;';
								}
							?>
							if (this.value != "") {
								this.value=this.value.split(" ")[1]
							}
							getElementById("csa-wp-plugin-newDelivery_spotDetails_invalidDeliveryTime_span").style.display = "none";
							this.type="time";'
						onblur='
							getElementById("csa-wp-plugin-newDeliveryCustomValues_button_id").disabled = false;
							<?php
								if ($custom_bool === true && $delivery_id == null) {
									echo 'getElementById("csa-wp-plugin-newDeliveryCustomValues_reset_button_id").disabled = false;';
								}
							?>
							this.type="text";
							if (this.value != "") {
								this.style.color="black";
								this.value = "<?php _e('to', CSA_WP_PLUGIN_DOMAIN); ?> " + this.value;
								csa_wp_plugin_validate_delivery_time_period("newDelivery_spotDetails");
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
						value="<?php _e('Use Custom Values', CSA_WP_PLUGIN_DOMAIN); ?>"
						onclick="csa_wp_plugin_new_delivery_format_custom_values(this);"
					/>
					<?php
						if ($custom_bool === true && $delivery_id == null) {
							echo "
								<input
									type='submit'
									class='button button-secondary'
									id='csa-wp-plugin-newDeliveryCustomValues_reset_button_id'
									value='".__('Reset to default values', CSA_WP_PLUGIN_DOMAIN)."'
									onclick='
										window.location.replace(\"". admin_url('/admin.php?page=csa_deliveries_management')."&id=". $spot_id ."\");
										event.preventDefault();
									'
								/>
							";					
						}
					?>
				</td>
				
				</tr>
				</table>
			</div>
		</form>
	</div>
	
<?php

}

function csa_wp_plugin_return_custom_values_for_new_delivery ($spot_id) {

	$custom_values = array();
	
	global $wpdb;
	$spot_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".CSA_WP_PLUGIN_TABLE_SPOTS." WHERE id=%d", $spot_id))[0];
	
	if ($_POST["csa-wp-plugin-newDelivery_order_deadline_day_input"] != $spot_info->default_order_deadline_day ) {
		$custom_values["order_deadline_day"] = $_POST["csa-wp-plugin-newDelivery_order_deadline_day_input"];
	}

	if ($_POST["csa-wp-plugin-newDelivery_order_deadline_time_input"] != $spot_info->default_order_deadline_time ) {
		$custom_values["order_deadline_time"] = csa_wp_plugin_remove_seconds($_POST["csa-wp-plugin-newDelivery_order_deadline_time_input"]);
	}

	if ($_POST["csa-wp-plugin-newDelivery_delivery_day_input"] != $spot_info->default_delivery_day ) {
		$custom_values["delivery_day"] = $_POST["csa-wp-plugin-newDelivery_delivery_day_input"];
	}

	if ($_POST["csa-wp-plugin-newDelivery_delivery_start_time_input"] != $spot_info->default_delivery_start_time ) {
		$custom_values["delivery_start_time"] = csa_wp_plugin_remove_seconds($_POST["csa-wp-plugin-newDelivery_delivery_start_time_input"]);
	}

	if ($_POST["csa-wp-plugin-newDelivery_delivery_end_time_input"] != $spot_info->default_delivery_end_time ) {
		$custom_values["delivery_end_time"] = csa_wp_plugin_remove_seconds($_POST["csa-wp-plugin-newDelivery_delivery_end_time_input"]);
	}
		
	return $custom_values;
}


add_action( 'wp_ajax_csa-wp-plugin-initiate_or_update_new_delivery_request', 'CsaWpPluginInitiateOrUpdateNewDelivery' );

function CsaWpPluginInitiateOrUpdateNewDelivery() {

	if( isset($_POST['data']) && isset($_POST['delivery_id'])) {

		$data_received = json_decode(stripslashes($_POST['data']),true);
		
		$parts = explode(";", $data_received[1]['value']);
		$order_deadline_date = $parts[0];
		$order_deadline_time = $parts[1];
		
		$parts = explode(", ", $data_received[2]['value']);
		
		$delivery_date = date(CSA_WP_PLUGIN_DATE_FORMAT, strtotime($parts[1]));
		
		$parts = explode(" ", $parts[2]);
		$delivery_start_time = $parts[1];
		$delivery_end_time = $parts[3];

		$user_in_charge = $data_received[3]['value'];
		
		$data_vals = array(
					'spot_id' 				=> intval(csa_wp_plugin_clean_input($data_received[0]['value'])),
					'order_deadline_date' 	=> $order_deadline_date,
					'order_deadline_time' 	=> $order_deadline_time,
					'delivery_date'			=> $delivery_date,
					'delivery_start_time'	=> $delivery_start_time,
					'delivery_end_time'	 	=> $delivery_end_time,
					'are_orders_open' 		=> ($data_received[4]['value'] == "yes" || $data_received[4]['value'] =="")?1:0
				);

		$data_types = array ("%d", "%s", "%s", "%s", "%s", "%s");
				
		
		if ($user_in_charge!=null) {
			$data_vals['user_in_charge'] = $user_in_charge;
			$data_types[6] = "%d";
		}

		
		global $wpdb;
	
		$delivery_id = intval(csa_wp_plugin_clean_input($_POST['delivery_id']));
	
		if ($delivery_id != null) {
			$delivery_id = intval($delivery_id);
			
			//update delivery (query)
			if(	$wpdb->update(
				CSA_WP_PLUGIN_TABLE_DELIVERIES, 
				$data_vals, 
				array('id' => $delivery_id), 
				$data_types
			) === FALSE) {
				echo 'error, sql request failed.';
			} else {
				echo 'Success, delivery is updated.';
			}
		
		}
		else { 
			//insert delivery (query)
			if(	$wpdb->insert(
				CSA_WP_PLUGIN_TABLE_DELIVERIES, 
				$data_vals, 
				$data_types
			) === FALSE) {
				echo 'error, sql request failed.';
			}
			else {
				echo 'Success, delivery is initiated.';
			}
		}
	}
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}


function csa_wp_plugin_show_deliveries($display) {
	wp_enqueue_script('csa-wp-plugin-general-scripts');
	wp_enqueue_script('csa-wp-plugin-deliveries-scripts');
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
			onclick="csa_wp_plugin_toggle_form('showDeliveriesList','Deliveries List', '')">
			<font size='4'>
			<?php 
				if ($display == false) {
					echo __('Deliveries List', CSA_WP_PLUGIN_DOMAIN) .' ('. __('show',CSA_WP_PLUGIN_DOMAIN) .')';
				} else {
					echo __('Deliveries List', CSA_WP_PLUGIN_DOMAIN) .' ('. __('hide',CSA_WP_PLUGIN_DOMAIN) .')';
				}
			?>
			</font>
		</span>
	</div>
	<div id="csa-wp-plugin-showDeliveriesList_div" 
		<?php 
			if ($display == false) {
				echo 'style="display:none"';
			}
		?>	
	>
		<span class='csa-wp-plugin-tip_deliveries' title='
			<?php _e('Deliveries in "green" are pending and still accept new orders',CSA_WP_PLUGIN_DOMAIN)?>.
			| <?php _e('Deliveries in "brown" are pending and do not accept new orders',CSA_WP_PLUGIN_DOMAIN)?>.
			| <?php _e('Deliveries in "grey" are accomplished',CSA_WP_PLUGIN_DOMAIN)?>.
			| <?php _e('To change the ability of new order submission, you can click on the "envelope" icon',CSA_WP_PLUGIN_DOMAIN)?>.
			| <?php _e('If you want to edit delivery details, click on the "pen" icon',CSA_WP_PLUGIN_DOMAIN)?>.
			| <?php _e('If you want to delete some delivery, click on the "x" icon',CSA_WP_PLUGIN_DOMAIN)?>.
			'>
		<p style="color:green;font-style:italic; font-size:13px">
			<?php _e('by pointing here you can read additional information.',CSA_WP_PLUGIN_DOMAIN)?></p></span>


		<table 
			class='table-bordered' 
			id="csa-wp-plugin-showDeliveriesList_table" 
			style='border-spacing:1em' 
		> 
		<thead class='tableHeader'>
			<tr>
				<th><?php _e('Spot',CSA_WP_PLUGIN_DOMAIN)?></th>
				<th><?php _e('Order Deadline Date',CSA_WP_PLUGIN_DOMAIN)?></th>
				<th><?php _e('Order Deadline Time',CSA_WP_PLUGIN_DOMAIN)?></th>
				<th><?php _e('Delivery Date',CSA_WP_PLUGIN_DOMAIN)?></th>
				<th><?php _e('Delivery Start Time',CSA_WP_PLUGIN_DOMAIN)?></th>
				<th><?php _e('Delivery End Time',CSA_WP_PLUGIN_DOMAIN)?></th>
				<th><?php _e('User In Charge',CSA_WP_PLUGIN_DOMAIN)?></th>
				<th><?php _e('New Orders Can be Submitted?',CSA_WP_PLUGIN_DOMAIN)?></th>
				<th/>
				<th/>
				<th/>
			</tr>
		</thead> 
		<tbody> <?php
			global $wpdb;
			$plugins_dir = plugins_url();

			$deliveries = $wpdb->get_results("SELECT * FROM ". CSA_WP_PLUGIN_TABLE_DELIVERIES);
			foreach($deliveries as $delivery) 
			{
				$delivery_id = $delivery->id;
				$spot_name = $wpdb->get_var($wpdb->prepare("SELECT spot_name FROM ". CSA_WP_PLUGIN_TABLE_SPOTS ." WHERE id=%d", $delivery->spot_id));
				 
				$user_in_charge_login = "";
				if ($delivery->user_in_charge != null ) {
					$user_in_charge_login = get_user_by('id', $delivery->user_in_charge)->user_login;
				}
				
				$past_delivery = false;
				$current_date_time = current_time('mysql');
				if (strtotime($delivery->order_deadline_date." ". $delivery->order_deadline_time) < strtotime($current_date_time)) {
					$past_delivery = true;
				}
				
				echo "
					<tr 
						valign='top' 
						id='csa-wp-plugin-showDeliveriesDeliveryID_$delivery_id'  
						class='csa-wp-plugin-showDeliveries-delivery'
						style='color:". (($past_delivery === true)?"gray": ($delivery->are_orders_open == 1?"green":"brown")) ."'
					>
					<td style='text-align:center' class='editable'>$spot_name </td>
					<td style='text-align:center'>".date(CSA_WP_PLUGIN_DATE_FORMAT_READABLE, strtotime($delivery->order_deadline_date))."</td>
					<td style='text-align:center' class='editable'>".csa_wp_plugin_remove_seconds($delivery->order_deadline_time)."</td>
					<td style='text-align:center'>".date(CSA_WP_PLUGIN_DATE_FORMAT_READABLE, strtotime($delivery->delivery_date))."</td>
					<td style='text-align:center'>".csa_wp_plugin_remove_seconds($delivery->delivery_start_time)."</td>
					<td style='text-align:center'>".csa_wp_plugin_remove_seconds($delivery->delivery_end_time)."</td>
					<td style='text-align:center' class='editable'>$user_in_charge_login</td>
					<td style='text-align:center'
						class='editable_boolean'
						id = 'csa-wp-plugin-showDeliveriesOpenOrdersID_$delivery_id'
					>".(($delivery->are_orders_open == 1)?"yes":"no")."</td>
					<td style='text-align:center'><img 
							style='cursor:pointer' 
							src='".plugins_url()."/csa-wp-plugin/icons/".(($delivery->are_orders_open == 1)?"open":"close").".png' 
							height='24' width='24' 
							id = 'csa-wp-plugin-showDeliveriesOpenOrdersIconID_$delivery_id'
							title='".(($delivery->are_orders_open == 1)?__('remover', CSA_WP_PLUGIN_DOMAIN):__('grant', CSA_WP_PLUGIN_DOMAIN))." ".__('ability to order', CSA_WP_PLUGIN_DOMAIN)."'
							onclick='csa_wp_plugin_request_toggle_delivery_ability_to_order(this,\"$plugins_dir\")'></td>
					<td style='text-align:center'> 
						<img 
							width='24' height='24'  
							class='delete no-underline' 
							src='$plugins_dir/csa-wp-plugin/icons/edit.png' 
							style='cursor:pointer;padding-left:10px;' 
							onclick='csa_wp_plugin_edit_delivery(this, \"". admin_url('/admin.php?page=csa_deliveries_management')."\")' 
							title='".__('click to edit this delivery', CSA_WP_PLUGIN_DOMAIN)."'/></td>
					<td style='text-align:center'> <img 
						style='cursor:pointer'
						src='".plugins_url()."/csa-wp-plugin/icons/delete.png' 
						height='24' width='24'
						onmouseover='csa_wp_plugin_hover_icon(this, \"delete\", \"$plugins_dir\")' 
						onmouseout='csa_wp_plugin_unhover_icon(this, \"delete\", \"$plugins_dir\")' 						
						onclick='csa_wp_plugin_request_delete_deliver(this)' 
						title='".__('delete delivery', CSA_WP_PLUGIN_DOMAIN)."'></td>
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
	if(isset($_POST['delivery_id']) && isset($_POST['are_orders_open'])) {
		$delivery_id = intval($_POST['delivery_id']);
		$are_orders_open = $_POST['are_orders_open'];

		global $wpdb;		
		if(	$wpdb->update(
			CSA_WP_PLUGIN_TABLE_DELIVERIES,
			array("are_orders_open" => $are_orders_open), 
			array('id' => $delivery_id)
		) === FALSE) {
			echo 'error, sql request failed';												
		} else {
			echo 'success, ability to order has been updated.';
		}
	} else {
		echo 'error, invalid request made.';
	}
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_csa-wp-plugin-delete_delivery', 'CsaWpPluginDeleteDelivery' );

function CsaWpPluginDeleteDelivery() {
	if(isset($_POST['delivery_id'])) {
		$delivery_id = intval(csa_wp_plugin_clean_input($_POST['delivery_id']));
		if(!empty($delivery_id)) {
			// Updating the information 
			global $wpdb;

			if(	$wpdb->delete(
				CSA_WP_PLUGIN_TABLE_DELIVERIES,
				array('id' => $delivery_id ),
				array ('%d')
			) === FALSE) {
				echo 'error, sql request failed.';												
			} else {
				echo 'success';
			}
		} 
		else {
			echo 'error,Empty values.';
		}
	} 
	else {
		echo 'error,Bad request.';
	}
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

function csa_wp_plugin_active_deliveries_exist() {

	global $wpdb;
	if ($wpdb->get_var("
			SELECT COUNT(id)
			FROM " .CSA_WP_PLUGIN_TABLE_DELIVERIES. " 
			WHERE 
				delivery_date > CURDATE() AND
				are_orders_open = 1
		") == 0) {
		
		echo "
			<h4 style='color:gray'>". __('You are not able to sumbit new order, since there is no delivery accepting new order submissions', CSA_WP_PLUGIN_DOMAIN)."</h4> 
			<h4 style='color:gray'>". __('You can create new deliveries', CSA_WP_PLUGIN_DOMAIN)."
			<a href='".
				admin_url('/admin.php?page=csa_deliveries_management')
			."'>". __('here', CSA_WP_PLUGIN_DOMAIN)." </a></h4>
		";
		return false;
	} else {
		return true;
	}

}

function csa_wp_plugin_active_deliveries_exist_for_spot($spot_id) {

	global $wpdb;
	if ($wpdb->get_var($wpdb->prepare("
			SELECT COUNT(id)
			FROM " .CSA_WP_PLUGIN_TABLE_DELIVERIES. " 
			WHERE 
				spot_id = %d AND
				delivery_date > CURDATE() AND
				are_orders_open = 1
		", $spot_id)) == 0) {
		return false;
	} else {
		return true;	
	}

}

function csa_wp_plugin_select_deliveries($spot_id, $selectedDeliveryID, $message) {
	global $wpdb;
	$deliveries = $wpdb->get_results($wpdb->prepare("
			SELECT 
				id, 
				order_deadline_date, 
				order_deadline_time, 
				delivery_date,
				delivery_start_time,
				delivery_end_time
			FROM ".CSA_WP_PLUGIN_TABLE_DELIVERIES." 
			WHERE 
				spot_id = %d AND
				delivery_date > CURDATE() AND
				are_orders_open = 1
		",$spot_id));
	
	foreach ($deliveries as $delivery) {
		global $days_of_week;
		$deadline_day = $days_of_week[($deadlineDayInt = (date('w', strtotime($delivery->order_deadline_date)) - 1)) == -1?6:$deadlineDayInt];
		$delivery_day = $days_of_week[($deliveryDayInt = (date('w', strtotime($delivery->delivery_date)) - 1)) == -1?6:$deliveryDayInt];
		
		$text_to_show = 	"deadline on $deadline_day, " .date(CSA_WP_PLUGIN_DATE_FORMAT_READABLE, strtotime($delivery->order_deadline_date)) .", " .  csa_wp_plugin_remove_seconds($delivery->order_deadline_time) . 
						" and delivery on $delivery_day, " . date(CSA_WP_PLUGIN_DATE_FORMAT_READABLE, strtotime($delivery->delivery_date)) ." between ". 
							 csa_wp_plugin_remove_seconds($delivery->delivery_start_time) ." and ". 
							csa_wp_plugin_remove_seconds($delivery->delivery_end_time);
	
		if ($delivery->id == $selectedDeliveryID) {
			echo "<option value='".$delivery->id."' selected='selected' style='color:black'>". $message. $text_to_show. "</option>";
		} else {
			echo "<option value='".$delivery->id."' style='color:black'>". $text_to_show ."</option>";
		}
	}
}

function CsaWpPluginGetReadableDeliveryInfo ($delivery_id) {

	global $days_of_week, $wpdb;
	
	$delivery_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". CSA_WP_PLUGIN_TABLE_DELIVERIES ." WHERE id=%d", $delivery_id))[0];
	$spotName = $wpdb->get_var($wpdb->prepare("
									SELECT DISTINCT ".CSA_WP_PLUGIN_TABLE_SPOTS.".spot_name 
									FROM ". CSA_WP_PLUGIN_TABLE_DELIVERIES ." LEFT JOIN ".CSA_WP_PLUGIN_TABLE_SPOTS." ON ".CSA_WP_PLUGIN_TABLE_DELIVERIES.".spot_id = ".CSA_WP_PLUGIN_TABLE_SPOTS.".id
									WHERE ".CSA_WP_PLUGIN_TABLE_DELIVERIES.".id = %d
								", $delivery_id));
	
	$deadlineDate = $delivery_info->order_deadline_date;
	$order_deadline_day = (date("w", strtotime($deadlineDate)) - 1) % 7;
	$deadline_day = $days_of_week[$order_deadline_day];
	
	$delivery_date = $delivery_info->delivery_date;
	$delivery_day = (date("w", strtotime($delivery_date)) - 1) % 7;
	$delivery_day = $days_of_week[$delivery_day];
	
	$delivery_info_readable = __('Delivery for spot', CSA_WP_PLUGIN_DOMAIN)." $spotName ".__('with', CSA_WP_PLUGIN_DOMAIN)."
		".__('deadline on', CSA_WP_PLUGIN_DOMAIN)." $deadline_day ,". date(CSA_WP_PLUGIN_DATE_FORMAT_READABLE, strtotime($deadlineDate)) . ", 
			up to ". csa_wp_plugin_remove_seconds($delivery_info->order_deadline_time). ",
		".__('and delivery on', CSA_WP_PLUGIN_DOMAIN)." $delivery_day,". date(CSA_WP_PLUGIN_DATE_FORMAT_READABLE, strtotime($delivery_date)) . ", 
			".__('from', CSA_WP_PLUGIN_DOMAIN)." ". csa_wp_plugin_remove_seconds($delivery_info->delivery_start_time). "
			".__('up to', CSA_WP_PLUGIN_DOMAIN)." ". csa_wp_plugin_remove_seconds($delivery_info->delivery_end_time);

	return $delivery_info_readable;
}

function csa_wp_plugin_is_deadline_reached($delivery_id) {
	
	global $wpdb;
	
	$delivery_info = $wpdb->get_results($wpdb->prepare("SELECT order_deadline_date, order_deadline_time FROM ". CSA_WP_PLUGIN_TABLE_DELIVERIES ." WHERE id=%d", $delivery_id))[0];
	$order_deadline_date = $delivery_info->order_deadline_date;
	$order_deadline_time = $delivery_info->order_deadline_time;
	
	$order_deadline_datetime = $order_deadline_date. " " . $order_deadline_time;
	$current_time = current_time( 'mysql' ); 
	
	if(strtotime($current_time)<strtotime($current_time)) {
		return false;
	} else {
		return true;
	}
}

?>