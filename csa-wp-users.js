
// clueTip code for showing product details in tooltip 
var $j = jQuery.noConflict();
$j(document).ready(function() {
	var user_tips = $j(".csa-wp-plugin-tip_users");

	if(user_tips.length > 0) {
		user_tips.cluetip({
			splitTitle: '|',							 
			showTitle: false,
			hoverClass: 'highlight',
			open: 'slideDown', 
			openSpeed: 'fast'
		});
	}
});
