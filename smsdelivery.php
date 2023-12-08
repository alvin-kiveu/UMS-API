<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  include 'config.php';
  $ResultCode = '';
  $EncodeData = file_get_contents('php://input');
  $submitedData = json_decode($EncodeData, true);
  $api_key = mysqli_escape_string($db, $submitedData['api_key']);
  $email = mysqli_escape_string($db, $submitedData['email']);
  $request_id = mysqli_escape_string($db, $submitedData['request_id']);
  if ($api_key == '' or $email == '' or  $request_id == '') {
    $ResultCode = "102";
    $massage = "Request is missing required query parameter!";
    $response = array(
      'ResultCode' => $ResultCode,
      'errorMessage' => $massage
    );
  } else {
    $checkcridential = mysqli_query($db, "SELECT *  FROM ums_users  WHERE email='$email'");
    if (mysqli_num_rows($checkcridential) > 0) {
      $checkdata = mysqli_query($db, "SELECT *  FROM ums_users  WHERE email='$email' AND apiKey='$api_key'");
      if (mysqli_num_rows($checkdata) > 0) {
        $baseUrl = "http://bulksms.mobitechtechnologies.com/api/sms_delivery_status";
        $ch = curl_init($baseUrl);
        $data = array(
          'api_key' => '6198b32ccfeb7',
          'username' => 'umeskia',
          'message_id' => $request_id
        );
        $payload = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Accept:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $datasent = json_decode($result);
        curl_close($ch);
        $ResultCode = "200";
        $response = array(
          'ResultCode' => $ResultCode,
          'request_id' => $datasent->message_id,
          'send_time' => $datasent->send_time,
          'sender_name' => $datasent->sender_name,
          'recepient' => $datasent->recepient,
          'sms_unit' => $datasent->sms_unit,
          'network_name' => $datasent->network_name,
          'message' => $datasent->message,
          'status' => $datasent->status
        );
      } else {
        $ResultCode = "Error";
        $massage = "Invalid  Api key : $api_key, please try again";
        $response = array(
          'ResultCode' => $ResultCode,
          'errorMessage' => $massage
        );
      }
    } else {
      $ResultCode = "Error";
      $massage = "Invalid email";
      $response = array(
        'ResultCode' => $ResultCode,
        'errorMessage' => $massage
      );
    }
  }
} else {
  $ResultCode = "Error";
  $massage = "Invalid Request method";
  $response = array(
    'ResultCode' => $ResultCode,
    'errorMessage' => $massage
  );
}

if (!$ResultCode == '') {
  echo json_encode($response);
}
