var $j = jQuery.noConflict();	

$j(document).ready(function() {
	var spot_list_table = $j("#csa-wp-plugin-showSpotsList_table");
		
	if (spot_list_table.length > 0) {	
		var o_table = spot_list_table.dataTable({
			"bPaginate": false, 
			"bStateSave": true, 
			"bInfo": false, 
			"bFilter": true
		});

		var data_editable = {
			"width" : "10em",
			"height": "3em",
			"type" : "text",
			"tooltip": spots_translation.tooltip_text_click_to_change,
			"placeholder": spots_translation.placeholder_click_to_fill,
			"onblur": "cancel",
			"loadtype": 'POST'
		};
		
		//edit any value of any object (of class .editable)
		$j(".editable", o_table.fnGetNodes()).editable(
			function(value, settings) { 
				var field = this;
				var spot_id = field.parentNode.getAttribute("id").split('_')[1];
				var column = o_table.fnGetPosition(field)[2] //???? why [2] and not [1]? - [0] describes the row, [1] describes the column, [2] describes again the column?, [3..] undefined
				var old_value = o_table.fnGetData(field);
				
				var data_post = {
					"action" : "csa-wp-plugin-update-spot",
					"value" : value,
					"spot_id": spot_id,
					"column": column
				};
				$j.post(ajaxurl, data_post, 
					function(response) { 
						//console.log ("Server returned:["+response+"]");						
						
						if (column==0) {
							csa_wp_plugin_request_spot_name_validity(value, null, 1,
								function() {
									alert (spots_translation.invalid_spot_name_hint)
									csa_wp_plugin_request_spot_update(spot_id,old_value,column);
									$j(field).html(old_value);
								}
							);
						}
					}
				);
			
				return(value);
			}, 
			data_editable
		);	
		
		// data_editable['submit'] = "OK";
		// data_editable['type'] = "select";
		// data_editable['data'] = "{'yes':'yes', 'no':'no'}"
		
		// //edit any value of any object (of class .editable)
		// $j(".editable_select", o_table.fnGetNodes()).editable(
			// function(value, settings) { 
				// var field = this;
				// var spot_id = field.parentNode.getAttribute("id").split('_')[1];
				// var column = o_table.fnGetPosition(field)[2] //???? why [2] and not [1]? - [0] describes the row, [1] describes the column, [2] describes again the column?, [3..] undefined
				// var old_value = o_table.fnGetData(field);
				
				// var data_post = {
					// "action" : "csa-wp-plugin-update-spot",
					// "value" : value,
					// "spot_id": spot_id,
					// "column": column
				// };
				// $j.post(ajaxurl, data_post, 
					// function(response) { 
						// //console.log ("Server returned:["+response+"]");						
					// }
				// );
			
				// return(value);
			// }, 
			// data_editable
		// );
	}
	
	var spot_tips = $j("#csa-wp-plugin-showSpotsList_div .csa-wp-plugin-tip_spots");
	if(spot_tips.length > 0) {
		spot_tips.cluetip({
			splitTitle: '|',							 
			showTitle: false,
			hoverClass: 'highlight',
			open: 'slideDown', 
			openSpeed: 'slow'
		});
	}
	
	spot_tips = $j("#csa-wp-plugin-showNewSpot_div .csa-wp-plugin-tip_spots");
	if(spot_tips.length > 0) {
		spot_tips.cluetip({
		splitTitle: '|',							 
		showTitle: false,
		hoverClass: 'highlight',
		open: 'slideDown', 
		openSpeed: 'fast'
		});
	}
	
});

function csa_wp_plugin_request_spot_update(spot_id, value, column) {
	var data_post = {
		"action" : "csa-wp-plugin-update-spot",
		"value" : value,
		"spot_id": spot_id,
		"column": column
	};
	$j.post(ajaxurl, data_post, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");						
		}
	);
}

function csa_wp_plugin_request_spot_name_validity(name, spot_id, num_entries_exist, invalid_function) {
	var $j = jQuery.noConflict();

	if (name!= "") {
		var btn = $j('#csa-wp-plugin-showNewSpot_button_id')[0];
		if (invalid_function == null) {
			btn.disabled = true;
		}
		
		var data = {
			'action': 'csa-wp-plugin-check_spot_name_validity',
			'spot_name': name,
			'spot_id' : spot_id,
			'num_entries_exist': num_entries_exist
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
				} else if (invalid_function != null) {
					invalid_function();
				} else {
					$j('#csa-wp-plugin-showNewSpot_name_span_id')[0].style.color = "brown";
					$j('#csa-wp-plugin-showNewSpot_name_span_id')[0].style.display = "inline";
					$j('#csa-wp-plugin-showNewSpot_name_span_id')[0].innerHTML = spots_translation.invalid_spot_name;
				}
		});
	} else $j('#csa-wp-plugin-showNewSpot_name_span_id')[0].style.display = "none";
}

