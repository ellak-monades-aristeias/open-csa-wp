<?php

function CsaWpPluginUserOrder($user = NULL) { 
	wp_enqueue_script('CsaWpPluginScripts');
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');

?>	
	<script type="text/javascript">
	var $j = jQuery.noConflict();
	$j(document).ready(function() {
	  $j('.tip').cluetip({
		splitTitle: '|',							 
		showTitle: false,
		hoverClass: 'highlight',
		open: 'slideDown', 
		openSpeed: 'slow'
	  });
	});
	</script>
<?php

	if (!$user) $user = wp_get_current_user();
	
	// for security reasons
	if (!($user instanceof WP_User) )
		return;
		
	// ============== THIS SHOULD BE UPDATED ==================
	//$current_date_string = current_time('mysql');
	//$current_date = new DateTime($current_date_string);
	$current_date = current_time('mysql');
	//$delivery_day = $wpdb->get_var("SELECT pref_value FROM $prefs WHERE pref_name='delivery_day'" ); //eg. "Monday"
	$delivery_day = "Monday";
	//$nextDelivery_date = new DateTime( date('Y-m-d', strtotime("Next ".$delivery_day)) );	
	//$last_delivery_date = $wpdb->get_var("SELECT pref_value FROM $prefs WHERE pref_name='last_delivery_date'");
	$date_format = "Y-m-d";
	$time_format = "H:i:s";
	$last_delivery_date = date( "{$date_format} {$time_format}", strtotime($current_date.' -1 day'));
	//$check = $wpdb->get_results("SELECT type FROM $ords WHERE user_login='".$current_user->user_login."' AND date BETWEEN '".$last_delivery_date."' AND CURDATE()", ARRAY_N);
	
	$userHasOrder = false;
	global $wpdb;
	$check = $wpdb->get_results("SELECT type FROM ". csaOrders ." WHERE user_login='". $user->user_login ."' AND date BETWEEN '".$last_delivery_date."' AND NOW()", ARRAY_N);
	if($wpdb->num_rows > 0)
		$userHasOrder = true;
	
	//...if the current user has placed an order before the delivery date, redirect to "my order" page
	//if ( $current_date < $nextDelivery_date && has_order($current_user) ) {
	if ( $userHasOrder == true )  CsaWpPluginShowEditableOrder($user, $last_delivery_date);
	else CsaWpPluginShowNewOrderForm($user);
	
}
add_shortcode('csa-wp-plugin-myPersonalOrder', 'CsaWpPluginUserOrder');



function CsaWpPluginShowNewOrderForm($user) {
	echo "<div id='csa-wp-plugin-submitOrder_formHeader' style='display:block'><span style='cursor:pointer' id='csa-wp-plugin-submitOrder_formHeader_text' onclick='CsaWpPluginToggleForm(\"submitOrder\",\"Add new order\", \" form\")'><font size='4'>Add new order (show form)</font></span></div>";
	echo "<div id='csa-wp-plugin-submitOrder_div' style='display:none'>";
	//echo "<br/>";
	echo "
		<span 
			class='tip' 
			title=' 
				Επιλέξτε την ποσότητα από κάθε προϊόν που επιθυμείτε και πιέστε \"Καταχώρηση\" στο κάτω μέρος της σελίδας.
				| Ύστερα από την καταχώρηση της παραγγελίας, μπορείτε να την επεξεργαστείτε έως τη μέρα και ώρα που έχει συμφωνηθεί η οριστικοποίησή της.
				| Δίπλα από κάθε προϊόν, στο πεδίο \"info\" εμπεριέχονται πληροφορίες για το κάθε προϊόν.
			'>
			<p><i> διαβάστε εδώ πληροφορίες για την καταχώρηση της παραγγελίας </i></p>
		</span>";

	CsaWpPluginShowOrderForm($user);
	echo "</div>";
}


