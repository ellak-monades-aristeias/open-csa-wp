function csa_wp_plugin_slide_toggle (which){
	var $j = jQuery.noConflict();
	$j(which).slideToggle("slow");
}

function csa_wp_plugin_hover_icon(element, iconName, plugins_dir) {
	element.setAttribute('src', plugins_dir + '/csa-wp-plugin/icons/' + iconName +'_hover.png');
}

function csa_wp_plugin_unhover_icon(element, iconName, plugins_dir) {
	element.setAttribute('src', plugins_dir + '/csa-wp-plugin/icons/' + iconName +'.png');
}

function csa_wp_plugin_toggle_form(text1, text2, text3, textSize, text4) {
	var $j = jQuery.noConflict();
	
	if (textSize == null) {
		textSize=4;
	}
	if (text4 == null) {
		text4 = "";
	}
	
	if (document.getElementById("csa-wp-plugin-"+ text1 +"_div").style.display == "none") {
		document.getElementById("csa-wp-plugin-"+ text1 +"_formHeader_text").innerHTML = "<font size='"+ textSize +"'>" + text4 + text2 + " (hide" + text3 +") </font>";
	} else {
		document.getElementById("csa-wp-plugin-"+ text1 +"_formHeader_text").innerHTML = "<font size='"+ textSize +"'>" + text4 + text2 + " (show" + text3 +") </font>";
	}

	$j("#csa-wp-plugin-"+ text1 +"_div").slideToggle("slow");

}

function csa_wp_plugin_you_forgot_this_one (span) {
	span.innerHTML = "<i style='color:brown'>&nbsp;&nbsp; you forgot this one...</i>"
	span.style.display = "inline";
}

function csa_wp_plugin_validate_delivery_time_period (text_to_fill) {
	var obj1 = document.getElementById("csa-wp-plugin-" + text_to_fill + "_delivery_start_time_input_id");
	var text1 = obj1.value;
	var obj2 = document.getElementById("csa-wp-plugin-" + text_to_fill + "_delivery_end_time_input_id");
	var text2 = obj2.value;
		
	var message = document.getElementById("csa-wp-plugin-" + text_to_fill + "_invalidDeliveryTime_span");
	
	if (text1 == "" && text2 != "") {
		message.innerHTML = "&nbsp; invalid delivery period! start of period in not defined";
	} else if (text2 != "") {
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
 
function csa_wp_plugin_producer_orders_info_via (selection, which) {
	if (selection.value == "consumer") {
		which.style.display = "none";
	} else {
		which.style.display = "block";
	}
}