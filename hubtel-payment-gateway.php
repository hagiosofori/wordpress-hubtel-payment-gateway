<?php

class Hubtel_Payment_Gateway{

    function __construct(){
        $this->init_form_fields();
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
             'default' => __( 'PayPal', 'woocommerce' )
             ),
        'description' => array(
             'title' => __( 'Description', 'woocommerce' ),
             'type' => 'textarea',
             'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
             'default' => __("Pay via PayPal; you can pay with your credit card if you don't have a PayPal account", 'woocommerce')
              )
        );
   } // End init_form_fields()

}

?>