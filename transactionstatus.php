<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $ResultCode = '';
  $data = file_get_contents('php://input');
  $data = json_decode($data, true);
  $api_key = $data['api_key'];
  $email = $data['email'];
  $tranasaction_request_id  = $data['tranasaction_request_id'];
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

      if ($tranasaction_request_id == '') {
        $ResultCode = "102";
        $massage = "Request is missing required tranasaction_request_id!";
        $response = array(
          'ResultCode' => $ResultCode,
          'errorMessage' => $massage
        );
      } else {
        $checkEmail = mysqli_query($db, "SELECT * FROM ums_users WHERE email='$email'");
        if (mysqli_num_rows($checkEmail) > 0) {
          $checkApi = mysqli_query($db, "SELECT * FROM ums_users WHERE apiKey='$api_key'");
          if (mysqli_num_rows($checkApi) > 0) {
            $checkTransaction = mysqli_query($db, "SELECT * FROM umspay_transactions WHERE TransactionID='$tranasaction_request_id'");
            if (mysqli_num_rows($checkTransaction) > 0) {
              $TransData = mysqli_fetch_array($checkTransaction);
              $TransactionStatus = $TransData['TransactionStatus'];
              $TransactionCode = $TransData['ResultCode'];
              $ResultDesc = $TransData['ResultDesc'];
              if($TransactionStatus == 'Pending'){
                $ResultDesc = 'Transaction is pending';
              }
              $ResultCode = "200";
              $response = array(
                'ResultCode' => $ResultCode,
                'ResultDesc' => $ResultDesc,
                'CheckoutRequestID' => $TransData['CheckoutRequestID'],
                'MerchantRequestID' => $TransData['MerchantRequestID'],
                'TransactionStatus' => $TransactionStatus,
                'TransactionCode' => $TransactionCode,
                'TransactionReceipt' => $TransData['TransactionReceipt'],
                'TransactionAmount' => $TransData['TransactionAmount'],
                'Msisdn' => $TransData['Msisdn'],
                'TransactionDate' => $TransData['TransactionDate'],
                'TransactionReference' => $TransData['TransactionReference'],
                'TransactionDate' => $TransData['TransactionDate'],
              );

            } else {
              $ResultCode = "102";
              $massage = "Transaction request does not exist!";
              $response = array(
                'ResultCode' => $ResultCode,
                'errorMessage' => $massage
              );
            }
          } else {
            $ResultCode = "102";
            $massage = "Api Key does not exist!";
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
