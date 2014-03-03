<?php

/*
    Copyright (c) 2013 HostVPN.com
    This software is released under the MIT License.
*/

function coinbase_config() {
  $configarray = array(
    "FriendlyName" => array("Type" => "System", "Value"=>"Coinbase (Bitcoin)"),
    "whmcs_admin_username" => array("FriendlyName" => "WHMCS Admin User Name", "Type" => "text", "Size" => "20", "Description" => "Need an admin user name to use the local API, usually it's just 'admin'.", ),
    "coinbase_api_key" => array("FriendlyName" => "Coinbase API Key", "Type" => "password", "Size" => "70", ),
    "coinbase_api_secret" => array("FriendlyName" => "Coinbase API Secret", "Type" => "password", "Size" => "70", ),
    "coinbase_type" => array("FriendlyName" => "Coinbase Button Type", "Type" => "dropdown", "Options" => "buy_now,donation,subscription", "Description" => "Subscription type is not yet available.", ),
    "coinbase_style" => array("FriendlyName" => "Coinbase Button Style", "Type" => "dropdown", "Options" => "buy_now_small,buy_now_large,donation_small,donation_large,custom_small,custom_large", ),
    "coinbase_text" => array("FriendlyName" => "Coinbase Button Text", "Type" => "text", "Size" => "70", "Description" => "<br />This is the text that will appear on your button (e.g. 'Pay with Bitcoin')." ),
    "coinbase_ca_path" => array("FriendlyName" => "Coinbase CA Cert Path", "Type" => "text", "Size" => "70", "Description" =>"<br />This should be the full directory path to the ca-coinbase.crt file (e.g. " . dirname(__FILE__) . "/coinbase/ca-coinbase.crt)." ),
    "callback_secret" => array("FriendlyName" => "Coinbase Callback Secret", "Type" => "text", "Size" => "70", "Description" => "<br />You will need to append your callback secret to the callback URL you supply to Coinbase.  For example, a Callback Secret of 'myKey000', then supply Coinbase with the Callback URL of:<br />https://yourdomain/modules/gateways/callback/coinbase.php?secret=myKey000" ),
  );
  return $configarray;
}

function coinbase_link($params) {

  # Gateway Specific Variables
  $api_auth = array($params['coinbase_api_key'], $params['coinbase_api_secret']);
  $url      = 'https://coinbase.com/api/v1/buttons';
  $type     = $params['coinbase_type'];
  $style    = $params['coinbase_style'];
  $text     = $params['coinbase_text'];
  $ca_path  = $params['coinbase_ca_path'];
  
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

  $code = coinbase_button_request($invoiceid, $amount, $currency, $description, $type, $style, $text, $url, $ca_path, $api_auth);

  return $code;

}

// Are refunds pratical? You would need to ask the user for their wallet address, otherwise you might refund the payment back to an online wallet or some other intermediary service.
/*function coinbase_refund($params) {

  # Gateway Specific Variables
  $api_key  = $params['coinbase_api_key'];
  $url      = 'https://coinbase.com/api/v1/buttons?api_key=' . $api_key;

  # Invoice Variables
  $transid      = $params['transid'];
  $invoiceid    = $params['invoiceid'];
  $description  = $params["description"];
  $amount       = $params['amount']; # Format: ##.##
  $currency     = $params['currency']; # Currency Code

    # Client Variables
  $firstname = $params['clientdetails']['firstname'];
  $lastname = $params['clientdetails']['lastname'];
  $email = $params['clientdetails']['email'];
  $address1 = $params['clientdetails']['address1'];
  $address2 = $params['clientdetails']['address2'];
  $city = $params['clientdetails']['city'];
  $state = $params['clientdetails']['state'];
  $postcode = $params['clientdetails']['postcode'];
  $country = $params['clientdetails']['country'];
  $phone = $params['clientdetails']['phonenumber'];

  # Card Details
  $cardtype = $params['cardtype'];
  $cardnumber = $params['cardnum'];
  $cardexpiry = $params['cardexp']; # Format: MMYY
  $cardstart = $params['cardstart']; # Format: MMYY
  $cardissuenum = $params['cardissuenum'];

  # Perform Refund Here & Generate $results Array, eg:
  $results = array();
  $results["status"] = "success";
    $results["transid"] = "12345";

  # Return Results
  if ($results["status"]=="success") {
    return array("status"=>"success","transid"=>$results["transid"],"rawdata"=>$results);
  } elseif ($gatewayresult=="declined") {
        return array("status"=>"declined","rawdata"=>$results);
    } else {
    return array("status"=>"error","rawdata"=>$results);
  }

}*/

function coinbase_button_request($invoiceid, $amount, $currency, $description, $type, $style, $text, $url, $ca_path, $api_auth) {

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

  $button_response = coinbase_post_json($url, $button_data, $ca_path, $api_auth);

  $button      = $button_response['button'];
  $button_code = $button['code'];
  $type        = $button['type'];
  $style       = $button['style'];
  $invoice_id  = $button['custom'];
  $price       = $button['price']['cents'];
  $currency    = $button['price']['currency_iso'];

  $code = '<a class="coinbase-button" data-code="'.$button_code.'" data-button-style="'.$style.'" data-button-text="'.$text.'" data-custom="'.$invoice_id.'" href="https://coinbase.com/checkouts/'.$button_code.'">'.$text.'</a><script src="https://coinbase.com/assets/button.js" type="text/javascript"></script>';

  return $code;

}

function coinbase_post_json($url, $button_data, $ca_path, $api_auth) {

  if(!function_exists('hash_hmac')) { 
    die("The hash_hmac() function is unavailable.  Please upgrade PHP.");
  }

  list($api_key, $api_secret) = $api_auth;

  // Same method as the official coinbase library for nonce
  $microseconds = sprintf('%0.0f',round(microtime(true) * 1000000));
  $to_sign = $microseconds . $url . json_encode($button_data);
  $signature = hash_hmac("sha256", $to_sign, $api_secret);

  $add_headers = array(
    'Content-Type: application/json',
    'ACCESS_KEY: ' . $api_key,
    'ACCESS_SIGNATURE: ' . $signature,
    'ACCESS_NONCE: ' . $microseconds,
  );

  //$ca_coinbase_path = getcwd() . '/coinbase/ca-coinbase.crt';
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $add_headers);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($button_data));
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
  curl_setopt($ch, CURLOPT_CAINFO, $ca_path);
  $response_data = curl_exec($ch);
  if (curl_error($ch)) die("Connection Error: ".curl_errno($ch).' - '.curl_error($ch));
  curl_close($ch);

  $button_response = json_decode($response_data, true);

  if ($button_response['success'] == 'true') {
    return $button_response;
  }

}

?>
