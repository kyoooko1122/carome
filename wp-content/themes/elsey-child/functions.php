<?php
/**
 * Enqueues child theme stylesheet, loading first the parent theme stylesheet.
 */
function elsey_enqueue_child_theme_styles() {
	wp_enqueue_style( 'elsey-child-style', get_stylesheet_uri(), array(), null );
}
add_action( 'wp_enqueue_scripts', 'elsey_enqueue_child_theme_styles', 11 );

function remove_product_editor() {
  remove_post_type_support( 'product', 'editor' );
}
add_action( 'init', 'remove_product_editor' );

remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 60 );

// google fonts
function custom_add_google_fonts() {
 wp_enqueue_style( 'custom-google-fonts', 'https://fonts.googleapis.com/css?family=Lato|Poppins:300,400,500,600', false );
 }
 add_action( 'wp_enqueue_scripts', 'custom_add_google_fonts' );

/*hide adminbar*/
add_filter('show_admin_bar', '__return_false');

/**
 * バージョンアップ通知を管理者のみ表示させるようにします。
 */
function update_nag_admin_only() {
    if ( ! current_user_can( 'administrator' ) ) {
        remove_action( 'admin_notices', 'update_nag', 3 );
    }
}
add_action( 'admin_init', 'update_nag_admin_only' );

/*add class to body*/
add_filter( 'body_class', 'add_page_slug_class_name' );
function add_page_slug_class_name( $classes ) {
  if ( is_page() ) {
    $page = get_post( get_the_ID() );
    $classes[] = $page->post_name . '-template';

    $parent_id = $page->post_parent;
    if ( $parent_id ) {
      $classes[] = get_post($parent_id)->post_name . '-child-template';
    }
  }
  return $classes;
}


/*add custom logo menu for sticky*/
add_filter( 'wp_nav_menu_items', 'custom_menu_item_logo', 10, 2 );
function custom_menu_item_logo ( $items, $args ) {
    if ($args->theme_location == 'primary') {
		$items_array = array();
		while ( false !== ( $item_pos = strpos ( $items, '<li', 1 ) ) )
		{
			$items_array[] = substr($items, 0, $item_pos);
			$items = substr($items, $item_pos);
		}
		$items_array[] = $items;
		$elsey_brand_logo_default = cs_get_option('brand_logo_default');
		array_splice($items_array, 0, 0, '<li class="navLogo"><a href="'. esc_url(home_url( '/' )) .'"><img src="'. esc_url( wp_get_attachment_url( $elsey_brand_logo_default ) ) .'" alt="'. esc_attr( get_bloginfo( 'name' ) ) .'" class="sticky-logo"></a></li>');
		$items = implode('', $items_array);
    }
    return $items;
}
/*add custom actions menu for sticky*/
add_filter( 'wp_nav_menu_items', 'custom_menu_item_actions', 10, 2 );
function custom_menu_item_actions ( $items2, $args ) {
	//$items2 = "";
    if ($args->theme_location == 'primary') {
		$items2 .= '<li class="navActions"><ul>';
		$elsey_myaccount_url = get_permalink(get_option('woocommerce_myaccount_page_id'));
		$items2 .= '<li class="action-minibar els-user-icon"><a href="' . esc_url($elsey_myaccount_url) . '" class="link-actions"><i class="carome-icon carome-single-01"></i></a></li>';
		$elsey_menubar_wishlist    = cs_get_option('menubar_wishlist');
		if ( $elsey_menubar_wishlist && class_exists('WooCommerce') ) {
			if ( defined( 'YITH_WCWL' ) ) {
				$els_wishlist_count = YITH_WCWL()->count_products();
				$els_wishlist_url   = get_permalink(get_option('yith_wcwl_wishlist_page_id'));
				$elsey_icon_wishlist_black = ELSEY_IMAGES.'/wishlist-icon.png';
				$els_wishlist_class = ($els_wishlist_count) ? 'els-wishlist-filled' : 'els-wishlist-empty';
				$items2 .= '<li class="action-minibar els-wishlist-icon '. esc_attr($els_wishlist_class) .'"><a href="'. esc_url($els_wishlist_url) .'" class="link-actions"><i class="carome-icon carome-heart-2"></i></a></li>';
			}
		}
		$elsey_menubar_cart        = cs_get_option('menubar_cart');
		if ( $elsey_menubar_cart && class_exists('WooCommerce') ) {
			global $woocommerce;
			$items2 .= '<li id="els-shopping-cart-content-sticky" class="els-shopping-cart-content-sticky action-minibar"><a href="javascript:void(0);" id="els-cart-trigger-sticky" class="link-actions">';
			$items2 .= '<span class="action-icon-count">';
			if ( $woocommerce->cart->get_cart_contents_count() == '0' ) {
				$items2 .= '<span class="els-cart-count els-cart-zero">' . esc_attr($woocommerce->cart->get_cart_contents_count()) . '</span>';
			} else {
				$items2 .= '<span class="els-cart-count">'. esc_attr($woocommerce->cart->get_cart_contents_count()) .'</span>';
			}
			$items2 .= '<i class="carome-icon carome-bag-09"></i></span>';
			$items2 .= '</a></li>';
		}
		$items2 .= '</li></ul>';
    }
	return $items2;
}
/*override checkiout.min.js*/
add_action( 'wp_enqueue_scripts', 'custom_wp_enqueue_scripts_for_frontend', 99 );
function custom_wp_enqueue_scripts_for_frontend(){
    if( is_checkout() ){
        // Checkout Page        
        wp_deregister_script('wc-checkout');
        wp_register_script('wc-checkout', get_stylesheet_directory_uri() . "/woocommerce/assets/js/frontend/checkout.js", 
        array( 'jquery', 'woocommerce', 'wc-country-select', 'wc-address-i18n' ), WC_VERSION, TRUE);
        wp_enqueue_script('wc-checkout');
    }
    
}
/*address form*/
add_filter( 'woocommerce_form_field_args', 'custom_wc_form_field_args', 10, 3 );
function custom_wc_form_field_args( $args, $key, $value ){
    // Only on My account > Edit Adresses
    if( is_wc_endpoint_url( 'edit-account' ) || is_checkout() ) return $args;

    $args['label_class'] = array('label');

    return $args;
}
/*change positon of payment section*/
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );
add_action( 'woocommerce_after_order_notes', 'woocommerce_checkout_payment', 20 );

