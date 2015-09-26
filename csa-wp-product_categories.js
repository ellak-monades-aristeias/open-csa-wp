var $j = jQuery.noConflict();	

$j(document).ready(function() {

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

	var categoryListTable = $j("#csa-wp-plugin-showProductCategoriesList_table");	
	if (categoryListTable.length > 0) {
		var oTable = categoryListTable.dataTable({
			"bPaginate": false, 
			"bStateSave": true, 
			"bInfo": false, 
			"bFilter": true
		});
		
		//edit any value of any object (of class .editable)
		$j(".editable", oTable.fnGetNodes()).editable(
			function(value, settings) { 
				var tmp = this;
			
				var dataPost = {
					"action" : "csa-wp-plugin-update_category",
					"value" : value,
					"productCategoryID": this.parentNode.getAttribute("id"),
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
	}
	var productCategoriesListDiv = $j("#csa-wp-plugin-showProductCategoriesList_div .csa-wp-plugin-tip_categories");
	if(productCategoriesListDiv.length > 0) {
		productCategoriesListDiv.cluetip({
			splitTitle: '|',						 
			showTitle: false,
			hoverClass: 'highlight',
			open: 'slideDown', 
			openSpeed: 'slow'
		});
	}
});



function submitCategoryForm(f) {
  if(f.checkValidity()) {
    CsaWpPluginSendRequestAddProductsCategoryToServer();
  }
}

function CsaWpPluginNewProducCategoriesFieldsValidation(btn)  {

	var form = btn.parentNode;
	
	if (!form.checkValidity()) btn.click();
	else CsaWpPluginRequestAddCategory(btn);
}

function CsaWpPluginRequestAddCategory(btn)  {

	var $j = jQuery.noConflict();
  
	var serializedFormData = $j('#csa-wp-plugin-addNewCategoryForm').serializeArray();
	serializedFormData = JSON.stringify(serializedFormData);
 
	var data = {
		'action': 'csa-wp-plugin-add_new_productCategory',
		'data': serializedFormData,
	}

	// post data to the Server
	$j.post(ajaxurl, data ,
		function(response){
			//console.log("Server returned: [" + response + "]");
			location.reload(true);
		});
}

 
/*function CsaWpPluginToggleProductsCategoryVisibility(categoryID, pluginsDir) {
 
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
}*/

function CsaWpPluginRequestDeleteProductCategory(category) {

	var $j = jQuery.noConflict();		
	var productCategoryTR = $j(category).closest("tr");

	var productCategoryID = $j(productCategoryTR).attr("id").split('_')[1];
	
	var data = {
		"action" : "csa-wp-plugin-delete_product_category",
		"productCategoryID" : productCategoryID
	};
	
	$j.post(ajaxurl, data, 
		function(response) { 
			console.log ("Server returned:["+response+"]");
			
			$j(productCategoryTR).fadeOut(200,function() {
					$j(productCategoryTR).remove();
					
					if ($j('#csa-wp-plugin-showProductCategoriesList_table .csa-wp-plugin-showProductCategoriesCategoryID-category').length == 0) 
						location.reload(true);
			});
		}
	);
}