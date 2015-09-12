<?php

function CsaWpPluginUserOrder($user = NULL) { 
	global $wpdb;
	// for security reasons
	if (!$user) $user = wp_get_current_user();
	if (!($user instanceof WP_User) )
		return;
		
	// ============== THIS SHOULD BE UPDATED ==================
	//$current_date = new DateTime(date('Y-m-d'));
	//$delivery_day = $wpdb->get_var("SELECT pref_value FROM $prefs WHERE pref_name='delivery_day'" ); //eg. "Monday"
	//$nextDelivery_date = new DateTime( date('Y-m-d', strtotime("Next ".$delivery_day)) );	
	//$last_delivery_date = $wpdb->get_var("SELECT pref_value FROM $prefs WHERE pref_name='last_delivery_date'");
	//$last_delivery_date = $nextDelivery_date; 
	//$check = $wpdb->get_results("SELECT type FROM $ords WHERE user_login='".$current_user->user_login."' AND date BETWEEN '".$last_delivery_date."' AND CURDATE()", ARRAY_N);
	//if($wpdb->num_rows > 0)
	//	$userHasOrder = true;
	//else
	//	$userHasorder = false;
	$userHasOrder = false;		
	
	//...if the current user has placed an order before the delivery date, redirect to "my order" page
//	if ( $current_date < $nextDelivery_date && has_order($current_user) ) {
	if ( $userHasOrder == true ) {
		//showEditableOrder();
		//verifyOrder(); //available separately below as shortcode - function
	} else {
		CsaWpPluginShowNewOrderForm($user);
	}
}
add_shortcode('personalOrder', 'CsaWpPluginUserOrder');

function CsaWpPluginShowNewOrderForm($user) {
	wp_enqueue_script('CsaWpPluginScripts');
	wp_enqueue_script('jquery.cluetip');
	wp_enqueue_style('jquery.cluetip.style');
?>

<script type="text/javascript">

// clueTip code for showing product details in tooltip 
var $j = jQuery.noConflict();
$j(document).ready(function() {
  $j('.tip').cluetip({
	splitTitle: '|',							 
	showTitle: false,
	hoverClass: 'highlight',
	open: 'slideDown', 
	openSpeed: 'slow'
  });
});

</script>

	<span class='tip' title=' Επιλέξτε την ποσότητα από κάθε είδος που επιθυμείτε και πιέστε "Καταχώρηση" στο κάτω μέρος της σελίδας.
							| Ύστερα από την καταχώρηση της παραγγελίας, μπορείτε να την επεξεργαστείτε έως τη μέρα και ώρα που έχει συμφωνηθεί η οριστικοποίησή της.
							| Δίπλα από κάθε προϊόν, στο πεδίο "info" εμπεριέχονται πληροφορίες για το κάθε προϊόν.'>Πληροφορίες</span> 
	<br/>

	<br/>
	<div id="csa_wp_submitOrder_formHeader"><span style="cursor:pointer" id="csa_wp_submitOrder_formHeader_text" onclick="slow_hideshow_submitOrderForm()"><font size='4'>Submit New Order Form (show)</font></span></div>
	<div id="csa_wp_submitOrder_div" style="display:none">
	
	<?php 
	//list categories as headers
	global $wpdb;
	$categories = $wpdb->get_results("SELECT DISTINCT category FROM ".csaProducts);
	foreach ($categories as $cat)
			echo "(<a href='#".$cat->category."'>".$cat->category."</a>)  " ;
	?>
	<br/>
	
	<!-- <link rel="stylesheet" href="jquery.cluetip.style" type="text/css" /> -->
	<FORM name='csa_wp_plugin_sumbitOrder_form' id='csa_wp_plugin_sumbitOrder_form' accept-charset='utf-8' method='post'> 
		<table id='ordersArray' class='table-bordered'> 
			<thead class="tableHeader"><tr><th>είδος</th><th>ποικιλία</th><th>τιμή</th><th>μονάδα</th><th>ποσότητα</th><th>παραγωγός</th><th> </th> <th class='th-hidden'></th></tr></thead>
			<?php
			foreach ($categories as $cat) {
				//echo "<tr><td><strong>".$cat->category."</strong></td></tr>";
				echo "<tr><td><strong> <a name='".$cat->category."' class='anchored'></a> <span class='emphasis-box' style='margin-left:-5px;'>".$cat->category."</span></strong></td></tr>";
				$products = $wpdb->get_results( $wpdb->prepare("SELECT id,type,variety,price,unit,producer,details,available FROM ".csaProducts." WHERE category='%s' ORDER BY type", $cat->category ));
				foreach($products as $row) {
					if($row->available == 'true') {
						echo "
						<tr class='csa-wp-plugin-product'>
						<td><span>".$row->type."</span></td>
						<td><span>".$row->variety."</span></td>
						<td><input type='number' name='csa_wp_plugin_order_productPrice' value='".$row->price."'/></td>
						<td>€/".$row->unit."</td>
						<td><input type='number' min='0' step='0.5' name='csa_wp_plugin_order_productQuantity' onchange='calcNewOrderCost()' onkeyup='calcNewOrderCost()' style='width:70px;background-color:LightGoldenRodYellow'></td>
						<td>".$row->producer."</td>";
						if ($row->details != '')
							echo "<td style='text-align:center'><span class='tip' title='|".$row->details."'>info</span></td>";
						echo "<td class='td-hidden'><input type='number' name='csa_wp_plugin_order_productID' value='".$row->id."' style='visibility:hidden'/></td>
						</tr>";
					}
				}
			}
			?>
			<TR style='background-color:#d0e4fe;'><TD><span id='totalCalc'/></TD></TR>
		</table>  
	
		<div style="margin-top:2%">
			<p><h6><span class='tip' title='Προσθέστε σημειώσεις στην παραγγελία σας, εάν θέλετε κάτι να ληφθεί υπόψιν. Οι σημειώσεις σας θα εμφανίζονται στη συνολική παραγγελία'>
			Σημειώσεις</span></h6></p>
			<!-- <input name="notes" type="text" maxlength="500" size="500" class='info-text' style="height:40px"></input> -->
			<textarea name="notes" id="notesArea" cols="50" rows="3" maxlength="500" class='info-text'></textarea>
		</div>
		
		<input type='button' value='Καταχώρηση' style="margin-top:3%" onclick="CsaWpPluginSendRequestSumbitOrderToServer('<?php echo $user->user_login; ?>')"/> 
	</FORM>
	</div>
		
	<?php
}