/*remove country field from checkout*/
function custom_override_checkout_fields( $fields )
{
	unset($fields['billing']['billing_country']);
	unset($fields['shipping']['shipping_country']);
	return $fields;
}
add_filter('woocommerce_checkout_fields','custom_override_checkout_fields');

function custom_override_billing_fields( $fields ) {
  unset($fields['billing_country']);
  return $fields;
}
add_filter( 'woocommerce_billing_fields' , 'custom_override_billing_fields' );

function custom_override_shipping_fields( $fields ) {
  unset($fields['shipping_country']);
  return $fields;
}
add_filter( 'woocommerce_shipping_fields' , 'custom_override_shipping_fields' );


/*remove postcode from shipping calculater*/
add_filter( 'woocommerce_shipping_calculator_enable_postcode', '__return_false' );

/*remove additional info*/
add_filter( 'woocommerce_product_tabs', 'bbloomer_remove_product_tabs', 98 );
 
function bbloomer_remove_product_tabs( $tabs ) {
    unset( $tabs['additional_information'] ); 
    return $tabs;
}
/*remove shortdescription*/
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
/*add custom tab in woo setting*/
add_filter( 'woocommerce_get_sections_products' , 'returnship_add_settings_tab' );
function returnship_add_settings_tab( $settings_tab ){
     $settings_tab['return_shipping_notices'] = __( 'Return&Shipping Notices' );
     return $settings_tab;
}
add_filter( 'woocommerce_get_settings_products' , 'returnship_get_settings' , 10, 2 );
function returnship_get_settings( $settings, $current_section ) {
        $custom_settings = array();
        if( 'return_shipping_notices' == $current_section ) {
        	$custom_settings =  array(
			array(
				'name' => __( 'Return&Shipping Notices' ),
				'type' => 'title',
			    'desc' => __( '全ての商品共通の配送返品について' ),
				'id'   => 'return_shipping' 
			),
			array(
				'name' => __( 'この記載を表示する' ),
				'type' => 'checkbox',
				'desc' => __( '表記の有無'),
				'id'	=> 'enable'
			),
			array(
				'name' => __( '返品について' ),
				'type' => 'textarea',
				'desc' => __( '返品についての概要'),
				'desc_tip' => true,
				'id'	=> 'msg_threshold'
			),
			array(
				'name' => __( 'Position' ),
				'type' => 'select',
				'desc' => __( 'Position of the notice on the product page'),
				'desc_tip' => true,
				'id'	=> 'position',
				'options' => array(
					      'top' => __( 'Top' ),
					      'bottom' => __('Bottom')
				)
			),
			 array( 'type' => 'sectionend', 'id' => 'return_shipping' ),
	);
		return $custom_settings;
     } else {
        	return $settings;
    }
}
/*add custom tab in woo setting2*/
add_filter( 'woocommerce_get_sections_products' , 'notice_add_settings_tab' );
function notice_add_settings_tab( $settings_tab ){
     $settings_tab['common_notices'] = __( 'Notice' );
     return $settings_tab;
}
add_filter( 'woocommerce_get_settings_products' , 'notice_get_settings' , 10, 2 );
function notice_get_settings( $settings, $current_section ) {
        $custom_settings = array();
        if( 'common_notices' == $current_section ) {
        	$custom_settings =  array(
			array(
				'name' => __( 'Return&Shipping Notices' ),
				'type' => 'title',
			    'desc' => __( '全ての商品共通の注意事項について' ),
				'id'   => 'notice_desc' 
			),
			array(
				'name' => __( 'この記載を表示する' ),
				'type' => 'checkbox',
				'desc' => __( '表記の有無'),
				'id'	=> 'enable_notice'
			),
			array(
				'name' => __( '注意事項について' ),
				'type' => 'textarea',
				'desc' => __( '注意事項についての文章'),
				'desc_tip' => true,
				'id'	=> 'msg_threshold_notice'
			),
			array(
				'name' => __( 'Position' ),
				'type' => 'select',
				'desc' => __( 'Position of the notice on the product page'),
				'desc_tip' => true,
				'id'	=> 'position_notice',
				'options' => array(
					      'top' => __( 'Top' ),
					      'bottom' => __('Bottom')
				)
			),
			 array( 'type' => 'sectionend', 'id' => 'notice_desc' ),
	);
		return $custom_settings;
     } else {
        	return $settings;
    }
}
/*change products per a page*/

/*add custom fields to product edit*/
// Display Fields
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');

// Save Fields
add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');


