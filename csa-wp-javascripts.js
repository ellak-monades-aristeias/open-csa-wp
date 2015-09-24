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

function CsaWpPluginToggleForm(text1, text2, text3) {
	var $j = jQuery.noConflict();

	if (document.getElementById("csa-wp-plugin-"+ text1 +"_div").style.display == "none")
		document.getElementById("csa-wp-plugin-"+ text1 +"_formHeader_text").innerHTML = "<font size='4'> " + text2 + " (hide" + text3 +") </font>";
	else document.getElementById("csa-wp-plugin-"+ text1 +"_formHeader_text").innerHTML = "<font size='4'> " + text2 + " (show" + text3 +") </font>";

	$j("#csa-wp-plugin-"+ text1 +"_div").slideToggle("slow");

}

function CsaWpPluginYouForgotThisOne (span) {
	span.innerHTML = "<i style='color:red'>&nbsp;&nbsp; you forgot this one...</i>"
	span.style.display = "inline";
}


/* *******************
 * ** --- USERS --- **
 * ******************* 
 */
 
function CsaWpPluginProducerOrderInfoVia (selection, which) {
	if (selection.value == "consumer") which.style.display = "none";
	else which.style.display = "block";
}


/* **********************
 * ** --- PRODUCTS --- **
 * ********************** 
 */
 
function submitProductsForm(f) {
  if(f.checkValidity()) {
    CsaWpPluginSendRequestAddProductToServer();
  }
}
 
function slow_hideshow_addNewProductForm() {
	var $j = jQuery.noConflict();
	if (document.getElementById("csa-wp-plugin-addProduct_ack").style.display != "none")
		document.getElementById("csa-wp-plugin-addProduct_ack").style.display = "none";
	
	if (document.getElementById("csa-wp-plugin-addProduct_div").style.display == "none")
		document.getElementById("csa-wp-plugin-addProduct_formHeader_text").innerHTML = "<font size='4'> Add New Product (hide form) </font>";
	else document.getElementById("csa-wp-plugin-addProduct_formHeader_text").innerHTML = "<font size='4'> Add New Product (show form) </font>";

	$j("#csa-wp-plugin-addProduct_div").slideToggle("slow");
}

function slow_hideshow_showProductsList() {
	var $j = jQuery.noConflict();

	if (document.getElementById("csa-wp-plugin-showProductsList_div").style.display == "none")
		document.getElementById("csa-wp-plugin-showProductsList_header_text").innerHTML = "<font size='4'> Products List (hide) </font>";
	else document.getElementById("csa-wp-plugin-showProductsList_header_text").innerHTML = "<font size='4'> Products List (show) </font>";

	$j("#csa-wp-plugin-showProductsList_div").slideToggle("slow");

}

// Send an "Insert New Product Request" to server, wait for response, and show result.
function CsaWpPluginSendRequestAddProductToServer()  {
   //if (!f.parentNode.ckeckValidity()) f.click();
 
    var $j = jQuery.noConflict();
 
 
    var serializedFormData = $j('#csa_wp_plugin_sumbitProduct_form').serializeArray();
    serializedFormData = JSON.stringify(serializedFormData);
 
    // get the values of the input elements 
    var name_field = document.getElementById("name_field").value;
    var variety_field = document.getElementById("variety_field").value;
    var category_field = document.getElementById("category_field").value;
    var price_field = document.getElementById("price_field").value;
    price_field = price_field.replace(',' , '.'); //replace comma entries with dot
 
 
    var producer_field = document.getElementById("producer_field").value;
    var unit_field = document.getElementById("unit_field").value;
    var selection = document.getElementById("available_field");
    var available_field = selection.options[selection.selectedIndex].value;
    selection = document.getElementById("exchangeable_field");
    var exchangeable_field = selection.options[selection.selectedIndex].value;
    selection = document.getElementById("frail_field");
    var frail_field = selection.options[selection.selectedIndex].value;
    var description_field = document.getElementById("description_field").value;
 
    // store them to data
 
    var data = {
        'action': 'csa-wp-plugin_add_new_product',
        'name': name_field,
        'variety': variety_field,
        'category': category_field,
        'price': price_field,
        'unit': unit_field,
        'producer': producer_field,
        'description': description_field,
        'available': available_field,
        'exchangeable' : exchangeable_field,
        'frail' : frail_field
      }
    
    // post data to the Server
 
    $j.post(ajaxurl, data ,
        function(data,status){
            $j("#csa_wp_addProduct_div").hide("slow");
            
            //clear html form
            document.getElementById("name_field").value = "";
            document.getElementById("variety_field").value = "";
            document.getElementById("category_field").value = "";
            document.getElementById("price_field").value = "";
 
            document.getElementById("unit_field").options[0].selected = true;
            document.getElementById("producer_field").value = "";
            document.getElementById("description_field").value = "";
            document.getElementById("available_field").options[0].selected = true;        
 
            document.getElementById("exchangeable_field").options[0].selected = true;     
            document.getElementById("frail_field").options[0].selected = true;        
 
            
            document.getElementById("csa_wp_addProduct_ack").style.display = "block";
            document.getElementById("csa_wp_addProduct_formHeader_text").innerHTML = "<font size='4'>New Product Form (show)</font>";
        });
}