add_action( 'wp_ajax_csa_wp_plugin_add_new_order', 'CsaWpPluginAddNewOrder' );

function CsaWpPluginAddNewOrder() {

	echo "Called!";
	echo "user:[". $_POST['user_login']. "], data:[" . $_POST['data'] . "]";

/*	echo "Member name: ".$current_user->user_login."<br />";

	//Insert order records per product type		
	$numOfProducts = count($_POST)/2; //divided by 2 because for each quantity, its relative product is posted as well
	echo "Number of products ordered = ".$numOfProducts."<br/><br/><br/>** Order **<br/><br/>"; 

	for($n=0; $n<$numOfProducts; $n++) {
		$quantity =  $_POST["quantity".$n];
		$productInfo = explode("|", $_POST["relProduct".$n]); //type|variety
		$productType = $productInfo[0];
		$productVariety = $productInfo[1];
		
		if($quantity>0){
			echo $productType.": ".$quantity."<br/>";
					
			//get product info from db
			$products = $wpdb->get_results( "SELECT id,variety,price,unit FROM csa_product WHERE type='".$productType."' AND variety='".$productVariety."' ");
			//insert order query
			$rowsAffected = $wpdb->query("INSERT INTO wp_csa_orders 
											(`id`,`user_login`,`product_id`,`type`,`variety`,`price`,`unit`,`date`,`quantity`) 
											VALUES (NULL,'".$current_user->user_login."',".$products[0]->id.",'".$productType."','".$products[0]->variety."',".$products[0]->price.",'".$products[0]->unit."',CURDATE(),".$quantity.") "); //DATE_FORMAT(CURDATE(),'%Y/%b/%e')


			// $completed = $wpdb->insert($ords,
									// array("user_login" => $current_user->user_login, "product_id" => $products[0]->id, "type" => $productType, "variety"=>$products[0]->variety, "price"=>$products[0]->price, "unit"=>$products[0]->unit, "date"=>date('d-M-Y'), "quantity" => $quantity ), 
									// array("%s", "%d", "%s", "%s", "%f", "%s", "%s", "%f") );
		}
	}
/*
	if($rowsAffected > 0) {
		
		//insert notes (if any)
		$notes = $_POST["notes"];
		if($notes != null)
			$rowsAffected = $wpdb->query("INSERT INTO $ordNotes (`note_id`,`user_login`,`note`,`date`) 
										  VALUES (NULL,'".$current_user->user_login."','".$notes."',CURDATE())");

		//Redirect to the specified page	
		$bloginfo = get_site_url();
		header("Location:".$bloginfo."/?page_id=103"); //?page_id=125
	}		
	else
		header("Location:".$bloginfo."/?page_id=203"); 
*/
}





