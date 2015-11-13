<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/*	***************************
	CREATION OF DATABASE TABLES
	***************************
*/

function open_csa_wp_db_tables_creation () {

	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();


	$sql = "

	CREATE TABLE ". OPEN_CSA_WP_TABLE_SPOTS ." (
		id int(4) NOT NULL UNIQUE AUTO_INCREMENT,
		spot_name varchar(30) NOT NULL,
		street_name varchar(30) NOT NULL,
		street_number varchar(5) NOT NULL,
		city varchar(20) NOT NULL,
		region varchar(30) NOT NULL,
		description varchar(100) DEFAULT NULL,
		is_delivery_spot boolean NOT NULL,
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

	CREATE TABLE ". OPEN_CSA_WP_TABLE_SPOTS_TO_USERS ." (
		spot_id  int(10) NOT NULL,
		user_id  int(10) NOT NULL,
		type enum('production','delivery','home') NOT NULL,
		PRIMARY KEY  (spot_id,user_id,type)
	) $charset_collate;

	CREATE TABLE ". OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES ." (
		id  int(4) NOT NULL AUTO_INCREMENT,
		name varchar(20) NOT NULL,
		description varchar(100) DEFAULT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;

	CREATE TABLE ". OPEN_CSA_WP_TABLE_PRODUCTS ." (
		id int(10) NOT NULL AUTO_INCREMENT,
		name varchar(30) NOT NULL,
		category int(4) NOT NULL,
		variety varchar(30) DEFAULT NULL,
		current_price_in_euro float(5) NOT NULL,
		measurement_unit enum('piece', 'litre', 'kilogram', 'bunch') NOT NULL,
		producer int(4) NOT NULL,
		description varchar(500) DEFAULT NULL,
		is_available boolean NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;

	CREATE TABLE ". OPEN_CSA_WP_TABLE_DELIVERIES ." (
		id int(11) NOT NULL AUTO_INCREMENT,
		spot_id int(4) NOT NULL,
		order_deadline_date date NOT NULL,
		order_deadline_time time NOT NULL,
		delivery_date date NOT NULL,
		delivery_start_time time NOT NULL,
		delivery_end_time time NOT NULL,
		user_in_charge int(4) DEFAULT NULL,
		are_orders_open boolean NOT NULL,
		PRIMARY KEY  (id)
	) $charset_collate;

	CREATE TABLE ". OPEN_CSA_WP_TABLE_PRODUCT_ORDERS ." (
		delivery_id int(11) NOT NULL,
		user_id int(11) NOT NULL,
		product_id int(11) NOT NULL,
		quantity int(4) NOT NULL,
		status enum('pending', 'accomplished', 'cancelled') DEFAULT 'pending',
		custom_price float(5) DEFAULT NULL,
		comments varchar(100) DEFAULT NULL,
		submission_or_last_edit_datetime datetime DEFAULT NULL,
		PRIMARY KEY  (delivery_id,user_id,product_id)
	) $charset_collate;

	CREATE TABLE ". OPEN_CSA_WP_TABLE_USER_ORDERS ." (
		delivery_id int(11) NOT NULL,
		user_id int(11) NOT NULL,
		time_of_arrival time DEFAULT NULL,
		comments varchar(500) DEFAULT NULL,
		submission_datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
		last_edit_datetime datetime DEFAULT NULL,
		PRIMARY KEY  (delivery_id,user_id)
	) $charset_collate;
	";

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );

	update_option( 'open-csa-wp-db-version', '1.0' );
}

/*	***************************
	DELETION OF DATABASE TABLES
	***************************
*/

function open_csa_wp_db_tables_drop() {

	global $wpdb;

	$wpdb->query("DROP TABLE IF EXISTS ". OPEN_CSA_WP_TABLE_SPOTS);
	$wpdb->query("DROP TABLE IF EXISTS ". OPEN_CSA_WP_TABLE_SPOTS_TO_USERS);
	$wpdb->query("DROP TABLE IF EXISTS ". OPEN_CSA_WP_TABLE_PRODUCT_CATEGORIES);
	$wpdb->query("DROP TABLE IF EXISTS ". OPEN_CSA_WP_TABLE_PRODUCTS);
	$wpdb->query("DROP TABLE IF EXISTS ". OPEN_CSA_WP_TABLE_DELIVERIES);
	$wpdb->query("DROP TABLE IF EXISTS ". OPEN_CSA_WP_TABLE_PRODUCT_ORDERS);
	$wpdb->query("DROP TABLE IF EXISTS ". OPEN_CSA_WP_TABLE_USER_ORDERS);
}


?>