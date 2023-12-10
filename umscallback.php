<?php
include 'config.php';
header("Content-Type: application/json");
$stkCallbackResponse = file_get_contents('php://input');
$logFile = "UMSPayMpesaStkResponse.json";
$log = fopen($logFile, "a");
fwrite($log, $stkCallbackResponse);
fclose($log);
$data = json_decode($stkCallbackResponse);
$CheckoutRequestID = $data->Body->stkCallback->CheckoutRequestID;
$MerchantRequestID = $data->Body->stkCallback->MerchantRequestID;
$ResultCode = $data->Body->stkCallback->ResultCode;
$Amount = $data->Body->stkCallback->CallbackMetadata->Item[0]->Value;
$TrdansactionId = $data->Body->stkCallback->CallbackMetadata->Item[1]->Value;
$PhoneNumber = $data->Body->stkCallback->CallbackMetadata->Item[4]->Value;
//GET THE umspay_transactions TABLE
$transactions = mysqli_query($db, "SELECT * FROM umspay_transactions WHERE CheckoutRequestID = '$CheckoutRequestID' AND MerchantRequestID = '$MerchantRequestID'");
$transactionsData = mysqli_fetch_array($transactions);
$TranctEmail = $transactionsData['email'];
$TransactionAmount = (int)$transactionsData['TransactionAmount'];
$TransactionDate = $transactionsData['TransactionDate'];
$TransactionID = $transactionsData['TransactionID'];
$TransactionReference = $transactionsData['TransactionReference'];
$Msisdn  = $transactionsData['Msisdn'];
$getCreadit = mysqli_fetch_array(mysqli_query($db, "SELECT * FROM umeskiaservice WHERE email='$TranctEmail' AND productName='umspay'"));
//CHECK IF THE CALLBACK URL IS SET
if (!$getCreadit['callback'] == '' || !$getCreadit['callback'] == null) {
  $callbackWebHookUrl = $getCreadit['callback'];
} else {
  $callbackWebHookUrl = '';
}
if ($ResultCode == 0) {
  $TransactionReceipt = $TrdansactionId;
  $dataWebhook = array(
    "ResponseCode" => 0,
    "ResponseDescription" => "Success. Request accepted for processing",
    "MerchantRequestID" => $MerchantRequestID,
    "CheckoutRequestID" => $CheckoutRequestID,
    "TransactionID" => $TransactionID,
    "TransactionAmount" => $TransactionAmount,
    "TransactionReceipt" => $TransactionReceipt,
    "TransactionDate" => $TransactionDate,
    "TransactionReference" =>  $TransactionReference,
    "Msisdn" => $Msisdn
  );
  $TransactionStatus = 'Completed';
  $update = mysqli_query($db, "UPDATE umspay_transactions SET TransactionStatus='$TransactionStatus', TransactionReceipt='$TransactionReceipt',ResultCode='$ResultCode', ResultDesc='Success. Request accepted for processing' WHERE CheckoutRequestID='$CheckoutRequestID' AND MerchantRequestID='$MerchantRequestID'");
} else {
  $responseCodes = [
    1 => "The balance is insufficient for the transaction.",
    1032 => "Request cancelled by user",
    1037 => "DS timeout user cannot be reached",
    1025 => "An error occurred while sending a push request",
    2001 => "The initiator information is invalid.",
    9999 => "An error occurred while sending a push request.",
    1019 => "The organization receiving the funds is invalid.",
    1001 => "Unable to lock subscriber, a transaction is already in process for the current subscriber"
  ];
  $TransactionReceipt = '';
  if($ResultCode == 1032){
    $TransactionStatus = 'Cancelled';
  }else{
    $TransactionStatus = 'Failed';
  }
  $ResponseDescription = $responseCodes[$ResultCode];
  $dataWebhook = array(
    "ResponseCode" => $ResultCode,
    "ResponseDescription" => $ResponseDescription,
    "MerchantRequestID" => $MerchantRequestID,
    "CheckoutRequestID" => $CheckoutRequestID,
    "TransactionID" => $TransactionID,
    "TransactionAmount" => $TransactionAmount,
    "TransactionDate" => $TransactionDate,
    "TransactionReference" =>  $TransactionReference,
    "Msisdn" => $Msisdn
  );
  // UPDATE umspay_transactions
  $update = mysqli_query($db, "UPDATE umspay_transactions SET TransactionStatus='$TransactionStatus', TransactionReceipt='$TransactionReceipt',ResultCode='$ResultCode', ResultDesc='$ResponseDescription' WHERE CheckoutRequestID='$CheckoutRequestID' AND MerchantRequestID='$MerchantRequestID'");
}



//CHECK IF THE CALLBACK URL IS SET
if (!$callbackWebHookUrl == '') {
  $jsonWebHookData = json_encode($dataWebhook);
  // Create cURL resource
  $webHook = curl_init();
  // Set the cURL options
  curl_setopt($webHook, CURLOPT_URL, $callbackWebHookUrl);
  curl_setopt($webHook, CURLOPT_POST, 1);
  curl_setopt($webHook, CURLOPT_POSTFIELDS, $jsonWebHookData);
  curl_setopt($webHook, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
  curl_setopt($webHook, CURLOPT_RETURNTRANSFER, true);
  // Execute the webhook request
  $webhookResponse = curl_exec($webHook);
  // webHookeck for errors
  if (curl_errno($webHook)) {
    $error = 'Webhook request failed: ' . curl_error($webHook);
  } else {
    // Process the response from the webhook endpoint
    $responseData = json_decode($webhookResponse, true);
    // Perform necessary actions with the response data
  }
  // Close cURL resource
  curl_close($webHook);
}
//SEND RESPONSE
echo json_encode($response);