function csa_wp_plugin_new_spot_fields_validation(btn, spot_id, url_address) {

	var form = btn.parentNode;
	
	if (!form.checkValidity()) {
		btn.click();
	} else {
		document.getElementById("csa-wp-plugin-delivery_spot_owner_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-spots_order_deadline_day_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-spots_delivery_day_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-spots_close_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-spots_parking_disabled_id").disabled = false;
		document.getElementById("csa-wp-plugin-spots_refrigerator_disabled_id").disabled = false;

		var $j = jQuery.noConflict();
		var serialized_form_data = $j('#csa-wp-plugin-showNewSpot_form').serializeArray();
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
			if (serialized_form_data[i+6].value == '') {
				validity = false;
				break;
			}
		}
		
		if (serialized_form_data[6].value == 'no') {
			validity = true;
		}
		
		if (validity == true) {
			csa_wp_plugin_request_add_or_update_spot(btn, spot_id, url_address);
		} else {
			csa_wp_plugin_you_forgot_this_one (document.getElementById(arraySpanElements[i]));
			event.preventDefault();
		}
	}
}

function csa_wp_plugin_request_add_or_update_spot(btn, spot_id, url_address) {

	btn.disabled = true;

	var $j = jQuery.noConflict();
	var serialized_form_data = $j('#csa-wp-plugin-showNewSpot_form').serializeArray();
	serialized_form_data = JSON.stringify(serialized_form_data);
			
	var data = {
		'action': 'csa-wp-plugin-spot_add_or_update_request',
		'spot_id': spot_id,
		'data'	: serialized_form_data
	}
		
	$j.post(ajaxurl, data ,
		function(response){
			//console.log("Server returned: [" + response + "]");
			btn.disabled = false;
			if (spot_id == null) { 
				location.reload(true);
			} else {
				window.location.replace(url_address);
			}
	});
}

function csa_wp_plugin_request_delete_spot(spot) {

	var $j = jQuery.noConflict();		
	var spot_tr = $j(spot).closest("tr");

	var spot_id = $j(spot_tr).attr("id").split('_')[1];
	
	var data = {
		"action" : "csa-wp-plugin-delete_spot",
		"spot_id" : spot_id
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");
			
			var return_values = response.split(",");
			
			if (return_values[0] == "skipped") {
				alert(spots_translation.spot_cannnot_be_deleted);
			} else {
				$j(spot_tr).fadeOut(200,function() {
						$j(spot_tr).remove();
						
						if ($j('#csa-wp-plugin-showSpotsList_table .csa-wp-plugin-showSpotsSpotID-spot').length == 0) {
							location.reload(true);
						}
				});
			}
		}
	);
}

function csa_wp_plugin_show_new_spot_is_delivery_selection(select_obj, spot_id) {
	select_obj.style.color = select_obj.options[select_obj.selectedIndex].style.color;
	
	var span = document.getElementById("csa-wp-plugin-showNewSpotForm_deliverySpot_span");
	if (select_obj.options[select_obj.selectedIndex].value == "yes") {
		select_obj.options[select_obj.selectedIndex].text = "it is a delivery spot";
		csa_wp_plugin_slide_toggle(document.getElementById("csa-wp-plugin-spots_deliverySpot_div"));
		if (spot_id != null) {
			span.innerHTML = "<i style='color:#999'>&nbsp;&nbsp; "+ spots_translation.can_update_info +"</i>";
		} else {
			span.innerHTML = "<i style='color:#999'>&nbsp;&nbsp;" + spots_translation.please_fill_info + " </i>";
		}
		span.style.display="inline";
	} else {
		select_obj.options[select_obj.selectedIndex].text = "it is not a delivery spot"
		var div = document.getElementById("csa-wp-plugin-spots_deliverySpot_div");
		if (div.style.display != "none") {
			csa_wp_plugin_slide_toggle(div);
		}
		if (spot_id != null) {
			span.innerHTML = "<i style='color:#999'>&nbsp;&nbsp; " + spots_translation.delivery_spot_details_maintained + "</i>";
			span.style.display="inline";
		} else {
			span.style.display="none";
		}
	}
}

function csa_wp_plugin_reset_spot_form(){
	document.getElementById("csa-wp-plugin-showNewSpot_form").reset();
	document.getElementById("csa-wp-plugin-spots_is_delivery_spot_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-spots_order_deadline_day_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-spots_delivery_day_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-spots_close_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-spots_parking_input_id").style.color = "#999";
	document.getElementById("csa-wp-plugin-spots_refrigerator_input_id").style.color = "#999";	
	
	if (document.getElementById("csa-wp-plugin-spots_deliverySpot_div").style.display != "none") {
		csa_wp_plugin_slide_toggle(document.getElementById("csa-wp-plugin-spots_deliverySpot_div"));	
	}
		
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

function csa_wp_plugin_edit_spot(spotObj, page_url) {
	var spot_tr = $j(spotObj).closest("tr");

	var spot_id = $j(spot_tr).attr("id").split('_')[1];

	window.location.replace( page_url + "&id=" + spot_id);
}
