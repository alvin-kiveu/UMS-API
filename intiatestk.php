<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $ResultCode = '';
  $callbackWebHookUrl = "";
  $consumerKey = "hRDj5AjUhqYwtWslwY5BAJA9f17nGXzb";
  $consumerSecret = "5mowhxiMYNgXzpnw";
  $BusinessShortCode = "8044747";
  $data = file_get_contents('php://input');
  $data = json_decode($data, true);
  $api_key = $data['api_key'];
  $email = $data['email'];
  $amount = $data['amount'];
  $msisdn = $data['msisdn'];
  $reference = $data['reference'];
  //CHECK IF ITS EMPTY
  if ($api_key == '') {
    $ResultCode = "102";
    $massage = "Request is missing required Api Key!";
    $response = array(
      'ResultCode' => $ResultCode,
      'errorMessage' => $massage
    );
  } else {
    if ($email == '') {
      $ResultCode = "102";
      $massage = "Request is missing required email!";
      $response = array(
        'ResultCode' => $ResultCode,
        'errorMessage' => $massage
      );
    } else {

        if ($amount == '') {
          $ResultCode = "102";
          $massage = "Request is missing required amount!";
          $response = array(
            'ResultCode' => $ResultCode,
            'errorMessage' => $massage
          );
        } else {
          if ($msisdn == '') {
            $ResultCode = "102";
            $massage = "Request is missing required msisdn!";
            $response = array(
              'ResultCode' => $ResultCode,
              'errorMessage' => $massage
            );
          } else {
            if ($reference == '') {
              $ResultCode = "102";
              $massage = "Request is missing required reference!";
              $response = array(
                'ResultCode' => $ResultCode,
                'errorMessage' => $massage
              );
            } else {
              $checkEmail = mysqli_query($db, "SELECT * FROM ums_users WHERE email='$email'");
              if (mysqli_num_rows($checkEmail) > 0) {
                $get_user_id = "SELECT *  FROM ums_users  WHERE email='$email' AND apiKey='$api_key';";
                $result_get_user_id = mysqli_query($db, $get_user_id);
                if (mysqli_num_rows($result_get_user_id) > 0) {
                  //GET THE DETAILS IN umspay_accounts
                  $getDetails = mysqli_query($db, "SELECT * FROM umspay_accounts WHERE email='$email'");
                  if (mysqli_num_rows($getDetails) > 0) {
                    $umspay_accounts = mysqli_fetch_array($getDetails);
                    $AccountStatus = $umspay_accounts['status'];
                    $AccountType = $umspay_accounts['accountType'];
                    if ($AccountStatus == 'Active') {
                      if ($AccountType == 'Bank') {
                        $TransactionType = 'CustomerPayBillOnline';
                        $ClientPaybill = $umspay_accounts['bank'];
                        $ClientsAccountNumber = $umspay_accounts['bankAccount'];
                      } elseif ($AccountType == 'Paybill') {
                        $TransactionType = 'CustomerPayBillOnline';
                        $ClientPaybill = $umspay_accounts['paybillNumber'];
                        $ClientsAccountNumber = $reference;
                      } elseif ($AccountType == 'Till') {
                        $TransactionType = 'CustomerBuyGoodsOnline';
                        $ClientPaybill = $umspay_accounts['tillNumber'];
                        $ClientsAccountNumber = $reference;
                      }
                      $phone = $msisdn;
                      //CHECK IF THERE IS 254 IN THE PHONE NUMBER
                      if (substr($phone, 0, 3) == '254') {
                        $phone = $phone;
                      } else {
                        $phone = "254" . (int)$phone;
                      }
                      //ACCESS TOKEN URL
                      $access_token_url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
                      $headers = ['Content-Type:application/json; charset=utf8'];
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
                      date_default_timezone_set('Africa/Nairobi');
                      $processrequestUrl = 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
                      $callbackurl = 'https://api.umeskiasoftwares.com/api/v1/umscallback.php';
                      $passkey = "f270ee48395636772c7a1eb7208cbeafd0c6a85848cab9d5530b5a76a0a79419";
                      $Timestamp = date('YmdHis');
                      $Password = base64_encode($BusinessShortCode . $passkey . $Timestamp);
                      $PartyA = $phone;
                      $PartyB = $ClientPaybill;
                      $AccountReference = $ClientsAccountNumber;
                      $TransactionDesc = 'stkpush test';
                      $Amount = $money;
                      $stkpushheader = ['Content-Type:application/json', 'Authorization:Bearer ' . $access_token];
                      //INITIATE CURL
                      $curl = curl_init();
                      curl_setopt($curl, CURLOPT_URL, $processrequestUrl);
                      curl_setopt($curl, CURLOPT_HTTPHEADER, $stkpushheader);
                      $curl_post_data = array(
                        'BusinessShortCode' => $BusinessShortCode,
                        'Password' => $Password,
                        'Timestamp' => $Timestamp,
                        'TransactionType' => $TransactionType,
                        'Amount' => $amount,
                        'PartyA' => $PartyA,
                        'PartyB' => $PartyB,
                        'PhoneNumber' => $PartyA,
                        'CallBackURL' => $callbackurl,
                        'AccountReference' => $AccountReference,
                        'TransactionDesc' => $TransactionDesc
                      );

                      $data_string = json_encode($curl_post_data);
                      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                      curl_setopt($curl, CURLOPT_POST, true);
                      curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
                      $curl_response = curl_exec($curl);
                      //ECHO  RESPONSE
                      $data = json_decode($curl_response);
                      if (isset($data->ResponseCode) && $data->ResponseCode == 0) {
                        $CheckoutRequestID = $data->CheckoutRequestID;
                        $MerchantRequestID = $data->MerchantRequestID;
                        $TransactionID = "UMSPID" . date('dmYHis') . rand(10000, 99999);
                        $TransactionDate = date('YmdHis');
                        $TransactionReference = $reference;
                        $TransactionAmount = $amount;
                        $TransactionStatus = 'Pending';
                        $Msisdn = $phone;
                        $storeTransaction = mysqli_query($db, "INSERT INTO umspay_transactions (email,TransactionID,TransactionDate,TransactionReference,TransactionAmount,Msisdn,CheckoutRequestID,MerchantRequestID,TransactionStatus) VALUES ('$email','$TransactionID','$TransactionDate','$TransactionReference','$TransactionAmount','$Msisdn','$CheckoutRequestID','$MerchantRequestID','$TransactionStatus')");
                        if ($storeTransaction) {
                          $ResultCode = "200";
                          $sendmessage = "Please enter your MPESA PIN to complete the transaction";
                          $response = array(
                            'success' => $ResultCode,
                            'message' => $sendmessage,
                            'tranasaction_request_id' => $TransactionID,
                          );
                        } else {
                          $ResultCode = "503";
                          $massage = "An error occurred while processing your request!";
                          $response = array(
                            'ResultCode' => $ResultCode,
                            'errorMessage' => $massage
                          );
                        }
                      } else {
                        $errorMessage = $data->errorMessage;
                        $ResultCode = "503";
                        $massage = "$errorMessage";
                        $response = array(
                          'ResultCode' => $ResultCode,
                          'errorMessage' => $massage
                        );
                      }
                    } else {
                      $ResultCode = "400";
                      $massage = "Your UMS Pay account is not active!";
                      $response = array(
                        'ResultCode' => $ResultCode,
                        'errorMessage' => $massage
                      );
                    }
                  } else {
                    $ResultCode = "400";
                    $massage = "You have not created a UMS Pay account!";
                    $response = array(
                      'ResultCode' => $ResultCode,
                      'errorMessage' => $massage
                    );
                  }
                } else {
                  $ResultCode = "102";
                  $massage = "The Api Key provided is not valid!";
                  $response = array(
                    'ResultCode' => $ResultCode,
                    'errorMessage' => $massage
                  );
                }
              } else {
                $ResultCode = "102";
                $massage = "Email does not exist!";
                $response = array(
                  'ResultCode' => $ResultCode,
                  'errorMessage' => $massage
                );
              }
            }
          }
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
