<?php

if(!defined('ABSPATH')) {die('You are not allowed to call this page directly.');}

if ( ! class_exists( 'MemberPress_Bulk_Coupons' ) ) {

	class MemberPress_Bulk_Coupons {

		static $version = '2015-05-12-01';

		public function plugins_loaded() {
			add_action( 'current_screen', array( $this, 'enqueue_js' ), 10, 0 );
			add_action( 'save_post', array( $this, 'handle_coupon_save' ), 100 );
			add_action( 'admin_notices', array( $this, 'display_download_coupons') );

		}


		public function enqueue_js() {

			if ( ! class_exists( 'MeprCoupon' ) ) {
				return;
			}

			if ( is_admin() ) {
				$screen = get_current_screen();
				if ( ! empty( $screen )  && $screen->post_type === MeprCoupon::$cpt ) {
					wp_enqueue_script( 'jquery' );
					wp_enqueue_script( 'memberpress-bulk-coupons', plugin_dir_url( __FILE__ ) . 'memberpress-bulk-coupons.js', array( 'jquery' ), self::$version, true );
				}
			}
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

				$coupon_codes = array( $post->title );

				// since the original hook below already processed the coupon, we only
				// need to do this for additional coupons
				$number_of_coupons--;

				// unhook the MemberPress save
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

					$coupon_codes[] = $post->title;

				}

				// save list of coupons to allow the user to download later
				if ( count( $coupon_codes ) > 1 ) {
					$key = $this->coupon_codes_transient_key();
					set_site_transient( $key, $coupon_codes, MINUTE_IN_SECONDS * 30 );
				}

			}


		}


		private function coupon_codes_transient_key() {
			return 'memberpress-bulk-coupons-' . get_current_user_id();
		}


		public function display_download_coupons() {
			$coupon_codes = get_site_transient( $this->coupon_codes_transient_key() );
			if ( ! empty( $coupon_codes ) && is_array( $coupon_codes ) ) {
				$url = add_query_arg(
					array(
							'_wpnonce' => wp_create_nonce( ),
							'memberpress-bulk-coupon-action' => 'download',
						),
					admin_url()
					);
				?>
				    <div class="updated">
				        <p><a href="<?php echo $url; ?>"><?php printf( __( 'Download %d bulk coupons', 'memberpress-bulk-coupons' ), count( $coupon_codes ) ); ?></a>
				    </div>
				<?php
			}
		}


	}


}