function woocommerce_product_custom_fields()
{
    global $woocommerce, $post;
    echo '<div class="product_custom_field">';
    // Custom Product Text Field
    woocommerce_wp_text_input(
        array(
            'id' => '_custom_product_text_field',
            'placeholder' => '日本語商品名',
            'label' => __('Japanese Name', 'woocommerce'),
            'desc_tip' => 'true'
        )
    );
    echo '</div>';

}
function woocommerce_product_custom_fields_save($post_id)
{
    // Custom Product Text Field
    $woocommerce_custom_product_text_field = $_POST['_custom_product_text_field'];
    if (!empty($woocommerce_custom_product_text_field))
        update_post_meta($post_id, '_custom_product_text_field', esc_attr($woocommerce_custom_product_text_field));
// Custom Product Number Field
    $woocommerce_custom_product_number_field = $_POST['_custom_product_number_field'];
    if (!empty($woocommerce_custom_product_number_field))
        update_post_meta($post_id, '_custom_product_number_field', esc_attr($woocommerce_custom_product_number_field));
// Custom Product Textarea Field
    $woocommerce_custom_procut_textarea = $_POST['_custom_product_textarea'];
    if (!empty($woocommerce_custom_procut_textarea))
        update_post_meta($post_id, '_custom_product_textarea', esc_html($woocommerce_custom_procut_textarea));

}
/*edit minicart buttons*/
remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_button_view_cart', 10 );
remove_action( 'woocommerce_widget_shopping_cart_buttons', 'woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );

function my_woocommerce_widget_shopping_cart_button_view_cart() {
    echo '<a href="' . esc_url( wc_get_cart_url() ) . '" class="button button--primary button--full">' . esc_html__( 'View cart', 'woocommerce' ) . '</a>';
}
function my_woocommerce_widget_shopping_cart_proceed_to_checkout() {
    echo '<div class="align--center order__actions__item"><a href="' . esc_url( wc_get_checkout_url() ) . '" class="button checkout button--link button--full">' . esc_html__( 'Checkout', 'woocommerce' ) . '</a></div>';
}
add_action( 'woocommerce_widget_shopping_cart_buttons', 'my_woocommerce_widget_shopping_cart_button_view_cart', 10 );
add_action( 'woocommerce_widget_shopping_cart_buttons', 'my_woocommerce_widget_shopping_cart_proceed_to_checkout', 20 );

/*show sku in cart/wishlist */
add_filter("woocommerce_in_cartproduct_obj_title", "wdm_test", 10 , 2);

function wdm_test($product_title, $product){
    if(is_a($product, "WC_Product_Variation")){
    	$parent_id = $product->get_parent_id();
    	$parent = get_product($parent_id);
    	$product_test    = get_product($product->variation_id);
    	$product_title = $parent->name;
    	$attributes = $product->get_attributes();
    	
    	$html = '<div class="mini-product__item mini-product__name-en small-text"><a href="'. esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $parent->id ) ) ) . '">' . get_post_meta($product->id, '_custom_product_text_field', true) . '</a></div>' .
    			'<div class="mini-product__item mini-product__name-ja p6">
			<a href="'. esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $parent->id ) ) ) . '">
				'.$product_title . '
			</a>
         </div>';
    	
    	foreach ($attributes as $attribute_key => $attribute_value)
    	{
    		$display_key   = wc_attribute_label( $attribute_key, $product );
    		$display_value = $attribute_value;
    	
    		if ( taxonomy_exists( $attribute_key ) ) {
    			$term = get_term_by( 'slug', $attribute_value, $attribute_key );
    			if ( ! is_wp_error( $term ) && is_object( $term ) && $term->name ) {
    				$display_value = $term->name;
    			}
    		}
    		$html .= '<div class="mini-product__item mini-product__attribute">
						<span class="label variation-color">'. $display_key .':</span>
						<span class="value variation-color">'. $display_value .'</span>
					</div>';
    	}
    	
    	$html .= '<p class="mini-product__item mini-product__id light-copy">商品番号 #' . $product_test->get_sku() . '</p>';
    	return $html;
    }
    elseif( is_a($product, "WC_Product") ){
        $product_test    = new WC_Product($product->id);
        
       return '<div class="mini-product__item mini-product__name-en small-text"><a href="'. esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $product->id ) ) ) . '">' . get_post_meta($product->id, '_custom_product_text_field', true) . '</a></div>' . 
         '<div class="mini-product__item mini-product__name-ja p6">
			<a href="'. esc_url( get_permalink( apply_filters( 'woocommerce_in_cart_product', $product->id ) ) ) . '">
				'.$product_title . '
			</a>
         </div>' .
       	'<p class="mini-product__item mini-product__id light-copy">商品番号 #' . $product_test->get_sku() . '</p>';
    }
    else{
     return $product_title ;
    }
}
//add action give it the name of our function to run
add_action( 'woocommerce_after_shop_loop_item_title', 'wcs_stock_text_shop_page', 25 );

//create our function
function wcs_stock_text_shop_page() {

//returns an array with 2 items availability and class for CSS
global $product;
$availability = $product->get_availability();

//check if availability in the array = string 'Out of Stock'
//if so display on page.//if you want to display the 'in stock' messages as well just leave out this, == 'Out of stock'
if ( $availability['availability'] == 'Out of stock') {
    echo apply_filters( 'woocommerce_stock_html', '<p class="stock ' . esc_attr( $availability['class'] ) . '">' . esc_html( $availability['availability'] ) . '</p>', $availability['availability'] );
}
}
/****** NOT WORKING************/
/*hide stock not working*/
function my_wc_hide_in_stock_message( $html='', $text, $product='' ) {
	
	if($product !=''){
	$availability = $product->get_availability();
	if ( isset( $availability['class'] ) && 'in-stock' === $availability['class'] ) {
		return '';
	}
	}else{
	
	if ('in-stock' != $text ) {
		return '';
	}
	}
	
	return $html;
}
add_filter( 'woocommerce_stock_html', 'my_wc_hide_in_stock_message', 10, 3 );

/*hide add to cart when out of stock not working
if (!function_exists('woocommerce_template_loop_add_to_cart')) {
    function woocommerce_template_loop_add_to_cart() {
        global $product;
        if ( ! $product->is_in_stock() || ! $product->is_purchasable() ) return;
        woocommerce_get_template('loop/add-to-cart.php');
    }
}*/

//webfonts
function webfonts_scripts ()
{
	wp_enqueue_style('smoke_css', get_stylesheet_directory_uri() . '/fonts/fonts.css');
}
add_action('wp_enqueue_scripts', 'webfonts_scripts');


