<?php

require_once ("zampayfunctions.php");
  /* 
  This is sample code that uses a file for illustrations. Make sure to use persistent storage e.g. Database
  You can write code to return an order status
  */
   if(isset($_POST['orderId'])){
       $json_orders = json_decode(file_get_contents('orders.txt'), true);
        echo $json_orders[$_POST['orderId']];    
   }else{
       echo 'Pending';
   }   
?>