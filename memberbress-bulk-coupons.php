<?php
/*
Plugin Name: MemberPress Bulk Coupons
*/

/*
TODO

get currency code
	*/


require_once 'class-memberpress-bulk-coupons-core.php';

if ( class_exists( 'MemberPress_Bulk_Coupons' ) ) {
	$mb_bulk_coupons = new MemberPress_Bulk_Coupons();
	add_action( 'plugins_loaded', array( $mb_bulk_coupons, 'plugins_loaded' ) );
}