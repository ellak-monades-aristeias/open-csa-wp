<?php

function CsaWpPluginShowOrderForm ($userID, $spotID, $deliveryID, $display, $pageURL, $personalOrder){

	wp_enqueue_script( 'CsaWpPluginScripts' );
	wp_enqueue_script( 'CsaWpPluginOrdersScripts' );
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');
	
	global $wpdb;
	
	if ($spotID != null)
		$spotInfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". csaSpots ." WHERE id=%d", $spotID))[0];	
	
	$editOrderBool = false;
	$headerText = "Submit New Order";
	if (isset($_POST["csa-wp-plugin-showEditableUserOrderForm_user_input"]) OR (
				$userID != null && 
				$deliveryID != null &&
				CsaWplPluginUserOrderExistsForDelivery($userID, $deliveryID) === true
			)
		) {
		$editOrderBool = true;
		$headerText = "Edit Order";
	}
?>

	<br/>

	<div id="csa-wp-plugin-showNewOrderForm_formHeader">
		<span 
			id="csa-wp-plugin-showNewOrderForm_formHeader_text"
			style="cursor:pointer"
			onclick="CsaWpPluginToggleForm('showNewOrderForm','<?php echo $headerText; ?>', ' form');"
		>
		<?php
			if ($display === false)
				echo "<font size=4> $headerText (show form) </font>";
			else echo "<font size=4> $headerText (hide form) </font>";
		?>
		</span>
	</div>
	<div 
		id="csa-wp-plugin-showNewOrderForm_div" 
		<?php if ($display === false) echo 'style="display:none"' ?>	
	>
		<form method="POST" id="csa-wp-plugin-showNewOrderForm_form_id">
			<table class="form-table">
				<tr valign="top" <?php if ($personalOrder === true) echo "style='display:none'"; ?>>
					<td id = "csa-wp-plugin-showNewOrderForm_user_input_td_id">
						<select
							name = 'csa-wp-plugin-showNewOrderForm_user_input'
							onchange = 'getElementById ("csa-wp-plugin-showNewOrderForm_form_id").submit()'
						>
							<option
								value=""
								selected="selected"
								disabled="disabled"
							> Select the user submitting the order ...
							</option>
							<?php CsaWpPluginSelectUsers($userID, "for user: ");?>
						</select>
					</td>
				</tr>
				
				<tr valign="top"
				<?php if ($userID == null) echo "style = 'display:none'"?>
				>
				<td>
					<select
						name = 'csa-wp-plugin-showSelectSpotForm_spot_input'
						onchange = 'getElementById("csa-wp-plugin-showNewOrderForm_form_id").submit();'
					>
						<option 
							value="" 
							selected="selected" 
							disabled="disabled"
						> 
						<?php
						if ($personalOrder === false)
							echo "now, select the delivery spot ...";
						else echo "select the delivery spot ...";
						?>
						
						</option>
						<?php CsaWpPluginSelectDeliverySpots($spotID, "on delivery spot: ");?>
					</select>
				</td>
				</tr>
				
				<tr valign = "top"
				<?php if ($spotID == null) echo "style = 'display:none'"?>
				><td>
				
				
				
				<div id="csa-wp-plugin-showNewOrderForm_spotDetails_formHeader">
					<span 
						id="csa-wp-plugin-showNewOrderForm_spotDetails_formHeader_text"
						style="cursor:pointer"
						onclick="CsaWpPluginToggleForm('showNewOrderForm_spotDetails','spot details...', '', 2);"
					><font size=2> spot details... (show) </font>
					</span>
				</div>
				<div 
					id="csa-wp-plugin-showNewOrderForm_spotDetails_div" 
					style="display:none"
				>
				
				<?php if ($spotID != null) {				
					echo "
						<table>
						<tr valign='top'><td>
							<input 
								type='text' 
								style='border:none; background-color:white; color:#999'
								readonly='readonly'
								value='".$spotInfo->street_name."'
							/>
							<input 
								type='text' 
								style='border:none; background-color:white; color:#999'
								readonly='readonly'
								value='".$spotInfo->street_number."'
							/>
						</td></tr>
						<tr valign='top'><td>
							<input 
								type='text' 
								style='border:none; background-color:white; color:#999'
								readonly='readonly'
								value='".$spotInfo->city."'
							/>
							<input 
								type='text' 
								style='border:none; background-color:white; color:#999'
								readonly='readonly'
								value='".$spotInfo->region."'
							/>
						</td></tr>
						<tr valign='top'><td>
							<textarea 
								style='border:none; background-color:white; color:#999'
								readonly='readonly'
								rows='3' 
								cols='30' >".$spotInfo->description ."</textarea>
						</td></tr>
						
						<tr valign='top'><td>
							<input 
								type='text'
								style='border:none; background-color:white; color:#999'
								readonly='readonly'
								value='parking is ". $spotInfo->parking."'
							/>
						</td></tr>										
						<tr valign='top'><td>
							<input
								type='text'
								style='border:none; background-color:white; color:#999'
								readonly='readonly'
								value='".
								($spotInfo->has_refrigerator == 1?
								"It has refrigerator to store products! :) ": 
								"It does not have refrigerator to store products! :(")
								."'
								size = '40px'
							/>
						</td></tr>

						
						</table>
					";
				}
				?>
				</div></td></tr>
				
				
				<tr valign = "top" 
				<?php if ($spotID == null) echo "style = 'display:none'"?>
				>
				<td id = "csa-wp-plugin-showSelectSpotForm_delivery_input_td_id">
					<?php
					if (CsaWpPluginActiveDeliveriesExistForSpot($spotID) == true) {
					?>
						<select
							name = 'csa-wp-plugin-showSelectSpotForm_delivery_input'
							onchange = 'getElementById("csa-wp-plugin-showNewOrderForm_form_id").submit();'
						>
							<option 
								value="" 
								selected="selected" 
								disabled="disabled"
							> now, select one of the active deliveries ...
							</option>
							<?php if ($spotID!=null) CsaWpPluginSelectDeliveries($spotID, $deliveryID, "for delivery with: "); ?>
						</select>
					<?php
					} else {
						$valueOfReadOnlyInput = "currently, this delivery spot has no active deliveries";
						$valueOfReadOnlyInput_len = strlen($valueOfReadOnlyInput);
						$sizeOfReadOnlyInput = (($valueOfReadOnlyInput_len + 1) ).'"px\"';
						echo "
							<input 
								type='text' 
								style='border:none; background-color:white; color:brown'
								readonly='readonly'
								value = '$valueOfReadOnlyInput'
								size = '$sizeOfReadOnlyInput'
								
							/>
						";
					}
					?>
				</td>
			</tr>
			</table>	
		</form>
	</div>
<?php
	if ($editOrderBool === true)
		CsaWpPluginShowEditableUserOrder($userID, $deliveryID, true, $pageURL);
	else if ($deliveryID != null) CsaWpPluginShowNewOrderUserForm($deliveryID, $userID, false, $pageURL);	
	
	if ($editOrderBool === false && $userID != null && $deliveryID == null && ($personalOrder === false || $spotID != null)) {
	?>
	<input 
	type="button"
	class="button button-secondary"
	value='cancel'
	onclick='window.location.replace(" <?php echo $pageURL ?> ");'
	/>	
	<?php
		
	}
}

