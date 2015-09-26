<?php

/* ****************************************************
function that creates the form for adding a new product
******************************************************** */
function CsaWpPluginShowNewProductForm($productID, $display) { 
	
	wp_enqueue_script( 'CsaWpPluginScripts' );
	wp_enqueue_script( 'CsaWpPluginProductsScripts' );
	
	global $days,$wpdb;
	$productInfo;
	if ($productID != null) 
		$productInfo = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".csaProducts." WHERE id=%d", $productID));
?>

	<br/>
	<div id="csa-wp-plugin-addProduct_formHeader">
		<span 
			id="csa-wp-plugin-addProduct_formHeader_text" 
			<?php 
				if ($productID == null) {
					echo 'style="cursor:pointer"';
					echo 'onclick="CsaWpPluginToggleForm(\'addProduct\',\'Add New Product\', \' form\')"';
				}
			?>>
			<font size='4'>
			<?php 
			if ($productID == null) {
				if ($display == false) echo 'Add New Product (show form)';
				else echo 'Add New Product (hide form)';
			}
			else echo 'Edit Product #'.$productID;
			?>

			</font>
		</span>
	</div>
	<div id="csa-wp-plugin-addProduct_div" 
		<?php if ($display == false) echo 'style="display:none"' ?>	
	>
		<form method="POST" id='csa-wp-plugin-showNewProduct_form'>
			<table class="form-table">
				<tr valign="top">
					<td>
					<input 
						type='text' 
						<?php if ($productID != null && $productInfo[0]->name != "" && $productInfo[0]->name != null) echo "value='".$productInfo[0]->name."'"; ?>
						placeholder='Product Name *' 
						name="csa-wp-plugin-product_name_input" 
						required></td></tr>
				<tr valign="top"><td>
					<select 
						name="csa-wp-plugin-product_category_input" 
						id="csa-wp-plugin-newProductForm_category_input_id"
						<?php if ($productID == null) echo "style='color:#999'"?>
						onfocus = '
							getElementById("csa-wp-plugin-newProductForm_category_input_span_id").style.display = "none";
						'
						onchange = '
							this.style.color="black"
							if (this.options[this.selectedIndex].text.split(" ")[0] != "Category")
								this.options[this.selectedIndex].text = "Category is " + this.options[this.selectedIndex].text;
						'
					>
					<option 
						value="" 
						selected='selected' 
						disabled='disabled'
						id = "csa-wp-plugin-newProductForm_category_input_disabled_id"
					>Category *</option>
 					<?php echo CsaWpPluginSelectOptionsFromDB(
									array("name"), 
									"id", 
									csaProductCategories, 
									($productID != null)?$productInfo[0]->category:null,
									"Category is "
								); ?>
                  	</select>
					<span id="csa-wp-plugin-newProductForm_category_input_span_id"></span>
				</td></tr>
					
				<tr valign="top"><td>
					<select 
						name="csa-wp-plugin-product_producer_input"
						id="csa-wp-plugin-newProductForm_producer_input_id"
						onfocus = '
							getElementById("csa-wp-plugin-newProductForm_producer_input_span_id").style.display = "none";
						'					
						onchange = '
							this.style.color="black"
							if (this.options[this.selectedIndex].text.split(" ")[0] != "Producer")
								this.options[this.selectedIndex].text = "Producer is " + this.options[this.selectedIndex].text;
						'
						<?php if ($productID == null) echo "style='color:#999'"?>
					>
						<option 
							value="" 
							<?php if ($productID == null) echo "selected='selected'"?>
							disabled='disabled'
							id = "csa-wp-plugin-newProductForm_producer_input_disabled_id"
						>Producer *</option>
						<?php echo CsaWpPluginSelectProducers($productInfo[0]->producer, "Producer is "); ?>
					</select>
					<span id="csa-wp-plugin-newProductForm_producer_input_span_id"></span>
				</td></tr>

				<tr valign="top">
					<td>
					<input 
						type='text' 
						onfocus = ' 
							getElementById ("csa-wp-plugin-showNewProduct_button_id").disabled=true;
							if (this.value != "") this.value = (this.value.split(" ").slice(2)).join(" ");
						'
						onblur = '
							getElementById ("csa-wp-plugin-showNewProduct_button_id").disabled=false;
							if (this.value != "") this.value = "Variety is "+ this.value;
						'
						<?php if ($productID != null && $productInfo[0]->variety != "" && $productInfo[0]->variety != null) echo "value='Variety is ".$productInfo[0]->variety."'"; ?>
						placeholder='Variety *' 
						required 
						name="csa-wp-plugin-product_variety_input">
					</td>
				</tr>
				<tr valign="top">
					<td>
					<input 
						min='0' step='0.1'
						<?php 
							if ($productID != null && $productInfo[0]->current_price_in_euro != "" && $productInfo[0]->current_price_in_euro != null) {
								echo "type='text'";
								echo "style='width:8em; text-align:right'";
								echo 'value = "it costs '. $productInfo[0]->current_price_in_euro. '"';
								
							}
							else {
								echo "type='number'";
								echo "style='width:8em'";
							}
						?>
						placeholder='Price *' 
						onfocus = '
							getElementById ("csa-wp-plugin-showNewProduct_button_id").disabled=true;
							this.value = this.value.split(" ")[2];
							this.type = "number";
						'
						onblur = '
							getElementById ("csa-wp-plugin-showNewProduct_button_id").disabled=false;
							this.type = "text";
							if (this.value == "") this.style.textAlign="left";
							else {
								this.value = "It costs " + this.value;
								this.style.textAlign="right";
							}
						'
						name="csa-wp-plugin-product_price_input" required> € &nbsp;
					<select 
						name="csa-wp-plugin-product_unit_input" 
						id="csa-wp-plugin-newProductForm_unit_input_id"
						<?php if ($productID == null) echo "style='color:#999'"?>
						onfocus = '
							getElementById("csa-wp-plugin-newProductForm_unit_input_span_id").style.display = "none";
						'
						onchange = '
							this.style.color="black";
							if (this.options[this.selectedIndex].text.split(" ")[0] != "per")
								this.options[this.selectedIndex].text = "per " + this.options[this.selectedIndex].text;
					'>
						<option 
							value="" 
							<?php if ($productID == null) echo "selected='selected'"; ?>
							disabled='disabled'
							id = "csa-wp-plugin-newProductForm_unit_input_disabled_id"
						>per... *</option>
						<?php echo CsaWpPluginSelectMeasurementUnit($productID, $productInfo); ?>
					</select> 
					<span id="csa-wp-plugin-newProductForm_unit_input_span_id"></span>
				</td></tr>
				<tr valign="top">
					<td>
						<textarea placeholder='Description' rows="3" cols="30" name="csa-wp-plugin-product_descritpion_input"
						><?php if ($productID != null && $productInfo[0]->description != "" && $productInfo[0]->description != null) echo $productInfo[0]->description; ?></textarea></td></tr>


				<tr valign="top"><td>
					<select 
					name="csa-wp-plugin-product_availability_input" 
					id="csa-wp-plugin-newProductForm_availability_input_id"
					<?php 
						if ($productID == null) echo "style='color:#999'";
						else if ($productInfo[0]->isAvailable == 1) echo "style='color:green'";
						else echo "style='color:red'";
					?>
					onfocus = '
							getElementById("csa-wp-plugin-newProductForm_availability_input_span_id").style.display = "none";
						'
					onchange='
						if (this.options[this.selectedIndex].value == "yes") {
							this.style.color = "green";
							this.options[this.selectedIndex].text = "Currently, it is available"
						}
						else {
							this.style.color = "red";
							this.options[this.selectedIndex].text = "Currently, it not is available"
						}
						'
				>
					<option 
						value="" 
						<?php if ($productID == null) echo "selected='selected'"; ?>
						disabled='disabled'
						id = "csa-wp-plugin-newProductForm_availability_input_disabled_id"
					>Available? *</option>
					<?php 
						if ($productID != null) {
							echo '
								<option value="yes" style="color:green". '. ($productInfo[0]->isAvailable == 1?"selected='selected'> Currently, it is available":">yes") .' </option>
								<option value="no" style="color:red"'. ($productInfo[0]->isAvailable == 0?"selected='selected'> Currently, it is not available":">no") .' </option>
							';
						}
						else {
						?>
							<option value="yes" style="color:green">yes</option>
							<option value="no" style="color:red">no</option>
						<?php
						}
					?>					
					</select>
					<span id="csa-wp-plugin-newProductForm_availability_input_span_id"></span>
				</td></tr>
			</table> 
		<input 
			type="submit" 
			name="Add Product"  
			class="button button-primary"
			id="csa-wp-plugin-showNewProduct_button_id"
			<?php 
				if ($productID == null) {
					echo "value='Add Product'";
					echo "onclick='CsaWpPluginNewProductFieldsValidation(this, null, \"". admin_url("/admin.php?page=csa_management") ."\")'";
				}
				else { 
					echo "value='Update Product'";
					echo "onclick='CsaWpPluginNewProductFieldsValidation(this, $productID, \"". admin_url("/admin.php?page=csa_management") ."\")'";
				}
				
			?>
		/>
		<input 
			type="button"
			class="button button-secondary"
			<?php 
			if ($productID == null) 
				echo "
				value='Reset Info'
				onclick='CsaWpPluginResetProductForm();'";
			else echo "
				value='Cancel'
				onclick='window.location.replace(\"". admin_url('/admin.php?page=csa_management')."\")'
				'";
			?>
		/>
		
		</form>
		<br/><br/>
	</div>
	
<?php

}
add_shortcode('csa-wp-plugin-addNewProductForm', 'CsaWpPluginShowNewProductForm');

