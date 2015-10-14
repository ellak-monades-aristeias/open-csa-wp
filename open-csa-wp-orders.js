var $j = jQuery.noConflict();
$j(document).ready(function() {
	var order_tips = $j('.open-csa-wp-tip_order');

	if (order_tips.length > 0)
	  order_tips.cluetip({
		splitTitle: '|',							 
		showTitle: false,
		hoverClass: 'highlight',
		open: 'slideDown', 
		openSpeed: 'slow'
	  });
	  
	  
	var table = $j("#open-csa-wp-showAllUserOrdersList_table");
	if (table.length > 0) {
		table.dataTable({
			"bPaginate": false, 
			"bStateSave": true, 
			"bInfo": false, 
			"bFilter": false,
			"bSort" : true
		});
	}
  
	table = $j("#open-csa-wp-showDeliveryOrdersListProducer_table");
	if (table.length > 0) {
		table.dataTable({
			"bPaginate": false, 
			"bStateSave": true, 
			"bInfo": false, 
			"bFilter": false,
			"bSort" : true
		});
	}
	
	table = $j("#open-csa-wp-showDeliveryOrdersList_table");
	if (table.length > 0) {
		table.dataTable({
			"bPaginate": false, 
			"bStateSave": true, 
			"bInfo": false, 
			"bFilter": false,
			"bSort" : true
		});
	}
	
	table = $j("#open-csa-wp-showUserOrder_table");
	if (table.length > 0) {
		var o_table = table.dataTable({
			"bPaginate": false, 
			"bStateSave": true, 
			"bInfo": false, 
			"bFilter": false,
			"bSort" : false
		});

		var data_editable = {
			"width" : "5em",
			"height": "3em",
			"type" : "text",
			//"submit" : "<img src='" + "<?php echo plugins_url(); ?>" + "/open-csa-wp/icons/ok.png'>",
			//"cancel" : "<img src='" + "<?php echo plugins_url(); ?>" + "/open-csa-wp/icons/cancel.png'>",
			"tooltip": orders_translation.tooltip,
			"placeholder": orders_translation.placeholder,
			"onblur": "cancel",
			"loadtype": 'POST',
		};
		
		//edit any value of any object (of class .editable)
		$j(".editable_product_order_quantity", o_table.fnGetNodes()).editable(
			function(value, settings) { 
				var field = this;
				var old_value = o_table.fnGetData(field);
			
				if (value == 0)	{
					open_csa_wp_request_delete_product_order(this, old_value, field, o_table);
				} else {
					var tmp = this;
					
					var parts = tmp.parentNode.getAttribute("id").split('_');
				
					var data_post = {
						"action" : "open-csa-wp-update_user_order_product_quantity",
						"value" : value,
						"delivery_id": parts[1],
						"user_id": parts[2],
						"product_id": parts[3],
						"current_user_id": parts[4],
					};
					
					$j.post(ajaxurl, data_post, 
						function(response) { 
							console.log ("Server returned:["+response+"]");
							var aPos = o_table.fnGetPosition(field);
								
							
							var return_values = response.split(",");
				
							if (return_values[0] == "skipped") {
								alert(orders_translation.product_quantity_cannnot_be_updated);
								o_table.fnUpdate(old_value, aPos[0], aPos[1]);
							} else {			
								open_csa_wp_calc_editable_order_cost(tmp);
							}
						}
					);
					return(value);
				}
			}, 
			data_editable
		);	
	}
});


function open_csa_wp_calc_new_order_cost() {
	var $j = jQuery.noConflict();
	var serialized_form_data = $j('#open-csa-wp-sumbitOrder_form_id').serializeArray();
	var total_cost = 0;
	var price, quantity;
		
	$j.each(serialized_form_data, function(i, field){
		if (field.name == "open-csa-wp-order_productPrice") {
			price = field.value;
		}
		if (field.name == "open-csa-wp-order_productQuantity") {
			total_cost += field.value * price;
		}
	});

	document.getElementById('open-csa-wp-totalCalc').innerHTML = orders_translation.total +': <span style=font-weight:bold;color:green>' + total_cost.toFixed(2) + ' €</span>';
}

function open_csa_wp_calc_editable_order_cost() {
	var $j = jQuery.noConflict();
	
	var ords_quantity = $j('.editable_product_order_quantity');
	var ords_price = $j('.editable_product_order_price');
	var ords_cost = $j('.editable_product_order_cost');
	var len = ords_quantity.length;
	
	var total_cost = 0;
	for (var i=0; i<len; i++) {
		var quantity = ords_quantity.eq(i).text().replace(' €', '');
		var price = ords_price.eq(i).text().replace(' €', '');
		var cost = quantity * price;
		ords_cost.eq(i).text(cost + " €");
		total_cost+=cost;
	}
	
	document.getElementById("editable_product_order_TotalCost").innerHTML = total_cost + " €";
}

