<?php

function CsaWpPluginShowNewProductCategoryForm($display) {

	wp_enqueue_script( 'CsaWpPluginScripts' );
	wp_enqueue_script( 'CsaWpPluginProductCategoriesScripts' );
?>
	<br />
	<div id="csa-wp-plugin-addProductCategory_formHeader">
		<span 
			style="cursor:pointer" 
			id="csa-wp-plugin-addProductCategory_formHeader_text" 
			onclick="CsaWpPluginToggleForm('addProductCategory','Add New Category of Products', ' form')"
		><font size='4'>
			<?php 
				if ($display == false) echo 'Add New Category of Products (show form)';
				else echo 'Add New Category of Products (hide form)'
			?>
		</font>
	</div>
	<div 
		id="csa-wp-plugin-addProductCategory_div" 		
		<?php if ($display == false) echo 'style="display:none"' ?>
	>
		<!-- SQL: Get Columns from csa_products_category table: SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'csaProductCategories' ORDER BY ORDINAL_POSITION -->
		<form method="POST" id='csa-wp-plugin-addNewCategoryForm'>
			<table class="form-table">
				<tr valign="top"><td>
					<input 
						type='text' 
						placeholder="Category Name *" 
						required
						name="csa-wp-plugin-newProductCategory_name_input"
					>
				</td></tr>
				<tr valign="top"><td>
					<textarea 
						rows="3" cols="30" 
						placeholder="Description"
						name="csa-wp-plugin-newProductCategory_desctription_input"
					></textarea>
				</td></tr>
			</table> 
			<input 
				type="submit" 
				name="csa-wp-plugin-AddCategory-button" 
				value="Add Category" 
				class="button button-primary"
				onclick="CsaWpPluginNewProducCategoriesFieldsValidation(this)"
			/>
		</form>
		<br/><br/>
	</div>
<?php
}

add_action( 'wp_ajax_csa-wp-plugin-add_new_productCategory', 'CsaWpPluginAddNewCategory' );

function CsaWpPluginAddNewCategory() {

	if( isset($_POST['data'])) {
		
		$dataReceived = json_decode(stripslashes($_POST['data']),true);
				
		global $wpdb;
		if($wpdb->insert(csaProductCategories,
			array(	
				'name' => $dataReceived[0]['value'],
				'description' => $dataReceived[1]['value']
			), 
			array ("%s", "%s")
		) === FALSE) echo 'error, sql request failed.';
		else echo 'success, category added.';
	} else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}

function CsaWpPluginShowProductCategories($display) {

	wp_enqueue_script('CsaWpPluginScripts');
	wp_enqueue_script('CsaWpPluginProductCategoriesScripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); 
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');
?>	
	<br/>
	<div id="csa-wp-plugin-showProductCategoriesList_header">
		<span 
			style="cursor:pointer" 
			id="csa-wp-plugin-showProductCategoriesList_formHeader_text" 
			onclick="CsaWpPluginToggleForm('showProductCategoriesList','Product Categories List', '')">
			<font size='4'>
			<?php 
				if ($display == false) echo 'Product Categories List (show)';
				else echo 'Product Categories List (hide)'
			?>

			</font>
		</span>
	</div>
	<div 
		id="csa-wp-plugin-showProductCategoriesList_div" 
		<?php if ($display == false) echo "style='display:none'"; ?>
	>		
		<span class='csa-wp-plugin-tip_categories' 
			title='If you want to update a field, click on it, write the new value, and then press ENTER.
			| If you want to delete a product, press the icon at the end of the corresponding row.'>
			<p style="color:green;font-style:italic; font-size:13px">By pointing here you can read additional information.</p>
		</span>
			
			
		<table class='table-bordered' id="csa-wp-plugin-showProductCategoriesList_table" style='border-spacing:1em'> 
		<thead class='tableHeader'>
			<tr>
				<th>Name</th>
				<th>Description</th>
				<th/>
			</tr>
		</thead> 
		<tbody> <?php
			global $wpdb;
			$pluginsDir = plugins_url();

			$products = $wpdb->get_results("SELECT id,name,description FROM ". csaProductCategories);
			foreach($products as $row) {
				$categoryID = $row->id;
				echo "
					<tr valign='top' id='csa-wp-plugin-showProductCategoriesCategoryID_$categoryID'  class='csa-wp-plugin-showProductCategoriesCategoryID-category'>
						<td class='editable'>$row->name </td>
						<td class='editable'>$row->description</td>
						<td> <img 
						style='cursor:pointer' 
						src='".plugins_url()."/csa-wp-plugin/icons/delete.png' 
						height='24' width='24'
						onmouseover='CsaWpPluginHoverIcon(this, \"delete\", \"$pluginsDir\")' 
						onmouseout='CsaWpPluginUnHoverIcon(this, \"delete\", \"$pluginsDir\")' 						
						onclick='CsaWpPluginRequestDeleteProductCategory(this)' 
						title='delete product'></td>
					</tr>";
			}
			?>
		</tbody> </table>
	</div>	
<?php 
}
add_shortcode('csa-wp-plugin-showProductsCategoryList', 'CsaWpPluginShowCategories');

add_action( 'wp_ajax_csa-wp-plugin-update_category', 'CsaWpPluginUpdateProductCategory');

function CsaWpPluginUpdateProductCategory() {
	if(isset($_POST['value']) && isset($_POST['column']) && isset($_POST['productCategoryID'])) {
		$new_value = clean_input($_POST['value']);
		$columnNum = intval(clean_input($_POST['column']))+1; //not valid for getting the right column, when html table structure differs from the relative db table

		$productCategoryID = intval(clean_input($_POST['productCategoryID']));
		if(!empty($columnNum) && !empty($productCategoryID)) {
			// Updating the information 
			global $wpdb;
			//get column names and assign them to an array
			$columns = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".csaProductCategories."' ORDER BY ORDINAL_POSITION", ARRAY_N);
			//update the database, using the relative column name
			$columnName = $columns[$columnNum][0];

			if(	$wpdb->update(
				csaProductCategories,
				array($columnName => $new_value), 
				array('id' => $productCategoryID )
			) === FALSE) 
				echo 'error, sql request failed.';
			else echo 'success,'.$new_value;
		} 
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_csa-wp-plugin-delete_product_category', 'CsaWpPluginDeleteProductCategory' );

function CsaWpPluginDeleteProductCategory() {
	if(isset($_POST['productCategoryID'])) {
		$productCategoryID = intval(clean_input($_POST['productCategoryID']));
		if(!empty($productCategoryID)) {
			// Updating the information 
			global $wpdb;

			if(	$wpdb->delete(
				csaProductCategories,
				array('id' => $productCategoryID ),
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