function CsaWpPluginSelectMeasurementUnit($productID, $productInfo) {
?>
	<option 
		value='kilogram'
		<?php
			if ($productID != null && $productInfo[0]->measurement_unit == "kilogram" ) 
				echo "selected='selected' >per kilogram"; 
			else echo ">kilogram";
		?>
	</option>
	<option 
		value='piece'
		<?php 
			if ($productID != null && $productInfo[0]->measurement_unit == "piece" ) 
				echo "selected='selected' >per piece"; 
			else echo ">piece";
		?>
	</option>
	<option 
		value='bunch'
		<?php 
			if ($productID != null && $productInfo[0]->measurement_unit == "bunch" ) 
				echo "selected='selected' >per bunch"; 
			else echo ">bunch";
		?>
	</option>
	<option 
		value='litre'
		<?php 
			if ($productID != null && $productInfo[0]->measurement_unit == "litre" ) 
				echo "selected='selected' >per litre"; 
			else echo ">litre";
		?>
	</option>
<?php
}


add_action( 'wp_ajax_csa-wp-plugin-product_add_or_update_request', 'CsaWpPluginAddOrUpdateProduct' );

function CsaWpPluginAddOrUpdateProduct() {

	if( isset($_POST['data']) && isset($_POST['productID'])) {

		$dataReceived = json_decode(stripslashes($_POST['data']),true);
		
		$varietyMessage = "Variety is ";
		$variety = substr($dataReceived[3]['value'], strlen($varietyMessage)); 
		$priceMessage = "it costs ";
		$price = substr($dataReceived[4]['value'], strlen($priceMessage)); 
		
		$dataVals = array(
					'name' 						=> $dataReceived[0]['value'],
					'category' 					=> $dataReceived[1]['value'],
					'producer' 					=> $dataReceived[2]['value'],
					'variety'					=> $variety,
					'current_price_in_euro'		=> $price,
					'measurement_unit'	 		=> $dataReceived[5]['value'],
					'description'				=> $dataReceived[6]['value'],
					'isAvailable' 				=> $dataReceived[7]['value'] == "yes"?1:0
				);
		$dataTypes = array ("%s", "%d", "%d", "%s", "%f", "%s", "%s", "%d");
		
		global $wpdb;
	
		$productID = intval(clean_input($_POST['productID']));
	
		if ($productID != null) {
			$productID = intval($productID);
			
			//update product (query)
			if(	$wpdb->update(
				csaProducts, 
				$dataVals, 
				array('id' => $productID), 
				$dataTypes
			) === FALSE) echo 'error, sql request failed.';
			
			else echo 'Success, product is updated.';
		
		}
		else { 
			//insert product (query)
			if(	$wpdb->insert(
				csaProducts, 
				$dataVals, 
				$dataTypes
			) === FALSE) echo 'error, sql request failed.';
			
			else echo 'Success, product is added.';
		}
	}
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

function CsaWpPluginShowProducts($display) {
	wp_enqueue_script('CsaWpPluginScripts');
	wp_enqueue_script('CsaWpPluginProductsScripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); ?>
		
	<br />
	<div id="csa-wp-plugin-showProductsList_header">
		<span 
			style="cursor:pointer" 
			id="csa-wp-plugin-showProductsList_formHeader_text" 
			onclick="CsaWpPluginToggleForm('showProductsList','Product List', '')">
			<font size='4'>
			<?php 
				if ($display == false) echo 'Product List (show)';
				else echo 'Product List (hide)'
			?>
			</font>
		</span>
	</div>
	<div id="csa-wp-plugin-showProductsList_div" 
		<?php if ($display == false) echo 'style="display:none"' ?>	
	>
		
		<span class='csa-wp-plugin-tip_products' title='
			If you want to update one among the name, variety, and description fields, click on it, write the new value, and then press ENTER.
			| To change the availilability of a product, you can either click on its field or press the "eye" icon.
			| If you want to edit all the product details, press the "pen" icon.
			| If you want to delete some product, press the "x" icon.
			'>
		<p style="color:green;font-style:italic; font-size:13px">
			By pointing here you can read additional information.</p></span>


		<table 
			class='table-bordered' 
			id="csa-wp-plugin-showProductsList_table" 
			style='border-spacing:1em'
			csa-wp-plugin-plugins_dir='<?php echo plugins_url(); ?>' 
		> 
		<thead class='tableHeader'>
			<tr>
				<th>Name</th>
				<th>Category</th>
				<th>Variety</th>
				<th>Price(€)</th>
				<th>Per...</th>
				<th>Producer</th>
				<th>Description</th>
				<th>Available?</th>
				<th/>
				<th/>
				<th/>
			</tr>
		</thead> 
		<tbody> <?php
			global $wpdb;
			$pluginsDir = plugins_url();

			$products = $wpdb->get_results("SELECT * FROM ". csaProducts);
			foreach($products as $row) 
			{
				$productID = $row->id;
				$categoryID = $row->category;
				$category = $wpdb->get_var($wpdb->prepare("SELECT name FROM ". csaProductCategories ." WHERE id=%d", $categoryID));
				$producerID = $wpdb->get_var($wpdb->prepare("SELECT producer FROM ". csaProducts ." WHERE id=%d", $productID));
				$producer = get_user_by('id', $producerID) -> user_login;
				
				echo "
					<tr 
						valign='top' 
						id='csa-wp-plugin-showProductsProductID_$productID'  
						class='csa-wp-plugin-showProducts-product'
						style='color:". (($row->isAvailable == '1')?"black":"gray") ."'
					>
					<td class='editable'>$row->name </td>
					<td>$category </td>
					<td class='editable'>$row->variety</td>
					<td>$row->current_price_in_euro</td>
					<td>$row->measurement_unit</td>
					<td	>$producer</td>
					<td class='editable'>$row->description</td>
					<td 
						class='editable_boolean'
						id = 'csa-wp-plugin-showProductsAvailabilityID_$productID'
					>".(($row->isAvailable == 1)?"yes":"no")."</td>
					<td style='text-align:center'><img 
							style='cursor:pointer' 
							src='".plugins_url()."/csa-wp-plugin/icons/".(($row->isAvailable == 1)?"visible":"nonVisible").".png' 
							height='24' width='24' 
							id = 'csa-wp-plugin-showProductsAvailabilityIconID_$productID'
							title='mark it as ".(($row->isAvailable == 1)?"unavailable":"available")."'
							onclick='CsaWpPluginRequestToggleProductVisibility(this,\"$pluginsDir\")'></td>
					<td style='text-align:center'> 
						<img 
							width='24' height='24'  
							class='delete no-underline' 
							src='$pluginsDir/csa-wp-plugin/icons/edit.png' 
							style='cursor:pointer;padding-left:10px;' 
							onclick='CsaWpPluginEditProduct(this, \"". admin_url('/admin.php?page=csa_management')."\")' 
							title='click to edit this product'/></td>
					<td style='text-align:center'> <img 
						style='cursor:pointer' 
						src='".plugins_url()."/csa-wp-plugin/icons/delete.png' 
						height='24' width='24'
						onmouseover='CsaWpPluginHoverIcon(this, \"delete\", \"$pluginsDir\")' 
						onmouseout='CsaWpPluginUnHoverIcon(this, \"delete\", \"$pluginsDir\")' 						
						onclick='CsaWpPluginRequestDeleteProduct(this)' 
						title='delete product'></td>
					</tr>

				";
						
			}
			?>
		</tbody> </table>
	</div>	
<?php
}
add_shortcode('csa-wp-plugin-showProductsList', 'CsaWpPluginShowProducts');

add_action( 'wp_ajax_csa-wp-plugin-update_product', 'CsaWpPluginUpdateProduct' );

function CsaWpPluginUpdateProduct() {
	if(isset($_POST['value']) && isset($_POST['column']) && isset($_POST['productID'])) {
		//$old_value = clean_input($_POST['old_val']);
		$new_value = clean_input($_POST['value']);
		$columnNum = intval(clean_input($_POST['column']))+1; //not valid for getting the right column, when html table structure differs from the relative db table
		$productID = intval(clean_input($_POST['productID']));
		if ($columnNum == 8) $new_value = ($new_value == "yes"?1:0);
		
		if(!empty($columnNum) && !empty($productID)) {
			// Updating the information 
			global $wpdb;
			//get csa_product's column names and assign them to an array
			$columns = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".csaProducts."' ORDER BY ORDINAL_POSITION", ARRAY_N);
			//update the database, using the relative column name
			$columnName = $columns[$columnNum][0];

			if(	$wpdb->update(
				csaProducts,
				array($columnName => $new_value), 
				array('id' => $productID )
			) === FALSE) 
				echo 'error, sql request failed.';												
			else echo 'success,'.$new_value;
		} 
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_csa-wp-plugin-update_product_availability', 'CsaWpPluginUpdateProductAvailability' );

 function CsaWpPluginUpdateProductAvailability() {
	if(isset($_POST['productID']) && isset($_POST['availability'])) {
		$productID = intval($_POST['productID']);
		$availability = $_POST['availability'];

		global $wpdb;		
		if(	$wpdb->update(
			csaProducts,
			array("isAvailable" => $availability), 
			array('id' => $productID)
		) === FALSE) 
			echo '<span style="color:red">Κάτι δε δούλεψε σωστά. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή ,['.$new_value.']['.$columnNum.']['.$id.']['.$columnName.']</span>';												
		else echo 'success, Availability has been updated.';
	} else {
		echo 'error,Invalid request made.';
	}
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_csa-wp-plugin-delete_product', 'CsaWpPluginDeleteProduct' );

function CsaWpPluginDeleteProduct() {
	if(isset($_POST['productID'])) {
		$productID = intval(clean_input($_POST['productID']));
		if(!empty($productID)) {
			// Updating the information 
			global $wpdb;

			if(	$wpdb->delete(
				csaProducts,
				array('id' => $productID ),
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