function CsaWpPluginShowAdditionalProductOrdersForm($user) {
	echo "<div id='csa-wp-plugin-submitOrder_formHeader' style='display:block'><span style='cursor:pointer' id='csa-wp-plugin-submitOrder_formHeader_text' onclick='slow_hideshow_submitOrderForm(\"Add more products\")'><font size='4'>Add more products (show form)</font></span></div>";
	echo "<div id='csa-wp-plugin-submitOrder_div' style='display:none'>";
	echo "<br/>";	
	echo "
		<span 
			class='tip' 
			title=' 
				Επιλέξτε την ποσότητα από κάθε προϊόν που επιθυμείτε και πιέστε \"Καταχώρηση\" στο κάτω μέρος της σελίδας.
				| Δίπλα από κάθε προϊόν, στο πεδίο \"info\" εμπεριέχονται πληροφορίες για το κάθε προϊόν.
			'>
			<p style=\"width:36em;text-align:right\">
				<i> διαβάστε εδώ πληροφορίες για την παραγγελία επιπλέον προϊόντων </i>
			</p>
		</span>";

	CsaWpPluginShowOrderForm($user);
	echo "</div>";
}

function CsaWpPluginShowOrderForm($user) {
	//list categories as headers
	global $wpdb;
	$categories = $wpdb->get_results("SELECT DISTINCT category FROM ".csaProducts);
	foreach ($categories as $cat)
			echo "(<a href='#".$cat->category."'>".$cat->category."</a>)  " ;
	?>
	<br/>
	
	<FORM name='csa-wp-plugin-sumbitOrder_form' id='csa-wp-plugin-sumbitOrder_form' accept-charset='utf-8' method='post'> 
		<table id='ordersArray' class='table-bordered'> 
			<thead class="tableHeader"><tr><th>είδος</th><th>ποικιλία</th><th>τιμή</th><th>μονάδα</th><th>ποσότητα</th><th>παραγωγός</th><th> </th> <th class='th-hidden'></th></tr></thead>
			<?php
			foreach ($categories as $cat) {
				//echo "<tr><td><strong>".$cat->category."</strong></td></tr>";
				echo "<tr><td><strong> <a name='".$cat->category."' class='anchored'></a> <span class='emphasis-box' style='margin-left:-5px;'>".$cat->category."</span></strong></td></tr>";
				$products = $wpdb->get_results( $wpdb->prepare("SELECT id,type,variety,price,unit,producer,details,available FROM ".csaProducts." WHERE category='%s' ORDER BY type", $cat->category ));
				foreach($products as $row) {
					if($row->available == 'true') {
						echo "
						<tr>
						<td><span>".$row->type."</span></td>
						<td><span>".$row->variety."</span></td>
						<td><input type='number' readonly = 'readonly' name='csa-wp-plugin-order_productPrice' value='".$row->price."' style=' width: 5em; border:none; background-color:white; text-align: center'/></td>
						<td>€/".$row->unit."</td>
						<td><input type='number' min='0' step='0.5' name='csa-wp-plugin-order_productQuantity' onchange='CsaWpPluginCalcNewOrderCost()' onkeyup='CsaWpPluginCalcNewOrderCost()' style='width:70px;background-color:LightGoldenRodYellow'></td>
						<td>".$row->producer."</td>";
						if ($row->details != '')
							echo "<td style='text-align:center'><span class='tip' title='|".$row->details."'>info</span></td>";
						echo "<td class='td-hidden'><input type='number' name='csa-wp-plugin-order_productID' value='".$row->id."' style='visibility:hidden'/></td>
						</tr>";
					}
				}
			}
			?>
			<TR style='background-color:#d0e4fe;'><TD><span id='csa-wp-plugin-totalCalc'/></TD></TR>
		</table>  
	
<!--		<div style="margin-top:2%">
			<p><h6><span class='tip' title='Προσθέστε σημειώσεις στην παραγγελία σας, εάν θέλετε κάτι να ληφθεί υπόψιν. Οι σημειώσεις σας θα εμφανίζονται στη συνολική παραγγελία'>
			Σημειώσεις</span></h6></p>
			<textarea name="notes" id="notesArea" cols="50" rows="3" maxlength="500" class='info-text'></textarea>
		</div> -->
		
		<input type='button' value='Καταχώρηση' style="margin-top:3%" onclick="CsaWpPluginRequestSumbitOrderToServer('<?php echo $user->user_login; ?>', this)"/> 
	</FORM>
		
	<?php
}

