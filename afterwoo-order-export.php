<?php

/*
Plugin Name:  AfterWoo - Order Export to Afterbuy
Plugin URI:   https://developer.wordpress.org/plugins/my-plugin
Description:  Export orders from WooCommerce to the ERP "Afterbuy"
Version:      1.0
Author:       Kubilay Tunca
License:      GPLv2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  afterwoo-order-export-to-afterbuy
*/


define('AFTERWOO_OE_PLUGIN_DIR',plugin_dir_path(__FILE__));

include( AFTERWOO_OE_PLUGIN_DIR . '/afterwoo_convert-country-iso-code.php');
include( AFTERWOO_OE_PLUGIN_DIR . '/afterwoo-settings.php' );


//error handling function
function awoo_admin_notice__error() {
	$class = 'notice notice-error is-dismissible';
	$message = __( 'Afterbuy: An error has occurred while trying to export your last Order.', 'afterwoo-order-export-to-afterbuy' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

//for development try the order completed hook and for production use the woocommerce_thankyou hook
add_action('woocommerce_order_status_completed', 'awoo_send_order_to_ext'); 




function awoo_send_order_to_ext( $order_id ){
    
    
    // ############ SETTINGS ################
    $options = get_option('awoo_setup_option');
    
    // PartnerID
    $PartnerID = $options['awoo_partner_id'];

    // Your PASSWORD for your PartnerID
    $PartnerPass = $options['awoo_partner_password'];

    // Your Afterbuy USERNAME
    $UserID = $options['awoo_user_id'];
        
    // Your shop email for Info on success of transmission
    //$INFO_EMAIL_SHOP = $options['awoo_text_field_4'];

    // ######################################
    
    
    
    // get order object and order details
    $order = wc_get_order( $order_id ); 
    $order_data = $order->get_data();
    $user_id = $order->get_user_id();
    $order_id = $order_data['id'];
    $shipmethod = $order->get_shipping_method();
    
     $data = array(
            'Action' => 'new',
            'PartnerID' => $PartnerID,
            'PartnerPass' => $PartnerPass,
            'UserID' => $UserID,
            'Kbenutzername' => $order_data['billing']['email']."_WOO_".$order_id,
            'BuyDate' => $order_data['date_created']->date('d-m-Y H:i:s'),
            'KFirma' => $order_data['billing']['company'],
            'KVorname' => $order_data['billing']['first_name'],
            'KNachname' => $order_data['billing']['last_name'],
            'KStrasse' => $order_data['billing']['address_1'],
            'KStrasse2' => $order_data['billing']['address_2'],
            'KPLZ' => $order_data['billing']['postcode'],
            'KOrt' => $order_data['billing']['city'],
            'KBundesland' => $order_data['billing']['state'],
            'Ktelefon' => $order_data['billing']['phone'],
            'KLFirma' => $order_data['shipping']['company'], 
            'KLVorname' => $order_data['shipping']['first_name'],
            'KLNachname' => $order_data['shipping']['last_name'],
            'KLStrasse' => $order_data['shipping']['address_1'],
            'KLStrasse2' => $order_data['shipping']['address_2'],
            'KLPLZ' => $order_data['shipping']['postcode'],
            'KLOrt' => $order_data['shipping']['city'],
            'Versandart' => $shipmethod,
            'NoVersandCalc' => 1,
            'Versandkosten' => preg_replace('/\./', ',', ($order_data['shipping_total'] + $order_data['shipping_tax'])),
            'PaymentTransactionId' => $transaction_key,
            'Zahlart' => $order_data['payment_method_title'],
            'SetPay' => 1,
            'Kundenerkennung' => 1,
            'VID' => $order_id,
            'Haendler' => 0,
            'Artikelerkennung' => 1,
            'NoFeedback' => 0,
            'SoldCurrency' => $order_data['currency'],
            'UseComplWeight' => 0
        );
    
    
    //shipping checkbox not optional
    if( empty($order_data['shipping']['last_name'] || 
              $order_data['shipping']['address_1'] || 
              $order_data['shipping']['postcode'] || 
              $order_data['shipping']['city'] || 
              awoo_convert_country_code(get_post_meta( $order_id, '_shipping_country', true ))) === true ){
        
        $data['Lieferanschrift'] = 0;
        
    } else{
        $data['Lieferanschrift'] = 1;
    }
    
    //email is not optional
    if(empty($order_data['billing']['email']) === true){
        $data['Kemail'] = 'keine Angabe';
    } else {
        $data['Kemail'] = $order_data['billing']['email'];
    }
    
    
    // counter
    $pos_counter=0;
    $nr = 1;
        
    // Iterating through each WC_Order_Item objects
foreach( $order-> get_items() as $item_key => $item_values ):

    ## Using WC_Order_Item methods ##

    // Item ID is directly accessible from the $item_key in the foreach loop or
    $item_id = $item_values->get_id();

    $item_name = $item_values->get_name(); // Name of the product
    $item_type = $item_values->get_type(); // Type of the order item ("line_item")
    
    //$item_weight = $item_values->get_weight();
   $item_link = get_permalink($item_id);

    ## Access Order Items data properties (in an array of values) ##
    $item_data = $item_values->get_data();
    
    //Getting the info
   	$product_name = $item_data['name'];
    $product_id = $item_data['product_id'];
    
    $variation_id = $item_data['variation_id'];
    $quantity = $item_data['quantity'];
    $tax_class = $item_data['tax_class'];
    $line_subtotal = $item_data['subtotal'];
    $line_subtotal_tax = $item_data['subtotal_tax'];
    $line_total = $item_data['total'];
    $line_total_tax = $item_data['total_tax'];
    
	
	//Get product by supplying variation id or product_id
	  $tax = new WC_Tax();
      $wc_product = get_product( $item_data['variation_id'] ? $item_data['variation_id'] : $item_data['product_id'] );
      //Get rates of the product
      $taxes = $tax->get_rates($wc_product->get_tax_class());
      $rates = array_shift($taxes);
      //Take only the item rate and round it. 
      $item_rate = round(array_shift($rates));
	
    //adding the items to the array
        $data['Artikelnr_'.$nr] = $product_id;
        $data['Artikelname_'.$nr] = $product_name;
	    $data['ArtikelEpreis_'.$nr] = preg_replace('/\./', ',', ($line_subtotal + $line_subtotal_tax));
	    $data['ArtikelMwSt_'.$nr] = preg_replace('/\./', ',', $item_rate);
	    $data['ArtikelMenge_'.$nr] = $quantity;
       //$data['ArtikelGewicht_'.$nr] = $item_weight;
        $data['ArtikelLink_'.$nr] = $item_link;
    
        //handle variations
        $product = new WC_Product_Variable( $product_id );
		$variations = $product->get_available_variations();
		$var_data = [];
		foreach ($variations as $variation) {
			if($variation['variation_id'] == $variation_id){
				$var_data[] = $variation['attributes'];
	}
}
	$att_counter=0;
    
	foreach($var_data[0] as $attrName => $var_name) {
		if($att_counter == 0){
		$data['Attribute_'.$nr] = str_replace('attribute_','',$attrName) . ':' .$var_name;
		} 
		if($att_counter > 0){
			$data['Attribute_'.$nr] .= '|'. str_replace('attribute_','',$attrName) . ':' .$var_name;
		}
		$att_counter++;
}
    
    
    
    $nr++;
    $pos_counter++;
    
endforeach;
	
    $data['PosAnz'] = $pos_counter;
    
    //woocommerce uses 2-letter iso code but afterbuy needs license-plate format  
    $data['KLand'] = awoo_convert_country_code(get_post_meta( $order_id, '_billing_country', true ));
    $data['KLLand'] = awoo_convert_country_code(get_post_meta( $order_id, '_shipping_country', true ));
        
    

    /* for online payments, send across the transaction ID/key. If the payment is handled offline, you could send across the order key instead */
    $transaction_key = get_post_meta( $order_id, '_transaction_id', true );
    $transaction_key = empty($transaction_key) ? $_GET['key'] : $transaction_key;   
    
    $endpoint = 'https://api.afterbuy.de/afterbuy/ShopInterface.aspx'; 
   

$response = wp_remote_post( $endpoint, array(
	   'method' => 'POST',
	   'timeout' => 45,
	   'redirection' => 5,
	   'httpversion' => '1.0',
	   'blocking' => true,
       'sslverify' => false,
	   'body' => $data
        )
    );
    
    if ( is_wp_error( $response ) ) {
            //$error_message = $response->get_error_message();
            add_action( 'admin_notices', 'awoo_admin_notice__error' );
        } 
}