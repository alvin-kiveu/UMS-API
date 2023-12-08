<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $ResultCode = '';
  $data = file_get_contents('php://input');
  // Decode the JSON data
  $data = json_decode($data, true);
  $mpesaphone = $data['mpesaNumber'];
  $senttophone = $data['airtimePhoneNumber'];
  $amount =  (int)$data['airtimeAmount'];
  if (substr($mpesaphone, 0, 3) === "254") {
    $mpesaphone = $mpesaphone;
  } else {
    $mpesaphone = "254" . substr($mpesaphone, -9);
  }
  if (substr($senttophone, 0, 3) === "254") {
    $senttophone = $senttophone;
  } else {
    $senttophone = "254" . substr($senttophone, -9);
  }
 
  date_default_timezone_set('Africa/Nairobi');
  # access token
  $consumerKey = 'hRDj5AjUhqYwtWslwY5BAJA9f17nGXzb';
  $consumerSecret = '5mowhxiMYNgXzpnw';
  $BusinessShortCode = '8044747';
  $Passkey = 'f270ee48395636772c7a1eb7208cbeafd0c6a85848cab9d5530b5a76a0a79419';
  $PartyA = $mpesaphone; // This is your phone number, 
  $PartyB = '254713851920'; //This os the sane as business short code
  $AccountReference = $senttophone;
  $TransactionDesc = 'Please cornfirm payment made to UMESKIA SOFTWARES.';
  # Get the timestamp, format YYYYmmddhms -> 20181004151020
  $Timestamp = date('YmdHis');
  # Get the base64 encoded string -> $password. The passkey is the M-PESA Public Key
  $Password = base64_encode($BusinessShortCode . $Passkey . $Timestamp);
  # header for access token
  $headers = ['Content-Type:application/json; charset=utf8'];
  # M-PESA endpoint urls
  $access_token_url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
  $initiate_url = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
  # callback url
  $CallBackURL = 'https://umeskiasoftwares.com/umswifi/callback.php';
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
  # header for stk push
  $stkheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];
  # initiating the transaction
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $initiate_url);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $stkheader); //setting custom header
  $curl_post_data = array(
    //Fill in the request parameters with valid values
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
    $ResultCode = "200";
    $massage = "Please enter your Mpesa PIN to complete the airtime purchase.";
    $response = array(
      'ResultCode' => $ResultCode,
      'successMessage' => $massage,
      'request_id' => $request_id
    );
  } else {
    $ResultCode = "102";
    $massage = "An error has occoured please try again! $curl_response";
    $response = array(
      'ResultCode' => $ResultCode,
      'errorMessage' => $massage
    );
  }
  if (!$ResultCode == '') {
    echo json_encode($response);
  }
} else {
  $ResultCode = "102";
  $massage = "This is not the right Method of rquest it shold be POST";
  $response = array(
    'ResultCode' => $ResultCode,
    'errorMessage' => $massage
  );
  echo json_encode($response);
}
