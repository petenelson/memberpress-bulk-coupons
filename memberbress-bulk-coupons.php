<?php
/*
Plugin Name: MemberPress Bulk Coupons
Description: Allows creation of bulk coupons for MemberPress
Author: Pete Nelson
Version: 1.0
Author: Pete Nelson
Author URI: https://twitter.com/gungeekatx
Text Domain: memberpress-bulk-coupons
*/

require_once 'class-memberpress-bulk-coupons-core.php';

if ( class_exists( 'MemberPress_Bulk_Coupons' ) ) {
	$mb_bulk_coupons = new MemberPress_Bulk_Coupons();
	add_action( 'plugins_loaded', array( $mb_bulk_coupons, 'plugins_loaded' ) );
}