//Validation jquery
function smoke_scripts ()
{
	wp_enqueue_style('smoke_css', get_stylesheet_directory_uri() . '/js/smoke/css/smoke.min.css');
	wp_enqueue_script('smoke_js', get_stylesheet_directory_uri() . '/js/smoke/js/smoke.min.js', array( 'jquery' ),'', true);
	wp_enqueue_script('smoke_lang', get_stylesheet_directory_uri() . '/js/smoke/lang/ja.js', array( 'jquery' ),'', true);
}
add_action('wp_enqueue_scripts', 'smoke_scripts');

/*Jquery*/
function custom_scripts ()
{
	wp_register_script('autokana', get_stylesheet_directory_uri() . '/js/jquery.autoKana.js', array( 'jquery' ),'', true);
	wp_enqueue_script('autokana');
	
	wp_register_script('custom_js', get_stylesheet_directory_uri() . '/js/custom.js', array( 'jquery' ),'', true);
	wp_enqueue_script('custom_js');
	
	wp_dequeue_script( 'sticky-header', ELSEY_SCRIPTS . '/sticky.min.js', array( 'jquery' ), '1.0.4', true );
	wp_enqueue_script('sticky-header', get_stylesheet_directory_uri() . '/js/sticky.min.js', array( 'jquery' ),'', true);
}
add_action('wp_enqueue_scripts', 'custom_scripts');

function hide_plugin_order_by_product ()
{
	global $wp_list_table;
	$hidearr = array(
		'remove-admin-menus-by-role/remove-admin-menus-by-role.php'
	);
	$active_plugins = get_option('active_plugins');

	$myplugins = $wp_list_table->items;
	foreach ( $myplugins as $key => $val )
	{
		if ( in_array($key, $hidearr) && in_array($key, $active_plugins))
		{
			unset($wp_list_table->items[$key]);
		}
	}
}
add_action('pre_current_active_plugins', 'hide_plugin_order_by_product');


add_filter('woocommerce_cart_shipping_method_full_label', 'elsey_woocommerce_cart_shipping_method_full_label', 10, 3);
function elsey_woocommerce_cart_shipping_method_full_label ($label, $method)
{
	if ( $method->cost > 0 ) {
		$label = '<span class="small-text">' . $method->get_label() . '</span>';
		if ( WC()->cart->tax_display_cart == 'excl' ) {
			$label .= ': ' . wc_price( $method->cost );
			if ( $method->get_shipping_tax() > 0 && wc_prices_include_tax() ) {
				$label .= ' <small class="tax_label">' . WC()->countries->ex_tax_or_vat() . '</small>';
			}
		} else {
			$label .= ': ' . wc_price( $method->cost + $method->get_shipping_tax() );
			if ( $method->get_shipping_tax() > 0 && ! wc_prices_include_tax() ) {
				$label .= ' <small class="tax_label">' . WC()->countries->inc_tax_or_vat() . '</small>';
			}
		}
	}
	else {
		$label = '<span class="small-text free_shipping">' . __('Free Shipping', 'elsey') . '</span>';
	}
	return $label;
}

add_filter( 'the_title', 'woo_title_order_received', 10, 2 );
function woo_title_order_received( $title, $id ) {
	if ( function_exists( 'is_order_received_page' ) &&
			is_order_received_page() && get_the_ID() === $id ) {
				$title = "ご注文完了";
			}
			return $title;
}

function wc_cart_totals_order_total_html1() {
	
	$value = '<strong>'. WC()->cart->get_total(). '</strong>';
	echo $value;
}
remove_all_filters('woocommerce_cart_totals_order_total_html');
add_filter( 'woocommerce_cart_totals_order_total_html', 'wc_cart_totals_order_total_html1' );


add_action('init', 'elsey_init', 1);
function elsey_init() {
	remove_shortcode('elsey_product');
	require_once get_stylesheet_directory() . '/override/plugins/elsey-core/visual-composer/shortcodes/product/product.php';
}

add_filter( 'woocommerce_email_order_meta_fields', 'elsey_woocommerce_email_order_meta_fields', 1000, 3 );
add_filter( 'woocommerce_email_order_meta_keys', 'elsey_woocommerce_email_order_meta_keys', 1000, 3 );
function elsey_woocommerce_email_order_meta_fields($fields, $sent_to_admin, $order){
	$fields = array();
	return $fields;
}


add_filter( 'cs_framework_settings', 'elsey_cs_framework_settings', 100, 1 );
function elsey_cs_framework_settings ($settings)
{
	$settings['ajax_save'] = true;
	return $settings;
}

add_filter('woocommerce_cart_item_name', 'elsey_woocommerce_cart_item_name', 10, 3);
add_filter('woocommerce_order_item_name', 'elsey_woocommerce_cart_item_name', 10, 3);
function elsey_woocommerce_cart_item_name ($product_name, $cart_item, $cart_item_key)
{
	$product = get_product($cart_item['product_id']);
	
	if ($cart_item['variation_id'])
	{
		$variation = get_product($cart_item['variation_id']);
		$product_link = $variation->get_permalink( $cart_item );
		$product_name = $product->name;
	}
	else {
		$product_link = $product->get_permalink( $cart_item );
		$product_name = $product->name;
	}
	
	return sprintf( '<a href="%s">%s</a>', $product_link, $product_name );
}

function my_formatted_billing_adress($ord) {
	$address = apply_filters('woocommerce_order_formatted_billing_address', array(
		'first_name' => $ord->billing_first_name,
		'last_name' => $ord->billing_last_name,
		'kana_first_name' => $ord->billing_first_name_kana,
		'kana_last_name' => $ord->billing_last_name_kana,
		'company' => $ord->billing_company,
		'address_1' => $ord->billing_address_1,
		'address_2' => $ord->billing_address_2,
		'city' => $ord->billing_city,
		'state' => $ord->billing_state,
		'postcode' => $ord->billing_postcode,
		'country' => $ord->billing_country
	), $ord);

	$add = WC()->countries->get_formatted_address($address);
	return $add;
}

