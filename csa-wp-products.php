<?php
add_action( 'wp_ajax_csa-wp-plugin-add_new_product', 'CsaWpPluginAddNewProduct' );
	

/* ****************************************************
function that creates the form for adding a new product
******************************************************** */
function CsaWpPluginShowNewProductForm() { 
	
	wp_enqueue_script( 'CsaWpPluginScripts' );?>

	<br/>
	<div id="csa-wp-plugin-addProduct_formHeader"><span style="cursor:pointer" id="csa-wp-plugin-addProduct_formHeader_text" onclick="slow_hideshow_addNewProductForm()"><font size='4'>Add New Product (show form)</font></span></div>
	<div id="csa-wp-plugin-addProduct_ack" class="info-text" style="display:none"> Το προϊόν καταχωρήθηκε επιτυχώς</div>
	<div id="csa-wp-plugin-addProduct_div" style="display:none">
		<!-- SQL: Get Columns from csa_product table: SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'csaProducts' ORDER BY ORDINAL_POSITION -->
		<form method="POST" id='addProduct_form'>
			<table class="form-table">
				<tr valign="top"><td><input type='text' placeholder='Name' id='name_field' required value=""></td></tr>
				
				<tr valign="top"><td>Κατηγορία: &nbsp<select id='category_field' required>
 					 <?php echo CsaWpPluginSelectOptionsFromDB(array("name"),"pc_id","wp_csa_products_categories"); ?>
                  		</select> </td></tr>

				<tr valign="top"><td>Παραγωγός: &nbsp<input type='text' id='producer_field' ></td></tr>

				<tr valign="top"><td>Ποικιλία: &nbsp<input type='text' id='variety_field' ></td></tr>
				<tr valign="top"><td>Τιμή (σε Ευρώ): &nbsp<input type='number' min='0' step='0.1' id='price_field' required></td></tr>
				
				<tr valign="top"><td>Tιμή ανά.. &nbsp <select id='unit_field' required>
					<option value='kilogram'>κιλό</option>
					<option value='piece'>τεμ.</option>
					<option value='bunch'>μάτσο</option>
					<option value='litre'>λίτρο</option>
				</select> </td></tr>

				<tr valign="top"><td>Περιγραφή: &nbsp <textarea id="description_field" rows="3" cols="30"></textarea></td></tr>
				<tr valign="top"><td>Διαθέσιμο: <select id='available_field' required>
					<option value='true'>Ναι</option>
					<option value='false' selected>Όχι</option>
				</select></td></tr>
				<tr valign="top"><td>Ανταλλάξιμο: &nbsp<select id='exchangeable_field' required>
					<option value='true'>Ναι</option>
					<option value='false' selected>Όχι</option>
				</select></td></tr>
				<tr valign="top"><td>Ευπαθές: &nbsp<select id='frail_field' required>
					<option value='true'>Ναι</option>
					<option value='false' selected>Όχι</option>
				</select></td></tr>
			</table> 
		<input type="submit" name="Add Product" value="Add Product" class="button button-primary" onclick="submitProductsForm(this.parentNode)"/>
		</form>
		<div id="error"> </div>
		<br/><br/>
	</div>
	
<?php

}
add_shortcode('csa-wp-plugin-addNewProductForm', 'CsaWpPluginShowNewProductForm');


/* ******************************************
function that adds a new product 
ToDos
1. elegxos input pedion
2. to required den xtypaei pote
2. connect tables !!
********************************************* */
add_action( 'wp_ajax_csa-wp-plugin-add_new_product', 'CsaWpPluginAddNewProduct' );

