<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function open_csa_wp_show_new_product_form($product_id, $display, $page_url) { 
	
	wp_enqueue_script( 'open-csa-wp-general-scripts' );
	wp_enqueue_script( 'open-csa-wp-products-scripts' );
	
	global $days_of_week,$wpdb;
	$product_info = null;
	if ($product_id != null) {
		$product_info = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".OPEN_CSA_WP_TABLE_PRODUCTS." WHERE id=%d", $product_id));
	}
?>

	<br/>
	<div id="open-csa-wp-addProduct_formHeader">
		<span 
			id="open-csa-wp-addProduct_formHeader_text" 
			<?php 
				if ($product_id == null) {
					echo 'style="cursor:pointer"';
					echo 'onclick="open_csa_wp_toggle_form(\'addProduct\',\''.__('Add New Product', OPEN_CSA_WP_DOMAIN).'\', \' '.__('form', OPEN_CSA_WP_DOMAIN).'\')"';
				}
			?>>
			<font size='4'>
			<?php 
			if ($product_id == null) {
				if ($display == false) {
					echo __('Add New Product', OPEN_CSA_WP_DOMAIN) .' ('. __('show form',OPEN_CSA_WP_DOMAIN) .')';
				} else {
					echo __('Add New Product', OPEN_CSA_WP_DOMAIN) .' ('. __('hide form',OPEN_CSA_WP_DOMAIN) .')';
				}
			} else {
				echo __('Edit Product', OPEN_CSA_WP_DOMAIN) .' #'.$product_id;
			}
			?>

			</font>
		</span>
	</div>
	<div id="open-csa-wp-addProduct_div" 
		<?php 
			if ($display == false) {
				echo 'style="display:none"';
			}
		?>	
	>
		<form method="POST" id='open-csa-wp-showNewProduct_form'>
			<table class="form-table">
				<tr valign="top">
					<td>
					<input 
						type='text' 
						<?php 
							if ($product_id != null && $product_info[0]->name != "" && $product_info[0]->name != null) {
								echo "value='".$product_info[0]->name."'"; 
							}
						?>
						placeholder='<?php _e('Product Name', OPEN_CSA_WP_DOMAIN)?> *' 
						name="open-csa-wp-product_name_input" 
						required></td></tr>
				<tr valign="top"><td>
					<select 
						name="open-csa-wp-product_category_input" 
						id="open-csa-wp-newProductForm_category_input_id"
						<?php 
							if ($product_id == null) {
								echo "style='color:#999'";
							}		
						?>
						onfocus = '
							getElementById("open-csa-wp-newProductForm_category_input_span_id").style.display = "none";
						'
						onchange = '
							this.style.color="black"
							if (this.options[this.selectedIndex].text.split(" ")[0] != "<?php _e('Category is', OPEN_CSA_WP_DOMAIN)?>".split(" ")[0]) {
								this.options[this.selectedIndex].text = "<?php _e('Category is', OPEN_CSA_WP_DOMAIN)?> " + this.options[this.selectedIndex].text;
							}
						'
					>
					<option 
						value="" 
						selected='selected' 
						disabled='disabled'
						id = "open-csa-wp-newProductForm_category_input_disabled_id"
					><?php _e("Category", OPEN_CSA_WP_DOMAIN)?> *</option>
 					<?php echo open_csa_wp_select_options_from_db(
									array("name"), 
									"id", 
									OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES, 
									($product_id != null)?$product_info[0]->category:null,
									__("Category is ", OPEN_CSA_WP_DOMAIN)
								); ?>
                  	</select>
					<span id="open-csa-wp-newProductForm_category_input_span_id"></span>
				</td></tr>
					
				<tr valign="top"><td>
					<select 
						name="open-csa-wp-product_producer_input"
						id="open-csa-wp-newProductForm_producer_input_id"
						onfocus = '
							getElementById("open-csa-wp-newProductForm_producer_input_span_id").style.display = "none";
						'					
						onchange = '
							this.style.color="black"
							if (this.options[this.selectedIndex].text.split(" ")[0] != "<?php _e('Producer is', OPEN_CSA_WP_DOMAIN)?>".split(" ")[0]) {
								this.options[this.selectedIndex].text = "<?php _e('Producer is', OPEN_CSA_WP_DOMAIN)?> " + this.options[this.selectedIndex].text;
							}
						'
						<?php 
							if ($product_id == null) { 
								echo "style='color:#999'";
							}
						?>
					>
						<option 
							value="" 
							<?php 
								if ($product_id == null) 
									echo "selected='selected'";
							?>
							disabled='disabled'
							id = "open-csa-wp-newProductForm_producer_input_disabled_id"
						><?php _e("Producer", OPEN_CSA_WP_DOMAIN)?> *</option>
						<?php echo open_csa_wp_select_users_of_type("producer", ($product_id!=null)?$product_info[0]->producer:null, __("Producer is ", OPEN_CSA_WP_DOMAIN)); ?>
					</select>
					<span id="open-csa-wp-newProductForm_producer_input_span_id"></span>
				</td></tr>

				<tr valign="top">
					<td>
					<input 
						type='text' 
						onfocus = ' 
							getElementById ("open-csa-wp-showNewProduct_button_id").disabled=true;
							if (this.value != "") {
								this.value = (this.value.split(" ").slice(2)).join(" ");
							}
						'
						onblur = '
							getElementById ("open-csa-wp-showNewProduct_button_id").disabled=false;
							if (this.value != "") {
								this.value = "<?php _e('Variety is', OPEN_CSA_WP_DOMAIN)?> "+ this.value;
							}
						'
						<?php 
							if ($product_id != null && $product_info[0]->variety != "" && $product_info[0]->variety != null) {
								echo "value='".__('Variety is', OPEN_CSA_WP_DOMAIN)." ".$product_info[0]->variety."'"; 
							}
						?>
						placeholder='<?php _e('Variety', OPEN_CSA_WP_DOMAIN)?> *' 
						required 
						name="open-csa-wp-product_variety_input">
					</td>
				</tr>
				<tr valign="top">
					<td>
					<input 
						min='0' step='0.1'
						<?php 
							if ($product_id != null && $product_info[0]->current_price_in_euro != "" && $product_info[0]->current_price_in_euro != null) {
								echo "type='text'";
								echo "style='width:8em; text-align:right'";
								echo 'value = "'.__('It costs', OPEN_CSA_WP_DOMAIN).' '. $product_info[0]->current_price_in_euro. '"';
							} else {
								echo "type='number'";
								echo "style='width:8em'";
							}
						?>
						placeholder='<?php _e('Price', OPEN_CSA_WP_DOMAIN); ?> *' 
						onfocus = '
							getElementById ("open-csa-wp-showNewProduct_button_id").disabled=true;
							this.value = this.value.split(" ")[2];
							this.type = "number";
						'
						onblur = '
							getElementById ("open-csa-wp-showNewProduct_button_id").disabled=false;
							this.type = "text";
							if (this.value == "") {
								this.style.textAlign="left";
							} else {
								this.value = "<?php _e('It costs', OPEN_CSA_WP_DOMAIN) ?> " + this.value;
								this.style.textAlign="right";
							}
						'
						name="open-csa-wp-product_price_input" required> € &nbsp;
					<select 
						name="open-csa-wp-product_unit_input" 
						id="open-csa-wp-newProductForm_unit_input_id"
						<?php 
							if ($product_id == null) {
								echo "style='color:#999'";
							}
						?>
						onfocus = '
							getElementById("open-csa-wp-newProductForm_unit_input_span_id").style.display = "none";
						'
						onchange = '
							this.style.color="black";
							if (this.options[this.selectedIndex].text.split(" ")[0] != "<?php _e('per', OPEN_CSA_WP_DOMAIN); ?>") {
								this.options[this.selectedIndex].text = "<?php _e('per', OPEN_CSA_WP_DOMAIN); ?> " + this.options[this.selectedIndex].text;
							}
					'>
						<option 
							value="" 
							<?php 
								if ($product_id == null) {
									echo "selected='selected'"; 
								}
							?>
							disabled='disabled'
							id = "open-csa-wp-newProductForm_unit_input_disabled_id"
						><?php _e("per...",OPEN_CSA_WP_DOMAIN)?> *</option>
						<?php echo open_csa_wp_select_measurement_unit($product_id, $product_info); ?>
					</select> 
					<span id="open-csa-wp-newProductForm_unit_input_span_id"></span>
				</td></tr>
				<tr valign="top">
					<td>
						<textarea placeholder='<?php _e('Description', OPEN_CSA_WP_DOMAIN); ?>' rows="3" cols="30" name="open-csa-wp-product_descritpion_input"
						><?php 
							if ($product_id != null && $product_info[0]->description != "" && $product_info[0]->description != null) {
								echo $product_info[0]->description; 
							}
						?></textarea></td></tr>


				<tr valign="top"><td>
					<select 
					name="open-csa-wp-product_availability_input" 
					id="open-csa-wp-newProductForm_availability_input_id"
					<?php 
						if ($product_id == null) {
							echo "style='color:#999'";
						} else if ($product_info[0]->is_available == 1) {
							echo "style='color:green'";
						} else {
							echo "style='color:brown'";
						}
					?>
					onfocus = '
							getElementById("open-csa-wp-newProductForm_availability_input_span_id").style.display = "none";
						'
					onchange='
						if (this.options[this.selectedIndex].value == "yes") {
							this.style.color = "green";
							this.options[this.selectedIndex].text = "<?php _e('Currently, it is available', OPEN_CSA_WP_DOMAIN); ?>"
						} else {
							this.style.color = "brown";
							this.options[this.selectedIndex].text = "<?php _e('Currently, it is not available', OPEN_CSA_WP_DOMAIN); ?>"
						}
						'
				>
					<option 
						value="" 
						<?php 
							if ($product_id == null) {
								echo "selected='selected'"; 
							}
						?>
						disabled='disabled'
						id = "open-csa-wp-newProductForm_availability_input_disabled_id"
					><?php _e('Available?', OPEN_CSA_WP_DOMAIN); ?> *</option>
					<?php 
						if ($product_id != null) {
							echo '
								<option value="yes" style="color:green". '. ($product_info[0]->is_available == 1?"selected='selected'> ". __('Currently, it is available', OPEN_CSA_WP_DOMAIN):">". __('yes', OPEN_CSA_WP_DOMAIN)) .' </option>
								<option value="no" style="color:brown"'. ($product_info[0]->is_available == 0?"selected='selected'> ". __('Currently, it is not available', OPEN_CSA_WP_DOMAIN):">". __('no', OPEN_CSA_WP_DOMAIN)) .' </option>
							';
						} else {
						?>
							<option value="yes" style="color:green"><?php _e('yes', OPEN_CSA_WP_DOMAIN); ?></option>
							<option value="no" style="color:brown"><?php _e('no', OPEN_CSA_WP_DOMAIN); ?></option>
						<?php
						}
					?>					
					</select>
					<span id="open-csa-wp-newProductForm_availability_input_span_id"></span>
				</td></tr>
			</table> 
		<input 
			type="submit" 
			name="Add Product"  
			class="button button-primary"
			id="open-csa-wp-showNewProduct_button_id"
			<?php 
				if ($product_id == null) {
					echo "value='". __('Add Product',OPEN_CSA_WP_DOMAIN) ."'";
					echo "onclick='open_csa_wp_new_product_fields_validation(this, null, \"$page_url\")'";
				} else { 
					echo "value='". __('Update Product',OPEN_CSA_WP_DOMAIN) ."'";
					echo "onclick='open_csa_wp_new_product_fields_validation(this, $product_id, \"$page_url\")'";
				}
				
			?>
		/>
		<input 
			type="button"
			class="button button-secondary"
			<?php 
			if ($product_id == null) {
				echo "
				value='". __('Reset Info',OPEN_CSA_WP_DOMAIN) ."'
				onclick='open_csa_wp_reset_product_form();'";
			}
			else {
				echo "
				value='". __('Cancel',OPEN_CSA_WP_DOMAIN) ."'
				onclick='window.location.replace(\"$page_url\")'
				'";
			}
			?>
		/>
		
		</form>
		<br/><br/>
	</div>
	
<?php

}

