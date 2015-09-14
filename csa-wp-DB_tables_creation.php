<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*	***************************
	CREATION OF DATABASE TABLES
	***************************
*/

function CsaWpPluginDBTablesCreation () {

	global $wpdb; 
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE ". csaOrders ." (
	  id int(11) NOT NULL AUTO_INCREMENT,
	  user_login varchar(30) DEFAULT NULL,
	  product_id int(11) NOT NULL,
	  type text,
	  variety varchar(30) DEFAULT NULL,
	  price float DEFAULT NULL,
	  unit text,
	  date datetime DEFAULT NULL,
	  quantity float DEFAULT NULL,
	  PRIMARY KEY  (id)
	) $charset_collate;

	CREATE TABLE ". csaProducts ." (
	  id int(11) NOT NULL AUTO_INCREMENT,
	  type varchar(40) NOT NULL,
	  variety text,
	  price float DEFAULT NULL,
	  unit tinytext,
	  producer text,
	  category tinytext,
	  details text,
	  available tinytext,
	  PRIMARY KEY  (id)
	) $charset_collate;";
		
/*	$sql = "CREATE TABLE $table_name (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	  name tinytext NOT NULL,
	  text text NOT NULL,
	  url varchar(55) DEFAULT '' NOT NULL,
	  UNIQUE KEY id (id)
	) $charset_collate;";
*/

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	update_option( 'csa_wp_plugin_db_version', '1.0' );
}

/*	************************************
	ADDING ELEMENTS INTO DATABASE TABLES
	************************************
*/

function CsaWpPluginDBAddElements() {
	global $wpdb;
	
	$wpdb->insert( 
		csaProducts, 
		array( 
//			'id' => 1,
			'type' => "Πατάτες",
			'variety' => "spunta",
			'price' => 1,
			'unit' => "κιλό",
			'producer' => "Βασίλης Μοσχόπουλος",
			'category' => "Λαχανικά",
			'details' => "",
			'available' => "true"
		) 
	);

	$wpdb->insert( 
		csaProducts, 
		array( 
//			'id' => 2,
			'type' => "Αυγά",
			'variety' => "Ντόπιας Κότας",
			'price' => 0.3,
			'unit' => "τεμάχιο",
			'producer' => "Βάσω Παρασύρη",
			'category' => "Γαλακτοκομικά",
			'details' => "αυτά...",
			'available' => "true"
		) 
	);
	
	
	$wpdb->insert( 
		csaOrders, 
		array( 
//			'id' => 1, 
			'user_login' => "ekosmas",
			'product_id' => 1,
			'type' => "Πατάτες",
			'variety' => "spunta",
			'price' => 1,
			'unit' => "κιλό",
			'date' => "2015-09-01",
			'quantity' => 5 
		) 
	);

	
	$wpdb->insert( 
		csaOrders, 
		array( 
//			'id' => 2, 
			'user_login' => "ekosmas",
			'product_id' => 2,
			'type' => "Αυγά",
			'variety' => "Ντόπιας Κότας",
			'price' => 0.3,
			'unit' => "τεμάχιο",
			'date' => "2015-09-01",
			'quantity' => 100
		) 
	);
	
	$wpdb->insert( 
		csaOrders, 
		array( 
//			'id' => 2, 
			'user_login' => "manda",
			'product_id' => 2,
			'type' => "Αυγά",
			'variety' => "Ντόπιας Κότας",
			'price' => 0.3,
			'unit' => "τεμάχιο",
			'date' => "2015-09-01",
			'quantity' => 50
		) 
	);
	

}


/*	***************************
	DELETION OF DATABASE TABLES
	***************************
*/

function CsaWpPluginDBTablesDrop() {

	global $wpdb; 

	$wpdb->query("DROP TABLE IF EXISTS ". csaOrders);
	$wpdb->query("DROP TABLE IF EXISTS ". csaProducts);
}


?>