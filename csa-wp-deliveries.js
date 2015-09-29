var $j = jQuery.noConflict();	

$j(document).ready(function() {
	var deliveryClueTips = $j("#csa-wp-plugin-newDelivery_div .csa-wp-plugin-tip_deliveries");
	
	if(deliveryClueTips.length > 0) {
		deliveryClueTips.cluetip({
			splitTitle: '|',						 
			showTitle: false,
			hoverClass: 'highlight',
			open: 'slideDown', 
			openSpeed: 'slow'
		});
	}

	deliveryClueTips = $j("#csa-wp-plugin-showDeliveriesList_div .csa-wp-plugin-tip_deliveries");
	
	if(deliveryClueTips.length > 0) {
		deliveryClueTips.cluetip({
			splitTitle: '|',						 
			showTitle: false,
			hoverClass: 'highlight',
			open: 'slideDown', 
			openSpeed: 'slow'
		});
	}
});

function CsaWpPluginNewDeliveryFormatCustomValues (btn) {
	btn.disabled = true;

	var deadlineTimeObj = document.getElementById("csa-wp-plugin-newDelivery_order_deadline_time_input_id");
	deadlineTimeObj.value = deadlineTimeObj.value.split(" ")[2] + ":00";
	
	var deliveryStartTimeObj = document.getElementById("csa-wp-plugin-newDelivery_spotDetails_delivery_start_time_input_id");
	deliveryStartTimeObj.value = deliveryStartTimeObj.value.split(" ")[1] + ":00";
	
	var deliveryEndTimeObj = document.getElementById("csa-wp-plugin-newDelivery_spotDetails_delivery_end_time_input_id");
	deliveryEndTimeObj.value = deliveryEndTimeObj.value.split(" ")[1] + ":00";
	
	var $j = jQuery.noConflict();	
		
	$j(btn).closest("form").submit();
}

function CsaWpPluginRequestInitiateNewOrUpdateDelivery(btn, deliveryID, urlAddress) {

	var $j = jQuery.noConflict();

	document.getElementById("csa-wp-plugin-newDelivery_spotDetails_spotID_input_disabled_id").disabled = false;
	document.getElementById("csa-wp-plugin-newDelivery_delivery_deadline_date_disabled_id").disabled = false;
	document.getElementById("csa-wp-plugin-newDelivery_inCharge_input_disabled_id").disabled = false;
		
	var serializedFormData = $j('#csa-wp-plugin-initiateNewDelivery_form_id').serializeArray();
	
	if (serializedFormData[1].value == "") {
		CsaWpPluginYouForgotThisOne (document.getElementById("csa-wp-plugin-newDelivery_delivery_deadline_date_input_span_id"));
		event.preventDefault();
	}
	else {
		btn.disabled = true;

		var $j = jQuery.noConflict();
		serializedFormData = JSON.stringify(serializedFormData);
				
		var data = {
			'action': 'csa-wp-plugin-initiate_or_update_new_delivery_request',
			'deliveryID' : deliveryID,
			'data'	: serializedFormData
		}
			
		$j.post(ajaxurl, data ,
			function(response){
				//console.log("Server returned: [" + response + "]");
				btn.disabled = false;
				window.location.replace(urlAddress);
		});
	}
}

function CsaWpPluginRequestDeleteDelivery(delivery) {

	var $j = jQuery.noConflict();		
	var deliveryTR = $j(delivery).closest("tr");

	var deliveryID = $j(deliveryTR).attr("id").split('_')[1];
	
	var data = {
		"action" : "csa-wp-plugin-delete_delivery",
		"deliveryID" : deliveryID
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");
			
			$j(deliveryTR).fadeOut(200,function() {
					$j(deliveryTR).remove();
					
					if ($j('#csa-wp-plugin-showDeliveriesList_table .csa-wp-plugin-showDeliveries-delivery').length == 0) 
						location.reload(true);
			});
		}
	);
}

function CsaWpPluginEditDelivery(deliveryObj, pageUrl) {
	var deliveryTR = $j(deliveryObj).closest("tr");

	var deliveryID = $j(deliveryTR).attr("id").split('_')[1];

	window.location.replace( pageUrl + "&deliveryID=" + deliveryID);
}

function CsaWpPluginRequestToggleDeliveryAbilityToOrder(imageObj, pluginsDir) {
	var $j = jQuery.noConflict();
	var row = imageObj.parentNode.parentNode;
	var deliveryID = row.id.split('_')[1];
	
	var areOrdersOpen = imageObj.title.split(" ")[0]=='remove'?0:1;
	
	//update database
	var data = {
		"action" : "csa-wp-plugin-update_delivery_abilityToOrder",
		"deliveryID" : deliveryID,
		"areOrdersOpen" : areOrdersOpen
	};
	
	
	$j.post(ajaxurl, data, 
		function(response) { 
			//console.log ("Server returned:["+response+"]");
			
			CsaWpPluginToggleDeliveryAbilityToOrder (deliveryID, pluginsDir);
		}
	);
}

function CsaWpPluginToggleDeliveryAbilityToOrder (deliveryID, pluginsDir) {

	objTR = document.getElementById ("csa-wp-plugin-showDeliveriesDeliveryID_"+deliveryID);
	imageObj = document.getElementById("csa-wp-plugin-showDeliveriesOpenOrdersIconID_"+deliveryID);;
	textObj = document.getElementById("csa-wp-plugin-showDeliveriesOpenOrdersID_"+deliveryID);
	
	//toggle row color, image source, text, and title
	if (imageObj.title.split(" ")[0] == 'grant') {
		if (objTR.style.color != 'grey')
			objTR.style.color = 'green';
		imageObj.src = pluginsDir + "/csa-wp-plugin/icons/open.png";
		textObj.innerHTML = "yes";
		imageObj.title = "remove ability to order";
	} else {
		if (objTR.style.color != 'grey')
			objTR.style.color = 'brown';
		imageObj.src = pluginsDir + "/csa-wp-plugin/icons/close.png";
		textObj.innerHTML = "no";
		imageObj.title = "grant ability to order";
	}
}