function open_csa_wp_select_measurement_unit($product_id, $product_info) {
?>
	<option 
		value='kilogram'
		<?php
			if ($product_id != null && $product_info[0]->measurement_unit == "kilogram" ) {
				echo "selected='selected' >". __('per kilogram',OPEN_CSA_WP_DOMAIN); 
			} else {
				echo ">". __('kilogram',OPEN_CSA_WP_DOMAIN);
			}
		?>
	</option>
	<option 
		value='piece'
		<?php 
			if ($product_id != null && $product_info[0]->measurement_unit == "piece" ) {
				echo "selected='selected' >". __('per piece',OPEN_CSA_WP_DOMAIN); 
			} else {
				echo ">". __('piece',OPEN_CSA_WP_DOMAIN);
			}
		?>
	</option>
	<option 
		value='bunch'
		<?php 
			if ($product_id != null && $product_info[0]->measurement_unit == "bunch" ) {
				echo "selected='selected' >". __('per bunch',OPEN_CSA_WP_DOMAIN); 
			} else {
				echo ">". __('bunch',OPEN_CSA_WP_DOMAIN);
			}
		?>
	</option>
	<option 
		value='litre'
		<?php 
			if ($product_id != null && $product_info[0]->measurement_unit == "litre" ) {
				echo "selected='selected' >". __('per litre',OPEN_CSA_WP_DOMAIN); 
			} else {
				echo ">". __('litre',OPEN_CSA_WP_DOMAIN);
			}
		?>
	</option>
<?php
}


