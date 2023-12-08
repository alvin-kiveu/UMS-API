<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
https: //smsportal.hostpinnacle.co.ke/SMSApi/report/status?userid=umeskia&password=xxxxx&uuid=8403414672158573510&output=json




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
        function hostpinnacleSmsReport($uuid)
        {
          $userid = "umeskia";
          $password = "z18aypEW";
          $url = "https://smsportal.hostpinnacle.co.ke/SMSApi/report/status?userid=$userid&password={$password}&uuid={$uuid}&output=json";
          $response = file_get_contents($url);
          return $response;
        }
        $uuid = $request_id;
        $response = hostpinnacleSmsReport($uuid);
        $data = json_decode($response, true);
        $status = $data['response']['status'];
        $uuid = $data['response']['report_statusList'][0]['status']['uuid'];
        $msgId = $data['response']['report_statusList'][0]['status']['msgId'];
        $length = $data['response']['report_statusList'][0]['status']['length'];
        $msgType = $data['response']['report_statusList'][0]['status']['msgType'];
        $StatusDelivery = $data['response']['report_statusList'][0]['status']['Status'];
        $mobileNo = $data['response']['report_statusList'][0]['status']['mobileNo'];
        $text = $data['response']['report_statusList'][0]['status']['text'];
        $deliveryStatus = $data['response']['report_statusList'][0]['status']['Status'];
        $submittedTime = $data['response']['report_statusList'][0]['status']['Submitted Time'];
        $deliveredTime = $data['response']['report_statusList'][0]['status']['Delivered Time'];
        $senderName = $data['response']['report_statusList'][0]['status']['senderName'];
        $cost = $data['response']['report_statusList'][0]['status']['cost'];
        $ResultCode = "200";
        $response = array(
          'ResultCode' => $ResultCode,
          'request_id' => $request_id,
          'submitted_time' => $submittedTime,
          'send_time' => $deliveredTime,
          'sender_name' => $senderName,
          'recepient' => $mobileNo,
          'sms_unit' =>  $cost,
          'sms_length' => $length,
          'sms_type' => $msgType,
          'message' => $text,
          'status' => $deliveryStatus
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
