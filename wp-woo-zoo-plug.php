<?php // Opening PHP tag
/**
* Plugin Name: WP Woo-Zoolz Integration
* Plugin URI: http://mypluginuri.com/
* Description: Integrating Zoolz for Resellers with Wordpress and Woo Commerce.
* Version: 1.0
* Author: Patrick D Thomas
* Author URI: www.dingoIT.com.au
* License: MIT License
*/

/*
 * Do something after WooCommerce sets an order on completed
 */
add_action( 'woocommerce_order_status_completed', 'create_zoolz_account' );

function create_zoolz_account($order_id) {
	
	// order object (optional but handy)
	$order = new WC_Order( $order_id );

	// get the details needed to create the Zoolz account
	$name = ''.$order->billing_first_name.' '.$order->billing_last_name.'';
	$email = $order->billing_email;
	
	$items = $order->get_items();
	$item_string = reset($items);
	$product_id = $item_string['product_id'];
	$item_one = (int)$product_id;

	
	if ($item_one == 629) {
		$plan = 1535;
	} else {
		$plan = 0;
	}

	/*******************
	 * PHP Soap API Gateway
	 * before any of the calls can be executed, you need to call initSession once
	 * see WSDL file for more information 
	*******************/

	####################################################
	#	insert new user
	####################################################

	$wsdl_url="http://www.zoolz.com/services/Reseller/Service.asmx?wsdl";		// wsdl url
	
	$client = new SoapClient($wsdl_url, array('trace'=>1 , 'exceptions'=>0));

	$parameters = array (
		'authToken' => '661CDFE9AFC34E2DB51D267B754FF1BE', // api key, to authenticate
		'name' => $name,		// users full name
		'email' => $email,		// email address
		'password' => '',		// password
		'planID' => $plan,		// the ID number for their plan
		'sendEmail' => 'true',		// boolean - user notification email
	);
		
	$return = $client->CreateAccount($parameters);
	
	/*
	print "<pre>\n";
    	print_r($return);
   	print "</pre>";	
	*/	
		
	/* if($return->Code=="1000"){	// test success by forcing an error code 
		$message = 'The Account has been created successfully';
	}else{				// call failed
		$message = 'Error';
	} */

	//Used for testing purposes
	//$message = 'Token:'.$token.' Name:'.$name.' Email:'.$email.' Plan:'.$plan.'';
	$message = 'Please check the Administrator Accounts section to confirm account creation for user: Name:'.$name.' Email: '.$email.' Plan:'.$plan.'';
	woocommerce_mail( 'pdthomas@y7mail.com', 'Order Complete', $message, $headers = "Content-Type: text/htmlrn", $attachments = "" );
}
