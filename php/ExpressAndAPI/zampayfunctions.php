<?php
	/********************************************
	Zampay API Module
	 
	Defines all the global variables and the wrapper functions 
	********************************************/
	$PROXY_HOST = '127.0.0.1';
	$PROXY_PORT = '808';
    $USE_PROXY = false;
    
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
        $protocol = 'http://';
    } else {
        $protocol = 'https://';
    }
    $HostName = 'http://localhost:8066/';
    
	$SandboxFlag = true;
    
    //'------------------------------------
    //' If true zampay express will be used otherwise the api will be used
    $UseExpress = true;

	//'------------------------------------
	//' Zampay API Credentials
	//' Replace <API_Key with your API Key
	//' Replace <API_PASSWORD> with your API Password
	$API_Key="5484a8d3859f48e9bf8af0608791c3e8";
	$API_Password="HayVXBYCzjWjQAmEDF";

		
	/*	
	' Define the zampay Redirect URLs.  
	' 	This is the URL that the buyer is sent to do payment
	' 	change the URL depending if you are testing on the sandbox or the live zampay site
	'
	' For the sandbox, the URL is       https://gwtest.zampay.com/pay/express
	' For the live site, the URL is        https://gw.zampay.com/pay/express
	*/
	$ZampayExpress_URL = '';
    $API_Endpoint = '';
    
	if ($SandboxFlag == true) 
	{
		#$API_Endpoint = "https://gwtest.zampay.com/pay/v1";
		#$ZampayExpress_URL = "https://gwtest.zampay.com/pay/express";
        $API_Endpoint = "http://localhost:9051/pay/api/v1";        
        $ZampayExpress_URL = "http://localhost:9051/pay/express";
	}
	else
	{
		$API_Endpoint = "https://gw.zampay.com/pay/api/v1";
		$ZampayExpress_URL = "https://gw.zampay.com/pay/express";
	}

	

	if (session_id() == "") 
		session_start();
     
    class ZampayCartItem{
        var $item_unit_price;
        var $item_quantity;
        var $item_name;
    }    
        
    class ZampayParams{
        var $items = array();
        var $country_code = 'ZM';
        var $currency_code = 'ZMW';
        var $custom;
        var $mpn_notify_url;
        var $return_url;
        var $cancel_return_url;
        var $customer_email = '';
        var $debug = false;
        var $customer_phone_number = '';
        var $customer_first_name = '';
        var $customer_last_name = '';
    
        function add_item($item) {
            $this->items[] = $item;
        }
    }
        
    function WebExpress($zampayParams)
    {
        global $ZampayExpress_URL;
        global $API_Key;
        
        ?>
        <html>
        <head></head>
        <body>
        <center>
        <form name="redir" id="redir" method="POST" action="<?=$ZampayExpress_URL?>">
        <p>
        You are now being redirected to ZAMPAY...<br>
        </p>
        <p>
        Press submit to go to ZAMPAY secure platform to complete the purchase if your still seeing this page.<br/>
        </p>
        <button type="submit" name="submit-redir" value="Here" >Submit</button>
        <input type="hidden" name="CountryCode" value="<?=$zampayParams->country_code?>" />
        <input type="hidden" name="CurrencyCode" value="<?=$zampayParams->currency_code?>" />
        <input type="hidden" name="Custom" value="<?=$zampayParams->custom?>" />
        <input type="hidden" name="NotifyUrl" value="<?=$zampayParams->notify_url ?>" />
        <input type="hidden" name="Return" value="<?=$zampayParams->return_url?>" />
        <input type="hidden" name="CancelReturn" value="<?=$zampayParams->cancel_return_url?>" />
        <input type="hidden" name="Email" value="<?=$zampayParams->customer_email?>" />
        <?
        if($zampayParams->debug == true)
        {
            ?><input type="hidden" name="Debug" value="True" /><?    
        }
        ?>
        <input type="hidden" name="PhoneNumber" value="<?=$zampayParams->customer_phone_number?>" />
        <input type="hidden" name="FirstName" value="<?=$zampayParams->customer_first_name?>" />
        <input type="hidden" name="LastName" value="<?=$zampayParams->customer_last_name?>" />
        <?
        $item_index = 1;
        foreach ($zampayParams->items as $item){
            ?>
            <input type="hidden" name="ItemAmount_<?=$item_index?>" value="<?=$item->item_unit_price?>" />
            <input type="hidden" name="ItemQuantity_<?=$item_index?>" value="<?=$item->item_quantity?>" />
            <input type="hidden" name="ItemName_<?=$item_index?>" value="<?=$item->item_name?>" />
            <?
            $item_index = $item_index + 1;
        }
        ?>        
        <input type="hidden" name="ApiKey" value="<?=$API_Key?>" />
        <script language="javascript">
            var theform = document.redir;
            theform.submit();
        </script>
        </form> 
        </center>                 
        </body>                        
        </html>
              

        <?
    }
    
    function CreateAndSendInvoice($zampayParams){
        
        global $API_Key;
        global $API_Endpoint;
        global $API_Password;
        
        // Post to Zampay system and add api Cmd and auth parameters
        $req = '';
        if(function_exists('get_magic_quotes_gpc')) {
           $get_magic_quotes_exists = true;
        } 
        $myPost = array(
            'Cmd' => 'CreateAndSendInvoice',
            'ApiKey' => $API_Key,
            'ApiPassword' => $API_Password,
            "CountryCode"=>$zampayParams->country_code,
            "CurrencyCode"=>$zampayParams->currency_code,
            "Custom"=>$zampayParams->custom,
            "NotifyUrl"=>$zampayParams->notify_url,
            "FirstName"=>$zampayParams->customer_first_name,
            "LastName"=>$zampayParams->customer_last_name,
            "Email"=>$zampayParams->customer_email,
            "PhoneNumber"=>$zampayParams->customer_phone_number
        );
        
        $item_index = 1;
        foreach ($zampayParams->items as $item){
            $myPost["ItemAmount_$item_index"]=$item->item_unit_price;
            $myPost["ItemQuantity_$item_index"]=$item->item_quantity;
            $myPost["ItemName_$item_index"]=$item->item_name;            
            $item_index = $item_index + 1;
        }
        
        foreach ($myPost as $key => $value) {        
           if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
                $value = urlencode(stripslashes($value)); 
           } else {
                $value = urlencode($value);
           }
           if($req == ''){
            $req .= "$key=$value";
           }else{
            $req .= "&$key=$value";
           }
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
            throw new Exception('Curl error: ' . curl_error($ch) . ' URL: ' . $API_Endpoint);
            curl_close($ch);            
        }else{
            curl_close($ch);
            $api_response = json_decode($res);            
            if($api_response == null || $api_response->Status != 200){        
                throw new Exception('API Call Failed: ' . $res . '<br/>req:' . $req );
            }
            return $api_response;
        }
    }
	

?>
