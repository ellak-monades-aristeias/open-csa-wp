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
		default_delivery_start_time time NOT NULL,
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
		id  int(4) NOT NULL AUTO_INCREMENT, 
		name varchar(20) NOT NULL, 
		description varchar(100),
		PRIMARY KEY  (id)
	) $charset_collate;

	CREATE TABLE ". csaProducts ." (
		id int(10) NOT NULL AUTO_INCREMENT,
		name varchar(30) NOT NULL,
		category int(4) NOT NULL,
		variety varchar(30) DEFAULT NULL,		
		current_price_in_euro float(5) NOT NULL,
		measurement_unit enum('piece', 'litre', 'kilogram', 'bunch') NOT NULL,
		producer int(4) NOT NULL,
		description varchar(500),
		isAvailable boolean NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;
	
	CREATE TABLE ". csaDeliveries ." (
		id int(11) NOT NULL AUTO_INCREMENT,
		spot_id int(4) NOT NULL,
		order_deadline_date date NOT NULL,
		order_deadline_time time NOT NULL,
		delivery_date date NOT NULL,
		delivery_start_time time NOT NULL,
		delivery_end_time time NOT NULL,		
		userInCharge int(4) default NULL,
		areOrdersOpen boolean NOT NULL,
		PRIMARY KEY  (id)
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

/*	***************************
	DELETION OF DATABASE TABLES
	***************************
*/

function CsaWpPluginDBTablesDrop() {

	global $wpdb; 

	$wpdb->query("DROP TABLE IF EXISTS ". csaSpots);
	$wpdb->query("DROP TABLE IF EXISTS ". csaSpotsToUsers);
	$wpdb->query("DROP TABLE IF EXISTS ". csaProductCategories);
	$wpdb->query("DROP TABLE IF EXISTS ". csaProducts);
	$wpdb->query("DROP TABLE IF EXISTS ". csaDeliveries);
	$wpdb->query("DROP TABLE IF EXISTS ". csaOrders);
}


?>