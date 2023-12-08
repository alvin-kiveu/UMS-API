<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $ResultCode = '';
  $data = file_get_contents('php://input');
  $data = json_decode($data, true);
  $consumerKey = $data['consumerKey'];
  $consumerSecret = $data['consumerSecret'];
  $shortCode = $data['shortCode'];
  $passKey = $data['passKey'];
  $amount = $data['amount'];
  $phoneNumber = $data['phoneNumber'];
  $accountReference = $data['accountReference'];
  $CallBackURL= $data['callbackUrl'];
  //CHECK IF IS EMPTY
  if ($consumerKey == '' || $consumerSecret == '' || $shortCode == '' || $passKey == '' || $amount == '' || $phoneNumber == '' || $accountReference == '' || $CallBackURL == '') {
    $ResultCode = "201";
    $ResultDesc = "Please fill all the fields provided";
    $response = array(
      "ResultCode" => $ResultCode,
      "ResultDesc" => $ResultDesc
    );
    echo json_encode($response);
    exit;
  } else {
    if (substr($phoneNumber, 0, 3) === "254") {
      $phoneNumber = $phoneNumber;
    } else {
      $phoneNumber = "254" . substr($phoneNumber, -9);
    }
    date_default_timezone_set('Africa/Nairobi');
    $BusinessShortCode = $shortCode;
    $Passkey = $passKey;
    $PartyA = $mpesaphone; // This is your phone number, 
    $PartyB = '254713851920'; //This os the sane as business short code
    $AccountReference =  $accountReference;
    $TransactionDesc = 'Please cornfirm payment made to UMESKIA SOFTWARES.';
    # Get the timestamp, format YYYYmmddhms -> 20181004151020
    $Timestamp = date('YmdHis');
    # Get the base64 encoded string -> $password. The passkey is the M-PESA Public Key
    $Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);
    # header for access token
    $headers = ['Content-Type:application/json; charset=utf8'];
    # M-PESA endpoint urls
    $access_token_url = 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
    $initiate_url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
    $curl = curl_init($access_token_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($curl, CURLOPT_HEADER, FALSE);
    curl_setopt($curl, CURLOPT_USERPWD, $consumerKey . ':' . $consumerSecret);
    $result = curl_exec($curl);
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $result = json_decode($result);
    $access_token = $result->access_token;
    curl_close($curl);
    if($access_token == ""){
      $ResultCode = "201";
      $massage = "Invalid consumer key : $consumerKey or consumer secret : $consumerSecret";
      $response = array(
        'ResultCode' => $ResultCode,
        'errorMessage' => $massage
      );  
    }else{
    # header for stk push
    $stkheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];
    # initiating the transaction
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $initiate_url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader); //setting custom header
    $curl_post_data = array(
      'BusinessShortCode' => $BusinessShortCode,
      'Password' => $Password,
      'Timestamp' => $Timestamp,
      'TransactionType' => 'CustomerPayBillOnline',
      'Amount' => $amount,
      'PartyA' => $PartyA,
      'PartyB' => $BusinessShortCode,
      'PhoneNumber' => $PartyA,
      'CallBackURL' => $CallBackURL,
      'AccountReference' => $AccountReference,
      'TransactionDesc' => $TransactionDesc
    );
    $data_string = json_encode($curl_post_data);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
    $curl_response = curl_exec($curl);
    $data_to = json_decode($curl_response);
    if ($data_to->ResponseCode == '0') {
      $CheckoutRequestID = $data_to->CheckoutRequestID;
      $MerchantRequestID = $data_to->MerchantRequestID;
      $ResponseCode = $data_to->ResponseCode;
      $ResponseDescription = $data_to->ResponseDescription;
      $CustomerMessage = $data_to->CustomerMessage;
      $ResultCode = "200";
      $massage = "Transaction request is successfull";
      $response = array(
        'ResultCode' => $ResultCode,
        'successMessage' => $massage,
        'CheckoutRequestID' => $CheckoutRequestID,
        'MerchantRequestID' => $MerchantRequestID,
        'ResponseCode' => $ResponseCode,
        'ResponseDescription' => $ResponseDescription,
        'CustomerMessage' => $CustomerMessage
      );
    } else {
      $ResultCode = "201";
      $massage = "An error has occoured please try again!";
      $response = array(
        'ResultCode' => $ResultCode,
        'errorMessage' => $massage,
        'error' => $curl_response
      );
    }
  }
  }
} else {
  $ResultCode = "201";
  $massage = "Invalid HTTPS request method request please use POST";
  $response = array(
    'ResultCode' => $ResultCode,
    'errorMessage' => $massage
  );
}
if (!$ResultCode == '') {
  echo json_encode($response);
}
