<?php

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

if ( ! class_exists( 'MemberPress_Bulk_Coupons' ) ) {

	class MemberPress_Bulk_Coupons {

		static $version = '2015-05-12-01';

		public function plugins_loaded() {
			add_action( 'admin_init', array( $this, 'enqueue_js' ) );
			add_action( 'save_post', array( $this, 'handle_coupon_save' ), 1, 1 );
		}

		public function enqueue_js() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'memberpress-bulk-coupons', plugin_dir_url( __FILE__ ) . 'memberpress-bulk-coupons.js', array( 'jquery' ), self::$version, true );
		}

		public function handle_coupon_save( $post_id ) {

	

			// TODO verify nonce
			$number_of_coupons = absint ( filter_input( INPUT_POST, '_mepr_bulk_number_of_coupons', FILTER_SANITIZE_NUMBER_INT ) );

			$post = get_post( $post_id );
			if ( ! empty( $post ) && $post->post_type === 'memberpresscoupon' && ! empty( $number_of_coupons ) ) {

				// unhook the default save
				remove_action( 'save_post', 'MeprCouponsController::save_postdata' );

				// unhook our own save
				remove_action( 'save_post', array( $this, 'handle_coupon_save'), 1 );

				// create a new post with for each of the number of coupons
				// with a coupon code as the title
				for ($i=0; $i < $number_of_coupons; $i++) {
					$new_post_id = wp_insert_post( array(
							'post_type' => 'memberpresscoupon',
							'post_status' => 'publish',
							'post_title' => strtoupper( wp_generate_password( 10, false, false ) ),
						)
					);

					MeprCouponsController::save_postdata( $new_post_id );

				}

				// call the MeprCouponsController::save_postdata for each post

			}

			// dd_action('save_post', 'MeprCouponsController::save_postdata');


		}

	}


}