function my_formatted_shipping_adress($ord) {
	if ($ord->shipping_address_1 || $ord->shipping_address_2) {

		// Formatted Addresses
		$address = apply_filters('woocommerce_order_formatted_shipping_address', array(
			'first_name' => $ord->shipping_first_name,
			'last_name' => $ord->shipping_last_name,
			'kana_first_name' => $ord->shipping_first_name_kana,
			'kana_last_name' => $ord->shipping_last_name_kana,
			'company' => $ord->shipping_company,
			'address_1' => $ord->shipping_address_1,
			'address_2' => $ord->shipping_address_2,
			'city' => $ord->shipping_city,
			'state' => $ord->shipping_state,
			'postcode' => $ord->shipping_postcode,
			'country' => $ord->shipping_country
		), $ord);

		$add = WC()->countries->get_formatted_address($address);
	}
	return $add;
}

function insertAtSpecificIndex($array = [], $item = [], $position = 0) {
	$previous_items = array_slice($array, 0, $position, true);
	$next_items     = array_slice($array, $position, NULL, true);
	return $previous_items + $item + $next_items;
}

// Begin - customize order detail ADMIN
add_filter( 'woocommerce_admin_billing_fields', 'look_woocommerce_admin_extra_fields', 10, 1 );
add_filter( 'woocommerce_admin_shipping_fields', 'look_woocommerce_admin_extra_fields', 10, 1 );
function look_woocommerce_admin_extra_fields($fields){
	$fieldExtras['last_name_kana'] = array(
		'label' => __( '姓(ふりがな)', 'woocommerce' ),
		'show'  => false
	);
	
	$fieldExtras['first_name_kana'] = array(
		'label' => __( '名(ふりがな)', 'woocommerce' ),
		'show'  => false
	);
	

	$fields = insertAtSpecificIndex($fields, $fieldExtras, array_search('last_name', array_keys($fields)) + 1);

	return $fields;
}

add_filter('woocommerce_localisation_address_formats', 'elsey_woocommerce_localisation_address_formats', 1000);
function elsey_woocommerce_localisation_address_formats($formats) {
	if(is_admin())
	{
		$format_string = "{last_name} {first_name}\n{kananame}\n{company}\n{country}\n〒{postcode}\n{state}\n{city}\n{address_1}\n{address_2}";
		
	}
	else {
		$format_string = "<span class='readonly-address__item'>{last_name} {first_name} ({kananame})</span><span class='readonly-address__item'>{company}</span><br><span class='readonly-address__item'>{country}</span><span class='readonly-address__item'>〒{postcode}</span><span class='readonly-address__item'>{state}{city}{address_1}</span><span class='readonly-address__item'>{address_2}</span>";
	}
	$formats['JP'] = $formats['default'] = $format_string;
	return $formats;
}

add_filter( 'woocommerce_formatted_address_replacements', 'look_woocommerce_formatted_address_replacements', 10000, 2);
function look_woocommerce_formatted_address_replacements ($fields, $args)
{
	$fields['{kananame}'] = $args['kananame'];
	return $fields;
}

add_filter( 'woocommerce_order_formatted_billing_address', 'look_woocommerce_order_formatted_billing_address', 10000, 2);
function look_woocommerce_order_formatted_billing_address ($args, $order)
{
	$args['kananame'] = $order->billing_last_name_kana . $order->billing_first_name_kana;
	$args['country'] = $args['country'] ? : 'JP';
	return $args;
}

add_filter( 'woe_fetch_order_row', 'elsey_woe_fetch_order_row', 10000, 2);
function elsey_woe_fetch_order_row ($row, $order_id)
{
	foreach($row as $key => $field)
	{
		if (strpos($key, '_country') !== false)
		{
			$row[$key] = WC()->countries->countries[ 'JP' ];
		}
		elseif (strpos($key, '_state') !== false)
		{
			$states = WC()->countries->get_states( 'JP' );
			$row[$key] = $states[$field];
		}
	}
	return $row;
}

add_filter( 'woocommerce_order_formatted_shipping_address', 'look_woocommerce_order_formatted_shipping_address', 10000, 2);
function look_woocommerce_order_formatted_shipping_address ($args, $order)
{
	$args['kananame'] = $order->shipping_last_name_kana . $order->shipping_first_name_kana;
	$args['country'] = $args['country'] ? : 'JP';
	return $args;
}

add_filter('woocommerce_customer_meta_fields', 'look_woocommerce_customer_meta_fields', 1000, 1);
function look_woocommerce_customer_meta_fields ($fields) {
	$extraBilling['billing_last_name_kana']['label'] = __( '姓(ふりがな)', 'woocommerce' );
	$extraBilling['billing_first_name_kana']['label'] = __( '名(ふりがな)', 'woocommerce' );

	$extraShipping['shipping_last_name_kana']['label'] = __( '姓(ふりがな)', 'woocommerce' );
	$extraShipping['shipping_first_name_kana']['label'] = __( '名(ふりがな)', 'woocommerce' );

	$fields['billing']['fields'] = insertAtSpecificIndex($fields['billing']['fields'], $extraBilling, array_search('billing_last_name', array_keys($fields['billing']['fields'])) + 1);
	$fields['shipping']['fields'] = insertAtSpecificIndex($fields['shipping']['fields'], $extraShipping, array_search('shipping_last_name', array_keys($fields['shipping']['fields'])) + 1);
	return $fields;
}
// Rename My Account navigation
function wpb_woo_my_account_order() {
	$myorder = array(
		//'my-custom-endpoint' => __( 'My Stuff', 'woocommerce' ),
		'edit-account'       => __( 'Member Information', 'elsey' ),
		//'dashboard'          => __( 'Dashboard', 'woocommerce' ),
		'orders'             => __( 'Order history', 'elsey' ),
		'favorite-list'      => __( 'Favorite items', 'elsey' ),
		'waitlist'           => __( 'Waitlist items', 'elsey' ),
		'edit-address'       => __( 'Addresses', 'woocommerce' ),
		'payment-methods'    => __( 'Credit Card Information', 'elsey' ),
		'customer-logout'    => __( 'Logout', 'woocommerce' ),
	);
	return $myorder;
}
add_filter ( 'woocommerce_account_menu_items', 'wpb_woo_my_account_order' );
// END - customize order detail ADMIN

