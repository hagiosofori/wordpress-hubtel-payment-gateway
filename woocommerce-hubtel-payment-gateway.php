<?php

/**
 * Plugin Name: WooCommerce Hubtel Payment Gateway
 * Plugin URI: http://yourepicdevsite.com/woocommerce-hubtel-payment-gateway/
 * Description: Hubtel wordpress extension for woocommerce, built by Crowncity Technologies.
 * Version: 1.0
 * Author: Alvin Ofori
 * Author URI: http://yourepicdevsite.com/
 * Text Domain: woocommerce-hubtel-payment-gateway
 * 
 * WC requires at least: 2.2
 * WC tested up to: 2.3
 * 
*/



if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // Put your plugin code here
    function add_hubtel_gateway_class( $methods ) {
        $methods[] = 'Hubtel_Payment_Gateway'; 
        return $methods;
    }
    
    add_filter( 'woocommerce_payment_gateways', 'add_hubtel_gateway_class' );
    
    add_action( 'plugins_loaded', 'init_hubtel_payment_gateway_class' );
    
    function init_hubtel_payment_gateway_class(){
        class Hubtel_Payment_Gateway extends WC_Payment_Gateway{
            
            function __construct(){
                $this->init_form_fields();
                $this->id = 'hubtel';
                $this->icon = "http://starrfmonline.com/wp-content/uploads/2017/05/4a_Uw7r.jpg"; 
                $this->has_fields = false;
                $this->method_title = "Hubtel Checkout";
                $this->method_description = "Wordpress plugin for Hubtel payment";
                $this->init_settings();
    
                //Define user set variables
                $this->title = $this->settings['title'];
                $this->description = $this->settings['description'];
                $this->client_id = $this->settings['client_id'];
                $this->client_secret = $this->settings['client_secret'];
                $this->store_tagline = $this->settings['store_tagline'];
                $this->store_name = $this->settings['store_name'];
                $this->store_phone = $this->settings['store_phone'];
                $this->store_website_url = $this->settings['store_website_url'];
        
                add_action('woocommerce_update_options_payment_gateways'.$this->id, array($this, 'process_admin_options'));
            }
        
    
            
    
        
            /**
         * Initialise Gateway Settings Form Fields
         */
            function init_form_fields() {
                $this->form_fields = array(
                    'title' => array(
                        'title' => __( 'Title', 'woocommerce' ),
                        'type' => 'text',
                        'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
                        'default' => __( 'Hubtel', 'woocommerce' )
                    ),
                    'description' => array(
                        'title' => __( 'Description', 'woocommerce' ),
                        'type' => 'textarea',
                        'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
                        'default' => __("Pay via any mobile money service; MTN, Airtel, Tigo, Vodafone.", 'woocommerce')
                    ),
                    'client_id' => array(
                        'title' => _( 'Cliend id', 'woocommerce' ),
                        'type' => 'text',
                        'description' => _( 'This is the client secret that comes with creating an application on Hubtel. Don\'t have an application yet? Create one by clicking here: https://unity.hubtel.com//account/api-accounts-add )', 'woocommerce' ),
                        'default' => _( 'xxxxxxx', 'woocommerce' ),
                    ),
                    'client_secret' => array(
                        'title' => _( 'Client secret', 'woocommerce' ),
                        'type' => 'text',
                        'description' => _( 'This is the client secret that comes with creating an application on Hubtel', 'woocommerce' ),
                        'default' => _( 'xxxxxxx', 'woocommerce' ),
                    ),
                    'store_name' => array(
                        'title' => _( 'Store name', 'woocommerce' ),
                        'type' => 'text',
                        'description' => _( 'Your company name here' ,'woocommerce' ),
                        'default' => _( '', 'woocommerce' ),
                    ),
                    'store_tagline' => array(
                        'title' => _( 'Store tagline', 'woocommerce' ),
                        'type' => 'text',
                        'description' => _( 'Your company\'s tagline here. You can leave empty if you want', 'woocommerce' ),
                        'default' => _( '', 'woocommerce' ),
                    ),
                    'store_phone' => array(
                        'title' => _( 'Store phone line', 'woocommerce' ),
                        'type' => 'text',
                        'description' => _( 'Your company\'s phone line', 'woocommerce'),
                        'default' => _( '', 'woocommerce' ),
                    ),
                    'store_website_url' => array(
                        'title' => _( 'Store website url', 'woocommerce' ),
                        'type' => 'text',
                        'description' => _( 'Your store\'s URL', 'woocommerce'),
                        'default' => _( '', 'woocommerce' ),
                    ),
                );
            } // End init_form_fields()
        
        
        
        
            /**
            * Admin options
            */
            function admin_options() {
                ?>
                <h2><?php _e('woocommerce-hubtel-payment-gateway-main','woocommerce'); ?></h2>
                <table class="form-table">
                <?php $this->generate_settings_html(); ?>
                </table> <?php
            }
    
    
            function process_payment ($order_id ){
                global $woocommerce;
                $order = new WC_Order( $order_id );
        
                //Mark as on hold
                $order->payment_complete();
                $redirect_url = hubtel_payment_gateway_create_invoice_and_checkout($order_id);
                // Remove cart
                if($redirect_url != 'error'){
                    $woocommerce->cart->empty_cart();
                }else{
                    wc_add_notice( __('Payment error:', 'woothemes') . $error_message, 'error' );
                    return;
                }
                
        
                //Return thankyou redirect
                return array(
                    'result' => 'success',
                    'redirect' => $redirect_url,
                );
        
            }
        
        
            function hubtel_payment_gateway_create_invoice_and_checkout($order_id){
                $order = wc_get_order($order_id);
        
                $items_array = array();
                $counter = 0;
        
                foreach ($order->get_items() as $item_key => $item_values ):
        
                    $item_data = $item_values->get_data();
                    $item_name = $item_values->get_name();
                    $item_quantity = $item_data['quantity'];
                    $item_description = $item_values->get_type();
                    $item_price = $item_data['total'];
                    
                    $item = array(
                        'name' => $item_name,
                        'quantity' => $item_quantity,
                        'unit_price' => $item_price,
                        'description' => $item_description,
                    );
        
                    $this->items_array['items']['item_'.$counter] = $item;
                    $counter += 1;
        
                endforeach;
        
        
                $invoice = array(
                    'invoice' => array(
                        'items' => $items_array,
                
                        'total_amount' => $order_data['total'],
                        'description' => '',
                
                    ),
                    'store' => array(
                        'name' => $this->settings['store_name'],
                        'tagline' => $this->settings['store_tagline'],
                        'phone' => $this->settings['store_phone'],
                        'website_url' => $this->settings['store_website_url'],
                    ),
                
                    'actions' => array(
                        'cancel_url' => $this->settings['store_website_url'],
                        'return_url' => $this->settings['store_website_url'],
                    ),
                );
                
                $clientId = 'xxxxxxxx'; //
                $clientSecret = 'xxxxxxx';
                $basic_auth_key =  'Basic ' . base64_encode($clientId . ':' . $clientSecret);
                $request_url = 'https://api.hubtel.com/v1/merchantaccount/onlinecheckout/invoice/create';
                $create_invoice = json_encode($invoice, JSON_UNESCAPED_SLASHES);
                
                $ch =  curl_init($request_url);  
                        curl_setopt( $ch, CURLOPT_POST, true );  
                        curl_setopt( $ch, CURLOPT_POSTFIELDS, $create_invoice);  
                        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );  
                        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
                            'Authorization: '.$basic_auth_key,
                            'Cache-Control: no-cache',
                            'Content-Type: application/json',
                          ));
                
                $result = curl_exec($ch); 
                $error = curl_error($ch);
                curl_close($ch);
                
                if($error){
                   
                    return 'error';
                }else{
                    // redirect customer to checkout
                    $response_param = json_decode($result);
                    $redirect_url = $response_param->response_text;
                    return $redirect_url;
                
                }
            
            }
        
        }//end of class
    
        
        
    }
    


}



?>