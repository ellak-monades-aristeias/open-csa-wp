<?php
add_action( 'wp_ajax_csa_wp_plugin_add_new_product', 'CsaWpPluginAddNewProduct' );
add_action( 'wp_ajax_csa_wp_plugin_update_product', 'CsaWpPluginUpdateProduct' );
add_action( 'wp_ajax_csa_wp_plugin_update_product_availability', 'CsaWpPluginUpdateProductAvailability' );

// Add new product shortcode
function CsaWpPluginShowNewProductForm() { 
	
	wp_enqueue_script( 'CsaWpPluginScripts' );?>
	
	<br />
	<div id="csa_wp_addProduct_formHeader"><span style="cursor:pointer" id="csa_wp_addProduct_formHeader_text" onclick="slow_hideshow_addNewProductForm()"><font size='4'>Add New Product (show form)</font></span></div>
	<div id="csa_wp_addProduct_ack" class="info-text" style="display:none"> Το προϊόν καταχωρήθηκε επιτυχώς</div>
	<div id="csa_wp_addProduct_div" style="display:none">
		<!-- SQL: Get Columns from csa_product table: SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'csa_product' ORDER BY ORDINAL_POSITION -->
		<form method="POST" id='addProduct_form'>
			<table class="form-table">
				<tr valign="top"><td><input type='text' placeholder='Είδος' required id='type_field'></td></tr>
				<tr valign="top"><td><input type='text' placeholder='Ποικιλία' required id='variety_field'></td></tr>
				<tr valign="top"><td><input type='text' placeholder='Κατηγορία' required id='category_field'></td></tr>
				<tr valign="top"><td><input type='number' min='0' step='0.1' placeholder='Τιμή' required id='price_field'></td></tr>
				
				<tr valign="top"><td><select id='unit_field' required>
					<option value='select' selected="selected" disabled="disabled">Tιμή ανά ...</option>
					<option value='kilo'>κιλό</option>
					<option value='peace'>τεμ.</option>
					<option value='batch'>μάτσο</option>
					<option value='litre'>λίτρο</option>
				</select> </td></tr>
				<tr valign="top"><td><input type='text' placeholder='Παραγωγός' required id='producer_field'></td></tr>
				<tr valign="top"><td><textarea placeholder='Πληροφορίες' id="details_field" rows="3" cols="30"></textarea></td></tr>
				<tr valign="top"><td><select id='available_field' required>
					<option selected="selected" disabled="disabled">Διαθέσιμο...</option>
					<option value='true'>Ναι</option>
					<option value='false'>Όχι</option>
				</select></td></tr>
			</table> 
			<input type="button" name="Add Product" value="Add Product" class="button button-primary" onclick="CsaWpPluginSendRequestAddProductToServer()"/>
		</form>
		<br/><br/>
	</div>
	
<?php
}
add_shortcode('csa-wp-plugin-addNewProductForm', 'CsaWpPluginShowNewProductForm');

function CsaWpPluginAddNewProduct() {

	if( isset($_POST['type']) && 
		isset($_POST['variety']) && 
		isset($_POST['category']) && 
		isset($_POST['price']) && 
		isset($_POST['unit']) && 
		isset($_POST['producer']) && 
		isset($_POST['details']) && 
		isset($_POST['available']) )  
	{
		$type = clean_input($_POST['type']);
		$variety = clean_input($_POST['variety']);
		$category = clean_input($_POST['category']);
		$price = floatval($_POST['price']);
		$unit = $_POST['unit'];
		$producer = clean_input($_POST['producer']);
		$details = clean_input($_POST['details']);
		$available = $_POST['available'];
				
		global $wpdb;
		if(	$wpdb->insert(
				csaProducts,
				array(
					'type' 		=> $type,
					'variety' 	=> $variety,
					'category' 	=> $category,
					'price'		=> $price,
					'unit'		=> $unit,
					'producer'	=> $producer,
					'details'	=> $details,
					'available'	=> $available
				), 
				array ("%s", "%s", "%s", "%f", "%s", "%s", "%s", "%s")
		) === FALSE)
			echo "<span style='color:red'>Κάτι δε δούλεψε σωστά. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή </span>";												
		else
			echo 'success, product added.';
	} else 
		echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}

function CsaWpPluginShowProducts() {
	wp_enqueue_script('CsaWpPluginScripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); ?>
	
<script type="text/javascript">
	var $j = jQuery.noConflict();	

	$j(document).ready(function() {
		var table = $j("#csa_wp_showProductsList_table");
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
					"action" : "csa_wp_plugin_update_product",
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
	<div id="csa_wp_showProductsList_header"><span style="cursor:pointer" id="csa_wp_showProductsList_header_text" onclick="slow_hideshow_showProductsList()"><font size='4'>Products List (show)</font></span></div>
	<div id="csa_wp_showProductsList_div" style="display:none">
		<!-- SQL: Get Columns from csa_product table: SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'csa_product' ORDER BY ORDINAL_POSITION -->
		
		<span style="color:green;font-style:italic; font-size:13px">Επιλέξτε το πεδίο που θέλετε να επεξεργαστείτε, δώστε τη νέα τιμή και και ύστερα πιέστε <strong>ENTER</strong>. Για να καταργήσετε ή να επανάφέρετε ένα προϊόν, πιέζετε το ματάκι στο τέλος της αντίστοιχης γραμμής</span></p>
		<table class='table-bordered' id="csa_wp_showProductsList_table" style='border-spacing:1em'> 
		<thead class='tableHeader'><tr><th>#</th><th>είδος</th><th>ποικιλία</th><th>€</th><th>ανά</th><th>παραγωγός</th><th>κατηγορία</th><th>πληροφορίες</th><th>διαθεσιμότητα</th></tr></thead> 
		<tbody> <?php
			global $wpdb;
			$pluginsDir = plugins_url();

			$products = $wpdb->get_results("SELECT id,type,variety,price,unit,producer,category,details,available FROM ". csaProducts);
			foreach($products as $row) 
			{
				$productId = $row->id;
				echo "<tr valign='top' id='$productId'>
					<td> $productId </td>
					<td class='editable'>$row->type </td>
					<td class='editable'>$row->variety </td>
					<td class='editable'>$row->price</td>
					<td class='editable'>$row->unit</td>
					<td class='editable'>$row->producer</td>
					<td class='editable'>$row->category</td>
					<td class='editable'>$row->details</td>
					<td>".(($row->available == 'true')?
						"<img id='eye$productId' style='cursor:pointer' src='".plugins_url()."/csa-wp-plugin/icons/visible.png' height='24' width='24' onclick='CsaWpPluginToggleProductVisibility($productId,\"$pluginsDir\")' title='απενεργοποίηση'>": 
						"<img id='eye$productId' style='cursor:pointer' src='".plugins_url()."/csa-wp-plugin/icons/nonVisible.png' height='24' width='24' onclick='CsaWpPluginToggleProductVisibility($productId,\"$pluginsDir\")' title='ενεργοποίηση'>"	
					)."</td></tr>";
					
				//grey each row related to an unavailable product
				if ($row->available == 'false') {
					echo "<script>document.getElementById(".$productId.").style.color='gray';</script>";
				}
			}
			?>
		</tbody> </table>
	</div>	
<?php
}
add_shortcode('csa-wp-plugin-showProductsList', 'CsaWpPluginShowProducts');

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
			echo '<span style="color:red">Κάτι δε δούλεψε σωστά. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή ,['.$new_value.']['.$columnNum.']['.$c_id.']['.$columnName.']</span>';												
		else echo 'success, Availability has been updated.';
	} else {
		echo 'error,Invalid request made.';
	}
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}
