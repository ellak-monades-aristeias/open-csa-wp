var $j = jQuery.noConflict();
$j(document).ready(function() {
	var order_tips = $j('.csa-wp-plugin-tip_order');

	if (order_tips.length > 0)
	  order_tips.cluetip({
		splitTitle: '|',							 
		showTitle: false,
		hoverClass: 'highlight',
		open: 'slideDown', 
		openSpeed: 'slow'
	  });
	  
	  
	var table = $j("#csa-wp-plugin-showAllUserOrdersList_table");
	if (table.length > 0) {
		table.dataTable({
			"bPaginate": false, 
			"bStateSave": true, 
			"bInfo": false, 
			"bFilter": false,
			"bSort" : true
		});
	}
  
	table = $j("#csa-wp-plugin-showDeliveryOrdersListProducer_table");
	if (table.length > 0) {
		table.dataTable({
			"bPaginate": false, 
			"bStateSave": true, 
			"bInfo": false, 
			"bFilter": false,
			"bSort" : true
		});
	}
	
	table = $j("#csa-wp-plugin-showDeliveryOrdersList_table");
	if (table.length > 0) {
		table.dataTable({
			"bPaginate": false, 
			"bStateSave": true, 
			"bInfo": false, 
			"bFilter": false,
			"bSort" : true
		});
	}
	
	table = $j("#csa-wp-plugin-showUserOrder_table");
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
			//"submit" : "<img src='" + "<?php echo plugins_url(); ?>" + "/csa-wp-plugin/icons/ok.png'>",
			//"cancel" : "<img src='" + "<?php echo plugins_url(); ?>" + "/csa-wp-plugin/icons/cancel.png'>",
			"tooltip": "click to change...",
			"placeholder": "click to fill ...",
			"onblur": "cancel",
			"loadtype": 'POST',
		};
		
		//edit any value of any object (of class .editable)
		$j(".editable_product_order_quantity", o_table.fnGetNodes()).editable(
			function(value, settings) { 
				var field = this;
				var old_value = o_table.fnGetData(field);
			
				if (value == 0)	{
					csa_wp_plugin_request_delete_product_order(this, old_value, field, o_table);
				} else {
					var tmp = this;
					
					var parts = tmp.parentNode.getAttribute("id").split('_');
				
					var data_post = {
						"action" : "csa-wp-plugin-update_user_order_product_quantity",
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
								alert("You can not update the quantity of your product orders, since the order deadline has been reached for this delivery. For any change, please contact either an administrator or the responsible for this delivery.");
								o_table.fnUpdate(old_value, aPos[0], aPos[1]);
							} else {			
								csa_wp_plugin_calc_editable_order_cost(tmp);
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


function csa_wp_plugiin_calc_new_order_cost() {
	var $j = jQuery.noConflict();
	var serialized_form_data = $j('#csa-wp-plugin-sumbitOrder_form_id').serializeArray();
	var total_cost = 0;
	var price, quantity;
		
	$j.each(serialized_form_data, function(i, field){
		if (field.name == "csa-wp-plugin-order_productPrice") {
			price = field.value;
		}
		if (field.name == "csa-wp-plugin-order_productQuantity") {
			total_cost += field.value * price;
		}
	});

	document.getElementById('csa-wp-plugin-totalCalc').innerHTML = 'Total: <span style=font-weight:bold;color:green>' + total_cost.toFixed(2) + ' €</span>';
}

function csa_wp_plugin_calc_editable_order_cost() {
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

function csa_wp_plugin_new_order_validation(delivery_id,user_id, current_user_id,btn) {
	btn.disabled = true;
	
	var $j = jQuery.noConflict();
	var serialized_form_data = $j('#csa-wp-plugin-sumbitOrder_form_id').serializeArray();
	
	var empty_order = true;
	$j.each(serialized_form_data, function(i, field){
		if (field.name == "csa-wp-plugin-order_productQuantity" && field.value != null && field.value > 0) {
			empty_order = false;
		}
	});
	
	if (empty_order) {
		document.getElementById("csa-wp-plugin-showNewOrderForm_emptyOrder_span_id").innerHTML = "Your order is still empty...";
		btn.disabled = false;
	} else {
		csa_wp_plugin_request_submit_order_to_server(delivery_id, user_id, current_user_id, btn);
	}
}

function csa_wp_plugin_request_submit_order_to_server(delivery_id,user_id, current_user_id, btn) {

	var $j = jQuery.noConflict();
	var serialized_form_data = $j('#csa-wp-plugin-sumbitOrder_form_id').serializeArray();
	serialized_form_data = JSON.stringify(serialized_form_data);
	
	var data = {
		'action': 'csa-wp-plugin-add_new_or_update_order',
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
				alert("You can not add new or upate your order for this delivery, since its order deadline has been reached. For any additional information, please contact either an administrator or the responsible for this delivery.");
			} else {
				btn.disabled = false;
				location.reload(true);
			}
		});
}

function csa_wp_plugin_request_delete_product_order(product, old_value, field, o_table) {

	var $j = jQuery.noConflict();		
	var product_tr = $j(product).closest("tr");

	var parts = $j(product_tr).attr("id").split('_');
	
	//update database
	var data = {
		"action" : "csa_wp_plugin_delete_user_product_order",
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
				alert("You can not delete your product order, since the order deadline has been reached for this delivery. For any change, please contact either an administrator or the responsible for this delivery.");
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
						
						csa_wp_plugin_calc_editable_order_cost();
						
						if ($j('#csa-wp-plugin-showUserOrder_table .csa-wp-plugin-user-order-product').length == 0) {
							location.reload(true);
						}
				});
			}
		}
	);
}

