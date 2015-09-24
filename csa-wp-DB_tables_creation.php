<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*	***************************
	CREATION OF DATABASE TABLES
	***************************
*/

function CsaWpPluginDBTablesCreation () {

	global $wpdb; 
	$charset_collate = $wpdb->get_charset_collate();
	
	
	$sql = "	
	
	CREATE TABLE ". csaSpots ." (
		id int(11) NOT NULL UNIQUE AUTO_INCREMENT,
		spotName varchar(30) NOT NULL,
		streetName varchar(30) NOT NULL,
		streetNumber varchar(5) NOT NULL,
		city varchar(20) NOT NULL,
		region varchar(30) NOT NULL,
		description varchar(100) DEFAULT NULL,
		isDeliverySpot boolean NOT NULL,
		close_order enum('manual','automatic'),
		default_order_deadline_day enum('0','1','2','3','4','5','6') NOT NULL,
		default_order_deadline_time time NOT NULL,
		default_delivery_day enum('0','1','2','3','4','5','6') NOT NULL,
		default_delivery_strart_time time NOT NULL,
		default_delivery_end_time  time NOT NULL,
		has_refrigerator boolean DEFAULT NULL,
		parking enum('easy','possible','hard','impossible') DEFAULT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;
	
	CREATE TABLE ". csaSpotsToUsers ." (
		spot_id  int(4) NOT NULL, 
		user_id  int(4) NOT NULL, 
		type enum('production','delivery','home') NOT NULL,
		PRIMARY KEY  (spot_id, user_id, type)
	) $charset_collate;	
	
	CREATE TABLE ". csaProductCategories ." (
		pc_id  int(4) NOT NULL AUTO_INCREMENT, 
		name varchar(20) NOT NULL, 
		description varchar(100),
		PRIMARY KEY  (pc_id)
	) $charset_collate;

	CREATE TABLE ". csaProducts ." (
		p_id int(10) NOT NULL AUTO_INCREMENT,
		name varchar(30) NOT NULL,
		category int(4) NOT NULL,
		producer int(4) NOT NULL,
		variety varchar(30) DEFAULT NULL,		
		measurement_unit enum('piece', 'litre', 'kilogram', 'bunch') NOT NULL,
		current_price_in_euro float(5) NOT NULL,
		description varchar(500),
		isFrail tinyint(1),
		isExchangeable tinyint(1),
		isAvailable tinyint(1),
		PRIMARY KEY  (p_id)
	) $charset_collate;

	CREATE TABLE ". csaOrders ." (
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
	
	";
		
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	update_option( 'csa-wp-plugin-db_version', '1.0' );
}

/*	************************************
	ADDING ELEMENTS INTO DATABASE TABLES
	************************************
*/

function CsaWpPluginDBAddElements() {
	global $wpdb;
	
/*	
		name 
		variety varchar(30) DEFAULT NULL,		
		measurement_unit enum('piece', 'litre', 'kilogram', 'bunch') NOT NULL,
		current_price_in_euro float(5) NOT NULL,
		description varchar(500),
		isFrail tinyint(1),
		isExchangeable tinyint(1),
		isAvailable tinyint(1),
		
/*	$wpdb->insert( 
		csaProducts, 
		array( 
			'name' => "Πατάτες",
			'variety' => "spunta",
			'current_price_in_euro' => 1,
			'measurement_unit' => "κιλό",
			'producer' => "Πάρης Πατατούδης",
			'category' => "Λαχανικά",
			'details' => "",
			'available' => "true"
		) 
	);

	$wpdb->insert( 
		csaProducts, 
		array( 
			'type' => "Αυγά",
			'variety' => "Ντόπιας Κότας",
			'price' => 0.3,
			'unit' => "τεμάχιο",
			'producer' => "Αυγή Λαχανούλα",
			'category' => "Γαλακτοκομικά",
			'details' => "αυτά...",
			'available' => "true"
		) 
	);
	
	
	$wpdb->insert( 
		csaOrders, 
		array( 
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
*/
	
	$wpdb->insert( 
		csaOrders, 
		array( 
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
			'user_login' => "haridimos",
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

	$wpdb->query("DROP TABLE IF EXISTS ". csaSpots);
	$wpdb->query("DROP TABLE IF EXISTS ". csaSpotsToUsers);
	$wpdb->query("DROP TABLE IF EXISTS ". csaOrders);
	$wpdb->query("DROP TABLE IF EXISTS ". csaProducts);
	$wpdb->query("DROP TABLE IF EXISTS ". csaProductCategories);
	
	

}


?>