// ================================

/*
// order verification (my order) shortcode 
function verifyOrder() { 
?>
	<script type="text/javascript"> 
	var templateDir = "<?php echo get_site_url() ?>";
		function hover(element) {
			element.setAttribute('src', templateDir + '/wp-content/uploads/2013/12/delete_hover.png');
		}
		function unhover(element) {
			element.setAttribute('src', templateDir + '/wp-content/uploads/2013/12/delete.png');
		}
	</script>
	<script type="text/javascript">
	var $j = jQuery.noConflict();
	var templateDir = "<?php echo get_site_url() ?>";

	$j(document).ready(function() {
		var $j = jQuery.noConflict();
		var table = $j("#preorder_tb");
		var oTable = table.dataTable({"bPaginate": false, "bStateSave": true, "bInfo": false, "bFilter": false, "aaSorting": [[ 1, "desc" ]]});
		
		//edit any value of any object (of class .editable)
		$j(".editable", oTable.fnGetNodes()).editable(templateDir + "/dt_ajax.php?r=edit_preOrder", { 
			"callback": function(sValue, y) {
				var fetch = sValue.split(",");
				var aPos = oTable.fnGetPosition(this);
				
				oTable.fnUpdate(fetch[1], aPos[0], aPos[1]);
			},
			"submitdata": function(value, settings) {
				return {
					"row_id": this.parentNode.getAttribute("id")
					//"column": oTable.fnGetPosition(this)[2]
				};
			},
			"height": "14px"
		});
		
		//delete row 
		$j(document).on("click", ".delete", function() {
			var pid = $j(this).attr("id").replace("delete-", "");
			var parent = $j("#"+pid);
			$j.ajax({
				type: "get",
				url: templateDir + "/dt_ajax.php?r=delete_row&id="+pid,  //arxika htan jeditableData/php/ajax.php
				data: "",
				beforeSend: function() {
					table.block({
						message: "",
						css: {
							border: "none",
							backgroundColor: "none"
						},
						overlayCSS: {
							backgroundColor: "#fff",
							opacity: "0.5",
							cursor: "wait"
						}
					});
				},
				success: function(response) {
					table.unblock();
					var get = response.split(",");
					if(get[0] == "success") {
						$j(parent).fadeOut(200,function() {
							$j(parent).remove();
							calcTotalCost();
							//refreshDiv('add_div');						
						});
					}
				}
			});
		});
		
		//delete order	
		$j("#del_btn").click(function() {
			var username = document.getElementById('username').innerHTML;
			$j.post(templateDir + "/dt_ajax.php?r=delete_order",
			  {
				user_login: username
			  },
			  function(data,status){
				//alert("Data: " + data + "\nStatus: " + status);
				window.location.replace(templateDir + "/?page_id=103"); //redirect to the "new order" page
			  });
		});
	
	});
	</script>
	<script language="javascript" type="text/javascript">
	
	function count_perRow_Cost(event,rowId)
	{
		if (event.keyCode == 13) //on enter pressed
		{
			setTimeout( function(){countCost(rowId)}, 300); //fire a little later after the ajax call has returned
		}
	}
	
	function countCost(rowId) 
	{ 
		var cost = 0;
		var quantity = document.getElementById('quantity'+rowId).innerHTML;
		if(quantity >= 0) {
			 var price = document.getElementById('price['+rowId+']').innerHTML;
			 cost =  price * quantity;	
		}
		document.getElementById('cost['+rowId+']').innerHTML = cost.toFixed(2) + ' €';
		
		calcTotalCost();
	}
	
	function calcTotalCost() // <!>This function is not intended for large tables!
	{   
		//loop through all cells, looking for the 'cost' class ones. Then add their values. 
		var tds = document.getElementById('preorder_tb').getElementsByTagName('TD');
		var sum = 0;
		for(var i = 0; i < tds.length; i ++) {
			if( tds[i].className.indexOf('cost') != -1 ) 
			{
				var price = parseFloat( tds[i].innerHTML.replace(' €', '') );
				sum += price;
			}
		}
		document.getElementById('tCost').innerHTML = sum.toFixed(2) ;
	}
		
		
	//Functions used at the "add more products" section
	
	function hideshow(which){
		if (!document.getElementById)
		return
		if (which.style.display=="block")
		which.style.display="none"
		else
		which.style.display="block"
	}
	
	function calcCost()
	{
		var table = document.getElementById('preorder_tb');
		var rowCount = document.getElementById('numOfProducts').innerHTML; //table.rows.length;
		var totalCost = 0;
		 for (var i = 0; i < rowCount; i++)
		 {
			 var quantity = document.extraProductForm['quantity'+i].value;
			 if(quantity != null || quantity != '')
			 {
				quantity = quantity.replace(',' , '.'); //replace comma entries with dot
				document.extraProductForm['quantity'+i].value = quantity;
			 }	 
			 if(quantity > 0) {
				 var price = document.getElementById('price['+i+']').innerHTML;
				 totalCost +=  price * quantity;
				 
				 //add product name in this row's last hidden field, in order to be posted later on (malakia?)
				 var relProduct = document.extraProductForm['relProduct'+i].value;
				 if(relProduct ==null || relProduct=='')
					document.extraProductForm['relProduct'+i].value = document.getElementById('type['+i+']').innerHTML ;	

				if(relProduct ==null || relProduct=='')
				 {
					var typeValue = document.getElementById('type['+i+']').innerHTML ;
					var varietyValue = document.getElementById('variety['+i+']').innerHTML ;
					document.extraProductForm['relProduct'+i].value = typeValue +'|'+ varietyValue ;	 
				 }
					
				}
			 else if(quantity == 0){ //in case the user erases this products quantity, erase the relProduct field as well
				if(relProduct != '')
					document.extraProductForm['relProduct'+i].value = '';			
			}
				 
		 }
		document.getElementById('totalCalc').innerHTML = 'επιπλέον κόστος: <span style=font-weight:bold;color:green>' + totalCost.toFixed(2) + ' €</span>';
	}
	
	// function refreshDiv(div_id)
	// {
		// var div = document.getElementById(div_id);
		// div.parentNode.removeChild(div);
	// }
	</script>
	
	<!--clueTip code for showing product details in tooltip-->
	<script type="text/javascript">
		var $j = jQuery.noConflict();
		$j(document).ready(function() {
		  $j('.tip').cluetip({
			splitTitle: '|',							 
			showTitle: false 
		  });
		});
	</script>
	<link rel="stylesheet" href="jquery.cluetip.css" type="text/css" />
	
	<script type="text/javascript">
		var templateDir = "<?php echo get_site_url() ?>";
		var $j = jQuery.noConflict();
		$j(document).ready(function() {
			var username = document.getElementById('username').innerHTML;
			
			//insert new note	
			$j("#newNote_btn").click(function() {
				var notes_field = document.getElementById("notesArea").value;
				
				$j.post(templateDir + "/dt_ajax.php?r=insert_note",
				  {
					user_login: username,
					notes: notes_field
				  },
				  function(data,status){
					//refresh just the notes_div of the page
					var childThemeDir = "<?php echo get_bloginfo('stylesheet_directory'); ?>";
					$j("#notes_div").load(templateDir + "/refresh.php");

					document.getElementById("notesArea").value = "";
					
					//alert("Data: " + data + "\nStatus: " + status);
					// var test = document.getElementById("lastNoteId").innerHTML;
					// var newNote_id = parseInt(document.getElementById("lastNoteId").innerHTML) + 1;
					// $j('#notes_div').append(					
						// "<div id='div-"+ newNote_id +"'><span class='simple-text'>"+ notes_field +"</span>"+
						// "<span class='x-delete' id='x-"+ newNote_id +"'>&nbsp(x)</span></div>");
					//document.getElementById("lastNoteId").innerHTML = newNote_id; //++
				  });
			
			});
			
			//delete an existing note		
			$j("#note_div").on('click', '.x-delete', function() {
				//var note_id = $j(this).attr("id").replace("x-", "");
				//var parent = $j("#div-"+note_id);
				var note_text = $j(this).prev().text();
				var parent = $j(this).parent();
				
				$j.post(templateDir + "/dt_ajax.php?r=delete_note",
				  {
					//id:note_id
					user_login: username,
					note:note_text
				  },
				  function(data,status){
					//alert("Data: " + data + "\nStatus: " + status);
					parent.remove();
				  });
				
			});
		});
	</script>
	
	
	<p class='info-text'>Για να επεξεργαστείτε οποιαδήποτε ποσότητα, πιέστε τον αριθμό της, αλλάξτε τον και πατήστε ENTER. <br/>
	Για να διαγράψετε ένα προϊόν από την παραγγελία, πιέστε το εικονίδιο στο τέλος της γραμμής.</p>	
	
	<table class='table-bordered' id="preorder_tb"> 
	<thead class="tableHeader"><tr><th>ποσότητα</th><th	style="text-align:center">είδος</th><th>ποικιλία</th><th>€</th><th>ανά</th><th>κοστος</th><th></th><th></th></tr></thead> 
	<tbody> <?php
		//get current user
		$current_user = wp_get_current_user();
		if ( !($current_user instanceof WP_User) )
			return;
		else
			echo "<span id='username' style='visibility:hidden'>".$current_user->user_login."</span>";

		// differentating the content among users participating to UOC and/or Vasileies
		$is_a_member_of_UOC = false;
		require_once( ABSPATH . 'wp-includes/pluggable.php' );
		if ( $group = Groups_Group::read_by_name( 'CSA-UOC' ) ) {
		$is_a_member_of_UOC = Groups_User_Group::read( get_current_user_id() , $group->group_id );
		}
		if ($is_a_member_of_UOC == true) {
			$prefs = 'ucsa_preferences';
			$ords = 'ucsa_preorders';
			$ordNotes = 'ucsa_ordernotes';
		}
		else {
			$prefs = 'vasileies_csa_preferences';
			$ords = 'vasileies_csa_orders';
			$ordNotes = 'vasileies_csa_ordernotes';
		}


		//show an editable version of the current user's pre-order	
		global $wpdb;
		$last_delivery_date = $wpdb->get_var("SELECT pref_value FROM $prefs WHERE pref_name='last_delivery_date'");
		$ord_products = $wpdb->get_results("SELECT $ords.id,$ords.type,$ords.variety,$ords.price,$ords.unit,$ords.quantity,csa_product.producer AS producer, csa_product.details 
											FROM $ords, csa_product 
											WHERE user_login='".$current_user->user_login."' AND csa_product.id = $ords.product_id AND date BETWEEN '".$last_delivery_date."' AND CURDATE()" );
		
		foreach($ord_products as $row) 
		{
			$pCost = $row->price * $row->quantity;
			echo "<TR id='".$row->id."'>
			<TD id='quantity".$row->id."' class='editable' onkeyup='count_perRow_Cost(event,".$row->id.")' style='font-weight:bold;text-align:center;width:5%;padding-left:5px;color:blue;cursor:pointer;background-color:LightGoldenRodYellow  '>".$row->quantity."</TD>
			<TD>".$row->type."</TD><TD>".$row->variety."</TD><TD id='price[".$row->id."]'>".$row->price."</TD><TD>".$row->unit."</TD>
			<TD id='cost[".$row->id."]' class='cost' style='text-align:center;font-weight:bold'>".$pCost." €</TD>
			<TD style='text-align:center'><span class='tip' title='|".$row->details."|Παραγωγός: ". $row->producer."'>info</span></TD>
			<TD> <img id='delete-".$row->id."' class='delete no-underline' src='".get_site_url()."/wp-content/uploads/2013/12/delete.png' style='cursor:pointer;padding-left:10px;' onmouseover='hover(this);' onmouseout='unhover(this);' title='διαγραφή'> </TD>
			</TR>"; 
			$totalCost+=$pCost;
		}?>
		</tbody> 
		</table>
		<?php $consumer_fee_percentage = (float)$wpdb->get_var("SELECT pref_value FROM $prefs WHERE pref_name='consumer_fee_percentage'");?>

		<div style='text-align:right'>Σύνολο: <span id='tCost' style='font-weight:bold'><?php echo round($totalCost,1) + round($totalCost*$consumer_fee_percentage,1); ?></span><strong>&nbsp€</strong></div>
		<div style='text-align:right'><span style='font-size:0.8em'>Έσοδα Παραγωγών: <span id='tCost' style='font-weight:bold'><?php echo round($totalCost,1); ?></span></span><strong>&nbsp€</strong></div>
		<p style='text-align:right'><span style='font-size:0.8em'>Προτεινόμενα Έσοδα Ομάδας <?php echo "(".$consumer_fee_percentage*100 ."%)" ?>: <span id='tCost' style='font-weight:bold'><?php echo round($totalCost*$consumer_fee_percentage,1); ?></span></span><strong>&nbsp€</strong></p>
		
		<?php 	
		function loadExtraProductForm($current_user, $last_delivery_date)
		{
			echo "<div id='add_div' style='display: none;'><br/>
				  <form method='POST' action='insertOrder.php' name='extraProductForm' id='extraProductForm'>	
				  <table class='table-bordered' id='extraProducts_tb'> <thead class='tableHeader'><tr><th>είδος</th><th>ποικιλία</th><th>€</th><th>ανά</th><th>ποσότητα</th> <th> </th> <th></th></tr></thead>";
			
			//get non-ordered product IDs
			global $wpdb;
			$ord_products = $wpdb->get_col("SELECT product_id FROM $ords WHERE user_login='".$current_user->user_login."' AND date BETWEEN '".$last_delivery_date."' AND CURDATE()", 0 );
			$products = $wpdb->get_col("SELECT id FROM csa_product WHERE available='true'", 0);
			$products_left = arrayDiffEmulation($products, $ord_products);
			
			//for each available category, find non-ordered product matches in the array and display them one after another
			$countR=0; //row counter
			$categories = $wpdb->get_col("SELECT DISTINCT category FROM csa_product WHERE available='true'", 0);
			foreach ($categories as $cat)
			{
				$categoryProducts_counter = 0; //holds the number of products applying per category
				echo "<tr><td><strong><span class='emphasis-box' style='margin-left:-5px;'>".$cat."</span></strong></td></tr>";
				
				foreach($products_left as $prod_id) 
				{
					$id = $wpdb->get_var($wpdb->prepare("SELECT id FROM `csa_product` WHERE category='%s' AND id='%d'", $cat, $prod_id) );
					if($id != null)
					{
						$row = $wpdb->get_row($wpdb->prepare("SELECT type,variety,price,unit,producer,details FROM csa_product WHERE id='%d'", $id  ));
						echo "<TR><TD><span id='type[".$countR."]'>".$row->type."</span></TD><TD><span id='variety[".$countR."]'>".$row->variety."</span></TD><TD><span id='price[".$countR."]'>".$row->price."</span></TD><TD>".$row->unit."</TD>
						<TD><input type='number' min='0' step='0.5' name='quantity".$countR."' style='width:70px' onkeyup='calcCost()' onchange='calcCost()'></TD>
						<TD style='text-align:center'><span class='tip' title='|".$row->details."|Παραγωγός: ". $row->producer."'>info</span></TD>
						<TD><input type='text' name='relProduct".$countR."' style='visibility:hidden; width:1px'></TD></TR>";
						$categoryProducts_counter++;
						$countR++;
					}
				}
				if($categoryProducts_counter == 0)
					echo "<TR><TD><strong> &nbsp - </strong></TD></TR>";
				
			}
			
			echo "</table>  <input type='submit' value='Καταχώρηση'/> </form> 
				  <span id='numOfProducts' style='visibility:hidden'>".$countR."</span>
				  <p style='text-align:right'><span id='totalCalc' style='font-weight:bold'/></p>
				  </div>";
			
		}?>
		
		<div style="cursor:pointer" onclick="hideshow(document.getElementById('add_div'))"> 
			<img src="<?php echo get_site_url(); ?>/wp-content/uploads/2013/11/add.png"/ height="24" width="24"> &nbsp
			<span class='showHide_div'>Προσθήκη προϊόντων</span>
		</div>
		<?php loadExtraProductForm($current_user, $last_delivery_date) ?>
		
		<br/>
		
		<div style="cursor:pointer" onclick="hideshow(document.getElementById('note_div'))"> 
			<img src="<?php echo get_site_url(); ?>/wp-content/uploads/2013/11/add.png"/ height="24" width="24"> &nbsp
			<span class='showHide_div' style='color:rgb(255,153,0);'>Σημειώσεις για την παραγγελία</span>
		</div>
		<div id='note_div' style='display: none; padding-left:30px'>
			
			<!-- Show existing notes-->
			<div id="notes_div" style="margin-top:1%;"> 
				<?php 
				//$lastNote_id = -1; //stores the latest note's id for deleting reference (see js)
				$notes = $wpdb->get_results($wpdb->prepare("SELECT note FROM $ordNotes WHERE user_login='%s' AND date BETWEEN '%s' and CURDATE()", $current_user->user_login, $last_delivery_date));
				if($notes != null)
				{	//echo "<div><span class='info-text' style='font-weight:bold'>Καταχωρημένες σημειώσεις</span></div>";
					foreach ($notes as $note) {
						echo "<div>
						<span class='simple-text'>".$note->note."</span>
						<span class='x-delete'>(x)</span></div>";
						//$lastNote_id = $note->note_id;
					}
				}
				?>	
			</div>
			
			<!-- Add new note-->
			<div style="cursor:pointer" onclick="hideshow(document.getElementById('newNote_div'))"> 
				<!--<img src="<?php echo get_site_url(); ?>/wp-content/uploads/2013/11/add.png"/ height="15" width="15"> &nbsp-->
				<span class='showHide_div' style='color:green;font-weight:bold;font-style:italic;font-size:15px'>
				Nέα σημείωση...</span>
			</div>
			<div id="newNote_div" style='display:none;'>
				<!--<span class="info-text" style="margin-top:1%;font-weight:bold">Nέα σημείωση</span>-->
				<textarea name="notes" id="notesArea" cols="50" rows="3" maxlength="500" class='info-text'></textarea>
				<button id="newNote_btn" type="button" value="Submit" style="float:right">Καταχώρηση</button>
			</div>
			<!--<span id='lastNoteId' style='visibility:hidden'><?php //echo $lastNote_id ?></span>-->
		</div>
		
		<br/>
		
		<div style="cursor:pointer;" onclick="hideshow(document.getElementById('cancel_div'))"> 
			<img src="<?php echo get_site_url(); ?>/wp-content/uploads/2013/12/delete.png"/ height="24" width="24"> &nbsp
			<span class='showHide_div' style='color:OrangeRed;'>Ακύρωση παραγγελίας</span>
		</div>
		<div id='cancel_div' style='display:none; padding-left:30px'> <br/>
			<span style='font-weight:bold;font-size:14px'>Θέλετε σίγουρα να ακυρώσετε την παραγγελία σας;</span>
			<br/> <br/>
			<button type="button" id="del_btn">Ναι</button>
			<button type="button" style='background-color:red' onclick="hideshow(document.getElementById('cancel_div'))">Όχι</button>
		</div>
		
		<?php 
		// function deleteOrder()
		// {
			// global $wpdb;
			// $rowsDeleted = $wpdb->delete( $ords, array( 'user_login' => $current_user ) );
			
			// if($rowsDeleted > 0) {
				// //Redirect to the "new order" page	
				// $bloginfo = get_site_url();
				// header("Location:".$bloginfo."/?page_id=103"); 
			// }		
			// else
				// echo "<span style='color:red'>Κάτι δε δούλεψε σωστά. Παρακαλώ αναφέρετε τις λεπτομέρειες της ενέργειάς σας στο διαχειριστή </span>";
		// }
		?>
		
		
<?php
}
add_shortcode('orderVerification', 'verifyOrder');
*/
?>