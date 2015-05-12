<?php

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

if ( ! class_exists( 'MemberPress_Bulk_Coupons' ) ) {

	class MemberPress_Bulk_Coupons {

		static $version = '2015-05-12-01';

		public function plugins_loaded() {
			add_action( 'admin_init', array( $this, 'enqueue_js' ) );
			add_action( 'save_post', array( $this, 'handle_coupon_save' ), 100 );
		}

		public function enqueue_js() {
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'memberpress-bulk-coupons', plugin_dir_url( __FILE__ ) . 'memberpress-bulk-coupons.js', array( 'jquery' ), self::$version, true );
		}

		public function handle_coupon_save( $post_id ) {

			if ( ! class_exists( 'MeprCoupon' ) ) {
				return;
			}

			// verify nonce, copied from Mepr code

		    if(!wp_verify_nonce((isset($_POST[MeprCoupon::$nonce_str]))?$_POST[MeprCoupon::$nonce_str]:'', MeprCoupon::$nonce_str.wp_salt()))
		      return $post_id; //Nonce prevents meta data from being wiped on move to trash

		    if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
		      return $post_id;

		    if(defined('DOING_AJAX'))
		      return;

		  	// see if we're doing bulk coupons
			$number_of_coupons = filter_input( INPUT_POST, '_mepr_bulk_number_of_coupons', FILTER_SANITIZE_NUMBER_INT );

			$post = get_post( $post_id );
			if ( ! empty( $post ) && $post->post_type === MeprCoupon::$cpt && $number_of_coupons >= 2 ) {

				// since the original hook below already processed the coupon, we only
				// need to do this for additional coupons
				$number_of_coupons--;

				// TODO somewhere in here, store a list of post IDs that were created
				// so we can offer a download link later on

				remove_action( 'save_post', 'MeprCouponsController::save_postdata' );

				// unhook our own save
				remove_action( 'save_post', array( $this, 'handle_coupon_save'), 100 );

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

			}


		}

	}


}