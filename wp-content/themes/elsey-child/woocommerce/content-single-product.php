<?php
/**
 * VictorTheme Custom Changes - Row Class, Image Col and Summary Col Class Added
 */

/**
 * The template for displaying product content in the single-product.php template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php
/**
 * woocommerce_before_single_product hook.
 *
 * @hooked wc_print_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
  echo get_the_password_form();
 	return;
}

global $post, $product;

$product_options = get_post_meta( $product->get_id(), 'product_options', true );
$product_sticky  = isset($product_options['product_sticky_info']) ? $product_options['product_sticky_info'] : false; 

if ( $product_sticky ) {
	$product_sticky_image_class   = "els-product-images-sticky col-lg-6 col-md-6 col-sm-6 col-xs-12";
	$product_sticky_summary_class = "els-product-summary-sticky col-lg-6 col-md-6 col-sm-6 col-xs-12";
} else {
	$product_sticky_image_class   = "col-lg-7 col-md-6 col-sm-6 col-xs-12";
    $product_sticky_summary_class = "col-lg-5 col-md-6 col-sm-6 col-xs-12";
} ?>

<div id="product-<?php the_ID(); ?>" <?php post_class(); ?>>

  	<!-- Custom Changes Starts -->
  	<div class="els-single-product-wrap">   

		<?php $woo_single_nav = (cs_get_option('woo_single_nav')) ? true : false;  
		if ($woo_single_nav) { ?>
		  	<div class="els-single-product-nav">
		    	<div class="els-single-next-link">
		      		<?php next_post_link('%link', apply_filters('elsey_single_product_next', '<i class="fa fa-angle-left" aria-hidden="true"></i>'), false, array(), 'product_cat'); ?>
		    	</div>
		   	 	<div class="els-single-prev-link">
		      		<?php previous_post_link('%link', apply_filters('elsey_single_product_prev', '<i class="fa fa-angle-right" aria-hidden="true"></i>'), false, array(), 'product_cat'); ?>
		    	</div>
		  	</div>
		<?php } ?>
		
		<div class="row"> 
    		<div class="els-product-image-col col-md-6 col-xs-12 images"> 
    	  		<!-- Custom Changes Ends --> 
				<?php
					/**
					 * woocommerce_before_single_product_summary hook.
					 *
					 * @hooked woocommerce_show_product_sale_flash - 10
					 * @hooked woocommerce_show_product_images - 20
					 */
					do_action( 'woocommerce_before_single_product_summary' );
				?>
				<!-- Custom Changes Starts -->
			</div>

			<div class="els-product-summary-col col-md-6 col-xs-12">	
			  <!-- Custom Changes Ends -->
				<div class="summary entry-summary">
					<?php
						/**
						 * woocommerce_single_product_summary hook.
						 *
						 * @hooked woocommerce_template_single_title - 5
						 * @hooked woocommerce_template_single_rating - 10
						 * @hooked woocommerce_template_single_price - 10
						 * @hooked woocommerce_template_single_excerpt - 20
						 * @hooked woocommerce_template_single_add_to_cart - 30
						 * @hooked woocommerce_template_single_meta - 40
						 * @hooked woocommerce_template_single_sharing - 50
						 * @hooked WC_Structured_Data::generate_product_data() - 60
						 */
						do_action( 'woocommerce_single_product_summary' );
					?>
				</div><!-- .summary -->
			  <!-- Custom Changes Starts -->
			</div>
		</div>

	</div>
	<!-- Custom Changes Ends -->

	<?php
		/**
		 * woocommerce_after_single_product_summary hook.
		 *
		 * @hooked woocommerce_output_product_data_tabs - 10
		 * @hooked woocommerce_upsell_display - 15
		 * @hooked woocommerce_output_related_products - 20
		 */
		do_action( 'woocommerce_after_single_product_summary' );
	?>

</div><!-- #product-<?php the_ID(); ?> -->

<?php do_action( 'woocommerce_after_single_product' ); ?>

<?php if (class_exists('productsize_chart_Public')) {?>
<div class="remodal" data-remodal-id="chart_size_modal" role="dialog" aria-labelledby="modal1Title" aria-describedby="modal1Desc">
	<button data-remodal-action="close" class="remodal-close" aria-label="Close"></button>
	<div id="chart_popup_content">
	</div>
</div>
<?php }?>