function open_csa_wp_new_order_validation(delivery_id,user_id, current_user_id,btn) {
	btn.disabled = true;
	
	var $j = jQuery.noConflict();
	var serialized_form_data = $j('#open-csa-wp-sumbitOrder_form_id').serializeArray();
	
	var empty_order = true;
	$j.each(serialized_form_data, function(i, field){
		if (field.name == "open-csa-wp-order_productQuantity" && field.value != null && field.value > 0) {
			empty_order = false;
		}
	});
	
	if (empty_order) {
		document.getElementById("open-csa-wp-showNewOrderForm_emptyOrder_span_id").innerHTML = orders_translation.empty_order;
		btn.disabled = false;
	} else {
		open_csa_wp_request_submit_order_to_server(delivery_id, user_id, current_user_id, btn);
	}
}

function open_csa_wp_request_submit_order_to_server(delivery_id,user_id, current_user_id, btn) {

	var $j = jQuery.noConflict();
	var serialized_form_data = $j('#open-csa-wp-sumbitOrder_form_id').serializeArray();
	serialized_form_data = JSON.stringify(serialized_form_data);
	
	var data = {
		'action': 'open-csa-wp-add_new_or_update_order',
		'delivery_id': delivery_id,
		'user_id': user_id,
		'data' : serialized_form_data,
		'current_user_id' : current_user_id
	};
	
	$j.post(ajaxurl, data ,
		function(response){
			//console.log("Server returned: [" + response + "]");
			
			var return_values = response.split(",");
				if (return_values[0] == "skipped") {
				alert(orders_translation.cannnot_add_or_update_order);
			} else {
				btn.disabled = false;
				location.reload(true);
			}
		});
}

function open_csa_wp_request_delete_product_order(product, old_value, field, o_table) {

	var $j = jQuery.noConflict();		
	var product_tr = $j(product).closest("tr");

	var parts = $j(product_tr).attr("id").split('_');
	
	//update database
	var data = {
		"action" : "open_csa_wp_delete_user_product_order",
		"delivery_id": parts[1],
		"user_id": parts[2],
		"product_id": parts[3],
		"current_user_id": parts[4]
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");

			var return_values = response.split(",");
						
			if (return_values[0] == "skipped") {
				alert(orders_translation.cannnot_delete_product);
				if (old_value != null) {
					var aPos = o_table.fnGetPosition(field);
					o_table.fnUpdate(old_value, aPos[0], aPos[1]);
				}
			} else {	
				$j(product_tr).fadeOut(200,function() {
						var product_tds = $j(product_tr).find("td");
						var len = product_tds.length;
						for (var i=0; i<len; i++) $j(product_tds.eq(i)).remove();
															
						$j(product_tr).remove();
						
						open_csa_wp_calc_editable_order_cost();
						
						if ($j('#open-csa-wp-showUserOrder_table .open-csa-wp-user-order-product').length == 0) {
							location.reload(true);
						}
				});
			}
		}
	);
}

function open_csa_wp_request_cancel_user_order(delivery_id, user_id, current_user_id) {
	var $j = jQuery.noConflict();		
	
	//update database
	var data = {
		"action" : "open-csa-wp-delete_user_order",
		"delivery_id" : delivery_id,
		"user_id" : user_id,
		'current_user_id' : current_user_id
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");
			var return_values = response.split(",");
						
			if (return_values[0] == "skipped") {
				alert(orders_translation.cannnot_cancel_order);
			} else {
				location.reload(true);
			}
		}
	);
}

function open_csa_wp_request_user_order_update (delivery_id, user_id, comments) {
	
	var $j = jQuery.noConflict();		

	//update database
	var data = {
		"action" : "open-csa-wp-update_user_order_comments",
		"delivery_id" : delivery_id,
		"user_id" : user_id,
		"comments" : comments
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");
		}
	);
}

function open_csa_wp_become_responsible(user_id, delivery_id) {
	var $j = jQuery.noConflict();		
	
	//update database
	var data = {
		"action" : "open-csa-wp-become_responsible",
		"delivery_id" : delivery_id,
		"user_id" : user_id,
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			console.log ("Server returned:["+response+"]");
			var return_values = response.split(",");
	
			if (return_values[0] == "skipped") {
				alert(orders_translation.cannnot_become_responsible);
			} else {
				location.reload(true);
			}
		}
	);
}

function open_csa_wp_edit_user_order(user_id, delivery_id) {
	
	document.getElementById("open-csa-wp-showNewOrderForm_user_input_td_id").innerHTML = 
		'<input type="text" name="open-csa-wp-showEditableUserOrderForm_user_input" value="'+ user_id +'" />';
	document.getElementById("open-csa-wp-showSelectSpotForm_delivery_input_td_id").innerHTML = 
		'<input type="text" name="open-csa-wp-showEditableUserOrderForm_delivery_input" value="'+ delivery_id +'" />';
	document.getElementById ("open-csa-wp-showNewOrderForm_form_id").submit();
}

function open_csa_wp_request_total_orders_of_delivery (delivery_id, producer_id) {
	if (producer_id != null) {
		document.getElementById("open-csa-wp-showNewOrderForm_user_input_td_id").innerHTML = 
			'<input type="text" name="open-csa-wp-showTotalOrdersOfDelivery_producer_input" value="'+ producer_id +'" />';
	}
	document.getElementById("open-csa-wp-showSelectSpotForm_delivery_input_td_id").innerHTML = 
		'<input type="text" name="open-csa-wp-showTotalOrdersOfDelivery_delivery_input" value="'+ delivery_id +'" />';
	document.getElementById ("open-csa-wp-showNewOrderForm_form_id").submit();	
}