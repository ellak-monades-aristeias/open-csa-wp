<?php
add_action( 'wp_ajax_csa_wp_plugin_add_new_productsCategory', 'CsaWpPluginAddNewCategory' );
add_action( 'wp_ajax_csa_wp_plugin_update_category', 'CsaWpPluginUpdateProduct' );
add_action( 'wp_ajax_csa_wp_plugin_show_categories', 'CsaWpPluginShowProductsCategories' );


/* ****************************************************
function that creates the form for adding a new category
******************************************************** */
function CsaWpPluginShowNewProductsCategoryForm() {

	wp_enqueue_script( 'CsaWpPluginScripts' );?>
	<br />
	<div id="csa_wp_addProductsCategory_formHeader"><span style="cursor:pointer" id="csa_wp_addCategory_formHeader_text" onclick="slow_hideshow_addNewProductsCategoryForm()"><font size='4'>Add New Category of Products (show form)</font></span></div>
	<div id="csa_wp_addCategory_ack" class="info-text" style="display:none"> Η κατηγορία καταχωρήθηκε επιτυχώς</div>
	<div id="csa_wp_addCategory_div" style="display:none">
		<!-- SQL: Get Columns from csa_products_category table: SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'csaProductCategories' ORDER BY ORDINAL_POSITION -->
		<form method="POST" id='addCategory_form'>
			<table class="form-table">
				<tr valign="top"><td>Όνομα: &nbsp <input type='text' id='name_field2' required value=""></td></tr>
				<tr valign="top"><td>Περιγραφή:&nbsp <textarea id="description_field2" rows="3" cols="30"></textarea></td></tr>
			</table> 
			<input type="submit" name="AddCategory" value="Add Category" class="button button-primary" onclick="submitCategoryForm(this.parentNode)"/>
		</form>
		<br/><br/>
	</div>
<?php
}
add_shortcode('csa-wp-plugin-addNewCategoryForm', 'CsaWpPluginShowNewCategoryForm');



/* ******************************************
function that adds a new category
********************************************* */
function CsaWpPluginAddNewCategory() {

	if( isset($_POST['name']) && 
		isset($_POST['description']) )  
	{
		$name = clean_input($_POST['name']);
		$description = clean_input($_POST['description']);
		echo("ola kala?");
				
		global $wpdb;
		if($wpdb->insert(csaProductsCategories,
			array(	'name' 		=> $name,
				'description' 	=> $description
			), 
			array ("%s", "%s")
		) === FALSE)
			echo "<span style='color:red'>Δεν καταχωρήθηκε η κατηγορία.. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή </span>";												
		else
			echo 'success, category added.';
	} else 
		echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}



/* ******************************************
function that shows all the categories
********************************************* */


function CsaWpPluginShowProductsCategories() {

	wp_enqueue_script('CsaWpPluginScripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); ?>
	
<script type="text/javascript">
	var $j = jQuery.noConflict();	

	$j(document).ready(function() {
		var table = $j("#csa_wp_showCategoriesList_table");
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
					"action" : "csa_wp_plugin_update_category",
					"value" : value,
					"categoryID": this.parentNode.getAttribute("id"),
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
	
	<br/>
	<div id="csa_wp_showProductsCategoryList_header"><span style="cursor:pointer" id="csa_wp_showProductsCategoryList_header_text" onclick="slow_hideshow_showProductsCategoriesList()"><font size='4'>Categories List (show)</font></span></div>
	<div id="csa_wp_showProductsCategoryList_div" style="display:none">
		<!-- SQL: Get Columns from csa_product table: SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'csa_product' ORDER BY ORDINAL_POSITION -->
		
		<span style="color:green;font-style:italic; font-size:13px">Επιλέξτε το πεδίο που θέλετε να επεξεργαστείτε, δώστε τη νέα τιμή και και ύστερα πιέστε <strong>ENTER</strong>. Για να καταργήσετε ή να επανάφέρετε μια κατηγορία, πιέζετε το ματάκι στο τέλος της αντίστοιχης γραμμής</span></p>
		<table class='table-bordered' id="csa_wp_showProductsCategoryList_table" style='border-spacing:1em'> 
		<thead class='tableHeader'><tr><th>#</th><th>Όνομα</th><th>Περιγραφή</th></tr></thead> 
		<tbody> <?php
			global $wpdb;
			$pluginsDir = plugins_url();

			$products = $wpdb->get_results("SELECT pc_id,name,description FROM ". csaProductsCategories);
			foreach($products as $row) 
			{
				$categoryId = $row->pc_id;
				echo "<tr valign='top' id='$categoryId'>
					<td> $categoryId </td>
					<td class='editable'>$row->name </td>
					<td class='editable'>$row->description</td>
					</tr>";
			}
			?>
		</tbody> </table>
	</div>	
<?php 
}
add_shortcode('csa-wp-plugin-showProductsCategoryList', 'CsaWpPluginShowCategories');
