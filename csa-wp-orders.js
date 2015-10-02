var $j = jQuery.noConflict();
$j(document).ready(function() {
	var orderTips = $j('.csa-wp-plugin-tip_order');

	if (orderTips.length > 0)
	  orderTips.cluetip({
		splitTitle: '|',							 
		showTitle: false,
		hoverClass: 'highlight',
		open: 'slideDown', 
		openSpeed: 'slow'
	  });

	  
	var table = $j("#csa-wp-plugin-showUserOrder_table");
	if (table.length > 0) {
		var oTable = table.dataTable({
			"bPaginate": false, 
			"bStateSave": true, 
			"bInfo": false, 
			"bFilter": false,
			"bSort" : false
		});

		var dataEditable = {
			"width" : "5em",
			"height": "3em",
			"type" : "text",
			//"submit" : "<img src='" + "<?php echo plugins_url(); ?>" + "/csa-wp-plugin/icons/ok.png'>",
			//"cancel" : "<img src='" + "<?php echo plugins_url(); ?>" + "/csa-wp-plugin/icons/cancel.png'>",
			"tooltip": "click to change...",
			"onblur": "cancel",
			"loadtype": 'POST',
		};
		
		//edit any value of any object (of class .editable)
		$j(".editable_product_order_quantity", oTable.fnGetNodes()).editable(
			function(value, settings) { 
			
				if (value == 0)	
					CsaWpPluginRequestDeleteProductOrder(this);
				else {
					var tmp = this;
					
					var parts = tmp.parentNode.getAttribute("id").split('_');
				
					var dataPost = {
						"action" : "csa-wp-plugin-update_user_order_product_quantity",
						"value" : value,
						"deliveryID": parts[1],
						"userID": parts[2],
						"productID": parts[3]
					};
					
					$j.post(ajaxurl, dataPost, 
						function(response) { 
							//console.log ("Server returned:["+response+"]");
							
							//var aPos = oTable.fnGetPosition(tmp);
							//oTable.fnUpdate(value, aPos[0], aPos[1]);	
						
							CsaWpPluginCalcEditableOrderCost(tmp);
						}
					);
					return(value);
				}
			}, 
			dataEditable
		);	
	}
});


function CsaWpPluginCalcNewOrderCost() {
	var $j = jQuery.noConflict();
	var serializedFormData = $j('#csa-wp-plugin-sumbitOrder_form_id').serializeArray();
	var totalCost = 0;
	var price, quantity;
		
	$j.each(serializedFormData, function(i, field){
		if (field.name == "csa-wp-plugin-order_productPrice") price = field.value;
		if (field.name == "csa-wp-plugin-order_productQuantity") totalCost += field.value * price;
	});

	document.getElementById('csa-wp-plugin-totalCalc').innerHTML = 'Σύνολο: <span style=font-weight:bold;color:green>' + totalCost.toFixed(2) + ' €</span>';
}

function CsaWpPluginCalcEditableOrderCost() {
	var $j = jQuery.noConflict();
	
	var ordsQuantity = $j('.editable_product_order_quantity');
	var ordsPrice = $j('.editable_product_order_price');
	var ordsCost = $j('.editable_product_order_cost');
	var len = ordsQuantity.length;
	
	var totalCost = 0;
	for (var i=0; i<len; i++) {
		var quantity = ordsQuantity.eq(i).text().replace(' €', '');
		var price = ordsPrice.eq(i).text().replace(' €', '');
		var cost = quantity * price;
		ordsCost.eq(i).text(cost + " €");
		totalCost+=cost;
	}
	
	document.getElementById("editable_product_order_TotalCost").innerHTML = totalCost + " €";
}