add_filter( 'woocommerce_my_account_my_address_formatted_address', 'look_woocommerce_my_account_my_address_formatted_address', 10000, 3);
function look_woocommerce_my_account_my_address_formatted_address($fields, $customer_id, $name) {
	$last_name_kana = get_user_meta( $customer_id, $name . '_last_name_kana', true );
	$first_name_kana = get_user_meta( $customer_id, $name . '_first_name_kana', true );
	$fields['kananame'] = $last_name_kana . $first_name_kana;
	return $fields;
}



//add fav content to my account
function carome_add_list_endpoint() {
    add_rewrite_endpoint( 'favorite-list', EP_ROOT | EP_PAGES );
    add_rewrite_endpoint( 'waitlist', EP_ROOT | EP_PAGES );
}
add_action( 'init', 'carome_add_list_endpoint' );

// ------------------
// 2. Add new query var
 
function carome_list_query_vars( $vars ) {
	if (!in_array('favorite-list', $vars))
	{
		$vars[] = 'favorite-list';
	}
	if (!in_array('waitlist', $vars))
	{
		$vars[] = 'waitlist';
	}
	
    return $vars;
}
add_filter( 'query_vars', 'carome_list_query_vars', 0 );
 
// ------------------
// 3. Insert the new endpoint into the My Account menu
 
/*function carome_add_fav_list_link_my_account( $items ) {
    $items['favorite-list'] = __('お気に入りアイテム', 'elsey');
	$items['waitlist'] = __('再入荷待ちアイテム', 'elsey');
    return $items;
}
add_filter( 'woocommerce_account_menu_items', 'carome_add_fav_list_link_my_account' );*/
 
 
// ------------------
// 4. Add content to the new endpoint
 
function carome_fav_list_content() {
echo '<h1 class="account__heading heading heading--xlarge serif">お気に入りアイテム</h1>';
echo do_shortcode( ' [yith_wcwl_wishlist] ' );
}
add_action( 'woocommerce_account_favorite-list_endpoint', 'carome_fav_list_content' );
function carome_wait_list_content() {
echo '<h1 class="account__heading heading heading--xlarge serif">再入荷待ちアイテム</h1>';
echo do_shortcode( ' [woocommerce_my_waitlist] ' );
}
add_action( 'woocommerce_account_waitlist_endpoint', 'carome_wait_list_content' );

add_action( 'woocommerce_save_account_details', 'woocommerce_save_account_details_custom' );
function woocommerce_save_account_details_custom ($userID)
{
	update_user_meta($userID, 'first_name_kana', $_POST['account_first_name_kana']);
	update_user_meta($userID, 'last_name_kana', $_POST['account_last_name_kana']);
}

add_action( 'woocommerce_save_account_details_required_fields', 'carome_woocommerce_save_account_details_required_fields' );
function carome_woocommerce_save_account_details_required_fields ($required_fields)
{
	$required_fields['account_first_name_kana'] = __( 'First Name Kana', 'woocommerce' );
	$required_fields['account_last_name_kana'] = __( 'Last Name Kana', 'woocommerce' );
	return $required_fields;
}

add_action( 'woocommerce_checkout_update_order_meta', 'elsey_custom_checkout_field_update_order_meta' );
function elsey_custom_checkout_field_update_order_meta( $order_id )
{
	$userID = get_current_user_id();
	if (!get_user_meta($user_id, 'first_name_kana', true))
	{	
		update_user_meta($userID, 'first_name_kana', $_POST['billing_first_name_kana']);
	}
	if (!get_user_meta($user_id, 'last_name_kana', true))
	{
		update_user_meta($userID, 'last_name_kana', $_POST['billing_last_name_kana']);
	}
}

add_filter('woocommerce_countries_base_state', 'elsey_woocommerce_countries_base_state');
function elsey_woocommerce_countries_base_state()
{
	return '';
}

add_action( 'wcwl_after_remove_user_from_waitlist', 'elsey_wcwl_after_remove_user_from_waitlist', 10, 2);
function elsey_wcwl_after_remove_user_from_waitlist ($product_id, $user )
{
	$user_id = $user->ID;
	$waitlist_user = get_user_meta($user_id, woocommerce_waitlist_user, true);
	$waitlist_user = $waitlist_user ? $waitlist_user : array();
	
	if (isset($waitlist_user) && isset($waitlist_user[$product_id])){
		unset($waitlist_user[$product_id]);
	}
	update_user_meta($user_id, 'woocommerce_waitlist_user', $waitlist_user);
}

add_action( 'wcwl_after_add_user_to_waitlist', 'elsey_wcwl_after_add_user_to_waitlist', 10, 2);
function elsey_wcwl_after_add_user_to_waitlist ($product_id, $user )
{
	$user_id = $user->ID;
	$waitlist_user = get_user_meta($user_id, woocommerce_waitlist_user, true);
	$waitlist_user = $waitlist_user ? $waitlist_user : array();
	if (!isset($waitlist_user) || !isset($waitlist_user[$product_id])){
		$waitlist_user[$product_id] = $product_id;
	}
	update_user_meta($user_id, 'woocommerce_waitlist_user', $waitlist_user);
}

function elsey_waitlist_user( $atts ) {
	ob_start();
	include( locate_template( 'waitlist-user.php' ) );
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}
add_shortcode( 'waitlist_user', 'elsey_waitlist_user' );

add_filter('woocommerce_waitlist_supported_products', 'elsey_woocommerce_waitlist_supported_products', 1000, 1);
function elsey_woocommerce_waitlist_supported_products ($classes) {
	if ($_REQUEST['waitlist'] == 1)
	{
		global $post;
		$product_id = woocommerce_waitlist;
		$post = get_product($product_id);
	}
	return $classes;
}

