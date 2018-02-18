<?php
/**
 * View Order
 *
 * Shows the details of a particular order on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/view-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="pt_order--details">
<div class="account__heading">
	<h1 class="heading heading--xlarge serif"><?php esc_html_e( 'Order Details', 'elsey' ); ?></h1>
</div>
<div class="order--details">
<div class="order--details__info">
	<h2 class="order--details__number heading heading--xsmall">
		<span class="label"><?php _e( 'Order ID', 'woocommerce' ); ?></span>
		<?php
		printf(
			'<span class="value">#' . $order->get_order_number() . '</span>'
		);
		?>
	</h2>
	<div class="order--details__info--half">
		<div class="order-status serif">
			<span class="label"><?php _e( 'Status', 'elsey' ); ?></span>
			<?php
			printf(
				'<span class="value"><mark class="order-status">' . wc_get_order_status_name( $order->get_status() ) . '</mark></span>'
			);
			?>
		</div>
		
		
		<div class="order-status serif">
			<span class="label"><?php _e( 'Payment Status', 'elsey' ); ?></span>		
		<?php
			$orderxid=$order->get_id();
			$paidstatus=get_post_meta( $orderxid, '_custom_payment_status', true );
				switch($paidstatus){
              case '1':
                $payment_status = __( 'Paid', 'woocommerce-payment-status' );
                break;
              case '2':
                $payment_status = __( 'Partially Paid', 'woocommerce-payment-status');
                break;              
              default:
                $payment_status = __( 'Not Paid', 'woocommerce-payment-status' );
                break;
            }
			
			echo '<span class="value"><mark class="payment-status">'.$payment_status.'</mark></span>';
	
			?>
		</div>
		<p class="order--details__date serif">
			<span class="label"><?php _e( 'Order date', 'elsey' ); ?></span>
			<?php
			printf(
				'<span class="value">' . wc_format_datetime( $order->get_date_created() ) . '</span>'
			);
			?>
		</p>
		<p class="purchase-note"><?php if ( ('bacs' == $order->payment_method) && ($order->status == 'on-hold') ) {?><?php } ?></p>
	</div>
</div>

<?php if ( $notes = $order->get_customer_order_notes() ) : ?>
	<h2><?php _e( 'Order updates', 'woocommerce' ); ?></h2>
	<ol class="woocommerce-OrderUpdates commentlist notes">
		<?php foreach ( $notes as $note ) : ?>
		<li class="woocommerce-OrderUpdate comment note">
			<div class="woocommerce-OrderUpdate-inner comment_container">
				<div class="woocommerce-OrderUpdate-text comment-text">
					<p class="woocommerce-OrderUpdate-meta meta"><?php echo date_i18n( __( 'l jS \o\f F Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ); ?></p>
					<div class="woocommerce-OrderUpdate-description description">
						<?php echo wpautop( wptexturize( $note->comment_content ) ); ?>
					</div>
	  				<div class="clear"></div>
	  			</div>
				<div class="clear"></div>
			</div>
		</li>
		<?php endforeach; ?>
	</ol>
<?php endif; ?>

<?php do_action( 'woocommerce_view_order', $order_id ); ?>
</div>
</div>