add_action( 'wp_ajax_csa-wp-plugin-check_spotName_validity', 'CsaWpPluginAddNewOrder' );


add_action( 'wp_ajax_csa-wp-plugin-add_new_order', 'CsaWpPluginAddNewOrder' );

function CsaWpPluginAddNewOrder() {

	$data = json_decode(stripslashes($_POST['data']),true);
	$numOfProducts = count($data);
	$hasOrder = false;
	
	for ($i=0; $i<$numOfProducts-1; $i+=3) {
		$productID = $data[$i+2]['value'];
		$quantity = $data[$i+1]['value'];
		
		if($quantity>0){
			$hasOrder = true;
			
			echo current_time('Y-m-d');
			
			//get product info from db
			global $wpdb;
			$products = $wpdb->get_results( "SELECT id,variety,price,unit FROM ".csaProducts." WHERE id=$productID");
			
			//insert product order (query)
			if(	$wpdb->insert(
					csaOrders,
					array(
						'user_login'	=> $_POST['user_login'],
						'product_id' 	=> $productID,
						'type' 			=> 'oti na nai',
						'variety' 		=> 'oti na nai leme',
						'price'			=> 100,
						'unit'			=> 'τεμάχιο',
						'date'			=> current_time('mysql'),
						'quantity'		=> $quantity
					), 
					array ("%s", "%s", "%s", "%d", "%s", "%d")
			) === FALSE)
				echo "<span style='color:red'>Κάτι δε δούλεψε σωστά. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή </span>";												
			else
				echo 'success, product order added.';
		}
	}

	if($hasOrder > 0) {
		
		//insert notes (if any)
		//$notes = $_POST["notes"];
		//if($notes != null) ;
			//$rowsAffected = $wpdb->query("INSERT INTO $ordNotes (`note_id`,`user_login`,`note`,`date`) 
			//							  VALUES (NULL,'".$current_user->user_login."','".$notes."',CURDATE())");

		//Redirect to the specified page	????? Where does this redirect ?????
//		$bloginfo = get_site_url();
//		header("Location:".$bloginfo."/?page_id=103"); //?page_id=125
	}		
//	else									????? Where does this redirect> ?????
//		header("Location:".$bloginfo."/?page_id=203"); 


	wp_die(); 	// this is required to terminate immediately and return a proper response

}

