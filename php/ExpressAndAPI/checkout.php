<?php

require_once ("zampayfunctions.php");

$fruits = $_POST["fruit"];
$_SESSION["fruit"] = $fruits;

if(empty($_POST['fruit'])){
    $_SESSION['checkout_errors']='Select fruits';
    header("Location: index.php"); /* Redirect browser */
    exit(); 
}

$currencyCodeType = "ZMW";

//'------------------------------------
//' The returnURL is the location where buyers return to when a
//' payment has been succesfully.
//'------------------------------------
$returnURL = $HostName . "mdt_notify.php";

//'------------------------------------
//' The cancelURL is the location buyers are sent to when they hit the
//' cancel button during zampay payment flow
//'------------------------------------
$cancelURL = $HostName . "cancelled.php";

//'------------------------------------
//' The notifyURL is the location where your application recieves notifications of sucessfull payment or payment that is cancelled
//' When set and passed, it will override the set under api settings
//' cancel button during zampay payment flow
//'------------------------------------
$notifyURL = $HostName . "mpn_notify.php";

$orderId = com_create_guid();



$zampayParams = new ZampayParams();
$zampayParams->cancel_return_url = $cancelURL;
$zampayParams->country_code = "ZM";
$zampayParams->currency_code = $currencyCodeType;
$zampayParams->custom = $orderId;
$zampayParams->debug=true;
$zampayParams->mpn_notify_url = $notifyURL;
$zampayParams->return_url = $returnURL;

foreach($fruits as $fruit){
    $zampayItem = new ZampayCartItem();    
    if($fruit == 'package_1'){
        $zampayItem->item_name = 'Water Mellon';
        $zampayItem->item_quantity = 1;
        $zampayItem->item_unit_price = 10;
    }else if($fruit == 'package_2'){
        $zampayItem->item_name = 'Mangoes';
        $zampayItem->item_quantity = 1;
        $zampayItem->item_unit_price = 20;
    }else if($fruit == 'package_3'){
        $zampayItem->item_name = 'Oranges';
        $zampayItem->item_quantity = 1;
        $zampayItem->item_unit_price = 40;
    }
    $zampayParams->add_item($zampayItem);
}

if($UseExpress == true){
    WebExpress($zampayParams);
}else{
    try{
        
        $api_response = CreateAndSendInvoice($zampayParams);
        ?>
        <html>
            <head>
                <script src="jquery-1.7.1.min.js"></script>
            </head>
            <body>
               <p> API JSON Response: <pre> <?= json_encode($api_response) ?> </pre></p>
               <br />
               <p>
                You can pay using the following:<br />
                Client Id: <strong><?= $api_response->AccountNumber ?></strong><br />
                Payment Reference: <strong><?= $api_response->PaymentRefNumber ?></strong><br />
                Pay Before: <?= $api_response->OrderExpiryDate ?>
                <!-- NB: You can add a count down since you have a value of OrderTimeOut minutes -->
               </p>
               <p>
                 <span>
                    <span>Now: make payment via your phone</span>
                    <span id="payment-countdown"></span>
                    <img src="ajax-loader-big.gif" alt="" title="Checking ..." />
                    <span style="display:none;">Order Status: <span id="orderStatus">Pending</span></span>
                    <span id="statusDesc">Payment is pending payment</span>
                 </span>
                <span id="error-msg" class="field-validation-error"></span>
                </p>
                <script language="javascript" type="text/javascript">
                    var maxWaitTime = <?= $api_response->OrderTimeOut ?> * 60 * 1000;
                    var totalWaitTime = 0;
                    var refreshInterval = 10000;

                    function setErrorMsg(error){
                        $("#error-msg").html(error);
                    }

                    function scheduleRefresh(){
                        if(totalWaitTime > maxWaitTime){
                            setErrorMsg("Payment not received within <?= $api_response->OrderTimeOut ?> minutes");
                            setTimeout(function(){window.location="index.php";},5000);
                            return;
                        }            
                        setTimeout(function(){ checkOrderStatus()},refreshInterval);
                    }

                    function checkOrderStatus() {
                        totalWaitTime += refreshInterval;
                        setErrorMsg("");
                        $.ajax({
                            url: "order_status.php",
                            data: "orderId=<?= $orderId ?>",
                            type: 'post',
                            success: checkOrderStatusSuccess,
                            error: checkOrderStatusError,
                        });
                    }

                    function checkOrderStatusSuccess(response){                                            
                        if(response == 'Paid'){
                            $("#orderStatus").html("Paid");
                            $("#statusDesc").html("Payment has been made. You can now redirect to page with order details or some confirmation message");
                            return;
                        }
                    
                        scheduleRefresh();
                    }

                    function checkOrderStatusError(){
                        scheduleRefresh();
                    }

                     $(document).ready(function () {
                        scheduleRefresh();                        
                    });

                </script>
            </body>
        </html>
        <?
        
    } catch (Exception $e) {
        echo 'Caught exception: ',  $e->getMessage(), "\n";
    } 
}

?>
