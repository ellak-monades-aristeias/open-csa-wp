<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function csa_wp_plugin_show_new_product_category_form($display) {

	wp_enqueue_script( 'csa-wp-plugin-general-scripts' );
	wp_enqueue_script( 'csa-wp-plugin-product-categories-scripts' );
?>
	<br />
	<div id="csa-wp-plugin-addProductCategory_formHeader">
		<span 
			style="cursor:pointer" 
			id="csa-wp-plugin-addProductCategory_formHeader_text" 
			onclick="csa_wp_plugin_toggle_form('addProductCategory','<?php _e('Add New Category of Products', CSA_WP_PLUGIN_DOMAIN);?>', ' <?php _e('form', CSA_WP_PLUGIN_DOMAIN);?>')"
		><font size='4'>
			<?php 
				if ($display == false) {
					echo __('Add New Category of Products', CSA_WP_PLUGIN_DOMAIN) .' ('. __('show form',CSA_WP_PLUGIN_DOMAIN) .')';
				} else {
					echo __('Add New Category of Products', CSA_WP_PLUGIN_DOMAIN) .' ('. __('hide form',CSA_WP_PLUGIN_DOMAIN) .')';
				}
			?>
		</font>
	</div>
	<div 
		id="csa-wp-plugin-addProductCategory_div" 		
		<?php 
			if ($display == false) {
				echo 'style="display:none"';
			}
		?>
	>
		<!-- SQL: Get Columns from csa_products_category table: SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'CSA_WP_PLUGIN_TABLE_PRODUCT_CATEGORIES' ORDER BY ORDINAL_POSITION -->
		<form method="POST" id='csa-wp-plugin-addNewCategoryForm'>
			<table class="form-table">
				<tr valign="top"><td>
					<input 
						type='text' 
						placeholder="<?php _e('Category Name', CSA_WP_PLUGIN_DOMAIN)?> *" 
						required
						name="csa-wp-plugin-newProductCategory_name_input"
					>
				</td></tr>
				<tr valign="top"><td>
					<textarea 
						rows="3" cols="30" 
						placeholder="<?php _e('Description', CSA_WP_PLUGIN_DOMAIN)?>"
						name="csa-wp-plugin-newProductCategory_desctription_input"
					></textarea>
				</td></tr>
			</table> 
			<input 
				type="submit" 
				name="csa-wp-plugin-AddCategory-button" 
				value="Add Category" 
				class="button button-primary"
				onclick="csa_wp_plugin_new_product_categories_fields_validation(this)"
			/>
		</form>
		<br/><br/>
	</div>
<?php
}

add_action( 'wp_ajax_csa-wp-plugin-add_new_productCategory', 'csa_wp_plugin_add_new_category' );

function csa_wp_plugin_add_new_category() {

	if( isset($_POST['data'])) {
		
		$data_received = json_decode(stripslashes($_POST['data']),true);
				
		global $wpdb;
		if($wpdb->insert(CSA_WP_PLUGIN_TABLE_PRODUCT_CATEGORIES,
			array(	
				'name' => $data_received[0]['value'],
				'description' => $data_received[1]['value']
			), 
			array ("%s", "%s")
		) === FALSE) {
			echo 'error, sql request failed.';
		} else {
			echo 'success, category added.';
		}
	} else {
		echo 'error,Bad request.';
	}
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}

