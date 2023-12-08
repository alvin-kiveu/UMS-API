<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
include 'config.php';
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $ResultCode = '';
  $callbackWebHookUrl = "";
  $data = file_get_contents('php://input');
  // Decode the JSON data
  $data = json_decode($data, true);
  $api_key = $data['api_key'];
  $email = $data['email'];
  $phone = $data['phone'];
  $Sender_Id = $data['Sender_Id'];
  $sendmessage = $data['message'];
  function generateConsumerSecret($length1 = 43)
  {
    $characters1 = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength1 = strlen($characters1);
    $randomString1 = '';
    for ($i1 = 0; $i1 < $length1; $i1++) {
      $randomString1 .= $characters1[rand(0, $charactersLength1 - 1)];
    }
    return $randomString1;
  }
  $request_id =  generateConsumerSecret();
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
      $massage = "Request is missing required email !";
      $response = array(
        'ResultCode' => $ResultCode,
        'errorMessage' => $massage
      );
    } else {
      if ($Sender_Id == '') {
        $ResultCode = "102";
        $massage = "Request is missing required Sender Id !";
        $response = array(
          'ResultCode' => $ResultCode,
          'errorMessage' => $massage
        );
      } else {
        if ($sendmessage == '') {
          $ResultCode = "102";
          $massage = "Request is missing required message !";
          $response = array(
            'ResultCode' => $ResultCode,
            'errorMessage' => $massage
          );
        } else {
          if ($phone == '') {
            $ResultCode = "102";
            $massage = "Request is missing required phone number !";
            $response = array(
              'ResultCode' => $ResultCode,
              'errorMessage' => $massage
            );
          } else {
            $checkEmail = mysqli_query($db, "SELECT * FROM ums_users WHERE email='$email'");
            if (mysqli_num_rows($checkEmail) > 0) {
              $checkSenderId = mysqli_query($db, "SELECT * FROM senderids WHERE email='$email' AND sid='$Sender_Id'");
              if (mysqli_num_rows($checkSenderId) > 0 || $Sender_Id == '23107') {
                $get_user_id = "SELECT *  FROM ums_users  WHERE email='$email' AND apiKey='$api_key';";
                $result_get_user_id = mysqli_query($db, $get_user_id);
                if (mysqli_num_rows($result_get_user_id) > 0) {
                  $userData = mysqli_fetch_array(mysqli_query($db, $get_user_id));
                  if ($email ==  $userData['email']) {
                    //MAKE TO PHONE NUBER TO ACCEPT 254 OR 07 OR 7 0R 01
                    $phone = str_replace(' ', '', $phone);
                    $phone = str_replace('+', '', $phone);
                    //RPLACE 254 WITH 0
                    $phone = str_replace('254', '0', $phone);
                    //GET THE FIRST CHARACTER
                    $firstCharacter = substr($phone, 0, 1);
                    //CHECK IF THE FIRST CHARACTER IS 7
                    if ($firstCharacter == '7') {
                      $phone = '0' . $phone;
                    }
                    if (strlen($phone) != 10) {
                      $ResultCode = "Error";
                      $massage = "Invalid Phone Number format please begin with 254 or 07";
                      $response = array(
                        'ResultCode' => $ResultCode,
                        'errorMessage' => $massage
                      );
                    } else {
                      if (strlen($phone) == 10) {
                        $phone = '254' . (int)$phone;
                      } else if (strlen($phone) == 9) {
                        $phone = '254' . $phone;
                      } else {
                        $phone = $phone;
                      }
                      $getCreadit = mysqli_fetch_array(mysqli_query($db, "SELECT * FROM umeskiaservice WHERE email='$email' AND productName='sms'"));
                      //CHECK IF THE CALLBACK URL IS SET
                      if (!$getCreadit['callback'] == '' || !$getCreadit['callback'] == null){
                        $callbackWebHookUrl = $getCreadit['callback'];
                      }
                      $credit = $getCreadit['accountBalance'];
                      $totalCharacters = strlen($sendmessage);
                      $numOfSms = ceil($totalCharacters / 150);
                      $newcredit = $credit - $numOfSms;
                      if ($credit > 0) {
                        if ($credit >= $numOfSms) {
                          $baseUrl = "http://bulksms.mobitechtechnologies.com/api/sendsms";
                          $ch = curl_init($baseUrl);
                          $data = array(
                            'api_key' => '6198b32ccfeb7',
                            'username' => 'umeskia',
                            'sender_id' => $Sender_Id,
                            'message' => $sendmessage,
                            'phone' => $phone
                          );
                          $payload = json_encode($data);
                          curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                          curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Accept:application/json'));
                          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                          $result = curl_exec($ch);
                          curl_close($ch);
                          $newResonse = str_replace(array('[', ']'), '',  $result);
                          $data = json_decode($newResonse);
                          $status = $data->status;
                          if ($status == '200') {
                            $msg_id = $data->message_id;
                            mysqli_query($db, "UPDATE umeskiaservice SET accountBalance='$newcredit' WHERE email='$email' AND productName='sms'");
                            mysqli_query($db, "INSERT INTO messagedeliver(email,messageid) VALUES ('$email','$msg_id')");
                            $ResultCode = "200";
                            $massage = "Sms sent successfully";
                            $response = array(
                              'success' => $ResultCode,
                              'message' => $massage,
                              'request_id' => $request_id
                            );
                          } else {
                            $ResultCode = "503";
                            $massage = "Application error please try again later";
                            $response = array(
                              'ResultCode' => $ResultCode,
                              'errorMessage' => $massage
                            );
                          }
                        } else {
                          $ResultCode = "400";
                          $massage = "The sms length is too long to be sent with the available credit balance of $credit";
                          $response = array(
                            'ResultCode' => $ResultCode,
                            'errorMessage' => $massage
                          );
                        }
                      } else {
                        $ResultCode = "400";
                        $massage = "Insuficient credit balance";
                        $response = array(
                          'ResultCode' => $ResultCode,
                          'errorMessage' => $massage
                        );
                      }
                    }
                  } else {
                    $ResultCode = "Error";
                    $massage = "Incorect Email";
                    $response = array(
                      'ResultCode' => $ResultCode,
                      'errorMessage' => $massage
                    );
                  }
                } else {
                  $ResultCode = "Error";
                  $massage = "Incorect API key, please check it.";
                  $response = array(
                    'ResultCode' => $ResultCode,
                    'errorMessage' => $massage
                  );
                }
              } else {
                $ResultCode = "Error";
                $massage = "The Sender ID $Sender_Id is not registered with us please confirm it. or you have not been approved to use it.";
                $response = array(
                  'ResultCode' => $ResultCode,
                  'errorMessage' => $massage
                );
              }
            } else {
              $ResultCode = "Error";
              $massage = "The email $email is not registered with us please confirm it.";
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
  //CHECK IF THE CALLBACK URL IS SET
  if (!$callbackWebHookUrl == '') {
    //SEND WEBHOOK
    if ($ResultCode == '200') {
      $dataWebhook = [
        'success' => $ResultCode,
        'message' => $sendmessage,
        'request_id' => $request_id,
      ];
    } elseif ($ResultCode == '503') {
      $dataWebhook = [
        'ResultCode' => $ResultCode,
        'errorMessage' => $massage,
      ];
    } elseif ($ResultCode == '400') {
      $dataWebhook = [
        'ResultCode' => $ResultCode,
        'errorMessage' => $massage,
      ];
    }
    // Convert data to JSON
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
}