function CsaWplPluginUserOrderExistsForDelivery($userID, $deliveryID) {
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare("
				SELECT delivery_id
				FROM ".csaUserOrders."
				WHERE 
					delivery_id = %d AND
					user_id = %d
			",$deliveryID, $userID)) != null?true:false;
}

function CsaWpPluginShowNewOrderUserForm($deliveryID, $userID, $editOrderBool, $pageURL) {
	echo "
		<span 
			class='csa-wp-plugin-tip_order' 
			title=' 
				Choose the quantity of each product you want to order and press \"Submit\" at the end of the page.
				| After submitting your order, you will still be able to edit it, until the deadline for order submission of this delivery.
				| You can read additional information for each product, by pointing to \"info\".
			'>
			<p style='color:green;font-style:italic; font-size:13px'>
			by pointing here you can read additional information...</p>
		</span>";

	CsaWpPluginShowUserOrderForm($deliveryID, $userID, $editOrderBool, $pageURL);
}

function CsaWpPluginShowEditableUserOrder($userID, $deliveryID, $editOrderBool, $pageURL) { 
	wp_enqueue_script( 'CsaWpPluginScripts' );
	wp_enqueue_script( 'CsaWpPluginOrdersScripts' );
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui');
?>	
	<span 
		class='csa-wp-plugin-tip_order' 
		title="
			If you want to edit the quantity of some product order, click on it, update it, and then press ENTER.
			|To delete some product from your order, press the 'x' icon.
		">
		<p style="color:green;font-style:italic; font-size:13px">
			by pointing here you can read additional information...</p>
	</span>	
	
	<table class='table-bordered' id="csa-wp-plugin-showUserOrder_table" style='border-spacing:1em'> 
	<thead class="tableHeader">
		<tr>
			<th>Quantity</th>
			<th	style="text-align:center">Name</th>
			<th>Category</th>
			<th>Variety</th>
			<th>Price(€)</th>
			<th>Per...</th>
			<th>Cost</th>
			<th>Producer</th>
			<th></th>
			<th></th>
		</tr>
	</thead> 
	<tbody> 
	<?php
		//show an editable version of the user's order	
		global $wpdb;
		$productOrders = $wpdb->get_results($wpdb->prepare("
							SELECT * 
							FROM ". csaProductOrders ."
							WHERE
								delivery_id = %d AND
								user_id = %d
						", $deliveryID, $userID));

		$totalCost = 0;
		$pluginsDir = plugins_url();
		
		$productCategoriesMap = $wpdb->get_results("SELECT id,name FROM ".csaProductCategories, OBJECT_K);
		$producersMap = CsaWpPluginProducersMapArray();
		
		foreach($productOrders as $productOrder) {
			$productID = $productOrder->product_id;
			$productInfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".csaProducts." WHERE id=%d", $productID))[0];
			
			$pCost = $productInfo->current_price_in_euro * $productOrder->quantity;
			echo "
			<tr class='csa-wp-plugin-user-order-product' id='csa-wp-plugin-userProductOrder_".$deliveryID."_".$userID."_$productID'>			
				<td class='editable_product_order_quantity' style='text-align:center'>$productOrder->quantity</td>
				<td>$productInfo->name</td>
				<td>".$productCategoriesMap[$productInfo->category]->name."
				<td>".$productInfo->variety."</td>
				<td class='editable_product_order_price'>".$productInfo->current_price_in_euro."</td>
				<td>".$productInfo->measurement_unit."</td>
				<td class='editable_product_order_cost' style='text-align:center;font-weight:bold'> $pCost €</td>
				<td>".$producersMap[$productInfo->producer]."</td>";	
				if ($productInfo->description != '')
					echo "<td style='text-align:center'><span class='csa-wp-plugin-tip_order' title='|".$productInfo->description."'>info</span></td>";			
				else echo "<td/>";			
				echo "<td> <img class='delete no-underline' src='$pluginsDir/csa-wp-plugin/icons/delete.png' style='cursor:pointer;padding-left:10px;' onmouseover='CsaWpPluginHoverIcon(this, \"delete\", \"$pluginsDir\")' onmouseout='CsaWpPluginUnHoverIcon(this, \"delete\", \"$pluginsDir\")' onclick='CsaWpPluginRequestDeleteProductOrder(this)' title='διαγραφή'/></td>
			</tr>"; 
			$totalCost+=$pCost;
		}
	?>
	</tbody>
	</table>

	<table>
		<tbody>
			<tr> 
				<td><div>Total Cost: <span id='editable_product_order_TotalCost' style='font-weight:bold'><?php echo round($totalCost,1). " €" ?></span></div></td>
				<td><div onclick="CsaWpPluginSlideToggle(document.getElementById('cancel_div'))"> 
						<img src="<?php echo plugins_url(); ?>/csa-wp-plugin/icons/delete.png"/ height="24" width="24"> &nbsp
						<span class='showHide_div' style='color:brown;cursor:pointer'>Cancel Order</span> 
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<input 
					type="button"
					class="button button-primary"
					value='Done'
					onclick='window.location.replace(" <?php echo $pageURL ?> ");'
					/>
				</td>
			</tr>
		</tbody></table>
	</div>
	
	<div id='cancel_div' style='display:none; '> <br/>
		<button type="button" class="button button-primary" onclick='CsaWpPluginRequestCancelUserOrder(<?php echo $deliveryID.", ".$userID ?>, this)'>Yes... cancel order!</button>
		<button type="button" class="button button-primary" style='background-color:brown' onclick="CsaWpPluginSlideToggle(document.getElementById('cancel_div'))">uhh... just a mistake!</button>
	</div>
	<br/>
	<?php CsaWpPluginShowAdditionalProductOrdersForm($deliveryID, $userID, $editOrderBool, $pageURL); 
}

function CsaWpPluginShowAdditionalProductOrdersForm($deliveryID, $userID, $editOrderBool, $pageURL) {
?>
	<div id="csa-wp-plugin-showAdditionalProductOrders_formHeader">
		<span 
			id="csa-wp-plugin-showAdditionalProductOrders_formHeader_text"
			style="cursor:pointer"
			onclick="CsaWpPluginToggleForm('showAdditionalProductOrders','Add more products', ' form');"
		><font size=4> Add more products (show form) </font>
		</span>
	</div>
	<div 
		id="csa-wp-plugin-showAdditionalProductOrders_div"  style='display:none'>
	<span 
		class='csa-wp-plugin-tip_order' 
		title=' 
			Choose the quantity of each product you want to order and press \"Submit\" at the end of the page.
			| You can read additional information for each product, by pointing to \"info\".
		'>
		<p style='color:green;font-style:italic; font-size:13px'>
			by pointing here you can read additional information...</p>
	</span>
<?php

	CsaWpPluginShowUserOrderForm($deliveryID, $userID, $editOrderBool, $pageURL);
	echo "</div>";
}

function CsaWpPluginShowUserOrderForm($deliveryID, $userID, $editOrderBool, $pageURL) {
	//list categories as headers
	
	global $wpdb;
	$productCategories = $wpdb->get_results("SELECT DISTINCT category FROM ".csaProducts);
	$productCategoriesMap = $wpdb->get_results("SELECT id,name FROM ".csaProductCategories, OBJECT_K);
	$producersMap = CsaWpPluginProducersMapArray();
	

	foreach ($productCategories as $productCategory)
			echo "(<a href='#csa-wp-plugin-jump_to_category_".$productCategoriesMap[$productCategory->category]->name."'>".$productCategoriesMap[$productCategory->category]->name."</a>)  " ;
	?>
	<br/>
	
	<form id='csa-wp-plugin-sumbitOrder_form_id' accept-charset='utf-8' method='post'> 
		<table id='ordersArray' class='table-bordered' style='border-spacing:1em'> 
			<thead class="tableHeader">
				<tr>
					<th>Name</th>
					<th>Variety</th>
					<th>Price</th>
					<th>Unit</th>
					<th>Quantity</th>
					<th>Producer</th>
					<th> </th> 
					<th class='th-hidden'>
					</th>
				</tr>
			</thead>
			<?php
			foreach ($productCategories as $productCategory) {
				$products = $wpdb->get_results($wpdb->prepare("
								SELECT * 
								FROM ".csaProducts." 
								WHERE category=%d ORDER BY name", $productCategory->category )
							);
								
				if (count($products) > 0) 
					echo "
						<tr>
							<td> <strong> 
								<a 
									name='csa-wp-plugin-jump_to_category_".$productCategoriesMap[$productCategory->category]->name."' 
									class='anchored'>
								</a> 
									<span 
										class='emphasis-box' 
										style='margin-left:-5px;'
									>".$productCategoriesMap[$productCategory->category]->name."
									</span>
								</strong>
							</td>
						</tr>
					";
				foreach($products as $row) {
					if($row->is_available == 1) {
						echo "
						<tr>
						<td><span>".$row->name."</span></td>
						<td><span>".$row->variety."</span></td>
						<td><input type='number' readonly = 'readonly' name='csa-wp-plugin-order_productPrice' value='".$row->current_price_in_euro."' style=' width: 5em; border:none; background-color:white; text-align: center'/></td>
						<td>€/".$row->measurement_unit."</td>
						<td><input type='number' min='0' step='0.5' name='csa-wp-plugin-order_productQuantity' onchange='CsaWpPluginCalcNewOrderCost()' onkeyup='CsaWpPluginCalcNewOrderCost()' style='width:70px;background-color:LightGoldenRodYellow'></td>
						<td>".$producersMap[$row->producer]."</td>";
						if ($row->description != null)
							echo "<td style='text-align:center'><span class='csa-wp-plugin-tip_order' title='|".$row->description."'>info</span></td>";
						echo "<td class='td-hidden'><input type='number' name='csa-wp-plugin-order_productID' value='".$row->id."' style='visibility:hidden'/></td>
						</tr>";
					}
				}
			}
			?>
			<tr style='background-color:#d0e4fe;'><td><span id='csa-wp-plugin-totalCalc'/></td></tr>
		</table>  
		<br/>
<!--		<div style="margin-top:2%">
			<p><h6><span class='csa-wp-plugin-tip_order' title='Προσθέστε σημειώσεις στην παραγγελία σας, εάν θέλετε κάτι να ληφθεί υπόψιν. Οι σημειώσεις σας θα εμφανίζονται στη συνολική παραγγελία'>
			Σημειώσεις</span></h6></p>
			<textarea name="notes" id="notesArea" cols="50" rows="3" maxlength="500" class='info-text'></textarea>
		</div> -->
		<table>
			<tr>
			<td>
			<input type='button' class="button button-primary" 
				<?php
				if($editOrderBool === true)
					echo "value='Submit Additional Order'";
				else echo "value='Submit Order'";
				
				?>
				style="margin-top:3%" onclick="CsaWpPluginNewOrderValidation(<?php echo "$deliveryID, $userID"; ?>, this)"/> 
			</td>
			<?php
			if ($editOrderBool === false) {
				echo "
					<td>
					<input 
						type='button'
						class='button button-secondary'
						value='cancel'
						onclick='window.location.replace(\" $pageURL\");'
					/>
					</td>
				";
			}
			?>
			<td><span id="csa-wp-plugin-showNewOrderForm_emptyOrder_span_id" style="color:brown; display:inline" /></td>
			</tr>
		</table>
		
	</form>
		
	<?php
}


function CsaWpPluginShowAllUserOrders($personalUserID, $display) {
	wp_enqueue_script('CsaWpPluginScripts');
	wp_enqueue_script('CsaWpPluginOrdersScripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); 
	
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');
	
	global $wpdb;
	if ($personalUserID != null)
		$userOrdersCount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(delivery_id) FROM ". csaUserOrders ." WHERE user_id = %d ", $personalUserID));
	else
		$userOrdersCount = $wpdb->get_var("SELECT COUNT(delivery_id) FROM ". csaUserOrders);
		
	if ($userOrdersCount == 0) {
		if ($personalUserID != null)
			echo "<br/><div style='color:brown'>You have not yet submitted any order.</div>";
		else "<br/><div style='color:brown'>No user has yet submitted any order.</div>";
	}
	else {
?>
		
		<br />
		<div id="csa-wp-plugin-showAllUserOrdersList_header">
			<span 
				style="cursor:pointer" 
				id="csa-wp-plugin-showAllUserOrdersList_formHeader_text" 
				onclick="CsaWpPluginToggleForm('showAllUserOrdersList','User Orders List', '')">
				<font size='4'>
				<?php 
					if ($personalUserID != null)
						$textToShow = "Your Orders List";
					else $textToShow = "User Orders List";
				
					if ($display === true)
						echo "$textToShow (hide)";
					else echo "$textToShow (show)";
				?>
				</font>
			</span>
		</div>
		<div id="csa-wp-plugin-showAllUserOrdersList_div" 
			<?php if ($display === false) echo 'style="display:none"' ?>	
		>
			<span class='csa-wp-plugin-tip_order' title='
				Deliveries in "green" are pending and still accept new orders.
				| Deliveries in "brown" are pending and do not accept new orders.
				| Deliveries in "grey" are accomplished.
				| If you want to edit user order details, press the "pen" icon.
				'>
			<p style="color:green;font-style:italic; font-size:13px">
				by pointing here you can read additional information...</p></span>


			<table 
				class='table-bordered' 
				id="csa-wp-plugin-showAllUserOrdersList_table" 
				style='border-spacing:1em' 
			> 
			<thead class='tableHeader'>
				<tr>
					<?php 
					if ($personalUserID == null) 
						echo "<th>User</th>"
					?>
					<th>Spot</th>
					<th>Order Deadline Date</th>
					<th>Order Deadline Time</th>
					<th>Delivery Date</th>
					<th>Delivery Start Time</th>
					<th>Delivery End Time</th>
					<th>User In Charge</th>
					<th>New Orders Can be Submitted?</th>
					<th/>
				</tr>
			</thead> 
			<tbody> <?php
				global $wpdb;
				$pluginsDir = plugins_url();
				
				if ($personalUserID == null) 
					$csaUsers = get_users();
				else $csaUsers = array(get_user_by('id', $personalUserID));
				
				foreach ($csaUsers as $csaUser) {
				
					$deliveries = $wpdb->get_col($wpdb->prepare("SELECT delivery_id FROM ". csaUserOrders ." WHERE user_id = %d ", $csaUser->ID));
					
					foreach($deliveries as $delivery) {
						$deliveryInfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". csaDeliveries. " WHERE id = %d", $delivery))[0];

						$deliveryID = $deliveryInfo->id;
						$spot_name = $wpdb->get_var($wpdb->prepare("SELECT spot_name FROM ". csaSpots ." WHERE id=%d", $deliveryInfo->spot_id));
						 
						$user_in_charge_login = "";
						if ($deliveryInfo->user_in_charge != null )
							$user_in_charge_login = get_user_by('id', $deliveryInfo->user_in_charge)->user_login;
						
						$pastDelivery = false;
						$currentDateTime = current_time('mysql');
						if (strtotime($deliveryInfo->order_deadline_date." ". $deliveryInfo->order_deadline_time) < strtotime($currentDateTime))
							$pastDelivery = true;
						
						echo "
							<tr 
								valign='top' 
								id='csa-wp-plugin-showDeliveriesDeliveryID_$deliveryID'  
								class='csa-wp-plugin-showDeliveries-delivery'
								style='color:". (($pastDelivery === true)?"gray": ($deliveryInfo->areOrdersOpen == 1?"green":"brown")) ."'
							>
							". (($personalUserID === null)?("<td>".CsaWpPluginUserReadable($csaUser)."</td>"):("")) ."
							<td style='text-align:center'>$spot_name </td>
							<td style='text-align:center'>".date(csa_wp_plugin_date_format_readable, strtotime($deliveryInfo->order_deadline_date))."</td>
							<td style='text-align:center'>".CsaWpPluginRemoveSeconds($deliveryInfo->order_deadline_time)."</td>
							<td style='text-align:center'>".date(csa_wp_plugin_date_format_readable, strtotime($deliveryInfo->delivery_date))."</td>
							<td style='text-align:center'>".CsaWpPluginRemoveSeconds($deliveryInfo->delivery_start_time)."</td>
							<td style='text-align:center'>".CsaWpPluginRemoveSeconds($deliveryInfo->delivery_end_time)."</td>
							<td style='text-align:center'>$user_in_charge_login</td>
							<td style='text-align:center'
								id = 'csa-wp-plugin-showDeliveriesOpenOrdersID_$deliveryID'
							>".(($deliveryInfo->areOrdersOpen == 1)?"yes":"no")."</td>
							<td style='text-align:center'> 
								<img 
									width='24' height='24'  
									class='delete no-underline' 
									src='$pluginsDir/csa-wp-plugin/icons/edit.png' 
									style='cursor:pointer;padding-left:10px;' 
									onclick='CsaWpPluginEditUserOrder($csaUser->ID, $deliveryID)' 
									title='click to edit this order'/></td>
							</tr>
						";
								
					}
				}
				?>
			</tbody> </table>
		</div>	
<?php
	}
}


function CsaWpPluginShowDeliveryOrdersList ($personalUserID, $isProducer, $display) {

	wp_enqueue_script('CsaWpPluginScripts');
	wp_enqueue_script('CsaWpPluginDeliveriesScripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); 
	
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

	global $wpdb;
	if ($personalUserID != null && $isProducer==false)
		$deliveryOrdersCount = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM ". csaDeliveries ." WHERE user_in_charge = %d ", $personalUserID));
	else
		$deliveryOrdersCount = $wpdb->get_var("SELECT COUNT(id) FROM ". csaDeliveries);
		
	if ($deliveryOrdersCount == 0) {
		if ($personalUserID == null)
			"<br/><div style='color:brown'>No delivery has yet been initiated.</div>";
		else if ($isProducer == false)
			echo "<br/><div style='color:brown'>You have not yet become responsible for any delivery.</div>";
	}
	else {
	
?>
		
		<br />
		<div id="csa-wp-plugin-showDeliveryOrdersList_header">
			<span 
				style="cursor:pointer" 
				id="csa-wp-plugin-showDeliveryOrdersList_formHeader_text" 
				onclick="CsaWpPluginToggleForm('showDeliveryOrdersList','Delivery Orders List', '')">
				<font size='4'>
				<?php 
					if ($personalUserID) $textToShow = "List";
					else $textToShow = "Delivery Total Orders List";
				
					if ($display == false) echo "$textToShow (show)";
					else echo "$textToShow (hide)";
				?>
				</font>
			</span>
		</div>
		<div id="csa-wp-plugin-showDeliveryOrdersList_div" 
			<?php if ($display == false) echo 'style="display:none"' ?>	
		>
			<span class='csa-wp-plugin-tip_order' title='
				To view the total user orders for some delivery, preee the "magnifier" icon.
				'>
			<p style="color:green;font-style:italic; font-size:13px">
				by pointing here you can read additional information... </p></span>


			<table 
				class='table-bordered' 
				id="csa-wp-plugin-showDeliveryOrdersList_table" 
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
					<?php 	
					if ($personalUserID == null || $isProducer === true)
						echo "<th>User In Charge</th>";
					?>
					<th>New Orders Can be Submitted?</th>
					<th/>
				</tr>
			</thead> 
			<tbody> <?php
				global $wpdb;
				$pluginsDir = plugins_url();
				
				if ($personalUserID == null || $isProducer === true) 
					$deliveries = $wpdb->get_results("SELECT * FROM ". csaDeliveries);
				else $deliveries = $wpdb->get_results($wpdb->prepare("SELECT * FROM ". csaDeliveries ." WHERE user_in_charge = %d ", $personalUserID));
				
				foreach($deliveries as $delivery) 
				{
					$deliveryID = $delivery->id;
					$spot_name = $wpdb->get_var($wpdb->prepare("SELECT spot_name FROM ". csaSpots ." WHERE id=%d", $delivery->spot_id));
					 
					$user_in_charge_login = "";
					if ($delivery->user_in_charge != null )
						$user_in_charge_login = get_user_by('id', $delivery->user_in_charge)->user_login;
					
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
						<td style='text-align:center'>$spot_name </td>
						<td style='text-align:center'>".date(csa_wp_plugin_date_format_readable, strtotime($delivery->order_deadline_date))."</td>
						<td style='text-align:center'>".CsaWpPluginRemoveSeconds($delivery->order_deadline_time)."</td>
						<td style='text-align:center'>".date(csa_wp_plugin_date_format_readable, strtotime($delivery->delivery_date))."</td>
						<td style='text-align:center'>".CsaWpPluginRemoveSeconds($delivery->delivery_start_time)."</td>
						<td style='text-align:center'>".CsaWpPluginRemoveSeconds($delivery->delivery_end_time)."</td>
						". (($personalUserID == null || $isProducer === true)?("<td style='text-align:center'>".$user_in_charge_login."</td>"):(""))."
						<td style='text-align:center'
							class='editable_boolean'
							id = 'csa-wp-plugin-showDeliveriesOpenOrdersID_$deliveryID'
						>".(($delivery->areOrdersOpen == 1)?"yes":"no")."</td>
						<td style='text-align:center'> 
							<img 
								width='24' height='24'  
								class='delete no-underline' 
								src='$pluginsDir/csa-wp-plugin/icons/magnifier.png' 
								style='cursor:pointer;padding-left:10px;' 
								onclick='CsaWpPluginRequestTotalOrdersOfDelivery(".$deliveryID.", ".
								(($isProducer === true) ? "$personalUserID" : "null")
								.");' 
								title='click to view the total user orders for this delivery'/></td>
						</tr>

					";
							
				}
				?>
			</tbody> </table>
		</div>	
<?php
	}
}

function CsaWpPluginShowTotalOrdersOfDelivery ($deliveryID, $producerID, $pageURL) {
	global $wpdb;

	$producersMap = CsaWpPluginProducersMapArray();
	
	if ($producerID != null )
		$producers_involved = $wpdb->get_col($wpdb->prepare("
								SELECT DISTINCT ".csaProducts.".producer 
								FROM ".csaProducts." LEFT JOIN ".csaProductOrders." ON ".csaProducts.".id = ".csaProductOrders.".product_id
								WHERE 
									".csaProductOrders.".delivery_id = %d AND
									".csaProducts.".producer = %d
							", $deliveryID, $producerID));
	else 
		$producers_involved = $wpdb->get_col($wpdb->prepare("
								SELECT DISTINCT ".csaProducts.".producer 
								FROM ".csaProducts." LEFT JOIN ".csaProductOrders." ON ".csaProducts.".id = ".csaProductOrders.".product_id
								WHERE ".csaProductOrders.".delivery_id = %d
								ORDER BY ".csaProducts.".producer
							", $deliveryID));
	$producers_count = count($producers_involved);
	
	
	if ($producerID != null) 
		echo "<span class='info-text' style='font-size:15px'>Your orders in ". CsaWpPluginGetReadableDeliveryInfo($deliveryID) ." </span>";
	else echo "<span class='info-text' style='font-size:15px'>Orders of ". CsaWpPluginGetReadableDeliveryInfo($deliveryID) ." </span>";
	
	if($producers_count <= 0) {
		if ($producerID == null)
			echo "<br/><span class='info-text' style='font-size:15px;color:brown'>No order has yet been submitted, for this delivery. </span> <br>";
		else echo "<br/><span class='info-text' style='font-size:15px;color:brown'>You have no orders in this delivery. </span> <br>";
	}
	else {	
		$users_count = $wpdb->get_var($wpdb->prepare("
									SELECT COUNT(DISTINCT user_id)
									FROM ".csaProductOrders." 
									WHERE ".csaProductOrders.".delivery_id = %d 
								", $deliveryID));
								
		$product_details_width = 130;
		$amount_perProduct_width = 30;
		$user_order_width = 58; 			//change also by css (class .left) ???? SHALL WE INCORPORATE THIS? ????
		$productValue_width = 70;
		$mainWidth = $product_details_width + $amount_perProduct_width + $user_order_width*$users_count + 30; // +30 is an offset to accomodate the space of the first orders
		$tableWidth = $mainWidth + $productValue_width;
				
		foreach ($producers_involved as $producer) {
			$revenue = 0; //the amount to be paid to this producer
			echo "<p class='panel'> <span style='font-size:14px'> Producer: </span> <span class='producer'>".$producersMap[$producer]."</span> </p>";
			
			//Get the products in order for this producer
			$products_in_order = $wpdb->get_results($wpdb->prepare("
										SELECT 
											".csaProducts.".name,
											".csaProducts.".variety,
											".csaProducts.".current_price_in_euro,
											".csaProducts.".id,
											".csaProducts.".measurement_unit, 
											SUM(".csaProductOrders.".quantity) AS total, 
											FORMAT(SUM(".csaProductOrders.".quantity*".csaProducts.".current_price_in_euro),2) as costPerProduct
										FROM ".csaProducts." LEFT JOIN ".csaProductOrders." ON ".csaProducts.".id = ".csaProductOrders.".product_id
										WHERE 
											".csaProductOrders.".delivery_id = %d
											AND ".csaProducts.".producer = %d
										GROUP BY ".csaProductOrders.".product_id 
									", $deliveryID, $producer));
									
			//display product details
			foreach ($products_in_order as $product) 
			{
				$ordersOfProduct = $wpdb->get_results($wpdb->prepare("
											SELECT 
												".csaProductOrders.".user_id, 
												".csaProducts.".name,
												".csaProducts.".variety,
												".csaProductOrders.".quantity
											FROM ".csaProducts." LEFT JOIN ".csaProductOrders." ON ".csaProducts.".id = ".csaProductOrders.".product_id
											WHERE 
												".csaProductOrders.".delivery_id = %d AND
												".csaProducts.".id = %d
										", $deliveryID, $product->	id));
				
				$total = $product->total;
				if (strpos($total, '.') !== FALSE )
					$total = number_format((float)$product->total, 1, '.', '');
				?>
				<div class='container' style='min-width:100%;width:<?php echo $tableWidth ?>px;font-size:14px'>  <!--keeps the quantity and total value divs together-->
					
						<div class='container' style='float:left; width:<?php echo $mainWidth ?>px;'> 
							<!--product details-->
							
							<div class='left' style='width:<?php echo $product_details_width ?>px; background-color:Khaki; display:block; '>
								<div style='display:table-cell; vertical-align:middle; height:70px; padding-left:10px '>
								<?php echo $product->name." ".$product->variety."<br/> <span class='info-text'>(".$product->current_price_in_euro." € / ".$product->measurement_unit.")</span>" ?>
								</div>
							</div>
							
							<div class='left' style='width:<?php echo $amount_perProduct_width ?>px;height:70px;line-height:70px;padding-left:10px;font-weight:bold;font-size:16px;background-color:Khaki'>
								<span style='display:inline-block; vertical-align:middle;'><?php echo $total ?></span>
							</div>

							<!--order details-->
							<div class="div_tr">
								<?php
								foreach($ordersOfProduct as $productOrder) 
									echo "<div class='left' style='font-weight:bold;'>".mb_substr(get_user_by('id', $productOrder->user_id)->user_login, 0, 7,'UTF-8')."</div>"; 
								?>
							</div>
								<!--<div style="height:37px;"></div>-->
							<div class="div_tr">
								<?php
								foreach($ordersOfProduct as $preOrder) 
									echo "<div class='left' >".$productOrder->quantity."</div>"; 
								?>
							</div>
													
						</div>
					
						<!-- price details-->
						<div class='left' style='display:table; height:70px;'>
							<div style='display:table-cell; vertical-align:bottom;'>
							<?php echo "<span class='info-text'>".$product->costPerProduct."€</span>"; ?>
							</div>
						</div>
					
					
				</div>
				
				<?php $revenue += $product->costPerProduct;
			}
			
			echo"<br/><p class='total' style='margin: -5% 0 3% 0'><span class='emphasis-box'>Total: ".$revenue." € </span></p><br/>";
		}
	
		?>
		<table style="margin-top:30px" class='table-straight'> <!-- stucture table (to display the following table next to each other -->
			<tr><td>
				<span style="font-weight:bold;color:green">Cost per consumer</span><?php
				
				//Show costs in total
				$total_user_costs = $wpdb->get_results($wpdb->prepare("
												SELECT 
													".csaProductOrders.".user_id,
													FORMAT(SUM(".csaProductOrders.".quantity*".csaProducts.".current_price_in_euro),2) AS cost
												FROM ".csaProducts." LEFT JOIN ".csaProductOrders." ON ".csaProducts.".id = ".csaProductOrders.".product_id
												WHERE 
													".csaProductOrders.".delivery_id = %d
												GROUP BY ".csaProductOrders.".user_id
											", $deliveryID));
				$total_cost = $wpdb->get_var($wpdb->prepare("
												SELECT FORMAT(SUM(".csaProductOrders.".quantity*".csaProducts.".current_price_in_euro),2)
												FROM ".csaProducts." LEFT JOIN ".csaProductOrders." ON ".csaProducts.".id = ".csaProductOrders.".product_id
												WHERE 
													".csaProductOrders.".delivery_id = %d
											", $deliveryID));
				?>
				<table style='width:200px;' class='table-bordered'>
					<thead>
						<tr>
							<td>Consumer</td>
							<td>Total Cost</td>
						</tr>
					</thead>
					
					<?php 
					foreach ($total_user_costs as $tCost) { 
					?>
						<tr>
							<td style='width:100px;font-weight:bold'><?php echo get_user_by('id', $tCost->user_id)->user_login ?></td>
							<td><?php echo round($tCost->cost, 1)." €"; ?></td>
						</tr>
					<?php 
					} 
					?>

					<tr style='background-color:Khaki'>
						<td style="font-weight:bold">Total</td>
						<td style="font-weight:bold"><?php echo round($total_cost,1)." €"; ?></td>
					</tr>
					</tr>
				</table>
			</td>
			<td> <div style='width:80px'>  </div> </td>
			<td>
				<?php	
				
				//Show costs per producer
				?><span style="font-weight:bold;color:green;margin-left:5%">Cost per producer</span><?php
				$producer_costs = $wpdb->get_results($wpdb->prepare("
															SELECT 
																".csaProducts.".producer,
																FORMAT(SUM(".csaProductOrders.".quantity*".csaProducts.".current_price_in_euro),2) AS cost
															FROM 
																(".csaProducts." LEFT JOIN ".csaProductOrders." ON ".csaProducts.".id = ".csaProductOrders.".product_id)
															WHERE 
																".csaProductOrders.".delivery_id = %d
															GROUP BY ".csaProducts.".producer
														", $deliveryID));
				
						
				?>


				<table style='width:200px;' class='table-bordered'>
					<thead>
						<tr>
							<td>Producer</td>
							<td style='width:100px'>Income</td>
						</tr>
					</thead>
					<?php
					foreach ($producer_costs as $pCost) {
						echo "
							<tr>
								<td style='width:100px;font-weight:bold'>".$producersMap[$pCost->producer]."</td>
								<td>". round($pCost->cost, 1)." €</td>
							</tr>";
					
						}
					?>

					<tr style='background-color:Khaki'>
						<td style="font-weight:bold">Σύνολο</td>
						<td style="font-weight:bold"><?php echo round($total_cost,1)." €" ?></td>
					</tr>
				</table>

			</td>
			</tr>
		</table>
								
	<?php
	}
	?>
	<input 
	type="button"
	class="button button-secondary"
	value='back to orders...'
	onclick='window.location.replace(" <?php echo $pageURL ?> ");'
	/>	

	
<?php
}


add_action( 'wp_ajax_csa-wp-plugin-add_new_or_update_order', 'CsaWpPluginAddNewOrUpdateOrder' );

function CsaWpPluginAddNewOrUpdateOrder() {

	if( isset($_POST['data']) && isset($_POST['deliveryID']) && isset($_POST['userID']) ) {

		$deliveryID = intval(clean_input($_POST['deliveryID']));
		$userID = intval(clean_input($_POST['userID']));
		$data = json_decode(stripslashes($_POST['data']),true);
		$numOfProducts = count($data);

		//insert product orders
		$success = true;
		for ($i=0; $i<$numOfProducts-1; $i+=3) {
			$productID = $data[$i+2]['value'];
			$quantity = $data[$i+1]['value'];
			
			if($quantity>0){								
				global $wpdb;

				$productOrderInfo = $wpdb->get_results($wpdb->prepare("
								SELECT *
								FROM ".csaProductOrders."
								WHERE 
									delivery_id = %d AND
									user_id = %d AND
									product_id = %d
							",$deliveryID, $userID, $productID));
				
				if (count($productOrderInfo) > 0) {
					if ($wpdb->update(
							csaProductOrders,
							array('quantity' => $productOrderInfo[0]->quantity + $quantity), 
							array(
								'delivery_id' => $deliveryID,
								'user_id' => $userID,
								'product_id' => $productID
							)
					) === false)
						$success = false;
				}
				else if( $wpdb->insert(
							csaProductOrders,
							array(
								'delivery_id'	=> $deliveryID,
								'user_id'		=> $userID,
								'product_id' 	=> $productID,
								'quantity'		=> $quantity
							), 
							array ("%d", "%d", "%d", "%d")
					) === FALSE)
						$success = false;
			}
		}
		
		if ($success === false) 
			echo 'error, some sql request failed';
		else echo 'success, product orders added.';
		
		
		$alreadyExist = $wpdb->get_var($wpdb->prepare("
				SELECT COUNT(delivery_id) 
				FROM ".csaUserOrders."
				WHERE 
					delivery_id = %d AND
					user_id = %d
			",$deliveryID, $userID));
		
		//insert user order
		if($alreadyExist == 0) {
			if ($wpdb->insert(
					csaUserOrders,
					array(
						'delivery_id'	=> $deliveryID,
						'user_id'		=> $userID,
					), 
					array ("%d", "%d")
			) === FALSE)
				echo 'error, sql request failed';
			else echo 'success, user order added.';
		}
	}
	else echo 'error,Bad request.';

	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_csa-wp-plugin-update_user_order_product_quantity', 'CsaWpPluginUpdateUserOrderProductQuantity' );

function CsaWpPluginUpdateUserOrderProductQuantity() {
	if(isset($_POST['value']) && isset($_POST['deliveryID']) && isset($_POST['userID']) && isset($_POST['productID'])) {
		$new_value = clean_input($_POST['value']);
		$deliveryID = intval(clean_input($_POST['deliveryID']));
		$userID = intval(clean_input($_POST['userID']));
		$productID = intval(clean_input($_POST['productID']));
		
		if(!empty($deliveryID) && !empty($userID) && !empty($productID)) {
			// Updating the information 
			global $wpdb;

			if(	$wpdb->update(
				csaProductOrders,
				array('quantity' => $new_value), 
				array(
					'delivery_id' => $deliveryID,
					'user_id' => $userID,
					'product_id' => $productID
				)
			) === FALSE) 
				echo 'error, sql request failed';												
			else echo 'success,'.$new_value;
		} 
		else echo 'error,Empty values: ';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_csa-wp-plugin-delete_user_order_product', 'CsaWpPluginDeleteUserProductOrder' );

function CsaWpPluginDeleteUserProductOrder() {
	if(isset($_POST['deliveryID']) && isset($_POST['userID']) && isset($_POST['productID'])) {
		$deliveryID = intval(clean_input($_POST['deliveryID']));
		$userID = intval(clean_input($_POST['userID']));
		$productID = intval(clean_input($_POST['productID']));
		
		if(!empty($deliveryID) && !empty($userID) && !empty($productID)) {
			// Updating the information 
			global $wpdb;

			if(	$wpdb->delete(
				csaProductOrders,
				array(
					'delivery_id' => $deliveryID,
					'user_id' => $userID,
					'product_id' => $productID
				),
				array ('%d', '%d', '%d')
			) === FALSE) 
				echo 'error, sql request failed';												
			else echo 'success';
		} 
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_csa-wp-plugin-delete_user_order', 'CsaWpPluginDeleteUserOrder' );

function CsaWpPluginDeleteUserOrder() {
	if(isset($_POST['deliveryID']) && isset($_POST['userID'])) {
		if(!empty($_POST['deliveryID']) && !empty($_POST['userID'])) {
			global $wpdb;

			$success = true;
			
			if(	$wpdb->delete(
					csaProductOrders,
					array(
						'delivery_id' => $_POST['deliveryID'],
						'user_id' => $_POST['userID']
					),
					array ('%d', '%d')
				) === FALSE) 
					$success = false;
			
			if(	$wpdb->delete(
					csaUserOrders,
					array(
						'delivery_id' => $_POST['deliveryID'],
						'user_id' => $_POST['userID']
					),
					array ('%d', '%d')
				) === FALSE) 
					$success = false;

			if ($success === true) echo 'success, user order deleted';
			else echo 'error, sql request failed';												
		} 
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

?>