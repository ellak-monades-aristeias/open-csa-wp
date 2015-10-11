var $j = jQuery.noConflict();

$j(document).ready(function() {

    var data_editable = {
        "width": "10em",
        "height": "3em",
        "type": "text",
        //"submit" : "<img src='" + "<?php echo plugins_url(); ?>" + "/csa-wp-plugin/icons/ok.png'>",
        //"cancel" : "<img src='" + "<?php echo plugins_url(); ?>" + "/csa-wp-plugin/icons/cancel.png'>",
        "tooltip": product_categories_translation.tooltip,
		"placeholder": product_categories_translation.placeholder,
        "onblur": "cancel",
        "loadtype": 'POST',
    };

    var category_list_table = $j("#csa-wp-plugin-showProductCategoriesList_table");
    if (category_list_table.length > 0) {
        var o_table = category_list_table.dataTable({
            "bPaginate": false,
            "bStateSave": true,
            "bInfo": false,
            "bFilter": true
        });

        //edit any value of any object (of class .editable)
        $j(".editable", o_table.fnGetNodes()).editable(
            function(value, settings) {
                var tmp = this;

                var data_post = {
                    "action": "csa-wp-plugin-update_category",
                    "value": value,
                    "product_category_id": this.parentNode.getAttribute("id"),
                    "column": o_table.fnGetPosition(this)[2] //???? why [2] and not [1]? - [0] describes the row, [1] describes the column, [2] describes again the column?, [3..] undefined
                };
                $j.post(ajaxurl, data_post,
                    function(response) {
                        //console.log ("Server returned:["+response+"]");

                        //var fetch = response.split(",");
                        //var aPos = o_table.fnGetPosition(tmp);
                        //o_table.fnUpdate(fetch[1], aPos[0], aPos[1]);
                    }
                );
                return (value);
            },
            data_editable
        );
    }
    var categories_tips = $j("#csa-wp-plugin-showProductCategoriesList_div .csa-wp-plugin-tip_categories");
    if (categories_tips.length > 0) {
        categories_tips.cluetip({
            splitTitle: '|',
            showTitle: false,
            hoverClass: 'highlight',
            open: 'slideDown',
            openSpeed: 'slow'
        });
    }
});


function csa_wp_plugin_new_product_categories_fields_validation(btn) {

    var form = btn.parentNode;

    if (!form.checkValidity()) {
		btn.click();
	} else {
		csa_wp_plugin_request_add_category(btn);
	}
}

function csa_wp_plugin_request_add_category(btn) {

    var $j = jQuery.noConflict();

    var serialized_form_data = $j('#csa-wp-plugin-addNewCategoryForm').serializeArray();
    serialized_form_data = JSON.stringify(serialized_form_data);

    var data = {
        'action': 'csa-wp-plugin-add_new_productCategory',
        'data': serialized_form_data,
    }

    // post data to the Server
    $j.post(ajaxurl, data,
        function(response) {
            //console.log("Server returned: [" + response + "]");
            location.reload(true);
        });
}

function csa_wp_plugin_request_delete_product_category(category) {

    var $j = jQuery.noConflict();
    var product_category_tr = $j(category).closest("tr");

    var product_category_id = $j(product_category_tr).attr("id").split('_')[1];

    var data = {
        "action": "csa-wp-plugin-delete_product_category",
        "product_category_id": product_category_id
    };

    $j.post(ajaxurl, data,
        function(response) {
            //console.log ("Server returned:["+response+"]");

			var return_values = response.split(",");
						
			if (return_values[0] == "skipped") {
				alert(product_categories_translation.product_category_cannnot_be_deleted);
			} else {
				$j(product_category_tr).fadeOut(200, function() {
					$j(product_category_tr).remove();

					if ($j('#csa-wp-plugin-showProductCategoriesList_table .csa-wp-plugin-showProductCategoriesCategoryID-category').length == 0) {
						location.reload(true);
					}
				});
			}
        }
    );
}