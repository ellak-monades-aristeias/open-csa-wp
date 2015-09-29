
var $j = jQuery.noConflict();

$j(document).ready(function() {
	var productsListTable = $j("#csa-wp-plugin-showProductsList_table");
	if (productsListTable.length > 0) {
		var oTable = productsListTable.dataTable({
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
					"action" : "csa-wp-plugin-update_product",
					"value" : value,
					"productID": this.parentNode.getAttribute("id").split('_')[1],
					"column": oTable.fnGetPosition(this)[2]			//???? why [2] and not [1]? - [0] describes the row, [1] describes the column, [2] describes again the column?, [3..] undefined
				};
				$j.post(ajaxurl, dataPost, 
					function(response) { 
						console.log ("Server returned:["+response+"]");
						
						//var fetch = response.split(",");
						//var aPos = oTable.fnGetPosition(tmp);
						//oTable.fnUpdate(fetch[1], aPos[0], aPos[1]);
					}
				);
				return(value);
			}, 
			dataEditable
		);
		
		dataEditable['submit'] = "OK";
		dataEditable['type'] = "select";
		dataEditable['data'] = "{'yes':'yes', 'no':'no'}"
		
		//edit any value of any object (of class .editable)
		$j(".editable_boolean", oTable.fnGetNodes()).editable(
			function(value, settings) { 
				var field = this;
				var productID = field.parentNode.getAttribute("id").split('_')[1];
				var column = oTable.fnGetPosition(field)[2] //???? why [2] and not [1]? - [0] describes the row, [1] describes the column, [2] describes again the column?, [3..] undefined
				var oldValue = oTable.fnGetData(field);
				
				var dataPost = {
					"action" : "csa-wp-plugin-update_product",
					"value" : value,
					"productID": productID,
					"column": column
				};
				$j.post(ajaxurl, dataPost, 
					function(response) { 
						//console.log ("Server returned:["+response+"]");
						
						CsaWpPluginToggleProductVisibility (productID, productsListTable.attr("csa-wp-plugin-plugins_dir"));
					}
				);
			
				return(value);
			}, 
			dataEditable
		);
	}
	
	var productsListDiv = $j("#csa-wp-plugin-showProductsList_div .csa-wp-plugin-tip_products");
	if(productsListDiv.length > 0) {
		productsListDiv.cluetip({
			splitTitle: '|',						 
			showTitle: false,
			hoverClass: 'highlight',
			open: 'slideDown', 
			openSpeed: 'slow'
		});
	}

});


function CsaWpPluginNewProductFieldsValidation(btn, productID, urlAddress) {

	var form = btn.parentNode;
	
	if (!form.checkValidity()) btn.click();
	else {
		document.getElementById("csa-wp-plugin-newProductForm_category_input_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-newProductForm_producer_input_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-newProductForm_unit_input_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-newProductForm_availability_input_disabled_id").disabled = false;
		
		var $j = jQuery.noConflict();
		var serializedFormData = $j('#csa-wp-plugin-showNewProduct_form').serializeArray();
		var arraySpanElements = [
			"",
			"csa-wp-plugin-newProductForm_category_input_span_id",
			"csa-wp-plugin-newProductForm_producer_input_span_id",
			"",
			"",
			"csa-wp-plugin-newProductForm_unit_input_span_id",
			"",
			"csa-wp-plugin-newProductForm_availability_input_span_id"
		];
		
		validity = true;
		var i;
		for (i=0; i<8; i++) {
			if (i!= 6 && serializedFormData[i].value == '') {
				validity = false;
				break;
			}
		}
	
		if (validity == true) CsaWpPluginSendRequestAddOrUpdateProductToServer(btn, productID, urlAddress);
		else {
			CsaWpPluginYouForgotThisOne (document.getElementById(arraySpanElements[i]));
			event.preventDefault();
		}
	}
}

function CsaWpPluginSendRequestAddOrUpdateProductToServer(btn, productID, urlAddress) {

	btn.disabled = true;

	var $j = jQuery.noConflict();
	var serializedFormData = $j('#csa-wp-plugin-showNewProduct_form').serializeArray();
	serializedFormData = JSON.stringify(serializedFormData);
			
	var data = {
		'action': 'csa-wp-plugin-product_add_or_update_request',
		'productID': productID,
		'data'	: serializedFormData
	}
		
	$j.post(ajaxurl, data ,
		function(response){
			//console.log("Server returned: [" + response + "]");
			btn.disabled = false;
			if (productID == null) location.reload(true);
			else window.location.replace(urlAddress);

	});
}

function CsaWpPluginRequestDeleteProduct(product) {

	var $j = jQuery.noConflict();		
	var productTR = $j(product).closest("tr");

	var productID = $j(productTR).attr("id").split('_')[1];
	
	var data = {
		"action" : "csa-wp-plugin-delete_product",
		"productID" : productID
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");
			
			$j(productTR).fadeOut(200,function() {
					$j(productTR).remove();
					
					if ($j('#csa-wp-plugin-showProductsList_table .csa-wp-plugin-showProducts-product').length == 0) 
						location.reload(true);
			});
		}
	);
}

function CsaWpPluginRequestToggleProductVisibility(imageObj, pluginsDir) {
	var $j = jQuery.noConflict();
	var row = imageObj.parentNode.parentNode;
	var productID = row.id.split('_')[1];
	
	var availability = row.style.color=='gray'?1:0;
	
	//update database
	var data = {
		"action" : "csa-wp-plugin-update_product_availability",
		"productID" : productID,
		"availability" : availability
	};
	
	
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");
			
			CsaWpPluginToggleProductVisibility (productID, pluginsDir);
		}
	);
}

function CsaWpPluginToggleProductVisibility (productID, pluginsDir) {

	objTR = document.getElementById ("csa-wp-plugin-showProductsProductID_"+productID);
	imageObj = document.getElementById("csa-wp-plugin-showProductsAvailabilityIconID_"+productID);;
	textObj = document.getElementById("csa-wp-plugin-showProductsAvailabilityID_"+productID);
	
	//toggle row color, image source, text, and title
	if (objTR.style.color=='gray') {
		objTR.style.color='black';
		imageObj.src = pluginsDir + "/csa-wp-plugin/icons/visible.png";
		textObj.innerHTML = "yes";
		imageObj.title = "mark it as unavailable";
	} else {
		objTR.style.color='gray';
		imageObj.src = pluginsDir + "/csa-wp-plugin/icons/nonVisible.png";
		textObj.innerHTML = "no";
		imageObj.title = "mark it as available";
	}
}


function CsaWpPluginEditProduct(productObj, pageUrl) {
	var productTR = $j(productObj).closest("tr");

	var productID = $j(productTR).attr("id").split('_')[1];

	window.location.replace( pageUrl + "&id=" + productID);
}

function CsaWpPluginResetProductForm(){
	document.getElementById("csa-wp-plugin-showNewProduct_form").reset();
	
	document.getElementById("csa-wp-plugin-newProductForm_category_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-newProductForm_producer_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-newProductForm_unit_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-newProductForm_availability_input_id").style.color = "#999";
	
	document.getElementById("csa-wp-plugin-newProductForm_category_input_span_id").style.display = "none";
	document.getElementById("csa-wp-plugin-newProductForm_producer_input_span_id").style.display = "none";
	document.getElementById("csa-wp-plugin-newProductForm_unit_input_span_id").style.display = "none";
	document.getElementById("csa-wp-plugin-newProductForm_availability_input_span_id").style.display = "none";


	
	document.getElementById("csa-wp-plugin-showNewProduct_button_id").disabled = false;
}