function CsaWpPluginShowEditableOrder($user, $last_delivery_date) { 
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui');
?>
	<script type="text/javascript">
	
	var $j = jQuery.noConflict();	

	// edit user product order quantity
	$j(document).ready(function() {
		var table = $j("#csa-wp-plugin-showUserOrder_table");
		var oTable = table.dataTable({
			"bPaginate": false, 
			"bStateSave": true, 
			"bInfo": false, 
			"bFilter": false,
			"bSort" : false
		});

		var dataEditable = {
			"width" : "5em",
			"height": "3em",
			"type" : "text",
			//"submit" : "<img src='" + "<?php echo plugins_url(); ?>" + "/csa-wp-plugin/icons/ok.png'>",
			//"cancel" : "<img src='" + "<?php echo plugins_url(); ?>" + "/csa-wp-plugin/icons/cancel.png'>",
			"tooltip": "click to change...",
			"onblur": "cancel",
			"loadtype": 'POST',
		};
		
		//edit any value of any object (of class .editable)
		$j(".editable_product_order_quantity", oTable.fnGetNodes()).editable(
			function(value, settings) { 
				var tmp = this;
			
				var dataPost = {
					"action" : "csa-wp-plugin-update_user_order_product_quantity",
					"value" : value,
					"productOrderID": this.parentNode.getAttribute("id").split('_')[1]
				};
				$j.post(ajaxurl, dataPost, 
					function(response) { 
						//console.log ("Server returned:["+response+"]");
						
						//var aPos = oTable.fnGetPosition(tmp);
						//oTable.fnUpdate(value, aPos[0], aPos[1]);	
					
						if (value == 0)	CsaWpPluginRequestDeleteProductOrder(tmp);
						else CsaWpPluginCalcEditableOrderCost(tmp);
					}
				);
				return(value);
			}, 
			dataEditable
		);	
	});
	
	</script>	

	<span  class='tip' title='Για να επεξεργαστείτε οποιαδήποτε ποσότητα, πιέστε τον αριθμό της, αλλάξτε τον και πατήστε ENTER.
	|Για να διαγράψετε ένα προϊόν από την παραγγελία, πιέστε το εικονίδιο στο τέλος της γραμμής.'><p style="width:36em;text-align:right"><i>διαβάστε εδώ πληροφορίες για την αλλαγή της παραγγελίας</i></p>
	</span>
	
	
	<table class='table-bordered' id="csa-wp-plugin-showUserOrder_table"> 
	<thead class="tableHeader">
		<tr>
			<th>ποσότητα</th>
			<th	style="text-align:center">είδος</th>
			<th>ποικιλία</th>
			<th>€</th>
			<th>ανά</th>
			<th>κοστος</th>
			<th>Παραγωγός</th>
			<th></th>
			<th></th>
		</tr>
	</thead> 
	<tbody> 
	<?php
		//show an editable version of the user's order	
		global $wpdb;
		$ord_products = $wpdb->get_results("SELECT ". csaOrders. ".id,". csaOrders. ".type,". csaOrders. ".variety,". csaOrders. ".price,". csaOrders. ".unit,". csaOrders. ".quantity, ". csaProducts. ".producer, ". csaProducts. ".details 
											FROM ". csaOrders. ", ". csaProducts. " 
											WHERE user_login='".$user->user_login."' AND ". csaProducts. ".id = ". csaOrders. ".product_id AND date BETWEEN '".$last_delivery_date."' AND NOW()" );
		
		$totalCost = 0;
		$pluginsDir = plugins_url();
		foreach($ord_products as $row) {
			$pCost = $row->price * $row->quantity;
			$productOrderID = $row->id;
			echo "
			<tr class='csa-wp-plugin-user-order-product' id='csa-wp-plugin-productOrderID_$productOrderID'>			
				<td id='quantity$productOrderID' class='editable_product_order_quantity' style='text-align:center'>$row->quantity</td>
				<td>".$row->type."</TD>
				<td>".$row->variety."</td>
				<td class='editable_product_order_price'>".$row->price."</td>
				<td>".$row->unit."</td>
				<td class='editable_product_order_cost' style='text-align:center;font-weight:bold'> $pCost €</td>
				<td>".$row->producer."</td>";	
				if ($row->details != '')
					echo "<td style='text-align:center'><span class='tip' title='|".$row->details."'>info</span></td>";			
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
				<td><div>Σύνολο: <span id='editable_product_order_TotalCost' style='font-weight:bold'><?php echo round($totalCost,1). " €" ?></span></div></td>
				<td><div onclick="CsaWpPluginSlideToggle(document.getElementById('cancel_div'))"> 
						<img src="<?php echo plugins_url(); ?>/csa-wp-plugin/icons/delete.png"/ height="24" width="24"> &nbsp
						<span class='showHide_div' style='color:OrangeRed;cursor:pointer'>Ακύρωση παραγγελίας</span> 
					</div></td></tr></tbody></table>
	
	<div id='cancel_div' style='display:none; '> <br/>
		<button type="button" onclick='CsaWpPluginRequestDeleteUserOrder("<?php echo $user->user_login; ?>", "<?php echo $last_delivery_date ?>")'>ΝΑΙ... ακύρωση</button>
		<button type="button" style='background-color:orange' onclick="CsaWpPluginSlideToggle(document.getElementById('cancel_div'))">Ουπς... λάθος</button>
	</div>
	<br/>
	<?php CsaWpPluginShowAdditionalProductOrdersForm($user); 
}
//add_shortcode('orderVerification', 'verifyOrder');

add_action( 'wp_ajax_csa-wp-plugin-update_user_order_product_quantity', 'CsaWpPluginUpdateUserOrderProductQuantity' );

function CsaWpPluginUpdateUserOrderProductQuantity() {
	if(isset($_POST['value']) && isset($_POST['productOrderID'])) {
		$new_value = clean_input($_POST['value']);
		$productOrderID = intval(clean_input($_POST['productOrderID']));
		if(!empty($productOrderID)) {
			// Updating the information 
			global $wpdb;

			if(	$wpdb->update(
				csaOrders,
				array('quantity' => $new_value), 
				array('id' => $productOrderID )
			) === FALSE) 
				echo '<span style="color:red">Κάτι δε δούλεψε σωστά. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή.</span>';												
			else echo 'success,'.$new_value;
		} 
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_csa-wp-plugin-delete_user_order_product', 'CsaWpPluginDeleteUserProductOrder' );

function CsaWpPluginDeleteUserProductOrder() {
	if(isset($_POST['productOrderID'])) {
		$productOrderID = intval(clean_input($_POST['productOrderID']));
		if(!empty($productOrderID)) {
			// Updating the information 
			global $wpdb;

			if(	$wpdb->delete(
				csaOrders,
				array('id' => $productOrderID ),
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

add_action( 'wp_ajax_csa-wp-plugin-delete_user_order', 'CsaWpPluginDeleteUserOrder' );

function CsaWpPluginDeleteUserOrder() {
	if(isset($_POST['userLogin']) && isset($_POST['lastDeliveryDate'])) {
		$userLogin = clean_input($_POST['userLogin']);
		$lastDeliveryDate = clean_input($_POST['lastDeliveryDate']);
		if(!empty($userLogin) && !empty($lastDeliveryDate)) {
			global $wpdb;

			$userOrdersToDelete = $wpdb->get_results("
				SELECT id 
				FROM ". csaOrders."
				WHERE user_login='".$userLogin."' AND date BETWEEN '".$lastDeliveryDate."' AND NOW()"
			);

			$success = true;
			foreach ($userOrdersToDelete as $productOrderID) {
				echo "deleting order with id [".$productOrderID->id."]";
				if(	$wpdb->delete(
					csaOrders,
					array('id' => $productOrderID->id),
					array ('%d')
				) === FALSE) 
					$sucsess = false;
			}
			if ($success) echo 'success';
			else echo '<span style="color:red">Κάτι δε δούλεψε σωστά. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή.</span>';												
		} 
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

function CsaWpPluginShowTotalUserOrdersForDelivery () {
	global $wpdb;

	//$last_delivery_date = $wpdb->get_var("SELECT pref_value FROM $prefs WHERE pref_name='last_delivery_date'");
	$current_date = current_time('mysql');
	$date_format = "Y-m-d";
	$time_format = "H:i:s";
	$last_delivery_date = date( "{$date_format} {$time_format}", strtotime($current_date.' -1 day'));
	$producers_involved = $wpdb->get_col($wpdb->prepare("SELECT distinct ".csaProducts.".producer FROM ".csaOrders.", ".csaProducts." WHERE ".csaProducts.".id = ".csaOrders.".product_id  AND date BETWEEN %s AND NOW()", $last_delivery_date));
	$producers_count = count($producers_involved);

	if($producers_count <= 0)
		echo "<span class='info-text' style='font-size:15px'>Δεν έχει καταχωρηθεί ακόμη καμία παραγγελία. </span> <br><br>";
	else
	{
		global $site_url;
		echo "<span class='info-text' style='font-size:15px'>Παραγγελίες: </span>";
		$users_count = $wpdb->get_var($wpdb->prepare("SELECT count(DISTINCT user_login) AS usersCount FROM ".csaOrders." WHERE date BETWEEN %s AND NOW()", $last_delivery_date));
		$product_details_width = 130;
		$amount_perProduct_width = 30;
		$user_order_width = 58; 			//change also by css (class .left) ???? SHALL WE INCORPORATE THIS? ????
		$productValue_width = 70;
		$mainWidth = $product_details_width + $amount_perProduct_width + $user_order_width*$users_count + 30; // +30 is an offset to accomodate the space of the first orders
		$tableWidth = $mainWidth + $productValue_width;
				
		foreach ($producers_involved as $producer)
		{
			$revenue = 0; //the amount to be paid to this producer
			echo "<p class='panel'> <span style='font-size:14px'> Παραγωγός: </span> <span class='producer'>".$producer."</span> </p>";
			
			//Get the products in order for this producer
			$products_in_order = $wpdb->get_results($wpdb->prepare("SELECT ".csaOrders.".type,".csaOrders.".variety,".csaOrders.".price,".csaOrders.".product_id,".csaOrders.".unit,SUM(".csaOrders.".quantity) AS total, FORMAT(SUM(".csaOrders.".quantity*".csaOrders.".price),2) as costPerProduct
																	FROM ".csaOrders.", ".csaProducts."
																	WHERE ".csaProducts.".id = ".csaOrders.".product_id
																	  AND date BETWEEN %s AND NOW()
																	  AND ".csaProducts.".producer=%s
																	GROUP BY ".csaOrders.".product_id ", $last_delivery_date, $producer));
			//display product details
			foreach ($products_in_order as $product) 
			{
				$preOrders_ofProduct = $wpdb->get_results($wpdb->prepare("SELECT user_login,type,variety,quantity FROM ".csaOrders." WHERE date BETWEEN %s AND NOW() AND product_id=%d", $last_delivery_date, $product->product_id) );
				$total = $product->total;
				if (strpos($total, '.') !== FALSE )
					$total = number_format((float)$product->total, 1, '.', '');
				?>
				<div class='container' style='min-width:100%;width:<?php echo $tableWidth ?>px;font-size:14px'>  <!--keeps the quantity and total value divs together-->
					
					
						<div class='container' style='float:left; width:<?php echo $mainWidth ?>px;'> 
							<!--product details-->
							
							<div class='left' style='width:<?php echo $product_details_width ?>px; background-color:Khaki; display:block; '>
								<div style='display:table-cell; vertical-align:middle; height:70px; padding-left:10px '>
								<?php echo $product->type." ".$product->variety."<br/> <span class='info-text'>(".$product->price." € / ".$product->unit.")</span>" ?>
								</div>
							</div>
							
							<div class='left' style='width:<?php echo $amount_perProduct_width ?>px;height:70px;line-height:70px;padding-left:10px;font-weight:bold;font-size:16px;background-color:Khaki'>
								<span style='display:inline-block; vertical-align:middle;'><?php echo $total ?></span>
							</div>

							<!--order details-->
							<div class="div_tr">
								<?php
								foreach($preOrders_ofProduct as $preOrder) 
									echo "<div class='left' style='font-weight:bold;'>".mb_substr($preOrder->user_login, 0, 7,'UTF-8')."</div>"; 
								?>
							</div>
								<!--<div style="height:37px;"></div>-->
							<div class="div_tr">
								<?php
								foreach($preOrders_ofProduct as $preOrder) 
									echo "<div class='left' >".$preOrder->quantity."</div>"; 
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
				
				<?php	$revenue += $product->costPerProduct;
			}
			
			echo"<br/><p class='total' style='margin: -5% 0 3% 0'><span class='emphasis-box'>Σύνολο: ".$revenue." € </span></p><br/>";
		}
	
		?>
		<table style="margin-top:30px" class='table-straight'> <!-- stucture table (to display the following table next to each other -->
			<tr><td>
				<span style="font-weight:bold;color:green">Κόστος ανά μέλος</span><?php
				
				//Show costs in total
				$total_user_costs = $wpdb->get_results($wpdb->prepare("SELECT FORMAT(SUM(quantity*price),2) AS cost,user_login
																		FROM ".csaOrders."
																		WHERE date BETWEEN '%s' AND NOW()
																		GROUP BY user_login", $last_delivery_date));
				$total_cost = $wpdb->get_var($wpdb->prepare("SELECT FORMAT(SUM(quantity*price),2)
																	FROM ".csaOrders."
																	WHERE date BETWEEN %s AND NOW() ", $last_delivery_date));
				?>
				<table style='width:200px;' class='table-bordered'>
					<thead>
						<tr>
							<td>Μέλος</td>
							<td>Συνολικό Κόστος</td>
						</tr>
					</thead>
					
					<?php 
					foreach ($total_user_costs as $tCost) { 
					?>
						<tr>
							<td style='width:100px;font-weight:bold'><?php echo $tCost->user_login ?></td>
							<td><?php echo round($tCost->cost, 1)." €"; ?></td>
						</tr>
					<?php 
					} 
					?>

					<tr style='background-color:Khaki'>
						<td style="font-weight:bold">Σύνολο</td>
						<td style="font-weight:bold"><?php echo round($total_cost,1)." €"; ?></td>
					</tr>
					</tr>
				</table>
			</td>
			<td> <div style='width:80px'>  </div> </td>
			<td>
				<?php	
				
				//Show costs per producer
				?><span style="font-weight:bold;color:green;margin-left:5%">Κόστος ανά παραγωγό</span><?php
				$producer_costs = $wpdb->get_results($wpdb->prepare("SELECT producer, FORMAT(SUM(quantity*".csaOrders.".price),2) AS cost 
																	 FROM ".csaProducts.", ".csaOrders."
																	 WHERE ".csaProducts.".id = ".csaOrders.".product_id AND date BETWEEN %s AND NOW() 
																	 GROUP BY producer",$last_delivery_date));
				?>


				<table style='width:200px;' class='table-bordered'>
					<thead>
						<tr>
							<td>Μέλος</td>
							<td style='width:100px'>Έσοδα Παραγωγού</td>
						</tr>
					</thead>
					<?php
					foreach ($producer_costs as $pCost) {
						echo "
							<tr>
								<td style='width:100px;font-weight:bold'>".$pCost->producer."</td>
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
		
<!--		<br>			
		<table style='width:600px;margin-left:5%' class='table-bordered'>
		<?php /*
			$noteGroups = $wpdb->get_results($wpdb->prepare("SELECT user_login, GROUP_CONCAT(note SEPARATOR '| ') AS notes FROM $ordNotes WHERE date BETWEEN '%s' AND CURDATE() GROUP BY user_login", $last_delivery_date));
			if (count($noteGroups) > 0){
				echo "<span style='font-weight:bold;color:green;margin-left:5%'>Σημειώσεις</span>";
				foreach ($noteGroups as $noteGroup)
					echo "<tr><td style='font-weight:bold'>".$noteGroup->user_login."</td><td>".$noteGroup->notes."</td></tr>";
			}*/
		?>
		</table> -->
						
	<?php
	}
}
add_shortcode('csa-wp-plugin-showTotalOrders', 'CsaWpPluginShowTotalUserOrdersForDelivery');	


?>