add_filter('woocommerce_is_attribute_in_product_name', 'elsey_woocommerce_is_attribute_in_product_name', 1000, 3);
function elsey_woocommerce_is_attribute_in_product_name ( $is_in_name, $attribute, $name )
{
	if ($is_in_name && $attribute)
	{
		return false;
	}
	return $is_in_name;
}

add_filter('woocommerce_cart_item_product', 'elsey_woocommerce_cart_item_product', 1000, 3);
add_filter('woocommerce_order_item_product', 'elsey_woocommerce_cart_item_product', 1000, 3);
function elsey_woocommerce_cart_item_product ( $product, $cart_item = '', $cart_item_key = '')
{
	if (is_a($product, 'WC_Product_Variation'))
	{
		$parent = $product->get_parent_data();
		$product->set_name($parent['title']);
		$product->apply_changes();
	}
	return $product;
}

add_filter('woocommerce_display_item_meta', 'elsey_woocommerce_display_item_meta', 1000, 3);
function elsey_woocommerce_display_item_meta ( $html, $item, $args)
{
	$html = '';
	$strings = array();
	foreach ( $item->get_formatted_meta_data() as $meta_id => $meta ) {
		$strings[] = '<div class="mini-product__item mini-product__attribute">
						<span class="label variation-color">' . wp_kses_post( $meta->display_key ) . ':</span> 
					 	<span class="value variation-color">'. strip_tags($meta->display_value) .'</span>
					</div>';
	}
	
	$html = implode( '', $strings );
	return $html;
}

function pr ($data)
{
	echo '<pre>'; print_r($data); echo '</pre>';
}

function elsey_title_area_custom(){
	if (is_shop())
	{
		echo __( 'CAROME SHOP', 'elsey' );
	}
	else {
		echo elsey_title_area();
	}
}
// change BACS fields
//original fields from plugins/woocommerce/includes/gateways/bacs/class-wc-gateway-bacs.php
add_filter('woocommerce_bacs_account_fields','custom_bacs_fields');

function custom_bacs_fields() {
	global $wpdb;
	$account_details = get_option( 'woocommerce_bacs_accounts',
				array(
					array(
						'account_name'   => get_option( 'account_name' ),
						'account_number' => get_option( 'account_number' ),
						'sort_code'      => get_option( 'sort_code' ),
						'bank_name'      => get_option( 'bank_name' ),
						'iban'           => get_option( 'iban' ),
						'bic'            => get_option( 'bic' )
					)
				)

			);
	$account_fields = array(
		'bank_name'      => array(
			'label' => '金融機関',
			'value' => $account_details[0]['bank_name']
		),
		'account_number' => array(
			'label' => __( '口座番号', 'woocommerce' ),
			'value' => $account_details[0]['sort_code'].' '.$account_details[0]['account_number']
		),
		'bic'            => array(
			'label' => __( '支店', 'woocommerce' ),
			'value' => $account_details[0]['iban'].'('.$account_details[0]['bic'].')'
		),
		'account_name'   => array(
			'label' => '口座名義',
			'value' => $account_details[0]['account_name']
		)
	);

	return $account_fields;
}

function show_epsilon_method() {
	$html = '';
	if (class_exists('WC_Epsilon'))
	{
		ob_start();
		$user = wp_get_current_user();
		$removed_epsilon = get_user_meta($user->ID, 'epsilon_cc_removed', true);
		
		// Stop showing if the method removed
		if ($removed_epsilon) return '';
		
		$epsilon = new WC_Epsilon();
		$customer_check = $epsilon->user_has_stored_data( $user->ID );
		if ( $customer_check['err_code']!=801 && $customer_check['result']==1) {
		?>
			<!--Start saved credit card-->
			<div class="payment-list row">
				<div class="col-xs-12 col-lg-6 first Visa">
					<div class="box box--rounded">
<!-- 						<span class="ccdetails__section cc-owner">Holder Name</span> -->
						<div class="ccdetails__section cc-info">
							<span class="cc-type"><?php echo $customer_check['card_bland']; ?></span>
							<span class="cc-number">************<?php echo $customer_check['card_number_mask'];?></span>
							<br>
							<div class="cc-exp"><?php esc_html_e( 'Expiry date', 'elsey' ); ?>: <?php echo $customer_check['card_expire']; ?></div>
						</div>
						<a class="cta cta--underlined txt--upper delete" id="remove_cc_card" data-message="<?php echo __('Are you sure you want delete ?', 'elsey') ?>" href="javascript:void(0)"><?php esc_html_e( 'Delete Card', 'elsey' ); ?></a>
					</div>
				</div>
			</div>
			<!--End saved credit card-->
		<?php
		}
		$html = ob_get_contents();
		ob_end_clean();
	}
	return $html;
}

add_action( 'wp_ajax_nopriv_removed_epsilon_method', 'removed_epsilon_method' );
add_action( 'wp_ajax_removed_epsilon_method', 'removed_epsilon_method' );

function removed_epsilon_method() {
	$response = array('success' => 1);
	$user = wp_get_current_user();
	update_user_meta($user->ID, 'epsilon_cc_removed', 1);
	echo json_encode($response); die;
}

add_filter( 'document_title_parts', 'elsey_document_title_parts', 10000, 1 );
function elsey_document_title_parts( $title ) {
	if (is_wc_endpoint_url( 'order-received' ))
	{
		$title['title'] = 'ご注文完了';
	}
	return $title;
}


add_filter( 'bulk_actions-edit-shop_order', 'elsey_shop_order_bulk_actions', 1000, 1 );
function elsey_shop_order_bulk_actions($actions)
{
	$actions['mark_cancelled'] = __('Mark cancelled', 'elsey');
	return $actions;
}

add_filter('woocommerce_create_account_default_checked', 'elsey_woocommerce_create_account_default_checked', 1000);
function elsey_woocommerce_create_account_default_checked(){
	return true;
}