function CsaWpPluginToggleProductVisibility(productID, pluginsDir) {
	var image = document.getElementById("eye"+productID);
	var row = document.getElementById(productID);
	var availability;
	
	//toggle row color and image source
	if (row.style.color=='gray') {
		row.style.color='black';
		image.src = pluginsDir + "/csa-wp-plugin/icons/visible.png";
		availability = "true";
	} else {
		row.style.color='gray';
		image.src = pluginsDir + "/csa-wp-plugin/icons/nonVisible.png";
		availability = "false";
	}

	//update database
	var data = {
		"action" : "csa-wp-plugin-update_product_availability",
		"productID" : productID,
		"availability" : availability
	};
	
	var $j = jQuery.noConflict();		
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");
		}
	);
}

/* **********************
 * ** --- CATEGORIES --- **
 * ********************** 
 */
 
 
function submitCategoryForm(f) {
  if(f.checkValidity()) {
    CsaWpPluginSendRequestAddProductsCategoryToServer();
  }
}
 
 
function slow_hideshow_addNewProductsCategoryForm() {
 
 
    var $j = jQuery.noConflict();
    if (document.getElementById("csa_wp_addCategory_ack").style.display != "none")
        document.getElementById("csa_wp_addCategory_ack").style.display = "none";
    
    if (document.getElementById("csa_wp_addCategory_div").style.display == "none")
        document.getElementById("csa_wp_addCategory_formHeader_text").innerHTML = "<font size='4'> Add New Category of Prodcuts (hide form) </font>";
    else document.getElementById("csa_wp_addCategory_formHeader_text").innerHTML = "<font size='4'>  Add New Category of Prodcuts (show form) </font>";
 
    $j("#csa_wp_addCategory_div").slideToggle("slow");
}
 
function slow_hideshow_showProductsCategoriesList() {
    var $j = jQuery.noConflict();
 
    if (document.getElementById("csa_wp_showProductsCategoryList_div").style.display == "none")
        document.getElementById("csa_wp_showProductsCategoryList_header_text").innerHTML = "<font size='4'> Products Categories List (hide) </font>";
    else document.getElementById("csa_wp_showProductsCategoryList_header_text").innerHTML = "<font size='4'> Products Categories List (show) </font>";
 
    $j("#csa_wp_showProductsCategoryList_div").slideToggle("slow");
 
}
 
/*  *****************
Send an "Insert New Product Request" to Server,
 wait for response, 
and show result.
***************************** */
function CsaWpPluginSendRequestAddProductsCategoryToServer()  {
 
 
    var $j = jQuery.noConflict();
 
 
    var serializedFormData = $j('#csa-wp-plugin-addNewCategoryForm').serializeArray();
    serializedFormData = JSON.stringify(serializedFormData);
 
    // get the values of the input elements 
    var name_field = document.getElementById("name_field2").value;
    var description_field = document.getElementById("description_field2").value;
 
    // store them to data
    var data = {
        'action': 'csa_wp_plugin_add_new_productsCategory',
        'name': name_field,
        'description': description_field
      }
 
    
 
    // post data to the Server
    $j.post(ajaxurl, data ,
        function(data,status){
            $j("#csa_wp_addCategory_div").hide("slow");
            
            //clear html form
            document.getElementById("name_field2").value = "";
            document.getElementById("description_field2").value = "";
 
            
            document.getElementById("csa_wp_addCategory_ack").style.display = "block";
            document.getElementById("csa_wp_addCategory_formHeader_text").innerHTML = "<font size='4'>New Products Category Form (show)</font>";
        });
}
 
function CsaWpPluginToggleProductsCategoryVisibility(categoryID, pluginsDir) {
 
    var row = document.getElementById(productID);
 
    
 
    //update database
    var data = {
        "action" : "csa_wp_plugin_update_product_availability",
        "productID" : categoryID,
        "availability" : availability
    };
    
    var $j = jQuery.noConflict();       
    $j.post(ajaxurl, data, 
        function(response) { 
            //console.log ("Server returned:["+response+"]");
        }
    );
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
