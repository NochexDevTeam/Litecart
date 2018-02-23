<?php

$postvars = http_build_query($_POST);

ini_set("SMTP","mail.nochex.com" ); 

// Set parameters for the email
$to = $POST["From_email"];
$url = "https://www.nochex.com/apcnet/apc.aspx";

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
    $msg = "DECLINED";  // displays debug message
	

}else { 
    $msg = "AUTHORISED"; // if AUTHORISED was found in the response then it was successful
	
	
}

	
?>