function CsaWpPluginNewOrderValidation(deliveryID,userID,btn) {
	btn.disabled = true;
	
	var $j = jQuery.noConflict();
	var serializedFormData = $j('#csa-wp-plugin-sumbitOrder_form_id').serializeArray();
	
	var emptyOrder = true;
	$j.each(serializedFormData, function(i, field){
		if (field.name == "csa-wp-plugin-order_productQuantity" && field.value != null && field.value > 0) emptyOrder = false;
	});
	
	if (emptyOrder) {
		document.getElementById("csa-wp-plugin-showNewOrderForm_emptyOrder_span_id").innerHTML = "Your order is still empty...";
		btn.disabled = false;
	}
	else CsaWpPluginRequestSumbitOrderToServer(deliveryID, userID,btn);
}

function CsaWpPluginRequestSumbitOrderToServer(deliveryID,userID,btn) {

	var $j = jQuery.noConflict();
	var serializedFormData = $j('#csa-wp-plugin-sumbitOrder_form_id').serializeArray();
	serializedFormData = JSON.stringify(serializedFormData);
	
	var data = {
		'action': 'csa-wp-plugin-add_new_or_update_order',
		'deliveryID': deliveryID,
		'userID': userID,
		'data' : serializedFormData
	};
	
	$j.post(ajaxurl, data ,
		function(response){
			//console.log("Server returned: [" + response + "]");
			btn.disabled = false;
			location.reload(true);
		});
}

function CsaWpPluginRequestDeleteProductOrder(product) {

	var $j = jQuery.noConflict();		
	var productTR = $j(product).closest("tr");

	var parts = $j(productTR).attr("id").split('_');
	
	//update database
	var data = {
		"action" : "csa-wp-plugin-delete_user_order_product",
		"deliveryID": parts[1],
		"userID": parts[2],
		"productID": parts[3]
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");
			
			$j(productTR).fadeOut(200,function() {
					var productTDs = $j(productTR).find("td");
					var len = productTDs.length;
					for (var i=0; i<len; i++) $j(productTDs.eq(i)).remove();
														
					$j(productTR).remove();
					
					CsaWpPluginCalcEditableOrderCost();
					
					if ($j('#csa-wp-plugin-showUserOrder_table .csa-wp-plugin-user-order-product').length == 0) location.reload(true);
			});
		}
	);
}

function CsaWpPluginRequestCancelUserOrder(deliveryID, userID, lastDeliveryDate) {
	var $j = jQuery.noConflict();		
	
	//update database
	var data = {
		"action" : "csa-wp-plugin-delete_user_order",
		"deliveryID" : deliveryID,
		"userID" : userID
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			console.log ("Server returned:["+response+"]");
			location.reload(true);
		}
	);
}

function CsaWpPluginEditUserOrder(userID, deliveryID) {
	
	document.getElementById("csa-wp-plugin-showNewOrderForm_user_input_td_id").innerHTML = 
		'<input type="text" name="csa-wp-plugin-showEditableUserOrderForm_user_input" value="'+ userID +'" />';
	document.getElementById("csa-wp-plugin-showSelectSpotForm_delivery_input_td_id").innerHTML = 
		'<input type="text" name="csa-wp-plugin-showEditableUserOrderForm_delivery_input" value="'+ deliveryID +'" />';
	document.getElementById ("csa-wp-plugin-showNewOrderForm_form_id").submit();
}

function CsaWpPluginRequestTotalOrdersOfDelivery (deliveryID, producerID) {
	if (producerID != null)
		document.getElementById("csa-wp-plugin-showNewOrderForm_user_input_td_id").innerHTML = 
			'<input type="text" name="csa-wp-plugin-showTotalOrdersOfDelivery_producer_input" value="'+ producerID +'" />';
	document.getElementById("csa-wp-plugin-showSelectSpotForm_delivery_input_td_id").innerHTML = 
		'<input type="text" name="csa-wp-plugin-showTotalOrdersOfDelivery_delivery_input" value="'+ deliveryID +'" />';
	document.getElementById ("csa-wp-plugin-showNewOrderForm_form_id").submit();	
}