add_action( 'wp_ajax_open-csa-wp-product_add_or_update_request', 'open_csa_wp_add_or_update_product' );

function open_csa_wp_add_or_update_product() {

	if( isset($_POST['data']) && isset($_POST['product_id'])) {

		$data_received = json_decode(stripslashes($_POST['data']),true);
		
		$variety_message = "Variety is ";
		$variety = substr($data_received[3]['value'], strlen($variety_message)); 
		$price_message = "it costs ";
		$price = substr($data_received[4]['value'], strlen($price_message)); 
		
		$data_vals = array(
					'name' 						=> $data_received[0]['value'],
					'category' 					=> $data_received[1]['value'],
					'producer' 					=> $data_received[2]['value'],
					'variety'					=> $variety,
					'current_price_in_euro'		=> $price,
					'measurement_unit'	 		=> $data_received[5]['value'],
					'description'				=> $data_received[6]['value'],
					'is_available' 				=> $data_received[7]['value'] == "yes"?1:0
				);
		$data_types = array ("%s", "%d", "%d", "%s", "%f", "%s", "%s", "%d");
		
		global $wpdb;
	
		$product_id = intval(open_csa_wp_clean_input($_POST['product_id']));
	
		if ($product_id != null) {
			$product_id = intval($product_id);
			
			//update product (query)
			if(	$wpdb->update(
				OPEN_CSA_WP_TABLE_PRODUCTS, 
				$data_vals, 
				array('id' => $product_id), 
				$data_types
			) === FALSE) {
				echo 'error, sql request failed.';
			} else {
				echo 'Success, product is updated.';
			}
		
		}
		else { 
			//insert product (query)
			if(	$wpdb->insert(
				OPEN_CSA_WP_TABLE_PRODUCTS, 
				$data_vals, 
				$data_types
			) === FALSE) {
				echo 'error, sql request failed.';
			} else {
				echo 'Success, product is added.';
			}
		}
	}
	else echo 'error,Bad request.';
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

function open_csa_wp_show_products($display, $page_url) {
	wp_enqueue_script('open-csa-wp-general-scripts');
	wp_enqueue_script('open-csa-wp-products-scripts');
	wp_enqueue_script('jquery.datatables');
	wp_enqueue_script('jquery.jeditable'); 
	wp_enqueue_script('jquery.blockui'); 	
?>
		
	<br />
	<div id="open-csa-wp-showProductsList_header">
		<span 
			style="cursor:pointer" 
			id="open-csa-wp-showProductsList_formHeader_text" 
			onclick="open_csa_wp_toggle_form('showProductsList','Product List', '')">
			<font size='4'>
			<?php 
				if ($display == false) {
					echo __('Product List', OPEN_CSA_WP_DOMAIN) .' ('. __('show',OPEN_CSA_WP_DOMAIN) .')';
				} else {
					echo __('Product List', OPEN_CSA_WP_DOMAIN) .' ('. __('hide',OPEN_CSA_WP_DOMAIN) .')';
				}
			?>
			</font>
		</span>
	</div>
	<div id="open-csa-wp-showProductsList_div" 
		<?php 
			if ($display == false) {
				echo 'style="display:none"';
			}
		?>	
	>
		
		<span class='open-csa-wp-tip_products' title='
			If you want to update one among the name, variety, and description fields, click on it, write the new value, and then press ENTER.
			| To change the availilability of a product, you can either click on its field or click the "eye" icon.
			| If you want to edit some of the other product details, click on the "pen" icon.
			| If you want to delete some product, click on the "x" icon.
			'>
		<p style="color:green;font-style:italic; font-size:13px">
			by pointing here you can read additional information...</p></span>


		<table 
			class='table-bordered' 
			id="open-csa-wp-showProductsList_table" 
			style='border-spacing:1em'
			open-csa-wp-plugins_dir='<?php echo plugins_url(); ?>' 
		> 
		<thead class='tableHeader'>
			<tr>
				<th><?php _e('Name', OPEN_CSA_WP_DOMAIN)?></th>
				<th><?php _e('Category', OPEN_CSA_WP_DOMAIN)?></th>
				<th><?php _e('Variety', OPEN_CSA_WP_DOMAIN)?></th>
				<th><?php _e('Price', OPEN_CSA_WP_DOMAIN)?>(€)</th>
				<th><?php _e('Per', OPEN_CSA_WP_DOMAIN)?>...</th>
				<th><?php _e('Producer', OPEN_CSA_WP_DOMAIN)?></th>
				<th><?php _e('Description', OPEN_CSA_WP_DOMAIN)?></th>
				<th><?php _e('Available?', OPEN_CSA_WP_DOMAIN)?></th>
				<th/>
				<th/>
				<th/>
			</tr>
		</thead> 
		<tbody> <?php
			global $wpdb;
			$plugins_dir = plugins_url();
			
			$product_categories_map = $wpdb->get_results("SELECT id,name FROM ".OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES, OBJECT_K);
			$producers_map = open_csa_wp_producers_map_array();


			$products = $wpdb->get_results("SELECT * FROM ". OPEN_CSA_WP_TABLE_PRODUCTS);
			foreach($products as $row) 
			{
				$product_id = $row->id;				
				$category = $product_categories_map[$row->category]->name;
				$producer_id = $wpdb->get_var($wpdb->prepare("SELECT producer FROM ". OPEN_CSA_WP_TABLE_PRODUCTS ." WHERE id=%d", $product_id));
				$producer = $producers_map[$producer_id];
				
				echo "
					<tr 
						valign='top' 
						id='open-csa-wp-showProductsProductID_$product_id'  
						class='open-csa-wp-showProducts-product'
						style='text-align:center;color:". (($row->is_available == '1')?"black":"gray") ."'
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
						id = 'open-csa-wp-showProductsAvailabilityID_$product_id'
					>".(($row->is_available == 1)?"yes":"no")."</td>
					<td style='text-align:center'><img 
							style='cursor:pointer' 
							src='".plugins_url()."/open-csa-wp/icons/".(($row->is_available == 1)?"visible":"nonVisible").".png' 
							height='24' width='24' 
							id = 'open-csa-wp-showProductsAvailabilityIconID_$product_id'
							title='". __('mark it as', OPEN_CSA_WP_DOMAIN)." ".(($row->is_available == 1)?__('unavailable', OPEN_CSA_WP_DOMAIN):__('available', OPEN_CSA_WP_DOMAIN))."'
							onclick='open_csa_wp_request_toggle_product_visibility(this,\"$plugins_dir\")'></td>
					<td style='text-align:center'> 
						<img 
							width='24' height='24'  
							class='delete no-underline' 
							src='$plugins_dir/open-csa-wp/icons/edit.png' 
							style='cursor:pointer;padding-left:10px;' 
							onclick='open_csa_wp_edit_product(this, \"$page_url\")' 
							title='". __('click to edit this product', OPEN_CSA_WP_DOMAIN)."'/></td>
					<td style='text-align:center'> <img 
						style='cursor:pointer' 
						src='".plugins_url()."/open-csa-wp/icons/delete.png' 
						height='24' width='24'
						onmouseover='open_csa_wp_hover_icon(this, \"delete\", \"$plugins_dir\")' 
						onmouseout='open_csa_wp_unhover_icon(this, \"delete\", \"$plugins_dir\")' 						
						onclick='open_csa_wp_request_delete_product(this)' 
						title='". __('delete product', OPEN_CSA_WP_DOMAIN)."'></td>
					</tr>
				";
						
			}
			?>
		</tbody> </table>
	</div>	
<?php
}

add_action( 'wp_ajax_open-csa-wp-update_product', 'open_csa_wp_update_product' );

function open_csa_wp_update_product() {
	if(isset($_POST['value']) && isset($_POST['column']) && isset($_POST['product_id'])) {
		//$old_value = open_csa_wp_clean_input($_POST['old_val']);
		$new_value = open_csa_wp_clean_input($_POST['value']);
		$column_num = intval(open_csa_wp_clean_input($_POST['column']))+1; //not valid for getting the right column, when html table structure differs from the relative db table
		$product_id = intval(open_csa_wp_clean_input($_POST['product_id']));
		if ($column_num == 8) {
			$new_value = ($new_value == "yes"?1:0);
		}
		
		if(!empty($column_num) && !empty($product_id)) {
			// Updating the information 
			global $wpdb;
			//get csa_product's column names and assign them to an array
			$columns = $wpdb->get_results("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '".OPEN_CSA_WP_TABLE_PRODUCTS."' ORDER BY ORDINAL_POSITION", ARRAY_N);
			//update the database, using the relative column name
			$column_name = $columns[$column_num][0];

			if(	$wpdb->update(
				OPEN_CSA_WP_TABLE_PRODUCTS,
				array($column_name => $new_value), 
				array('id' => $product_id )
			) === FALSE) {
				echo 'error, sql request failed.';											
			} else {
				echo 'success,'.$new_value;
			}
		} else {
			echo 'error,Empty values.';
		}
	} else {
		echo 'error,Bad request.';
	}
	
	wp_die(); 	// this is required to terminate immediately and return a proper response

}

add_action( 'wp_ajax_open-csa-wp-update_product_availability', 'open_csa_wp_update_product_availability' );

function open_csa_wp_update_product_availability() {
	if(isset($_POST['product_id']) && isset($_POST['availability'])) {
		$product_id = intval($_POST['product_id']);
		$availability = $_POST['availability'];

		global $wpdb;		
		if(	$wpdb->update(
			OPEN_CSA_WP_TABLE_PRODUCTS,
			array("is_available" => $availability), 
			array('id' => $product_id)
		) === FALSE) {
			echo 'error, sql request failed';												
		} else {
			echo 'success, Availability has been updated.';
		}
	} else {
		echo 'error,Invalid request made.';
	}
	
	wp_die(); 	// this is required to terminate immediately and return a proper response
}

add_action( 'wp_ajax_open-csa-wp-delete_product', 'open_csa_wp_delete_product' );

function open_csa_wp_delete_product() {
	if(isset($_POST['product_id'])) {
		$product_id = intval(open_csa_wp_clean_input($_POST['product_id']));
		if(!empty($product_id)) {
			// Updating the information 
			global $wpdb;

			$product_is_used = $wpdb->get_var($wpdb->prepare("
									SELECT COUNT(product_id)
									FROM ".OPEN_CSA_WP_TABLE_PRODUCT_ORDERS." 
									WHERE product_id=%d", $product_id));
			if ($product_is_used > 0) {
				echo 'skipped, used in orders';
			} else {			
				if(	$wpdb->delete(
					OPEN_CSA_WP_TABLE_PRODUCTS,
					array('id' => $product_id ),
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

function  open_csa_wp_delivery_products_exist (){
	global $wpdb;
	if ($wpdb->get_var("SELECT COUNT(id) FROM " .OPEN_CSA_WP_TABLE_PRODUCTS. " WHERE is_available = 1") == 0) {
		echo "
			<h3 style='color:brown'>".__('sorry... no available products found... be patient, soon they will have grown enough... !', OPEN_CSA_WP_DOMAIN)."</h3> 
		";
		return false;
	} else {
		return true;	
	}
}
