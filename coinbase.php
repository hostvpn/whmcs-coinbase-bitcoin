<?php

/*
    Copyright (c) 2013 HostVPN.com
    This software is released under the MIT License.
*/

function coinbase_config() {
  $configarray = array(
    "FriendlyName" => array("Type" => "System", "Value"=>"Coinbase (Bitcoin)"),
    "coinbase_api_key" => array("FriendlyName" => "Coinbase API Key", "Type" => "password", "Size" => "70", ),
    "coinbase_type" => array("FriendlyName" => "Coinbase Button Type", "Type" => "dropdown", "Options" => "buy_now,donation,subscription", "Description" => "Subscription type is not yet available.", ),
    "coinbase_style" => array("FriendlyName" => "Coinbase Button Style", "Type" => "dropdown", "Options" => "buy_now_small,buy_now_large,donation_small,donation_large,custom_small,custom_large", ),
    "coinbase_text" => array("FriendlyName" => "Coinbase Button Text", "Type" => "text", "Size" => "70", "Description" => "<br />This is the text that will appear on your button (e.g. 'Pay with Bitcoin')." ),
    "callback_secret" => array("FriendlyName" => "Coinbase Callback Secret", "Type" => "text", "Size" => "70", "Description" => "<br />Secret key will be appended to the end of your callback URL (e.g. https://yourdomain/modules/gateways/callback/coinbase.php?secret=) which you will need to provide to Coinbase so they can notify WHMCS of payments." ),
  );
  return $configarray;
}

function coinbase_link($params) {

  # Gateway Specific Variables
  $api_key  = $params['coinbase_api_key'];
  $url      = 'https://coinbase.com/api/v1/buttons?api_key=' . $api_key;
  $type     = $params['coinbase_type'];
  $style    = $params['coinbase_style'];
  $text     = $params['coinbase_text'];
  
  # Invoice Variables
  $invoiceid    = $params['invoiceid'];
  $description  = $params["description"];
  $amount       = $params['amount']; # Format: ##.##
  $currency     = $params['currency']; # Currency Code

  # Client Variables
  $firstname  = $params['clientdetails']['firstname'];
  $lastname   = $params['clientdetails']['lastname'];
  $email      = $params['clientdetails']['email'];
  $address1   = $params['clientdetails']['address1'];
  $address2   = $params['clientdetails']['address2'];
  $city       = $params['clientdetails']['city'];
  $state      = $params['clientdetails']['state'];
  $postcode   = $params['clientdetails']['postcode'];
  $country    = $params['clientdetails']['country'];
  $phone      = $params['clientdetails']['phonenumber'];

  # System Variables
  $companyname = $params['companyname'];
  $systemurl   = $params['systemurl'];
  $currency    = $params['currency'];

  $code = coinbase_button_request($invoiceid, $amount, $currency, $description, $type, $style, $text, $url);

  return $code;

}

function coinbase_button_request($invoiceid, $amount, $currency, $description, $type, $style, $text, $url) {

  $button_data = array(
    'button'=>array(
      'name'=>'Invoice '.$invoiceid.' Payment',
      'price_string'=>$amount,
      'price_currency_iso'=>$currency,
      'custom'=>$invoiceid,
      'description'=>$description,
      'type'=>$type,
      'style'=>$style),
    );

  $button_response = coinbase_post_json($url, $button_data);

  $button      = $button_response['button'];
  $button_code = $button['code'];
  $type        = $button['type'];
  $style       = $button['style'];
  $invoice_id  = $button['custom'];
  $price       = $button['price']['cents'];
  $currency    = $button['price']['currency_iso'];

  $code = '<a class="coinbase-button" data-code="'.$button_code.'" data-button-style="'.$style.'" data-button-text="'.$text.'" data-custom="'.$invoice_id.'" href="https://coinbase.com/checkouts/'.$button_code.'">Pay With Bitcoin</a><script src="https://coinbase.com/assets/button.js" type="text/javascript"></script>';

  return $code;

}

function coinbase_post_json($url, $button_data) {

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($button_data));
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  $response_data = curl_exec($ch);
  if (curl_error($ch)) die("Connection Error: ".curl_errno($ch).' - '.curl_error($ch));
  curl_close($ch);

  $button_response = json_decode($response_data, true);

  if ($button_response['success'] != 'true') {
    die("API Request failed.");
  }

  return $button_response;

}

?>