function CsaWpPluginAddNewProduct() {

	if( isset($_POST['name']) && 
		isset($_POST['category']) &&
		isset($_POST['variety']) &&  
		isset($_POST['producer']) && 
		isset($_POST['price']) && 
		isset($_POST['unit']) && 
		isset($_POST['description']) && 
		isset($_POST['available']) &&
		isset($_POST['exchangeable']) &&
		isset($_POST['frail']) 
 )  
	{
		$name = clean_input($_POST['name']);
		$category = intval($_POST['category']);
		$variety = clean_input($_POST['variety']);
		$producer = intval($_POST['producer']);
		$price = floatval($_POST['price']);
		$unit = clean_input($_POST['unit']);
		$description = clean_input($_POST['description']);
		$available = (($_POST['available'] == 'true')?1:0);
		$exchangeable = (($_POST['exchangeable'] == 'true')?1:0);
		$frail = (($_POST['frail'] == 'true')?1:0);
		

		global $wpdb;
		if($wpdb->insert(csaProducts,array(
					'name' 		=> $name,
					'category' 	=> $category,
					'variety' 	=> $variety,
					'producer'	=> $producer,
					'current_price_in_euro' => $price,
					'measurement_unit' => $unit,
					'description'	=> $description,
					'isAvailable'	=> $available,
					'isExchangeable'=> $exchangeable,
					'isFrail'	=> $frail

				), 
				array ("%s", "%d", "%s", "%d", "%f", "%s", "%s", "%s", "%s", "%s")
		) === FALSE)
			echo "<span style='color:red'>Το προϊόν δεν καταχωρήθηκε. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή </span>";												
		else
			echo 'Success, product added!';
	} else 
		echo 'Error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}


/* ******************************************
function that shows all the products
********************************************* */
function CsaWpPluginShowProducts() {
	wp_enqueue_script('CsaWpPluginScripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); ?>
	
	<script type="text/javascript">
	var $j = jQuery.noConflict();	

	$j(document).ready(function() {
		var table = $j("#csa-wp-plugin-showProductsList_table");
		var oTable = table.dataTable({
			"bPaginate": false, 
			"bStateSave": true, 
			"bInfo": false, 
			"bFilter": true
		});

		var dataEditable = {
			"width" : "10em",
			"height": "3em",
			"type" : "text",
			//"submit" : "<img src='" + "<?php echo plugins_url(); ?>" + "/csa-wp-plugin/icons/ok.png'>",
			//"cancel" : "<img src='" + "<?php echo plugins_url(); ?>" + "/csa-wp-plugin/icons/cancel.png'>",
			"tooltip": "click to change...",
			"onblur": "cancel",
			"loadtype": 'POST',
		};
		
		//edit any value of any object (of class .editable)
		$j(".editable", oTable.fnGetNodes()).editable(
			function(value, settings) { 
				var tmp = this;
			
				var dataPost = {
					"action" : "csa-wp-plugin-update_product",
					"value" : value,
					"productID": this.parentNode.getAttribute("id"),
					"column": oTable.fnGetPosition(this)[2]			//???? why [2] and not [1]? - [0] describes the row, [1] describes the column, [2] describes again the column?, [3..] undefined
				};
				$j.post(ajaxurl, dataPost, 
					function(response) { 
						//console.log ("Server returned:["+response+"]");
						
						//var fetch = response.split(",");
						//var aPos = oTable.fnGetPosition(tmp);
						//oTable.fnUpdate(fetch[1], aPos[0], aPos[1]);
					}
				);
				return(value);
			}, 
			dataEditable
		);	
	});
</script> 
	
	<br />
	<div id="csa-wp-plugin-showProductsList_header"><span style="cursor:pointer" id="csa-wp-plugin-showProductsList_header_text" onclick="slow_hideshow_showProductsList()"><font size='4'>Products List (show)</font></span></div>
	<div id="csa-wp-plugin-showProductsList_div" style="display:none">
		<!-- SQL: Get Columns from csa_product table: SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'csa_product' ORDER BY ORDINAL_POSITION -->
		
		<span style="color:green;font-style:italic; font-size:13px">Επιλέξτε το πεδίο που θέλετε να επεξεργαστείτε, δώστε τη νέα τιμή και και ύστερα πιέστε <strong>ENTER</strong>. Για να καταργήσετε ή να επανάφέρετε ένα προϊόν, πιέζετε το ματάκι στο τέλος της αντίστοιχης γραμμής</span></p>
		<table class='table-bordered' id="csa-wp-plugin-showProductsList_table" style='border-spacing:1em'> 
		<thead class='tableHeader'><tr><th>#</th><th>Όνομα</th><th>Κατηγορία</th><th>Ποικιλία</th><th>Τιμή(€)</th><th>Ανά</th><th>Παραγωγός</th><th>Περιγραφή</th><th>Διαθέσιμο;</th><th>Ευπαθές;</th><th>Ανταλλάξιμο;</th></tr></thead> 
		<tbody> <?php
			global $wpdb;
			$pluginsDir = plugins_url();

			$products = $wpdb->get_results("SELECT p_id,name,category,variety,current_price_in_euro,measurement_unit,producer,description,isAvailable,isFrail,isExchangeable FROM ". csaProducts);
			foreach($products as $row) 
			{
				$productId = $row->p_id;
				echo "<tr valign='top' id='$productId'>
					<td> $productId </td>
					<td class='editable'>$row->name </td>
					<td class='editable'>$row->category </td>
					<td class='editable'>$row->variety</td>
					<td class='editable'>$row->current_price_in_euro</td>
					<td class='editable'>$row->measurement_unit</td>
					<td class='editable'>$row->producer</td>
					<td class='editable'>$row->description</td>
					<td>".(($row->isAvailable == '1')?"yes":"no")."</td>
					<td>".(($row->isFrail == '1')?"yes":"no")."</td>
					<td>".(($row->isExchangeable == '1')?"yes":"no")."</td>
					<td>".(($row->isAvailable == '1')?
						"<img id='eye$productId' style='cursor:pointer' src='".plugins_url()."/csa-wp-plugin/icons/visible.png' height='24' width='24' onclick='CsaWpPluginToggleProductVisibility($productId,\"$pluginsDir\")' title='απενεργοποίηση'>": 
						"<img id='eye$productId' style='cursor:pointer' src='".plugins_url()."/csa-wp-plugin/icons/nonVisible.png' height='24' width='24' onclick='CsaWpPluginToggleProductVisibility($productId,\"$pluginsDir\")' title='ενεργοποίηση'>"	
					)."</td>
					<td> <img id='eye$productId' style='cursor:pointer' src='".plugins_url()."/csa-wp-plugin/icons/edit.png' height='24' width='24' onclick='CsaWpPluginEditProduct($productId,\"$pluginsDir\")' title='ενεργοποίηση'></td>
<td> <img id='eye$productId' style='cursor:pointer' src='".plugins_url()."/csa-wp-plugin/icons/delete.png' height='24' width='24' onclick='CsaWpPluginDeleteProduct($productId,\"$pluginsDir\")' title='ενεργοποίηση'></td></tr>";
					
				//grey each row related to an unavailable product
				if ($row->isAvailable == '0') {
					echo "<script>document.getElementById(".$productId.").style.color='gray';</script>";
				}
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
		$columnNum = intval(clean_input($_POST['column'])); //not valid for getting the right column, when html table structure differs from the relative db table ??????? IS THIS IMPORTANT ?????
		$productID = intval(clean_input($_POST['productID']));
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
				echo '<span style="color:red">Κάτι δε δούλεψε σωστά. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή.</span>';												
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
			array("available" => $availability), 
			array('id' => $productID)
		) === FALSE) 
			echo '<span style="color:red">Κάτι δε δούλεψε σωστά. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή ,['.$new_value.']['.$columnNum.']['.$p_id.']['.$columnName.']</span>';												
		else echo 'success, Availability has been updated.';
	} else {
		echo 'error,Invalid request made.';
	}
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}
