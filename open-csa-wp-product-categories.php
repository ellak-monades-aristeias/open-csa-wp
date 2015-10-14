<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function open_csa_wp_show_new_product_category_form($display) {

	wp_enqueue_script( 'open-csa-wp-general-scripts' );
	wp_enqueue_script( 'open-csa-wp-product-categories-scripts' );
?>
	<br />
	<div id="open-csa-wp-addProductCategory_formHeader">
		<span 
			style="cursor:pointer" 
			id="open-csa-wp-addProductCategory_formHeader_text" 
			onclick="open_csa_wp_toggle_form('addProductCategory','<?php _e('Add New Category of Products', OPEN_CSA_WP_DOMAIN);?>', ' <?php _e('form', OPEN_CSA_WP_DOMAIN);?>')"
		><font size='4'>
			<?php 
				if ($display == false) {
					echo __('Add New Category of Products', OPEN_CSA_WP_DOMAIN) .' ('. __('show form',OPEN_CSA_WP_DOMAIN) .')';
				} else {
					echo __('Add New Category of Products', OPEN_CSA_WP_DOMAIN) .' ('. __('hide form',OPEN_CSA_WP_DOMAIN) .')';
				}
			?>
		</font>
	</div>
	<div 
		id="open-csa-wp-addProductCategory_div" 		
		<?php 
			if ($display == false) {
				echo 'style="display:none"';
			}
		?>
	>
		<!-- SQL: Get Columns from csa_products_category table: SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES' ORDER BY ORDINAL_POSITION -->
		<form method="POST" id='open-csa-wp-addNewCategoryForm'>
			<table class="form-table">
				<tr valign="top"><td>
					<input 
						type='text' 
						placeholder="<?php _e('Category Name', OPEN_CSA_WP_DOMAIN)?> *" 
						required
						name="open-csa-wp-newProductCategory_name_input"
					>
				</td></tr>
				<tr valign="top"><td>
					<textarea 
						rows="3" cols="30" 
						placeholder="<?php _e('Description', OPEN_CSA_WP_DOMAIN)?>"
						name="open-csa-wp-newProductCategory_desctription_input"
					></textarea>
				</td></tr>
			</table> 
			<input 
				type="submit" 
				name="open-csa-wp-AddCategory-button" 
				value="Add Category" 
				class="button button-primary"
				onclick="open_csa_wp_new_product_categories_fields_validation(this)"
			/>
		</form>
		<br/><br/>
	</div>
<?php
}

add_action( 'wp_ajax_open-csa-wp-add_new_productCategory', 'open_csa_wp_add_new_category' );

function open_csa_wp_add_new_category() {

	if( isset($_POST['data'])) {
		
		$data_received = json_decode(stripslashes($_POST['data']),true);
				
		global $wpdb;
		if($wpdb->insert(OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES,
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

function open_csa_wp_show_product_categories($display) {

	wp_enqueue_script('open-csa-wp-general-scripts');
	wp_enqueue_script('open-csa-wp-product-categories-scripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); 
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');
?>	
	<br/>
	<div id="open-csa-wp-showProductCategoriesList_header">
		<span 
			style="cursor:pointer" 
			id="open-csa-wp-showProductCategoriesList_formHeader_text" 
			onclick="open_csa_wp_toggle_form('showProductCategoriesList','<?php _e('Product Categories List', OPEN_CSA_WP_DOMAIN);?>', '')">
			<font size='4'>
			<?php 
				if ($display == false) {
					echo __('Product Categories List', OPEN_CSA_WP_DOMAIN) .' ('. __('show',OPEN_CSA_WP_DOMAIN) .')';
				} else {
					echo __('Product Categories List', OPEN_CSA_WP_DOMAIN) .' ('. __('hide',OPEN_CSA_WP_DOMAIN) .')';
				}
			?>

			</font>
		</span>
	</div>
	<div 
		id="open-csa-wp-showProductCategoriesList_div" 
		<?php 
			if ($display == false) {
				echo "style='display:none'"; 
			}
		?>
	>		
		<span class='open-csa-wp-tip_categories' 
			title='If you want to update a field, click on it, write the new value, and then press ENTER.
			| If you want to delete a product, click on the "x" icon at the end of the corresponding row.'>
			<p style="color:green;font-style:italic; font-size:13px">
			by pointing here you can read additional information...p>
		</span>
			
			
		<table class='table-bordered' id="open-csa-wp-showProductCategoriesList_table" style='border-spacing:1em'> 
		<thead class='tableHeader'>
			<tr>
				<th><?php _e('Name', OPEN_CSA_WP_DOMAIN);?></th>
				<th><?php _e('Description', OPEN_CSA_WP_DOMAIN);?></th>
				<th/>
			</tr>
		</thead> 
		<tbody> <?php
			global $wpdb;
			$plugins_dir = plugins_url();

			$products = $wpdb->get_results("SELECT id,name,description FROM ". OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES);
			foreach($products as $row) {
				$category_id = $row->id;
				echo "
					<tr valign='top' 
						id='open-csa-wp-showProductCategoriesCategoryID_$category_id'  
						class='open-csa-wp-showProductCategoriesCategoryID-category'
						style='text-align:center'
					>
						<td class='editable'>$row->name </td>
						<td class='editable'>$row->description</td>
						<td> <img 
						style='cursor:pointer' 
						src='".plugins_url()."/open-csa-wp/icons/delete.png' 
						height='24' width='24'
						onmouseover='open_csa_wp_hover_icon(this, \"delete\", \"$plugins_dir\")' 
						onmouseout='open_csa_wp_unhover_icon(this, \"delete\", \"$plugins_dir\")' 						
						onclick='open_csa_wp_request_delete_product_category(this)' 
						title='". __('delete product', OPEN_CSA_WP_DOMAIN) ."'></td>
					</tr>";
			}
			?>
		</tbody> </table>
	</div>	
<?php 
}

add_action( 'wp_ajax_open-csa-wp-update_category', 'open_csa_wp_update_product_category');

function open_csa_wp_update_product_category() {
	if(isset($_POST['value']) && isset($_POST['column']) && isset($_POST['product_category_id'])) {
		$new_value = open_csa_wp_clean_input($_POST['value']);
		$column_num = intval(open_csa_wp_clean_input($_POST['column']))+1; //not valid for getting the right column, when html table structure differs from the relative db table

		$product_category_id = intval(open_csa_wp_clean_input($_POST['product_category_id']));
		if(!empty($column_num) && !empty($product_category_id)) {
			// Updating the information 
			global $wpdb;
			//get column names and assign them to an array
			$columns = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES."' ORDER BY ORDINAL_POSITION", ARRAY_N);
			//update the database, using the relative column name
			$column_name = $columns[$column_num][0];

			if(	$wpdb->update(
				OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES,
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

add_action( 'wp_ajax_open-csa-wp-delete_product_category', 'open_csa_wp_delete_product_category' );

function open_csa_wp_delete_product_category() {
	if(isset($_POST['product_category_id'])) {
		$product_category_id = intval(open_csa_wp_clean_input($_POST['product_category_id']));
		if(!empty($product_category_id)) {
			// Updating the information 
			global $wpdb;

			$product_category_is_used = $wpdb->get_var($wpdb->prepare("
										SELECT COUNT(category)
										FROM ".OPEN_CSA_WP_TABLE_PRODUCTS." 
										WHERE category=%d", $product_category_id));
			if ($product_category_is_used > 0) {
				echo 'skipped, used in products';
			} else {
				if(	$wpdb->delete(
					OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES,
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