function csa_wp_plugin_request_cancel_user_order(delivery_id, user_id, current_user_id) {
	var $j = jQuery.noConflict();		
	
	//update database
	var data = {
		"action" : "csa-wp-plugin-delete_user_order",
		"delivery_id" : delivery_id,
		"user_id" : user_id,
		'current_user_id' : current_user_id
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");
			var return_values = response.split(",");
						
			if (return_values[0] == "skipped") {
				alert("You can not cancel your product order, since the order deadline has been reached for this delivery. For any change, please contact either an administrator or the responsible for this delivery.");
			} else {
				location.reload(true);
			}
		}
	);
}

function csa_wp_plugin_request_user_order_update (delivery_id, user_id, comments) {
	
	var $j = jQuery.noConflict();		

	//update database
	var data = {
		"action" : "csa-wp-plugin-update_user_order_comments",
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

function csa_wp_plugin_become_responsible(user_id, delivery_id) {
	var $j = jQuery.noConflict();		
	
	//update database
	var data = {
		"action" : "csa-wp-plugin-become_responsible",
		"delivery_id" : delivery_id,
		"user_id" : user_id,
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			console.log ("Server returned:["+response+"]");
			var return_values = response.split(",");
	
			if (return_values[0] == "skipped") {
				alert("You can not become responsible, since another user is already. For any change, please contact either an administrator or the responsible for this delivery.");
			} else {
				location.reload(true);
			}
		}
	);
}

function csa_wp_plugin_edit_user_order(user_id, delivery_id) {
	
	document.getElementById("csa-wp-plugin-showNewOrderForm_user_input_td_id").innerHTML = 
		'<input type="text" name="csa-wp-plugin-showEditableUserOrderForm_user_input" value="'+ user_id +'" />';
	document.getElementById("csa-wp-plugin-showSelectSpotForm_delivery_input_td_id").innerHTML = 
		'<input type="text" name="csa-wp-plugin-showEditableUserOrderForm_delivery_input" value="'+ delivery_id +'" />';
	document.getElementById ("csa-wp-plugin-showNewOrderForm_form_id").submit();
}

function csa_wp_plugin_request_total_orders_of_delivery (delivery_id, producer_id) {
	if (producer_id != null) {
		document.getElementById("csa-wp-plugin-showNewOrderForm_user_input_td_id").innerHTML = 
			'<input type="text" name="csa-wp-plugin-showTotalOrdersOfDelivery_producer_input" value="'+ producer_id +'" />';
	}
	document.getElementById("csa-wp-plugin-showSelectSpotForm_delivery_input_td_id").innerHTML = 
		'<input type="text" name="csa-wp-plugin-showTotalOrdersOfDelivery_delivery_input" value="'+ delivery_id +'" />';
	document.getElementById ("csa-wp-plugin-showNewOrderForm_form_id").submit();	
}