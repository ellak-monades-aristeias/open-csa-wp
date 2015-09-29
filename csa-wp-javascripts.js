/* *********************
 * ** --- GENERAL --- **
 * ********************* 
 */

function CsaWpPluginSlideToggle (which){
	var $j = jQuery.noConflict();
	$j(which).slideToggle("slow");
}

function CsaWpPluginHoverIcon(element, iconName, pluginsDir) {
	element.setAttribute('src', pluginsDir + '/csa-wp-plugin/icons/' + iconName +'_hover.png');
}

function CsaWpPluginUnHoverIcon(element, iconName, pluginsDir) {
	element.setAttribute('src', pluginsDir + '/csa-wp-plugin/icons/' + iconName +'.png');
}

function CsaWpPluginToggleForm(text1, text2, text3, textSize, text4) {
	var $j = jQuery.noConflict();
	
	if (textSize == null) textSize=4;
	if (text4 == null) text4 = "";
	
	if (document.getElementById("csa-wp-plugin-"+ text1 +"_div").style.display == "none")
		document.getElementById("csa-wp-plugin-"+ text1 +"_formHeader_text").innerHTML = "<font size='"+ textSize +"'>" + text4 + text2 + " (hide" + text3 +") </font>";
	else document.getElementById("csa-wp-plugin-"+ text1 +"_formHeader_text").innerHTML = "<font size='"+ textSize +"'>" + text4 + text2 + " (show" + text3 +") </font>";

	$j("#csa-wp-plugin-"+ text1 +"_div").slideToggle("slow");

}

function CsaWpPluginYouForgotThisOne (span) {
	span.innerHTML = "<i style='color:brown'>&nbsp;&nbsp; you forgot this one...</i>"
	span.style.display = "inline";
}

function CsaWpPluginValidateDeliveryTimePeriod (textToFill) {
	var obj1 = document.getElementById("csa-wp-plugin-" + textToFill + "_delivery_start_time_input_id");
	var text1 = obj1.value;
	var obj2 = document.getElementById("csa-wp-plugin-" + textToFill + "_delivery_end_time_input_id");
	var text2 = obj2.value;
		
	var message = document.getElementById("csa-wp-plugin-" + textToFill + "_invalidDeliveryTime_span");
	
	if (text1 == "" && text2 != "")
		message.innerHTML = "&nbsp; invalid delivery period! start of period in not defined";
		
	else if (text2 != "") {
		var time1 = text1.split(" ")[1];	
		var time2 = text2.split(" ")[1];	

		if (time2 <= time1 ) {
			obj2.value = "";
			message.innerHTML = "&nbsp; invalid delivery period! please fill in for end time some value > " + time1;
			message.style.color='brown';
			message.style.display='inline';
		}
	}
}


/* *******************
 * ** --- USERS --- **
 * ******************* 
 */
 
function CsaWpPluginProducerOrderInfoVia (selection, which) {
	if (selection.value == "consumer") which.style.display = "none";
	else which.style.display = "block";
}

/* ********************
 * ** --- ORDERS --- **
 * ******************** 
 */

function CsaWpPluginCalcNewOrderCost() {
	var $j = jQuery.noConflict();
	var serializedFormData = $j('#csa-wp-plugin-sumbitOrder_form').serializeArray();
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

function CsaWpPluginRequestSumbitOrderToServer(user_login,btn) {

	btn.disabled = true;

	var $j = jQuery.noConflict();
	var serializedFormData = $j('#csa-wp-plugin-sumbitOrder_form').serializeArray();
	serializedFormData = JSON.stringify(serializedFormData);
		
	var data = {
		'action': 'csa-wp-plugin-add_new_order',
		'user_login': user_login,
		'data': serializedFormData
	}
	
	$j.post(ajaxurl, data ,
		function(response){
			//console.log("Server returned: [" + response + "]");
			location.reload(true);
		});
}

function CsaWpPluginRequestDeleteProductOrder(product) {

	var $j = jQuery.noConflict();		
	var productTR = $j(product).closest("tr");

	var productOrderID = $j(productTR).attr("id").split('_')[1];
	
	//update database
	var data = {
		"action" : "csa-wp-plugin-delete_user_order_product",
		"productOrderID" : productOrderID
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

function CsaWpPluginRequestDeleteUserOrder(user, lastDeliveryDate) {
	var $j = jQuery.noConflict();		
	
	//update database
	var data = {
		"action" : "csa-wp-plugin-delete_user_order",
		"userLogin" : user,
		"lastDeliveryDate" : lastDeliveryDate
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");
			location.reload(true);
		}
	);

}

/* **************************
 * ** --- OLD - UNUSED --- **
 * **************************
 */

/*
function hideshow (which){
	if (!document.getElementById) return;
	if (which.style.display=="block") which.style.display="none";
	else which.style.display="block";
	
	var $j = jQuery.noConflict();
	$j(which).slideToggle("fast");
}
/*
// Prevent a form page to autorefresh after submission  -->
var $j = jQuery.noConflict();
$j(document).ready(function() {
  $j("#form_id").on('submit', function(e) {
	e.preventDefault(); // prevent default form submit
  });
});
*/
