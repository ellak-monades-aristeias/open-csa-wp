function open_csa_wp_slide_toggle (which){
	var $j = jQuery.noConflict();
	$j(which).slideToggle("slow");
}

function open_csa_wp_hover_icon(element, iconName, plugins_dir) {
	element.setAttribute('src', plugins_dir + '/open-csa-wp/icons/' + iconName +'_hover.png');
}

function open_csa_wp_unhover_icon(element, iconName, plugins_dir) {
	element.setAttribute('src', plugins_dir + '/open-csa-wp/icons/' + iconName +'.png');
}

function open_csa_wp_toggle_form(text1, text2, text3, textSize, text4) {
	var $j = jQuery.noConflict();
	
	if (textSize == null) {
		textSize=4;
	}
	if (text4 == null) {
		text4 = "";
	}
	
	if (document.getElementById("open-csa-wp-"+ text1 +"_div").style.display == "none") {
		document.getElementById("open-csa-wp-"+ text1 +"_formHeader_text").innerHTML = "<font size='"+ textSize +"'>" + text4 + text2 + " (hide" + text3 +") </font>";
	} else {
		document.getElementById("open-csa-wp-"+ text1 +"_formHeader_text").innerHTML = "<font size='"+ textSize +"'>" + text4 + text2 + " (show" + text3 +") </font>";
	}

	$j("#open-csa-wp-"+ text1 +"_div").slideToggle("slow");

}

function open_csa_wp_you_forgot_this_one (span) {
	span.innerHTML = "<i style='color:brown'>&nbsp;&nbsp;"+ general_translation.you_forgot_this_one + "...</i>"
	span.style.display = "inline";
}

function open_csa_wp_validate_delivery_time_period (text_to_fill) {
	var obj1 = document.getElementById("open-csa-wp-" + text_to_fill + "_delivery_start_time_input_id");
	var text1 = obj1.value;
	var obj2 = document.getElementById("open-csa-wp-" + text_to_fill + "_delivery_end_time_input_id");
	var text2 = obj2.value;
		
	var message = document.getElementById("open-csa-wp-" + text_to_fill + "_invalidDeliveryTime_span");
	
	if (text1 == "" && text2 != "") {
		message.innerHTML = "&nbsp; "+ general_translation.invalid_delivery_period_undefined;
	} else if (text2 != "") {
		var time1 = text1.split(" ")[1];	
		var time2 = text2.split(" ")[1];	

		if (time2 <= time1 ) {
			obj2.value = "";
			message.innerHTML = "&nbsp; " + general_translation.invalid_delivery_period_value + " > " + time1;
			message.style.color='brown';
			message.style.display='inline';
		}
	}
}


/* *******************
 * ** --- USERS --- **
 * ******************* 
 */
 
function open_csa_wp_producer_orders_info_via (selection, which) {
	if (selection.value == "consumer") {
		which.style.display = "none";
	} else {
		which.style.display = "block";
	}
}