function csa_wp_plugin_show_product_categories($display) {

	wp_enqueue_script('csa-wp-plugin-general-scripts');
	wp_enqueue_script('csa-wp-plugin-product-categories-scripts');
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
			onclick="csa_wp_plugin_toggle_form('showProductCategoriesList','<?php _e('Product Categories List', CSA_WP_PLUGIN_DOMAIN);?>', '')">
			<font size='4'>
			<?php 
				if ($display == false) {
					echo __('Product Categories List', CSA_WP_PLUGIN_DOMAIN) .' ('. __('show',CSA_WP_PLUGIN_DOMAIN) .')';
				} else {
					echo __('Product Categories List', CSA_WP_PLUGIN_DOMAIN) .' ('. __('hide',CSA_WP_PLUGIN_DOMAIN) .')';
				}
			?>

			</font>
		</span>
	</div>
	<div 
		id="csa-wp-plugin-showProductCategoriesList_div" 
		<?php 
			if ($display == false) {
				echo "style='display:none'"; 
			}
		?>
	>		
		<span class='csa-wp-plugin-tip_categories' 
			title='If you want to update a field, click on it, write the new value, and then press ENTER.
			| If you want to delete a product, click on the "x" icon at the end of the corresponding row.'>
			<p style="color:green;font-style:italic; font-size:13px">
			by pointing here you can read additional information...p>
		</span>
			
			
		<table class='table-bordered' id="csa-wp-plugin-showProductCategoriesList_table" style='border-spacing:1em'> 
		<thead class='tableHeader'>
			<tr>
				<th><?php _e('Name', CSA_WP_PLUGIN_DOMAIN);?></th>
				<th><?php _e('Description', CSA_WP_PLUGIN_DOMAIN);?></th>
				<th/>
			</tr>
		</thead> 
		<tbody> <?php
			global $wpdb;
			$plugins_dir = plugins_url();

			$products = $wpdb->get_results("SELECT id,name,description FROM ". CSA_WP_PLUGIN_TABLE_PRODUCT_CATEGORIES);
			foreach($products as $row) {
				$category_id = $row->id;
				echo "
					<tr valign='top' 
						id='csa-wp-plugin-showProductCategoriesCategoryID_$category_id'  
						class='csa-wp-plugin-showProductCategoriesCategoryID-category'
						style='text-align:center'
					>
						<td class='editable'>$row->name </td>
						<td class='editable'>$row->description</td>
						<td> <img 
						style='cursor:pointer' 
						src='".plugins_url()."/csa-wp-plugin/icons/delete.png' 
						height='24' width='24'
						onmouseover='csa_wp_plugin_hover_icon(this, \"delete\", \"$plugins_dir\")' 
						onmouseout='csa_wp_plugin_unhover_icon(this, \"delete\", \"$plugins_dir\")' 						
						onclick='csa_wp_plugin_request_delete_product_category(this)' 
						title='". __('delete product', CSA_WP_PLUGIN_DOMAIN) ."'></td>
					</tr>";
			}
			?>
		</tbody> </table>
	</div>	
<?php 
}

add_action( 'wp_ajax_csa-wp-plugin-update_category', 'csa_wp_plugin_update_product_category');

function csa_wp_plugin_update_product_category() {
	if(isset($_POST['value']) && isset($_POST['column']) && isset($_POST['product_category_id'])) {
		$new_value = csa_wp_plugin_clean_input($_POST['value']);
		$column_num = intval(csa_wp_plugin_clean_input($_POST['column']))+1; //not valid for getting the right column, when html table structure differs from the relative db table

		$product_category_id = intval(csa_wp_plugin_clean_input($_POST['product_category_id']));
		if(!empty($column_num) && !empty($product_category_id)) {
			// Updating the information 
			global $wpdb;
			//get column names and assign them to an array
			$columns = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".CSA_WP_PLUGIN_TABLE_PRODUCT_CATEGORIES."' ORDER BY ORDINAL_POSITION", ARRAY_N);
			//update the database, using the relative column name
			$column_name = $columns[$column_num][0];

			if(	$wpdb->update(
				CSA_WP_PLUGIN_TABLE_PRODUCT_CATEGORIES,
				array($column_name => $new_value), 
				array('id' => $product_category_id )
			) === FALSE) {
				echo 'error, sql request failed.';
			} else {
				echo 'success,'.$new_value;
			}
		} 
		else echo 'error,Empty values.';
	} 
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_csa-wp-plugin-delete_product_category', 'csa_wp_plugin_delete_product_category' );

function csa_wp_plugin_delete_product_category() {
	if(isset($_POST['product_category_id'])) {
		$product_category_id = intval(csa_wp_plugin_clean_input($_POST['product_category_id']));
		if(!empty($product_category_id)) {
			// Updating the information 
			global $wpdb;

			$product_category_is_used = $wpdb->get_var($wpdb->prepare("
										SELECT COUNT(category)
										FROM ".CSA_WP_PLUGIN_TABLE_PRODUCTS." 
										WHERE category=%d", $product_category_id));
			if ($product_category_is_used > 0) {
				echo 'skipped, used in products';
			} else {
				if(	$wpdb->delete(
					CSA_WP_PLUGIN_TABLE_PRODUCT_CATEGORIES,
					array('id' => $product_category_id ),
					array ('%d')
				) === FALSE) {
					echo 'error, sql request failed.';												
				} else {
					echo 'success';
				}
			}
		} else {
			echo 'error,Empty values.';
		}
	} else {
		echo 'error,Bad request.';
	}
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}