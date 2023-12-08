<?php
include 'config.php';
if (isset($_SERVER['HTTP_ORIGIN'])) {
  // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
  // you want to allow, and if so:
  header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $ResultCode = '';
  $EncodeData = file_get_contents('php://input');
  $submitedData = json_decode($EncodeData, true);
  $usercode = $_POST['usercode'];
  //GET DATA FROM hotspotCodes
  $getInfo = mysqli_query($db, "SELECT * FROM hotspotCodes WHERE accessCode='$usercode'");
  if (mysqli_num_rows($getInfo) > 0) {
    $info = mysqli_fetch_array($getInfo);
    $name = $info['name'];
    $refID = $info['TransactionID'];
    $start = gmdate("M Y d h:i:s:a", $info['start']);
    $stop = gmdate("M Y d h:i:s:a", $info['stop']);
    $amount = $info['amount'];
    $ResultCode = "200";
    $massage = "data fetched sucessfuly";
    $response = array(
      'ResultCode' => $ResultCode,
      'message' => $massage,
      'name' => $name,
      'refID' => $refID,
      'start' => $start,
      'stop' => $stop,
      'amount' => $amount,
    );
  } else {
    $ResultCode = "201";
    $massage = "Genrated code";
    $response = array(
      'ResultCode' => $ResultCode,
      'errorMessage' => $massage
    );
  }
} else {
  $ResultCode = "201";
  $massage = "Invelid Request method";
  $response = array(
    'ResultCode' => $ResultCode,
    'errorMessage' => $massage
  );
}

if (!$ResultCode == '') {
  echo json_encode($response, JSON_PRETTY_PRINT);
}