<?php

require_once('../../../../includes/app_header.inc.php');

	  
	  $order = new ent_order((int)$_POST["order_id"]);
	  
	  $order->data['payment_transaction_id'] = $_POST['transaction_id'];
	  
	  
$postvars = http_build_query($_POST);

ini_set("SMTP","mail.nochex.com" ); 

if ($_POST["optional_2"] == "Enabled") {

$url = "https://secure.nochex.com/callback/callback.aspx";
$ch = curl_init ();
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt ($ch, CURLOPT_POST, true);
curl_setopt ($ch, CURLOPT_POSTFIELDS, $postvars);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
$response = curl_exec ($ch);
curl_close ($ch);

if($_POST["transaction_status"] == "100"){
$testStatus = "Test"; 
}else{
$testStatus = "Live";
}

// Put the variables in a printable format for the email
$debug = "IP -> " . $_SERVER['REMOTE_ADDR'] ."\r\n\r\nPOST DATA:\r\n"; 
foreach($_POST as $Index => $Value) 
$debug .= "$Index -> $Value\r\n"; 
$debug .= "\r\nRESPONSE:\r\n$response";

if ($response=="AUTHORISED") {

    $msg = "Payment complete, Callback: AUTHORISED, this was a " . $testStatus . " transaction"; // if AUTHORISED was found in the response then it was successful
	$order_status_id = 3;
	$orderstarred = "0";
	
} else {
    
	$msg = "Payment complete, Callback: DECLINED, this was a " . $testStatus . " transaction";  // displays debug message
	$order_status_id = 5;
	$orderstarred = "1";
	
}

} else {
// Set parameters for the email
$url = "https://secure.nochex.com/apc/apc.aspx";

// Curl code to post variables back
$ch = curl_init(); // Initialise the curl tranfer
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars); // Set POST fields
curl_setopt ($ch, CURLOPT_SSLVERSION, 6); // set openSSL version variable to 3
$output = curl_exec($ch); // Post back
curl_close($ch);

// Put the variables in a printable format for the email
$debug = "IP -> " . $_SERVER['REMOTE_ADDR'] ."\r\n\r\nPOST DATA:\r\n"; 
foreach($_POST as $Index => $Value) 
$debug .= "$Index -> $Value\r\n"; 
$debug .= "\r\nRESPONSE:\r\n$output";

//If statement
if (!strstr($output, "AUTHORISED")) {  // searches response to see if AUTHORISED is present if it isn’t a failure message is displayed
    $msg = "Payment complete, APC: DECLINED, this was a " . $_POST["status"] . " transaction";  // displays debug message
	$order_status_id = 5;
	$orderstarred = "1";
}else { 
    $msg = "Payment complete, APC: AUTHORISED, this was a " . $_POST["status"] . " transaction"; // if AUTHORISED was found in the response then it was successful
	$order_status_id = 3;
	$orderstarred = "0";
}

}

 database::query(
        "update ". DB_TABLE_ORDERS ." set
        `order_status_id` = " . $order_status_id . ",
        `payment_transaction_id` = ". $_POST["transaction_id"] ."
        where `id` = ".(int)$_POST["order_id"].";"
      );
database::query(
              "insert into ". DB_TABLE_ORDERS_COMMENTS ."
              (order_id, date_created, author, text, hidden)
              values (". (int)$_POST["order_id"] .", '". date('Y-m-d H:i:s') ."','Nochex',' ".$msg."',1);"
            );
?>
