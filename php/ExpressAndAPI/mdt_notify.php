<?php
require_once ("zampayfunctions.php");
 
// STEP 1: Read POST data

// reading posted data from directly from $_POST causes serialization 
// issues with array data in POST
// reading raw POST data from input stream instead. 
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
  $keyval = explode ('=', $keyval);
  if (count($keyval) == 2)
     $myPost[$keyval[0]] = urldecode($keyval[1]);
}

// read the post from Zampay system and add api Cmd and auth parameters
$req = 'Cmd=VerifyMdt&ApiKey=' . $API_Key . '&ApiPassword=' . $API_Password ;
if(function_exists('get_magic_quotes_gpc')) {
   $get_magic_quotes_exists = true;
} 
foreach ($myPost as $key => $value) {        
   if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
        $value = urlencode(stripslashes($value)); 
   } else {
        $value = urlencode($value);
   }
   $req .= "&$key=$value";
}
 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
$res = curl_exec($ch);


if(!$res){
    //HTTP ERROR
    echo 'Curl error: ' . curl_error($ch);
    echo ("<p>Request:<br/>" . $req . "</p>");
    echo $API_Endpoint;
    curl_close($ch);
}else{
    curl_close($ch);
     // parse the data
    $api_response = json_decode($res);
    if($api_response == null || $api_response->Status != 200){        
        // log for manual investigation
        echo ("<p><h3>MDT Verification failed</h3></p>");
        echo "URL: " . $API_Endpoint;
        echo ("<p>Request:<br/>" . $req . "</p>");
        echo ("<p>Response:<br/>" . $res . "</p>");
        exit();
    }
    
    $payment_status = $myPost['PaymentStatus'];
    $order_number = $myPost['Custom'];
    
    // check the payment_status is Paid
    if( $payment_status == 'Paid' ){
        // check that order number has not been previously processed
        
        // check that payment_amount/payment_currency are correct
        
        // process payment    
        
        $payer_name = $myPost['PayerName'];
        $currency_code = $myPost['CurrencyCode'];
        $amount = $myPost['Amount'];
        $zampay_transaction_id = $myPost['TransactionId'];
        $zampay_payment_order_id =$myPost['OrderId'];
        $zampay_payment_order_number =$myPost['OrderNumber'];
         
        echo ("<p><h3>Thank you for your purchase!</h3></p>");        
        echo ("<b>Payment Details</b><br>\n");
        echo ("<li>Name: $payer_name </li>\n");
        echo ("<li>Amount: $amount</li>\n");
        echo ("<li>Zampay Transaction Id: $zampay_transaction_id</li>\n");
        echo ("<li>Zampay Payment Order Id: $zampay_payment_order_id</li>\n");
        echo ("<li>Zampay Payment Order Number: $zampay_payment_order_number</li>\n");
        echo ("");
    }else{
       // log for manual investigation
       echo ("<p><h3>Payment Status With MDT is not paid</h3></p>");
       echo ("<p>Request:<br/>" . $req . "</p>");
       echo ("<p>Response:<br/>" . $res . "</p>");
    }   
}
 
?>
