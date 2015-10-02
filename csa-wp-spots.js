var $j = jQuery.noConflict();	

$j(document).ready(function() {
	var spotListTable = $j("#csa-wp-plugin-showSpotsList_table");
		
	if (spotListTable.length > 0) {	
		var oTable = spotListTable.dataTable({
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
			"placeholder": "click to fill ...",
			"onblur": "cancel",
			"loadtype": 'POST',
		};
		
		//edit any value of any object (of class .editable)
		$j(".editable", oTable.fnGetNodes()).editable(
			function(value, settings) { 
				var field = this;
				var spotID = field.parentNode.getAttribute("id").split('_')[1];
				var column = oTable.fnGetPosition(field)[2] //???? why [2] and not [1]? - [0] describes the row, [1] describes the column, [2] describes again the column?, [3..] undefined
				var oldValue = oTable.fnGetData(field);
				
				var dataPost = {
					"action" : "csa-wp-plugin-update_spot",
					"value" : value,
					"spotID": spotID,
					"column": column
				};
				$j.post(ajaxurl, dataPost, 
					function(response) { 
						//console.log ("Server returned:["+response+"]");						
						
						if (column==0) {
							CsaWpPluginRequestspot_nameValidity(value, null, 1,
								function() {
									alert ("invalid! spot name already exists. please choose a unique one...")
									CsaWpPluginRequestSpotUpdate(spotID,oldValue,column);
									$j(field).html(oldValue);
								}
							);
						}
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
		$j(".editable_select", oTable.fnGetNodes()).editable(
			function(value, settings) { 
				var field = this;
				var spotID = field.parentNode.getAttribute("id").split('_')[1];
				var column = oTable.fnGetPosition(field)[2] //???? why [2] and not [1]? - [0] describes the row, [1] describes the column, [2] describes again the column?, [3..] undefined
				var oldValue = oTable.fnGetData(field);
				
				var dataPost = {
					"action" : "csa-wp-plugin-update_spot",
					"value" : value,
					"spotID": spotID,
					"column": column
				};
				$j.post(ajaxurl, dataPost, 
					function(response) { 
						//console.log ("Server returned:["+response+"]");						
					}
				);
			
				return(value);
			}, 
			dataEditable
		);
	}
	
	var spotsListDiv = $j("#csa-wp-plugin-showSpotsList_div .csa-wp-plugin-tip_spots");
	if(spotsListDiv) {
		spotsListDiv.cluetip({
			splitTitle: '|',							 
			showTitle: false,
			hoverClass: 'highlight',
			open: 'slideDown', 
			openSpeed: 'slow'
		});
	}
	
	var showNewSpotDiv = $j("#csa-wp-plugin-showNewSpot_div .csa-wp-plugin-tip_spots");
	if(showNewSpotDiv) {
		showNewSpotDiv.cluetip({
		splitTitle: '|',							 
		showTitle: false,
		hoverClass: 'highlight',
		open: 'slideDown', 
		openSpeed: 'fast'
		});
	}
	
});

function CsaWpPluginRequestSpotUpdate(spotID, value, column) {
	var dataPost = {
		"action" : "csa-wp-plugin-update_spot",
		"value" : value,
		"spotID": spotID,
		"column": column
	};
	$j.post(ajaxurl, dataPost, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");						
		}
	);
}

function CsaWpPluginRequestspot_nameValidity(name, spotID, numEntriesExist, invalidFunction) {
	var $j = jQuery.noConflict();

	if (name!= "") {
		var btn = $j('#csa-wp-plugin-showNewSpot_button_id')[0];
		if (invalidFunction == null) btn.disabled = true;
		
		var data = {
			'action': 'csa-wp-plugin-check_spot_name_validity',
			'spot_name': name,
			'spotID' : spotID,
			'numEntriesExist': numEntriesExist
		}
		
		$j.post(ajaxurl, data ,
			function(response){
				//console.log("Server checked [" + name + "] for validity and returned: [" + response + "]");
				var answer = response.split(" ")[0];
				if (answer == "valid") {
					btn.disabled = false;
					if (response.split(" ")[1] != "updating") {
						$j('#csa-wp-plugin-showNewSpot_name_span_id')[0].style.color = "green";
						$j('#csa-wp-plugin-showNewSpot_name_span_id')[0].style.display = "inline";
						$j('#csa-wp-plugin-showNewSpot_name_span_id')[0].innerHTML = "valid!";
					}
					else $j('#csa-wp-plugin-showNewSpot_name_span_id')[0].style.display = "none";
				}			
				else if (invalidFunction != null) invalidFunction();
				else {
					$j('#csa-wp-plugin-showNewSpot_name_span_id')[0].style.color = "brown";
					$j('#csa-wp-plugin-showNewSpot_name_span_id')[0].style.display = "inline";
					$j('#csa-wp-plugin-showNewSpot_name_span_id')[0].innerHTML = "invalid! name already exists";
				}
		});
	} else $j('#csa-wp-plugin-showNewSpot_name_span_id')[0].style.display = "none";
}

function CsaWpPluginNewSpotFieldsValidation(btn, spotID, urlAddress) {

	var form = btn.parentNode;
	
	if (!form.checkValidity()) btn.click();
	else {
		document.getElementById("csa-wp-plugin-delivery_spot_owner_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-spots_order_deadline_day_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-spots_delivery_day_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-spots_close_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-spots_parking_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-spots_refrigerator_disabled_id").disabled = false;

		var $j = jQuery.noConflict();
		var serializedFormData = $j('#csa-wp-plugin-showNewSpot_form').serializeArray();
		var arraySpanElements = [
			"csa-wp-plugin-showNewSpotForm_deliverySpot_span",
			"csa-wp-plugin-delivery_spot_owner_input_id",
			"csa-wp-plugin-showNewSpotForm_orderDeadline_span",
			"csa-wp-plugin-showNewSpotForm_orderDeadline_span",
			"csa-wp-plugin-showNewSpotForm_invalidDeliveryTime_span",
			"csa-wp-plugin-showNewSpotForm_invalidDeliveryTime_span",
			"csa-wp-plugin-showNewSpotForm_invalidDeliveryTime_span",
			"csa-wp-plugin-showNewSpotForm_ordersClose_span_id"			
		];
				
		validity = true;
		var i;
		for (i=0; i<8; i++) {
			if (serializedFormData[i+6].value == '') {
				validity = false;
				break;
			}
		}
		
		if (serializedFormData[6].value == 'no') validity = true;
		
		if (validity == true) CsaWpPluginRequestRequestAddOrUpdateSpot(btn, spotID, urlAddress);
		else {
			CsaWpPluginYouForgotThisOne (document.getElementById(arraySpanElements[i]));
			event.preventDefault();
		}
	}
}

function CsaWpPluginRequestRequestAddOrUpdateSpot(btn, spotID, urlAddress) {

	btn.disabled = true;

	var $j = jQuery.noConflict();
	var serializedFormData = $j('#csa-wp-plugin-showNewSpot_form').serializeArray();
	serializedFormData = JSON.stringify(serializedFormData);
			
	var data = {
		'action': 'csa-wp-plugin-spot_add_or_update_request',
		'spotID': spotID,
		'data'	: serializedFormData
	}
		
	$j.post(ajaxurl, data ,
		function(response){
			//console.log("Server returned: [" + response + "]");
			btn.disabled = false;
			if (spotID == null) location.reload(true);
			else window.location.replace(urlAddress);
	});
}

function CsaWpPluginRequestDeleteSpot(spot) {

	var $j = jQuery.noConflict();		
	var spotTR = $j(spot).closest("tr");

	var spotID = $j(spotTR).attr("id").split('_')[1];
	
	var data = {
		"action" : "csa-wp-plugin-delete_spot",
		"spotID" : spotID
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");
			
			$j(spotTR).fadeOut(200,function() {
					$j(spotTR).remove();
					
					if ($j('#csa-wp-plugin-showSpotsList_table .csa-wp-plugin-showSpotsSpotID-spot').length == 0) 
						location.reload(true);
			});
		}
	);
}

function CsaWpPluginShowNewSpotIsDeliverySelection(selectObj, spotID) {
	selectObj.style.color = selectObj.options[selectObj.selectedIndex].style.color;
	
	var span = document.getElementById("csa-wp-plugin-showNewSpotForm_deliverySpot_span");
	if (selectObj.options[selectObj.selectedIndex].value == "yes") {
		selectObj.options[selectObj.selectedIndex].text = "it is a delivery spot";
		CsaWpPluginSlideToggle(document.getElementById("csa-wp-plugin-spots_deliverySpot_div"));
		if (spotID != null) 
			span.innerHTML = "<i style='color:#999'>&nbsp;&nbsp; you can update the following info...</i>";
		else span.innerHTML = "<i style='color:#999'>&nbsp;&nbsp; please fill in the following info...</i>";
		span.style.display="inline";
	} else {
		selectObj.options[selectObj.selectedIndex].text = "it is not a delivery spot"
		var div = document.getElementById("csa-wp-plugin-spots_deliverySpot_div");
		if (div.style.display != "none") CsaWpPluginSlideToggle(div);
		if (spotID != null) {
			span.innerHTML = "<i style='color:#999'>&nbsp;&nbsp; the details of the former delivery spot will be maintained for later reference...</i>";
			span.style.display="inline";
		}
		else span.style.display="none";
	}
}

function CsaWpPluginResetSpotForm(){
	document.getElementById("csa-wp-plugin-showNewSpot_form").reset();
	document.getElementById("csa-wp-plugin-spots_is_delivery_spot_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-spots_order_deadline_day_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-spots_delivery_day_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-spots_close_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-spots_parking_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-spots_refrigerator_input_id").style.color = "#999";	
	
	if (document.getElementById("csa-wp-plugin-spots_deliverySpot_div").style.display != "none")
		CsaWpPluginSlideToggle(document.getElementById("csa-wp-plugin-spots_deliverySpot_div"));	
		
	document.getElementById("csa-wp-plugin-showNewSpot_name_span_id").style.display = "none";
	document.getElementById("csa-wp-plugin-showNewSpotForm_deliverySpot_span").style.display = "none";
	document.getElementById("csa-wp-plugin-showNewSpotForm_orderDeadline_span").style.display = "none";
	document.getElementById("csa-wp-plugin-showNewSpotForm_invalidDeliveryTime_span").style.display = "none";
	document.getElementById("csa-wp-plugin-showNewSpotForm_ordersClose_span_id").style.display = "none";
	document.getElementById("csa-wp-plugin-showNewSpotForm_parkingSpace_span_id").style.display = "none";
	document.getElementById("csa-wp-plugin-showNewSpotForm_hasRefrigerator_span_id").style.display = "none";

	document.getElementById("csa-wp-plugin-spots_close_automatic").style.display = "none";
	document.getElementById("csa-wp-plugin-spots_close_manual").style.display = "none";
	
	document.getElementById("csa-wp-plugin-showNewSpot_button_id").disabled = false;
}

function CsaWpPluginEditSpot(spotObj, pageUrl) {
	var spotTR = $j(spotObj).closest("tr");

	var spotID = $j(spotTR).attr("id").split('_')[1];

	window.location.replace( pageUrl + "&id=" + spotID);
}
