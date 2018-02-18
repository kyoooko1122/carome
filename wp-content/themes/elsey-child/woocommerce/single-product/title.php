<?php
/**
 * Single Product title
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/title.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.woocommerce.com/document/template-structure/
 * @author     WooThemes
 * @package    WooCommerce/Templates
 * @version    1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
global $post, $main_product_id;
$main_product_id = $post->ID;
$current_user = wp_get_current_user();

echo '<p class="pdp__name product_title_en">'. get_post_meta($post->ID, '_custom_product_text_field', true) .'</p>';
// Display the value of custom product text field
echo '<h1 class="product_title entry-title ja-product-name">';
the_title();
echo '</h1>';


?>

<script type="text/javascript">
	var already_in_waitlist = 'すでに再入荷通知に登録されています';
</script>
<div class="remodal" data-remodal-id="waitlist_remodal" id="waitlist_remodal" data-remodal-options="hashTracking: false, closeOnOutsideClick: false">

  <button data-remodal-action="close" class="remodal-close"></button>
  <h1><?php echo __('WaitList', 'elsey')?></h1>
  
  <div class="body_content">
	<p><?php echo sprintf(__('We will notify to %s', 'elsey'), $current_user->user_email)?></p>
</div>

  <form method="post" id="form_waitlist_modal">
  <div id="waitlist_remodal_content"></div>
  
  <?php if (!is_user_logged_in()) {?>
	<div class="wcwl_email_field form-group">
		<label for="wcwl_email_new"><?php echo __('Email', 'elsey')?></label>
		<input type="email" name="wcwl_email" class="form-control" id="wcwl_email_new" required />
	</div>
	<?php }?>
	
  <h3 class="waitlist_error" style="display: none;"></h3>
  <button type="button" class="button alt woocommerce_waitlist_new" id="submit_add_waitlist"><?php echo __('Add To Waitlist', 'elsey')?></button>
  </form>
</div>