<?php
/**
 * Exit if accesses directly
 */
defined( 'ABSPATH' ) or exit;
if ( ! class_exists( 'Pie_WCWL_Frontend_Simple' ) ) {
	/**
	 * Loads up the waitlist for simple products
	 *
	 * @package  WooCommerce Waitlist
	 */
	class Pie_WCWL_Frontend_Simple extends Pie_WCWL_Frontend_Product {

		/**
		 * Current product ID
		 *
		 * @var int
		 */
		private $product_id;

		/**
		 * Pie_WCWL_Frontend_Simple constructor.
		 */
		public function __construct() {
			parent::__construct();
			if ( ! $this->product->is_in_stock() ) {
				$this->product_id = Pie_WCWL_Compatibility::get_product_id( $this->product );
				$this->init();
			}
		}

		/**
		 * Load up hooks if product is out of stock
		 */
		private function init() {
			if ( ! isset( $_POST['add-to-cart'] ) && $this->user_modified_waitlist ) {
				$this->process_waitlist_update();
			}
			$this->output_waitlist_elements();
		}

		/**
		 * If the user has attempted to modify the waitlist process the request
		 */
		private function process_waitlist_update() {
			if ( ! $this->user ) {
				$this->handle_waitlist_when_new_user( $this->product->waitlist );
			} else {
				$this->toggle_waitlist_action( $this->product->waitlist );
			}
		}

		/**
		 * Check version of WC and output waitlist elements on appropriate hooks
		 */
		private function output_waitlist_elements() {
			if ( Pie_WCWL_Compatibility::wc_is_at_least_3_0() ) {
				add_filter( 'woocommerce_get_stock_html', array( $this, 'append_waitlist_control' ), 20 );
			} else {
				add_filter( 'woocommerce_stock_html', array( $this, 'append_waitlist_control' ), 20 );
			}
			add_filter( 'woocommerce_get_availability', array( $this, 'append_waitlist_message' ), 20, 2 );
		}

		/**
		 * Appends the waitlist button HTML to text string
		 *
		 * @hooked filter woocommerce_stock_html
		 *
		 * @param string $string HTML for Out of Stock message
		 *
		 * @access public
		 * @return string HTML with waitlist button appended if product is out of stock
		 * @since  1.0
		 */
		public function append_waitlist_control( $string = '' ) {
			$string .= '<div class="wcwl_control">';
			if ( ! is_user_logged_in() ) {
				$string = $this->get_waitlist_elements_for_logged_out_user( $string );
			} else {
				if ( $this->product->waitlist->user_is_registered( $this->user ) ) {
					$string .= $this->get_waitlist_control( 'leave' );
				} else {
					$string .= $this->get_waitlist_control( 'join' );
				}
			}
			$string .= '</div>';

			return $string;
		}

		/**
		 * Appends the email input field and waitlist button HTML to text string for simple products
		 *
		 * @param string $string HTML for Out of Stock message
		 *
		 * @access public
		 * @return string HTML with email field and waitlist button appended
		 * @since  1.3
		 */
		private function get_waitlist_elements_for_logged_out_user( $string ) {
			$url = $this->create_button_url( 'join' );
			
			$varmsh='';
			
	if (! is_user_logged_in() &&  !isset($_GET['woocommerce_waitlist']) ) {
	
	$varmsh= __( 'If this product is restocked, we will notify by email', 'woocommerce-waitlist' );
	
	echo '<style>.button.alt.woocommerce_waitlist.join{margin-top: 18px;}</style>';
	}		
			
			$string .= '<form name="wcwl_add_user_form" action="' . esc_url( $url ) . '" method="post"><div class="clzwaitlisttop">'.$varmsh.'</div>';
			
			
			
			if ( ! WooCommerce_Waitlist_Plugin::users_must_be_logged_in_to_join_waitlist() ) {
				$string .= $this->get_waitlist_email_field();
			}
			$string .= $this->get_waitlist_control( 'join', $url ) . '</form>';

			return $string;
		}

		/**
		 * Get HTML for waitlist elements depending on product type
		 *
		 * @param string $context the context in which the button should be generated (join|leave)
		 * @param string $url
		 *
		 * @return string HTML for join waitlist button
		 * @access public
		 * @since  1.0
		 */
		private function get_waitlist_control( $context, $url = false ) {
			$text_string = $context . '_waitlist_button_text';
			$classes     = implode( ' ', apply_filters( 'wcwl_' . $context . '_waitlist_button_classes', array( 'button', 'alt', WCWL_SLUG, $context, ) ) );
			$text        = apply_filters( 'wcwl_' . $context . '_waitlist_button_text', $this->$text_string );
			$url         = $url ? $url : $this->create_button_url( $context );

				
			
			
			
			/*
			if ($tst ) {
				$varmsh='Test here';
			}
			
			*/
			
			
			return apply_filters( 'wcwl_' . $context . '_waitlist_button_html', '<div class="wcwl_control"><a href="' . esc_url( $url ) . '" class="' . esc_attr( $classes ) . '" data-id="' . $this->product_id . '" id="wcwl-product-' . esc_attr( $this->product_id ) . '">' . esc_html( $text ) . '</a></div>' );
		}

		/**
		 * Get URL to toggle waitlist status
		 *
		 * @param string $action
		 *
		 * @return string
		 * @access private
		 */
		private function create_button_url( $action = '' ) {
			$url = add_query_arg( WCWL_SLUG, $this->product_id, get_permalink( $this->product_id ) );
			$url = add_query_arg( WCWL_SLUG . '_action', $action, $url );
			$url = add_query_arg( WCWL_SLUG . '_nonce', wp_create_nonce( __FILE__ ), $url );

			return apply_filters( 'wcwl_toggle_waitlist_url', $url );
		}

		/**
		 * Checks whether product is in stock and if not, appends the waitlist message of 'join/leave waitlist' to the 'out
		 * of stock' message
		 *
		 * @param array  $array   stock details
		 * @param object $product the current product
		 *
		 * @access public
		 * @return mixed Value.
		 * @since  1.4.12
		 */
		public function append_waitlist_message( $array, $product ) {
			if ( ! is_user_logged_in() || ! $this->product->waitlist->user_is_registered( $this->user ) ) {
				$array['availability'] .= apply_filters( 'wcwl_join_waitlist_message_text', ' - ' . $this->join_waitlist_message_text );
			} else {
				$array['availability'] .= apply_filters( 'wcwl_leave_waitlist_message_text', ' - ' . $this->leave_waitlist_message_text );
			}

			return $array;
		}
	}
}