add_action( 'restrict_manage_posts', 'elsey_restrict_manage_posts', 50  );
// Display dropdown
function elsey_restrict_manage_posts(){
	global $typenow;
	if ( 'shop_order' != $typenow ) {
		return;
	}
	?>
		<style>
			.select2-container {margin-top: 0 !important;}
		</style>
	    <span id="woe_order_exported_wrap">
		    <select name="woe_order_exported" id="woe_order_exported">
		    	<option value=""><?php _e('Choose Order Export', 'elsey'); ?></option>
		    	<option value="0" <?php echo (isset($_REQUEST['woe_order_exported']) && $_REQUEST['woe_order_exported'] !== "") ? 'selected' : '';?>><?php _e('Orders Not Exported', 'elsey'); ?></option>
		    	<option value="<?php echo 1?>" <?php echo (isset($_REQUEST['woe_order_exported']) && $_REQUEST['woe_order_exported'] == 1) ? 'selected' : '';?>><?php _e('Orders Exported', 'elsey'); ?></option>
		    </select>
		</span>
	    <?php
}

add_filter( 'parse_query', 'else_parse_query' ); 
function else_parse_query ( $query )
{
	global $pagenow;
	if ( 'shop_order' == $_GET['post_type'] && is_admin() && $pagenow == 'edit.php' && isset($_GET['woe_order_exported']) && $_GET['woe_order_exported'] !== '' )
	{
		$query->query_vars['meta_query'] = isset($query->query_vars['meta_query']) ? $query->query_vars['meta_query'] : array();
		$query->query_vars['meta_query'][] = array(
			'key' => woe_order_exported,
			'value' => 1,
			'compare' => $_GET['woe_order_exported'] ? '=' : 'NOT EXISTS'
		);
	}
	return $query;
}

add_action('woocommerce_thankyou_bacs', 'elsey_woocommerce_thankyou_bacs', 1);
function elsey_woocommerce_thankyou_bacs() 
{
	echo '<div class="before_bacs">' . __('ご注文の確定はご入金確認後となり、<strong>ご注文日から3営業日以内にご入金が確認できない場合はキャンセルとなります</strong>のであらかじめご了承ください。', 'elsey') . '</div>';
}

add_action( 'woe_order_exported', 'elsey_woe_order_exported', 1000, 2 );
function elsey_woe_order_exported($order_id){
	if (class_exists('WC_Order_Export_Manage'))
	{
		$order = new WC_Order( $order_id );
		$settings = WC_Order_Export_Manage::make_new_settings( $_POST );
		if ( $settings[ 'mark_exported_orders' ] && $order->status == 'processing') {
			// Set new status
			$order->update_status('wc-process-deliver', 'mark exported for processing order'); 
		}
	}
}
add_action( 'woocommerce_email_before_order_table', 'add_order_email_instructions', 10, 2 );
 
function add_order_email_instructions( $order, $sent_to_admin ) {
  
  if ( ! $sent_to_admin ) {
 
    if ( ('bacs' == $order->payment_method) && ($order->status == 'processing') ) {
      // cash on delivery method
      echo '<p>お客様のご注文のご入金を確認いたしましたので、お知らせ致します。<br/>お忙しいなか、お手続きをありがとうございました。</p><p>発送手続き完了後、「商品発送のご案内」メールを再度配信いたしますので、<br/>発送完了までもうしばらくお待ちください。</p><p>お客様のご注文内容は以下となりますので、ご確認ください。</p>';
    } elseif (('epsilon' == $order->payment_method) && ($order->status == 'processing')) {
      echo '<p>お客様のご注文を下記の内容で承りましたので、ご確認ください。</p><p>発送手続き完了後、「商品発送のご案内」メールを再度配信いたしますので、<br/>発送完了までもうしばらくお待ちください。</p>';
    } elseif (('epsilon_pro_sc' == $order->payment_method) && ($order->status == 'processing')) {
      echo '<p>お客様のご注文を下記の内容で承りましたので、ご確認ください。</p><p>発送手続き完了後、「商品発送のご案内」メールを再度配信いたしますので、<br/>発送完了までもうしばらくお待ちください。</p>';
    } else {
      // other methods (ie credit card)
      echo '';
    }
  }
}
/*add_filter('woocommerce_variation_option_name', 'get_text_for_select_based_on_attribute');
function get_text_for_select_based_on_attribute($atr) {
  $count=0;
  // var_dump($atr);
  global $product;
  if($product){
     foreach ($product->get_available_variations() as $variation){
             $var=wc_get_product($variation['variation_id']);
             $var_name=str_replace($product->get_title().' - ','',$var->get_name());
             // var_dump($var_name);
             // echo "<br>";
             // var_dump($atr);
            
             if(($var->get_stock_status()=='outofstock')&&($var_name==$atr)){
                  return $atr.' (SOLD OUT)';
             }
          
    // var_dump( $var->get_title().$var->get_stock_status().$var->get_name().$atr);
    }
    return $atr;
  }
  
}*/







// Display Fields
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');

// Save Fields
add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');


function woocommerce_product_custom_fields()
{
    global $woocommerce, $post;
    echo '<div class="product_custom_field">';
    // Custom Product Text Field
    woocommerce_wp_text_input(
        array(
            'id' => '_custom_product_text_field',
            'placeholder' => 'Custom Product Text Field',
            'label' => __('Custom Product Text Field', 'woocommerce'),
            'desc_tip' => 'true'
        )
    );    
    echo '</div>';

}


function woocommerce_product_custom_fields_save($post_id)
{
    // Custom Product Text Field
    $woocommerce_custom_product_text_field = $_POST['_custom_product_text_field'];
    if (!empty($woocommerce_custom_product_text_field))
        update_post_meta($post_id, '_custom_product_text_field', esc_attr($woocommerce_custom_product_text_field));

}







