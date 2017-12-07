<?php


function hubtel_payment_gateway_create_invoice_and_checkout(){
    $invoice = array(
        'invoice' => array(
            'items' => array(
                'item_0' => array(
                      'name' => 'T Shirt',
                      'quantity' => 2,
                      'unit_price' => '35.0',
                      'total_price' => '700.0',
                      'description' => '{Customer Name} order from Mycompany.com'
                    )
                ),
    
            'total_amount' => 700.00,
            'description' => 'Total cost of 2 shirts'
    
            ),
        'store' => array(
            'name' => 'Your Company Name Here',
            'tagline' => 'Your tagline',
            'phone' => '+2335417698',
            'website_url' => 'https://hubtel.com'
            ),
    
        'actions' => array(
            'cancel_url' => 'https://hubtel.com',
            'return_url' => 'https://hubtel.com'
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
        echo $error;
    }else{
        // redirect customer to checkout
        $response_param = json_decode($result);
        $redirect_url = $response_param->response_text;
        header('Location: '.$redirect_url);
    
    }

}


?>